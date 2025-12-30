<div class="max-w-md mx-auto bg-zinc-50 min-h-screen">

    {{-- HEADER --}}
    <div class="bg-white sticky top-0 z-10 border-b">
        <div class="px-4 py-3">

            {{-- LOGO --}}
            <div class="flex ">
                <img
                    src="{{ asset('images/charlie-logo.jpg') }}"
                    alt="Charlie"
                    class="h-16 object-contain"
                />
            </div>

            {{-- SEARCH + BURGER --}}
            <div x-data="{ isMobileOpen: false }" class="relative mt-4">

                <div class="flex items-center gap-2">

                    {{-- BURGER --}}
                    <button
                        @click="isMobileOpen = !isMobileOpen"
                        type="button"
                        class="w-10 h-10 flex items-center justify-center
                               rounded-md border border-gray-200
                               text-gray-500 hover:text-gray-700
                               focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    {{-- SEARCH INPUT --}}
                    <input
                        type="search"
                        placeholder="Pretraga..."
                        wire:model.debounce.300ms="search"
                        class="flex-1 h-10 px-3 text-sm
                               rounded-md border border-gray-200
                               bg-gray-100
                               focus:bg-white focus:outline-none
                               focus:border-orange-500 focus:ring-2 focus:ring-orange-500"
                    />

                    {{-- SEARCH BUTTON --}}
                    <button
                        class="w-10 h-10 flex items-center justify-center
                               bg-orange-500 text-white
                               rounded-md hover:bg-orange-600
                               focus:outline-none focus:ring-2 focus:ring-orange-500"
                    >
                        🔍
                    </button>
                </div>

                {{-- MOBILE MENU --}}
                <div
                    x-show="isMobileOpen"
                    x-transition
                    x-cloak
                    class="absolute left-0 right-0 mt-2 bg-white border rounded-md shadow-lg z-20"
                >
                    <ul class="divide-y">
                        @foreach ($restaurant->categories as $category)
                            <li class="px-4 py-3 text-sm hover:bg-zinc-50">
                                {{ $category->name }}
                            </li>
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="px-0.5 py-5">

        @foreach ($restaurant->categories as $category)

            @if ($category->items->count() === 0)
                @continue
            @endif

            {{-- CATEGORY TITLE --}}
            <h2 class="text-orange-500 font-semibold text-lg mb-1">
                {{ $category->name }}
            </h2>

            {{-- ORANGE LINE --}}

            <div class="h-px bg-orange-200 mb-4"></div>

            {{-- ITEMS --}}
            <div class="space-y-3 mb-8">

                @foreach ($category->items as $item)

                    <a
                        href="{{ route('item.show', [$restaurant, $item]) }}"
                        wire:navigate
                        class="block bg-white rounded-lg shadow-sm border flex items-center gap-3 px-3 py-6"
                    >


                        {{-- IMAGE --}}
                        <div class="w-14 h-14 rounded-full bg-zinc-200 overflow-hidden flex-shrink-0">
                            @if ($item->image)
                                <img
                                    src="{{ asset('storage/'.$item->image) }}"
                                    alt="{{ $item->name }}"
                                    class="w-full h-full object-cover"
                                />
                            @endif
                        </div>

                        {{-- TEXT --}}
                        <div class="flex-1">
                            <p class="font-medium text-zinc-900 text-base">
                                {{ $item->name }}
                            </p>

                            @if ($item->description)
                                <p class="text-sm text-zinc-500">
                                    {{ $item->description }}
                                </p>
                            @endif
                        </div>

                        {{-- PRICE --}}
                        <div class="text-sm font-semibold text-zinc-700 whitespace-nowrap">
                            {{ number_format($item->price, 2) }} KM
                        </div>

                    </a>

                @endforeach

            </div>

        @endforeach

    </div>
</div>
