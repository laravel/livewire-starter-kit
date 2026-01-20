<?php

namespace App\Services;

use App\Models\{Shift, Holiday, OverTime, Standard, Part, SentList, WorkOrder};
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

        // Calculate break minutes from start_break_time and end_break_time
        $break_minutes = 0;
        foreach ($shift->breakTimes as $breakTime) {
            $breakStart = Carbon::parse($breakTime->start_break_time);
            $breakEnd = Carbon::parse($breakTime->end_break_time);
            if ($breakEnd->lessThan($breakStart)) {
                $breakEnd->addDay();
            }
            $break_minutes += $breakStart->diffInMinutes($breakEnd);
        }
        
        $net_minutes = $total_minutes - $break_minutes;

        return round($net_minutes / 60, 2);
    }

    /**
     * Calculate required hours for a work order using standard configurations.
     *
     * @param int $part_id Part ID
     * @param int $quantity Quantity to produce
     * @param int $available_employees Number of available employees
     * @param string|null $preferred_workstation_type Optional preferred workstation type
     * @return array{required_hours: float, configuration: array, part_number: string}
     * @throws \RuntimeException if no standard or configuration found
     */
    public function calculateRequiredHours(
        int $part_id, 
        int $quantity, 
        int $available_employees,
        ?string $preferred_workstation_type = null
    ): array
    {
        $part = Part::find($part_id);

        if (!$part) {
            throw new \RuntimeException("Part with ID {$part_id} not found");
        }

        // Get the active standard for this part
        $standard = Standard::where('part_id', $part_id)
            ->where('active', true)
            ->first();

        if (!$standard) {
            throw new \RuntimeException("No active standard found for part {$part->number}");
        }

        // Check if standard has configurations
        if (!$standard->hasMigratedConfigurations()) {
            throw new \RuntimeException(
                "Part {$part->number} has no configurations. Please add configurations for this standard in the Standards management section."
            );
        }

        // Calculate using optimal configuration
        try {
            $result = $standard->calculateRequiredHoursOptimal(
                $quantity,
                $available_employees,
                $preferred_workstation_type
            );

            $config = $result['configuration'];

            return [
                'required_hours' => $result['hours'],
                'configuration' => [
                    'id' => $config->id,
                    'workstation_type' => $config->workstation_type,
                    'workstation_type_label' => $config->workstation_type_label,
                    'persons_required' => $config->persons_required,
                    'units_per_hour' => $config->units_per_hour,
                    'label' => $config->label,
                ],
                'part_number' => $part->number,
            ];
        } catch (\RuntimeException $e) {
            // Get minimum persons required from all configurations
            $minPersons = $standard->configurations()->min('persons_required') ?? 0;
            
            throw new \RuntimeException(
                "Part {$part->number} requires at least {$minPersons} employee(s). " .
                "Currently {$available_employees} employee(s) are available. " .
                "Please increase available employees or select different shifts."
            );
        }
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
