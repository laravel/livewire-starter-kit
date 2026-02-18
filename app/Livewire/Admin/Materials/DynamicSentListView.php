<?php

namespace App\Livewire\Admin\Materials;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use App\Models\WorkOrder;
use App\Models\Lot;
use App\Models\Kit;
use App\Models\StatusWO;
use Illuminate\Support\Collection;

class DynamicSentListView extends Component
{
    use WithPagination;

    // Search and filters
    public string $searchTerm = '';
    public string $filterStatus = '';

    // Modal states
    public bool $showCreateLotModal = false;
    public bool $showEditLotModal = false;
    public bool $showDeleteLotConfirm = false;
    public bool $showCreateKitModal = false;
    public bool $showEditKitModal = false;
    public bool $showDeleteKitConfirm = false;
    public bool $showEditWOStatusModal = false;

    // Selected entities
    public ?int $selectedLotId = null;
    public ?int $selectedKitId = null;
    public ?int $selectedWorkOrderId = null;

    // Form data for WO status editing
    public ?int $selectedWOStatusId = null;
    public string $woStatusAction = '';

    // Form data for lot creation
    public string $newLotNumber = '';
    public int $newLotQuantity = 0;

    // Form data for lot editing
    public string $lotStatus = '';
    public string $lotComments = '';
    public string $lotDescription = '';
    public int $lotQuantity = 0;

    // Form data for kit creation
    public string $kitStatus = 'preparing';
    public array $selectedLots = [];
    public string $kitValidationNotes = '';

    // Form data for kit editing
    public string $editKitStatus = '';
    public string $editKitValidationNotes = '';

    // ===============================================
    // COMPUTED PROPERTIES
    // ===============================================

    /**
     * Get the selected lot.
     */
    #[Computed]
    public function selectedLot(): ?Lot
    {
        return $this->selectedLotId ? Lot::find($this->selectedLotId) : null;
    }

    /**
     * Get the selected kit.
     */
    #[Computed]
    public function selectedKit(): ?Kit
    {
        return $this->selectedKitId ? Kit::with(['lots', 'preparedBy', 'releasedBy'])->find($this->selectedKitId) : null;
    }

    /**
     * Get available lots for kit creation.
     */
    #[Computed]
    public function availableLotsForKit(): Collection
    {
        if (!$this->selectedWorkOrderId) {
            return collect([]);
        }

        return Lot::where('work_order_id', $this->selectedWorkOrderId)
            ->whereIn('status', ['pending', 'in_progress', 'completed'])
            ->get();
    }

    /**
     * Get the selected work order.
     */
    #[Computed]
    public function selectedWorkOrder(): ?WorkOrder
    {
        return $this->selectedWorkOrderId ? WorkOrder::with(['status', 'purchaseOrder.part'])->find($this->selectedWorkOrderId) : null;
    }

    /**
     * Get all available WO statuses.
     */
    #[Computed]
    public function woStatuses(): Collection
    {
        return StatusWO::orderBy('name')->get();
    }

    // ===============================================
    // LIFECYCLE HOOKS
    // ===============================================

    /**
     * React to search term changes.
     */
    public function updatedSearchTerm(): void
    {
        $this->resetPage();
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->searchTerm = '';
        $this->resetPage();
    }

    // ===============================================
    // LOT MANAGEMENT - CREATE
    // ===============================================

    /**
     * Open the create lot modal.
     */
    public function openCreateLotModal(int $workOrderId): void
    {
        $this->selectedWorkOrderId = $workOrderId;
        $this->newLotNumber = '';
        $this->newLotQuantity = 0;
        $this->showCreateLotModal = true;
    }

    /**
     * Close the create lot modal.
     */
    public function closeCreateLotModal(): void
    {
        $this->showCreateLotModal = false;
        $this->selectedWorkOrderId = null;
        $this->newLotNumber = '';
        $this->newLotQuantity = 0;
    }

