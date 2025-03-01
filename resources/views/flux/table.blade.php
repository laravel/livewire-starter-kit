@props([
    'paginate' => null,
])
@php
use function Livewire\Volt\{on};
$classes = Flux::classes('[:where(&)]:min-w-full table-fixed text-zinc-800 divide-y divide-zinc-800/10 dark:divide-white/20 text-zinc-800 whitespace-nowrap [&_dialog]:whitespace-normal [&_[popover]]:whitespace-normal');
@endphp

<table {{ $attributes->class($classes) }}>
    {{ $slot }}
</table>
@if ($paginate)
    {{ $paginate->links() }}
@endif
