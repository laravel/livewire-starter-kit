<?php

namespace App\Livewire\Admin\WorkOrders;

use App\Models\StatusWO;
use App\Models\WorkOrder;
use App\Services\PurchaseOrderService;
use Livewire\Component;

class WOEdit extends Component
{
    public WorkOrder $workOrder;

    public int $status_id;
    public ?string $scheduled_send_date = null;
    public ?string $actual_send_date = null;
    public ?string $eq = null;
    public ?string $pr = null;
    public ?string $comments = null;
    public ?string $status_change_comments = null;

    protected PurchaseOrderService $purchaseOrderService;

    protected function rules(): array
    {
        return [
            'status_id' => 'required|exists:statuses_wo,id',
            'scheduled_send_date' => 'nullable|date',
            'actual_send_date' => 'nullable|date',
            'eq' => 'nullable|string|max:255',
            'pr' => 'nullable|string|max:255',
            'comments' => 'nullable|string',
        ];
    }

    public function boot(PurchaseOrderService $purchaseOrderService): void
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function mount(WorkOrder $workOrder): void
    {
        $this->workOrder = $workOrder->load(['purchaseOrder.part', 'status']);
        $this->status_id = $workOrder->status_id;
        $this->scheduled_send_date = $workOrder->scheduled_send_date?->format('Y-m-d');
        $this->actual_send_date = $workOrder->actual_send_date?->format('Y-m-d');
        $this->eq = $workOrder->eq;
        $this->pr = $workOrder->pr;
        $this->comments = $workOrder->comments;
    }

    public function save(): void
    {
        $this->validate();

        $statusChanged = $this->workOrder->status_id !== $this->status_id;

        // Update the work order
        $this->workOrder->update([
            'scheduled_send_date' => $this->scheduled_send_date,
            'actual_send_date' => $this->actual_send_date,
            'eq' => $this->eq,
            'pr' => $this->pr,
            'comments' => $this->comments,
        ]);

        // If status changed, use the service to log it
        if ($statusChanged) {
            $this->purchaseOrderService->updateWorkOrderStatus(
                $this->workOrder,
                $this->status_id,
                $this->status_change_comments
            );
        }

        session()->flash('flash.banner', 'Work Order actualizada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.work-orders.show', $this->workOrder));
    }

    public function render()
    {
        return view('livewire.admin.work-orders.wo-edit', [
            'statuses' => StatusWO::all(),
        ]);
    }
}
