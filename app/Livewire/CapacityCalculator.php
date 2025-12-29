<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\{PurchaseOrder, Shift, Part, SentList, WorkOrder};
use App\Services\CapacityCalculatorService;
use App\Exceptions\CapacityExceededException;
use Carbon\Carbon;

class CapacityCalculator extends Component
{
    // Form inputs
    public $po_id = null;
    public $selected_shifts = [];
    public $num_persons = 1;
    public $start_date;
    public $end_date;

    // Calculator state
    public $total_available_hours = 0;
    public $remaining_hours = 0;
    public $work_orders = [];

    // Add WO form
    public $current_part_id = null;
    public $current_quantity = 0;
    public $current_assembly_mode = '1_person';

    // UI state
    public $error_message = '';
    public $success_message = '';
    public $is_capacity_calculated = false;

    protected CapacityCalculatorService $service;

    /**
     * Boot method to inject service
     */
    public function boot(CapacityCalculatorService $service)
    {
        $this->service = $service;
    }

    /**
     * Validation rules
     */
    protected function rules()
    {
        return [
            'po_id' => 'required|exists:purchase_orders,id',
            'selected_shifts' => 'required|array|min:1',
            'selected_shifts.*' => 'exists:shifts,id',
            'num_persons' => 'required|integer|min:1|max:100',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'current_part_id' => 'nullable|exists:parts,id',
            'current_quantity' => 'nullable|integer|min:1',
            'current_assembly_mode' => 'nullable|in:1_person,2_persons,3_persons',
        ];
    }

    /**
     * Component initialization
     */
    public function mount()
    {
        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(7)->format('Y-m-d');
    }

    /**
     * Calculate total available capacity
     */
    public function calculateCapacity()
    {
        $this->validate([
            'selected_shifts' => 'required|array|min:1',
            'num_persons' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        try {
            $this->total_available_hours = $this->service->calculateTotalAvailableHours(
                $this->selected_shifts,
                $this->num_persons,
                Carbon::parse($this->start_date),
                Carbon::parse($this->end_date)
            );

            $this->remaining_hours = $this->total_available_hours;
            $this->is_capacity_calculated = true;
            $this->error_message = '';
            $this->success_message = 'Capacity calculated successfully!';
        } catch (\Exception $e) {
            $this->error_message = 'Error calculating capacity: ' . $e->getMessage();
            $this->success_message = '';
        }
    }

    /**
     * Add work order to the list
     */
    public function addWorkOrder()
    {
        $this->validate([
            'current_part_id' => 'required|exists:parts,id',
            'current_quantity' => 'required|integer|min:1',
            'current_assembly_mode' => 'required|in:1_person,2_persons,3_persons',
        ]);

        if (!$this->is_capacity_calculated) {
            $this->error_message = 'Please calculate capacity first.';
            return;
        }

        try {
            // Calculate required hours for this work order
            $required_hours = $this->service->calculateRequiredHours(
                $this->current_part_id,
                $this->current_quantity,
                $this->current_assembly_mode
            );

            // Validate capacity
            $this->service->validateCapacity($this->remaining_hours, $required_hours);

            // Get part details
            $part = Part::find($this->current_part_id);

            // Add to work orders list
            $this->work_orders[] = [
                'part_id' => $this->current_part_id,
                'part_number' => $part->number,
                'part_description' => $part->description,
                'quantity' => $this->current_quantity,
                'assembly_mode' => $this->current_assembly_mode,
                'required_hours' => $required_hours,
            ];

            // Update remaining hours
            $this->remaining_hours -= $required_hours;

            // Reset form
            $this->current_part_id = null;
            $this->current_quantity = 0;
            $this->current_assembly_mode = '1_person';

            $this->error_message = '';
            $this->success_message = 'Work order added successfully!';
        } catch (CapacityExceededException $e) {
            $this->error_message = $e->getMessage();
            $this->success_message = '';
        } catch (\Exception $e) {
            $this->error_message = 'Error adding work order: ' . $e->getMessage();
            $this->success_message = '';
        }
    }

    /**
     * Remove work order from the list
     */
    public function removeWorkOrder($index)
    {
        if (isset($this->work_orders[$index])) {
            $removed_hours = $this->work_orders[$index]['required_hours'];
            unset($this->work_orders[$index]);
            $this->work_orders = array_values($this->work_orders); // Re-index array

            // Restore hours
            $this->remaining_hours += $removed_hours;

            $this->success_message = 'Work order removed.';
        }
    }

    /**
     * Generate SentList with all work orders
     */
    public function generateSentList()
    {
        $this->validate([
            'po_id' => 'required|exists:purchase_orders,id',
        ]);

        if (empty($this->work_orders)) {
            $this->error_message = 'Please add at least one work order.';
            return;
        }

        try {
            $sentList = $this->service->createSentList(
                $this->po_id,
                $this->selected_shifts,
                $this->num_persons,
                Carbon::parse($this->start_date),
                Carbon::parse($this->end_date),
                $this->work_orders
            );

            session()->flash('success', 'Lista de envío creada exitosamente!');
            return redirect()->route('admin.sent-lists.show', $sentList->id);
        } catch (\Exception $e) {
            $this->error_message = 'Error generating SentList: ' . $e->getMessage();
            $this->success_message = '';
        }
    }

    /**
     * Reset calculator
     */
    public function resetCalculator()
    {
        $this->reset([
            'po_id',
            'selected_shifts',
            'num_persons',
            'total_available_hours',
            'remaining_hours',
            'work_orders',
            'current_part_id',
            'current_quantity',
            'current_assembly_mode',
            'error_message',
            'success_message',
            'is_capacity_calculated',
        ]);

        $this->start_date = now()->format('Y-m-d');
        $this->end_date = now()->addDays(7)->format('Y-m-d');
        $this->num_persons = 1;
    }

    /**
     * Render component
     */
    public function render()
    {
        return view('livewire.capacity-calculator', [
            'shifts' => Shift::active()->get(),
            'parts' => Part::active()->with('prices')->get(),
            'purchase_orders' => PurchaseOrder::with('part.prices')
                ->whereNotNull('id')
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }
}
