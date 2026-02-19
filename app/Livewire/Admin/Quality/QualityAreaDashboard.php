<?php

namespace App\Livewire\Admin\Quality;

use App\Models\Lot;
use App\Models\Kit;
use App\Models\QualityWeighing;
use App\Models\WorkOrder;
use App\Models\PurchaseOrder;
use Livewire\Component;

class QualityAreaDashboard extends Component
{
    public function render()
    {
        // ── WO metrics ──
        $totalWOs = WorkOrder::count();
        $activeWOs = WorkOrder::whereHas('purchaseOrder', fn($q) => $q->where('status', 'active'))
            ->count();
        $closedWOs = WorkOrder::whereHas('purchaseOrder', fn($q) => $q->where('status', 'closed'))
            ->count();

        // ── Inspection metrics ──
        $pendingInspection = Lot::whereHas('kits', fn($q) => $q->where('status', Kit::STATUS_RELEASED))
            ->where('inspection_status', Lot::INSPECTION_PENDING)
            ->count();
        $approvedInspection = Lot::where('inspection_status', Lot::INSPECTION_APPROVED)->count();
        $rejectedInspection = Lot::where('inspection_status', Lot::INSPECTION_REJECTED)->count();

        // ── Quality weighing metrics ──
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

        // ── Recent quality weighings ──
        $recentQualityWeighings = QualityWeighing::with(['lot.workOrder.purchaseOrder.part', 'weighedBy'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('livewire.admin.quality.quality-area-dashboard', [
            'totalWOs' => $totalWOs,
            'activeWOs' => $activeWOs,
            'closedWOs' => $closedWOs,
            'pendingInspection' => $pendingInspection,
            'approvedInspection' => $approvedInspection,
            'rejectedInspection' => $rejectedInspection,
            'totalWithProd' => $totalWithProd,
            'pendingQuality' => $pendingQuality,
            'completedQuality' => $completedQuality,
            'withRejected' => $withRejected,
            'recentQualityWeighings' => $recentQualityWeighings,
        ])->layout('components.layouts.app');
    }
}
