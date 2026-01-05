<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Machine;
use App\Models\Part;
use App\Models\Semi_Automatic;
use App\Models\Standard;
use App\Models\Table;
use Livewire\Component;

class StandardCreate extends Component
{
    public ?int $part_id = null;
    public string $units_per_hour = '';
    public ?int $work_table_id = null;
    public ?int $semi_auto_work_table_id = null;
    public ?int $machine_id = null;
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
            'units_per_hour' => 'required|integer|min:1',
            'work_table_id' => 'nullable|exists:tables,id',
            'semi_auto_work_table_id' => 'nullable|exists:semi__automatics,id',
            'machine_id' => 'nullable|exists:machines,id',
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
            'units_per_hour.required' => 'Las unidades por hora son obligatorias.',
            'units_per_hour.integer' => 'Las unidades por hora deben ser un número entero.',
            'units_per_hour.min' => 'Las unidades por hora deben ser al menos 1.',
            'work_table_id.exists' => 'La mesa de trabajo seleccionada no existe.',
            'semi_auto_work_table_id.exists' => 'La mesa semi-automática seleccionada no existe.',
            'machine_id.exists' => 'La máquina seleccionada no existe.',
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
            'units_per_hour' => $this->units_per_hour,
            'work_table_id' => $this->work_table_id ?: null,
            'semi_auto_work_table_id' => $this->semi_auto_work_table_id ?: null,
            'machine_id' => $this->machine_id ?: null,
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
            'workTables' => Table::active()->orderBy('number')->get(),
            'semiAutoWorkTables' => Semi_Automatic::active()->orderBy('number')->get(),
            'machines' => Machine::active()->orderBy('name')->get(),
        ]);
    }
}
