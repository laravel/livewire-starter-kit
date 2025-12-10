<?php

namespace App\Livewire\Admin\Permissions;

use Spatie\Permission\Models\Permission;
use Livewire\Component;

class PermissionCreate extends Component
{
    public string $name = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name',
        ];
    }

    public function save(): void
    {
        $this->validate();

        Permission::create([
            'name' => $this->name,
            'guard_name' => 'web',
        ]);

        session()->flash('flash.banner', 'Permiso creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.permissions.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.permissions.permission-create');
    }
}
