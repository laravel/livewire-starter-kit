<?php

namespace App\Livewire\Admin\Materials;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WorkOrder;
use App\Models\Lot;
use App\Models\Kit;

class DynamicSentListView extends Component
{
    use WithPagination;

    public string $searchTerm = '';
    public string $filterStatus = '';

    /**
     * React to search term changes.
     */
    public function updatedSearchTerm(): void
    {
        $this->resetPage();
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->filterStatus = '';
        $this->searchTerm = '';
        $this->resetPage();
    }

    /**
     * Get lot count for a work order.
     */
    public function getWorkOrderLotCount(int $workOrderId): int
    {
        return Lot::where('work_order_id', $workOrderId)->count();
    }

    /**
     * Get kit count for a work order.
     */
    public function getWorkOrderKitCount(int $workOrderId): int
    {
        return Kit::where('work_order_id', $workOrderId)->count();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $query = WorkOrder::with(['purchaseOrder.part', 'lots', 'kits'])
            ->whereHas('lots'); // Solo WOs que tengan lotes

        // Apply search
        if (!empty($this->searchTerm)) {
            $query->where(function ($q) {
                $q->where('wo_number', 'like', "%{$this->searchTerm}%")
                  ->orWhereHas('purchaseOrder', function ($poQuery) {
                      $poQuery->where('po_number', 'like', "%{$this->searchTerm}%")
                              ->orWhere('customer', 'like', "%{$this->searchTerm}%");
                  })
                  ->orWhereHas('purchaseOrder.part', function ($partQuery) {
                      $partQuery->where('name', 'like', "%{$this->searchTerm}%")
                               ->orWhere('number', 'like', "%{$this->searchTerm}%");
                  });
            });
        }

        // Apply status filter
        if (!empty($this->filterStatus)) {
            $query->whereHas('lots', function ($q) {
                $q->where('status', $this->filterStatus);
            });
        }

        $workOrders = $query->latest()->paginate(15);

        return view('livewire.admin.materials.dynamic-sent-list-view', [
            'workOrders' => $workOrders,
        ]);
    }
}
