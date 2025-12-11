<?php

namespace App\Livewire\Admin\Parts;

use App\Models\Part;
use Livewire\Component;

class PartCreate extends Component
{
    public string $number = '';
    public string $item_number = '';
    public string $unit_of_measure = '';
    public string $description = '';
    public string $notes = '';
    public bool $active = true;

    protected function rules(): array
    {
        return [
            'number' => 'required|string|max:255|unique:parts,number',
            'item_number' => 'required|string|max:255|unique:parts,item_number',
            'unit_of_measure' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'notes' => 'nullable|string|max:255',
            'active' => 'boolean',
        ];
    }

    protected function messages(): array
    {
        return [
            'number.required' => 'El número de parte es obligatorio.',
            'number.unique' => 'Ya existe una parte con este número.',
            'item_number.required' => 'El número de ítem es obligatorio.',
            'item_number.unique' => 'Ya existe una parte con este número de ítem.',
        ];
    }

    public function savePart(): void
    {
        $this->validate();

        Part::create([
            'number' => $this->number,
            'item_number' => $this->item_number,
            'unit_of_measure' => $this->unit_of_measure ?: null,
            'description' => $this->description ?: null,
            'notes' => $this->notes ?: null,
            'active' => $this->active,
        ]);

        session()->flash('flash.banner', 'Parte creada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.parts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.parts.part-create');
    }
}
