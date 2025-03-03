<div class="flex items-start max-md:flex-col">
    <div class="mr-10 w-full pb-4 md:w-[220px]">
        <flux:navlist>
            <flux:navlist.item @class(['bg-zinc-800/[4%] dark:bg-white/[7%] text-zinc-800 dark:text-white' => request()->routeIs('settings.profile')]) :href="route('settings.profile')" wire:navigate>
                {{ __('Profile') }}
            </flux:navlist.item>
            <flux:navlist.item @class(['bg-zinc-800/[4%] dark:bg-white/[7%] text-zinc-800 dark:text-white' => request()->routeIs('settings.password')]) :href="route('settings.password')" wire:navigate>
                {{ __('Password') }}
            </flux:navlist.item>
            <flux:navlist.item @class(['bg-zinc-800/[4%] dark:bg-white/[7%] text-zinc-800 dark:text-white' => request()->routeIs('settings.appearance')]) :href="route('settings.appearance')" wire:navigate>
                {{ __('Appearance') }}
            </flux:navlist.item>
        </flux:navlist>
    </div>

    <flux:separator class="md:hidden" />

    <div class="flex-1 self-stretch max-md:pt-6">
        <flux:heading>{{ $heading ?? '' }}</flux:heading>
        <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>

        <div class="mt-5 w-full max-w-lg">
            {{ $slot }}
        </div>
    </div>
</div>
