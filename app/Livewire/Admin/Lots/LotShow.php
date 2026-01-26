<?php

namespace App\Livewire\Admin\Lots;

use App\Models\Lot;
use Livewire\Component;

class LotShow extends Component
{
    public Lot $lot;

    public function mount(Lot $lot): void
    {
        $this->lot = $lot->load(['workOrder.purchaseOrder.part']);
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

    public function render()
    {
        return view('livewire.admin.lots.lot-show');
    }
}
