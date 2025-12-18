@props(['signature'])

<div class="flex items-start space-x-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
    <div class="flex-shrink-0">
        <img src="{{ $signature->signature_url }}" alt="Firma de {{ $signature->user->name }}" 
            class="h-20 w-32 object-contain border border-gray-300 dark:border-gray-600 rounded bg-white">
    </div>
    <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-900 dark:text-white">
            {{ $signature->user->name }}
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Firmado el {{ $signature->signed_at->locale('es')->isoFormat('D [de] MMMM [de] YYYY [a las] HH:mm') }}
        </p>
        @if($signature->ip_address)
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                IP: {{ $signature->ip_address }}
            </p>
        @endif
    </div>
    <div class="flex-shrink-0">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Firmado
        </span>
    </div>
</div>
