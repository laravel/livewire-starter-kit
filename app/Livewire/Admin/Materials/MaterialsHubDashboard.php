<?php

namespace App\Livewire\Admin\Materials;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\WorkOrder;
use App\Models\Lot;
use App\Models\Kit;
use App\Models\SentList;

#[Layout('components.layouts.app')]
class MaterialsHubDashboard extends Component
{
    public function render()
    {
        // ── Work Order metrics ──
        $totalWOs = WorkOrder::whereHas('lots')->count();
        $activeWOs = WorkOrder::whereHas('purchaseOrder', fn($q) => $q->where('status', 'active'))
            ->whereHas('lots')
            ->count();
        $closedWOs = WorkOrder::whereHas('purchaseOrder', fn($q) => $q->where('status', 'closed'))
            ->whereHas('lots')
            ->count();

        // ── Lot metrics ──
        $totalLots = Lot::count();
        $pendingLots = Lot::where('status', 'pending')->count();
        $inProgressLots = Lot::where('status', 'in_progress')->count();
        $completedLots = Lot::where('status', 'completed')->count();

        // ── Kit metrics ──
        $totalKits = Kit::count();
        $kitsPreparing = Kit::where('status', Kit::STATUS_PREPARING)->count();
        $kitsReady = Kit::where('status', Kit::STATUS_READY)->count();
        $kitsReleased = Kit::where('status', Kit::STATUS_RELEASED)->count();
        $kitsInAssembly = Kit::where('status', Kit::STATUS_IN_ASSEMBLY)->count();
        $kitsRejected = Kit::where('status', Kit::STATUS_REJECTED)->count();

        // ── Sent List metrics ──
        $totalSentLists = SentList::count();
        $recentSentLists = SentList::with(['workOrders'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('livewire.admin.materials.materials-hub-dashboard', [
            'totalWOs' => $totalWOs,
            'activeWOs' => $activeWOs,
            'closedWOs' => $closedWOs,
            'totalLots' => $totalLots,
            'pendingLots' => $pendingLots,
            'inProgressLots' => $inProgressLots,
            'completedLots' => $completedLots,
            'totalKits' => $totalKits,
            'kitsPreparing' => $kitsPreparing,
            'kitsReady' => $kitsReady,
            'kitsReleased' => $kitsReleased,
            'kitsInAssembly' => $kitsInAssembly,
            'kitsRejected' => $kitsRejected,
            'totalSentLists' => $totalSentLists,
            'recentSentLists' => $recentSentLists,
        ]);
    }
}
