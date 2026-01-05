<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Mi Perfil</h1>
        <p class="text-gray-600 dark:text-gray-400">Actualiza tu información personal</p>
    </div>

    @if (session()->has('success'))
        <div class="rounded-lg bg-green-50 dark:bg-green-900/20 p-4 mb-4">
            <p class="text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="rounded-lg bg-red-50 dark:bg-red-900/20 p-4 mb-4">
            <p class="text-sm text-red-700 dark:text-red-300">{{ session('error') }}</p>
        </div>
    @endif

    @if($employee)
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Profile Info Card --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Información Personal</h2>
                
                <form wire:submit="updateProfile" class="space-y-4">
                    <div>
                        <flux:input 
                            wire:model="name" 
                            label="Nombre" 
                            placeholder="Tu nombre"
                        />
                        @error('name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:input 
                            wire:model="last_name" 
                            label="Apellido" 
                            placeholder="Tu apellido"
                        />
                        @error('last_name') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    {{-- Read-only fields --}}
                    <div>
                        <flux:input 
                            value="{{ $employee->employee_number }}" 
                            label="Número de Empleado" 
                            disabled
                        />
                        <p class="text-xs text-gray-500 mt-1">Este campo no puede ser modificado</p>
                    </div>

                    <div>
                        <flux:input 
                            value="{{ $employee->email }}" 
                            label="Email" 
                            disabled
                        />
                        <p class="text-xs text-gray-500 mt-1">Contacta al administrador para cambiar tu email</p>
                    </div>

                    <div>
                        <flux:input 
                            value="{{ $employee->area?->name ?? 'No asignada' }}" 
                            label="Área" 
                            disabled
                        />
                    </div>

                    <div>
                        <flux:input 
                            value="{{ $employee->shift?->name ?? 'No asignado' }}" 
                            label="Turno" 
                            disabled
                        />
                    </div>

                    <div class="pt-4">
                        <flux:button type="submit" variant="primary">
                            Guardar Cambios
                        </flux:button>
                    </div>
                </form>
            </div>

            {{-- Password Card --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Cambiar Contraseña</h2>
                
                <form wire:submit="updatePassword" class="space-y-4">
                    <div>
                        <flux:input 
                            wire:model="current_password" 
                            type="password"
                            label="Contraseña Actual" 
                            placeholder="••••••••"
                        />
                        @error('current_password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:input 
                            wire:model="password" 
                            type="password"
                            label="Nueva Contraseña" 
                            placeholder="••••••••"
                        />
                        @error('password') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <flux:input 
                            wire:model="password_confirmation" 
                            type="password"
                            label="Confirmar Contraseña" 
                            placeholder="••••••••"
                        />
                    </div>

                    <div class="pt-4">
                        <flux:button type="submit" variant="primary">
                            Actualizar Contraseña
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-yellow-200 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20 p-6">
            <div class="flex items-center gap-3">
                <flux:icon.exclamation-triangle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
                <div>
                    <h3 class="font-semibold text-yellow-800 dark:text-yellow-200">Registro no encontrado</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-300">
                        No se encontró un registro de empleado asociado a tu cuenta. Contacta al administrador.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
