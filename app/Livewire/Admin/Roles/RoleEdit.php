<?php

namespace App\Livewire\Admin\Roles;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Component;

class RoleEdit extends Component
{
    public Role $role;
    public string $name = '';
    public array $selectedPermissions = [];

    public function mount(Role $role): void
    {
        $this->role = $role;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $this->role->id,
            'selectedPermissions' => 'array',
            'selectedPermissions.*' => 'exists:permissions,id',
        ];
    }

    public function updateRole(): void
    {
        $this->validate();

        $this->role->update(['name' => $this->name]);
        
        // Validar que los permisos seleccionados existan en la base de datos
        $existingPermissionIds = Permission::whereIn('id', $this->selectedPermissions)->pluck('id')->toArray();
        
        // Solo sincronizar los permisos que realmente existen
        $this->role->syncPermissions($existingPermissionIds);

        session()->flash('flash.banner', 'Rol actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.roles.index'), navigate: true);
    }

    public function render()
    {
        $permissions = Permission::orderBy('name')->get();

        // Group permissions by area prefix for organized display
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode('.', $permission->name);
            return count($parts) > 1 ? $parts[0] : 'otros';
        });

        $groupLabels = [
            'admin' => 'Administración General',
            'usuarios' => 'Usuarios & Seguridad',
            'catalogos' => 'Catálogos',
            'ordenes' => 'Órdenes (PO / WO)',
            'produccion' => 'Producción',
            'calidad' => 'Calidad',
            'materiales' => 'Materiales',
            'otros' => 'Otros',
        ];

        return view('livewire.admin.roles.role-edit', [
            'permissions' => $permissions,
            'groupedPermissions' => $groupedPermissions,
            'groupLabels' => $groupLabels,
        ]);
    }
}
