<?php

namespace App\Livewire\Admin\SentLists;

use App\Models\Lot;
use App\Models\QualityWeighing;
use App\Models\SentList;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SentListQualityView extends Component
{
    public SentList $sentList;

    // Quality weighing modal
    public bool $showWeighingModal = false;
    public ?int $weighingLotId = null;
    public int $goodPieces = 0;
    public int $badPieces = 0;
    public ?int $weighingKitId = null;
    public string $weighingComments = '';
    public string $weighingAt = '';

    // Send to packaging modal
    public bool $showSendModal = false;
    public string $sendNotes = '';

    public function mount(SentList $sentList): void
    {
        $this->sentList   = $sentList;
        $this->weighingAt = now()->format('Y-m-d\TH:i');
    }

    public function openWeighingModal(int $lotId): void
    {
        $this->weighingLotId     = $lotId;
        $this->goodPieces        = 0;
        $this->badPieces         = 0;
        $this->weighingKitId     = null;
        $this->weighingComments  = '';
        $this->weighingAt        = now()->format('Y-m-d\TH:i');
        $this->showWeighingModal = true;
    }

    public function saveWeighing(): void
    {
        $this->validate([
            'goodPieces'       => 'required|integer|min:0',
            'badPieces'        => 'required|integer|min:0',
            'weighingAt'       => 'required|date',
            'weighingComments' => 'nullable|string|max:500',
        ], [
            'goodPieces.required' => 'Las piezas buenas son obligatorias.',
            'badPieces.required'  => 'Las piezas malas son obligatorias.',
            'weighingAt.required' => 'La fecha/hora es obligatoria.',
        ]);

        $lot             = Lot::findOrFail($this->weighingLotId);
        $productionTotal = $lot->weighings()->sum('quantity');

        QualityWeighing::create([
            'lot_id'                 => $this->weighingLotId,
            'kit_id'                 => $this->weighingKitId ?: null,
            'production_good_pieces' => $productionTotal,
            'good_pieces'            => $this->goodPieces,
            'bad_pieces'             => $this->badPieces,
            'weighed_at'             => $this->weighingAt,
            'weighed_by'             => Auth::id(),
            'comments'               => $this->weighingComments ?: null,
        ]);

        $this->showWeighingModal = false;
        $this->weighingLotId     = null;
        $this->goodPieces        = 0;
        $this->badPieces         = 0;
        $this->weighingKitId     = null;
        $this->weighingComments  = '';
        $this->sentList->refresh();
        session()->flash('message', 'Pesada de calidad registrada.');
    }

    public function closeWeighingModal(): void
    {
        $this->showWeighingModal = false;
        $this->weighingLotId     = null;
        $this->goodPieces        = 0;
        $this->badPieces         = 0;
        $this->weighingKitId     = null;
        $this->weighingComments  = '';
    }

    public function deleteWeighing(int $id): void
    {
        QualityWeighing::findOrFail($id)->delete();
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

    public function sendToPackaging(): void
    {
        if (!empty($this->sendNotes)) {
            $this->sentList->update([
                'notes' => trim(($this->sentList->notes ?? '') . "\n[Calidad " . now()->format('d/m/Y H:i') . '] ' . $this->sendNotes),
            ]);
        }

        $this->sentList->moveToNextDepartment(Auth::id());
        session()->flash('message', 'Enviado a Empaque correctamente.');
        $this->redirect(route('admin.sent-lists.show', $this->sentList));
    }

    public function render()
    {
        $this->sentList->load([
            'purchaseOrders.workOrder.purchaseOrder.part',
            'purchaseOrders.workOrder.lots.weighings',
            'purchaseOrders.workOrder.lots.qualityWeighings.weighedBy',
            'purchaseOrders.workOrder.kits',
            'workOrders.purchaseOrder.part',
            'workOrders.lots.weighings',
            'workOrders.lots.qualityWeighings.weighedBy',
            'workOrders.kits',
        ]);

        $workOrders = $this->sentList->getEffectiveWorkOrders();

        return view('livewire.admin.sent-lists.quality-view', compact('workOrders'));
    }
}
