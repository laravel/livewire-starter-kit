<?php

namespace App\Livewire\Admin\Production;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Weighing;
use App\Models\QualityWeighing;
use App\Models\Lot;
use App\Models\Kit;
use Illuminate\Support\Facades\DB;

#[Layout('components.layouts.app')]
class ProductionHubDashboard extends Component
{
    public function render()
    {
        // ── Weighing metrics ──
        $totalWeighings = Weighing::count();
        $totalPiecesWeighed = Weighing::sum('good_pieces');

        // ── Lots with weighings ──
        $lotsWithWeighings = Lot::whereHas('weighings')->count();
        $lotsFullyWeighed = Lot::whereHas('weighings')
            ->whereRaw('(SELECT COALESCE(SUM(good_pieces),0) + COALESCE(SUM(bad_pieces),0) FROM weighings WHERE weighings.lot_id = lots.id AND weighings.deleted_at IS NULL) >= lots.quantity')
            ->count();
        $lotsPendingWeighing = $lotsWithWeighings - $lotsFullyWeighed;
        $lotsWithoutWeighings = Lot::whereDoesntHave('weighings')
            ->where('status', '!=', 'completed')
            ->count();

        // ── Rejected by Quality (discarded) ──
        $rejectedPieces = (int) QualityWeighing::sum('bad_pieces');
        $rejectedLots = Lot::whereHas('qualityWeighings', function ($q) {
            $q->where('bad_pieces', '>', 0);
        })->count();

        // ── Today's activity ──
        $todayWeighings = Weighing::whereDate('weighed_at', today())->count();
        $todayPiecesWeighed = Weighing::whereDate('weighed_at', today())->sum('good_pieces');

        // ── Recent weighings ──
        $recentWeighings = Weighing::with(['lot.workOrder.purchaseOrder.part', 'kit', 'weighedBy'])
            ->orderBy('weighed_at', 'desc')
            ->limit(10)
            ->get();

        // ── Top operators (last 30 days) ──
        $topOperators = Weighing::select('weighed_by', DB::raw('COUNT(*) as total_weighings'), DB::raw('SUM(good_pieces) as total_good'))
            ->where('weighed_at', '>=', now()->subDays(30))
            ->whereNotNull('weighed_by')
            ->groupBy('weighed_by')
            ->orderByDesc('total_weighings')
            ->limit(5)
            ->with('weighedBy')
            ->get();

        return view('livewire.admin.production.production-hub-dashboard', [
            'totalWeighings' => $totalWeighings,
            'totalPiecesWeighed' => $totalPiecesWeighed,
            'lotsWithWeighings' => $lotsWithWeighings,
            'lotsFullyWeighed' => $lotsFullyWeighed,
            'lotsPendingWeighing' => $lotsPendingWeighing,
            'lotsWithoutWeighings' => $lotsWithoutWeighings,
            'rejectedPieces' => $rejectedPieces,
            'rejectedLots' => $rejectedLots,
            'todayWeighings' => $todayWeighings,
            'todayPiecesWeighed' => $todayPiecesWeighed,
            'recentWeighings' => $recentWeighings,
            'topOperators' => $topOperators,
        ]);
    }
}
