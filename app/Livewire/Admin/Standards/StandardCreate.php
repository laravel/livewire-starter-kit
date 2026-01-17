<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Machine;
use App\Models\Part;
use App\Models\Semi_Automatic;
use App\Models\Standard;
use App\Models\StandardConfiguration;
use App\Models\Table;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StandardCreate extends Component
{
    // Propiedades del Standard
    public ?int $part_id = null;
    public string $effective_date = '';
    public bool $active = true;
    public string $description = '';

    // Configuraciones multiples
    public array $configurations = [];

    // Propiedades legacy (mantenidas por compatibilidad)
    public string $units_per_hour = '';
    public ?int $work_table_id = null;
    public ?int $semi_auto_work_table_id = null;
    public ?int $machine_id = null;
    public string $persons_1 = '';
    public string $persons_2 = '';
    public string $persons_3 = '';

    // Control de modo
    public bool $useNewConfigSystem = true;

    public function mount(): void
    {
        $this->effective_date = now()->format('Y-m-d');
        // Agregar una configuracion inicial
        $this->addConfiguration();
    }

    /**
     * Agrega una nueva configuracion al array
     */
    public function addConfiguration(): void
    {
        $this->configurations[] = [
            'workstation_type' => StandardConfiguration::TYPE_MANUAL,
            'workstation_id' => null,
            'persons_required' => 1,
            'units_per_hour' => '',
            'is_default' => count($this->configurations) === 0, // Primera es default
            'notes' => '',
        ];
    }

    /**
     * Elimina una configuracion del array
     */
    public function removeConfiguration(int $index): void
    {
        if (count($this->configurations) > 1) {
            $wasDefault = $this->configurations[$index]['is_default'] ?? false;
            unset($this->configurations[$index]);
            $this->configurations = array_values($this->configurations);

            // Si eliminamos la default, hacer la primera como default
            if ($wasDefault && count($this->configurations) > 0) {
                $this->configurations[0]['is_default'] = true;
            }
        }
    }

    /**
     * Establece una configuracion como default
     */
    public function setDefaultConfiguration(int $index): void
    {
        foreach ($this->configurations as $i => $config) {
            $this->configurations[$i]['is_default'] = ($i === $index);
        }
    }

    /**
     * Limpia el workstation_id cuando cambia el tipo
     */
    public function updatedConfigurations($value, $key): void
    {
        // El key viene como "0.workstation_type"
        if (str_contains($key, '.workstation_type')) {
            $index = explode('.', $key)[0];
            $this->configurations[$index]['workstation_id'] = null;
        }
    }

    /**
     * Obtiene las estaciones disponibles segun el tipo
     */
    public function getWorkstationsForType(string $type): array
    {
        return match($type) {
            StandardConfiguration::TYPE_MANUAL => Table::active()->orderBy('number')->get()->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->number,
            ])->toArray(),
            StandardConfiguration::TYPE_SEMI_AUTOMATIC => Semi_Automatic::active()->orderBy('number')->get()->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->number,
            ])->toArray(),
            StandardConfiguration::TYPE_MACHINE => Machine::active()->orderBy('name')->get()->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
            ])->toArray(),
            default => [],
        };
    }

    protected function rules(): array
    {
        $rules = [
            'part_id' => 'required|exists:parts,id',
            'effective_date' => 'nullable|date',
            'active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ];

        if ($this->useNewConfigSystem) {
            $rules['configurations'] = 'required|array|min:1';
            $rules['configurations.*.workstation_type'] = 'required|in:manual,semi_automatic,machine';
            $rules['configurations.*.workstation_id'] = 'nullable|integer';
            $rules['configurations.*.persons_required'] = 'required|integer|min:1|max:3';
            $rules['configurations.*.units_per_hour'] = 'required|integer|min:1';
            $rules['configurations.*.is_default'] = 'boolean';
            $rules['configurations.*.notes'] = 'nullable|string|max:255';
        } else {
            $rules['units_per_hour'] = 'required|integer|min:1';
            $rules['work_table_id'] = 'nullable|exists:tables,id';
            $rules['semi_auto_work_table_id'] = 'nullable|exists:semi__automatics,id';
            $rules['machine_id'] = 'nullable|exists:machines,id';
            $rules['persons_1'] = 'nullable|integer|min:1';
            $rules['persons_2'] = 'nullable|integer|min:1';
            $rules['persons_3'] = 'nullable|integer|min:1';
        }

        return $rules;
    }

    protected function messages(): array
    {
        return [
            'part_id.required' => 'Debe seleccionar una parte.',
            'part_id.exists' => 'La parte seleccionada no existe.',
            'effective_date.date' => 'La fecha efectiva no es valida.',
            'description.max' => 'La descripcion no puede exceder 500 caracteres.',

            // Mensajes para configuraciones
            'configurations.required' => 'Debe agregar al menos una configuracion.',
            'configurations.min' => 'Debe agregar al menos una configuracion.',
            'configurations.*.workstation_type.required' => 'El tipo de estacion es obligatorio.',
            'configurations.*.workstation_type.in' => 'El tipo de estacion no es valido.',
            'configurations.*.persons_required.required' => 'Las personas requeridas son obligatorias.',
            'configurations.*.persons_required.min' => 'Debe haber al menos 1 persona.',
            'configurations.*.persons_required.max' => 'No puede haber mas de 3 personas.',
            'configurations.*.units_per_hour.required' => 'Las unidades por hora son obligatorias.',
            'configurations.*.units_per_hour.min' => 'Las unidades por hora deben ser al menos 1.',
            'configurations.*.notes.max' => 'Las notas no pueden exceder 255 caracteres.',

            // Mensajes legacy
            'units_per_hour.required' => 'Las unidades por hora son obligatorias.',
            'units_per_hour.integer' => 'Las unidades por hora deben ser un numero entero.',
            'units_per_hour.min' => 'Las unidades por hora deben ser al menos 1.',
        ];
    }

    /**
     * Valida que no haya configuraciones duplicadas (mismo tipo + personas)
     */
    protected function validateUniqueConfigurations(): bool
    {
        $seen = [];
        foreach ($this->configurations as $index => $config) {
            $key = $config['workstation_type'] . '-' . $config['persons_required'];
            if (isset($seen[$key])) {
                $this->addError(
                    "configurations.{$index}.persons_required",
                    'Ya existe una configuracion con este tipo de estacion y cantidad de personas.'
                );
                return false;
            }
            $seen[$key] = true;
        }
        return true;
    }

    /**
     * Valida que haya exactamente una configuracion default
     */
    protected function validateDefaultConfiguration(): bool
    {
        $defaultCount = collect($this->configurations)->where('is_default', true)->count();

        if ($defaultCount === 0) {
            $this->addError('configurations', 'Debe marcar una configuracion como predeterminada.');
            return false;
        }

        if ($defaultCount > 1) {
            $this->addError('configurations', 'Solo puede haber una configuracion predeterminada.');
            return false;
        }

        return true;
    }

    public function saveStandard(): void
    {
        $this->validate();

        if ($this->useNewConfigSystem) {
            // Validaciones adicionales
            if (!$this->validateUniqueConfigurations()) {
                return;
            }

            if (!$this->validateDefaultConfiguration()) {
                return;
            }

            DB::transaction(function () {
                // Crear el standard
                $standard = Standard::create([
                    'part_id' => $this->part_id,
                    'effective_date' => $this->effective_date ?: null,
                    'active' => $this->active,
                    'description' => $this->description ?: null,
                    'is_migrated' => true,
                    // Campos legacy - tomar valores de la primera configuracion default
                    'units_per_hour' => $this->getDefaultUnitsPerHour(),
                    'work_table_id' => null,
                    'semi_auto_work_table_id' => null,
                    'machine_id' => null,
                ]);

                // Crear las configuraciones
                foreach ($this->configurations as $config) {
                    $standard->configurations()->create([
                        'workstation_type' => $config['workstation_type'],
                        'workstation_id' => $config['workstation_id'] ?: null,
                        'persons_required' => $config['persons_required'],
                        'units_per_hour' => $config['units_per_hour'],
                        'is_default' => $config['is_default'] ?? false,
                        'notes' => $config['notes'] ?: null,
                    ]);
                }
            });
        } else {
            // Modo legacy
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
                'is_migrated' => false,
            ]);
        }

        session()->flash('flash.banner', 'Estandar creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.standards.index'), navigate: true);
    }

    /**
     * Obtiene las units_per_hour de la configuracion default
     */
    protected function getDefaultUnitsPerHour(): ?int
    {
        $default = collect($this->configurations)->firstWhere('is_default', true);
        return $default ? (int) $default['units_per_hour'] : null;
    }

    public function render()
    {
        return view('livewire.admin.standards.standard-create', [
            'parts' => Part::orderBy('number')->get(),
            'workTables' => Table::active()->orderBy('number')->get(),
            'semiAutoWorkTables' => Semi_Automatic::active()->orderBy('number')->get(),
            'machines' => Machine::active()->orderBy('name')->get(),
            'workstationTypes' => StandardConfiguration::getWorkstationTypes(),
            'personsOptions' => StandardConfiguration::getPersonsOptions(),
        ]);
    }
}
