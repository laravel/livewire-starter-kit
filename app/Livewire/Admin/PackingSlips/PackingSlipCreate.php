<?php

namespace App\Livewire\Admin\PackingSlips;

use App\Models\Lot;
use App\Models\PackingSlip;
use App\Models\PackingSlipItem;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PackingSlipCreate extends Component
{
    public string $notes = '';
    public array $selectedLotIds = [];
    public array $labelSpecs = [];

    protected function rules(): array
    {
        return [
            'notes'            => 'nullable|string|max:1000',
            'selectedLotIds'   => 'required|array|min:1',
            'selectedLotIds.*' => 'integer|exists:lots,id',
            'labelSpecs'       => 'array',
            'labelSpecs.*'     => 'nullable|string|max:50',
        ];
    }

    protected function messages(): array
    {
        return [
            'selectedLotIds.required' => 'Debe seleccionar al menos un lote.',
            'selectedLotIds.min'      => 'Debe seleccionar al menos un lote.',
        ];
    }

    public function toggleLot(int $lotId): void
    {
        if (in_array($lotId, $this->selectedLotIds)) {
            $this->selectedLotIds = array_values(
                array_filter($this->selectedLotIds, fn($id) => $id !== $lotId)
            );
            unset($this->labelSpecs[$lotId]);
        } else {
            $this->selectedLotIds[] = $lotId;
            $this->labelSpecs[$lotId] = '';
        }
    }

    public function save(): void
    {
        $this->validate();

        // Verificar que todos los lotes seleccionados tengan WO con external_wo_number
        $lots = Lot::with('workOrder')->whereIn('id', $this->selectedLotIds)->get();

        foreach ($lots as $lot) {
            if (empty($lot->workOrder->external_wo_number ?? null)) {
                $this->addError(
                    'selectedLotIds',
                    "El lote {$lot->lot_number} pertenece a una WO sin número externo (external_wo_number). Corrija la WO antes de continuar."
                );
                return;
            }
        }

        // Crear el Packing Slip
        $packingSlip = PackingSlip::create([
            'created_by' => Auth::id(),
            'status'     => PackingSlip::STATUS_DRAFT,
            'notes'      => $this->notes ?: null,
        ]);

        // Crear los items
        foreach ($lots as $lot) {
            PackingSlipItem::create([
                'packing_slip_id' => $packingSlip->id,
                'lot_id'          => $lot->id,
                'quantity_packed' => $lot->quantity_packed_final ?? $lot->quantity ?? 0,
                'wo_number_ps'    => $lot->workOrder->external_wo_number ?? $lot->workOrder->wo_number,
                'lot_date_code'   => $lot->receipt_date?->format('Y-m-d') ?? null,
                'label_spec'      => $this->labelSpecs[$lot->id] ?? null,
            ]);
        }

        session()->flash('flash.banner', "Packing Slip {$packingSlip->ps_number} creado correctamente.");
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.packing-slips.show', $packingSlip), navigate: true);
    }

    public function render()
    {
        $availableLots = Lot::with(['workOrder.purchaseOrder.part'])
            ->readyForShipping()
            ->orderBy('lot_number')
            ->get();

        return view('livewire.admin.packing-slips.packing-slip-create', [
            'availableLots' => $availableLots,
        ]);
    }
}
