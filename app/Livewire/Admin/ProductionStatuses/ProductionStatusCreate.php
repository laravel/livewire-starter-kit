<?php

namespace App\Livewire\Admin\ProductionStatuses;

use App\Models\ProductionStatus;
use Livewire\Component;

class ProductionStatusCreate extends Component
{
    public string $name = '';
    public string $color = '#10b981';
    public string $order = '';
    public bool $active = true;
    public string $description = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:production_statuses,name',
            'color' => 'required|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
            'order' => 'required|integer|min:0',
            'active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function save()
    {
        $this->validate();

        ProductionStatus::create([
            'name' => $this->name,
            'color' => $this->color,
            'order' => $this->order,
            'active' => $this->active,
            'description' => $this->description ?: null,
        ]);

        session()->flash('flash.banner', 'Estado de producción creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.production-statuses.index');
    }

    public function render()
    {
        return view('livewire.admin.production-statuses.production-status-create');
    }
}
