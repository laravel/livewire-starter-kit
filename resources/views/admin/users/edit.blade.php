<x-layouts.app :title="__('Editar Usuario')">
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @livewire('users.user-edit', ['user' => $user])
        </div>
    </div>
</x-layouts.app>
