<?php

namespace App\Livewire\Admin\Quality;

use App\Models\Lot;
use App\Models\Kit;
use App\Models\QualityWeighing;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\WithPagination;

class QualityWeighings extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterQualityStatus = '';
    public int $perPage = 15;
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';

    // Modal de detalle de lote
    public bool $showDetailModal = false;
    public ?int $selectedLotId = null;
    public $selectedLot = null;
    public array $productionWeighings = [];
    public array $qualityWeighings = [];
    public int $prodGoodTotal = 0;
    public int $prodBadTotal = 0;
    public int $qualGoodTotal = 0;
    public int $qualBadTotal = 0;
    public int $qualPending = 0;

    // Modal de nueva pesada
    public bool $showWeighingModal = false;
    public int $qualGoodPieces = 0;
    public int $qualBadPieces = 0;
    public string $qualWeighedAt = '';
    public string $qualComments = '';
    public ?int $qualKitId = null;
    public $qualKits = [];
    public int $qualRemainingPieces = 0;
    public bool $qualIsCrimp = true;

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

    /**
     * Open lot detail modal showing production + quality weighings.
     */
    public function openDetailModal(int $lotId): void
    {
        $lot = Lot::with([
            'workOrder.purchaseOrder.part',
            'kits',
            'weighings.weighedBy',
            'qualityWeighings.weighedBy',
        ])->find($lotId);

        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        $this->selectedLotId = $lotId;
        $this->selectedLot = $lot;

        // Production weighings
        $this->productionWeighings = $lot->weighings->map(fn($w) => [
            'id' => $w->id,
            'good_pieces' => $w->good_pieces,
            'bad_pieces' => $w->bad_pieces,
            'weighed_at' => $w->weighed_at->format('d/m/Y H:i'),
            'weighed_by' => $w->weighedBy->name ?? 'N/A',
            'comments' => $w->comments,
        ])->toArray();

        // Quality weighings
        $this->qualityWeighings = $lot->qualityWeighings->map(fn($qw) => [
            'id' => $qw->id,
            'good_pieces' => $qw->good_pieces,
            'bad_pieces' => $qw->bad_pieces,
            'disposition' => $qw->disposition,
            'rework_status' => $qw->rework_status,
            'weighed_at' => $qw->weighed_at->format('d/m/Y H:i'),
            'weighed_by' => $qw->weighedBy->name ?? 'N/A',
            'comments' => $qw->comments,
        ])->toArray();

        // Totals
        $this->prodGoodTotal = $lot->getProductionGoodPieces();
        $this->prodBadTotal = $lot->getProductionBadPieces();
        $this->qualGoodTotal = $lot->getQualityGoodPieces();
        $this->qualBadTotal = $lot->getQualityBadPieces();
        $this->qualPending = $lot->getQualityPendingPieces();

        $this->showDetailModal = true;
    }

    public function closeDetailModal(): void
    {
        $this->showDetailModal = false;
        $this->selectedLotId = null;
        $this->selectedLot = null;
        $this->productionWeighings = [];
        $this->qualityWeighings = [];
        $this->prodGoodTotal = 0;
        $this->prodBadTotal = 0;
        $this->qualGoodTotal = 0;
        $this->qualBadTotal = 0;
        $this->qualPending = 0;
    }

    /**
     * Open weighing form from detail modal.
     */
    public function openWeighingModal(): void
    {
        if (!$this->selectedLot) return;

        $this->qualRemainingPieces = $this->qualPending;
        $this->qualGoodPieces = 0;
        $this->qualBadPieces = 0;
        $this->qualWeighedAt = now()->format('Y-m-d\TH:i');
        $this->qualComments = '';
        $this->qualIsCrimp = (bool) ($this->selectedLot->workOrder->purchaseOrder->part->is_crimp ?? true);
        $this->qualKitId = null;
        $this->qualKits = $this->qualIsCrimp ? $this->selectedLot->kits : collect([]);
        $this->showWeighingModal = true;
    }

    public function closeWeighingModal(): void
    {
        $this->showWeighingModal = false;
        $this->qualGoodPieces = 0;
        $this->qualBadPieces = 0;
        $this->qualWeighedAt = '';
        $this->qualComments = '';
        $this->qualKitId = null;
        $this->qualKits = [];
        $this->qualRemainingPieces = 0;
        $this->qualIsCrimp = true;
        $this->resetErrorBag();
    }

    public function saveQualityWeighing(): void
    {
        $this->validate([
            'qualGoodPieces' => 'required|integer|min:0',
            'qualBadPieces' => 'required|integer|min:0',
            'qualWeighedAt' => 'required|date',
            'qualComments' => 'nullable|string|max:1000',
        ], [
            'qualGoodPieces.required' => 'Las piezas aprobadas son requeridas.',
            'qualBadPieces.required' => 'Las piezas rechazadas son requeridas.',
            'qualWeighedAt.required' => 'La fecha y hora son requeridas.',
        ]);

        $total = $this->qualGoodPieces + $this->qualBadPieces;
        if ($total > $this->qualRemainingPieces) {
            $this->addError('qualGoodPieces', 'La suma (' . number_format($total) . ') sobrepasa las pendientes (' . number_format($this->qualRemainingPieces) . ').');
            return;
        }

        if ($total <= 0) {
            $this->addError('qualGoodPieces', 'Debe registrar al menos 1 pieza.');
            return;
        }

        $lot = Lot::find($this->selectedLotId);
        if (!$lot) {
            session()->flash('error', 'Lote no encontrado.');
            return;
        }

        QualityWeighing::create([
            'lot_id' => $lot->id,
            'kit_id' => $this->qualKitId ?: null,
            'production_good_pieces' => $lot->getProductionGoodPieces(),
            'good_pieces' => $this->qualGoodPieces,
            'bad_pieces' => $this->qualBadPieces,
            'disposition' => $this->qualBadPieces > 0 ? QualityWeighing::DISPOSITION_SCRAP : null,
            'rework_status' => null,
            'weighed_at' => $this->qualWeighedAt,
            'weighed_by' => auth()->id(),
            'comments' => $this->qualComments ?: null,
        ]);

        $message = 'Pesada de calidad registrada.';
        if ($this->qualBadPieces > 0) {
            $message .= ' ' . number_format($this->qualBadPieces) . ' piezas descartadas.';
        }

        session()->flash('message', $message);
        $this->closeWeighingModal();
        // Refresh the detail modal data
        $this->openDetailModal($this->selectedLotId);
    }

    /**
     * Delete a quality weighing.
     */
    public function deleteQualityWeighing(int $qualityWeighingId): void
    {
        $qw = QualityWeighing::find($qualityWeighingId);
        if ($qw) {
            $qw->delete();
            session()->flash('message', 'Pesada de calidad eliminada.');
            if ($this->selectedLotId) {
                $this->openDetailModal($this->selectedLotId);
            }
        }
    }

    public function render()
    {
        // Get lots that have production weighings (candidates for quality)
        $query = Lot::with([
            'workOrder.purchaseOrder.part',
            'kits',
            'weighings',
            'qualityWeighings',
        ])
        ->whereHas('weighings') // Only lots that have production weighings
        ->search($this->search);

        // Filter by quality status
        if ($this->filterQualityStatus === 'pending') {
            // Has production weighings but quality hasn't fully verified
            $query->whereHas('weighings')
                ->where(function ($q) {
                    $q->whereDoesntHave('qualityWeighings')
                        ->orWhereRaw('(SELECT COALESCE(SUM(good_pieces),0) + COALESCE(SUM(bad_pieces),0) FROM quality_weighings WHERE quality_weighings.lot_id = lots.id AND quality_weighings.deleted_at IS NULL) < (SELECT COALESCE(SUM(good_pieces),0) FROM weighings WHERE weighings.lot_id = lots.id AND weighings.deleted_at IS NULL)');
                });
        } elseif ($this->filterQualityStatus === 'completed') {
            // Quality has verified all production good pieces
            $query->whereRaw('(SELECT COALESCE(SUM(good_pieces),0) + COALESCE(SUM(bad_pieces),0) FROM quality_weighings WHERE quality_weighings.lot_id = lots.id AND quality_weighings.deleted_at IS NULL) >= (SELECT COALESCE(SUM(good_pieces),0) FROM weighings WHERE weighings.lot_id = lots.id AND weighings.deleted_at IS NULL)');
        } elseif ($this->filterQualityStatus === 'rejected') {
            // Has rejected/discarded pieces
            $query->whereHas('qualityWeighings', function ($q) {
                $q->where('bad_pieces', '>', 0);
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);
        $lots = $query->paginate($this->perPage);

        // Statistics
        $totalWithProd = Lot::whereHas('weighings')->count();
        $pendingQuality = Lot::whereHas('weighings')
            ->where(function ($q) {
                $q->whereDoesntHave('qualityWeighings')
                    ->orWhereRaw('(SELECT COALESCE(SUM(good_pieces),0) + COALESCE(SUM(bad_pieces),0) FROM quality_weighings WHERE quality_weighings.lot_id = lots.id AND quality_weighings.deleted_at IS NULL) < (SELECT COALESCE(SUM(good_pieces),0) FROM weighings WHERE weighings.lot_id = lots.id AND weighings.deleted_at IS NULL)');
            })->count();
        $completedQuality = Lot::whereHas('weighings')
            ->whereRaw('(SELECT COALESCE(SUM(good_pieces),0) + COALESCE(SUM(bad_pieces),0) FROM quality_weighings WHERE quality_weighings.lot_id = lots.id AND quality_weighings.deleted_at IS NULL) >= (SELECT COALESCE(SUM(good_pieces),0) FROM weighings WHERE weighings.lot_id = lots.id AND weighings.deleted_at IS NULL)')
            ->count();
        $withRejected = Lot::whereHas('qualityWeighings', function ($q) {
            $q->where('bad_pieces', '>', 0);
        })->count();

        $stats = [
            'total' => $totalWithProd,
            'pending' => $pendingQuality,
            'completed' => $completedQuality,
            'rejected' => $withRejected,
        ];

        return view('livewire.admin.quality.quality-weighings', [
            'lots' => $lots,
            'stats' => $stats,
        ])->layout('components.layouts.app');
    }
}
