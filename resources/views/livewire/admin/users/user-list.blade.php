<div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white tracking-tight">Usuarios</h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gestiona los usuarios del sistema</p>
                </div>
                
                <a href="{{ route('admin.users.create') }}" 
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nuevo Usuario
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total</div>
                <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalUsers }}</div>
            </div>

            @foreach($usersByRole as $roleData)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">{{ $roleData->name }}</div>
                    <div class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $roleData->users_count }}</div>
                </div>
            @endforeach
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm mb-6">
            <div class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.300ms="search"
                                placeholder="Nombre, email o cuenta..."
                                class="block w-full pl-10 pr-3 py-2.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white focus:border-gray-900 dark:focus:border-white"
                            />
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rol</label>
                        <select wire:model.live="roleFilter" class="block w-full px-3 py-2.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white focus:border-gray-900 dark:focus:border-white">
                            <option value="">Todos</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Departamento</label>
                        <select wire:model.live="departmentFilter" class="block w-full px-3 py-2.5 text-sm bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-gray-900 dark:focus:ring-white focus:border-gray-900 dark:focus:border-white">
                            <option value="">Todos</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if($search || $roleFilter || $departmentFilter)
                    <div class="mt-4 flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Resultados filtrados
                        </div>
                        <button 
                            wire:click="clearFilters"
                            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium transition-colors"
                        >
                            Limpiar
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Users Table - Desktop -->
        <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="px-6 py-3 text-left">
                                <button wire:click="sortBy('name')" class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-900 dark:hover:text-white transition-colors">
                                    Usuario
                                    @if($sortField === 'name')
                                        <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left">
                                <button wire:click="sortBy('email')" class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-900 dark:hover:text-white transition-colors">
                                    Email
                                    @if($sortField === 'email')
                                        <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Área</th>
                            <th class="px-6 py-3 text-left">
                                <button wire:click="sortBy('created_at')" class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider hover:text-gray-900 dark:hover:text-white transition-colors">
                                    Registro
                                    @if($sortField === 'created_at')
                                        <svg class="w-3 h-3 {{ $sortDirection === 'asc' ? '' : 'rotate-180' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                        </svg>
                                    @endif
                                </button>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-9 w-9">
                                            <div class="h-9 w-9 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-700 dark:text-gray-300 font-medium text-sm">
                                                {{ $user->initials }}
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }} {{ $user->last_name }}</div>
                                            @if($user->account)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->account }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 dark:text-white">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->roles->isNotEmpty())
                                        @php
                                            $roleName = $user->roles->first()->name;
                                        @endphp
                                        <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                            {{ $roleName }}
                                        </span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Sin rol</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($user->areas->isNotEmpty())
                                        @foreach($user->areas as $area)
                                            <div class="text-sm text-gray-900 dark:text-white">{{ $area->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $area->department->name }}</div>
                                        @endforeach
                                    @else
                                        <span class="text-sm text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        @if($user->id !== auth()->id())
                                            <button wire:click="deleteUser({{ $user->id }})" wire:confirm="¿Estás seguro de que quieres eliminar este usuario?" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">No se encontraron usuarios</div>
                                        @if($search || $roleFilter || $departmentFilter)
                                            <button wire:click="clearFilters" class="mt-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium">Limpiar filtros</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- Users Cards - Mobile/Tablet -->
        <div class="lg:hidden space-y-4">
            @forelse($users as $user)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-700 dark:text-gray-300 font-medium text-sm">
                                {{ $user->initials }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-base font-medium text-gray-900 dark:text-white truncate">{{ $user->name }} {{ $user->last_name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ $user->email }}</div>
                                @if($user->account)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $user->account }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2 ml-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>
                            @if($user->id !== auth()->id())
                                <button wire:click="deleteUser({{ $user->id }})" wire:confirm="¿Estás seguro de que quieres eliminar este usuario?" class="inline-flex items-center justify-center w-8 h-8 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">Rol</div>
                            @if($user->roles->isNotEmpty())
                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                    {{ $user->roles->first()->name }}
                                </span>
                            @else
                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400">Sin rol</span>
                            @endif
                        </div>
                        <div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">Registro</div>
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->created_at->format('d/m/Y') }}</div>
                        </div>
                    </div>

                    @if($user->areas->isNotEmpty())
                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mb-1.5">Área</div>
                            @foreach($user->areas as $area)
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $area->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $area->department->name }}</div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-12 text-center">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">No se encontraron usuarios</div>
                    @if($search || $roleFilter || $departmentFilter)
                        <button wire:click="clearFilters" class="mt-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium">Limpiar filtros</button>
                    @endif
                </div>
            @endforelse

            @if($users->hasPages())
                <div class="pt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
