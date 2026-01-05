<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Empleado</h1>
        <p class="text-gray-600 dark:text-gray-400">Bienvenido, {{ auth()->user()->name }}</p>
    </div>

    @if($employee)
        <div class="grid gap-4 md:grid-cols-3">
            {{-- Info Card --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900">
                        <flux:icon.user class="h-6 w-6 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">{{ $employee->full_name }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">#{{ $employee->employee_number }}</p>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Posición:</span> {{ $employee->position ?? 'No asignada' }}
                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Email:</span> {{ $employee->email }}
                    </p>
                </div>
            </div>

            {{-- Area Card --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900">
                        <flux:icon.map-pin class="h-6 w-6 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Área</h3>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Nombre:</span> {{ $employee->area?->name ?? 'No asignada' }}
                    </p>
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Departamento:</span> {{ $employee->area?->department?->name ?? 'N/A' }}
                    </p>
                </div>
            </div>

            {{-- Shift Card --}}
            <div class="rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900">
                        <flux:icon.clock class="h-6 w-6 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Turno</h3>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Nombre:</span> {{ $employee->shift?->name ?? 'No asignado' }}
                    </p>
                    @if($employee->shift)
                        <p class="text-gray-600 dark:text-gray-400">
                            <span class="font-medium">Horario:</span> 
                            {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('H:i') }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="mt-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Acciones Rápidas</h2>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('employee.profile') }}" wire:navigate
                    class="flex items-center gap-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                    <flux:icon.pencil-square class="h-5 w-5 text-gray-500" />
                    <span class="text-gray-700 dark:text-gray-300">Editar Perfil</span>
                </a>
                <a href="{{ route('employee.settings.profile') }}" wire:navigate
                    class="flex items-center gap-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4 hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                    <flux:icon.cog-6-tooth class="h-5 w-5 text-gray-500" />
                    <span class="text-gray-700 dark:text-gray-300">Configuración</span>
                </a>
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
