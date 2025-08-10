<?php

use Livewire\Volt\Component;
use App\Models\Department;

new class extends Component {
    public $name = '';
    public $description = '';
    public $comments = '';

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string',
            'comments' => 'nullable|string|max:255',
        ];
    }

    public function saveDepartment(): void
    {
        $this->validate();

        Department::create([
            'name' => $this->name,
            'description' => $this->description,
            'comments' => $this->comments,
        ]);

        session()->flash('flash.banner', 'Departamento creado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        redirect()->route('departments.index');
    }
}; ?>

<div>
    <form wire:submit="saveDepartment" class="space-y-6">
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="description" :value="__('Descripción')" />
            <textarea 
                wire:model="description" 
                id="description" 
                rows="4" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
            ></textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="comments" :value="__('Comentarios')" />
            <x-text-input wire:model="comments" id="comments" type="text" class="mt-1 block w-full" />
            <x-input-error :messages="$errors->get('comments')" class="mt-2" />
        </div>

        <div class="flex justify-end">
            <x-secondary-button type="button" onclick="window.location.href='{{ route('departments.index') }}'">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-primary-button class="ml-3">
                {{ __('Guardar') }}
            </x-primary-button>
        </div>
    </form>
</div>
