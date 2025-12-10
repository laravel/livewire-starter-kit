<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Department;
use App\Models\Area;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserEdit extends Component
{
    public User $user;
    public string $name = '';
    public string $last_name = '';
    public string $account = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $changePassword = false;
    public ?int $department_id = null;
    public ?int $area_id = null;
    public string $selected_role = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->last_name = $user->last_name ?? '';
        $this->account = $user->account ?? '';
        $this->email = $user->email;
        $this->selected_role = $user->roles->first()?->name ?? '';

        $supervisedArea = Area::where('user_id', $user->id)->first();
        if ($supervisedArea) {
            $this->area_id = $supervisedArea->id;
            $this->department_id = $supervisedArea->department_id;
        }
    }

    public function render()
    {
        $areas = $this->department_id 
            ? Area::where('department_id', $this->department_id)->orderBy('name')->get()
            : collect();

        return view('livewire.admin.users.user-edit', [
            'departments' => Department::orderBy('name')->get(),
            'areas' => $areas,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function updatedDepartmentId(): void
    {
        $this->area_id = null;
    }

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'account' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'selected_role' => ['required', 'exists:roles,name'],
        ];

        if ($this->changePassword) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    public function updateUser(): void
    {
        $validated = $this->validate();

        $userData = [
            'name' => $this->name,
            'last_name' => $this->last_name,
            'account' => $this->account ?: null,
            'email' => $this->email,
        ];

        if ($this->changePassword) {
            $userData['password'] = Hash::make($this->password);
        }

        $this->user->update($userData);
        $this->user->syncRoles([$this->selected_role]);

        Area::where('user_id', $this->user->id)->update(['user_id' => null]);

        if ($this->area_id && $this->selected_role === 'Supervisor') {
            Area::find($this->area_id)?->update(['user_id' => $this->user->id]);
        }

        session()->flash('flash.banner', 'Usuario actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.users.index'));
    }

    public function toggleChangePassword(): void
    {
        $this->changePassword = !$this->changePassword;
        if (!$this->changePassword) {
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('admin.users.index'));
    }
}
