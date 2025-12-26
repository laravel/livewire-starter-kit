<?php

namespace App\Services;

use App\Models\{Shift, Holiday, OverTime, Standard, Part, SentList, WorkOrder, PurchaseOrder};
use App\Exceptions\CapacityExceededException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CapacityCalculatorService
{
    /**
     * Calculate total available hours based on shifts, persons, and date range.
     *
     * Formula: (shift_hours * num_persons * available_days) + overtime_hours
     *
     * @param array $shift_ids Array of shift IDs
     * @param int $num_persons Number of persons available
     * @param Carbon $start_date Start date
     * @param Carbon $end_date End date
     * @return float Total available hours
     */
    public function calculateTotalAvailableHours(
        array $shift_ids,
        int $num_persons,
        Carbon $start_date,
        Carbon $end_date
    ): float {
        // Get available working days (excluding holidays and weekends)
        $available_days = $this->getAvailableDays($start_date, $end_date);

        // Get shifts with eager loading
        $shifts = Shift::with('breakTimes')
            ->whereIn('id', $shift_ids)
            ->get();

        // Calculate total shift hours (considering breaks)
        $total_shift_hours = 0;
        foreach ($shifts as $shift) {
            $shift_hours = $this->calculateShiftNetHours($shift);
            $total_shift_hours += $shift_hours;
        }

        // Regular hours = available_days × total_shift_hours × num_persons
        $regular_hours = $available_days * $total_shift_hours * $num_persons;

        // Get overtime hours for the date range
        $overtime_hours = OverTime::whereBetween('date', [$start_date, $end_date])
            ->get()
            ->sum('total_hours'); // Uses accessor from OverTime model

        return round($regular_hours + $overtime_hours, 2);
    }

    /**
     * Calculate net hours for a shift (total hours - break time).
     *
     * @param Shift $shift
     * @return float Net hours
     */
    protected function calculateShiftNetHours(Shift $shift): float
    {
        $start = Carbon::parse($shift->start_time);
        $end = Carbon::parse($shift->end_time);

        // Handle shifts that cross midnight
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $total_minutes = $start->diffInMinutes($end);

        // Subtract break times
        $break_minutes = $shift->breakTimes()->sum('duration') ?? 0;
        $net_minutes = $total_minutes - $break_minutes;

        return round($net_minutes / 60, 2);
    }

    /**
     * Calculate required hours for a work order.
     *
     * Formula: quantity / units_per_hour (from standard)
     *
     * @param int $part_id Part ID
     * @param int $quantity Quantity to produce
     * @param string $assembly_mode Assembly mode (1_person, 2_persons, 3_persons)
     * @return float Required hours
     * @throws \Exception if no standard found
     */
    public function calculateRequiredHours(int $part_id, int $quantity, string $assembly_mode = '1_person'): float
    {
        $part = Part::find($part_id);

        if (!$part) {
            throw new \Exception("Part with ID {$part_id} not found");
        }

        // Get the active standard for this part
        $standard = Standard::where('part_id', $part_id)
            ->where('active', true)
            ->first();

        if (!$standard) {
            throw new \Exception("No active standard found for part {$part->number}");
        }

        // Get units_per_hour based on assembly mode
        $units_per_hour = $this->getUnitsPerHourByMode($standard, $assembly_mode);

        if ($units_per_hour === 0) {
            throw new \Exception("Standard for part {$part->number} has units_per_hour = 0 for {$assembly_mode}");
        }

        return round($quantity / $units_per_hour, 2);
    }

    /**
     * Get units per hour based on assembly mode.
     *
     * @param Standard $standard
     * @param string $assembly_mode
     * @return int Units per hour
     */
    protected function getUnitsPerHourByMode(Standard $standard, string $assembly_mode): int
    {
        return match ($assembly_mode) {
            '1_person' => $standard->persons_1 ?? 0,
            '2_persons' => $standard->persons_2 ?? 0,
            '3_persons' => $standard->persons_3 ?? 0,
            default => $standard->units_per_hour ?? 0,
        };
    }

    /**
     * Validate that there is enough capacity available.
     *
     * @param float $remaining_hours Hours remaining
     * @param float $required_hours Hours required
     * @return bool
     * @throws CapacityExceededException if capacity is exceeded
     */
    public function validateCapacity(float $remaining_hours, float $required_hours): bool
    {
        if ($remaining_hours < $required_hours) {
            throw new CapacityExceededException(
                $remaining_hours,
                $required_hours
            );
        }

        return true;
    }

    /**
     * Create a SentList with work orders.
     *
     * Uses database transaction to ensure data integrity.
     *
     * @param int $po_id Purchase Order ID
     * @param array $shift_ids Array of shift IDs
     * @param int $num_persons Number of persons
     * @param Carbon $start_date Start date
     * @param Carbon $end_date End date
     * @param array $work_orders Array of work order data [{part_id, quantity, assembly_mode, required_hours}]
     * @return SentList
     * @throws \Exception if transaction fails
     */
    public function createSentList(
        int $po_id,
        array $shift_ids,
        int $num_persons,
        Carbon $start_date,
        Carbon $end_date,
        array $work_orders
    ): SentList {
        return DB::transaction(function () use (
            $po_id,
            $shift_ids,
            $num_persons,
            $start_date,
            $end_date,
            $work_orders
        ) {
            // Calculate total available hours
            $total_available = $this->calculateTotalAvailableHours(
                $shift_ids,
                $num_persons,
                $start_date,
                $end_date
            );

            // Calculate used hours from work orders
            $used_hours = array_sum(array_column($work_orders, 'required_hours'));
            $remaining_hours = $total_available - $used_hours;

            // Create SentList
            $sentList = SentList::create([
                'po_id' => $po_id,
                'shift_ids' => $shift_ids,
                'num_persons' => $num_persons,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'total_available_hours' => $total_available,
                'used_hours' => $used_hours,
                'remaining_hours' => $remaining_hours,
                'status' => SentList::STATUS_PENDING,
            ]);

            // Create work orders associated with the SentList
            foreach ($work_orders as $wo_data) {
                WorkOrder::create([
                    'wo_number' => WorkOrder::generateWONumber(),
                    'purchase_order_id' => $po_id,
                    'sent_list_id' => $sentList->id,
                    'assembly_mode' => $wo_data['assembly_mode'],
                    'required_hours' => $wo_data['required_hours'],
                    'status_id' => 1, // Default status (adjust based on your system)
                    'sent_pieces' => 0,
                    'opened_date' => now(),
                ]);
            }

            // Sync shifts with SentList (pivot table)
            $sentList->shifts()->sync($shift_ids);

            return $sentList;
        });
    }

    /**
     * Get available working days (excluding holidays and weekends).
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     * @return int Number of available days
     */
    public function getAvailableDays(Carbon $start, Carbon $end): int
    {
        $total_days = $start->diffInDays($end) + 1; // Include both start and end dates

        // Count holidays in range
        $holidays = Holiday::whereBetween('date', [$start, $end])->count();

        // Count weekends in range
        $weekends = $this->countWeekends($start, $end);

        $available_days = $total_days - $holidays - $weekends;

        return max(0, $available_days); // Ensure non-negative
    }

    /**
     * Count weekend days in a date range.
     *
     * @param Carbon $start Start date
     * @param Carbon $end End date
     * @return int Number of weekend days
     */
    public function countWeekends(Carbon $start, Carbon $end): int
    {
        $count = 0;
        $current = $start->copy();

        while ($current->lessThanOrEqualTo($end)) {
            if ($current->isWeekend()) {
                $count++;
            }
            $current->addDay();
        }

        return $count;
    }

    /**
     * Get capacity statistics for a date range.
     *
     * @param Carbon $start_date
     * @param Carbon $end_date
     * @return array
     */
    public function getCapacityStats(Carbon $start_date, Carbon $end_date): array
    {
        $sentLists = SentList::whereBetween('start_date', [$start_date, $end_date])
            ->with('workOrders')
            ->get();

        $total_available = $sentLists->sum('total_available_hours');
        $total_used = $sentLists->sum('used_hours');
        $total_remaining = $sentLists->sum('remaining_hours');

        $utilization = $total_available > 0
            ? round(($total_used / $total_available) * 100, 2)
            : 0;

        return [
            'total_available_hours' => $total_available,
            'total_used_hours' => $total_used,
            'total_remaining_hours' => $total_remaining,
            'capacity_utilization_percent' => $utilization,
            'sent_lists_count' => $sentLists->count(),
            'work_orders_count' => $sentLists->sum(fn($sl) => $sl->workOrders->count()),
        ];
    }
}
