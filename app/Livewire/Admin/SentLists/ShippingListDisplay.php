<?php

namespace App\Livewire\Admin\SentLists;

use App\Models\SentList;
use App\Models\WorkOrder;
use App\Models\Lot;
use Livewire\Component;
use Livewire\Attributes\On;



class ShippingListDisplay extends Component
{
    public $refreshInterval = 30; // Segundos para auto-refresh
    public $filterDepartment = '';
    public $filterStatus = '';
    public $showCompleted = true;

    // Modal de lotes
    public $showLotModal = false;
    public $selectedWorkOrderId = null;
    public $selectedWorkOrder = null;
    public $lots = []; // Array de lotes: [['id' => 1, 'number' => '001', 'quantity' => 100], ...]

    // Modal de estado de departamentos
    public $showDepartmentStatusModal = false;
    public $selectedWoForStatus = null;
    public $departmentStatuses = [
        'materials' => 'pending',
        'quality' => 'pending',
        'production' => 'pending',
    ];

    public function mount()
    {
        // Inicializar filtros
    }

    #[On('refresh-display')]
    public function refreshDisplay()
    {
        // Forzar actualización
    }

    public function toggleCompleted()
    {
        $this->showCompleted = !$this->showCompleted;
    }

    public function setDepartmentFilter($department)
    {
        $this->filterDepartment = $department === $this->filterDepartment ? '' : $department;
    }

    public function setStatusFilter($status)
    {
        $this->filterStatus = $status === $this->filterStatus ? '' : $status;
    }

    public function getWorkstationHeaderColor($type)
    {
        return match($type) {
            'Mesa' => 'bg-blue-600',
            'Máquina' => 'bg-green-600',
            'Semi-Automática' => 'bg-gray-600',
            default => 'bg-gray-600',
        };
    }

    public function openLotModal($workOrderId)
    {
        $this->selectedWorkOrderId = $workOrderId;
        $this->selectedWorkOrder = WorkOrder::with(['purchaseOrder.part', 'lots'])->find($workOrderId);
        
        if (!$this->selectedWorkOrder) {
            session()->flash('error', 'Work Order no encontrada.');
            return;
        }

        // Cargar lotes existentes
        $this->lots = $this->selectedWorkOrder->lots->map(function ($lot) {
            return [
                'id' => $lot->id,
                'number' => $lot->lot_number,
                'quantity' => $lot->quantity,
            ];
        })->toArray();

        $this->showLotModal = true;
    }

    public function closeLotModal()
    {
        $this->showLotModal = false;
        $this->selectedWorkOrderId = null;
        $this->selectedWorkOrder = null;
        $this->lots = [];
    }

    public function addLot()
    {
        $this->lots[] = [
            'id' => null,
            'number' => '',
            'quantity' => '',
        ];
    }

    public function removeLot($index)
    {
        // Si el lote tiene ID, significa que existe en la BD y debe eliminarse
        if (isset($this->lots[$index]['id']) && $this->lots[$index]['id']) {
            $lot = Lot::find($this->lots[$index]['id']);
            if ($lot) {
                $lot->delete();
                session()->flash('message', 'Lote eliminado correctamente.');
            }
        }
        
        // Remover del array
        unset($this->lots[$index]);
        $this->lots = array_values($this->lots); // Reindexar
    }

    public function saveLots()
    {
        // Validar
        $this->validate([
            'lots.*.number' => 'required|string|max:255',
            'lots.*.quantity' => 'required|integer|min:1',
        ], [
            'lots.*.number.required' => 'El número de lote es requerido.',
            'lots.*.quantity.required' => 'La cantidad es requerida.',
            'lots.*.quantity.integer' => 'La cantidad debe ser un número.',
            'lots.*.quantity.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        if (!$this->selectedWorkOrder) {
            session()->flash('error', 'Work Order no encontrada.');
            return;
        }

        $po = $this->selectedWorkOrder->purchaseOrder;
        $part = $po->part;

        foreach ($this->lots as $lotData) {
            if ($lotData['id']) {
                // Actualizar lote existente
                $lot = Lot::find($lotData['id']);
                if ($lot) {
                    $lot->update([
                        'lot_number' => $lotData['number'],
                        'quantity' => $lotData['quantity'],
                    ]);
                }
            } else {
                // Crear nuevo lote
                Lot::create([
                    'work_order_id' => $this->selectedWorkOrder->id,
                    'lot_number' => $lotData['number'],
                    'quantity' => $lotData['quantity'],
                    'description' => $part->description,
                    'status' => Lot::STATUS_PENDING,
                ]);
            }
        }

        session()->flash('message', 'Lotes actualizados correctamente.');
        $this->closeLotModal();
        $this->dispatch('refresh-display');
    }

    public function openDepartmentStatusModal($workOrderId, $department)
    {
        $this->selectedWoForStatus = $workOrderId;
        $this->showDepartmentStatusModal = true;
    }

    public function closeDepartmentStatusModal()
    {
        $this->showDepartmentStatusModal = false;
        $this->selectedWoForStatus = null;
    }

    public function updateDepartmentStatus($department, $status)
    {
        $this->departmentStatuses[$department] = $status;
    }

    public function saveDepartmentStatuses()
    {
        // Por ahora solo cerramos el modal
        // Cuando tengas la lógica, aquí guardarás en la BD
        session()->flash('message', 'Estados actualizados correctamente (estático por ahora).');
        $this->closeDepartmentStatusModal();
    }

    public function render()
    {
        // Obtener Work Orders con lots (todos los estados)
        $query = WorkOrder::with([
            'purchaseOrder.part.standards' => function ($query) {
                $query->active();
            },
            'lots', // Cargar todos los lotes sin filtrar por estado
            'sentList'
        ])
        ->whereHas('lots'); // Solo WOs que tengan al menos un lote

        // Aplicar filtros de SentList si existen
        if ($this->filterDepartment) {
            $query->whereHas('sentList', function ($q) {
                $q->where('current_department', $this->filterDepartment);
            });
        }

        if ($this->filterStatus) {
            $query->whereHas('sentList', function ($q) {
                $q->where('status', $this->filterStatus);
            });
        }

        if (!$this->showCompleted) {
            $query->whereHas('sentList', function ($q) {
                $q->where('status', '!=', SentList::STATUS_CONFIRMED);
            });
        }

        $workOrders = $query->orderBy('wo_number')->get();

        // Agrupar por tipo de estación (Mesa, Máquina, Semi-Automática)
        $workOrdersGrouped = $workOrders->groupBy(function ($wo) {
            $standard = $wo->purchaseOrder->part->standards()->active()->first();
            if (!$standard) return 'Sin Clasificar';
            
            $assemblyMode = $standard->getAssemblyMode();
            return match($assemblyMode) {
                'manual' => 'Mesa',
                'machine' => 'Máquina',
                'semi_automatic' => 'Semi-Automática',
                default => 'Sin Clasificar',
            };
        });

        return view('livewire.admin.sent-lists.shipping-list-display', [
            'workOrdersGrouped' => $workOrdersGrouped,
        ])->layout('components.layouts.app');
    }
}
