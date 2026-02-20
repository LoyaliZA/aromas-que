<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Recepción - Aromas</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>
<body class="font-sans antialiased bg-aromas-main text-gray-200 min-h-screen flex flex-col selection:bg-aromas-highlight selection:text-aromas-main">

    {{-- NAVBAR TOUCH --}}
    <nav class="bg-aromas-secondary border-b border-aromas-tertiary/30 px-4 py-3 shadow-lg shrink-0 sticky top-0 z-40">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            
            {{-- Identidad --}}
            <div class="flex items-center gap-4">
                <div class="h-12 w-auto">
                    {{-- Logo Blanco del Proyecto --}}
                    <img src="{{ asset('images/logo_blanco.png') }}" alt="Aromas Logo" class="h-full w-auto object-contain drop-shadow-md">
                </div>
                <div class="hidden md:block pl-4 border-l border-aromas-tertiary/20">
                    <h1 class="text-lg font-bold text-white leading-tight tracking-wide">Recepción</h1>
                    <p class="text-xs text-aromas-tertiary uppercase tracking-widest">Módulo de Checador</p>
                </div>
            </div>

            {{-- Usuario y Salir --}}
            <div class="flex items-center gap-4">
                <div class="text-right hidden sm:block">
                    <div class="text-sm font-bold text-white">{{ Auth::user()->name }}</div>
                    <div class="text-xs text-aromas-success flex justify-end items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-aromas-success animate-pulse"></span> Online
                    </div>
                </div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="group flex items-center justify-center w-12 h-12 rounded-xl bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-600 hover:text-white transition-all active:scale-90 shadow-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 md:p-6 relative">
        <div class="max-w-7xl mx-auto h-full flex flex-col">
            {{ $slot }}
        </div>
    </main>

</body>
</html>