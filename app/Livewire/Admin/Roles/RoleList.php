<?php

namespace App\Livewire\Admin\Roles;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Component;
use Livewire\WithPagination;

class RoleList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function deleteRole(Role $role): void
    {
        // Verificar si el rol tiene usuarios asignados
        if ($role->users()->count() > 0) {
            session()->flash('flash.banner', 'No se puede eliminar el rol porque tiene usuarios asignados.');
            session()->flash('flash.bannerStyle', 'danger');
            return;
        }

        $role->delete();

        session()->flash('flash.banner', 'Rol eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $query = Role::withCount(['users', 'permissions'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $roles = $query->paginate(10);
        $totalRoles = Role::count();
        $totalPermissions = Permission::count();

        return view('livewire.admin.roles.role-list', [
            'roles' => $roles,
            'totalRoles' => $totalRoles,
            'totalPermissions' => $totalPermissions,
        ]);
    }
}
