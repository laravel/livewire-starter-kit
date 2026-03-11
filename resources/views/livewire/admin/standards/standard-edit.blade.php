<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.standards.index') }}" class="inline-flex items-center justify-center w-10 h-10 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border-2 border-gray-200 dark:border-gray-600 rounded-md transition-colors" title="Volver">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
        </a>
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Editar estándar</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Modificar información del estándar de la parte {{ $standard->part->number }}</p>
        </div>
    </div>

    <div class="flex items-center gap-2">
        @if($standard->active)
            <span class="px-3 py-1 text-xs font-medium rounded-full border-2 border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300">Activo</span>
        @else
            <span class="px-3 py-1 text-xs font-medium rounded-full border-2 border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-300">Inactivo</span>
        @endif
    </div>

    @if(!$useNewConfigSystem)
        <div class="p-4 rounded-lg border-2 border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                    <h3 class="text-sm font-medium text-amber-800 dark:text-amber-200">Estándar con formato antiguo</h3>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">Este estándar usa el sistema legacy. Puede migrarlo al nuevo sistema de configuraciones múltiples.</p>
                    <button type="button" wire:click="migrateToNewSystem" class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-md transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Migrar al nuevo sistema
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden divide-y divide-gray-200 dark:divide-gray-700">
        <form wire:submit="updateStandard" class="p-6 space-y-6">
                    <!-- Part -->
                    <div>
                        <label for="part_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Parte <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="part_id" id="part_id"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            required>
                            <option value="">Seleccione una parte</option>
                            @foreach($parts as $part)
                                <option value="{{ $part->id }}">{{ $part->number }} - {{ Str::limit($part->description, 40) }}</option>
                            @endforeach
                        </select>
                        @error('part_id')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    @if($useNewConfigSystem)
                        <!-- Configurations Section (New System) -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Configuraciones de Produccion</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Defina las diferentes configuraciones de productividad segun tipo de estacion y personal
                                    </p>
                                </div>
                                <button type="button" wire:click="addConfiguration"
                                    class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Agregar Configuracion
                                </button>
                            </div>

                            @error('configurations')
                                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded-lg dark:bg-red-900/30 dark:border-red-600 dark:text-red-400">
                                    {{ $message }}
                                </div>
                            @enderror

                            <!-- Configurations List -->
                            <div class="space-y-4">
                                @foreach($configurations as $index => $config)
                                    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700 relative">
                                        <!-- Configuration Header -->
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center space-x-3">
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-sm font-semibold rounded-full">
                                                    {{ $index + 1 }}
                                                </span>
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    Configuracion #{{ $index + 1 }}
                                                    @if(!empty($config['id']))
                                                        <span class="text-xs text-gray-400">(ID: {{ $config['id'] }})</span>
                                                    @else
                                                        <span class="text-xs text-green-600 dark:text-green-400">(Nueva)</span>
                                                    @endif
                                                </span>
                                                @if($config['is_default'])
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                        Predeterminada
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center space-x-2">
                                                @if(!$config['is_default'])
                                                    <button type="button" wire:click="setDefaultConfiguration({{ $index }})"
                                                        class="text-purple-600 hover:text-purple-800 dark:text-purple-400 dark:hover:text-purple-300 text-sm"
                                                        title="Establecer como predeterminada">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                                @if(count($configurations) > 1)
                                                    <button type="button" wire:click="removeConfiguration({{ $index }})"
                                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                                                        title="Eliminar configuracion">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Configuration Fields -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                            <!-- Workstation Type -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Tipo de Estacion <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model.live="configurations.{{ $index }}.workstation_type"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                                    @foreach($workstationTypes as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error("configurations.{$index}.workstation_type")
                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Workstation (Dynamic based on type) -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Estacion Especifica
                                                </label>
                                                <select wire:model="configurations.{{ $index }}.workstation_id"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                                    <option value="">Sin asignar</option>
                                                    @php
                                                        $workstations = $this->getWorkstationsForType($config['workstation_type']);
                                                    @endphp
                                                    @foreach($workstations as $ws)
                                                        <option value="{{ $ws['id'] }}">{{ $ws['name'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error("configurations.{$index}.workstation_id")
                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Persons Required -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Personas Requeridas <span class="text-red-500">*</span>
                                                </label>
                                                <select wire:model="configurations.{{ $index }}.persons_required"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm">
                                                    @foreach($personsOptions as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                @error("configurations.{$index}.persons_required")
                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            <!-- Units Per Hour -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                    Unidades/Hora <span class="text-red-500">*</span>
                                                </label>
                                                <input type="number" wire:model="configurations.{{ $index }}.units_per_hour"
                                                    min="1" placeholder="Ej: 50"
                                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm" />
                                                @error("configurations.{$index}.units_per_hour")
                                                    <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>

                                        <!-- Notes -->
                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                Notas (opcional)
                                            </label>
                                            <input type="text" wire:model="configurations.{{ $index }}.notes"
                                                placeholder="Observaciones adicionales..."
                                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white text-sm" />
                                            @error("configurations.{$index}.notes")
                                                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            @if(count($configurations) === 0)
                                <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <p class="mt-2">No hay configuraciones. Haga clic en "Agregar Configuracion" para comenzar.</p>
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Legacy System Fields -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Configuracion Legacy</h3>

                            <!-- Units Per Hour -->
                            <div class="mb-6">
                                <label for="units_per_hour" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Unidades por Hora <span class="text-red-500">*</span>
                                </label>
                                <input wire:model="units_per_hour" id="units_per_hour" type="number" min="1"
                                    placeholder="Ej: 50"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400"
                                    required />
                                @error('units_per_hour')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Work Stations -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div>
                                    <label for="work_table_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Mesa de Trabajo
                                    </label>
                                    <select wire:model="work_table_id" id="work_table_id"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Seleccione una mesa</option>
                                        @foreach($workTables as $table)
                                            <option value="{{ $table->id }}">{{ $table->number }}</option>
                                        @endforeach
                                    </select>
                                    @error('work_table_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="semi_auto_work_table_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Mesa Semi-Automatica
                                    </label>
                                    <select wire:model="semi_auto_work_table_id" id="semi_auto_work_table_id"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Seleccione una mesa semi-auto</option>
                                        @foreach($semiAutoWorkTables as $semiAuto)
                                            <option value="{{ $semiAuto->id }}">{{ $semiAuto->number }}</option>
                                        @endforeach
                                    </select>
                                    @error('semi_auto_work_table_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="machine_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Maquina
                                    </label>
                                    <select wire:model="machine_id" id="machine_id"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white">
                                        <option value="">Seleccione una maquina</option>
                                        @foreach($machines as $machine)
                                            <option value="{{ $machine->id }}">{{ $machine->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('machine_id')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Persons 1, 2, 3 -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="persons_1" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Personas 1
                                    </label>
                                    <input wire:model="persons_1" id="persons_1" type="number" min="1"
                                        placeholder="0"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" />
                                    @error('persons_1')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="persons_2" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Personas 2
                                    </label>
                                    <input wire:model="persons_2" id="persons_2" type="number" min="1"
                                        placeholder="0"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" />
                                    @error('persons_2')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="persons_3" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Personas 3
                                    </label>
                                    <input wire:model="persons_3" id="persons_3" type="number" min="1"
                                        placeholder="0"
                                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" />
                                    @error('persons_3')
                                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Active Status -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <div class="flex items-center">
                            <label class="flex items-center">
                                <input wire:model="active" type="checkbox" id="active"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">Estandar activo</span>
                            </label>
                            @error('active')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Descripcion
                        </label>
                        <textarea wire:model="description" id="description" rows="3"
                            placeholder="Descripcion detallada del estandar..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 resize-none"></textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('admin.standards.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 rounded-md transition-colors">Cancelar</a>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md shadow-sm transition-colors">Actualizar estándar</button>
            </div>
        </form>
    </div>
</div>
