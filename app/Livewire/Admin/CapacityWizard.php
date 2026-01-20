<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\{Shift, Part, SentList, User, Lot};
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
    /**
     * Work order items with configuration details
     * 
     * Structure: [
     *   'part_id' => int,
     *   'part_number' => string,
     *   'part_description' => string,
     *   'quantity' => int,
     *   'required_hours' => float,
     *   'configuration' => [
     *     'workstation_type' => string,
     *     'workstation_type_label' => string,
     *     'persons_required' => int,
     *     'units_per_hour' => int
     *   ]
     * ]
     */
    public array $workOrderItems = [];
    public ?int $currentPartId = null;
    public int $currentQuantity = 0;
    public float $totalRequiredHours = 0;
    public float $remainingHours = 0;
    public float $suggestedOvertime = 0;

    // Modal PO state
    public bool $showPOModal = false;
    public array $selectedPOs = [];
    public array $poConfigurations = []; // Store selected configuration for each PO
    public string $poSearchTerm = '';

    // Step 3 - Lista Preliminar
    public ?int $generatedSentListId = null;
    public array $lotNumbers = []; // Números de lote para cada PO (múltiples por índice)
    
    // Modal de Lotes
    public bool $showLotModal = false;
    public ?int $currentLotIndex = null; // Índice del item actual para agregar lotes
    public array $tempLots = []; // Lotes temporales para el modal

    // UI state
    public string $errorMessage = '';
    public string $successMessage = '';
    public array $warnings = [];

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

    // ==========================================
    // PO Modal Methods
    // ==========================================

    public function openPOModal()
    {
        $this->showPOModal = true;
        $this->selectedPOs = [];
        $this->poConfigurations = [];
        $this->poSearchTerm = '';
    }

    public function closePOModal()
    {
        $this->showPOModal = false;
        $this->selectedPOs = [];
        $this->poConfigurations = [];
    }

    public function togglePOSelection(int $poId)
    {
        if (in_array($poId, $this->selectedPOs)) {
            // Remove from selection
            $this->selectedPOs = array_values(array_diff($this->selectedPOs, [$poId]));
            unset($this->poConfigurations[$poId]);
        } else {
            // Add to selection
            $this->selectedPOs[] = $poId;
        }
    }

    public function setConfigurationForPO(int $poId, int $configId)
    {
        $this->poConfigurations[$poId] = $configId;
    }

    public function addSelectedPOs()
    {
        if (empty($this->selectedPOs)) {
            $this->errorMessage = 'Debe seleccionar al menos un PO.';
            return;
        }

        try {
            $pos = \App\Models\PurchaseOrder::with(['part.standards.configurations'])
                ->whereIn('id', $this->selectedPOs)
                ->get();

            foreach ($pos as $po) {
                // Check if already added
                $existingIndex = array_search($po->part_id, array_column($this->workOrderItems, 'part_id'));
                if ($existingIndex !== false) {
                    continue; // Skip if already added
                }

                $standard = $po->part->standards()->where('active', true)->first();
                
                if (!$standard || !$standard->hasMigratedConfigurations()) {
                    $this->warnings[] = "PO {$po->po_number}: Part {$po->part->number} has no configurations.";
                    continue;
                }

                // Get selected configuration or use optimal
                $configId = $this->poConfigurations[$po->id] ?? null;
                
                if ($configId) {
                    $config = $standard->configurations()->find($configId);
                    if (!$config) {
                        $this->warnings[] = "PO {$po->po_number}: Selected configuration not found.";
                        continue;
                    }
                    
                    // Validate persons required
                    if ($config->persons_required > $this->numPersons) {
                        $this->warnings[] = "PO {$po->po_number}: Configuration requires {$config->persons_required} persons but only {$this->numPersons} available.";
                        continue;
                    }
                    
                    $requiredHours = $config->calculateRequiredHours($po->quantity);
                } else {
                    // Use optimal configuration
                    $result = $this->service->calculateRequiredHours(
                        $po->part_id,
                        $po->quantity,
                        $this->numPersons
                    );
                    
                    $requiredHours = $result['required_hours'];
                    $config = $standard->configurations()->find($result['configuration']['id']);
                }

                // Validate capacity
                if ($config) {
                    $validation = $config->validateCapacity();
                    if (!$validation['is_valid']) {
                        $this->warnings[] = "PO {$po->po_number}: {$validation['message']}";
                    }
                }

                $this->workOrderItems[] = [
                    'part_id' => $po->part_id,
                    'part_number' => $po->part->number,
                    'part_description' => $po->part->description,
                    'quantity' => $po->quantity,
                    'required_hours' => $requiredHours,
                    'po_id' => $po->id,
                    'po_number' => $po->po_number,
                    'configuration' => [
                        'id' => $config->id,
                        'workstation_type' => $config->workstation_type,
                        'workstation_type_label' => $config->workstation_type_label,
                        'persons_required' => $config->persons_required,
                        'units_per_hour' => $config->units_per_hour,
                    ],
                ];
            }

            $this->calculateDifference();
            $this->closePOModal();
            
            $count = count($this->selectedPOs);
            $this->successMessage = "{$count} PO(s) agregado(s) exitosamente.";
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al agregar POs: ' . $e->getMessage();
        }
    }

    public function getAvailablePOsProperty()
    {
        $query = \App\Models\PurchaseOrder::with(['part.standards.configurations', 'workOrder'])
            ->where('status', \App\Models\PurchaseOrder::STATUS_APPROVED)
            ->whereHas('part.standards', function($q) {
                $q->where('active', true)
                  ->has('configurations');
            })
            // Solo POs que tienen Work Order con status "Open"
            ->whereHas('workOrder.status', function($q) {
                $q->where('name', 'Open');
            });

        if (!empty($this->poSearchTerm)) {
            $query->where(function($q) {
                $q->where('po_number', 'like', "%{$this->poSearchTerm}%")
                  ->orWhereHas('part', function($partQuery) {
                      $partQuery->where('number', 'like', "%{$this->poSearchTerm}%")
                                ->orWhere('description', 'like', "%{$this->poSearchTerm}%");
                  });
            });
        }

        return $query->orderBy('po_number')->get();
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
            
            if (!$standard) {
                $this->errorMessage = "No hay estándar activo para la parte {$part->number}.";
                return;
            }

            // Check if standard has configurations
            if (!$standard->hasMigratedConfigurations()) {
                $this->errorMessage = "Part {$part->number} has no configurations. Please add configurations for this standard in the Standards management section.";
                return;
            }

            // Verificar si ya se agregó esta parte
            $existingIndex = array_search($this->currentPartId, array_column($this->workOrderItems, 'part_id'));
            if ($existingIndex !== false) {
                $this->errorMessage = "La parte {$part->number} ya fue agregada.";
                return;
            }

            // Calculate using service with available employees
            $result = $this->service->calculateRequiredHours(
                $this->currentPartId,
                $this->currentQuantity,
                $this->numPersons  // Available employees from loaded shifts
            );

            // Validate capacity for the selected configuration
            $configuration = \App\Models\StandardConfiguration::find($result['configuration']['id']);
            if ($configuration) {
                $validation = $configuration->validateCapacity();
                if (!$validation['is_valid']) {
                    $this->warnings[] = "Part {$part->number}: {$validation['message']}";
                }
            }

            $this->workOrderItems[] = [
                'part_id' => $this->currentPartId,
                'part_number' => $part->number,
                'part_description' => $part->description,
                'quantity' => $this->currentQuantity,
                'required_hours' => $result['required_hours'],
                'po_id' => null, // Agregado manualmente, no desde PO
                'po_number' => null,
                'configuration' => $result['configuration'],
            ];

            $this->calculateDifference();

            // Reset form
            $this->currentPartId = null;
            $this->currentQuantity = 0;

            // Dispatch event to clear Tom Select
            $this->dispatch('partAdded');

            $this->errorMessage = '';
            $this->successMessage = "Parte {$part->number} agregada. Horas requeridas: {$result['required_hours']}";
        } catch (\RuntimeException $e) {
            $this->errorMessage = $e->getMessage();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error al agregar parte: ' . $e->getMessage();
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
    // Step 3 Methods - Lista Preliminar
    // ==========================================

    public function openLotModal(int $index)
    {
        $this->currentLotIndex = $index;
        // Cargar lotes existentes o inicializar con uno vacío
        $this->tempLots = $this->lotNumbers[$index] ?? [''];
        if (empty($this->tempLots)) {
            $this->tempLots = [''];
        }
        $this->showLotModal = true;
    }

    public function closeLotModal()
    {
        $this->showLotModal = false;
        $this->currentLotIndex = null;
        $this->tempLots = [];
    }

    public function addLotInput()
    {
        $this->tempLots[] = '';
    }

    public function removeLotInput(int $lotIndex)
    {
        if (count($this->tempLots) > 1) {
            unset($this->tempLots[$lotIndex]);
            $this->tempLots = array_values($this->tempLots);
        }
    }

    public function saveLots()
    {
        if ($this->currentLotIndex === null) {
            return;
        }

        // Filtrar lotes vacíos y guardar
        $filteredLots = array_values(array_filter($this->tempLots, fn($lot) => !empty(trim($lot))));
        
        if (!empty($filteredLots)) {
            $this->lotNumbers[$this->currentLotIndex] = $filteredLots;
        } else {
            unset($this->lotNumbers[$this->currentLotIndex]);
        }

        $this->closeLotModal();
    }

    public function setLotNumber(int $index, string $lotNumber)
    {
        if (!empty(trim($lotNumber))) {
            $this->lotNumbers[$index] = [$lotNumber];
        } else {
            unset($this->lotNumbers[$index]);
        }
    }

    public function generateSentList()
    {
        if (empty($this->workOrderItems)) {
            $this->errorMessage = 'No hay items para generar la lista.';
            return;
        }

        try {
            DB::transaction(function () {
                // Crear SentList como Lista Preliminar
                $sentList = SentList::create([
                    'po_id' => null, // Ya no se usa, ahora es relación many-to-many
                    'shift_ids' => $this->selectedShifts,
                    'num_persons' => $this->numPersons,
                    'start_date' => $this->startDate,
                    'end_date' => $this->endDate,
                    'total_available_hours' => $this->totalAvailableHours,
                    'used_hours' => $this->totalRequiredHours,
                    'remaining_hours' => max(0, $this->remainingHours),
                    'status' => SentList::STATUS_PENDING,
                    'current_department' => SentList::DEPT_MATERIALS,
                    'notes' => 'Lista preliminar generada desde Capacity Wizard',
                ]);

                // Sync shifts
                $sentList->shifts()->sync($this->selectedShifts);

                // Attach purchase orders with their details and create Lot records
                foreach ($this->workOrderItems as $index => $item) {
                    if (isset($item['po_id'])) {
                        // Obtener el PO para acceder al work_order_id
                        $purchaseOrder = \App\Models\PurchaseOrder::with('workOrder')->find($item['po_id']);
                        
                        // Convertir array de lotes a string separado por comas para la tabla pivot
                        $lotNumbersString = null;
                        $lotNumbersArray = [];
                        
                        if (isset($this->lotNumbers[$index])) {
                            if (is_array($this->lotNumbers[$index])) {
                                $lotNumbersArray = $this->lotNumbers[$index];
                                $lotNumbersString = implode(', ', $this->lotNumbers[$index]);
                            } else {
                                $lotNumbersArray = [$this->lotNumbers[$index]];
                                $lotNumbersString = $this->lotNumbers[$index];
                            }
                        }
                        
                        $sentList->purchaseOrders()->attach($item['po_id'], [
                            'quantity' => $item['quantity'],
                            'required_hours' => $item['required_hours'],
                            'lot_number' => $lotNumbersString,
                        ]);
                        
                        // Crear registros Lot reales para que aparezcan en /admin/lots
                        if ($purchaseOrder && $purchaseOrder->workOrder && !empty($lotNumbersArray)) {
                            $workOrder = $purchaseOrder->workOrder;
                            $quantityPerLot = count($lotNumbersArray) > 0 
                                ? intval($item['quantity'] / count($lotNumbersArray)) 
                                : $item['quantity'];
                            
                            foreach ($lotNumbersArray as $lotNumber) {
                                if (!empty(trim($lotNumber))) {
                                    // Verificar si ya existe un lote con ese número para esta WO
                                    $existingLot = Lot::where('work_order_id', $workOrder->id)
                                        ->where('lot_number', trim($lotNumber))
                                        ->first();
                                    
                                    if (!$existingLot) {
                                        Lot::create([
                                            'work_order_id' => $workOrder->id,
                                            'lot_number' => trim($lotNumber),
                                            'description' => "Lote creado desde Lista Preliminar #{$sentList->id}",
                                            'quantity' => $quantityPerLot,
                                            'status' => Lot::STATUS_PENDING,
                                            'comments' => "Generado automáticamente desde Capacity Wizard",
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }

                $this->generatedSentListId = $sentList->id;
            });

            $this->successMessage = '¡Lista preliminar generada exitosamente!';
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
            'lotNumbers',
            'showLotModal',
            'currentLotIndex',
            'tempLots',
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
        $this->warnings = [];
    }

    public function render()
    {
        // Obtener todas las partes activas que tienen estándar activo con configuraciones
        $partsWithStandard = Part::active()
            ->whereHas('standards', function($q) {
                $q->where('active', true)
                  ->has('configurations');
            })
            ->orderBy('number')
            ->get();

        return view('livewire.admin.capacity-wizard', [
            'shifts' => Shift::active()->get(),
            'parts' => $partsWithStandard,
        ]);
    }
}
