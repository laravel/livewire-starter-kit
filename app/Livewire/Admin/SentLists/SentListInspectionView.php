<?php

namespace App\Livewire\Admin\SentLists;

use App\Models\Lot;
use App\Models\SentList;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class SentListInspectionView extends Component
{
    public SentList $sentList;

    // Rejection modal
    public bool $showRejectModal = false;
    public ?int $rejectLotId = null;
    public string $rejectReason = '';

    // Return to materials modal
    public bool $showReturnModal = false;
    public string $returnReason = '';

    // Approve and send modal
    public bool $showApproveModal = false;
    public string $approveNotes = '';

    public function mount(SentList $sentList): void
    {
        $this->sentList = $sentList;
    }

    public function approveLot(int $lotId): void
    {
        $lot = Lot::findOrFail($lotId);
        $lot->update([
            'inspection_status'        => 'approved',
            'inspection_completed_at'  => now(),
            'inspection_completed_by'  => Auth::id(),
        ]);

        $this->sentList->refresh();
        session()->flash('message', 'Lote aprobado correctamente.');
    }

    public function openRejectModal(int $lotId): void
    {
        $this->rejectLotId  = $lotId;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function rejectLot(): void
    {
        $this->validate([
            'rejectReason' => 'required|string|min:5',
        ], [
            'rejectReason.required' => 'El motivo del rechazo es obligatorio.',
            'rejectReason.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $lot = Lot::findOrFail($this->rejectLotId);
        $lot->update([
            'inspection_status'   => 'rejected',
            'inspection_comments' => $this->rejectReason,
        ]);

        $this->showRejectModal = false;
        $this->rejectLotId     = null;
        $this->rejectReason    = '';
        $this->sentList->refresh();
        session()->flash('message', 'Lote rechazado. Usa el botón "Regresar a Materiales" para enviar la corrección.');
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectLotId     = null;
        $this->rejectReason    = '';
    }

    public function openReturnModal(): void
    {
        $this->returnReason    = '';
        $this->showReturnModal = true;
    }

    public function returnToMaterials(): void
    {
        $this->validate([
            'returnReason' => 'required|string|min:5',
        ], [
            'returnReason.required' => 'Debe indicar el motivo del retorno.',
            'returnReason.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $rejectedLot = $this->sentList->getEffectiveWorkOrders()
            ->flatMap->lots
            ->firstWhere('inspection_status', 'rejected');

        $this->sentList->moveToPreviousDepartment(
            SentList::DEPT_MATERIALS,
            Auth::id(),
            $this->returnReason,
            $rejectedLot?->id
        );

        session()->flash('message', 'Lista regresada a Materiales con el motivo indicado.');
        $this->redirect(route('admin.sent-lists.show', $this->sentList));
    }

    public function closeReturnModal(): void
    {
        $this->showReturnModal = false;
        $this->returnReason    = '';
    }

    public function openApproveModal(): void
    {
        $this->approveNotes    = '';
        $this->showApproveModal = true;
    }

    public function sendToProduction(): void
    {
        if (!empty($this->approveNotes)) {
            $this->sentList->update([
                'notes' => trim(($this->sentList->notes ?? '') . "\n[Inspección " . now()->format('d/m/Y H:i') . '] ' . $this->approveNotes),
            ]);
        }

        $this->sentList->moveToNextDepartment(Auth::id());
        session()->flash('message', 'Lista aprobada y enviada a Producción.');
        $this->redirect(route('admin.sent-lists.show', $this->sentList));
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->approveNotes     = '';
    }

    public function render()
    {
        $this->sentList->load([
            'purchaseOrders.workOrder.purchaseOrder.part',
            'purchaseOrders.workOrder.lots',
            'purchaseOrders.workOrder.kits',
            'workOrders.purchaseOrder.part',
            'workOrders.lots',
            'workOrders.kits',
        ]);

        $workOrders  = $this->sentList->getEffectiveWorkOrders();
        $allLots     = $workOrders->flatMap->lots;
        $allApproved = $allLots->isNotEmpty() && $allLots->every(fn($l) => $l->inspection_status === 'approved');
        $hasRejected = $allLots->contains(fn($l) => $l->inspection_status === 'rejected');

        return view('livewire.admin.sent-lists.inspection-view', compact('workOrders', 'allApproved', 'hasRejected'));
    }
}
