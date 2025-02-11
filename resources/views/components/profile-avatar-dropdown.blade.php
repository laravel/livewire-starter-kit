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
    <div class="shrink-0 size-8 bg-zinc-400 rounded-lg overflow-hidden">
        <?php if($initials): ?>
            <span class="relative flex shrink-0 h-8 w-8 overflow-hidden rounded-lg text-sm"><span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">{{ $initials }}</span></span>
        <?php else: ?>
            <?php if (is_string($avatar)): ?>
                <img src="{{ $avatar }}" />
            <?php else: ?>
                {{ $avatar }}
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($name): ?>
        <span class="ml-2 text-sm text-zinc-500 dark:text-white/80 group-hover:text-zinc-800 group-hover:dark:text-white font-medium truncate">
            {{ $name }}
        </span>
    <?php endif; ?>

    <?php if ($iconTrailing): ?>
        <div class="shrink-0 ml-auto size-8 flex justify-center items-center">
            <x-dynamic-component :component="'icon.' . $iconTrailing" variant="micro" class="text-zinc-400 dark:text-white/80 group-hover:text-zinc-800 group-hover:dark:text-white" />
        </div>
    <?php endif; ?>
</button>
