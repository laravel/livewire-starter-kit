@php
    $classes = Flux::classes('divide-y divide-zinc-800/10 dark:divide-white/20 [&:not(:has(*))]:border-t-0!');
@endphp

<tbody {{ $attributes->class($classes) }}>
    {{ $slot }}
</tbody>
