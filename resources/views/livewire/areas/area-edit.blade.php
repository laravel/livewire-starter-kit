<?php

use Livewire\Volt\Component;
use App\Models\Area;
use App\Models\User;
use App\Models\Department;

new class extends Component {
    public Area $area;
    public $departments = [];
    public $name = '';
    public $description = '';
    public $comments = '';
    public $department_id = '';
    public $user_id = null;
    public $users = [];

    public function mount(Area $area)
    {
        $this->area = $area;
        $this->departments = Department::orderBy('name')->get();
        $this->name = $area->name;
        $this->description = $area->description;
        $this->comments = $area->comments;
        $this->department_id = $area->department_id;
        $this->user_id = $area->user_id;
        
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

        redirect()->route('areas.index');
    }
}; ?>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Área</h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Modifica la información del área
                    </p>
                </div>
                <div>
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
        
        <!-- Formulario -->
        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-5">
                <form wire:submit="updateArea" class="space-y-6">
        <div>
            <label for="name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nombre</label>
            <input wire:model="name" id="name" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
            @error('name')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="department_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Departamento</label>
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
            @error('department_id')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="user_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Supervisor</label>
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
            @error('user_id')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Descripción</label>
            <textarea 
                wire:model="description" 
                id="description" 
                rows="4" 
                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
            ></textarea>
            @error('description')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="comments" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Comentarios</label>
            <input wire:model="comments" id="comments" type="text" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
            @error('comments')
                <p class="mt-2 text-sm text-red-600 dark:text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('areas.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                Cancelar
            </a>

            <button
                type="submit"
                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200"
            >
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Actualizar
            </button>
        </div>
    </form>
</div>
