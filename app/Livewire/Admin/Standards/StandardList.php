<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Standard;
use App\Models\StandardConfiguration;
use Livewire\Component;
use Livewire\WithPagination;

class StandardList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'created_at';
    public string $sortDirection = 'desc';
    public int $perPage = 10;
    public ?int $deleteId = null;
    public bool $confirmingDeletion = false;
    public string $filterStatus = 'all';
    public string $filterWorkstationType = 'all';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterWorkstationType(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
    }

    public function confirmDeletion(int $id): void
    {
        $this->deleteId = $id;
        $this->confirmingDeletion = true;
    }

    public function delete(): void
    {
        $standard = Standard::findOrFail($this->deleteId);
        $standard->delete();

        session()->flash('flash.banner', 'Estandar eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->confirmingDeletion = false;
    }

    public function toggleActive(int $id): void
    {
        $standard = Standard::findOrFail($id);
        $standard->active = !$standard->active;
        $standard->save();

        $status = $standard->active ? 'activado' : 'desactivado';
        session()->flash('flash.banner', "Estandar {$status} correctamente.");
        session()->flash('flash.bannerStyle', 'success');
    }

    /**
     * Obtiene el resumen de configuraciones para mostrar en la lista
     */
    public function getConfigurationSummary(Standard $standard): array
    {
        $configs = $standard->configurations;

        if ($configs->isEmpty()) {
            return [
                'count' => 0,
                'types' => [],
                'default_uph' => $standard->units_per_hour,
                'is_migrated' => $standard->is_migrated,
            ];
        }

        $types = $configs->groupBy('workstation_type')->map(fn($group) => $group->count())->toArray();
        $default = $configs->firstWhere('is_default', true);

        return [
            'count' => $configs->count(),
            'types' => $types,
            'default_uph' => $default ? $default->units_per_hour : $standard->units_per_hour,
            'is_migrated' => true,
        ];
    }

    public function render()
    {
        $query = Standard::with(['part', 'workTable', 'semiAutoWorkTable', 'machine', 'configurations'])
            ->search($this->search);

        // Apply status filter
        if ($this->filterStatus === 'active') {
            $query->active();
        } elseif ($this->filterStatus === 'inactive') {
            $query->inactive();
        }

        // Apply workstation type filter
        if ($this->filterWorkstationType !== 'all') {
            $query->whereHas('configurations', function ($q) {
                $q->where('workstation_type', $this->filterWorkstationType);
            });
        }

        $standards = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.admin.standards.standard-list', [
            'standards' => $standards,
            'stats' => Standard::getStats(),
            'workstationTypes' => StandardConfiguration::getWorkstationTypes(),
        ]);
    }
}
