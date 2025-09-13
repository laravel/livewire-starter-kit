@php
    use App\Support\Sidebar;
    $sidebar = config('ui.sidebar', []);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-white dark:bg-zinc-800">
    <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

        <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
            <x-app-logo />
        </a>
        @foreach ($sidebar as $section)
            {{-- Group of nav items --}}
            @if ($section['type'] === 'group')
                <flux:navlist variant="outline">
                    <flux:navlist.group :heading="__($section['text'])" class="grid">
                        @foreach ($section['items'] as $item)
                            @if (Sidebar::shouldRender($item))
                                <flux:navlist.item href="{{ Sidebar::href($item) }}"
                                    :current="request()->routeIs(Sidebar::currentPattern($item))" wire:navigate>
                                    <span class="inline-flex items-center gap-2">
                                        @if (Sidebar::isFluxIcon($item))
                                            <flux:icon :name="$item['icon']" class="inline-block w-5 h-5" />
                                        @else
                                            <i class="{{ $item['icon'] }} w-4 h-4"></i>
                                        @endif
                                        {{ __($item['text']) }}
                                    </span>
                                </flux:navlist.item>
                            @endif
                        @endforeach
                    </flux:navlist.group>
                </flux:navlist>

                {{-- Single external/internal link --}}
            @elseif ($section['type'] === 'link')
                @if (Sidebar::shouldRender($section))
                    <flux:navlist variant="outline">
                        <flux:navlist.item href="{{ Sidebar::href($section) }}"
                            target="{{ $section['target'] ?? '_self' }}">
                            <span class="inline-flex items-center gap-2">
                                @if (Sidebar::isFluxIcon($section))
                                    <flux:icon :name="$section['icon']" class="inline-block w-5 h-5" />
                                @else
                                    <i class="{{ $section['icon'] }} w-4 h-4"></i>
                                @endif
                                {{ __($section['text']) }}
                            </span>
                        </flux:navlist.item>
                    </flux:navlist>
                @endif
            @endif
        @endforeach
        <flux:spacer />

        {{-- Theme toggle --}}
        @if (config('ui.toggle_theme', true))
            <flux:navlist variant="outline">
                <flux:navlist.item class="cursor-pointer" x-data x-on:click="$flux.dark = ! $flux.dark">
                    <template x-if="$flux.dark">
                        <span class="flex items-center">
                            <flux:icon.sun class="inline-block align-middle me-2 text-yellow-500" />
                            {{ __('Light Mode') }}
                        </span>
                    </template>
                    <template x-if="!$flux.dark">
                        <span class="flex items-center">
                            <flux:icon.moon class="inline-block align-middle me-2 text-blue-500" />
                            {{ __('Dark Mode') }}
                        </span>
                    </template>
                </flux:navlist.item>
            </flux:navlist>
        @endif
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
                </flux:menu.radio.group>

                <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
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
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                        {{ __('Log Out') }}
                    </flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full" data-test="logout-button">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

    @fluxScripts
</body>

</html>
