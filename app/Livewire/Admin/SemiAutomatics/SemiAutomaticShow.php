<?php

namespace App\Livewire\Admin\SemiAutomatics;

use App\Models\Semi_Automatic;
use Livewire\Component;

class SemiAutomaticShow extends Component
{
    public Semi_Automatic $semiAutomatic;

    public function mount(Semi_Automatic $semiAutomatic): void
    {
        $this->semiAutomatic = $semiAutomatic->load('area');
    }

    public function render()
    {
        return view('livewire.admin.semi-automatics.semi-automatic-show');
    }
}
