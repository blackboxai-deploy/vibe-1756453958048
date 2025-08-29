<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Review SAAS'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <!-- Tailwind CSS -->
    @vite('resources/css/app.css')
    
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div id="app">
        <!-- Navigation -->
        @if(!request()->routeIs('review.*'))
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <a href="{{ route('home') }}" class="flex items-center">
                            @if($logo = \App\Models\Setting::get('app_logo'))
                                <img src="{{ Storage::url($logo) }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                            @else
                                <img src="https://placehold.co/120x40?text={{ urlencode(config('app.name')) }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                            @endif
                        </a>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        @auth
                            <div class="relative dropdown">
                                <button class="flex items-center space-x-2 text-gray-700 hover:text-gray-900">
                                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="h-8 w-8 rounded-full">
                                    <span>{{ auth()->user()->name }}</span>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </button>
                                <div class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden dropdown-menu">
                                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                    </a>
                                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-user mr-2"></i> Profile
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <form method="POST" action="{{ route('logout') }}" class="block">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-gray-900">Login</a>
                            <a href="{{ route('register') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md transition-colors">Sign Up</a>
                        @endauth
                    </div>
                </div>
            </div>
        </nav>
        @endif

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 mx-4 mt-4" role="alert">
            <div class="flex">
                <div class="py-1"><i class="fas fa-check-circle mr-2"></i></div>
                <div>{{ session('success') }}</div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 mx-4 mt-4" role="alert">
            <div class="flex">
                <div class="py-1"><i class="fas fa-exclamation-circle mr-2"></i></div>
                <div>{{ session('error') }}</div>
            </div>
        </div>
        @endif

        @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4 mx-4 mt-4" role="alert">
            <div class="flex">
                <div class="py-1"><i class="fas fa-exclamation-triangle mr-2"></i></div>
                <div>{{ session('warning') }}</div>
            </div>
        </div>
        @endif

        <!-- Main Content -->
        <main>
            @yield('content')
        </main>

        <!-- Footer -->
        @if(!request()->routeIs('review.*'))
        <footer class="bg-white border-t mt-12">
            <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="col-span-1 md:col-span-2">
                        <a href="{{ route('home') }}" class="flex items-center mb-4">
                            @if($logo = \App\Models\Setting::get('app_logo'))
                                <img src="{{ Storage::url($logo) }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                            @else
                                <img src="https://placehold.co/120x40?text={{ urlencode(config('app.name')) }}" alt="{{ config('app.name') }}" class="h-8 w-auto">
                            @endif
                        </a>
                        <p class="text-gray-600 text-sm">
                            Streamline your online reputation management with our comprehensive review collection and analytics platform.
                        </p>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">Platform</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Features</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Pricing</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Support</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">Legal</h3>
                        <ul class="space-y-2">
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Privacy Policy</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Terms of Service</a></li>
                            <li><a href="#" class="text-gray-600 hover:text-gray-900 text-sm">Contact</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-8 pt-8 border-t border-gray-200">
                    <p class="text-center text-gray-600 text-sm">
                        © {{ date('Y') }} {{ \App\Models\Setting::get('company_name', config('app.name')) }}. All rights reserved.
                    </p>
                </div>
            </div>
        </footer>
        @endif
    </div>

    <!-- Scripts -->
    @vite('resources/js/app.js')
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Clipboard.js -->
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2/dist/clipboard.min.js"></script>

    <script>
        // Dropdown functionality
        document.addEventListener('DOMContentLoaded', function() {
            const dropdowns = document.querySelectorAll('.dropdown');
            
            dropdowns.forEach(dropdown => {
                const button = dropdown.querySelector('button');
                const menu = dropdown.querySelector('.dropdown-menu');
                
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    menu.classList.toggle('hidden');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!dropdown.contains(e.target)) {
                        menu.classList.add('hidden');
                    }
                });
            });
        });

        // Flash message auto-hide
        setTimeout(() => {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>

    @stack('scripts')
</body>
</html>