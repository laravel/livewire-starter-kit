@props([
    'avatar' => null,
    'name' => null,
    'initials' => null,
    'iconTrailing' => null,
])

@php
    $classes = Flux::classes()
        ->add('group flex items-center rounded-lg')
        ->add('[ui-dropdown>&]:w-full') // Without this, the "name" won't get truncated in a sidebar dropdown...
        ->add('p-1 hover:bg-zinc-800/5 dark:hover:bg-white/10');
@endphp

<button type="button" {{ $attributes->class($classes) }} data-flux-profile>
    <div class="size-8 shrink-0 overflow-hidden rounded-lg bg-zinc-400">
        <?php if($initials): ?>

        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg text-sm">
            <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                {{ $initials }}
            </span>
        </span>

        <?php else: ?>

        <?php if (is_string($avatar)): ?>

        <img src="{{ $avatar }}" />

        <?php else: ?>

        {{ $avatar }}

        <?php endif; ?>

        <?php endif; ?>
    </div>

    <?php if ($name): ?>

    <span class="ml-2 truncate text-sm font-medium text-zinc-500 group-hover:text-zinc-800 dark:text-white/80 group-hover:dark:text-white">
        {{ $name }}
    </span>

    <?php endif; ?>

    <?php if ($iconTrailing): ?>

    <div class="ml-auto flex size-8 shrink-0 items-center justify-center">
        <x-dynamic-component :component="'icon.' . $iconTrailing" variant="micro" class="text-zinc-400 group-hover:text-zinc-800 dark:text-white/80 group-hover:dark:text-white" />
    </div>

    <?php endif; ?>
</button>
