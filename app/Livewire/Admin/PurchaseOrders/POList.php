<?php

namespace App\Livewire\Admin\PurchaseOrders;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Livewire\Component;
use Livewire\WithPagination;

class POList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'po_date';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public string $filterStatus = 'all';

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

    public function deletePO(int $id): void
    {
        $po = PurchaseOrder::findOrFail($id);
        try {
            $po->forceDeleteWithRelations();
            session()->flash('flash.banner', 'Orden de compra y registros relacionados eliminados correctamente.');
            session()->flash('flash.bannerStyle', 'success');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar la orden de compra: ' . $e->getMessage());
        }
    }

    public function approve(int $id): void
    {
        $po = PurchaseOrder::findOrFail($id);
        $result = $this->purchaseOrderService->approveAndCreateWO($po);

        if ($result['success']) {
            session()->flash('flash.banner', $result['message']);
            session()->flash('flash.bannerStyle', 'success');
        } else {
            session()->flash('error', $result['message']);
        }
    }

    public function reject(int $id): void
    {
        $po = PurchaseOrder::findOrFail($id);
        $this->purchaseOrderService->reject($po, 'Rechazada por el usuario.');

        session()->flash('flash.banner', 'Orden de compra rechazada.');
        session()->flash('flash.bannerStyle', 'warning');
    }

    public function render()
    {
        $query = PurchaseOrder::with('part')
            ->search($this->search)
            ->filterByStatus($this->filterStatus);

        $purchaseOrders = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $totalPOs = PurchaseOrder::count();
        $pendingPOs = PurchaseOrder::pending()->count();
        $approvedPOs = PurchaseOrder::approved()->count();
        $pendingCorrectionPOs = PurchaseOrder::pendingCorrection()->count();

        return view('livewire.admin.purchase-orders.po-list', [
            'purchaseOrders' => $purchaseOrders,
            'statuses' => PurchaseOrder::getStatuses(),
            'totalPOs' => $totalPOs,
            'pendingPOs' => $pendingPOs,
            'approvedPOs' => $approvedPOs,
            'pendingCorrectionPOs' => $pendingCorrectionPOs,
        ]);
    }
}
