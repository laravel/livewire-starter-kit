<?php

namespace App\Livewire\Admin\Machines;

use App\Models\Machine;
use Livewire\Component;

class MachineShow extends Component
{
    public Machine $machine;

    public function mount(Machine $machine): void
    {
        $this->machine = $machine->load('area');
    }

    public function render()
    {
        return view('livewire.admin.machines.machine-show');
    }
}
