@php
    $classes = Flux::classes('');
@endphp

<tr {{ $attributes->class($classes) }}>
    {{ $slot }}
</tr>
