<?php

namespace App\Livewire\Admin\Roles;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Component;

class RoleCreate extends Component
{
    public string $name = '';
    public array $selectedPermissions = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ];
    }

    public function saveRole(): void
    {
        $this->validate();

        $role = Role::create(['name' => $this->name]);

        if (!empty($this->selectedPermissions)) {
            // Validar que los permisos seleccionados existan en la base de datos
            $existingPermissionIds = Permission::whereIn('id', $this->selectedPermissions)->pluck('id')->toArray();
            
            // Solo sincronizar los permisos que realmente existen
            $role->syncPermissions($existingPermissionIds);
        }

        session()->flash('flash.banner', 'Rol creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.roles.index'), navigate: true);
    }

    public function render()
    {
        $permissions = Permission::orderBy('name')->get();

        return view('livewire.admin.roles.role-create', [
            'permissions' => $permissions,
        ]);
    }
}
