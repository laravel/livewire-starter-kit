<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\User;
use App\Models\Department;

new class extends Component {
    public $departments = [];
    public $name = '';
    public $description = '';
    public $comments = '';
    public $department_id = '';
    public $user_id = null;
    public $users = [];

    public function mount()
    {
        $this->departments = Department::orderBy('name')->get();
        // Obtenemos todos los usuarios en lugar de filtrar por un rol que no existe
        $this->users = User::orderBy('name')->get();
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

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Nueva Área</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Crea una nueva área para un departamento
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('areas.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Volver a la lista
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Container -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <form wire:submit="saveArea" class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Nombre
            </label>
            <input 
                wire:model="name" 
                id="name" 
                type="text" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" 
                required 
            />
            @error('name')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="department_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Departamento
            </label>
            <select 
                wire:model="department_id" 
                id="department_id" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                required
            >
                <option value="">Seleccione un departamento</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
            @error('department_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Supervisor
            </label>
            <select 
                wire:model="user_id" 
                id="user_id" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
            >
                <option value="">Sin supervisor</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            @error('user_id')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Descripción
            </label>
            <textarea 
                wire:model="description" 
                id="description" 
                rows="4" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 resize-none"
            ></textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="comments" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Comentarios
            </label>
            <input 
                wire:model="comments" 
                id="comments" 
                type="text" 
                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" 
            />
            @error('comments')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <button 
                type="button" 
                onclick="window.location.href='{{ route('areas.index') }}'"
                class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors duration-200 font-medium"
            >
                Cancelar
            </button>

            <button 
                type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 font-medium shadow-sm"
            >
                Guardar
            </button>
        </div>
                </form>
            </div>
        </div>
    </div>
</div>
