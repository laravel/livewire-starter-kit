<?php

namespace App\Livewire\Admin\Kits;

use App\Models\Kit;
use Livewire\Component;
use Livewire\WithPagination;

class KitList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public bool $confirmingDeletion = false;
    public ?int $kitToDelete = null;
    
    // Modal para cambiar estado
    public bool $showStatusModal = false;
    public ?int $selectedKitId = null;
    public string $newStatus = '';

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
        $this->kitToDelete = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        if ($this->kitToDelete) {
            $kit = Kit::find($this->kitToDelete);
            if ($kit) {
                // Detach lots first
                $kit->lots()->detach();
                $kit->delete();
                session()->flash('message', 'Kit eliminado correctamente.');
            } else {
                session()->flash('error', 'Kit no encontrado.');
            }
        }
        $this->confirmingDeletion = false;
        $this->kitToDelete = null;
    }

    public function markAsReady(int $id): void
    {
        $kit = Kit::find($id);
        if ($kit && $kit->canBeReady()) {
            $kit->update([
                'status' => Kit::STATUS_READY,
                'prepared_by' => auth()->id(),
            ]);
            session()->flash('message', 'Kit marcado como listo.');
        }
    }

    public function release(int $id): void
    {
        $kit = Kit::find($id);
        if ($kit && $kit->canBeReleased()) {
            $kit->update([
                'status' => Kit::STATUS_RELEASED,
                'released_by' => auth()->id(),
            ]);
            session()->flash('message', 'Kit liberado correctamente.');
        } else {
            session()->flash('error', 'El kit debe estar validado antes de ser liberado.');
        }
    }

    public function startAssembly(int $id): void
    {
        $kit = Kit::find($id);
        if ($kit && $kit->canStartAssembly()) {
            $kit->update(['status' => Kit::STATUS_IN_ASSEMBLY]);
            session()->flash('message', 'Kit en ensamble.');
        }
    }

    public function openStatusModal(int $id): void
    {
        $kit = Kit::find($id);
        if ($kit) {
            $this->selectedKitId = $id;
            $this->newStatus = $kit->status;
            $this->showStatusModal = true;
        }
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->selectedKitId = null;
        $this->newStatus = '';
    }

    public function setNewStatus(string $status): void
    {
        $this->newStatus = $status;
    }

    public function updateKitStatus(): void
    {
        if (!$this->selectedKitId || !$this->newStatus) {
            return;
        }

        $kit = Kit::find($this->selectedKitId);
        if (!$kit) {
            session()->flash('error', 'Kit no encontrado.');
            $this->closeStatusModal();
            return;
        }

        $kit->update(['status' => $this->newStatus]);
        
        $statusLabels = Kit::getStatuses();
        session()->flash('message', "Estado del kit actualizado a: {$statusLabels[$this->newStatus]}");
        
        $this->closeStatusModal();
    }

    public function rejectKit(int $id): void
    {
        $kit = Kit::find($id);
        if ($kit) {
            $kit->update(['status' => Kit::STATUS_REJECTED]);
            session()->flash('message', 'Kit rechazado.');
        }
    }

    public function render()
    {
        $kits = Kit::with(['workOrder.purchaseOrder.part', 'preparedBy', 'releasedBy'])
            ->search($this->search)
            ->when($this->filterStatus, fn($q) => $q->status($this->filterStatus))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.kits.kit-list', [
            'kits' => $kits,
            'statuses' => Kit::getStatuses(),
        ]);
    }
}
