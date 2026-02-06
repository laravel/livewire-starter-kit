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

    // Modal de calidad por lote
    public $showQualityModal = false;
    public $selectedLotId = null;
    public $selectedLot = null;
    public $qualityStatus = 'pending';
    public $qualityComments = '';

    // Modal de Kit por lote
    public $showKitModal = false;
    public $selectedLotForKit = null;
    public $selectedKit = null;
    public $kitStatus = 'preparing';

    // Modal de Empaque por lote
    public $showPackagingModal = false;
    public $selectedLotForPackaging = null;
    public $packagingStatus = 'pending';
    public $packagingComments = '';

    // Modal de Calidad Final por lote
    public $showFinalQualityModal = false;
    public $selectedLotForFinalQuality = null;
    public $finalQualityStatus = 'pending';
    public $finalQualityComments = '';

    // Modal de Pesada (Producción) por lote
    public $showProductionModal = false;
    public $selectedLotForProduction = null;
    public $prodGoodPieces = 0;
    public $prodBadPieces = 0;
    public $prodWeighedAt = '';
    public $prodComments = '';
    public $prodQuantity = 0;
    public $prodKitId = null;
    public $prodKits = [];

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

        // Validar que la suma de lotes no sobrepase la Cant. WO
        $totalNewQuantity = collect($this->lots)->sum('quantity');
        $cantWO = $this->selectedWorkOrder->original_quantity;
        if ($totalNewQuantity > $cantWO) {
            session()->flash('error', 'ALERTA: La suma de lotes (' . number_format($totalNewQuantity) . ') sobrepasa la Cant. WO (' . number_format($cantWO) . ') por ' . number_format($totalNewQuantity - $cantWO) . ' piezas.');
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

    /**
     * Open kit status modal for a specific lot.
     */
    public function openKitModal($lotId)
    {
        $this->selectedLotForKit = Lot::with(['workOrder.purchaseOrder.part', 'kits'])->find($lotId);

        if (!$this->selectedLotForKit) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        // Obtener el kit asociado al lote (el más reciente)
        $this->selectedKit = $this->selectedLotForKit->kits->sortByDesc('created_at')->first();
        
        if ($this->selectedKit) {
            $this->kitStatus = $this->selectedKit->status ?? 'preparing';
        } else {
            $this->kitStatus = 'preparing';
        }

        $this->showKitModal = true;
    }

    /**
     * Close kit status modal.
     */
    public function closeKitModal()
    {
        $this->showKitModal = false;
        $this->selectedLotForKit = null;
        $this->selectedKit = null;
        $this->kitStatus = 'preparing';
        $this->resetErrorBag();
    }

    /**
     * Set kit status (for visual update).
     */
    public function setKitStatus($status)
    {
        $this->kitStatus = $status;
    }

    /**
     * Save kit status.
     */
    public function saveKitStatus()
    {
        $this->validate([
            'kitStatus' => 'required|in:released,rejected',
        ], [
            'kitStatus.required' => 'Debe seleccionar Aprobado o Rechazado.',
        ]);

        if (!$this->selectedKit) {
            session()->flash('error', 'No hay kit asociado a este lote.');
            $this->closeKitModal();
            return;
        }

        // Actualizar kit
        $this->selectedKit->update([
            'status' => $this->kitStatus,
        ]);

        $statusLabels = [
            'released' => 'Aprobado',
            'rejected' => 'Rechazado',
        ];
        
        $statusLabel = $statusLabels[$this->kitStatus] ?? $this->kitStatus;
        session()->flash('message', "Status de kit actualizado a: {$statusLabel}");

        $this->closeKitModal();
        $this->dispatch('refresh-display');
    }

    /**
     * Approve a lot (set status to completed).
     */
    public function approveLot($lotId)
    {
        $lot = Lot::find($lotId);
        
        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $lot->update([
            'status' => Lot::STATUS_COMPLETED,
        ]);

        session()->flash('message', "Lote {$lot->lot_number} aprobado correctamente.");
        $this->dispatch('refresh-display');
    }

    /**
     * Reject a lot (set status to cancelled).
     */
    public function rejectLot($lotId)
    {
        $lot = Lot::find($lotId);
        
        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $lot->update([
            'status' => Lot::STATUS_CANCELLED,
        ]);

        session()->flash('message', "Lote {$lot->lot_number} rechazado.");
        $this->dispatch('refresh-display');
    }

    /**
     * Open quality status modal for a specific lot.
     */
    public function openQualityModal($lotId)
    {
        $this->selectedLotId = $lotId;
        $this->selectedLot = Lot::with(['workOrder.purchaseOrder.part', 'kits'])->find($lotId);

        if (!$this->selectedLot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        // VALIDACION DE DEPENDENCIA MAT -> CAL
        if (!$this->selectedLot->canBeInspectedByQuality()) {
            $reason = $this->selectedLot->getQualityBlockedReason();
            session()->flash('error', $reason);
            return;
        }

        // Cargar valores actuales
        $this->qualityStatus = $this->selectedLot->quality_status ?? 'pending';
        $this->qualityComments = $this->selectedLot->quality_comments ?? '';

        $this->showQualityModal = true;
    }

    /**
     * Close quality status modal.
     */
    public function closeQualityModal()
    {
        $this->showQualityModal = false;
        $this->selectedLotId = null;
        $this->selectedLot = null;
        $this->qualityStatus = 'pending';
        $this->qualityComments = '';
        $this->resetErrorBag();
    }

    /**
     * Set quality status (for visual update).
     */
    public function setQualityStatus($status)
    {
        $this->qualityStatus = $status;
    }

    /**
     * Save quality status for the selected lot.
     */
    public function saveQualityStatus()
    {
        // Validar
        $rules = [
            'qualityStatus' => 'required|in:pending,approved,rejected',
        ];

        // Comentario requerido si es rechazado
        if ($this->qualityStatus === 'rejected') {
            $rules['qualityComments'] = 'required|string|min:5|max:1000';
        } else {
            $rules['qualityComments'] = 'nullable|string|max:1000';
        }

        $this->validate($rules, [
            'qualityStatus.required' => 'Debe seleccionar un status de calidad.',
            'qualityComments.required' => 'Debe indicar el motivo del rechazo.',
            'qualityComments.min' => 'El comentario debe tener al menos 5 caracteres.',
        ]);

        if (!$this->selectedLot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        // Doble verificacion de dependencia MAT -> CAL
        if (!$this->selectedLot->canBeInspectedByQuality()) {
            session()->flash('error', 'Este lote ya no puede ser inspeccionado. El kit asociado no esta liberado.');
            $this->closeQualityModal();
            return;
        }

        // Actualizar lote
        $this->selectedLot->update([
            'quality_status' => $this->qualityStatus,
            'quality_comments' => $this->qualityComments,
            'quality_inspected_at' => now(),
            'quality_inspected_by' => auth()->id(),
        ]);

        $statusLabel = Lot::getQualityStatuses()[$this->qualityStatus];
        session()->flash('message', "Status de calidad actualizado a: {$statusLabel}");

        $this->closeQualityModal();
        $this->dispatch('refresh-display');
    }

    // ===============================================
    // PACKAGING (EMPAQUE) MODAL
    // ===============================================

    public function openPackagingModal($lotId)
    {
        $lot = Lot::with(['workOrder.purchaseOrder.part'])->find($lotId);

        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $this->selectedLotForPackaging = $lot;
        $this->packagingStatus = $lot->packaging_status ?? 'pending';
        $this->packagingComments = $lot->packaging_comments ?? '';
        $this->showPackagingModal = true;
    }

    public function closePackagingModal()
    {
        $this->showPackagingModal = false;
        $this->selectedLotForPackaging = null;
        $this->packagingStatus = 'pending';
        $this->packagingComments = '';
        $this->resetErrorBag();
    }

    public function setPackagingStatus($status)
    {
        $this->packagingStatus = $status;
    }

    public function savePackagingStatus()
    {
        $rules = [
            'packagingStatus' => 'required|in:pending,approved,rejected',
        ];

        if ($this->packagingStatus === 'rejected') {
            $rules['packagingComments'] = 'required|string|min:5|max:1000';
        } else {
            $rules['packagingComments'] = 'nullable|string|max:1000';
        }

        $this->validate($rules, [
            'packagingStatus.required' => 'Debe seleccionar un status de empaque.',
            'packagingComments.required' => 'Debe indicar el motivo del rechazo.',
            'packagingComments.min' => 'El comentario debe tener al menos 5 caracteres.',
        ]);

        if (!$this->selectedLotForPackaging) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $this->selectedLotForPackaging->update([
            'packaging_status' => $this->packagingStatus,
            'packaging_comments' => $this->packagingComments,
            'packaging_inspected_at' => now(),
            'packaging_inspected_by' => auth()->id(),
        ]);

        $statusLabels = ['pending' => 'Pendiente', 'approved' => 'Aprobado', 'rejected' => 'Rechazado'];
        $statusLabel = $statusLabels[$this->packagingStatus] ?? $this->packagingStatus;
        session()->flash('message', "Status de empaque actualizado a: {$statusLabel}");

        $this->closePackagingModal();
        $this->dispatch('refresh-display');
    }

    // ===============================================
    // FINAL QUALITY (CALIDAD) MODAL
    // ===============================================

    public function openFinalQualityModal($lotId)
    {
        $lot = Lot::with(['workOrder.purchaseOrder.part'])->find($lotId);

        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $this->selectedLotForFinalQuality = $lot;
        $this->finalQualityStatus = $lot->final_quality_status ?? 'pending';
        $this->finalQualityComments = $lot->final_quality_comments ?? '';
        $this->showFinalQualityModal = true;
    }

    public function closeFinalQualityModal()
    {
        $this->showFinalQualityModal = false;
        $this->selectedLotForFinalQuality = null;
        $this->finalQualityStatus = 'pending';
        $this->finalQualityComments = '';
        $this->resetErrorBag();
    }

    public function setFinalQualityStatus($status)
    {
        $this->finalQualityStatus = $status;
    }

    public function saveFinalQualityStatus()
    {
        $rules = [
            'finalQualityStatus' => 'required|in:pending,approved,rejected',
        ];

        if ($this->finalQualityStatus === 'rejected') {
            $rules['finalQualityComments'] = 'required|string|min:5|max:1000';
        } else {
            $rules['finalQualityComments'] = 'nullable|string|max:1000';
        }

        $this->validate($rules, [
            'finalQualityStatus.required' => 'Debe seleccionar un status de calidad.',
            'finalQualityComments.required' => 'Debe indicar el motivo del rechazo.',
            'finalQualityComments.min' => 'El comentario debe tener al menos 5 caracteres.',
        ]);

        if (!$this->selectedLotForFinalQuality) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $this->selectedLotForFinalQuality->update([
            'final_quality_status' => $this->finalQualityStatus,
            'final_quality_comments' => $this->finalQualityComments,
            'final_quality_inspected_at' => now(),
            'final_quality_inspected_by' => auth()->id(),
        ]);

        $statusLabels = ['pending' => 'Pendiente', 'approved' => 'Aprobado', 'rejected' => 'Rechazado'];
        $statusLabel = $statusLabels[$this->finalQualityStatus] ?? $this->finalQualityStatus;
        session()->flash('message', "Status de calidad final actualizado a: {$statusLabel}");

        $this->closeFinalQualityModal();
        $this->dispatch('refresh-display');
    }

    // ===============================================
    // PRODUCTION (PESADA) MODAL
    // ===============================================

    public function openProductionModal($lotId)
    {
        $lot = Lot::with(['workOrder.purchaseOrder.part', 'kits'])->find($lotId);

        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $this->selectedLotForProduction = $lot;
        $this->prodQuantity = $lot->quantity;
        $this->prodGoodPieces = 0;
        $this->prodBadPieces = 0;
        $this->prodWeighedAt = now()->format('Y-m-d\TH:i');
        $this->prodComments = '';
        $this->prodKitId = null;
        $this->prodKits = $lot->kits;
        $this->showProductionModal = true;
    }

    public function closeProductionModal()
    {
        $this->showProductionModal = false;
        $this->selectedLotForProduction = null;
        $this->prodQuantity = 0;
        $this->prodGoodPieces = 0;
        $this->prodBadPieces = 0;
        $this->prodWeighedAt = '';
        $this->prodComments = '';
        $this->prodKitId = null;
        $this->prodKits = [];
        $this->resetErrorBag();
    }

    public function saveProduction()
    {
        $this->validate([
            'prodGoodPieces' => 'required|integer|min:0',
            'prodBadPieces' => 'required|integer|min:0',
            'prodWeighedAt' => 'required|date',
            'prodComments' => 'nullable|string|max:1000',
        ], [
            'prodGoodPieces.required' => 'Las piezas buenas son requeridas.',
            'prodGoodPieces.min' => 'Las piezas buenas no pueden ser negativas.',
            'prodBadPieces.required' => 'Las piezas malas son requeridas.',
            'prodBadPieces.min' => 'Las piezas malas no pueden ser negativas.',
            'prodWeighedAt.required' => 'La fecha y hora son requeridas.',
        ]);

        if (!$this->selectedLotForProduction) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        \App\Models\Weighing::create([
            'lot_id' => $this->selectedLotForProduction->id,
            'kit_id' => $this->prodKitId ?: null,
            'quantity' => $this->selectedLotForProduction->quantity,
            'good_pieces' => $this->prodGoodPieces,
            'bad_pieces' => $this->prodBadPieces,
            'weighed_at' => $this->prodWeighedAt,
            'weighed_by' => auth()->id(),
            'comments' => $this->prodComments ?: null,
        ]);

        session()->flash('message', 'Pesada registrada correctamente.');
        $this->closeProductionModal();
        $this->dispatch('refresh-display');
    }

    public function render()
    {
        // Obtener Work Orders con lots (todos los estados)
        $query = WorkOrder::with([
            'purchaseOrder.part.standards' => function ($query) {
                $query->active();
            },
            'lots.weighings', // Cargar todos los lotes con sus pesadas
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
