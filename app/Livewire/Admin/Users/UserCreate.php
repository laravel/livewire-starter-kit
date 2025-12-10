<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Department;
use App\Models\Area;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;

class UserCreate extends Component
{
    public string $name = '';
    public string $last_name = '';
    public string $account = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $department_id = '';
    public string $area_id = '';
    public string $selected_role = '';

    public function render()
    {
        $areas = $this->department_id 
            ? Area::where('department_id', $this->department_id)->orderBy('name')->get()
            : collect();

        return view('livewire.admin.users.user-create', [
            'departments' => Department::orderBy('name')->get(),
            'areas' => $areas,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function updatedDepartmentId(): void
    {
        $this->area_id = '';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'account' => 'nullable|string|max:255|unique:users,account',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'department_id' => 'nullable|exists:departments,id',
            'area_id' => 'nullable|exists:areas,id',
            'selected_role' => 'required|exists:roles,name',
        ];
    }

    public function saveUser(): void
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'last_name' => $this->last_name,
            'account' => $this->account ?: null,
            'email' => $this->email,
            'password' => Hash::make($this->password),
        ]);

        $user->assignRole($this->selected_role);

        if ($this->selected_role === 'Supervisor' && $this->area_id) {
            $area = Area::find($this->area_id);
            if ($area) {
                $area->update(['user_id' => $user->id]);
            }
        }

        session()->flash('flash.banner', 'Usuario creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.users.index'));
    }

    public function cancel(): void
    {
        $this->redirect(route('admin.users.index'));
    }
}
