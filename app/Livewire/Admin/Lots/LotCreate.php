<?php

namespace App\Livewire\Admin\Lots;

use App\Models\Lot;
use App\Models\WorkOrder;
use Livewire\Component;

class LotCreate extends Component
{
    public ?int $work_order_id = null;
    public string $lot_number = '';
    public int $quantity = 0;

    protected function rules(): array
    {
        return [
            'work_order_id' => 'required|exists:work_orders,id',
            'lot_number' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ];
    }

    public function mount(?int $workOrderId = null): void
    {
        $this->work_order_id = $workOrderId;
    }

    public function save(): void
    {
        $this->validate();

        // Validate quantity doesn't exceed pending
        $workOrder = WorkOrder::find($this->work_order_id);
        if ($this->quantity > $workOrder->pending_quantity) {
            $this->addError('quantity', 'La cantidad no puede exceder la cantidad pendiente (' . number_format($workOrder->pending_quantity) . ')');
            return;
        }

        $lot = Lot::create([
            'work_order_id' => $this->work_order_id,
            'lot_number' => $this->lot_number,
            'quantity' => $this->quantity,
            'description' => $workOrder->purchaseOrder->part->description,
            'status' => Lot::STATUS_PENDING,
        ]);

        session()->flash('message', 'Lote creado correctamente.');
        $this->redirect(route('admin.lots.show', $lot), navigate: true);
    }

    public function render()
    {
        $workOrders = WorkOrder::with('purchaseOrder.part')
            ->whereIn('status_id', function ($query) {
                $query->select('id')
                    ->from('statuses_wo')
                    ->whereIn('name', ['Open', 'In Progress']);
            })
            ->orderBy('wo_number', 'desc')
            ->get()
            ->filter(fn($wo) => $wo->pending_quantity > 0);

        $selectedWorkOrder = $this->work_order_id 
            ? WorkOrder::with('purchaseOrder.part')->find($this->work_order_id) 
            : null;

        return view('livewire.admin.lots.lot-create', [
            'workOrders' => $workOrders,
            'selectedWorkOrder' => $selectedWorkOrder,
        ]);
    }
}
