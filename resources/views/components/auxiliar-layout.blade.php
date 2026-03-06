<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Panel Auxiliar - T.E.R.A.</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-aromas-main text-gray-200 font-sans antialiased" x-data="{ sidebarOpen: false }">

    {{-- HEADER MÓVIL --}}
    <header class="bg-aromas-secondary shadow-md lg:hidden flex items-center justify-between px-4 fixed w-full h-16 z-50 top-0 border-b border-aromas-tertiary/30">
        <a href="{{ route('auxiliar.dashboard') }}" class="block">
            <img src="{{ asset('images/aromas_logo_blanco.png') }}" alt="Aromas Logo" class="h-8 w-auto">
        </a>

        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-300 p-2 rounded-md hover:bg-aromas-tertiary/20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </header>

    <div class="flex h-screen overflow-hidden pt-16 lg:pt-0">
        {{-- SIDEBAR --}}
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-50 w-64 bg-aromas-secondary border-r border-aromas-tertiary/20 transform transition-transform duration-300 ease-in-out lg:relative lg:translate-x-0 overflow-y-auto shadow-2xl">
            
            <div class="p-6 flex flex-col h-full">
                <div class="mb-10 hidden lg:block text-center">
                    <img src="{{ asset('images/aromas_logo_blanco.png') }}" alt="Aromas" class="h-12 mx-auto">
                    <p class="text-[10px] text-aromas-highlight font-black mt-2 tracking-[0.2em] uppercase italic">Módulo Auxiliar</p>
                </div>

                <nav class="flex-grow space-y-2">
                    {{-- GESTIÓN DE ANUNCIOS (Siempre visible para este rol) --}}
                    <a href="{{ route('auxiliar.dashboard') }}" 
                       class="flex items-center px-4 py-3 rounded-xl transition-all {{ request()->routeIs('auxiliar.dashboard') ? 'bg-aromas-highlight text-aromas-main font-bold shadow-lg shadow-aromas-highlight/20' : 'text-gray-400 hover:bg-aromas-tertiary/10 hover:text-white' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a2 2 0 002-2V6a2 2 0 00-2-2H4a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Anuncios TV
                    </a>

                </nav>

                {{-- USUARIO Y LOGOUT --}}
                <div class="pt-6 border-t border-aromas-tertiary/20">
                    <div class="px-4 mb-4">
                        <p class="text-xs text-aromas-tertiary font-bold uppercase tracking-widest">Usuario</p>
                        <p class="text-white font-bold truncate">{{ Auth::user()->name }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-3 text-aromas-error hover:bg-aromas-error/10 rounded-xl transition-all font-bold">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            Cerrar Sesión
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="flex-1 overflow-y-auto p-4 lg:p-8 relative">
            
            {{-- ALERTAS --}}
            @if(session('success'))
                <div class="max-w-4xl mx-auto mb-6 bg-aromas-success/20 border-l-4 border-aromas-success text-white p-4 rounded shadow-lg backdrop-blur-sm flex items-start animate-fade-in-down">
                    <div class="p-1 text-aromas-success mr-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div>
                        <p class="font-bold">¡Éxito!</p>
                        <p class="text-sm opacity-90">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <div class="max-w-7xl mx-auto">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>