    /**
     * Create a new lot.
     */
    public function createLot(): void
    {
        $this->validate([
            'newLotNumber' => 'required|string|max:255',
            'newLotQuantity' => 'required|integer|min:1',
        ], [
            'newLotNumber.required' => 'El número de lote es obligatorio.',
            'newLotQuantity.required' => 'La cantidad es obligatoria.',
            'newLotQuantity.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        $workOrder = WorkOrder::findOrFail($this->selectedWorkOrderId);

        // Validar que la suma de lotes no sobrepase la Cant. WO
        $currentTotal = $workOrder->lots()->sum('quantity');
        if (($currentTotal + $this->newLotQuantity) > $workOrder->original_quantity) {
            $available = $workOrder->original_quantity - $currentTotal;
            $this->addError('newLotQuantity', 'La suma de lotes sobrepasaría la Cant. WO (' . number_format($workOrder->original_quantity) . '). Máximo disponible: ' . number_format($available));
            return;
        }

        $part = $workOrder->purchaseOrder->part;

        Lot::create([
            'work_order_id' => $workOrder->id,
            'lot_number' => $this->newLotNumber,
            'quantity' => $this->newLotQuantity,
            'description' => $part->description ?? '',
            'status' => Lot::STATUS_PENDING,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Lote creado correctamente'
        ]);

        $this->closeCreateLotModal();
    }

    // ===============================================
    // LOT MANAGEMENT - EDIT
    // ===============================================

    /**
     * Open the edit lot modal.
     */
    public function openEditLotModal(int $lotId): void
    {
        $this->selectedLotId = $lotId;
        $lot = $this->selectedLot;

        if ($lot) {
            $this->lotStatus = $lot->status;
            $this->lotDescription = $lot->description ?? '';
            $this->lotComments = $lot->comments ?? '';
            $this->lotQuantity = $lot->quantity;
            $this->showEditLotModal = true;
        }
    }

    /**
     * Close the edit lot modal.
     */
    public function closeEditLotModal(): void
    {
        $this->showEditLotModal = false;
        $this->resetLotForm();
    }

    /**
     * Update the lot.
     */
    public function updateLot(): void
    {
        $this->validate([
            'lotStatus' => 'required|in:pending,in_progress,completed,cancelled',
            'lotDescription' => 'nullable|string|max:255',
            'lotComments' => 'nullable|string|max:500',
            'lotQuantity' => 'required|integer|min:1',
        ]);

        $lot = Lot::findOrFail($this->selectedLotId);

        // Validar que la suma de lotes no sobrepase la Cant. WO
        $workOrder = $lot->workOrder;
        $otherLotsTotal = $workOrder->lots()->where('id', '!=', $lot->id)->sum('quantity');
        if (($otherLotsTotal + $this->lotQuantity) > $workOrder->original_quantity) {
            $available = $workOrder->original_quantity - $otherLotsTotal;
            $this->addError('lotQuantity', 'La suma de lotes sobrepasaría la Cant. WO (' . number_format($workOrder->original_quantity) . '). Máximo disponible: ' . number_format($available));
            return;
        }

        $lot->update([
            'status' => $this->lotStatus,
            'description' => $this->lotDescription,
            'comments' => $this->lotComments,
            'quantity' => $this->lotQuantity,
        ]);

        $this->dispatch('lot-updated');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Lote actualizado correctamente'
        ]);

        $this->closeEditLotModal();
    }

    // ===============================================
    // LOT MANAGEMENT - DELETE
    // ===============================================

    /**
     * Confirm lot deletion.
     */
    public function confirmDeleteLot(int $lotId): void
    {
        $this->selectedLotId = $lotId;
        $this->showDeleteLotConfirm = true;
    }

