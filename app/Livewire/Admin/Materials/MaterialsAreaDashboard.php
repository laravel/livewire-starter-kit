<?php

namespace App\Livewire\Admin\Materials;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\WorkOrder;
use App\Models\Lot;
use App\Models\Kit;

#[Layout('components.layouts.app')]
class MaterialsAreaDashboard extends Component
{
    public string $viewMode = 'work-orders'; // Default to work-orders view
    public string $searchTerm = '';

    /**
     * Switch between different views.
     */
    public function switchView(string $mode): void
    {
        $this->viewMode = $mode;
        $this->dispatch('view-switched', mode: $mode);
    }

    /**
     * Get statistics for the dashboard.
     */
    public function getStatsProperty(): array
    {
        return [
            'total_work_orders' => WorkOrder::whereHas('lots')->count(),
            'total_lots' => Lot::count(),
            'pending_lots' => Lot::where('status', 'pending')->count(),
            'total_kits' => Kit::count(),
            'kits_preparing' => Kit::where('status', 'preparing')->count(),
            'kits_pending_quality' => Kit::where('status', 'pending_quality')->count(),
        ];
    }

    /**
     * Render the component.
     */
    public function render()
    {
        return view('livewire.admin.materials.materials-area-dashboard', [
            'stats' => $this->stats,
        ]);
    }
}
