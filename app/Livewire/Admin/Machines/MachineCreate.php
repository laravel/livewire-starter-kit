<?php

namespace App\Livewire\Admin\Machines;

use App\Models\Machine;
use App\Models\Area;
use Livewire\Component;

class MachineCreate extends Component
{
    public string $name = '';
    public string $brand = '';
    public string $model = '';
    public string $sn = '';
    public string $asset_number = '';
    public string $employees = '';
    public string $setup_time = '';
    public string $maintenance_time = '';
    public bool $active = true;
    public string $comments = '';
    public string $area_id = '';

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'sn' => 'nullable|string|max:255',
            'asset_number' => 'nullable|string|max:255',
            'employees' => 'nullable|integer|min:1',
            'setup_time' => 'nullable|numeric|min:0',
            'maintenance_time' => 'nullable|numeric|min:0',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
        ];
    }

    public function save()
    {
        $this->validate();

        Machine::create([
            'name' => $this->name,
            'brand' => $this->brand ?: null,
            'model' => $this->model ?: null,
            'sn' => $this->sn ?: null,
            'asset_number' => $this->asset_number ?: null,
            'employees' => $this->employees ?: null,
            'setup_time' => $this->setup_time ?: null,
            'maintenance_time' => $this->maintenance_time ?: null,
            'active' => $this->active,
            'comments' => $this->comments ?: null,
            'area_id' => $this->area_id,
        ]);

        session()->flash('flash.banner', 'Máquina creada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.machines.index');
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get();

        return view('livewire.admin.machines.machine-create', compact('areas'));
    }
}
