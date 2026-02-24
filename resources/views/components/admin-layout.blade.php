<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Panel de Administración - Aromas</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-aromas-main text-gray-200 font-sans antialiased" x-data="{ sidebarOpen: false }">

    <header class="bg-aromas-secondary shadow-md lg:hidden flex items-center justify-between px-4 fixed w-full h-16 z-50 top-0 border-b border-aromas-tertiary/30">
        <a href="{{ route('admin.dashboard') }}" class="block hover:opacity-80 transition-opacity">
            <img src="{{ asset('images/logo_blanco.png') }}" alt="Aromas Logo" class="h-10 w-auto object-contain">
        </a>

        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-300 focus:outline-none p-2 rounded-md hover:bg-aromas-tertiary/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </header>

    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-40 w-64 bg-aromas-secondary shadow-xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 border-r border-aromas-tertiary/20 pt-16 lg:pt-0">

        <div class="hidden lg:flex items-center justify-center h-24 bg-black/20 shadow-sm border-b border-aromas-tertiary/10">
            <a href="{{ route('admin.dashboard') }}" class="block p-2 hover:opacity-80 transition-opacity">
                <img src="{{ asset('images/logo_blanco.png') }}" alt="Aromas Logo" class="h-20 w-auto object-contain">
            </a>
        </div>

        <nav class="mt-5 px-4 space-y-2">
            <p class="px-4 text-xs font-semibold text-aromas-tertiary uppercase tracking-wider mb-2">Principal</p>
            
            <a href="{{ route('admin.dashboard') }}"
                class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200
               {{ request()->routeIs('admin.dashboard') 
                  ? 'bg-aromas-main/50 text-aromas-highlight border-l-4 border-aromas-highlight' 
                  : 'text-gray-300 hover:bg-aromas-tertiary/20 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                Dashboard
            </a>

            <p class="px-4 mt-6 text-xs font-semibold text-aromas-tertiary uppercase tracking-wider mb-2">Gestión</p>

            <a href="{{ route('admin.users.index') }}"
                class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200
               {{ request()->routeIs('admin.users.*') 
                  ? 'bg-aromas-main/50 text-aromas-highlight border-l-4 border-aromas-highlight' 
                  : 'text-gray-300 hover:bg-aromas-tertiary/20 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                Personal
            </a>

            <a href="{{ route('admin.tv_ads.index') }}"
                class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200
               {{ request()->routeIs('admin.tv_ads.*') 
                  ? 'bg-aromas-main/50 text-aromas-highlight border-l-4 border-aromas-highlight' 
                  : 'text-gray-300 hover:bg-aromas-tertiary/20 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Publicidad (TV)
            </a>

            <a href="{{ route('admin.reports.index') }}"
                class="flex items-center px-4 py-3 rounded-lg transition-colors duration-200
               {{ request()->routeIs('admin.reports.*') 
                  ? 'bg-aromas-main/50 text-aromas-highlight border-l-4 border-aromas-highlight' 
                  : 'text-gray-300 hover:bg-aromas-tertiary/20 hover:text-white' }}">
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Reportes
            </a>
        </nav>

        <div class="absolute bottom-0 w-full p-4 border-t border-aromas-tertiary/20 bg-black/10">
            <div class="flex items-center mb-4 px-2">
                <div class="w-8 h-8 rounded-full bg-aromas-highlight flex items-center justify-center text-aromas-main font-bold text-sm shadow-md ring-2 ring-aromas-main">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="ml-3 overflow-hidden">
                    <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">Administrador</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center w-full text-gray-400 hover:text-aromas-error transition-colors px-2 py-1 rounded-md hover:bg-white/5">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 01-3-3h4a3 3 0 013 3v1"></path></svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </aside>

    <div class="flex-1 flex flex-col lg:ml-64 min-h-screen transition-all duration-300">
        <div class="h-16 lg:hidden shrink-0"></div>
        
        <main class="flex-1 p-4 lg:p-6 overflow-x-hidden">
            @if(session('success'))
            <div class="mb-4 bg-aromas-success/20 border-l-4 border-aromas-success text-white p-4 rounded shadow-lg backdrop-blur-sm">
                <p class="font-bold flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    ¡Éxito!
                </p>
                <p class="text-sm opacity-90">{{ session('success') }}</p>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 z-30 bg-black/80 backdrop-blur-sm lg:hidden"></div>

    
</body>
</html>