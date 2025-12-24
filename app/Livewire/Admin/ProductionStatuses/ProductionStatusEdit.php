<?php

namespace App\Livewire\Admin\ProductionStatuses;

use App\Models\ProductionStatus;
use Livewire\Component;

class ProductionStatusEdit extends Component
{
    public ProductionStatus $productionStatus;
    public string $name = '';
    public string $color = '';
    public string $order = '';
    public bool $active = true;
    public string $description = '';

    public function mount(ProductionStatus $productionStatus): void
    {
        $this->productionStatus = $productionStatus;
        $this->name = $productionStatus->name;
        $this->color = $productionStatus->color;
        $this->order = $productionStatus->order;
        $this->active = $productionStatus->active;
        $this->description = $productionStatus->description ?? '';
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:production_statuses,name,' . $this->productionStatus->id,
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'order' => 'required|integer|min:0',
            'active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function update()
    {
        $this->validate();

        $this->productionStatus->update([
            'name' => $this->name,
            'color' => $this->color,
            'order' => $this->order,
            'active' => $this->active,
            'description' => $this->description ?: null,
        ]);

        session()->flash('flash.banner', 'Estado de producción actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.production-statuses.index');
    }

    public function render()
    {
        return view('livewire.admin.production-statuses.production-status-edit');
    }
}
