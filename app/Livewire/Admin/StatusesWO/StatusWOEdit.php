<?php

namespace App\Livewire\Admin\StatusesWO;

use App\Models\StatusWO;
use Livewire\Component;

class StatusWOEdit extends Component
{
    public StatusWO $statusWO;
    public string $name = '';
    public string $color = '#6B7280';
    public string $comments = '';

    public function mount(StatusWO $statusWO): void
    {
        $this->statusWO = $statusWO;
        $this->name = $statusWO->name;
        $this->color = $statusWO->color;
        $this->comments = $statusWO->comments ?? '';
    }

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:statuses_wo,name,' . $this->statusWO->id,
            'color' => 'required|string|max:7',
            'comments' => 'nullable|string',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'El nombre del estado es requerido.',
            'name.unique' => 'Ya existe un estado con este nombre.',
            'color.required' => 'El color es requerido.',
        ];
    }

    public function updateStatus(): void
    {
        $this->validate();

        $this->statusWO->update([
            'name' => $this->name,
            'color' => $this->color,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Estado actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('admin.statuses-wo.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.statuses-wo.status-wo-edit');
    }
}
