<?php

namespace App\Livewire\Admin\SentLists;

use App\Models\SentList;
use App\Models\Weighing;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SentListProductionView extends Component
{
    public SentList $sentList;

    // Add weighing modal
    public bool $showWeighingModal = false;
    public ?int $weighingLotId = null;
    public int $weighingQuantity = 0;
    public ?int $weighingKitId = null;
    public string $weighingComments = '';
    public string $weighingAt = '';

    // Send to quality modal
    public bool $showSendModal = false;
    public string $sendNotes = '';

    public function mount(SentList $sentList): void
    {
        $this->sentList   = $sentList;
        $this->weighingAt = now()->format('Y-m-d\TH:i');
    }

    public function openWeighingModal(int $lotId): void
    {
        $this->weighingLotId      = $lotId;
        $this->weighingQuantity   = 0;
        $this->weighingKitId      = null;
        $this->weighingComments   = '';
        $this->weighingAt         = now()->format('Y-m-d\TH:i');
        $this->showWeighingModal  = true;
    }

    public function openKitWeighingModal(int $lotId, int $kitId): void
    {
        $this->weighingLotId     = $lotId;
        $this->weighingQuantity  = 0;
        $this->weighingKitId     = $kitId;
        $this->weighingComments  = '';
        $this->weighingAt        = now()->format('Y-m-d\TH:i');
        $this->showWeighingModal = true;
    }

    public function saveWeighing(): void
    {
        $this->validate([
            'weighingQuantity' => 'required|integer|min:1',
            'weighingAt'       => 'required|date',
            'weighingComments' => 'nullable|string|max:500',
        ], [
            'weighingQuantity.required' => 'La cantidad es obligatoria.',
            'weighingQuantity.min'      => 'La cantidad debe ser mayor a 0.',
            'weighingAt.required'       => 'La fecha/hora es obligatoria.',
        ]);

        $lot            = \App\Models\Lot::findOrFail($this->weighingLotId);
        $alreadyWeighed = $lot->weighings()->sum('quantity');
        $remaining      = $lot->quantity - $alreadyWeighed;

        if ($this->weighingQuantity > $remaining) {
            $this->addError('weighingQuantity', "Solo quedan {$remaining} pieza(s) disponibles en este lote (meta: {$lot->quantity}, ya pesadas: {$alreadyWeighed}).");
            return;
        }

        Weighing::create([
            'lot_id'      => $this->weighingLotId,
            'kit_id'      => $this->weighingKitId ?: null,
            'quantity'    => $this->weighingQuantity,
            'good_pieces' => $this->weighingQuantity,
            'bad_pieces'  => 0,
            'weighed_at'  => $this->weighingAt,
            'weighed_by'  => Auth::id(),
            'comments'    => $this->weighingComments ?: null,
        ]);

        // Update lot status based on progress
        if ($lot->status === \App\Models\Lot::STATUS_PENDING) {
            $lot->update(['status' => \App\Models\Lot::STATUS_IN_PROGRESS]);
        }

        if ($alreadyWeighed + $this->weighingQuantity >= $lot->quantity) {
            $lot->update(['status' => \App\Models\Lot::STATUS_COMPLETED]);
        }

        $this->showWeighingModal = false;
        $this->weighingLotId     = null;
        $this->weighingQuantity  = 0;
        $this->weighingKitId     = null;
        $this->weighingComments  = '';
        $this->sentList->refresh();
        session()->flash('message', 'Pesada registrada correctamente.');
    }

    public function closeWeighingModal(): void
    {
        $this->showWeighingModal = false;
        $this->weighingLotId     = null;
        $this->weighingQuantity  = 0;
        $this->weighingKitId     = null;
        $this->weighingComments  = '';
    }

    public function deleteWeighing(int $weighingId): void
    {
        Weighing::findOrFail($weighingId)->delete();
        $this->sentList->refresh();
        session()->flash('message', 'Pesada eliminada.');
    }

    public function openSendModal(): void
    {
        $this->sendNotes    = '';
        $this->showSendModal = true;
    }

    public function closeSendModal(): void
    {
        $this->showSendModal = false;
        $this->sendNotes     = '';
    }

    public function sendToQuality(): void
    {
        if (!empty($this->sendNotes)) {
            $this->sentList->update([
                'notes' => trim(($this->sentList->notes ?? '') . "\n[Producción " . now()->format('d/m/Y H:i') . '] ' . $this->sendNotes),
            ]);
        }

        $this->sentList->moveToNextDepartment(Auth::id());
        session()->flash('message', 'Enviado a Calidad correctamente.');
        $this->redirect(route('admin.sent-lists.show', $this->sentList));
    }

    public function markLotComplete(int $lotId): void
    {
        $lot = \App\Models\Lot::findOrFail($lotId);
        $lot->update(['status' => \App\Models\Lot::STATUS_COMPLETED]);
        $this->sentList->refresh();
        session()->flash('message', "Lote {$lot->lot_number} marcado como completado.");
    }

    public function reopenLot(int $lotId): void
    {
        $lot = \App\Models\Lot::findOrFail($lotId);
        $lot->update(['status' => \App\Models\Lot::STATUS_IN_PROGRESS]);
        $this->sentList->refresh();
        session()->flash('message', "Lote {$lot->lot_number} reabierto para correcciones.");
    }

    public function render()
    {
        $this->sentList->load([
            'purchaseOrders.workOrder.purchaseOrder.part',
            'purchaseOrders.workOrder.lots.weighings.weighedBy',
            'purchaseOrders.workOrder.kits',
            'workOrders.purchaseOrder.part',
            'workOrders.lots.weighings.weighedBy',
            'workOrders.kits',
        ]);

        $workOrders = $this->sentList->getEffectiveWorkOrders();

        return view('livewire.admin.sent-lists.production-view', compact('workOrders'));
    }
}
