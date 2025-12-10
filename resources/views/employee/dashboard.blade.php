<x-layouts.employee :title="__('Employee Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Panel de Empleado</h1>
            <p class="text-gray-600 dark:text-gray-400">Bienvenido a tu panel personal</p>
        </div>
        
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Mi Perfil</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Ver y editar tu información</p>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Horarios</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Consultar tus horarios</p>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 p-4">
                <h3 class="font-semibold text-gray-900 dark:text-white">Asistencia</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Registro de asistencia</p>
            </div>
        </div>
        
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-placeholder-pattern class="absolute inset-0 size-full stroke-gray-900/20 dark:stroke-neutral-100/20" />
        </div>
    </div>
</x-layouts.employee>
