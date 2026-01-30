<?php

namespace App\Livewire\Admin\Lots;

use App\Models\Lot;
use Livewire\Component;

class LotEdit extends Component
{
    public Lot $lot;
    public string $lot_number = '';
    public int $quantity = 0;

    protected function rules(): array
    {
        return [
            'lot_number' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function mount(Lot $lot): void
    {
        $this->lot = $lot->load(['workOrder.purchaseOrder.part']);
        $this->lot_number = $lot->lot_number;
        $this->quantity = $lot->quantity;
    }

    public function save(): void
    {
        $this->validate();

        $this->lot->update([
            'lot_number' => $this->lot_number,
            'quantity' => $this->quantity,
        ]);

        session()->flash('message', 'Lote actualizado correctamente.');
        $this->redirect(route('admin.lots.show', $this->lot), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.lots.lot-edit');
    }
}
