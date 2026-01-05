<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.employee')]
#[Title('Employee Dashboard')]
class Dashboard extends Component
{
    public ?User $employee = null;

    public function mount()
    {
        // El usuario autenticado ES el empleado (ya unificados)
        $this->employee = Auth::user();
    }

    public function render()
    {
        return view('livewire.employee.dashboard');
    }
}
