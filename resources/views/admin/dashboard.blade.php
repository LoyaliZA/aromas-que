<x-admin-layout>
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Panel de Control</h1>
            <p class="text-aromas-tertiary mt-1">Bienvenido de nuevo, {{ Auth::user()->name }}</p>
        </div>
        <span class="px-3 py-1 bg-aromas-info/20 text-aromas-info rounded-full text-xs font-semibold border border-aromas-info/30">
            v1.0 Beta
        </span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <div class="bg-aromas-secondary rounded-xl shadow-lg p-6 border border-aromas-tertiary/20 hover:border-aromas-highlight/50 transition-colors group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Personal Activo</p>
                    <p class="text-3xl font-bold text-white mt-1 group-hover:text-aromas-highlight transition-colors">--</p>
                </div>
                <div class="p-3 bg-aromas-main rounded-lg text-aromas-highlight">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
            </div>
        </div>

        <div class="bg-aromas-secondary rounded-xl shadow-lg p-6 border border-aromas-tertiary/20 hover:border-aromas-success/50 transition-colors group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Turnos Hoy</p>
                    <p class="text-3xl font-bold text-white mt-1 group-hover:text-aromas-success transition-colors">--</p>
                </div>
                <div class="p-3 bg-aromas-main rounded-lg text-aromas-success">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

    </div>

    <div class="bg-aromas-secondary rounded-xl shadow-lg p-8 border border-aromas-tertiary/20">
        <h3 class="text-lg font-semibold text-white mb-6 flex items-center">
            <span class="w-1 h-6 bg-aromas-highlight rounded mr-3"></span>
            Acciones Rápidas
        </h3>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-6 py-3 bg-aromas-highlight text-aromas-main font-bold rounded-lg hover:bg-white transition-all transform hover:scale-105 shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                Nuevo Empleado
            </a>
            
            <button class="inline-flex items-center px-6 py-3 bg-aromas-main border border-aromas-tertiary text-gray-300 font-medium rounded-lg hover:text-white hover:border-gray-400 transition-all">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Ver Reportes
            </button>
        </div>
    </div>

</x-admin-layout>