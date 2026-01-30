<x-layouts.app.sidebar :title="$title ?? null">
    <flux:main>
        @if(isset($header))
            <flux:header class="mb-6">
                {{ $header }}
            </flux:header>
        @endif
        
        {{ $slot }}
    </flux:main>
</x-layouts.app.sidebar>
