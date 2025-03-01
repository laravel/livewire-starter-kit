@php
    $classes = Flux::classes('flex py-6 flex-col');
@endphp
<div {{ $attributes->class($classes) }}>
    {{ $slot }}
</div>
