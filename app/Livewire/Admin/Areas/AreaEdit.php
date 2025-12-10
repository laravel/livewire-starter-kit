<?php

namespace App\Livewire\Admin\Areas;

use App\Models\Area;
use App\Models\User;
use App\Models\Department;
use Livewire\Component;

class AreaEdit extends Component
{
    public Area $area;
    public string $name = '';
    public string $description = '';
    public string $comments = '';
    public string $department_id = '';
    public ?int $user_id = null;

    public function mount(Area $area): void
    {
        $this->area = $area;
        $this->name = $area->name;
        $this->description = $area->description ?? '';
        $this->comments = $area->comments ?? '';
        $this->department_id = $area->department_id;
        $this->user_id = $area->user_id;
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'comments' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    public function updateArea(): void
    {
        $this->validate();

        $this->area->update([
            'name' => $this->name,
            'description' => $this->description,
            'comments' => $this->comments,
            'department_id' => $this->department_id,
            'user_id' => $this->user_id,
        ]);

        session()->flash('flash.banner', 'Área actualizada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.areas.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.areas.area-edit', [
            'departments' => Department::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }
}
