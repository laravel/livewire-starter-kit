<div class="space-y-6">
    {{-- Error and Success Messages --}}
    @if ($error_message)
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ $error_message }}</span>
        </div>
    @endif

    @if ($success_message)
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ $success_message }}</span>
        </div>
    @endif

    {{-- Step 1: Configuration Panel --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Step 1: Configure Capacity Parameters
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Purchase Order Selection --}}
                <div>
                    <label for="po_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Purchase Order *
                    </label>
                    <select wire:model="po_id" id="po_id"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select PO</option>
                        @foreach ($purchase_orders as $po)
                            <option value="{{ $po->id }}">
                                PO: {{ $po->po_number }} - {{ $po->part->number ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                    @error('po_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Number of Persons --}}
                <div>
                    <label for="num_persons" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Number of Persons *
                    </label>
                    <input type="number" wire:model="num_persons" id="num_persons" min="1" max="100"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('num_persons') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- Start Date --}}
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Start Date *
                    </label>
                    <input type="date" wire:model="start_date" id="start_date"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('start_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                {{-- End Date --}}
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        End Date *
                    </label>
                    <input type="date" wire:model="end_date" id="end_date"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('end_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Shifts Selection (Multi-select) --}}
            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Select Shifts *
                </label>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @foreach ($shifts as $shift)
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="selected_shifts" value="{{ $shift->id }}"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                                {{ $shift->name }}
                            </span>
                        </label>
                    @endforeach
                </div>
                @error('selected_shifts') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Calculate Button --}}
            <div class="mt-6">
                <button wire:click="calculateCapacity" type="button"
                    class="inline-flex items-center px-6 py-3 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:border-indigo-700 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Calculate Capacity
                </button>
            </div>
        </div>
    </div>

    {{-- Step 2: Capacity Display --}}
    @if ($is_capacity_calculated)
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Capacity Summary
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Total Available Hours</p>
                        <p class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                            {{ number_format($total_available_hours, 2) }}
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Used Hours</p>
                        <p class="text-3xl font-bold text-amber-600 dark:text-amber-400">
                            {{ number_format($total_available_hours - $remaining_hours, 2) }}
                        </p>
                    </div>

                    <div class="bg-white dark:bg-gray-700 rounded-lg p-4 shadow">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Remaining Hours</p>
                        <p class="text-3xl font-bold {{ $remaining_hours > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ number_format($remaining_hours, 2) }}
                        </p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="mt-6">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Capacity Utilization</span>
                        <span>{{ $total_available_hours > 0 ? number_format((($total_available_hours - $remaining_hours) / $total_available_hours) * 100, 1) : 0 }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                        <div class="h-4 rounded-full transition-all duration-500 {{ $remaining_hours > 0 ? 'bg-green-500' : 'bg-red-500' }}"
                            style="width: {{ $total_available_hours > 0 ? min((($total_available_hours - $remaining_hours) / $total_available_hours) * 100, 100) : 0 }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Add Work Orders --}}
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                    Step 2: Add Work Orders
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    {{-- Part Selection --}}
                    <div>
                        <label for="current_part_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Part *
                        </label>
                        <select wire:model="current_part_id" id="current_part_id"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Select Part</option>
                            @foreach ($parts as $part)
                                <option value="{{ $part->id }}">{{ $part->number }} - {{ Str::limit($part->description, 30) }}</option>
                            @endforeach
                        </select>
                        @error('current_part_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label for="current_quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Quantity *
                        </label>
                        <input type="number" wire:model="current_quantity" id="current_quantity" min="1"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('current_quantity') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Assembly Mode --}}
                    <div>
                        <label for="current_assembly_mode" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Assembly Mode *
                        </label>
                        <select wire:model="current_assembly_mode" id="current_assembly_mode"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="1_person">1 Person</option>
                            <option value="2_persons">2 Persons</option>
                            <option value="3_persons">3 Persons</option>
                        </select>
                        @error('current_assembly_mode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Add Button --}}
                    <div class="flex items-end">
                        <button wire:click="addWorkOrder" type="button"
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-300 disabled:opacity-25 transition">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Add WO
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4: Work Orders List --}}
        @if (count($work_orders) > 0)
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Work Orders Queue ({{ count($work_orders) }} items)
                    </h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Part Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Assembly Mode</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Required Hours</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($work_orders as $index => $wo)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $wo['part_number'] }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">{{ Str::limit($wo['part_description'], 40) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ number_format($wo['quantity']) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                {{ str_replace('_', ' ', ucfirst($wo['assembly_mode'])) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600 dark:text-indigo-400">
                                            {{ number_format($wo['required_hours'], 2) }} hrs
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <button wire:click="removeWorkOrder({{ $index }})" type="button"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Generate SentList Button --}}
                    <div class="mt-6 flex justify-between items-center">
                        <button wire:click="resetCalculator" type="button"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:bg-gray-500 active:bg-gray-700 focus:outline-none focus:border-gray-700 focus:ring focus:ring-gray-300 disabled:opacity-25 transition">
                            Reset Calculator
                        </button>

                        <button wire:click="generateSentList" type="button"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-widest hover:from-green-500 hover:to-emerald-500 active:from-green-700 active:to-emerald-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-300 disabled:opacity-25 transition shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Generate SentList
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
