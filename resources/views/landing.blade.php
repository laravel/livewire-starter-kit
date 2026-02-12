<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IESD File Tracker - Landing</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Philippine-Statistics-Authority-PSA-logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-purple': '#6B46C1',
                        'primary-blue': '#3B82F6',
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-primary-purple via-purple-900 to-primary-blue">
    <!-- Background Shapes -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-30"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-300 rounded-full mix-blend-multiply filter blur-xl opacity-30"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-400 rounded-full mix-blend-multiply filter blur-xl opacity-20"></div>
    </div>

    <!-- Navigation -->
    <nav class="relative z-10 bg-white/10 backdrop-blur-md border-b border-white/20">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-8">
                    <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/Philippine-Statistics-Authority-PSA-logo.png') }}" alt="PSA Logo" class="h-8 w-auto">
                    <div class="text-white font-bold text-xl">IESD File Tracker</div>
                </div>
                    <div class="hidden md:flex space-x-6">
                        <a href="#" class="text-white/80 hover:text-white transition">Home</a>
                        <a href="#" class="text-white/80 hover:text-white transition">News</a>
                        <a href="#" class="text-white/80 hover:text-white transition">About</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Events</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Gallery</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Blog</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Services</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Contact</a>
                    </div>
                </div>
                @guest
                    <a href="{{ route('login') }}" class="bg-white text-purple-600 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">
                        Sign in
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="bg-white text-purple-600 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">
                        Dashboard
                    </a>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="relative z-10 container mx-auto px-6 py-12">
        <div class="flex flex-col lg:flex-row items-center justify-between">
            <div class="lg:w-1/2 mb-12 lg:mb-0">
                <h1 class="text-5xl lg:text-6xl font-bold text-white mb-6">
                    IESD File Tracker<br>
                    <span class="text-yellow-300">Management System</span>
                </h1>
                <p class="text-xl text-white/80 mb-8">
                    Welcome to IESD File Tracker - Your comprehensive solution for managing and tracking files with ease.
                </p>
                <div class="flex space-x-4">
                    @guest
                        <a href="{{ route('login') }}" class="bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                            Get Started
                        </a>
                        <a href="{{ route('register') }}" class="border-2 border-white text-white px-8 py-3 rounded-full font-semibold hover:bg-white hover:text-purple-600 transition">
                            Sign Up
                        </a>
                    @else
                        <a href="{{ route('filetracker.index') }}" class="bg-white text-purple-600 px-8 py-3 rounded-full font-semibold hover:bg-gray-100 transition">
                            Go to File Tracker
                        </a>
                    @endguest
                </div>
            </div>
            <div class="lg:w-1/2 flex justify-center">
                <div class="relative">
                    <div class="w-96 h-96 bg-white/10 backdrop-blur-sm rounded-3xl flex items-center justify-center">
                        <img src="{{ asset('images/Philippine-Statistics-Authority-PSA-logo.png') }}" alt="PSA Logo" class="w-48 h-auto opacity-80">
                    </div>
                    <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-yellow-400 rounded-full flex items-center justify-center">
                        <svg class="w-10 h-10 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.394 2.08a1 1 0 00-.788 0l-7 3a1 1 0 000 1.84L5.25 8.051a.999.999 0 01.356-.257l4-1.714a1 1 0 11.788 1.838L7.667 9.088l1.94.831a1 1 0 00.787 0l7-3a1 1 0 000-1.838l-7-3zM3.31 9.397L5 10.12v4.102a8.969 8.969 0 00-1.05-.174 1 1 0 01-.89-.89 11.115 11.115 0 01.25-3.762zM9.3 16.573A9.026 9.026 0 007 14.935v-3.957l1.818.78a3 3 0 002.364 0l5.508-2.361a11.026 11.026 0 01.25 3.762 1 1 0 01-.89.89 8.968 8.968 0 00-5.35 2.524 1 1 0 01-1.4 0zM6 18a1 1 0 001-1v-2.065a8.935 8.935 0 00-2-.712V17a1 1 0 001 1z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
