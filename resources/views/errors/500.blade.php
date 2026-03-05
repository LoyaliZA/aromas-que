<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del Servidor - Aromas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white font-sans antialiased min-h-screen flex items-center justify-center p-4 overflow-hidden relative">
    
    {{-- Efecto de fondo --}}
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-orange-600/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-lg w-full text-center relative z-10 p-8 bg-gray-800/50 backdrop-blur-xl rounded-3xl border border-gray-700 shadow-2xl">
        <div class="flex justify-center mb-6">
            <div class="p-6 bg-orange-500/10 rounded-full border-2 border-orange-500/30 text-orange-400 shadow-[0_0_30px_rgba(249,115,22,0.2)]">
                <svg class="w-20 h-20 animate-[spin_4s_linear_infinite]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            </div>
        </div>
        
        <h1 class="text-6xl font-black text-white mb-2 tracking-tighter">500</h1>
        <h2 class="text-2xl font-bold text-orange-400 uppercase tracking-widest mb-4">Corto Circuito Interno</h2>
        
        <p class="text-gray-400 mb-8 text-lg">
            ¡Ups! Nuestros engranes se atascaron. El servidor encontró un error inesperado, pero ya estamos mandando a los técnicos a revisarlo.
        </p>
        
        <a href="javascript:history.back()" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gray-700 hover:bg-white text-white hover:text-gray-900 font-bold rounded-xl transition-all duration-300 transform hover:-translate-y-1 shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Regresar a la página anterior
        </a>
    </div>
</body>
</html>