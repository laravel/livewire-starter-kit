@props([
    'noWidth' => false,
])
@php
    $classes = Flux::classes('w-full flex items-center')
        ->add($noWidth ? '' : 'py-6');
@endphp

<div {{ $attributes->class($classes) }}>
    {{ $slot }}
</div>
