<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\{Shift, Part, SentList, User};
use App\Services\CapacityCalculatorService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CapacityWizard extends Component
{
    // Wizard state
    public int $currentStep = 1;

    // Step 1 - Disponibilidad de horas
    public array $selectedShifts = [];
    public int $numPersons = 0;  // Ahora se calcula automáticamente
    public array $loadedEmployees = [];  // Empleados cargados por turno
    public string $startDate = '';
    public string $endDate = '';
    public float $totalAvailableHours = 0;
    public array $shiftDetails = [];

    // Step 2 - Cálculo de horas necesarias (por número de parte)
    public array $workOrderItems = []; // Items agregados [{part_id, part_number, quantity, required_hours, work_order_id}]
    public ?int $currentPartId = null;
    public int $currentQuantity = 0;
    public float $totalRequiredHours = 0;
    public float $remainingHours = 0;
    public float $suggestedOvertime = 0;

    // Step 3 - Cierre
    public ?int $generatedSentListId = null;

    // UI state
    public string $errorMessage = '';
    public string $successMessage = '';

    protected CapacityCalculatorService $service;

    public function boot(CapacityCalculatorService $service)
    {
        $this->service = $service;
    }

    public function mount()
    {
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(4)->format('Y-m-d');
    }

    // ==========================================
    // Navigation Methods
    // ==========================================

    public function nextStep()
    {
        if ($this->currentStep === 1) {
            if (!$this->validateStep1()) return;
            $this->calculateAvailableHours();
        } elseif ($this->currentStep === 2) {
            if (!$this->validateStep2()) return;
        }

        if ($this->currentStep < 3) {
            $this->currentStep++;
            $this->clearMessages();
        }
    }

    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
            $this->clearMessages();
        }
    }

    public function goToStep(int $step)
    {
        if ($step >= 1 && $step <= 3 && $step <= $this->currentStep) {
            $this->currentStep = $step;
            $this->clearMessages();
        }
    }

    // ==========================================
    // Step 1 Methods - Disponibilidad de horas
    // ==========================================

    protected function validateStep1(): bool
    {
        if (empty($this->selectedShifts)) {
            $this->errorMessage = 'Debe seleccionar al menos un turno.';
            return false;
        }

        // Permitir continuar con 0 personas (se mostrará advertencia en la vista)
        if ($this->numPersons < 0) {
            $this->errorMessage = 'El número de personas no puede ser negativo.';
            return false;
        }

        if (!$this->validateDateRange()) {
            return false;
        }

        return true;
    }

    public function validateDateRange(): bool
    {
        $start = Carbon::parse($this->startDate);
        $end = Carbon::parse($this->endDate);

        if ($end->lt($start)) {
            $this->errorMessage = 'La fecha de fin debe ser posterior a la fecha de inicio.';
            return false;
        }

        $days = $start->diffInDays($end) + 1;
        if ($days > 5) {
            $this->errorMessage = 'El rango de fechas no puede exceder 5 días.';
            return false;
        }

        return true;
    }

    public function calculateAvailableHours()
    {
        try {
            $shifts = Shift::with('breakTimes')
                ->whereIn('id', $this->selectedShifts)
                ->get();

            $this->shiftDetails = [];
            foreach ($shifts as $shift) {
                $netHours = $this->calculateShiftNetHours($shift);
                $this->shiftDetails[] = [
                    'id' => $shift->id,
                    'name' => $shift->name,
                    'start_time' => Carbon::parse($shift->start_time)->format('H:i'),
                    'end_time' => Carbon::parse($shift->end_time)->format('H:i'),
                    'net_hours' => $netHours,
                ];
            }

            $this->totalAvailableHours = $this->service->calculateTotalAvailableHours(
                $this->selectedShifts,
                $this->numPersons,
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate)
            );

            $this->remainingHours = $this->totalAvailableHours;
            $this->errorMessage = '';
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al calcular horas: ' . $e->getMessage();
        }
    }

    protected function calculateShiftNetHours(Shift $shift): float
    {
        $start = Carbon::parse($shift->start_time);
        $end = Carbon::parse($shift->end_time);

        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $totalMinutes = $start->diffInMinutes($end);
        
        $breakMinutes = 0;
        foreach ($shift->breakTimes as $breakTime) {
            $breakStart = Carbon::parse($breakTime->start_break_time);
            $breakEnd = Carbon::parse($breakTime->end_break_time);
            if ($breakEnd->lessThan($breakStart)) {
                $breakEnd->addDay();
            }
            $breakMinutes += $breakStart->diffInMinutes($breakEnd);
        }
        
        $netMinutes = $totalMinutes - $breakMinutes;

        return round($netMinutes / 60, 2);
    }

    public function updatedSelectedShifts()
    {
        // Cargar empleados para los turnos seleccionados
        $this->loadEmployeesForShifts();
        
        if (!empty($this->selectedShifts) && $this->currentStep === 1) {
            $this->calculateAvailableHours();
        }
    }

    // ==========================================
    // Employee Loading Methods
    // ==========================================

    /**
     * Carga los empleados activos asociados a los turnos seleccionados.
     * Agrupa los empleados por turno para mostrar en la vista.
     */
    protected function loadEmployeesForShifts(): void
    {
        if (empty($this->selectedShifts)) {
            $this->loadedEmployees = [];
            $this->numPersons = 0;
            return;
        }

        // Obtener empleados activos con rol 'employee' de los turnos seleccionados
        $employees = User::active()
            ->role('employee')
            ->whereIn('shift_id', $this->selectedShifts)
            ->with('shift:id,name')
            ->orderBy('shift_id')
            ->orderBy('name')
            ->get();

        // Agrupar por turno
        $grouped = [];
        foreach ($employees as $employee) {
            $shiftId = $employee->shift_id;
            if (!isset($grouped[$shiftId])) {
                $grouped[$shiftId] = [
                    'shift_id' => $shiftId,
                    'shift_name' => $employee->shift->name ?? 'Sin turno',
                    'employees' => [],
                ];
            }
            $grouped[$shiftId]['employees'][] = [
                'id' => $employee->id,
                'name' => $employee->name,
                'last_name' => $employee->last_name,
                'full_name' => $employee->full_name,
                'employee_number' => $employee->employee_number,
                'position' => $employee->position,
            ];
        }

        $this->loadedEmployees = array_values($grouped);
        $this->numPersons = $employees->count();
    }

    /**
     * Obtiene el total de empleados cargados.
     */
    public function getTotalEmployeesProperty(): int
    {
        return $this->numPersons;
    }

    /**
     * Obtiene el conteo de empleados por turno.
     */
    public function getEmployeeCountByShiftProperty(): array
    {
        $counts = [];
        foreach ($this->loadedEmployees as $group) {
            $counts[$group['shift_id']] = count($group['employees']);
        }
        return $counts;
    }

    // ==========================================
    // Step 2 Methods - Agregar por número de parte
    // ==========================================

    protected function validateStep2(): bool
    {
        if (empty($this->workOrderItems)) {
            $this->errorMessage = 'Debe agregar al menos un número de parte.';
            return false;
        }
        return true;
    }

    public function addWorkOrderItem()
    {
        $this->validate([
            'currentPartId' => 'required|exists:parts,id',
            'currentQuantity' => 'required|integer|min:1',
        ], [
            'currentPartId.required' => 'Debe seleccionar un número de parte.',
            'currentPartId.exists' => 'El número de parte no existe.',
            'currentQuantity.required' => 'La cantidad es requerida.',
            'currentQuantity.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        try {
            $part = Part::find($this->currentPartId);

            // Obtener el estándar para calcular horas
            $standard = $part->standards()->where('active', true)->first();
            
            if (!$standard || !$standard->units_per_hour || $standard->units_per_hour == 0) {
                $this->errorMessage = "No hay estándar activo con unidades por hora para la parte {$part->number}.";
                return;
            }

            // Verificar si ya se agregó esta parte
            $existingIndex = array_search($this->currentPartId, array_column($this->workOrderItems, 'part_id'));
            if ($existingIndex !== false) {
                $this->errorMessage = "La parte {$part->number} ya fue agregada.";
                return;
            }

            $requiredHours = round($this->currentQuantity / $standard->units_per_hour, 2);

            $this->workOrderItems[] = [
                'part_id' => $this->currentPartId,
                'part_number' => $part->number,
                'part_description' => $part->description,
                'quantity' => $this->currentQuantity,
                'required_hours' => $requiredHours,
                'units_per_hour' => $standard->units_per_hour,
            ];

            $this->calculateDifference();

            // Reset form
            $this->currentPartId = null;
            $this->currentQuantity = 0;

            // Dispatch event to clear Tom Select
            $this->dispatch('partAdded');

            $this->errorMessage = '';
            $this->successMessage = "Parte {$part->number} agregada. Horas requeridas: {$requiredHours}";
        } catch (\Exception $e) {
            $this->errorMessage = $e->getMessage();
        }
    }

    public function removeWorkOrderItem(int $index)
    {
        if (isset($this->workOrderItems[$index])) {
            unset($this->workOrderItems[$index]);
            $this->workOrderItems = array_values($this->workOrderItems);
            $this->calculateDifference();
            $this->successMessage = 'Item eliminado.';
        }
    }

    public function calculateDifference()
    {
        $this->totalRequiredHours = array_sum(array_column($this->workOrderItems, 'required_hours'));
        $this->remainingHours = $this->totalAvailableHours - $this->totalRequiredHours;

        if ($this->remainingHours < 0) {
            $this->suggestedOvertime = abs($this->remainingHours);
        } else {
            $this->suggestedOvertime = 0;
        }
    }

    // ==========================================
    // Step 3 Methods - Cierre y salida
    // ==========================================

    public function generateSentList()
    {
        if (empty($this->workOrderItems)) {
            $this->errorMessage = 'No hay items para generar la lista.';
            return;
        }

        try {
            DB::transaction(function () {
                // Crear SentList (sin po_id ya que es una lista de planificación)
                $sentList = SentList::create([
                    'po_id' => null, // Se asignará cuando se creen los WOs
                    'shift_ids' => $this->selectedShifts,
                    'num_persons' => $this->numPersons,
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'total_available_hours' => $this->totalAvailableHours,
                    'used_hours' => $this->totalRequiredHours,
                    'remaining_hours' => max(0, $this->remainingHours),
                    'status' => SentList::STATUS_PENDING,
                ]);

                // Sync shifts
                $sentList->shifts()->sync($this->selectedShifts);

                $this->generatedSentListId = $sentList->id;
            });

            $this->successMessage = '¡Lista de planificación generada exitosamente!';
            $this->errorMessage = '';
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al generar la lista: ' . $e->getMessage();
        }
    }

    public function resetWizard()
    {
        $this->reset([
            'currentStep',
            'selectedShifts',
            'numPersons',
            'loadedEmployees',
            'totalAvailableHours',
            'shiftDetails',
            'workOrderItems',
            'currentPartId',
            'currentQuantity',
            'totalRequiredHours',
            'remainingHours',
            'suggestedOvertime',
            'generatedSentListId',
            'errorMessage',
            'successMessage',
        ]);

        $this->numPersons = 0;
        $this->startDate = now()->format('Y-m-d');
        $this->endDate = now()->addDays(4)->format('Y-m-d');
    }

    public function viewSentList()
    {
        if ($this->generatedSentListId) {
            return redirect()->route('admin.sent-lists.show', $this->generatedSentListId);
        }
    }

    protected function clearMessages()
    {
        $this->errorMessage = '';
        $this->successMessage = '';
    }

    public function render()
    {
        // Obtener todas las partes activas que tienen estándar activo con units_per_hour
        $partsWithStandard = Part::active()
            ->whereHas('standards', fn($q) => $q->where('active', true)->where('units_per_hour', '>', 0))
            ->orderBy('number')
            ->get();

        return view('livewire.admin.capacity-wizard', [
            'shifts' => Shift::active()->get(),
            'parts' => $partsWithStandard,
        ]);
    }
}
