<?php

namespace App\Livewire\Admin\PackingSlips;

use App\Models\PackingSlip;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PackingSlipShow extends Component
{
    public PackingSlip $packingSlip;

    public function mount(PackingSlip $packingSlip): void
    {
        $this->packingSlip = $packingSlip->load(['creator', 'shipper', 'items.lot.workOrder.purchaseOrder.part']);
    }

    public function confirm(): void
    {
        if (!$this->packingSlip->isDraft()) {
            session()->flash('flash.banner', 'Solo se pueden confirmar Packing Slips en estado Borrador.');
            session()->flash('flash.bannerStyle', 'danger');
            return;
        }

        $this->packingSlip->update([
            'status' => PackingSlip::STATUS_CONFIRMED,
        ]);

        $this->packingSlip->refresh();

        session()->flash('flash.banner', "Packing Slip {$this->packingSlip->ps_number} confirmado correctamente.");
        session()->flash('flash.bannerStyle', 'success');
    }

    public function markAsShipped(): void
    {
        if (!$this->packingSlip->isConfirmed()) {
            session()->flash('flash.banner', 'Solo se pueden despachar Packing Slips en estado Confirmado.');
            session()->flash('flash.bannerStyle', 'danger');
            return;
        }

        $this->packingSlip->update([
            'status'     => PackingSlip::STATUS_SHIPPED,
            'shipped_at' => now(),
            'shipped_by' => Auth::id(),
        ]);

        $this->packingSlip->refresh()->load(['creator', 'shipper', 'items.lot.workOrder.purchaseOrder.part']);

        session()->flash('flash.banner', "Packing Slip {$this->packingSlip->ps_number} marcado como Despachado.");
        session()->flash('flash.bannerStyle', 'success');
    }

    public function updateItemDate(int $itemId, string $value): void
    {
        if ($this->packingSlip->isShipped()) return;

        $item = $this->packingSlip->items()->findOrFail($itemId);
        $item->update(['lot_date_code' => trim($value) ?: null]);
        $this->packingSlip->load(['creator', 'shipper', 'items.lot.workOrder.purchaseOrder.part']);
    }

    public function updateItemLabelSpec(int $itemId, string $value): void
    {
        if ($this->packingSlip->isShipped()) return;

        $item = $this->packingSlip->items()->findOrFail($itemId);
        $item->update(['label_spec' => trim($value) ?: null]);
        $this->packingSlip->load(['creator', 'shipper', 'items.lot.workOrder.purchaseOrder.part']);
    }

    public function render()
    {
        // Agrupar items por PO para mostrar subtotales por grupo en la vista,
        // replicando la estructura del Excel FPL-10 (columna C agrupada con subtotal).
        $itemsGroupedByPo = $this->packingSlip->items
            ->groupBy(fn ($item) => $item->lot?->workOrder?->purchaseOrder?->po_number ?? 'Sin PO');

        return view('livewire.admin.packing-slips.packing-slip-show', [
            'itemsGroupedByPo' => $itemsGroupedByPo,
        ]);
    }
}
