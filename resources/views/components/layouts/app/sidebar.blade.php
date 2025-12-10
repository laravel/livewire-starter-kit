<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('admin.dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>

        <flux:navlist variant="outline">
            <flux:navlist.group :heading="__('Dashboard')" class="grid">
                <flux:navlist.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')"
                    wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                <flux:navlist.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')"
                    wire:navigate>{{ __('Usuarios') }}</flux:navlist.item>
            </flux:navlist.group>

            <flux:navlist.group :heading="__('Production')" class="grid">
<<<<<<< HEAD
                <flux:navlist.item icon="calendar-days" :href="route('holidays.index')"
                    :current="request()->routeIs('holidays.*')" wire:navigate>{{ __('Holidays') }}</flux:navlist.item>
                <flux:navlist.item icon="clock" :href="route('shifts.index')"
                    :current="request()->routeIs('shifts.*')" wire:navigate>{{ __('Turnos') }}</flux:navlist.item>
                <flux:navlist.item icon="pause-circle" :href="route('break-times.index')"
                    :current="request()->routeIs('break-times.*')" wire:navigate>{{ __('Descansos') }}</flux:navlist.item>
=======
                <flux:navlist.item icon="calendar-days" :href="route('admin.holidays.index')"
                    :current="request()->routeIs('admin.holidays.*')" wire:navigate>{{ __('Holidays') }}</flux:navlist.item>
<<<<<<< HEAD
>>>>>>> 594aa2ae8968ad0d72db7a5a4977ef862ef188c0
=======
                <flux:navlist.item icon="clock" :href="route('admin.shifts.index')"
                    :current="request()->routeIs('admin.shifts.*')" wire:navigate>{{ __('Turnos') }}</flux:navlist.item>
>>>>>>> 555ab2209e164dfdd7dadecfe262b2c5e57ef8a9
            </flux:navlist.group>
            <flux:navlist.group :heading="__('Administración')" class="grid">
                <flux:navlist.item icon="rectangle-group" :href="route('admin.departments.index')"
                    :current="request()->routeIs('admin.departments.*')" wire:navigate>{{ __('Departamentos') }}
                </flux:navlist.item>
                <flux:navlist.item icon="shield-check" :href="route('admin.areas.index')"
                    :current="request()->routeIs('admin.areas.*')" wire:navigate>{{ __('Areas') }}</flux:navlist.item>
                <flux:navlist.item icon="shield-check" :href="route('admin.roles.index')"
                    :current="request()->routeIs('admin.roles.*')" wire:navigate>{{ __('Roles') }}</flux:navlist.item>
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

    @livewire('admin.components.toast-notification')

    @fluxScripts
</body>

</html>
