<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\User;

new class extends Component {
    public $departments = [];
    public $name = '';
    public $description = '';
    public $comments = '';
    public $department_id = '';
    public $user_id = null;
    public $users = [];

    public function mount($departments)
    {
        $this->departments = $departments;
        $this->users = User::role('supervisor')->orderBy('name')->get();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'comments' => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    public function saveArea(): void
    {
        $this->validate();

        Area::create([
            'name' => $this->name,
            'description' => $this->description,
            'comments' => $this->comments,
            'department_id' => $this->department_id,
            'user_id' => $this->user_id,
        ]);

        session()->flash('flash.banner', 'Área creada correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        redirect()->route('areas.index');
    }
}; ?>

<div>
    <form wire:submit="saveArea" class="space-y-6">
        <div>
            <x-input-label for="name" :value="__('Nombre')" />
            <x-text-input wire:model="name" id="name" type="text" class="mt-1 block w-full" required />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="department_id" :value="__('Departamento')" />
            <select 
                wire:model="department_id" 
                id="department_id" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                required
            >
                <option value="">Seleccione un departamento</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('department_id')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="user_id" :value="__('Supervisor')" />
            <select 
                wire:model="user_id" 
                id="user_id" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
            >
                <option value="">Sin supervisor</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
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
            <x-secondary-button type="button" onclick="window.location.href='{{ route('areas.index') }}'">
                {{ __('Cancelar') }}
            </x-secondary-button>

            <x-primary-button class="ml-3">
                {{ __('Guardar') }}
            </x-primary-button>
        </div>
    </form>
</div>
