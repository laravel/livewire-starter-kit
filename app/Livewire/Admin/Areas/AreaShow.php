<?php

namespace App\Livewire\Admin\Areas;

use App\Models\Area;
use Livewire\Component;

class AreaShow extends Component
{
    public Area $area;
    public array $stats = [];

    public function mount(Area $area): void
    {
        $this->area = $area->load(['department', 'machines', 'tables', 'semiAutomatics']);
        $this->stats = $area->getStats();
    }

    public function render()
    {
        return view('livewire.admin.areas.area-show');
    }
}
