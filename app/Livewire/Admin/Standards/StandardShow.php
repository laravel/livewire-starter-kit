<?php

namespace App\Livewire\Admin\Standards;

use App\Models\Standard;
use Livewire\Component;

class StandardShow extends Component
{
    public Standard $standard;
    public bool $is_current = false;

    public function mount(Standard $standard): void
    {
        $this->standard = $standard->load(['part', 'workTable', 'semiAutoWorkTable', 'machine']);
        $this->calculateInfo();
    }

    protected function calculateInfo(): void
    {
        // Check if this standard is current (effective and active)
        $this->is_current = $this->standard->active &&
                           $this->standard->effective_date &&
                           $this->standard->effective_date->lte(now());
    }

    public function toggleActive(): void
    {
        $this->standard->active = !$this->standard->active;
        $this->standard->save();

        $status = $this->standard->active ? 'activado' : 'desactivado';
        session()->flash('flash.banner', "Estándar {$status} correctamente.");
        session()->flash('flash.bannerStyle', 'success');

        $this->standard = $this->standard->fresh(['part', 'workTable', 'semiAutoWorkTable', 'machine']);
        $this->calculateInfo();
    }

    public function delete(): void
    {
        $this->standard->delete();

        session()->flash('flash.banner', 'Estándar eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.standards.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.standards.standard-show');
    }
}
