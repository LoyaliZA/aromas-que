<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Logística Bellaroma - T.E.R.A.</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-aromas-main text-gray-200 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <header class="bg-aromas-secondary shadow-md lg:hidden flex items-center justify-between px-4 fixed w-full h-16 z-50 top-0 border-b border-aromas-tertiary/30">
        <a href="{{ route('bellaroma.dashboard') }}" class="block hover:opacity-80 transition-opacity">
            <img src="{{ asset('images/aromas_logo_blanco.png') }}" alt="Aromas Logo" class="h-10 w-auto object-contain">
        </a>
        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-300 focus:outline-none p-2 rounded-md hover:bg-aromas-tertiary/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </header>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-40 w-64 bg-aromas-secondary shadow-xl transition-transform duration-300 ease-in-out lg:translate-x-0 border-r border-aromas-tertiary/30 flex flex-col pt-16 lg:pt-0">
        
        <div class="hidden lg:flex items-center justify-center h-20 border-b border-aromas-tertiary/30">
            <img src="{{ asset('images/br_logo_lanco_transparente.png') }}" alt="Bellaroma Logo" class="h-12 w-auto object-contain">
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <p class="px-2 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Menú Operativo</p>
            
            <a href="{{ route('bellaroma.dashboard') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('bellaroma.dashboard') ? 'bg-aromas-tertiary text-white' : 'text-gray-300 hover:bg-aromas-tertiary/50 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Dashboard
            </a>

            </nav>

        <div class="p-4 border-t border-aromas-tertiary/30">
            <div class="flex items-center mb-4 px-2">
                <div class="flex-shrink-0 bg-aromas-tertiary p-2 rounded-full">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400">Bellaroma</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-4 py-2 text-sm font-medium text-red-400 rounded-lg hover:bg-red-500/10 transition-colors">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/50 lg:hidden" style="display: none;"></div>

    <div class="flex-1 flex flex-col lg:ml-64 min-h-screen transition-all duration-300">
        <div class="h-16 lg:hidden shrink-0"></div>

        <main class="flex-1 p-4 lg:p-8 overflow-x-hidden">
            @if(session('success'))
            <div class="mb-6 bg-aromas-success/20 border-l-4 border-aromas-success text-white p-4 rounded shadow-lg backdrop-blur-sm">
                <p class="font-bold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('success') }}
                </p>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>