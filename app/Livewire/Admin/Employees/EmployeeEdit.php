<?php

namespace App\Livewire\Admin\Employees;

use Livewire\Component;
use App\Models\User;
use App\Models\Area;
use App\Models\Shift;
use Illuminate\Support\Facades\Hash;

class EmployeeEdit extends Component
{
    public User $employee;

    public $name = '';
    public $last_name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $position = '';
    public $birth_date = '';
    public $entry_date = '';
    public $active = true;
    public $comments = '';
    public $area_id = '';
    public $shift_id = '';

    public function mount(User $employee)
    {
        $this->employee = $employee;
        $this->name = $employee->name;
        $this->last_name = $employee->last_name;
        $this->email = $employee->email;
        $this->position = $employee->position ?? '';
        $this->birth_date = $employee->birth_date ? $employee->birth_date->format('Y-m-d') : '';
        $this->entry_date = $employee->entry_date ? $employee->entry_date->format('Y-m-d') : '';
        $this->active = $employee->active;
        $this->comments = $employee->comments ?? '';
        $this->area_id = $employee->area_id;
        $this->shift_id = $employee->shift_id;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->employee->id,
            'password' => 'nullable|string|min:8|confirmed',
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
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'area_id.required' => 'El área es requerida.',
            'area_id.exists' => 'El área seleccionada no existe.',
            'shift_id.required' => 'El turno es requerido.',
            'shift_id.exists' => 'El turno seleccionado no existe.',
        ];
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'position' => $this->position ?: null,
            'birth_date' => $this->birth_date ?: null,
            'entry_date' => $this->entry_date ?: null,
            'active' => $this->active,
            'comments' => $this->comments ?: null,
            'area_id' => $this->area_id,
            'shift_id' => $this->shift_id,
        ];

        // Solo actualizar contraseña si se proporciona
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        $this->employee->update($data);

        session()->flash('flash.banner', 'Empleado actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        return redirect()->route('admin.employees.show', $this->employee);
    }

    public function render()
    {
        return view('livewire.admin.employees.employee-edit', [
            'areas' => Area::orderBy('name')->get(),
            'shifts' => Shift::active()->orderBy('name')->get(),
        ]);
    }
}
