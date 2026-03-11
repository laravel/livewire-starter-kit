<?php

namespace App\Livewire\Admin\SentLists;

use App\Models\Kit;
use App\Models\Lot;
use App\Models\SentList;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SentListMaterialsView extends Component
{
    public SentList $sentList;

    // Lot modal
    public bool $showLotModal = false;
    public ?int $selectedWorkOrderId = null;
    public array $lots = [];

    // Kit modal (crimp only)
    public bool $showKitModal = false;
    public ?int $kitWorkOrderId = null;
    public array $kitLots = [];
    public int $kitQuantity = 0;
    public string $kitNotes = '';

    // Send to inspection modal
    public bool $showSendModal = false;
    public string $sendNotes = '';

    // Material status modal (non-CRIMP)
    public bool $showMaterialModal = false;
    public ?int $materialLotId = null;
    public string $materialStatus = 'pending';

    // Kit status modal (CRIMP)
    public bool $showKitStatusModal = false;
    public ?int $kitStatusId = null;
    public string $kitStatusValue = 'preparing';

    public function mount(SentList $sentList): void
    {
        $this->sentList = $sentList;
    }

    // ─── LOT MODAL ────────────────────────────────────────────────────────────

    public function openLotModal(int $workOrderId): void
    {
        $this->selectedWorkOrderId = $workOrderId;
        $wo = WorkOrder::with('lots')->findOrFail($workOrderId);

        $this->lots = $wo->lots->map(fn($l) => [
            'id'       => $l->id,
            'number'   => $l->lot_number,
            'quantity' => $l->quantity,
        ])->toArray();

        if (empty($this->lots)) {
            $this->lots = [['id' => null, 'number' => '', 'quantity' => 0]];
        }

        $this->showLotModal = true;
    }

    public function addLotRow(): void
    {
        $this->lots[] = ['id' => null, 'number' => '', 'quantity' => 0];
    }

    public function removeLotRow(int $index): void
    {
        if (!empty($this->lots[$index]['id'])) {
            Lot::find($this->lots[$index]['id'])?->delete();
        }

        unset($this->lots[$index]);
        $this->lots = array_values($this->lots);
    }

    public function saveLots(): void
    {
        $this->validate([
            'lots.*.number'   => 'required|string|max:100',
            'lots.*.quantity' => 'required|integer|min:1',
        ], [
            'lots.*.number.required'   => 'El número de lote es obligatorio.',
            'lots.*.quantity.required' => 'La cantidad es obligatoria.',
            'lots.*.quantity.min'      => 'La cantidad debe ser mayor a 0.',
        ]);

        $wo    = WorkOrder::with('purchaseOrder.part')->findOrFail($this->selectedWorkOrderId);
        $total = collect($this->lots)->sum('quantity');

        if ($total > $wo->original_quantity) {
            $this->addError('lots', 'La suma de lotes (' . number_format($total) . ') excede la cantidad del WO (' . number_format($wo->original_quantity) . ').');
            return;
        }

        // Bind WO to this SentList if not already linked
        if ($wo->sent_list_id !== $this->sentList->id) {
            $wo->update(['sent_list_id' => $this->sentList->id]);
        }

        foreach ($this->lots as $row) {
            if (!empty($row['id'])) {
                Lot::find($row['id'])?->update([
                    'lot_number' => $row['number'],
                    'quantity'   => $row['quantity'],
                ]);
            } else {
                Lot::create([
                    'work_order_id' => $wo->id,
                    'lot_number'    => $row['number'],
                    'quantity'      => $row['quantity'],
                    'description'   => $wo->purchaseOrder->part->description ?? '',
                    'status'        => Lot::STATUS_PENDING,
                ]);
            }
        }

        $this->sentList->refresh();
        $this->showLotModal = false;
        $this->selectedWorkOrderId = null;
        $this->lots = [];
        session()->flash('message', 'Lotes guardados correctamente.');
    }

    public function closeLotModal(): void
    {
        $this->showLotModal        = false;
        $this->selectedWorkOrderId = null;
        $this->lots                = [];
    }

    // ─── KIT MODAL ────────────────────────────────────────────────────────────

    public function openKitModal(int $workOrderId): void
    {
        $this->kitWorkOrderId = $workOrderId;
        $this->kitLots        = [];
        $this->kitQuantity    = 0;
        $this->kitNotes       = '';
        $this->showKitModal   = true;
    }

    public function saveKit(): void
    {
        $this->validate([
            'kitLots'     => 'required|array|min:1',
            'kitQuantity' => 'required|integer|min:1',
        ], [
            'kitLots.required'    => 'Seleccione al menos un lote.',
            'kitLots.min'         => 'Seleccione al menos un lote.',
            'kitQuantity.required' => 'La cantidad es obligatoria.',
            'kitQuantity.min'     => 'La cantidad debe ser mayor a 0.',
        ]);

        $wo  = WorkOrder::findOrFail($this->kitWorkOrderId);
        $kit = Kit::create([
            'work_order_id'    => $wo->id,
            'kit_number'       => Kit::generateKitNumber($wo->id),
            'quantity'         => $this->kitQuantity,
            'status'           => Kit::STATUS_PREPARING,
            'validation_notes' => $this->kitNotes ?: null,
            'prepared_by'      => Auth::id(),
        ]);

        $kit->lots()->attach($this->kitLots);

        $this->sentList->refresh();
        $this->showKitModal   = false;
        $this->kitWorkOrderId = null;
        $this->kitLots        = [];
        $this->kitQuantity    = 0;
        $this->kitNotes       = '';
        session()->flash('message', 'Kit creado correctamente.');
    }

    public function closeKitModal(): void
    {
        $this->showKitModal   = false;
        $this->kitWorkOrderId = null;
        $this->kitLots        = [];
        $this->kitQuantity    = 0;
        $this->kitNotes       = '';
    }

    // ─── SEND TO INSPECTION ───────────────────────────────────────────────────

    public function openSendModal(): void
    {
        $this->sentList->load([
            'purchaseOrders.workOrder.purchaseOrder.part',
            'purchaseOrders.workOrder.lots',
            'purchaseOrders.workOrder.kits',
            'workOrders.purchaseOrder.part',
            'workOrders.lots',
            'workOrders.kits',
        ]);

        $workOrders = $this->sentList->workOrders
            ->merge($this->sentList->purchaseOrders->map->workOrder->filter())
            ->unique('id');

        if ($workOrders->isEmpty()) {
            session()->flash('error', 'Esta lista no tiene Work Orders asignados.');
            return;
        }

        foreach ($workOrders as $wo) {
            if ($wo->lots->isEmpty()) {
                session()->flash('error', 'El WO ' . $wo->wo_number . ' no tiene lotes asignados.');
                return;
            }

            if ($wo->purchaseOrder->part->is_crimp && $wo->kits->isEmpty()) {
                session()->flash('error', 'El WO ' . $wo->wo_number . ' (CRIMP) no tiene kits asignados.');
                return;
            }
        }

        $this->sendNotes    = '';
        $this->showSendModal = true;
    }

    public function sendToInspection(): void
    {
        $this->sentList->unresolvedRejections->each(fn($r) => $r->update(['resolved_at' => now()]));

        if (!empty($this->sendNotes)) {
            $this->sentList->update([
                'notes' => trim(($this->sentList->notes ?? '') . "\n[Materiales " . now()->format('d/m/Y H:i') . '] ' . $this->sendNotes),
            ]);
        }

        // Update semaphore statuses so the display page reflects materials approval
        $this->sentList->load([
            'workOrders.purchaseOrder.part',
            'workOrders.lots',
            'workOrders.kits',
            'purchaseOrders.workOrder.purchaseOrder.part',
            'purchaseOrders.workOrder.lots',
            'purchaseOrders.workOrder.kits',
        ]);

        $allWorkOrders = $this->sentList->workOrders
            ->merge($this->sentList->purchaseOrders->map->workOrder->filter())
            ->unique('id');

        foreach ($allWorkOrders as $wo) {
            if ($wo->purchaseOrder->part->is_crimp) {
                // CRIMP: mark all kits as released
                $wo->kits->each(fn($kit) => $kit->update(['status' => Kit::STATUS_RELEASED]));
            } else {
                // Non-CRIMP: mark all lots' material_status as released
                $wo->lots->each(fn($lot) => $lot->update(['material_status' => 'released']));
            }
        }

        $this->sentList->moveToNextDepartment(Auth::id());
        session()->flash('message', 'Lista enviada a Inspección correctamente.');
        $this->redirect(route('admin.sent-lists.show', $this->sentList));
    }

    public function closeSendModal(): void
    {
        $this->showSendModal = false;
        $this->sendNotes     = '';
    }

    // ─── MATERIAL STATUS MODAL (non-CRIMP) ───────────────────────────────────────

    public function openMaterialModal(int $lotId): void
    {
        $lot = Lot::findOrFail($lotId);
        $this->materialLotId  = $lotId;
        $this->materialStatus = $lot->material_status ?? 'pending';
        $this->showMaterialModal = true;
    }

    public function saveMaterial(): void
    {
        Lot::findOrFail($this->materialLotId)->update(['material_status' => $this->materialStatus]);
        $this->showMaterialModal = false;
        $this->materialLotId     = null;
        $this->sentList->refresh();
        session()->flash('message', 'Estado de material actualizado.');
    }

    public function closeMaterialModal(): void
    {
        $this->showMaterialModal = false;
        $this->materialLotId     = null;
        $this->materialStatus    = 'pending';
    }

    // ─── KIT STATUS MODAL (CRIMP) ─────────────────────────────────────────────────

    public function openKitStatusModal(int $kitId): void
    {
        $kit = Kit::findOrFail($kitId);
        $this->kitStatusId    = $kitId;
        $this->kitStatusValue = $kit->status;
        $this->showKitStatusModal = true;
    }

    public function saveKitStatus(): void
    {
        Kit::findOrFail($this->kitStatusId)->update(['status' => $this->kitStatusValue]);
        $this->showKitStatusModal = false;
        $this->kitStatusId        = null;
        $this->sentList->refresh();
        session()->flash('message', 'Estado del kit actualizado.');
    }

    public function closeKitStatusModal(): void
    {
        $this->showKitStatusModal = false;
        $this->kitStatusId        = null;
        $this->kitStatusValue     = 'preparing';
    }

    public function render()
    {
        $this->sentList->load([
            'purchaseOrders.workOrder.purchaseOrder.part',
            'purchaseOrders.workOrder.lots',
            'purchaseOrders.workOrder.kits.lots',
            'workOrders.purchaseOrder.part',
            'workOrders.lots',
            'workOrders.kits.lots',
            'unresolvedRejections.rejectedBy',
            'unresolvedRejections.lot',
        ]);

        // Merge WOs from direct sent_list_id AND from the PO pivot (old-system data)
        $directWOs  = $this->sentList->workOrders;
        $pivotWOs   = $this->sentList->purchaseOrders->map->workOrder->filter()->values();
        $workOrders = $directWOs->merge($pivotWOs)->unique('id')->values();

        return view('livewire.admin.sent-lists.materials-view', compact('workOrders'));
    }
}