    /**
     * Delete the lot.
     */
    public function deleteLot(): void
    {
        $lot = Lot::findOrFail($this->selectedLotId);

        if (!$lot->canBeDeleted()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Este lote no puede ser eliminado en su estado actual.'
            ]);
            return;
        }

        // Desasociar kits antes de eliminar
        $lot->kits()->detach();
        $lot->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Lote eliminado correctamente'
        ]);

        $this->cancelDeleteLot();
    }

    /**
     * Cancel lot deletion.
     */
    public function cancelDeleteLot(): void
    {
        $this->showDeleteLotConfirm = false;
        $this->selectedLotId = null;
    }

    // ===============================================
    // KIT MANAGEMENT - CREATE
    // ===============================================

    /**
     * Open the create kit modal.
     */
    public function openCreateKitModal(int $workOrderId): void
    {
        $this->selectedWorkOrderId = $workOrderId;
        $this->showCreateKitModal = true;
    }

    /**
     * Close the create kit modal.
     */
    public function closeCreateKitModal(): void
    {
        $this->showCreateKitModal = false;
        $this->resetKitForm();
    }

    /**
     * Create a new kit.
     */
    public function createKit(): void
    {
        $this->validate([
            'kitStatus' => 'required|in:preparing,ready',
            'selectedLots' => 'required|array|min:1',
            'selectedLots.*' => 'exists:lots,id',
            'kitValidationNotes' => 'nullable|string|max:500',
        ], [
            'selectedLots.required' => 'Debe seleccionar al menos un lote para crear el kit.',
            'selectedLots.min' => 'Debe seleccionar al menos un lote para crear el kit.',
        ]);

        $workOrder = WorkOrder::findOrFail($this->selectedWorkOrderId);

        // Create kit
        $kit = Kit::create([
            'work_order_id' => $workOrder->id,
            'kit_number' => Kit::generateKitNumber($workOrder->id),
            'status' => $this->kitStatus,
            'validation_notes' => $this->kitValidationNotes,
            'prepared_by' => auth()->id(),
        ]);

        // Attach lots if any selected
        if (!empty($this->selectedLots)) {
            $kit->lots()->attach($this->selectedLots);
        }

        $this->dispatch('kit-created');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Kit creado correctamente'
        ]);

        $this->closeCreateKitModal();
    }

    // ===============================================
    // KIT MANAGEMENT - EDIT
    // ===============================================

    /**
     * Open the edit kit modal.
     */
    public function openEditKitModal(int $kitId): void
    {
        $this->selectedKitId = $kitId;
        $kit = $this->selectedKit;

        if ($kit) {
            $this->editKitStatus = $kit->status;
            $this->editKitValidationNotes = $kit->validation_notes ?? '';
            $this->showEditKitModal = true;
        }
    }

    /**
     * Close the edit kit modal.
     */
    public function closeEditKitModal(): void
    {
        $this->showEditKitModal = false;
        $this->selectedKitId = null;
        $this->editKitStatus = '';
        $this->editKitValidationNotes = '';
    }

    /**
     * Update the kit.
     */
    public function updateKit(): void
    {
        $this->validate([
            'editKitStatus' => 'required|in:preparing,ready,released,in_assembly,rejected',
            'editKitValidationNotes' => 'nullable|string|max:500',
        ]);

        $kit = Kit::findOrFail($this->selectedKitId);

        // Check if status change is allowed
        if ($kit->status === 'released' && $this->editKitStatus !== 'released' && $this->editKitStatus !== 'in_assembly') {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'No se puede cambiar el estado de un kit liberado a un estado anterior'
            ]);
            return;
        }

        $updateData = [
            'status' => $this->editKitStatus,
            'validation_notes' => $this->editKitValidationNotes,
        ];

        // If changing to released, set released_by
        if ($this->editKitStatus === 'released' && $kit->status !== 'released') {
            $updateData['released_by'] = auth()->id();
        }

        $kit->update($updateData);

        $this->dispatch('kit-updated');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Kit actualizado correctamente'
        ]);

        $this->closeEditKitModal();
    }

    // ===============================================
    // KIT MANAGEMENT - DELETE
    // ===============================================

    /**
     * Confirm kit deletion.
     */
    public function confirmDeleteKit(int $kitId): void
    {
        $this->selectedKitId = $kitId;
        $this->showDeleteKitConfirm = true;
    }

    /**
     * Delete the kit.
     */
    public function deleteKit(): void
    {
        $kit = Kit::findOrFail($this->selectedKitId);

        if (!$kit->canBeDeleted()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Este kit no puede ser eliminado. Solo se pueden eliminar kits en estado "En Preparación".'
            ]);
            return;
        }

        // Detach lots
        $kit->lots()->detach();

        // Delete kit
        $kit->delete();

        $this->dispatch('kit-deleted');
        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Kit eliminado correctamente'
        ]);

        $this->cancelDeleteKit();
    }

    /**
     * Cancel kit deletion.
     */
    public function cancelDeleteKit(): void
    {
        $this->showDeleteKitConfirm = false;
        $this->selectedKitId = null;
    }

    // ===============================================
    // WORK ORDER STATUS MANAGEMENT
    // ===============================================

    /**
     * Open the edit WO status modal.
     */
    public function openEditWOStatusModal(int $workOrderId): void
    {
        $this->selectedWorkOrderId = $workOrderId;
        $workOrder = WorkOrder::find($workOrderId);
        
        if ($workOrder) {
            $this->selectedWOStatusId = $workOrder->status_id;
            $this->showEditWOStatusModal = true;
        }
    }

    /**
     * Close the edit WO status modal.
     */
    public function closeEditWOStatusModal(): void
    {
        $this->showEditWOStatusModal = false;
        $this->selectedWorkOrderId = null;
        $this->selectedWOStatusId = null;
        $this->woStatusAction = '';
    }

    /**
     * Update the work order status.
     */
    public function updateWOStatus(): void
    {
        $this->validate([
            'woStatusAction' => 'required|in:approved,rejected',
        ], [
            'woStatusAction.required' => 'Debes seleccionar Aprobado o Rechazado',
        ]);

        $workOrder = WorkOrder::findOrFail($this->selectedWorkOrderId);
        
        // Find the status by name
        $statusName = $this->woStatusAction === 'approved' ? 'Aprobado' : 'Rechazado';
        $status = StatusWO::where('name', $statusName)->first();
        
        if (!$status) {
            // Create the status if it doesn't exist
            $status = StatusWO::create([
                'name' => $statusName,
                'color' => $this->woStatusAction === 'approved' ? '#22c55e' : '#ef4444',
            ]);
        }
        
        $workOrder->update([
            'status_id' => $status->id,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Estado del Work Order actualizado a ' . $statusName
        ]);

        $this->closeEditWOStatusModal();
    }

    // ===============================================
    // MODAL CLOSE LISTENERS (for Flux modals)
    // ===============================================

    /**
     * Handle edit-lot-modal close event.
     */
    #[On('modal-close:create-lot-modal')]
    public function onCreateLotModalClose(): void
    {
        $this->showCreateLotModal = false;
        $this->selectedWorkOrderId = null;
        $this->newLotNumber = '';
        $this->newLotQuantity = 0;
    }

    #[On('modal-close:edit-lot-modal')]
    public function onEditLotModalClose(): void
    {
        $this->showEditLotModal = false;
        $this->resetLotForm();
    }

    #[On('modal-close:delete-lot-modal')]
    public function onDeleteLotModalClose(): void
    {
        $this->showDeleteLotConfirm = false;
        $this->selectedLotId = null;
    }

    /**
     * Handle create-kit-modal close event.
     */
    #[On('modal-close:create-kit-modal')]
    public function onCreateKitModalClose(): void
    {
        $this->showCreateKitModal = false;
        $this->resetKitForm();
    }

    /**
     * Handle edit-kit-modal close event.
     */
    #[On('modal-close:edit-kit-modal')]
    public function onEditKitModalClose(): void
    {
        $this->showEditKitModal = false;
        $this->selectedKitId = null;
        $this->editKitStatus = '';
        $this->editKitValidationNotes = '';
    }

    /**
     * Handle delete-kit-modal close event.
     */
    #[On('modal-close:delete-kit-modal')]
    public function onDeleteKitModalClose(): void
    {
        $this->showDeleteKitConfirm = false;
        $this->selectedKitId = null;
    }

    // ===============================================
    // HELPER METHODS
    // ===============================================

    /**
     * Reset lot form data.
     */
    private function resetLotForm(): void
    {
        $this->selectedLotId = null;
        $this->lotStatus = '';
        $this->lotDescription = '';
        $this->lotComments = '';
        $this->lotQuantity = 0;
    }

    /**
     * Reset kit form data.
     */
    private function resetKitForm(): void
    {
        $this->selectedWorkOrderId = null;
        $this->kitStatus = 'preparing';
        $this->selectedLots = [];
        $this->kitValidationNotes = '';
    }

    // ===============================================
    // UTILITY METHODS
    // ===============================================

    /**
     * Get lot count for a work order.
     */
    public function getWorkOrderLotCount(int $workOrderId): int
    {
        return Lot::where('work_order_id', $workOrderId)->count();
    }

    /**
     * Get kit count for a work order.
     */
    public function getWorkOrderKitCount(int $workOrderId): int
    {
        return Kit::where('work_order_id', $workOrderId)->count();
    }

    // ===============================================
    // RENDER
    // ===============================================

    /**
     * Render the component.
     */
    public function render()
    {
        $closedStatusIds = \App\Models\StatusWO::whereIn('name', ['Completed', 'Cancelled'])
            ->pluck('id')
            ->toArray();

        $query = WorkOrder::with([
            'purchaseOrder.part',
            'lots',
            'kits.preparedBy',
            'kits.releasedBy',
            'kits.lots'
        ])->whereHas('lots') // Solo WOs que tengan lotes
          ->whereNotIn('status_id', $closedStatusIds); // Excluir cerrados/cancelados

        // Apply search
        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('wo_number', 'like', "%{$this->searchTerm}%")
                  ->orWhereHas('purchaseOrder', function ($poQuery) {
                      $poQuery->where('po_number', 'like', "%{$this->searchTerm}%")
                              ->orWhere('wo', 'like', "%{$this->searchTerm}%");
                  })
                  ->orWhereHas('purchaseOrder.part', function ($partQuery) {
                      $partQuery->where('description', 'like', "%{$this->searchTerm}%")
                               ->orWhere('number', 'like', "%{$this->searchTerm}%");
                  });
            });
        }

        // Apply status filter
        if (!empty($this->filterStatus)) {
            $query->whereHas('lots', function ($q) {
                $q->where('status', $this->filterStatus);
            });
        }

        $workOrders = $query->latest()->paginate(15);

        return view('livewire.admin.materials.dynamic-sent-list-view', [
            'workOrders' => $workOrders,
        ]);
    }
}
