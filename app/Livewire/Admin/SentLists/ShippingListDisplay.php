<?php

namespace App\Livewire\Admin\SentLists;

use App\Models\SentList;
use App\Models\WorkOrder;
use App\Models\Lot;
use App\Models\Kit;
use App\Models\QualityWeighing;
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
        'inspection' => 'pending',
        'production' => 'pending',
    ];

    // Modal de inspeccion por lote
    public $showInspectionModal = false;
    public $selectedLotId = null;
    public $selectedLot = null;
    public $inspectionStatus = 'pending';
    public $inspectionComments = '';

    // Modal de Kit por lote
    public $showKitModal = false;
    public $selectedLotForKit = null;
    public $selectedKit = null;
    public $kitStatus = 'preparing';

    // Sub-modal crear kit desde el modal de kit
    public $showCreateKitForm = false;
    public $newKitNumber = '';

    // Modal de Empaque por lote
    public $showPackagingModal = false;
    public $selectedLotForPackaging = null;
    public $packagingStatus = 'pending';
    public $packagingComments = '';

    // Modal de Pesada (Producción) por lote
    public $showProductionModal = false;
    public $selectedLotForProduction = null;
    public $prodWeighedPieces = 0;
    public $prodWeighedAt = '';
    public $prodComments = '';
    public $prodQuantity = 0;
    public $prodRemainingPieces = 0;
    public $prodAlreadyWeighed = 0;

    // Modal de Pesada (Calidad) por lote
    public $showQualityModal = false;
    public $selectedLotForQuality = null;
    public $qualGoodPieces = 0;
    public $qualBadPieces = 0;
    public $qualWeighedAt = '';
    public $qualComments = '';
    public $qualProductionGoodPieces = 0;
    public $qualAlreadyWeighed = 0;
    public $qualRemainingPieces = 0;
    public $qualKitId = null;
    public $qualKits = [];
    public $qualWeighingsList = [];
    public $qualEditingId = null;
    public $qualIsCrimp = true;

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

        // No abrir modal de kit para partes que no son CRIMP
        $isCrimp = (bool) ($this->selectedLotForKit->workOrder->purchaseOrder->part->is_crimp ?? true);
        if (!$isCrimp) {
            session()->flash('error', 'Esta parte no es CRIMP — el lote funciona como kit.');
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
        $this->showCreateKitForm = false;
        $this->newKitNumber = '';
        $this->resetErrorBag();
    }

    /**
     * Show the create kit form inside the kit modal.
     */
    public function openCreateKitForm()
    {
        if ($this->selectedLotForKit) {
            $this->newKitNumber = Kit::generateKitNumber($this->selectedLotForKit->work_order_id);
        }
        $this->showCreateKitForm = true;
    }

    /**
     * Close the create kit form.
     */
    public function closeCreateKitForm()
    {
        $this->showCreateKitForm = false;
        $this->newKitNumber = '';
        $this->resetErrorBag();
    }

    /**
     * Save the new kit and associate it to the lot.
     */
    public function saveNewKit()
    {
        $this->validate([
            'newKitNumber' => 'required|string|max:255|unique:kits,kit_number',
        ], [
            'newKitNumber.required' => 'El número de kit es requerido.',
            'newKitNumber.unique' => 'Este número de kit ya existe.',
        ]);

        if (!$this->selectedLotForKit) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $kit = Kit::create([
            'work_order_id' => $this->selectedLotForKit->work_order_id,
            'kit_number' => $this->newKitNumber,
            'status' => Kit::STATUS_PREPARING,
            'current_approval_cycle' => 1,
        ]);

        // Asociar kit al lote via pivot
        $this->selectedLotForKit->kits()->attach($kit->id);

        // Actualizar el modal con el kit recién creado
        $this->selectedKit = $kit;
        $this->kitStatus = $kit->status;
        $this->showCreateKitForm = false;
        $this->newKitNumber = '';

        session()->flash('message', "Kit {$kit->kit_number} creado y asociado al lote.");
        $this->dispatch('refresh-display');
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
     * Open inspection status modal for a specific lot.
     */
    public function openInspectionModal($lotId)
    {
        $this->selectedLotId = $lotId;
        $this->selectedLot = Lot::with(['workOrder.purchaseOrder.part', 'kits'])->find($lotId);

        if (!$this->selectedLot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        // VALIDACION DE DEPENDENCIA MAT -> INSP
        if (!$this->selectedLot->canBeInspected()) {
            $reason = $this->selectedLot->getInspectionBlockedReason();
            session()->flash('error', $reason);
            return;
        }

        // Cargar valores actuales
        $this->inspectionStatus = $this->selectedLot->inspection_status ?? 'pending';
        $this->inspectionComments = $this->selectedLot->inspection_comments ?? '';

        $this->showInspectionModal = true;
    }

    /**
     * Close inspection status modal.
     */
    public function closeInspectionModal()
    {
        $this->showInspectionModal = false;
        $this->selectedLotId = null;
        $this->selectedLot = null;
        $this->inspectionStatus = 'pending';
        $this->inspectionComments = '';
        $this->resetErrorBag();
    }

    /**
     * Set inspection status (for visual update).
     */
    public function setInspectionStatus($status)
    {
        $this->inspectionStatus = $status;
    }

    /**
     * Save inspection status for the selected lot.
     */
    public function saveInspectionStatus()
    {
        // Validar
        $rules = [
            'inspectionStatus' => 'required|in:pending,approved,rejected',
        ];

        // Comentario requerido si es rechazado
        if ($this->inspectionStatus === 'rejected') {
            $rules['inspectionComments'] = 'required|string|min:5|max:1000';
        } else {
            $rules['inspectionComments'] = 'nullable|string|max:1000';
        }

        $this->validate($rules, [
            'inspectionStatus.required' => 'Debe seleccionar un status de inspeccion.',
            'inspectionComments.required' => 'Debe indicar el motivo del rechazo.',
            'inspectionComments.min' => 'El comentario debe tener al menos 5 caracteres.',
        ]);

        if (!$this->selectedLot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        // Doble verificacion de dependencia MAT -> INSP
        if (!$this->selectedLot->canBeInspected()) {
            session()->flash('error', 'Este lote ya no puede ser inspeccionado. El kit asociado no esta liberado.');
            $this->closeInspectionModal();
            return;
        }

        // Actualizar lote
        $this->selectedLot->update([
            'inspection_status' => $this->inspectionStatus,
            'inspection_comments' => $this->inspectionComments,
            'inspection_completed_at' => now(),
            'inspection_completed_by' => auth()->id(),
        ]);

        $statusLabel = Lot::getInspectionStatuses()[$this->inspectionStatus];
        session()->flash('message', "Status de inspeccion actualizado a: {$statusLabel}");

        $this->closeInspectionModal();
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

        // Calcular piezas ya pesadas
        $alreadyWeighed = \App\Models\Weighing::where('lot_id', $lot->id)
            ->selectRaw('COALESCE(SUM(good_pieces), 0) as total')
            ->value('total');
        $this->prodAlreadyWeighed = (int) $alreadyWeighed;
        $this->prodRemainingPieces = max(0, $lot->quantity - $this->prodAlreadyWeighed);

        $this->prodWeighedPieces = 0;
        $this->prodWeighedAt = now()->format('Y-m-d\TH:i');
        $this->prodComments = '';
        $this->showProductionModal = true;
    }

    public function closeProductionModal()
    {
        $this->showProductionModal = false;
        $this->selectedLotForProduction = null;
        $this->prodQuantity = 0;
        $this->prodWeighedPieces = 0;
        $this->prodWeighedAt = '';
        $this->prodComments = '';
        $this->prodRemainingPieces = 0;
        $this->prodAlreadyWeighed = 0;
        $this->resetErrorBag();
    }

    public function saveProduction()
    {
        $this->validate([
            'prodWeighedPieces' => 'required|integer|min:1',
            'prodWeighedAt' => 'required|date',
            'prodComments' => 'nullable|string|max:1000',
        ], [
            'prodWeighedPieces.required' => 'Las piezas pesadas son requeridas.',
            'prodWeighedPieces.min' => 'Debe registrar al menos 1 pieza.',
            'prodWeighedAt.required' => 'La fecha y hora son requeridas.',
        ]);

        if (!$this->selectedLotForProduction) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        if ($this->prodWeighedPieces > $this->prodRemainingPieces) {
            $this->addError('prodWeighedPieces', 'Las piezas pesadas (' . number_format($this->prodWeighedPieces) . ') sobrepasan la cantidad pendiente (' . number_format($this->prodRemainingPieces) . ').');
            return;
        }

        \App\Models\Weighing::create([
            'lot_id' => $this->selectedLotForProduction->id,
            'kit_id' => null,
            'quantity' => $this->selectedLotForProduction->quantity,
            'good_pieces' => $this->prodWeighedPieces,
            'bad_pieces' => 0,
            'weighed_at' => $this->prodWeighedAt,
            'weighed_by' => auth()->id(),
            'comments' => $this->prodComments ?: null,
        ]);

        session()->flash('message', 'Pesada registrada correctamente.');
        $this->closeProductionModal();
        $this->dispatch('refresh-display');
    }

    // ===============================================
    // QUALITY (CALIDAD) MODAL
    // ===============================================

    public function openQualityModal($lotId)
    {
        $lot = Lot::with(['workOrder.purchaseOrder.part', 'kits', 'weighings', 'qualityWeighings.weighedBy'])->find($lotId);

        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        // Verificar que haya pesadas de produccion
        if (!$lot->hasProductionWeighings()) {
            session()->flash('error', 'Este lote no tiene pesadas de produccion aun. Produccion debe pesar primero.');
            return;
        }

        $this->selectedLotForQuality = $lot;
        $this->qualProductionGoodPieces = $lot->getProductionGoodPieces();
        $this->qualAlreadyWeighed = $lot->getQualityAlreadyWeighed();
        $this->qualRemainingPieces = $lot->getQualityPendingPieces();
        $this->qualGoodPieces = 0;
        $this->qualBadPieces = 0;
        $this->qualWeighedAt = now()->format('Y-m-d\TH:i');
        $this->qualComments = '';
        $this->qualIsCrimp = (bool) ($lot->workOrder->purchaseOrder->part->is_crimp ?? true);
        $this->qualKitId = null;
        $this->qualKits = $this->qualIsCrimp ? $lot->kits : collect([]);
        $this->qualWeighingsList = $lot->qualityWeighings->map(function ($qw) {
            return [
                'id' => $qw->id,
                'good_pieces' => $qw->good_pieces,
                'bad_pieces' => $qw->bad_pieces,
                'disposition' => $qw->disposition,
                'rework_status' => $qw->rework_status,
                'weighed_at' => $qw->weighed_at->format('d/m/Y H:i'),
                'weighed_by' => $qw->weighedBy->name ?? 'N/A',
                'comments' => $qw->comments,
            ];
        })->toArray();
        $this->showQualityModal = true;
    }

    public function closeQualityModal()
    {
        $this->showQualityModal = false;
        $this->selectedLotForQuality = null;
        $this->qualProductionGoodPieces = 0;
        $this->qualAlreadyWeighed = 0;
        $this->qualRemainingPieces = 0;
        $this->qualGoodPieces = 0;
        $this->qualBadPieces = 0;
        $this->qualWeighedAt = '';
        $this->qualComments = '';
        $this->qualKitId = null;
        $this->qualKits = [];
        $this->qualWeighingsList = [];
        $this->qualEditingId = null;
        $this->qualIsCrimp = true;
        $this->resetErrorBag();
    }

    public function saveQuality()
    {
        $this->validate([
            'qualGoodPieces' => 'required|integer|min:0',
            'qualBadPieces' => 'required|integer|min:0',
            'qualWeighedAt' => 'required|date',
            'qualComments' => 'nullable|string|max:1000',
        ], [
            'qualGoodPieces.required' => 'Las piezas aprobadas son requeridas.',
            'qualGoodPieces.min' => 'Las piezas aprobadas no pueden ser negativas.',
            'qualBadPieces.required' => 'Las piezas rechazadas son requeridas.',
            'qualBadPieces.min' => 'Las piezas rechazadas no pueden ser negativas.',
            'qualWeighedAt.required' => 'La fecha y hora son requeridas.',
        ]);

        if (!$this->selectedLotForQuality) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $total = $this->qualGoodPieces + $this->qualBadPieces;
        if ($total > $this->qualRemainingPieces) {
            $this->addError('qualGoodPieces', 'La suma de piezas aprobadas + rechazadas (' . number_format($total) . ') sobrepasa la cantidad pendiente de verificar (' . number_format($this->qualRemainingPieces) . ').');
            return;
        }

        if ($total <= 0) {
            $this->addError('qualGoodPieces', 'Debe registrar al menos 1 pieza (aprobada o rechazada).');
            return;
        }

        $data = [
            'lot_id' => $this->selectedLotForQuality->id,
            'kit_id' => $this->qualKitId ?: null,
            'production_good_pieces' => $this->qualProductionGoodPieces,
            'good_pieces' => $this->qualGoodPieces,
            'bad_pieces' => $this->qualBadPieces,
            'disposition' => $this->qualBadPieces > 0 ? QualityWeighing::DISPOSITION_SCRAP : null,
            'rework_status' => null,
            'weighed_at' => $this->qualWeighedAt,
            'weighed_by' => auth()->id(),
            'comments' => $this->qualComments ?: null,
        ];

        if ($this->qualEditingId) {
            $qw = QualityWeighing::find($this->qualEditingId);
            if ($qw) {
                $qw->update($data);
                $message = 'Pesada de calidad actualizada correctamente.';
            } else {
                session()->flash('error', 'Pesada no encontrada.');
                return;
            }
        } else {
            $qw = QualityWeighing::create($data);
            $message = 'Pesada de calidad registrada correctamente.';
        }

        if ($this->qualBadPieces > 0) {
            $message .= ' ' . number_format($this->qualBadPieces) . ' piezas descartadas.';
        }

        session()->flash('message', $message);
        $this->closeQualityModal();
        $this->dispatch('refresh-display');
    }

    public function editQualityWeighing($qualityWeighingId)
    {
        $qw = QualityWeighing::find($qualityWeighingId);
        if (!$qw) {
            session()->flash('error', 'Pesada no encontrada.');
            return;
        }

        $this->qualEditingId = $qw->id;
        $this->qualGoodPieces = $qw->good_pieces;
        $this->qualBadPieces = $qw->bad_pieces;
        $this->qualWeighedAt = $qw->weighed_at->format('Y-m-d\TH:i');
        $this->qualComments = $qw->comments ?? '';
        $this->qualKitId = $qw->kit_id;
    }

    public function cancelEditQuality()
    {
        $this->qualEditingId = null;
        $this->qualGoodPieces = 0;
        $this->qualBadPieces = 0;
        $this->qualWeighedAt = now()->format('Y-m-d\TH:i');
        $this->qualComments = '';
        $this->qualKitId = null;
        $this->resetErrorBag();
    }

    public function deleteQualityWeighing($qualityWeighingId)
    {
        $qw = QualityWeighing::find($qualityWeighingId);
        if ($qw) {
            $qw->delete();
            // Refresh the modal data
            if ($this->selectedLotForQuality) {
                $this->openQualityModal($this->selectedLotForQuality->id);
            }
            session()->flash('message', 'Pesada de calidad eliminada.');
            $this->dispatch('refresh-display');
        }
    }

    public function render()
    {
        // Obtener Work Orders con lots (todos los estados)
        $query = WorkOrder::with([
            'purchaseOrder.part.standards' => function ($query) {
                $query->active();
            },
            'lots.weighings', // Cargar todos los lotes con sus pesadas
            'lots.qualityWeighings', // Cargar pesadas de calidad
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
