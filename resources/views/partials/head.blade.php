<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/flexcon.png" type="image/png">
<link rel="apple-touch-icon" href="/flexcon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- Tom Select for searchable selects --}}
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.4.1/dist/js/tom-select.complete.min.js"></script>
<style>
    /* Tom Select Dark Mode Styles */
    .dark .ts-wrapper .ts-control {
        background-color: rgb(31 41 55); /* gray-800 */
        border-color: rgb(75 85 99); /* gray-600 */
        color: white;
    }
    .dark .ts-wrapper .ts-control input {
        color: white;
    }
    .dark .ts-wrapper .ts-control input::placeholder {
        color: rgb(156 163 175); /* gray-400 */
    }
    .dark .ts-dropdown {
        background-color: rgb(31 41 55); /* gray-800 */
        border-color: rgb(75 85 99); /* gray-600 */
        color: white;
    }
    .dark .ts-dropdown .option {
        color: white;
    }
    .dark .ts-dropdown .option:hover,
    .dark .ts-dropdown .option.active {
        background-color: rgb(55 65 81); /* gray-700 */
        color: white;
    }
    .dark .ts-dropdown .option.selected {
        background-color: rgb(37 99 235); /* blue-600 */
        color: white;
    }
    .dark .ts-wrapper.focus .ts-control {
        border-color: rgb(59 130 246); /* blue-500 */
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
    }
    .dark .ts-dropdown .no-results {
        color: rgb(156 163 175); /* gray-400 */
    }
    /* Light mode focus ring */
    .ts-wrapper.focus .ts-control {
        border-color: rgb(59 130 246); /* blue-500 */
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
    }
</style>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
