<?php

namespace App\Livewire\Admin\Quality;

use App\Models\Lot;
use App\Models\Kit;
use Livewire\Component;
use Livewire\WithPagination;

class QualityInspectionList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterQualityStatus = '';
    public int $perPage = 10;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Modal state
    public bool $showQualityModal = false;
    public ?int $selectedLotId = null;
    public ?Lot $selectedLot = null;
    public string $qualityAction = '';
    public string $qualityComments = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterQualityStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openQualityModal(int $lotId): void
    {
        $this->selectedLotId = $lotId;
        $this->selectedLot = Lot::with(['workOrder.purchaseOrder.part', 'kits', 'qualityInspector'])->find($lotId);
        $this->qualityAction = '';
        $this->qualityComments = '';
        $this->showQualityModal = true;
    }

    public function closeQualityModal(): void
    {
        $this->showQualityModal = false;
        $this->selectedLotId = null;
        $this->selectedLot = null;
        $this->qualityAction = '';
        $this->qualityComments = '';
    }

    public function submitQualityDecision(): void
    {
        $this->validate([
            'qualityAction' => 'required|in:approved,rejected',
            'qualityComments' => 'nullable|string|max:1000',
        ]);

        if (!$this->selectedLotId) {
            session()->flash('error', 'No se ha seleccionado un lote.');
            return;
        }

        $lot = Lot::find($this->selectedLotId);

        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        if (!$lot->canBeInspectedByQuality()) {
            session()->flash('error', $lot->getQualityBlockedReason() ?? 'Este lote no puede ser inspeccionado.');
            $this->closeQualityModal();
            return;
        }

        $lot->update([
            'quality_status' => $this->qualityAction,
            'quality_comments' => $this->qualityComments,
            'quality_inspected_at' => now(),
            'quality_inspected_by' => auth()->id(),
        ]);

        // If approved, transition the kit to in_assembly
        if ($this->qualityAction === Lot::QUALITY_APPROVED) {
            $releasedKit = $lot->getReleasedKit();
            if ($releasedKit) {
                $releasedKit->update(['status' => Kit::STATUS_IN_ASSEMBLY]);
            }
            session()->flash('message', 'Lote aprobado correctamente. El kit ha sido enviado a ensamble.');
        } else {
            // If rejected, mark the kit as rejected
            $releasedKit = $lot->getReleasedKit();
            if ($releasedKit) {
                $releasedKit->update(['status' => Kit::STATUS_REJECTED]);
            }
            session()->flash('message', 'Lote rechazado. El kit ha sido marcado para correccion.');
        }

        $this->closeQualityModal();
    }

    public function render()
    {
        $query = Lot::with(['workOrder.purchaseOrder.part', 'kits', 'qualityInspector'])
            ->search($this->search)
            ->when($this->filterQualityStatus, function ($q) {
                return $q->where('quality_status', $this->filterQualityStatus);
            })
            // Show lots that have at least one kit with status 'released' OR have already been inspected
            ->where(function ($q) {
                $q->whereHas('kits', function ($kitQuery) {
                    $kitQuery->where('status', Kit::STATUS_RELEASED);
                })
                ->orWhereNotNull('quality_inspected_at');
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $lots = $query->paginate($this->perPage);

        // Statistics
        $stats = [
            'pending' => Lot::whereHas('kits', fn($q) => $q->where('status', Kit::STATUS_RELEASED))
                ->where('quality_status', Lot::QUALITY_PENDING)
                ->count(),
            'approved' => Lot::where('quality_status', Lot::QUALITY_APPROVED)->count(),
            'rejected' => Lot::where('quality_status', Lot::QUALITY_REJECTED)->count(),
        ];

        return view('livewire.admin.quality.quality-inspection-list', [
            'lots' => $lots,
            'qualityStatuses' => Lot::getQualityStatuses(),
            'stats' => $stats,
        ]);
    }
}
