<?php

namespace App\Livewire\Admin\Parts;

use App\Models\Part;
use Livewire\Component;

class PartEdit extends Component
{
    public Part $part;
    public string $number = '';
    public string $item_number = '';
    public string $unit_of_measure = '';
    public string $description = '';
    public string $notes = '';
    public bool $active = true;

    public function mount(Part $part): void
    {
        $this->part = $part;
        $this->number = $part->number;
        $this->item_number = $part->item_number;
        $this->unit_of_measure = $part->unit_of_measure ?? '';
        $this->description = $part->description ?? '';
        $this->notes = $part->notes ?? '';
        $this->active = (bool) $part->active;
    }

    protected function rules(): array
    {
        return [
            'number' => 'required|string|max:255|unique:parts,number,' . $this->part->id,
            'item_number' => 'required|string|max:255|unique:parts,item_number,' . $this->part->id,
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

    public function updatePart(): void
    {
        $this->validate();

        $this->part->update([
            'number' => $this->number,
            'item_number' => $this->item_number,
            'unit_of_measure' => $this->unit_of_measure ?: null,
            'description' => $this->description ?: null,
            'notes' => $this->notes ?: null,
            'active' => $this->active,
        ]);

        session()->flash('flash.banner', 'Parte actualizada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.parts.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.parts.part-edit');
    }
}
