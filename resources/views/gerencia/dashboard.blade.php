<x-gerencia-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Panel de Control</h1>
        <p class="text-aromas-tertiary mt-1">Métricas y estado actual de la sucursal.</p>
    </div>

    {{-- WIDGETS DE ESTADO (KPIs) --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        
        {{-- Widget 1: Pendientes --}}
        <a href="{{ route('gerencia.daily') }}" class="block transform hover:scale-105 transition-transform">
            <div class="bg-aromas-secondary rounded-xl shadow-lg p-6 border border-aromas-tertiary/20 group cursor-pointer hover:border-yellow-500/50">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">En Custodia (Pendientes)</p>
                        <p class="text-4xl font-bold text-white mt-2 group-hover:text-yellow-400 transition-colors">{{ $pendingCount }}</p>
                    </div>
                    <div class="p-3 bg-yellow-900/20 rounded-lg text-yellow-400 border border-yellow-500/20">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500 flex items-center">
                    <span class="text-yellow-500 mr-1">●</span> Requieren atención o entrega
                </div>
            </div>
        </a>

        {{-- Widget 2: Entregados Hoy --}}
        <div class="bg-aromas-secondary rounded-xl shadow-lg p-6 border border-aromas-tertiary/20 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Entregados Hoy</p>
                    <p class="text-4xl font-bold text-white mt-2 group-hover:text-aromas-success transition-colors">{{ $deliveredTodayCount }}</p>
                </div>
                <div class="p-3 bg-green-900/20 rounded-lg text-aromas-success border border-green-500/20">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        {{-- Widget 3: Flujo Total --}}
        <div class="bg-aromas-secondary rounded-xl shadow-lg p-6 border border-aromas-tertiary/20 group">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400 uppercase tracking-wider">Resguardos del día</p>
                    <p class="text-4xl font-bold text-white mt-2 group-hover:text-aromas-info transition-colors">{{ $totalTodayCount }}</p>
                </div>
                <div class="p-3 bg-blue-900/20 rounded-lg text-aromas-info border border-blue-500/20">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
        </div>
    </div>
</x-gerencia-layout>