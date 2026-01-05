<?php

namespace App\Livewire\Admin\Employees;

use Livewire\Component;
use App\Models\User;
use App\Models\Area;
use App\Models\Shift;
use Illuminate\Support\Facades\Hash;

class EmployeeCreate extends Component
{
    public $name = '';
    public $last_name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $employee_number = '';
    public $position = '';
    public $birth_date = '';
    public $entry_date = '';
    public $active = true;
    public $comments = '';
    public $area_id = '';
    public $shift_id = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'employee_number' => 'nullable|string|max:50|unique:users,employee_number',
            'position' => 'nullable|string|max:255',
            'birth_date' => 'nullable|date',
            'entry_date' => 'nullable|date',
            'active' => 'boolean',
            'comments' => 'nullable|string',
            'area_id' => 'required|exists:areas,id',
            'shift_id' => 'required|exists:shifts,id',
        ];
    }

    protected function messages()
    {
        return [
            'name.required' => 'El nombre es requerido.',
            'last_name.required' => 'El apellido es requerido.',
            'email.required' => 'El correo electrónico es requerido.',
            'email.email' => 'El correo electrónico debe ser válido.',
            'email.unique' => 'El correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es requerida.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'employee_number.unique' => 'El número de empleado ya existe.',
            'area_id.required' => 'El área es requerida.',
            'area_id.exists' => 'El área seleccionada no existe.',
            'shift_id.required' => 'El turno es requerido.',
            'shift_id.exists' => 'El turno seleccionado no existe.',
        ];
    }

    public function save()
    {
        $this->validate();

        // Crear usuario con rol employee
        $user = User::create([
            'name' => $this->name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'employee_number' => $this->employee_number ?: null, // Se auto-genera si está vacío
            'position' => $this->position ?: null,
            'birth_date' => $this->birth_date ?: null,
            'entry_date' => $this->entry_date ?: null,
            'active' => $this->active,
            'comments' => $this->comments ?: null,
            'area_id' => $this->area_id,
            'shift_id' => $this->shift_id,
        ]);

        // Asignar rol de empleado
        $user->assignRole('employee');

        session()->flash('flash.banner', 'Empleado creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.employees.index');
    }

    public function render()
    {
        return view('livewire.admin.employees.employee-create', [
            'areas' => Area::orderBy('name')->get(),
            'shifts' => Shift::active()->orderBy('name')->get(),
        ]);
    }
}
