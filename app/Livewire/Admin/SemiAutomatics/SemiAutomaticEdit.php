<?php

namespace App\Livewire\Admin\SemiAutomatics;

use App\Models\Semi_Automatic;
use App\Models\Area;
use Livewire\Component;

class SemiAutomaticEdit extends Component
{
    public Semi_Automatic $semiAutomatic;
    public string $number = '';
    public string $employees = '';
    public bool $active = true;
    public string $comments = '';
    public string $area_id = '';

    public function mount(Semi_Automatic $semiAutomatic): void
    {
        $this->semiAutomatic = $semiAutomatic;
        $this->number = $semiAutomatic->number;
        $this->employees = $semiAutomatic->employees ?? '';
        $this->active = $semiAutomatic->active;
        $this->comments = $semiAutomatic->comments ?? '';
        $this->area_id = $semiAutomatic->area_id;
    }

    public function rules(): array
    {
        return [
            'number' => 'required|string|max:255|unique:semi_automatics,number,' . $this->semiAutomatic->id,
            'employees' => 'nullable|integer|min:1',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
        ];
    }

    public function update()
    {
        $this->validate();

        $this->semiAutomatic->update([
            'number' => $this->number,
            'employees' => $this->employees ?: null,
            'active' => $this->active,
            'comments' => $this->comments ?: null,
            'area_id' => $this->area_id,
        ]);

        session()->flash('flash.banner', 'Semi-automático actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('semi-automatics.index');
    }

    public function render()
    {
        $areas = Area::orderBy('name')->get();

        return view('livewire.admin.semi-automatics.semi-automatic-edit', compact('areas'));
    }
}
