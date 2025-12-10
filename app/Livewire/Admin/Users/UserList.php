<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $roleFilter = '';
    public string $departmentFilter = '';
    public string $sortField = 'name';
    public string $sortDirection = 'asc';

    public function render()
    {
        $query = User::with(['roles', 'areas.department'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('email', 'like', '%' . $this->search . '%')
                      ->orWhere('account', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', $this->roleFilter);
                });
            })
            ->when($this->departmentFilter, function ($query) {
                $query->whereHas('areas.department', function ($q) {
                    $q->where('id', $this->departmentFilter);
                });
            })
            ->orderBy($this->sortField, $this->sortDirection);

        return view('livewire.admin.users.user-list', [
            'users' => $query->paginate(10),
            'departments' => Department::orderBy('name')->get(),
            'roles' => Role::orderBy('name')->get(),
            'totalUsers' => User::count(),
            'usersByRole' => Role::withCount('users')->get(),
        ]);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter(): void
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

    public function deleteUser(User $user): void
    {
        if ($user->id === auth()->id()) {
            session()->flash('flash.banner', 'No puedes eliminar tu propia cuenta.');
            session()->flash('flash.bannerStyle', 'danger');
            return;
        }

        $user->areas()->update(['user_id' => null]);
        $user->delete();

        session()->flash('flash.banner', 'Usuario eliminado correctamente.');
        session()->flash('flash.bannerStyle', 'success');
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->roleFilter = '';
        $this->departmentFilter = '';
        $this->resetPage();
    }
}
