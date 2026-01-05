<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Table;
use App\Models\Area;
use App\Models\ProductionStatus;
use Livewire\Component;

class TableEdit extends Component
{
    public Table $table;
    public string $number = '';
    public string $name = '';
    public string $employees = '';
    public bool $active = true;
    public string $comments = '';
    public string $area_id = '';
    public string $production_status_id = '';
    public string $standard_id = '';
    public string $brand = '';
    public string $model = '';
    public string $s_n = '';
    public string $asset_number = '';
    public string $description = '';

    public function mount(Table $table): void
    {
        $this->table = $table;
        $this->number = $table->number;
        $this->name = $table->name ?? '';
        $this->employees = $table->employees ?? '';
        $this->active = $table->active;
        $this->comments = $table->comments ?? '';
        $this->area_id = $table->area_id;
        $this->production_status_id = $table->production_status_id ?? '';
        $this->standard_id = $table->standard_id ?? '';
        $this->brand = $table->brand ?? '';
        $this->model = $table->model ?? '';
        $this->s_n = $table->s_n ?? '';
        $this->asset_number = $table->asset_number ?? '';
        $this->description = $table->description ?? '';
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string|max:255|unique:tables,number,' . $this->table->id,
            'name' => 'nullable|string|max:255',
            'employees' => 'nullable|integer|min:1',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
            'production_status_id' => 'required|exists:production_statuses,id',
            'standard_id' => 'nullable|exists:standards,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            's_n' => 'nullable|string',
            'asset_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function update()
    {
        $this->validate();

        $this->table->update([
            'number' => $this->number,
            'name' => $this->name ?: null,
            'employees' => $this->employees ?: null,
            'active' => $this->active,
            'comments' => $this->comments ?: null,
            'area_id' => $this->area_id,
            'production_status_id' => $this->production_status_id,
            'standard_id' => $this->standard_id ?: null,
            'brand' => $this->brand ?: null,
            'model' => $this->model ?: null,
            's_n' => $this->s_n ?: null,
            'asset_number' => $this->asset_number ?: null,
            'description' => $this->description ?: null,
        ]);

        session()->flash('flash.banner', 'Mesa actualizada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.tables.index');
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get();
        $productionStatuses = ProductionStatus::active()->ordered()->get();

        return view('livewire.admin.tables.table-edit', compact('areas', 'productionStatuses'));
    }
}
