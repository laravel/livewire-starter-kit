<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

#[Layout('components.layouts.employee')]
#[Title('Mi Perfil')]
class Profile extends Component
{
    public ?User $employee = null;
    
    // Editable fields
    public string $name = '';
    public string $last_name = '';
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount()
    {
        // El usuario autenticado ES el empleado
        $this->employee = Auth::user();
        
        if ($this->employee) {
            $this->name = $this->employee->name;
            $this->last_name = $this->employee->last_name ?? '';
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
        ]);

        if (!$this->employee) {
            session()->flash('error', 'No se encontró el usuario.');
            return;
        }

        $this->employee->update([
            'name' => $this->name,
            'last_name' => $this->last_name,
        ]);

        session()->flash('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if (!$this->employee) {
            session()->flash('error', 'No se encontró el usuario.');
            return;
        }

        if (!Hash::check($this->current_password, $this->employee->password)) {
            $this->addError('current_password', 'La contraseña actual no es correcta.');
            return;
        }

        $this->employee->update([
            'password' => Hash::make($this->password),
        ]);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('success', 'Contraseña actualizada correctamente.');
    }

    public function render()
    {
        return view('livewire.employee.profile');
    }
}
