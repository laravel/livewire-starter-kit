<?php

namespace App\Livewire\Admin\Lots;

use App\Models\Lot;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithPagination;

class LotList extends Component
{
    use WithPagination;

    public ?int $workOrderId = null;
    public ?WorkOrder $workOrder = null;
    public string $search = '';
    public string $filterStatus = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public bool $confirmingDeletion = false;
    public ?int $lotToDelete = null;
    
    // Modal para cambiar estado
    public bool $showStatusModal = false;
    public ?int $selectedLotId = null;
    public string $newStatus = '';

    public function mount(?int $workOrderId = null): void
    {
        $this->workOrderId = $workOrderId;
        if ($workOrderId) {
            $this->workOrder = WorkOrder::with('purchaseOrder.part')->find($workOrderId);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function confirmDeletion(int $id): void
    {
        $this->lotToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        if ($this->lotToDelete) {
            $lot = Lot::find($this->lotToDelete);
            if ($lot && ($lot->canBeDeleted() || $lot->status === Lot::STATUS_COMPLETED)) {
                $lot->delete();
                session()->flash('message', 'Lote eliminado correctamente.');
            } else {
                session()->flash('error', 'No se puede eliminar este lote.');
            }
        }
        $this->confirmingDeletion = false;
        $this->lotToDelete = null;
    }

    public function startLot(int $id): void
    {
        $lot = Lot::find($id);
        if ($lot && $lot->canBeStarted()) {
            $lot->update(['status' => Lot::STATUS_IN_PROGRESS]);
            session()->flash('message', 'Lote iniciado.');
        }
    }

    public function completeLot(int $id): void
    {
        $lot = Lot::find($id);
        if ($lot && $lot->canBeCompleted()) {
            $lot->update(['status' => Lot::STATUS_COMPLETED]);
            session()->flash('message', 'Lote completado.');
        }
    }

    public function cancelLot(int $id): void
    {
        $lot = Lot::find($id);
        if ($lot && $lot->canBeCancelled()) {
            $lot->update(['status' => Lot::STATUS_CANCELLED]);
            session()->flash('message', 'Lote cancelado.');
        }
    }

    public function openStatusModal(int $id): void
    {
        $lot = Lot::find($id);
        if ($lot) {
            $this->selectedLotId = $id;
            $this->newStatus = $lot->status;
            $this->showStatusModal = true;
        }
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->selectedLotId = null;
        $this->newStatus = '';
    }

    public function setNewStatus(string $status): void
    {
        $this->newStatus = $status;
    }

    public function updateLotStatus(): void
    {
        if (!$this->selectedLotId || !$this->newStatus) {
            return;
        }

        $lot = Lot::find($this->selectedLotId);
        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            $this->closeStatusModal();
            return;
        }

        $lot->update(['status' => $this->newStatus]);
        
        $statusLabels = Lot::getStatuses();
        session()->flash('message', "Estado del lote actualizado a: {$statusLabels[$this->newStatus]}");
        
        $this->closeStatusModal();
    }

    public function render()
    {
        $query = Lot::with(['workOrder.purchaseOrder.part', 'kits'])
            ->search($this->search)
            ->when($this->filterStatus, fn($q) => $q->status($this->filterStatus))
            ->when($this->workOrderId, fn($q) => $q->where('work_order_id', $this->workOrderId))
            ->orderBy($this->sortField, $this->sortDirection);

        $lots = $query->paginate($this->perPage);

        return view('livewire.admin.lots.lot-list', [
            'lots' => $lots,
            'statuses' => Lot::getStatuses(),
        ]);
    }
}
