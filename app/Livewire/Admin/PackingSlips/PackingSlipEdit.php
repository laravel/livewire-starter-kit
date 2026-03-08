<?php

namespace App\Livewire\Admin\PackingSlips;

use App\Models\Lot;
use App\Models\PackingSlip;
use App\Models\PackingSlipItem;
use Livewire\Component;

class PackingSlipEdit extends Component
{
    public PackingSlip $packingSlip;
    public string $notes = '';
    public array $selectedLotIds = [];
    public array $labelSpecs = [];

    public function mount(PackingSlip $packingSlip): void
    {
        $this->packingSlip = $packingSlip->load(['items.lot', 'creator']);

        if (!$packingSlip->isDraft()) {
            session()->flash('flash.banner', 'Este Packing Slip no se puede editar porque ya no está en estado Borrador.');
            session()->flash('flash.bannerStyle', 'danger');
            $this->redirect(route('admin.packing-slips.show', $packingSlip), navigate: true);
            return;
        }

        $this->notes = $packingSlip->notes ?? '';

        // Cargar los lotes actuales del PS
        foreach ($packingSlip->items as $item) {
            $this->selectedLotIds[] = $item->lot_id;
            $this->labelSpecs[$item->lot_id] = $item->label_spec ?? '';
        }
    }

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
            if (!isset($this->labelSpecs[$lotId])) {
                $this->labelSpecs[$lotId] = '';
            }
        }
    }

    public function update(): void
    {
        if (!$this->packingSlip->isDraft()) {
            session()->flash('flash.banner', 'Este Packing Slip no se puede editar porque ya no está en estado Borrador.');
            session()->flash('flash.bannerStyle', 'danger');
            $this->redirect(route('admin.packing-slips.show', $this->packingSlip), navigate: true);
            return;
        }

        $this->validate();

        // Verificar que todos los lotes tengan WO con external_wo_number
        $lots = Lot::with('workOrder')->whereIn('id', $this->selectedLotIds)->get();

        foreach ($lots as $lot) {
            if (empty($lot->workOrder->external_wo_number ?? null)) {
                $this->addError(
                    'selectedLotIds',
                    "El lote {$lot->lot_number} pertenece a una WO sin número externo. Corrija la WO antes de continuar."
                );
                return;
            }
        }

        // Actualizar notas
        $this->packingSlip->update([
            'notes' => $this->notes ?: null,
        ]);

        // IDs de items actuales en el PS
        $currentLotIds = $this->packingSlip->items()->pluck('lot_id')->toArray();
        $newLotIds     = $this->selectedLotIds;

        // Eliminar items que fueron deseleccionados
        $toRemove = array_diff($currentLotIds, $newLotIds);
        if (!empty($toRemove)) {
            $this->packingSlip->items()->whereIn('lot_id', $toRemove)->delete();
        }

        // Agregar nuevos items y actualizar label_spec de existentes
        foreach ($lots as $lot) {
            $existing = $this->packingSlip->items()->where('lot_id', $lot->id)->first();

            if ($existing) {
                $existing->update([
                    'label_spec' => $this->labelSpecs[$lot->id] ?? null,
                ]);
            } else {
                PackingSlipItem::create([
                    'packing_slip_id' => $this->packingSlip->id,
                    'lot_id'          => $lot->id,
                    'quantity_packed' => $lot->quantity_packed_final ?? $lot->quantity ?? 0,
                    'wo_number_ps'    => $lot->workOrder->external_wo_number ?? $lot->workOrder->wo_number,
                    'lot_date_code'   => $lot->receipt_date?->format('Y-m-d') ?? null,
                    'label_spec'      => $this->labelSpecs[$lot->id] ?? null,
                ]);
            }
        }

        session()->flash('flash.banner', "Packing Slip {$this->packingSlip->ps_number} actualizado correctamente.");
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.packing-slips.show', $this->packingSlip), navigate: true);
    }

    public function render()
    {
        // Lotes disponibles = los que están listos para shipping Y no tienen PS asignado
        // MAS los que ya están en ESTE PS (para poder mantenerlos seleccionados)
        $currentLotIds = $this->packingSlip->items()->pluck('lot_id')->toArray();

        $availableLots = Lot::with(['workOrder.purchaseOrder.part'])
            ->where(function ($q) use ($currentLotIds) {
                $q->readyForShipping()
                  ->orWhereIn('id', $currentLotIds);
            })
            ->orderBy('lot_number')
            ->get();

        return view('livewire.admin.packing-slips.packing-slip-edit', [
            'availableLots' => $availableLots,
        ]);
    }
}
