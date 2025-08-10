<?php

use App\Models\User;
use App\Models\Department;
use App\Models\Area;
use Spatie\Permission\Models\Role;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

new class extends Component {
    public ?int $userId = null;
    public string $name = '';
    public string $last_name = '';
    public string $account = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $changePassword = false;
    public ?int $department_id = null;
    public ?int $area_id = null;
    public string $selected_role = '';

    public function render(): mixed
    {
        $departments = Department::orderBy('name')->get();
        $areas = $this->department_id 
            ? Area::where('department_id', $this->department_id)->orderBy('name')->get()
            : collect();
        $roles = Role::orderBy('name')->get();
            
        return view('livewire.users.user-edit', [
            'departments' => $departments,
            'areas' => $areas,
            'roles' => $roles,
        ]);
    }

    public function mount(User $user): void
    {
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->last_name = $user->last_name ?? '';
        $this->account = $user->account ?? '';
        $this->email = $user->email;
        $this->selected_role = $user->roles->first()?->name ?? '';
        
        // Obtener el área supervisada por el usuario
        $supervisedArea = Area::where('user_id', $user->id)->first();
        if ($supervisedArea) {
            $this->area_id = $supervisedArea->id;
            $this->department_id = $supervisedArea->department_id;
        }
    }
    
    public function updatedDepartmentId(): void
    {
        $this->area_id = null;
    }

    protected function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'account' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->userId)],
            'department_id' => ['nullable', 'exists:departments,id'],
            'area_id' => ['nullable', 'exists:areas,id'],
            'selected_role' => ['required', 'exists:roles,name'],
        ];

        if ($this->changePassword) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }

    public function updateUser(): void
    {
        $validated = $this->validate();

        $user = User::find($this->userId);

        // Remove password from validated data if not changing
        if (!$this->changePassword) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Remove selected_role from validated data as it's handled separately
        unset($validated['selected_role']);

        $user->update($validated);
        
        // Actualizar rol usando Spatie Permission
        $user->syncRoles([$this->selected_role]);
        
        // Actualizar asignación de área
        // Primero, remover al usuario de cualquier área que supervise actualmente
        Area::where('user_id', $user->id)->update(['user_id' => null]);
        
        // Si se seleccionó un área y el rol es supervisor, asignar el área
        if ($this->area_id && $this->selected_role === 'Supervisor') {
            $area = Area::find($this->area_id);
            $area->update(['user_id' => $user->id]);
        }

        session()->flash('flash.banner', 'Usuario actualizado correctamente.');
        session()->flash('flash.bannerStyle', 'success');

        $this->redirect(route('users.index'));
    }

    public function toggleChangePassword(): void
    {
        $this->changePassword = !$this->changePassword;

        if (!$this->changePassword) {
            $this->password = '';
            $this->password_confirmation = '';
        }
    }

    public function cancel(): void
    {
        $this->redirect(route('users.index'));
    }
};
?>

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-2">
                <div class="flex items-center justify-center w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Editar Usuario</h1>
                    <p class="text-gray-600 dark:text-gray-400">Modifica la información del usuario en el sistema</p>
                </div>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-8">
                <form wire:submit="updateUser" class="space-y-8">
                    <!-- Información Personal -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                            Información Personal
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre *</label>
                                <input 
                                    type="text" 
                                    wire:model="name" 
                                    placeholder="Ingrese el nombre"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    required 
                                />
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Apellido</label>
                                <input 
                                    type="text" 
                                    wire:model="last_name"
                                    placeholder="Ingrese el apellido"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                />
                                @error('last_name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Información de Cuenta -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                            Información de Cuenta
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">                
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cuenta</label>
                                <input 
                                    type="text" 
                                    wire:model="account"
                                    placeholder="Número de cuenta (opcional)"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                />
                                @error('account')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>                

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email *</label>
                                <input 
                                    type="email" 
                                    wire:model="email"
                                    placeholder="usuario@ejemplo.com"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    required
                                />
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Rol y Asignación -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                            Rol y Asignación
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rol *</label>
                                <select 
                                    wire:model.live="selected_role" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Seleccionar rol</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                                    @endforeach
                                </select>
                                @error('selected_role')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Departamento</label>
                                <select 
                                    wire:model.live="department_id" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                                >
                                    <option value="">Seleccionar departamento</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                                @error('department_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Área {{ $selected_role === 'Supervisor' ? '*' : '' }}
                                </label>
                                <select 
                                    wire:model="area_id" 
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white disabled:opacity-50 disabled:cursor-not-allowed"
                                    {{ !$department_id ? 'disabled' : '' }}
                                >
                                    <option value="">Seleccionar área</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                                @error('area_id')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror                                
                            </div>
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                            <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                            Contraseña
                        </h3>
                        
                        <div class="mb-6">
                            <label class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    wire:model.live="changePassword" 
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                />
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Cambiar contraseña</span>
                            </label>
                        </div>

                        @if ($changePassword)
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nueva Contraseña *</label>
                                    <input 
                                        type="password" 
                                        wire:model="password"
                                        placeholder="Mínimo 8 caracteres"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    />
                                    @error('password')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Confirmar Contraseña *</label>
                                    <input 
                                        type="password" 
                                        wire:model="password_confirmation"
                                        placeholder="Repite la nueva contraseña"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    />
                                    @error('password_confirmation')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        @endif  
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-4 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <button 
                            type="button"
                            wire:click="cancel"
                            class="px-6 py-2.5 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition-colors font-medium"
                        >
                            Cancelar
                        </button>

                        <button 
                            type="submit" 
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium flex items-center gap-2"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Actualizar Usuario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>