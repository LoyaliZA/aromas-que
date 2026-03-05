<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso Denegado - Aromas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white font-sans antialiased min-h-screen flex items-center justify-center p-4 overflow-hidden relative">
    
    {{-- Efecto de fondo rojo/alerta --}}
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-red-600/20 rounded-full blur-[100px] pointer-events-none"></div>

    <div class="max-w-lg w-full text-center relative z-10 p-8 bg-gray-800/50 backdrop-blur-xl rounded-3xl border border-gray-700 shadow-2xl">
        <div class="flex justify-center mb-6">
            <div class="p-6 bg-red-500/10 rounded-full border-2 border-red-500/30 text-red-500 shadow-[0_0_30px_rgba(239,68,68,0.2)]">
                <svg class="w-20 h-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
        </div>
        
        <h1 class="text-6xl font-black text-white mb-2 tracking-tighter">403</h1>
        <h2 class="text-2xl font-bold text-red-400 uppercase tracking-widest mb-4">Acceso Restringido</h2>
        
        <p class="text-gray-400 mb-8 text-lg">
            {{ $exception->getMessage() ?: 'No tienes los permisos necesarios para explorar esta zona. Tu gafete no funciona aquí.' }}
        </p>
        
        <a href="javascript:history.back()" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gray-700 hover:bg-white text-white hover:text-gray-900 font-bold rounded-xl transition-all duration-300 transform hover:-translate-y-1 shadow-lg">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Regresar a la página anterior
        </a>
    </div>
</body>
</html>