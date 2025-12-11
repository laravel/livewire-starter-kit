<?php

namespace App\Livewire\Admin\WorkOrders;

use App\Models\StatusWO;
use App\Models\WorkOrder;
use App\Services\PurchaseOrderService;
use Livewire\Component;

class WOShow extends Component
{
    public WorkOrder $workOrder;

    protected PurchaseOrderService $purchaseOrderService;

    public function boot(PurchaseOrderService $purchaseOrderService): void
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder->load([
            'purchaseOrder.part',
            'status',
            'statusLogs.fromStatus',
            'statusLogs.toStatus',
            'statusLogs.user',
        ]);
    }

    public function updateStatus(int $statusId, ?string $comments = null): void
    {
        $this->purchaseOrderService->updateWorkOrderStatus($this->workOrder, $statusId, $comments);

        session()->flash('flash.banner', 'Estado actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->workOrder = $this->workOrder->fresh([
            'purchaseOrder.part',
            'status',
            'statusLogs.fromStatus',
            'statusLogs.toStatus',
            'statusLogs.user',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.work-orders.wo-show', [
            'statuses' => StatusWO::all(),
        ]);
    }
}
