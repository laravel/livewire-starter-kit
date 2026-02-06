<?php

namespace App\Livewire\Admin\Lots;

use App\Models\Lot;
use Livewire\Component;

class LotShow extends Component
{
    public Lot $lot;

    // Modal para cambiar estado
    public bool $showStatusModal = false;
    public string $newStatus = '';

    public function mount(Lot $lot): void
    {
        $this->lot = $lot->load(['workOrder.purchaseOrder.part', 'kits']);
    }

    public function startLot(): void
    {
        if ($this->lot->canBeStarted()) {
            $this->lot->update(['status' => Lot::STATUS_IN_PROGRESS]);
            session()->flash('message', 'Lote iniciado.');
        }
    }

    public function completeLot(): void
    {
        if ($this->lot->canBeCompleted()) {
            $this->lot->update(['status' => Lot::STATUS_COMPLETED]);
            session()->flash('message', 'Lote completado. Las piezas enviadas de la WO han sido actualizadas.');
        }
    }

    public function cancelLot(): void
    {
        if ($this->lot->canBeCancelled()) {
            $this->lot->update(['status' => Lot::STATUS_CANCELLED]);
            session()->flash('message', 'Lote cancelado.');
        }
    }

    public function openStatusModal(): void
    {
        $this->newStatus = $this->lot->status;
        $this->showStatusModal = true;
    }

    public function closeStatusModal(): void
    {
        $this->showStatusModal = false;
        $this->newStatus = '';
    }

    public function setNewStatus(string $status): void
    {
        $this->newStatus = $status;
    }

    public function updateLotStatus(): void
    {
        if (!$this->newStatus) {
            return;
        }

        $this->lot->update(['status' => $this->newStatus]);
        $this->lot->refresh();
        
        $statusLabels = Lot::getStatuses();
        session()->flash('message', "Estado del lote actualizado a: {$statusLabels[$this->newStatus]}");
        
        $this->closeStatusModal();
    }

    public function render()
    {
        return view('livewire.admin.lots.lot-show');
    }
}
