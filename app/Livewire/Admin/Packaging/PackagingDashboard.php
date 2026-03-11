<?php

namespace App\Livewire\Admin\Packaging;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Lot;
use App\Models\PackagingRecord;
use App\Models\WorkOrder;
use App\Models\SentList;

#[Layout('components.layouts.app')]
class PackagingDashboard extends Component
{
    public function render()
    {
        // Lots with packaging records
        $lotsWithPackaging = Lot::whereHas('packagingRecords')->count();

        // Lots pending packaging (have quality weighings but no packaging yet)
        $lotsPendingPackaging = Lot::whereHas('qualityWeighings')
            ->whereDoesntHave('packagingRecords')
            ->where('status', '!=', Lot::STATUS_COMPLETED)
            ->count();

        // Lots with viajero received but no closure decision
        $lotsPendingDecision = Lot::where('viajero_received', true)
            ->whereNull('closure_decision')
            ->count();

        // Lots with closure decision but surplus not received
        $lotsPendingSurplus = Lot::whereNotNull('closure_decision')
            ->where(function ($q) {
                $q->whereNull('surplus_received')->orWhere('surplus_received', false);
            })
            ->count();

        // Completed packaging lots
        $lotsCompleted = Lot::where('packaging_status', 'approved')->count();

        // Total packaging records
        $totalRecords = PackagingRecord::count();

        // Total packed pieces
        $totalPackedPieces = PackagingRecord::sum('packed_pieces');

        // Total surplus pieces
        $totalSurplusPieces = PackagingRecord::sum('surplus_pieces');

        // Recent packaging records
        $recentRecords = PackagingRecord::with(['lot.workOrder.purchaseOrder.part', 'packedBy'])
            ->orderByDesc('packed_at')
            ->limit(10)
            ->get();

        // Lots in packaging flow (have records, not completed)
        $lotsInProgress = Lot::with(['workOrder.purchaseOrder.part', 'packagingRecords'])
            ->whereHas('packagingRecords')
            ->where('packaging_status', '!=', 'approved')
            ->orderByDesc('updated_at')
            ->limit(15)
            ->get();

        $pendingSentLists = SentList::with(['workOrders.purchaseOrder.part', 'workOrders.lots.packagingRecords'])
            ->where('current_department', SentList::DEPT_SHIPPING)
            ->where('status', SentList::STATUS_PENDING)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.admin.packaging.packaging-dashboard', [
            'pendingSentLists'  => $pendingSentLists,
            'lotsWithPackaging' => $lotsWithPackaging,
            'lotsPendingPackaging' => $lotsPendingPackaging,
            'lotsPendingDecision' => $lotsPendingDecision,
            'lotsPendingSurplus' => $lotsPendingSurplus,
            'lotsCompleted' => $lotsCompleted,
            'totalRecords' => $totalRecords,
            'totalPackedPieces' => $totalPackedPieces,
            'totalSurplusPieces' => $totalSurplusPieces,
            'recentRecords' => $recentRecords,
            'lotsInProgress' => $lotsInProgress,
        ]);
    }
}
