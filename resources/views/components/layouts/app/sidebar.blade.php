<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('admin.dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse"
            wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Dashboard')" class="grid">
                <flux:navlist.item icon="home" :href="route('admin.dashboard')"
                    :current="request()->routeIs('admin.dashboard')" wire:navigate>{{ __('Dashboard') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Órdenes')" class="grid">
                <flux:navlist.item icon="document-text" :href="route('admin.purchase-orders.index')"
                    :current="request()->routeIs('admin.purchase-orders.*')" wire:navigate>{{ __('Purchase Orders') }}
                </flux:navlist.item>
                <flux:navlist.item icon="clipboard-document-check" :href="route('admin.work-orders.index')"
                    :current="request()->routeIs('admin.work-orders.*')" wire:navigate>{{ __('Manage PO') }}
                </flux:navlist.item>
                <flux:navlist.item icon="clipboard-document-list" :href="route('admin.statuses-wo.index')"
                    :current="request()->routeIs('admin.statuses-wo.*')" wire:navigate>{{ __('Estados') }}
                </flux:navlist.item>
                <flux:navlist.item icon="calculator" :href="route('admin.capacity.wizard')"
                    :current="request()->routeIs('admin.capacity.*', 'admin.sent-lists.*')" wire:navigate>
                    {{ __('Capacidad') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Catálogos')" class="grid">
                <flux:navlist.item icon="cube" :href="route('admin.parts.index')"
                    :current="request()->routeIs('admin.parts.*')" wire:navigate>{{ __('Partes') }}
                </flux:navlist.item>
                <flux:navlist.item icon="currency-dollar" :href="route('admin.prices.index')"
                    :current="request()->routeIs('admin.prices.*')" wire:navigate>{{ __('Precios') }}
                </flux:navlist.item>
                <flux:navlist.item icon="chart-bar" :href="route('admin.standards.index')"
                    :current="request()->routeIs('admin.standards.*')" wire:navigate>{{ __('Estándares') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Producción')" class="grid">
                <flux:navlist.item icon="calendar-days" :href="route('admin.holidays.index')"
                    :current="request()->routeIs('admin.holidays.*')" wire:navigate>{{ __('Días Festivos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="clock" :href="route('admin.shifts.index')"
                    :current="request()->routeIs('admin.shifts.*')" wire:navigate>{{ __('Turnos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="pause-circle" :href="route('admin.break-times.index')"
                    :current="request()->routeIs('admin.break-times.*')" wire:navigate>{{ __('Descansos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="bolt" :href="route('admin.over-times.index')"
                    :current="request()->routeIs('admin.over-times.*')" wire:navigate>{{ __('Tiempo Extra') }}
                </flux:navlist.item>
                <flux:navlist.item icon="cube" :href="route('admin.kits.index')"
                    :current="request()->routeIs('admin.kits.*')" wire:navigate>{{ __('Kits') }}
                </flux:navlist.item>
                <flux:navlist.item icon="queue-list" :href="route('admin.lots.index')"
                    :current="request()->routeIs('admin.lots.*')" wire:navigate>{{ __('Lotes') }}
                </flux:navlist.item>
                <flux:navlist.item icon="truck" :href="route('admin.sent-lists.display')"
                    :current="request()->routeIs('admin.sent-lists.display')" wire:navigate>{{ __('Lista de Envío') }}
                </flux:navlist.item>
                <flux:navlist.item icon="signal" :href="route('admin.production-statuses.index')"
                    :current="request()->routeIs('admin.production-statuses.*')" wire:navigate>
                    {{ __('Estados de Producción') }}
                </flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Administración')" class="grid">
                <flux:navlist.item icon="users" :href="route('admin.users.index')"
                    :current="request()->routeIs('admin.users.*')" wire:navigate>{{ __('Usuarios') }}
                </flux:navlist.item>
                <flux:navlist.item icon="user-group" :href="route('admin.employees.index')"
                    :current="request()->routeIs('admin.employees.*')" wire:navigate>{{ __('Empleados') }}
                </flux:navlist.item>
                <flux:navlist.item icon="rectangle-group" :href="route('admin.departments.index')"
                    :current="request()->routeIs('admin.departments.*')" wire:navigate>{{ __('Departamentos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="map-pin" :href="route('admin.areas.index')"
                    :current="request()->routeIs('admin.areas.*')" wire:navigate>{{ __('Áreas') }}
                </flux:navlist.item>
                <flux:navlist.item icon="table-cells" :href="route('admin.tables.index')"
                    :current="request()->routeIs('admin.tables.*')" wire:navigate>{{ __('Mesas') }}
                </flux:navlist.item>
                <flux:navlist.item icon="wrench-screwdriver" :href="route('admin.semi-automatics.index')"
                    :current="request()->routeIs('admin.semi-automatics.*')" wire:navigate>
                    {{ __('Semi-Automáticos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="cog-6-tooth" :href="route('admin.machines.index')"
                    :current="request()->routeIs('admin.machines.*')" wire:navigate>{{ __('Máquinas') }}
                </flux:navlist.item>
                <flux:navlist.item icon="shield-check" :href="route('admin.roles.index')"
                    :current="request()->routeIs('admin.roles.*')" wire:navigate>{{ __('Roles') }}
                </flux:navlist.item>
                <flux:navlist.item icon="key" :href="route('admin.permissions.index')"
                    :current="request()->routeIs('admin.permissions.*')" wire:navigate>{{ __('Permisos') }}
                </flux:navlist.item>
            </flux:navlist.group>
        </flux:navlist>

        <flux:spacer />

        <!-- Desktop User Menu -->
        <flux:dropdown class="hidden lg:block" position="bottom" align="start">
            <flux:profile :name="auth()->user()->name" :initials="auth()->user()->initials()"
                icon:trailing="chevrons-up-down" />

            <flux:menu class="w-[220px]">
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('admin.settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:sidebar>

    <!-- Mobile User Menu -->
    <flux:header class="lg:hidden">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

        <flux:spacer />

        <flux:dropdown position="top" align="end">
            <flux:profile :initials="auth()->user()->initials()" icon-trailing="chevron-down" />

            <flux:menu>
                <flux:menu.radio.group>
                    <div class="p-0 text-sm font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                            <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                <span
                                    class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                    {{ auth()->user()->initials() }}
                                </span>
                            </span>

                            <div class="grid flex-1 text-start text-sm leading-tight">
                                <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                            </div>
                        </div>
                    </div>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <flux:menu.radio.group>
                    <flux:menu.item :href="route('admin.settings.profile')" icon="cog" wire:navigate>
                        {{ __('Settings') }}</flux:menu.item>
                </flux:menu.radio.group>

                <flux:menu.separator />

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

    {{ $slot }}

    {{-- @livewire('admin.components.toast-notification') --}}

    @fluxScripts
</body>

</html>
