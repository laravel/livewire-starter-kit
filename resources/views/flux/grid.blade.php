@props([
    'cols' => null,
])
@php
    $classes = Flux::classes('grid gap-x-4 gap-y-6');

    if ($cols) {
        $classes->add(match($cols) {
            2 => 'grid-cols-2',
            3 => 'grid-cols-3',
            4 => 'grid-cols-4',
            5 => 'grid-cols-5',
            6 => 'grid-cols-6',
            7 => 'grid-cols-7',
            8 => 'grid-cols-8',
            9 => 'grid-cols-9',
            10 => 'grid-cols-10',
            11 => 'grid-cols-11',
            12 => 'grid-cols-12',
            default => 'grid-cols-' . $cols,
        });
    }

@endphp
<div {{ $attributes->class($classes) }}>
    {{ $slot }}
</div>
