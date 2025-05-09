<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-light">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <aside class="w-64 bg-primary text-light flex flex-col justify-between py-6 px-4">
                <div>
                    <a href="{{ route('dashboard') }}" class="flex flex-col items-center mb-8">
                        <img src="/images/logo.png" alt="Laguna Gateway Logo" class="h-16 w-auto mb-2">
                        <span class="text-lg font-bold text-white"></span>
                    </a>
                    <nav class="flex flex-col gap-2">
                        <a href="{{ route('dashboard') }}" class="py-2 px-4 rounded hover:bg-steel/30 {{ request()->routeIs('dashboard') ? 'bg-steel/50 text-crimson font-semibold' : '' }}">
                            Dashboard
                        </a>
                        <a href="{{ route('events.index') }}" class="py-2 px-4 rounded hover:bg-steel/30 {{ request()->routeIs('events.*') ? 'bg-steel/50 text-crimson font-semibold' : '' }}">
                            Events
                        </a>
                        <a href="{{ route('settings.profile') }}" class="py-2 px-4 rounded hover:bg-steel/30 {{ request()->routeIs('settings.profile') ? 'bg-steel/50 text-crimson font-semibold' : '' }}">
                            Profile
                        </a>
                    </nav>
                </div>
                <div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full py-2 px-4 rounded bg-accent text-white hover:bg-accent/80 font-semibold mt-4">Log Out</button>
                    </form>
                </div>
            </aside>
            <!-- Main Content -->
            <main class="flex-1 bg-light text-steel">
                {{ $slot }}
            </main>
        </div>
        <div class="hidden bg-navy bg-crimson bg-steel bg-light text-navy text-crimson text-steel text-light"></div>
    </body>
</html>
