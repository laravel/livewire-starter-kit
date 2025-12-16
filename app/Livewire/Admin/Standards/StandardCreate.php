<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Area;
use App\Models\Department;
use App\Models\Part;
use App\Models\Standard;
use Livewire\Component;

class StandardCreate extends Component
{
    public ?int $part_id = null;
    public ?int $area_id = null;
    public ?int $department_id = null;
    public string $persons_1 = '';
    public string $persons_2 = '';
    public string $persons_3 = '';
    public string $effective_date = '';
    public bool $active = true;
    public string $description = '';

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
    }

    protected function rules(): array
    {
        return [
            'part_id' => 'required|exists:parts,id',
            'area_id' => 'nullable|exists:areas,id',
            'department_id' => 'nullable|exists:departments,id',
            'persons_1' => 'nullable|integer|min:1',
            'persons_2' => 'nullable|integer|min:1',
            'persons_3' => 'nullable|integer|min:1',
            'effective_date' => 'nullable|date',
            'active' => 'boolean',
            'description' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'part_id.required' => 'Debe seleccionar una parte.',
            'part_id.exists' => 'La parte seleccionada no existe.',
            'area_id.exists' => 'El área seleccionada no existe.',
            'department_id.exists' => 'El departamento seleccionado no existe.',
            'persons_1.integer' => 'El campo Personas 1 debe ser un número entero.',
            'persons_1.min' => 'El campo Personas 1 debe ser al menos 1.',
            'persons_2.integer' => 'El campo Personas 2 debe ser un número entero.',
            'persons_2.min' => 'El campo Personas 2 debe ser al menos 1.',
            'persons_3.integer' => 'El campo Personas 3 debe ser un número entero.',
            'persons_3.min' => 'El campo Personas 3 debe ser al menos 1.',
            'effective_date.date' => 'La fecha efectiva no es válida.',
        ];
    }

    public function saveStandard(): void
    {
        $this->validate();

        Standard::create([
            'part_id' => $this->part_id,
            'area_id' => $this->area_id ?: null,
            'department_id' => $this->department_id ?: null,
            'persons_1' => $this->persons_1 ?: null,
            'persons_2' => $this->persons_2 ?: null,
            'persons_3' => $this->persons_3 ?: null,
            'effective_date' => $this->effective_date ?: null,
            'active' => $this->active,
            'description' => $this->description ?: null,
        ]);

        session()->flash('flash.banner', 'Estándar creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.standards.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.standards.standard-create', [
            'parts' => Part::orderBy('number')->get(),
            'areas' => Area::orderBy('name')->get(),
            'departments' => Department::orderBy('name')->get(),
        ]);
    }
}
