<?php

namespace App\Livewire\Admin\SemiAutomatics;

use App\Models\Semi_Automatic;
use App\Models\Area;
use Livewire\Component;

class SemiAutomaticCreate extends Component
{
    public string $number = '';
    public string $employees = '';
    public bool $active = true;
    public string $comments = '';
    public string $area_id = '';

    public function rules(): array
    {
        return [
            'number' => 'required|string|max:255|unique:semi__automatics,number',
            'employees' => 'nullable|integer|min:1',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
        ];
    }

    public function save()
    {
        $this->validate();

        Semi_Automatic::create([
            'number' => $this->number,
            'employees' => $this->employees ?: null,
            'active' => $this->active,
            'comments' => $this->comments ?: null,
            'area_id' => $this->area_id,
        ]);

        session()->flash('flash.banner', 'Semi-automático creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.semi-automatics.index');
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get();

        return view('livewire.admin.semi-automatics.semi-automatic-create', compact('areas'));
    }
}
