<?php

namespace App\Livewire\Admin\PurchaseOrders;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Livewire\Component;

class POShow extends Component
{
    public PurchaseOrder $purchaseOrder;
    public ?float $expected_price = null;
    public bool $price_valid = false;
    public string $price_message = '';

    protected PurchaseOrderService $purchaseOrderService;

    public function boot(PurchaseOrderService $purchaseOrderService): void
    {
        $this->purchaseOrderService = $purchaseOrderService;
    }

    public function mount(PurchaseOrder $purchaseOrder): void
    {
        $this->purchaseOrder = $purchaseOrder->load(['part', 'workOrder']);
        $this->validatePrice();
    }

    protected function validatePrice(): void
    {
        $validation = $this->purchaseOrderService->validatePrice($this->purchaseOrder);
        $this->expected_price = $validation['expected_price'];
        $this->price_valid = $validation['valid'];
        $this->price_message = $validation['message'];
    }

    public function approve(): void
    {
        $result = $this->purchaseOrderService->approveAndCreateWO($this->purchaseOrder);

        if ($result['success']) {
            session()->flash('flash.banner', $result['message']);
            session()->flash('flash.bannerStyle', 'success');
        } else {
            session()->flash('error', $result['message']);
        }

        $this->purchaseOrder = $this->purchaseOrder->fresh(['part', 'workOrder']);
        $this->validatePrice();
    }

    public function reject(): void
    {
        $this->purchaseOrderService->reject($this->purchaseOrder, 'Rechazada por el usuario.');

        session()->flash('flash.banner', 'Orden de compra rechazada.');
        session()->flash('flash.bannerStyle', 'warning');

        $this->purchaseOrder = $this->purchaseOrder->fresh(['part', 'workOrder']);
    }

    public function render()
    {
        return view('livewire.admin.purchase-orders.po-show');
    }
}
