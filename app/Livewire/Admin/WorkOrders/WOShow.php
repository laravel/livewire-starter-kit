<?php

namespace App\Livewire\Admin\WorkOrders;

use App\Models\Kit;
use App\Models\Lot;
use App\Models\StatusWO;
use App\Models\Weighing;
use App\Models\WorkOrder;
use App\Services\PurchaseOrderService;
use App\Services\SignatureService;
use Livewire\Component;

class WOShow extends Component
{
    public WorkOrder $workOrder;

    protected PurchaseOrderService $purchaseOrderService;
    protected SignatureService $signatureService;

    // ===============================================
    // TAB MANAGEMENT
    // ===============================================
    public string $activeTab = 'general';

    // ===============================================
    // LOT CRUD PROPERTIES
    // ===============================================
    public bool $showLotModal = false;
    public bool $showDeleteLotConfirm = false;
    public ?int $editingLotId = null;
    public string $lotNumber = '';
    public int $lotQuantity = 0;
    public string $lotStatus = 'pending';
    public string $lotDescription = '';
    public string $lotComments = '';

    // ===============================================
    // KIT CRUD PROPERTIES
    // ===============================================
    public bool $showKitModal = false;
    public bool $showDeleteKitConfirm = false;
    public ?int $editingKitId = null;
    public string $kitStatus = 'preparing';
    public array $selectedLots = [];
    public string $kitValidationNotes = '';

    // ===============================================
    // WEIGHING CRUD PROPERTIES
    // ===============================================
    public bool $showWeighingModal = false;
    public bool $showDeleteWeighingConfirm = false;
    public ?int $editingWeighingId = null;
    public ?int $weighingLotId = null;
    public ?int $weighingKitId = null;
    public int $goodPieces = 0;
    public int $badPieces = 0;
    public string $weighedAt = '';
    public string $weighingComments = '';
    public int $remainingPieces = 0;

    // ===============================================
    // LIFECYCLE
    // ===============================================

