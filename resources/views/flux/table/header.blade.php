@php
    $classes = Flux::classes('');
@endphp

<thead {{ $attributes->class($classes) }}>
    {{ $slot }}
</thead>
