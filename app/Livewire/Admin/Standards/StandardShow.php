<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Standard;
use App\Models\StandardConfiguration;
use Livewire\Component;

class StandardShow extends Component
{
    public Standard $standard;
    public bool $is_current = false;
    public array $configurationStats = [];

    public function mount(Standard $standard): void
    {
        $this->standard = $standard->load([
            'part',
            'workTable',
            'semiAutoWorkTable',
            'machine',
            'configurations'
        ]);
        $this->calculateInfo();
    }

    protected function calculateInfo(): void
    {
        // Check if this standard is current (effective and active)
        $this->is_current = $this->standard->active;

        // Calculate configuration stats
        $this->configurationStats = $this->standard->getConfigurationsStats();
    }

    public function toggleActive(): void
    {
        $this->standard->active = !$this->standard->active;
        $this->standard->save();

        $status = $this->standard->active ? 'activado' : 'desactivado';
        session()->flash('flash.banner', "Estandar {$status} correctamente.");
        session()->flash('flash.bannerStyle', 'success');

        $this->standard = $this->standard->fresh([
            'part',
            'workTable',
            'semiAutoWorkTable',
            'machine',
            'configurations'
        ]);
        $this->calculateInfo();
    }

    public function delete(): void
    {
        $this->standard->delete();

        session()->flash('flash.banner', 'Estandar eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.standards.index'), navigate: true);
    }

    /**
     * Obtiene la etiqueta del tipo de estacion
     */
    public function getWorkstationTypeLabel(string $type): string
    {
        return match($type) {
            StandardConfiguration::TYPE_MANUAL => 'Mesa Manual',
            StandardConfiguration::TYPE_SEMI_AUTOMATIC => 'Mesa Semi-Automatica',
            StandardConfiguration::TYPE_MACHINE => 'Maquina',
            default => 'Desconocido',
        };
    }

    /**
     * Obtiene las configuraciones agrupadas por tipo
     */
    public function getGroupedConfigurations(): array
    {
        return $this->standard->configurations
            ->sortBy(['workstation_type', 'persons_required'])
            ->groupBy('workstation_type')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.admin.standards.standard-show', [
            'workstationTypes' => StandardConfiguration::getWorkstationTypes(),
            'groupedConfigurations' => $this->getGroupedConfigurations(),
        ]);
    }
}
