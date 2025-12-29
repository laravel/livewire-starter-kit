<?php

namespace App\Livewire\Admin\Kits;

use App\Models\Kit;
use App\Models\WorkOrder;
use Livewire\Component;
use Livewire\Attributes\Layout;

class KitCreate extends Component
{
    public ?int $work_order_id = null;
    public string $validation_notes = '';

    protected function rules(): array
    {
        return [
            'work_order_id' => 'required|exists:work_orders,id',
            'validation_notes' => 'nullable|string|max:1000',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $kit = Kit::create([
            'work_order_id' => $this->work_order_id,
            'kit_number' => Kit::generateKitNumber($this->work_order_id),
            'status' => Kit::STATUS_PREPARING,
            'validated' => false,
            'validation_notes' => $this->validation_notes,
        ]);

        session()->flash('message', 'Kit creado correctamente.');
        $this->redirect(route('admin.kits.show', $kit), navigate: true);
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
            ->get();

        return view('livewire.admin.kits.kit-create', [
            'workOrders' => $workOrders,
        ]);
    }
}
