<?php

namespace App\Livewire\Admin;

use App\Models\Kit;
use App\Models\Lot;
use App\Models\Part;
use App\Models\PurchaseOrder;
use App\Models\SentList;
use App\Models\User;
use App\Models\WorkOrder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]

class AdminDashboard extends Component
{
    public function render()
    {
        // ── KPIs ─────────────────────────────────────────────────────────
        $totalWO        = WorkOrder::count();
        $totalPO        = PurchaseOrder::count();
        $totalParts     = Part::count();
        $totalUsers     = User::count();

        // ── Sent Lists ───────────────────────────────────────────────────
        $sentLists       = SentList::with(['workOrders.purchaseOrder.part', 'purchaseOrders.part'])
            ->latest()
            ->take(10)
            ->get();

        $sentListsByDept = SentList::selectRaw('current_department, count(*) as total')
            ->groupBy('current_department')
            ->pluck('total', 'current_department');

        $sentListsByStatus = SentList::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $activeSentLists = SentList::whereIn('status', [SentList::STATUS_PENDING])
            ->count();

        // ── Work Orders ──────────────────────────────────────────────────
        $recentWorkOrders = WorkOrder::with(['purchaseOrder.part', 'status'])
            ->latest()
            ->take(8)
            ->get();

        // ── Lots ─────────────────────────────────────────────────────────
        $lotsByStatus = Lot::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalLots      = Lot::count();
        $lotsInProgress = (int) ($lotsByStatus['in_progress'] ?? 0);
        $lotsCompleted  = (int) ($lotsByStatus['completed'] ?? 0);
        $lotsPending    = (int) ($lotsByStatus['pending'] ?? 0);

        // ── Kits ─────────────────────────────────────────────────────────
        $kitsByStatus = Kit::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalKits = Kit::count();

        // ── Pipeline depts ───────────────────────────────────────────────
        $pipeline = [
            SentList::DEPT_MATERIALS  => ['label' => 'Materiales',  'color' => 'blue',   'icon' => 'archive-box'],
            SentList::DEPT_INSPECTION => ['label' => 'Inspección',  'color' => 'yellow', 'icon' => 'magnifying-glass'],
            SentList::DEPT_PRODUCTION => ['label' => 'Producción',  'color' => 'indigo', 'icon' => 'cog-6-tooth'],
            SentList::DEPT_QUALITY    => ['label' => 'Calidad',     'color' => 'green',  'icon' => 'shield-check'],
            SentList::DEPT_SHIPPING   => ['label' => 'Empaque',     'color' => 'orange', 'icon' => 'truck'],
        ];

        foreach ($pipeline as $dept => &$info) {
            $info['count'] = (int) ($sentListsByDept[$dept] ?? 0);
        }
        unset($info);

        return view('livewire.admin.admin-dashboard', compact(
            'totalWO', 'totalPO', 'totalParts', 'totalUsers',
            'sentLists', 'sentListsByDept', 'sentListsByStatus', 'activeSentLists',
            'recentWorkOrders',
            'lotsByStatus', 'totalLots', 'lotsInProgress', 'lotsCompleted', 'lotsPending',
            'kitsByStatus', 'totalKits',
            'pipeline'
        ));
    }
}
