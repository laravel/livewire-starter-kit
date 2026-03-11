<?php

namespace App\Livewire\Admin\WorkOrders;

use App\Models\StatusWO;
use App\Models\WorkOrder;
use App\Services\PurchaseOrderService;
use Livewire\Component;
use Livewire\WithPagination;

class WOList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'opened_date';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public ?int $filterStatus = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    protected PurchaseOrderService $purchaseOrderService;

    public function boot(PurchaseOrderService $purchaseOrderService): void
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function deleteWorkOrder(int $id): void
    {
        $wo = WorkOrder::findOrFail($id);
        try {
            $wo->forceDeleteWithRelations();
            session()->flash('flash.banner', 'Work Order y registros relacionados eliminados correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la Work Order: ' . $e->getMessage());
        }
    }

    public function updateStatus(int $id, int $statusId): void
    {
        $wo = WorkOrder::findOrFail($id);
        $this->purchaseOrderService->updateWorkOrderStatus($wo, $statusId);

        session()->flash('flash.banner', 'Estado actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function clearFilters(): void
    {
        $this->reset(['filterStatus', 'startDate', 'endDate', 'search']);
        $this->resetPage();
    }

    public function render()
    {
        $query = WorkOrder::with(['purchaseOrder.part', 'status'])
            ->search($this->search)
            ->filterByStatus($this->filterStatus)
            ->filterByDateRange($this->startDate, $this->endDate);

        $workOrders = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $totalWOs = WorkOrder::count();

        return view('livewire.admin.work-orders.wo-list', [
            'workOrders' => $workOrders,
            'statuses' => StatusWO::all(),
            'totalWOs' => $totalWOs,
        ]);
    }
}
