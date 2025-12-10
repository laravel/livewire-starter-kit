<?php

namespace App\Livewire\Admin\Permissions;

use Spatie\Permission\Models\Permission;
use Livewire\Component;
use Livewire\WithPagination;

class PermissionList extends Component
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

    public function deletePermission(Permission $permission): void
    {
        // Verificar si el permiso está asignado a algún rol
        if ($permission->roles()->count() > 0) {
            session()->flash('flash.banner', 'No se puede eliminar el permiso porque está asignado a uno o más roles.');
            session()->flash('flash.bannerStyle', 'danger');
            return;
        }

        $permission->delete();

        session()->flash('flash.banner', 'Permiso eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function render()
    {
        $query = Permission::withCount('roles')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection);

        $permissions = $query->paginate(15);
        $totalPermissions = Permission::count();

        return view('livewire.admin.permissions.permission-list', [
            'permissions' => $permissions,
            'totalPermissions' => $totalPermissions,
        ]);
    }
}
