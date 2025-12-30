<div class="max-w-md mx-auto min-h-screen relative pb-32">

    {{-- HEADER --}}
    <div class="bg-white sticky top-0 z-10 border-b">
        <div class="px-4 py-3">
            <div class="flex">
                <img
                    src="{{ asset('images/charlie-logo.jpg') }}"
                    alt="Charlie"
                    class="h-16 object-contain"
                />
            </div>
        </div>
    </div>

    {{-- CONTENT --}}
    <div class="px-4 py-6">


        {{-- IMAGE --}}
        <div class="flex justify-center mb-6">
            <div class="w-40 h-40 full bg-zinc-200 overflow-hidden shadow
                flex items-center justify-center">

                @if($item->image)
                    <img
                        src="{{ asset('storage/' . $item->image) }}"
                        alt="{{ $item->name }}"
                        class="w-full h-full object-cover"
                    >
                @else
                    {{-- PLACEHOLDER --}}
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="w-16 h-16 text-zinc-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 5h18M3 19h18M5 7v10M19 7v10"/>
                    </svg>
                @endif

            </div>
        </div>


        {{-- NAME --}}
        <h1 class="text-xl font-semibold text-zinc-900 text-center">
            {{ $item->name }}
        </h1>

        {{-- PRICE --}}
        <p class="text-orange-500 text-lg font-bold text-center mt-2">
            {{ number_format($item->price, 2) }} KM
        </p>

        {{-- DESCRIPTION --}}
        @if($item->description)
            <p class="text-zinc-600 text-sm text-center mt-4">
                {{ $item->description }}
            </p>
        @endif
    </div>


    <a href="{{ route('menu.show', $restaurant->slug) }}"
       class="absolute bottom-6 left-1/2 -translate-x-1/2
          w-14 h-14 rounded-full
          bg-orange-500 text-white
          flex items-center justify-center
          text-2xl shadow-lg
          hover:bg-orange-600 transition">
        ✕
    </a>


</div>
