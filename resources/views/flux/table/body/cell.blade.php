@props([
    'align' => 'left',
])
@php
    $classes = Flux::classes('py-3 px-3 first:pl-0 last:pr-0 text-sm  text-zinc-500 dark:text-zinc-300')
        ->add($align === 'right' ? 'text-right' : '');
@endphp

<td {{ $attributes->class($classes) }}>
    {{ $slot }}
</td>
