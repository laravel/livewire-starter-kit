<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - IESD File Tracker</title>
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
                    <div class="text-white font-bold text-xl">YourLOGO</div>
                    <div class="hidden md:flex space-x-6">
                        <a href="{{ route('landing') }}" class="text-white/80 hover:text-white transition">Home</a>
                        <a href="#" class="text-white/80 hover:text-white transition">News</a>
                        <a href="#" class="text-white/80 hover:text-white transition">About</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Events</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Gallery</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Blog</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Services</a>
                        <a href="#" class="text-white/80 hover:text-white transition">Contact</a>
                    </div>
                </div>
                <a href="{{ route('register') }}" class="bg-white text-purple-600 px-6 py-2 rounded-full font-semibold hover:bg-gray-100 transition">
                    Sign Up
                </a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <main class="relative z-10 container mx-auto px-6 py-12">
        <div class="max-w-md mx-auto">
            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-8 border border-white/20">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2">Welcome Back</h2>
                    <p class="text-white/80">Sign in to access your file tracker</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 rounded-lg">
                        <p class="text-green-200 text-sm">{{ session('status') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="mb-6">
                        <label for="email" class="block text-white/80 text-sm font-medium mb-2">Email Address</label>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               class="w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent"
                               placeholder="Enter your email">
                        @error('email')
                            <p class="mt-2 text-red-300 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-6">
                        <label for="password" class="block text-white/80 text-sm font-medium mb-2">Password</label>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               class="w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent"
                               placeholder="Enter your password">
                        @error('password')
                            <p class="mt-2 text-red-300 text-sm">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="remember" 
                                   class="rounded border-white/30 bg-white/20 text-purple-600 shadow-sm focus:ring-purple-500 focus:ring-offset-0">
                            <span class="ml-2 text-white/80 text-sm">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-white/80 hover:text-white text-sm">
                                Forgot your password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-white text-purple-600 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-200">
                        Sign In
                    </button>
                </form>

                <!-- Sign Up Link -->
                <div class="text-center mt-6">
                    <p class="text-white/80">
                        Don't have an account? 
                        <a href="{{ route('register') }}" class="text-white font-semibold hover:underline">
                            Sign up
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
