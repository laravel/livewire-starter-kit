<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<!-- Used to add dark mode right away, adding here prevents any flicker... -->
{{--
    <script>
    if (typeof(Storage) !== "undefined") {
    if(localStorage.getItem('dark_mode') && localStorage.getItem('dark_mode') == 'true'){
    document.documentElement.classList.add('dark');
    }
    }
    </script>
--}}

<title>{{ $title ?? 'Laravel' }}</title>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxStyles
