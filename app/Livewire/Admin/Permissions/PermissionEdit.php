<?php

namespace App\Livewire\Admin\Permissions;

use Spatie\Permission\Models\Permission;
use Livewire\Component;

class PermissionEdit extends Component
{
    public Permission $permission;
    public string $name = '';

    public function mount(Permission $permission): void
    {
        $this->permission = $permission;
        $this->name = $permission->name;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:permissions,name,' . $this->permission->id,
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->permission->update([
            'name' => $this->name,
        ]);

        session()->flash('flash.banner', 'Permiso actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.permissions.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.permissions.permission-edit');
    }
}
