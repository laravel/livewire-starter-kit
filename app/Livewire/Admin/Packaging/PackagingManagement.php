<?php

namespace App\Livewire\Admin\Packaging;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\PackagingRecord;
use App\Models\Lot;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.app')]
class PackagingManagement extends Component
{
    use WithPagination;

    // Filters
    public string $searchTerm = '';
    public string $filterLotId = '';
    public string $filterWorkOrderId = '';

    // Modal state
    public bool $showModal = false;
    public ?int $editingId = null;

    // Form fields
    public ?int $formLotId = null;
    public int $formPackedPieces = 0;
    public int $formSurplusPieces = 0;
    public ?int $formAdjustedSurplus = null;
    public string $formAdjustmentReason = '';
    public string $formComments = '';
    public string $formPackedAt = '';

    // Context info for modal
    public ?string $modalLotNumber = null;
    public ?string $modalPartNumber = null;
    public ?string $modalWo = null;
    public int $modalAvailable = 0;

    public function updatedSearchTerm(): void { $this->resetPage(); }
    public function updatedFilterLotId(): void { $this->resetPage(); }
    public function updatedFilterWorkOrderId(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->searchTerm = '';
        $this->filterLotId = '';
        $this->filterWorkOrderId = '';
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->formPackedAt = now()->format('Y-m-d\TH:i');
        $this->showModal = true;
    }

    public function openCreateForLot(int $lotId): void
    {
        $this->resetForm();
        $this->formLotId = $lotId;
        $this->formPackedAt = now()->format('Y-m-d\TH:i');
        $this->loadLotContext($lotId);
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $record = PackagingRecord::findOrFail($id);
        $this->editingId = $id;
        $this->formLotId = $record->lot_id;
        $this->formPackedPieces = $record->packed_pieces;
        $this->formSurplusPieces = $record->surplus_pieces;
        $this->formAdjustedSurplus = $record->adjusted_surplus;
        $this->formAdjustmentReason = $record->adjustment_reason ?? '';
        $this->formComments = $record->comments ?? '';
        $this->formPackedAt = $record->packed_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i');
        $this->loadLotContext($record->lot_id);
        $this->showModal = true;
    }

    public function updatedFormLotId($value): void
    {
        if ($value) {
            $this->loadLotContext((int) $value);
        } else {
            $this->modalLotNumber = null;
            $this->modalPartNumber = null;
            $this->modalWo = null;
            $this->modalAvailable = 0;
        }
    }

    protected function loadLotContext(int $lotId): void
    {
        $lot = Lot::with('workOrder.purchaseOrder.part')->find($lotId);
        if ($lot) {
            $this->modalLotNumber = $lot->lot_number;
            $this->modalPartNumber = $lot->workOrder->purchaseOrder->part->number ?? 'N/A';
            $this->modalWo = $lot->workOrder->purchaseOrder->wo ?? 'N/A';
            $this->modalAvailable = $lot->getPackagingAvailablePieces();
        }
    }

    public function save(): void
    {
        $this->validate([
            'formLotId' => 'required|exists:lots,id',
            'formPackedPieces' => 'required|integer|min:0',
            'formSurplusPieces' => 'required|integer|min:0',
            'formAdjustedSurplus' => 'nullable|integer|min:0',
            'formAdjustmentReason' => $this->formAdjustedSurplus !== null ? 'required|string|max:500' : 'nullable|string|max:500',
            'formComments' => 'nullable|string|max:1000',
            'formPackedAt' => 'required|date',
        ], [
            'formLotId.required' => 'Debe seleccionar un lote.',
            'formPackedPieces.required' => 'Las piezas empacadas son obligatorias.',
            'formPackedPieces.min' => 'Las piezas empacadas no pueden ser negativas.',
            'formSurplusPieces.min' => 'Las piezas sobrantes no pueden ser negativas.',
            'formAdjustmentReason.required' => 'Si ajusta el sobrante, debe indicar la razón.',
            'formPackedAt.required' => 'La fecha de empaque es obligatoria.',
        ]);

        $data = [
            'lot_id' => $this->formLotId,
            'packed_pieces' => $this->formPackedPieces,
            'surplus_pieces' => $this->formSurplusPieces,
            'adjusted_surplus' => $this->formAdjustedSurplus,
            'adjustment_reason' => $this->formAdjustmentReason ?: null,
            'comments' => $this->formComments ?: null,
            'packed_at' => $this->formPackedAt,
            'packed_by' => Auth::id(),
        ];

        // Calculate available_pieces for the record
        $lot = Lot::find($this->formLotId);
        if ($lot) {
            $data['available_pieces'] = $lot->getPackagingAvailablePieces();
        }

        if ($this->editingId) {
            $record = PackagingRecord::findOrFail($this->editingId);
            $record->update($data);
            session()->flash('message', 'Registro de empaque actualizado.');
        } else {
            PackagingRecord::create($data);
            session()->flash('message', 'Registro de empaque creado.');
        }

        $this->closeModal();
    }

    public function deleteRecord(int $id): void
    {
        $record = PackagingRecord::find($id);
        if ($record) {
            $record->delete();
            session()->flash('message', 'Registro de empaque eliminado.');
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->formLotId = null;
        $this->formPackedPieces = 0;
        $this->formSurplusPieces = 0;
        $this->formAdjustedSurplus = null;
        $this->formAdjustmentReason = '';
        $this->formComments = '';
        $this->formPackedAt = '';
        $this->modalLotNumber = null;
        $this->modalPartNumber = null;
        $this->modalWo = null;
        $this->modalAvailable = 0;
        $this->resetValidation();
    }

    public function render()
    {
        $query = PackagingRecord::with(['lot.workOrder.purchaseOrder.part', 'packedBy']);

        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->whereHas('lot', function ($lq) {
                    $lq->where('lot_number', 'like', "%{$this->searchTerm}%");
                })
                ->orWhereHas('lot.workOrder.purchaseOrder', function ($woq) {
                    $woq->where('wo', 'like', "%{$this->searchTerm}%");
                })
                ->orWhereHas('lot.workOrder.purchaseOrder.part', function ($pq) {
                    $pq->where('number', 'like', "%{$this->searchTerm}%");
                })
                ->orWhere('comments', 'like', "%{$this->searchTerm}%");
            });
        }

        if (!empty($this->filterLotId)) {
            $query->where('lot_id', $this->filterLotId);
        }

        if (!empty($this->filterWorkOrderId)) {
            $query->whereHas('lot', fn ($q) => $q->where('work_order_id', $this->filterWorkOrderId));
        }

        $records = $query->latest('packed_at')->paginate(20);

        // Lots for dropdown filter
        $lotsForFilter = Lot::whereHas('packagingRecords')
            ->with('workOrder.purchaseOrder.part')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        // WOs for dropdown filter
        $workOrdersForFilter = WorkOrder::whereHas('lots.packagingRecords')
            ->with('purchaseOrder.part')
            ->orderByDesc('wo_number')
            ->get();

        // Lots available for creating new records
        $lotsForCreate = Lot::with('workOrder.purchaseOrder.part')
            ->whereHas('qualityWeighings')
            ->where('status', '!=', Lot::STATUS_COMPLETED)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('livewire.admin.packaging.packaging-management', [
            'records' => $records,
            'lotsForFilter' => $lotsForFilter,
            'workOrdersForFilter' => $workOrdersForFilter,
            'lotsForCreate' => $lotsForCreate,
        ]);
    }
}