    public function boot(PurchaseOrderService $purchaseOrderService, SignatureService $signatureService): void
    {
        $this->purchaseOrderService = $purchaseOrderService;
        $this->signatureService = $signatureService;
    }

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder->load([
            'purchaseOrder.part',
            'purchaseOrder.signatures.user',
            'status',
            'statusLogs.fromStatus',
            'statusLogs.toStatus',
            'statusLogs.user',
            'lots.kits',
            'lots.weighings.weighedBy',
            'kits.lots',
            'kits.preparedBy',
            'kits.releasedBy',
        ]);
    }

    public function refreshWorkOrder(): void
    {
        $this->workOrder = $this->workOrder->fresh([
            'purchaseOrder.part',
            'purchaseOrder.signatures.user',
            'status',
            'statusLogs.fromStatus',
            'statusLogs.toStatus',
            'statusLogs.user',
            'lots.kits',
            'lots.weighings.weighedBy',
            'kits.lots',
            'kits.preparedBy',
            'kits.releasedBy',
        ]);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    // ===============================================
    // EXISTING METHODS (signatures, status)
    // ===============================================

    public function openSignatureModal(): void
    {
        if ($this->workOrder->purchaseOrder) {
            $this->dispatch('openSignatureModal', purchaseOrderId: $this->workOrder->purchaseOrder->id);
        }
    }

    public function updateStatus(int $statusId, ?string $comments = null): void
    {
        $this->purchaseOrderService->updateWorkOrderStatus($this->workOrder, $statusId, $comments);

        session()->flash('flash.banner', 'Estado actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->refreshWorkOrder();
    }

    // ===============================================
    // LOT CRUD
    // ===============================================

    public function openCreateLotModal(): void
    {
        $this->resetLotForm();
        $this->showLotModal = true;
    }

    public function openEditLotModal(int $lotId): void
    {
        $lot = Lot::findOrFail($lotId);
        $this->editingLotId = $lotId;
        $this->lotNumber = $lot->lot_number;
        $this->lotQuantity = $lot->quantity;
        $this->lotStatus = $lot->status;
        $this->lotDescription = $lot->description ?? '';
        $this->lotComments = $lot->comments ?? '';
        $this->showLotModal = true;
    }

    public function saveLot(): void
    {
        $this->validate([
            'lotNumber' => 'required|string|max:255',
            'lotQuantity' => 'required|integer|min:1',
            'lotStatus' => 'required|in:pending,in_progress,completed,cancelled',
        ], [
            'lotNumber.required' => 'El número de lote es obligatorio.',
            'lotQuantity.required' => 'La cantidad es obligatoria.',
            'lotQuantity.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        // Validate total doesn't exceed WO quantity
        $excludeId = $this->editingLotId ?? 0;
        $otherLotsTotal = $this->workOrder->lots()->where('id', '!=', $excludeId)->sum('quantity');
        if (($otherLotsTotal + $this->lotQuantity) > $this->workOrder->original_quantity) {
            $available = $this->workOrder->original_quantity - $otherLotsTotal;
            $this->addError('lotQuantity', 'La suma de lotes sobrepasaría la Cant. WO (' . number_format($this->workOrder->original_quantity) . '). Máximo disponible: ' . number_format($available));
            return;
        }

        if ($this->editingLotId) {
            $lot = Lot::findOrFail($this->editingLotId);
            $lot->update([
                'lot_number' => $this->lotNumber,
                'quantity' => $this->lotQuantity,
                'status' => $this->lotStatus,
                'description' => $this->lotDescription,
                'comments' => $this->lotComments,
            ]);
            $message = 'Lote actualizado correctamente.';
        } else {
            $part = $this->workOrder->purchaseOrder->part;
            Lot::create([
                'work_order_id' => $this->workOrder->id,
                'lot_number' => $this->lotNumber,
                'quantity' => $this->lotQuantity,
                'status' => $this->lotStatus,
                'description' => $this->lotDescription ?: ($part->description ?? ''),
                'comments' => $this->lotComments,
            ]);
            $message = 'Lote creado correctamente.';
        }

        session()->flash('success', $message);
        $this->closeLotModal();
        $this->refreshWorkOrder();
    }

    public function confirmDeleteLot(int $lotId): void
    {
        $this->editingLotId = $lotId;
        $this->showDeleteLotConfirm = true;
    }

    public function deleteLot(): void
    {
        $lot = Lot::findOrFail($this->editingLotId);

        if (!$lot->canBeDeleted()) {
            session()->flash('error', 'Este lote no puede ser eliminado en su estado actual.');
            $this->showDeleteLotConfirm = false;
            $this->editingLotId = null;
            return;
        }

        $lot->kits()->detach();
        $lot->weighings()->delete();
        $lot->delete();

        session()->flash('success', 'Lote eliminado correctamente.');
        $this->showDeleteLotConfirm = false;
        $this->editingLotId = null;
        $this->refreshWorkOrder();
    }

    public function closeLotModal(): void
    {
        $this->showLotModal = false;
        $this->resetLotForm();
    }

    private function resetLotForm(): void
    {
        $this->editingLotId = null;
        $this->lotNumber = '';
        $this->lotQuantity = 0;
        $this->lotStatus = 'pending';
        $this->lotDescription = '';
        $this->lotComments = '';
        $this->resetErrorBag();
    }

    // ===============================================
    // KIT CRUD
    // ===============================================

    public function openCreateKitModal(): void
    {
        $this->resetKitForm();
        $this->showKitModal = true;
    }

    public function openEditKitModal(int $kitId): void
    {
        $kit = Kit::with('lots')->findOrFail($kitId);
        $this->editingKitId = $kitId;
        $this->kitStatus = $kit->status;
        $this->selectedLots = $kit->lots->pluck('id')->toArray();
        $this->kitValidationNotes = $kit->validation_notes ?? '';
        $this->showKitModal = true;
    }

    public function saveKit(): void
    {
        $rules = [
            'kitStatus' => 'required|in:preparing,ready,released,in_assembly,rejected',
            'kitValidationNotes' => 'nullable|string|max:500',
        ];
        $messages = [];

        if (!$this->editingKitId) {
            $rules['selectedLots'] = 'required|array|min:1';
            $rules['selectedLots.*'] = 'exists:lots,id';
            $messages['selectedLots.required'] = 'Debe seleccionar al menos un lote.';
            $messages['selectedLots.min'] = 'Debe seleccionar al menos un lote.';
        }

        $this->validate($rules, $messages);

        if ($this->editingKitId) {
            $kit = Kit::findOrFail($this->editingKitId);
            $updateData = [
                'status' => $this->kitStatus,
                'validation_notes' => $this->kitValidationNotes,
            ];
            if ($this->kitStatus === 'released' && $kit->status !== 'released') {
                $updateData['released_by'] = auth()->id();
            }
            $kit->update($updateData);

            // Sync lots
            if (!empty($this->selectedLots)) {
                $kit->lots()->sync($this->selectedLots);
            }

            $message = 'Kit actualizado correctamente.';
        } else {
            $kit = Kit::create([
                'work_order_id' => $this->workOrder->id,
                'kit_number' => Kit::generateKitNumber($this->workOrder->id),
                'status' => $this->kitStatus,
                'validation_notes' => $this->kitValidationNotes,
                'prepared_by' => auth()->id(),
            ]);
            $kit->lots()->attach($this->selectedLots);
            $message = 'Kit creado correctamente.';
        }

        session()->flash('success', $message);
        $this->closeKitModal();
        $this->refreshWorkOrder();
    }

    public function confirmDeleteKit(int $kitId): void
    {
        $this->editingKitId = $kitId;
        $this->showDeleteKitConfirm = true;
    }

    public function deleteKit(): void
    {
        $kit = Kit::findOrFail($this->editingKitId);

        if (!$kit->canBeDeleted()) {
            session()->flash('error', 'Este kit no puede ser eliminado en su estado actual.');
            $this->showDeleteKitConfirm = false;
            $this->editingKitId = null;
            return;
        }

        $kit->lots()->detach();
        $kit->delete();

        session()->flash('success', 'Kit eliminado correctamente.');
        $this->showDeleteKitConfirm = false;
        $this->editingKitId = null;
        $this->refreshWorkOrder();
    }

    public function closeKitModal(): void
    {
        $this->showKitModal = false;
        $this->resetKitForm();
    }

    private function resetKitForm(): void
    {
        $this->editingKitId = null;
        $this->kitStatus = 'preparing';
        $this->selectedLots = [];
        $this->kitValidationNotes = '';
        $this->resetErrorBag();
    }

    // ===============================================
    // WEIGHING CRUD
    // ===============================================

    public function openCreateWeighingModal(?int $lotId = null): void
    {
        $this->resetWeighingForm();
        if ($lotId) {
            $this->weighingLotId = $lotId;
            $this->calculateRemaining();
        }
        $this->weighedAt = now()->format('Y-m-d\TH:i');
        $this->showWeighingModal = true;
    }

    public function openEditWeighingModal(int $weighingId): void
    {
        $weighing = Weighing::findOrFail($weighingId);
        $this->editingWeighingId = $weighingId;
        $this->weighingLotId = $weighing->lot_id;
        $this->weighingKitId = $weighing->kit_id;
        $this->goodPieces = $weighing->good_pieces;
        $this->badPieces = $weighing->bad_pieces;
        $this->weighedAt = $weighing->weighed_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->weighingComments = $weighing->comments ?? '';
        $this->calculateRemaining();
        $this->showWeighingModal = true;
    }

    public function updatedWeighingLotId(): void
    {
        $this->calculateRemaining();
    }

    private function calculateRemaining(): void
    {
        if (!$this->weighingLotId) {
            $this->remainingPieces = 0;
            return;
        }

        $lot = Lot::find($this->weighingLotId);
        if (!$lot) {
            $this->remainingPieces = 0;
            return;
        }

        $excludeId = $this->editingWeighingId ?? 0;
        $alreadyWeighed = Weighing::where('lot_id', $this->weighingLotId)
            ->where('id', '!=', $excludeId)
            ->selectRaw('COALESCE(SUM(good_pieces), 0) + COALESCE(SUM(bad_pieces), 0) as total')
            ->value('total');

        $this->remainingPieces = max(0, $lot->quantity - $alreadyWeighed);
    }

    public function saveWeighing(): void
    {
        $this->validate([
            'weighingLotId' => 'required|exists:lots,id',
            'weighingKitId' => 'nullable|exists:kits,id',
            'goodPieces' => 'required|integer|min:0',
            'badPieces' => 'required|integer|min:0',
            'weighedAt' => 'required|date',
        ], [
            'weighingLotId.required' => 'Debe seleccionar un lote.',
            'goodPieces.required' => 'Las piezas buenas son obligatorias.',
            'badPieces.required' => 'Las piezas malas son obligatorias.',
            'weighedAt.required' => 'La fecha es obligatoria.',
        ]);

        $total = $this->goodPieces + $this->badPieces;
        if ($total <= 0) {
            $this->addError('goodPieces', 'Debe registrar al menos 1 pieza (buena o mala).');
            return;
        }

        // Calculate remaining and validate
        $this->calculateRemaining();
        if ($total > $this->remainingPieces) {
            $this->addError('goodPieces', 'La suma de piezas (' . number_format($total) . ') excede el pendiente de pesar (' . number_format($this->remainingPieces) . ').');
            return;
        }

        if ($this->editingWeighingId) {
            $weighing = Weighing::findOrFail($this->editingWeighingId);
            $weighing->update([
                'lot_id' => $this->weighingLotId,
                'kit_id' => $this->weighingKitId ?: null,
                'good_pieces' => $this->goodPieces,
                'bad_pieces' => $this->badPieces,
                'weighed_at' => $this->weighedAt,
                'comments' => $this->weighingComments,
            ]);
            $message = 'Pesada actualizada correctamente.';
        } else {
            Weighing::create([
                'lot_id' => $this->weighingLotId,
                'kit_id' => $this->weighingKitId ?: null,
                'good_pieces' => $this->goodPieces,
                'bad_pieces' => $this->badPieces,
                'weighed_at' => $this->weighedAt,
                'weighed_by' => auth()->id(),
                'comments' => $this->weighingComments,
            ]);
            $message = 'Pesada registrada correctamente.';
        }

        session()->flash('success', $message);
        $this->closeWeighingModal();
        $this->refreshWorkOrder();
    }

    public function confirmDeleteWeighing(int $weighingId): void
    {
        $this->editingWeighingId = $weighingId;
        $this->showDeleteWeighingConfirm = true;
    }

    public function deleteWeighing(): void
    {
        $weighing = Weighing::findOrFail($this->editingWeighingId);
        $weighing->delete();

        session()->flash('success', 'Pesada eliminada correctamente.');
        $this->showDeleteWeighingConfirm = false;
        $this->editingWeighingId = null;
        $this->refreshWorkOrder();
    }

    public function closeWeighingModal(): void
    {
        $this->showWeighingModal = false;
        $this->resetWeighingForm();
    }

    private function resetWeighingForm(): void
    {
        $this->editingWeighingId = null;
        $this->weighingLotId = null;
        $this->weighingKitId = null;
        $this->goodPieces = 0;
        $this->badPieces = 0;
        $this->weighedAt = '';
        $this->weighingComments = '';
        $this->remainingPieces = 0;
        $this->resetErrorBag();
    }

    // ===============================================
    // RENDER
    // ===============================================

    public function render()
    {
        $signatures = [];
        if ($this->workOrder->purchaseOrder) {
            $signatures = $this->signatureService->getDocumentSignatures($this->workOrder->purchaseOrder);
        }

        // Gather all weighings for this WO through lots
        $lotIds = $this->workOrder->lots->pluck('id');
        $weighings = Weighing::whereIn('lot_id', $lotIds)
            ->with(['lot', 'kit', 'weighedBy'])
            ->latest('weighed_at')
            ->get();

        return view('livewire.admin.work-orders.wo-show', [
            'statuses' => StatusWO::all(),
            'signatures' => $signatures,
            'weighings' => $weighings,
        ]);
    }
}
