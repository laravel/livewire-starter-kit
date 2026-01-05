<?php

namespace App\Livewire\Admin\Tables;

use App\Models\Table;
use App\Models\Area;
use Livewire\Component;

class TableCreate extends Component
{
    public string $number = '';
    public string $employees = '';
    public bool $active = true;
    public string $comments = '';
    public string $area_id = '';

    public function rules(): array
    {
        return [
            'number' => 'required|string|max:255|unique:tables,number',
            'employees' => 'required|integer|min:1',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
        ];
    }

    public function save()
    {
        $this->validate();

        Table::create([
            'number' => $this->number,
            'employees' => $this->employees,
            'active' => $this->active,
            'comments' => $this->comments ?: null,
            'area_id' => $this->area_id,
        ]);

        session()->flash('flash.banner', 'Mesa creada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.tables.index');
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get();

        return view('livewire.admin.tables.table-create', compact('areas'));
    }
}
