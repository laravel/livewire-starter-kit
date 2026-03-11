<?php

namespace App\Livewire\Admin\SentLists;

use App\Models\Kit;
use App\Models\Lot;
use App\Models\PackagingRecord;
use App\Models\SentList;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SentListPackagingView extends Component
{
    public SentList $sentList;

    // Packaging modal
    public bool $showPackagingModal = false;
    public ?int $packagingLotId = null;
    public int $packedPieces = 0;
    public int $surplusPieces = 0;
    public string $packagingComments = '';
    public string $packedAt = '';
    public int $modalAvailable = 0;

    // Close list modal
    public bool $showCloseModal = false;

    // ── Decision modal (Control de Materiales) ──────────────────────────
    public bool $showDecisionModal = false;
    public $selectedLotForDecision = null;
    public int $decLotTotal = 0;
    public int $decPacked = 0;
    public int $decSurplus = 0;
    public int $decMissing = 0;
    public bool $decIsCrimp = false;
    public $decClosureDecision = null;
    public bool $decSurplusDelivered = false;
    public bool $decSurplusReceived = false;

    // ── Create Lot form modal (from Decision) ────────────────────────────
    public bool $showCreateLotFormModal = false;
    public string $createLotName = '';
    public int $createLotQuantity = 0;
    public string $createLotType = ''; // 'complete' or 'new_lot'

    public function mount(SentList $sentList): void
    {
        $this->sentList = $sentList;
        $this->packedAt = now()->format('Y-m-d\TH:i');
    }

    // ── Packaging modal ──────────────────────────────────────────────────

    public function openPackagingModal(int $lotId): void
    {
        $lot = Lot::with('qualityWeighings')->findOrFail($lotId);

        $this->packagingLotId      = $lotId;
        $this->packedPieces        = 0;
        $this->surplusPieces       = 0;
        $this->packagingComments   = '';
        $this->packedAt            = now()->format('Y-m-d\TH:i');
        $this->modalAvailable      = (int) $lot->qualityWeighings->sum('good_pieces');
        $this->showPackagingModal  = true;
    }

    public function savePackaging(): void
    {
        $this->validate([
            'packedPieces'      => 'required|integer|min:0',
            'surplusPieces'     => 'required|integer|min:0',
            'packedAt'          => 'required|date',
            'packagingComments' => 'nullable|string|max:500',
        ], [
            'packedPieces.required'  => 'Las piezas empacadas son obligatorias.',
            'surplusPieces.required' => 'Las piezas sobrantes son obligatorias.',
            'packedAt.required'      => 'La fecha/hora es obligatoria.',
        ]);

        if ($this->packedPieces > $this->modalAvailable) {
            $this->addError('packedPieces', "No puede empacar más de {$this->modalAvailable} piezas disponibles.");
            return;
        }

        PackagingRecord::create([
            'lot_id'           => $this->packagingLotId,
            'available_pieces' => $this->modalAvailable,
            'packed_pieces'    => $this->packedPieces,
            'surplus_pieces'   => $this->surplusPieces,
            'comments'         => $this->packagingComments ?: null,
            'packed_at'        => $this->packedAt,
            'packed_by'        => Auth::id(),
        ]);

        $this->showPackagingModal = false;
        $this->packagingLotId    = null;
        $this->packedPieces      = 0;
        $this->surplusPieces     = 0;
        $this->packagingComments = '';
        $this->modalAvailable    = 0;
        $this->sentList->refresh();
        session()->flash('message', 'Empaque registrado correctamente.');
    }

    public function closePackagingModal(): void
    {
        $this->showPackagingModal = false;
        $this->packagingLotId    = null;
        $this->packedPieces      = 0;
        $this->surplusPieces     = 0;
        $this->packagingComments = '';
        $this->modalAvailable    = 0;
    }

    public function deletePackaging(int $id): void
    {
        PackagingRecord::findOrFail($id)->delete();
        $this->sentList->refresh();
        session()->flash('message', 'Registro de empaque eliminado.');
    }

    public function receiveViajero(int $lotId): void
    {
        $lot = Lot::findOrFail($lotId);
        $lot->update([
            'viajero_received'    => true,
            'viajero_received_at' => now(),
            'viajero_received_by' => Auth::id(),
        ]);
        $this->sentList->refresh();
        session()->flash('message', "Viajero del lote {$lot->lot_number} confirmado.");
    }

    // ── Close list modal ─────────────────────────────────────────────────

    public function openCloseModal(): void
    {
        $this->showCloseModal = true;
    }

    public function closeList(): void
    {
        $this->sentList->update(['status' => SentList::STATUS_CONFIRMED]);
        session()->flash('message', 'Lista completada y cerrada exitosamente.');
        $this->redirect(route('admin.sent-lists.index'));
    }

    // ── Decision modal ───────────────────────────────────────────────────

    public function openDecisionModal(int $lotId): void
    {
        $lot = Lot::with(['workOrder.purchaseOrder.part', 'packagingRecords'])->find($lotId);

        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $this->selectedLotForDecision = $lot;
        $this->decLotTotal            = $lot->quantity;
        $this->decPacked              = $lot->getPackagingPackedPieces();
        $this->decSurplus             = $lot->getPackagingTotalSurplus();
        $this->decMissing             = max(0, $this->decLotTotal - $this->decPacked - $this->decSurplus);
        $this->decIsCrimp             = (bool) ($lot->workOrder->purchaseOrder->part->is_crimp ?? false);
        $this->decClosureDecision     = $lot->closure_decision;
        $this->decSurplusDelivered    = (bool) $lot->surplus_delivered;
        $this->decSurplusReceived     = (bool) $lot->surplus_received;
        $this->showDecisionModal      = true;
    }

    public function closeDecisionModal(): void
    {
        $this->showDecisionModal      = false;
        $this->selectedLotForDecision = null;
        $this->decLotTotal            = 0;
        $this->decPacked              = 0;
        $this->decSurplus             = 0;
        $this->decMissing             = 0;
        $this->decIsCrimp             = false;
        $this->decClosureDecision     = null;
        $this->decSurplusReceived     = false;
        $this->resetErrorBag();
    }

    /**
     * Decision: Completar Lote — create a new lot with the missing pieces.
     */
    public function decisionCompleteLot(): void
    {
        if (!$this->selectedLotForDecision) return;

        $this->createLotType     = 'complete';
        $this->createLotQuantity = $this->decMissing;
        $this->createLotName     = Lot::generateNextLotNumber($this->selectedLotForDecision->work_order_id);
        $this->showCreateLotFormModal = true;
    }

    /**
     * Decision: Nuevo Lote — close current lot, create new lot with missing pieces,
     * and send SentList back to Materiales.
     */
    public function decisionNewLot(): void
    {
        if (!$this->selectedLotForDecision) return;

        $this->createLotType     = 'new_lot';
        $this->createLotQuantity = $this->decMissing;
        $this->createLotName     = Lot::generateNextLotNumber($this->selectedLotForDecision->work_order_id);
        $this->showCreateLotFormModal = true;
    }

    /**
     * Decision: Cerrar Lote aceptando faltantes.
     */
    public function decisionCloseAsIs(): void
    {
        if (!$this->selectedLotForDecision) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $lot     = $this->selectedLotForDecision;
        $missing = $this->decMissing;

        $lot->update([
            'closure_decision'   => Lot::CLOSURE_CLOSE_AS_IS,
            'closure_decided_by' => Auth::id(),
            'closure_decided_at' => now(),
            'status'             => Lot::STATUS_COMPLETED,
            'packaging_status'   => 'approved',
        ]);

        session()->flash('message', $missing > 0
            ? "Lote cerrado aceptando " . number_format($missing) . " piezas faltantes."
            : 'Lote cerrado sin faltantes.');

        $this->openDecisionModal($lot->id);
        $this->sentList->refresh();
    }

    /**
     * Reopen a lot: clear its closure decision and reset status.
     */
    public function reopenLot(): void
    {
        if (!$this->selectedLotForDecision) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $lot = $this->selectedLotForDecision;

        $lot->update([
            'closure_decision'     => null,
            'closure_decided_by'   => null,
            'closure_decided_at'   => null,
            'surplus_delivered'    => false,
            'surplus_delivered_at' => null,
            'surplus_delivered_by' => null,
            'surplus_received'     => false,
            'surplus_received_at'  => null,
            'surplus_received_by'  => null,
            'status'               => Lot::STATUS_IN_PROGRESS,
            'packaging_status'     => 'pending',
        ]);

        session()->flash('message', 'Lote ' . $lot->lot_number . ' reabierto exitosamente.');
        $this->openDecisionModal($lot->id);
        $this->sentList->refresh();
    }

    /**
     * Confirm surplus material received.
     */
    public function confirmSurplusReceived(): void
    {
        if (!$this->selectedLotForDecision) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $lot = $this->selectedLotForDecision;

        $lot->update([
            'surplus_received'    => true,
            'surplus_received_at' => now(),
            'surplus_received_by' => Auth::id(),
            'status'              => Lot::STATUS_COMPLETED,
            'packaging_status'    => 'approved',
        ]);

        session()->flash('message', 'Material sobrante recibido. Lote completado.');
        $this->openDecisionModal($lot->id);
        $this->sentList->refresh();
    }

    // ── Create Lot modal ─────────────────────────────────────────────────

    public function closeCreateLotFormModal(): void
    {
        $this->showCreateLotFormModal = false;
        $this->createLotName          = '';
        $this->createLotQuantity      = 0;
        $this->createLotType          = '';
        $this->resetErrorBag();
    }

    /**
     * Confirm creation of the new lot (and kit if crimp).
     * If type is 'new_lot', also sends the SentList back to Materiales.
     */
    public function confirmCreateLot(): void
    {
        $this->validate([
            'createLotName'     => 'required|string|max:255',
            'createLotQuantity' => 'required|integer|min:1',
        ], [
            'createLotName.required'     => 'El nombre del lote es requerido.',
            'createLotQuantity.required' => 'La cantidad es requerida.',
            'createLotQuantity.min'      => 'La cantidad debe ser mayor a 0.',
        ]);

        if (!$this->selectedLotForDecision) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $lot    = $this->selectedLotForDecision;
        $part   = $lot->workOrder->purchaseOrder->part;
        $isCrimp = (bool) ($part->is_crimp ?? false);

        // Create new lot
        $newLot = Lot::create([
            'work_order_id' => $lot->work_order_id,
            'lot_number'    => $this->createLotName,
            'quantity'      => $this->createLotQuantity,
            'description'   => $part->description,
            'status'        => Lot::STATUS_PENDING,
        ]);

        $message = "Lote #{$this->createLotName} creado con " . number_format($this->createLotQuantity) . " piezas.";

        // If crimp, also create a kit
        if ($isCrimp) {
            $kit = Kit::create([
                'work_order_id'          => $lot->work_order_id,
                'kit_number'             => Kit::generateKitNumber($lot->work_order_id),
                'quantity'               => $this->createLotQuantity,
                'status'                 => Kit::STATUS_PREPARING,
                'current_approval_cycle' => 1,
            ]);
            $newLot->kits()->attach($kit->id);
            $message .= " Kit {$kit->kit_number} creado automáticamente (parte con crimp).";
        }

        if ($this->createLotType === 'complete') {
            // Completar Lote: reset viajero so flow continues on original lot
            $lot->update([
                'viajero_received'    => false,
                'viajero_received_at' => null,
                'viajero_received_by' => null,
                'closure_decision'    => null,
                'closure_decided_by'  => null,
                'closure_decided_at'  => null,
            ]);
        } elseif ($this->createLotType === 'new_lot') {
            // Nuevo Lote: close current lot
            $lot->update([
                'closure_decision'   => Lot::CLOSURE_NEW_LOT,
                'closure_decided_by' => Auth::id(),
                'closure_decided_at' => now(),
                'status'             => Lot::STATUS_COMPLETED,
                'packaging_status'   => 'approved',
            ]);

            $surplus = $lot->getPackagingTotalSurplus();
            if ($surplus > 0) {
                $message .= " Sobrantes ({$surplus} pz) pendientes de devolución.";
            }

            // Send SentList back to Materiales
            $this->sentList->update([
                'current_department'    => SentList::DEPT_MATERIALS,
                'materials_approved_at' => null,
                'materials_approved_by' => null,
                'inspection_approved_at' => null,
                'inspection_approved_by' => null,
                'production_approved_at' => null,
                'production_approved_by' => null,
                'quality_approved_at'   => null,
                'quality_approved_by'   => null,
            ]);

            $message .= ' La lista regresó a Materiales para el nuevo lote.';
        }

        session()->flash('message', $message);
        $this->closeCreateLotFormModal();
        $this->openDecisionModal($lot->id);
        $this->sentList->refresh();
    }

    // ── Render ───────────────────────────────────────────────────────────

    public function render()
    {
        $this->sentList->load([
            'purchaseOrders.workOrder.purchaseOrder.part',
            'purchaseOrders.workOrder.lots.qualityWeighings',
            'purchaseOrders.workOrder.lots.packagingRecords.packedBy',
            'purchaseOrders.workOrder.lots.viajeroReceivedByUser',
            'workOrders.purchaseOrder.part',
            'workOrders.lots.qualityWeighings',
            'workOrders.lots.packagingRecords.packedBy',
            'workOrders.lots.viajeroReceivedByUser',
        ]);

        $workOrders           = $this->sentList->getEffectiveWorkOrders();
        $allLots              = $workOrders->flatMap->lots;
        $allLotsHavePackaging = $allLots->isNotEmpty()
            && $allLots->every(fn($l) => $l->packagingRecords->isNotEmpty());

        return view('livewire.admin.sent-lists.packaging-view', compact('workOrders', 'allLotsHavePackaging'));
    }
}
