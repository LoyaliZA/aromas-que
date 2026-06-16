<x-gerencia-layout>
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white">Panel de Control General</h1>
        <p class="text-aromas-tertiary mt-1">Métricas de atención en piso, control de turnos y estado de resguardos.</p>
    </div>

    {{-- SECCIÓN 1: MÉTRICAS DE ATENCIÓN A CLIENTES --}}
    <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2 border-b border-aromas-tertiary/20 pb-2">
        <svg class="w-6 h-6 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
        Flujo de Clientes (Hoy)
    </h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
        <div class="bg-aromas-secondary rounded-xl shadow p-5 border border-aromas-tertiary/20 text-center relative overflow-hidden group hover:border-yellow-500/50 transition-colors">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-yellow-500/10 rounded-full blur-xl group-hover:bg-yellow-500/20 transition-all"></div>
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider relative z-10">En Espera</p>
            <p class="text-4xl font-black text-yellow-500 mt-2 relative z-10">{{ $queueMetrics['waiting'] }}</p>
        </div>
        <div class="bg-aromas-secondary rounded-xl shadow p-5 border border-aromas-tertiary/20 text-center relative overflow-hidden group hover:border-blue-500/50 transition-colors">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-500/10 rounded-full blur-xl group-hover:bg-blue-500/20 transition-all"></div>
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider relative z-10">Atendiendo</p>
            <p class="text-4xl font-black text-blue-400 mt-2 relative z-10">{{ $queueMetrics['serving'] }}</p>
        </div>
        <div class="bg-aromas-secondary rounded-xl shadow p-5 border border-aromas-tertiary/20 text-center relative overflow-hidden group hover:border-green-500/50 transition-colors">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-green-500/10 rounded-full blur-xl group-hover:bg-green-500/20 transition-all"></div>
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider relative z-10">Completados</p>
            <p class="text-4xl font-black text-green-400 mt-2 relative z-10">{{ $queueMetrics['completed'] }}</p>
        </div>
        <div class="bg-aromas-secondary rounded-xl shadow p-5 border border-aromas-tertiary/20 text-center relative overflow-hidden group hover:border-red-500/50 transition-colors">
            <div class="absolute -right-4 -top-4 w-16 h-16 bg-red-500/10 rounded-full blur-xl group-hover:bg-red-500/20 transition-all"></div>
            <p class="text-xs text-gray-400 uppercase font-bold tracking-wider relative z-10">Abandonos</p>
            <p class="text-4xl font-black text-red-500 mt-2 relative z-10">{{ $queueMetrics['abandoned'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        {{-- SECCIÓN 2: CONTROL DE PERSONAL (2 Columnas en pantallas grandes) --}}
        <div class="lg:col-span-2">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2 border-b border-aromas-tertiary/20 pb-2">
                <svg class="w-6 h-6 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884.6 2 2 2h4.667M16 16l-4-4-4 4"></path></svg>
                Gestión Rápida de Personal
            </h2>
            <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                            <th class="p-4">Colaborador</th>
                            <th class="p-4 text-center">Estado Actual</th>
                            <th class="p-4 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                        @forelse($sellers as $seller)
                            @php
                                $shift = $seller->todayShift;
                                $isOnline = $shift && $shift->current_status !== 'OFFLINE';
                                $statusText = $shift->current_status ?? 'OFFLINE';
                                
                                if ($statusText === 'ONLINE') $statusText = 'Activo en Piso';
                                if ($statusText === 'BREAK') $statusText = 'En Descanso';
                                if ($statusText === 'OFFLINE') $statusText = 'Fuera de Turno';
                            @endphp
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-white">{{ $seller->full_name }}</div>
                                    <span class="block text-xs text-gray-500 font-normal">{{ $seller->employee_code }}</span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isOnline ? ($shift->current_status === 'BREAK' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-green-500/20 text-green-400') : 'bg-gray-700 text-gray-400' }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    {{-- Validamos si el usuario logueado es ADMIN o tiene el permiso explícito --}}
                                    @if(auth()->user()->isAdmin() || auth()->user()->can_manage_shifts)
                                        <form action="{{ route('gerencia.staff.toggle') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="employee_id" value="{{ $seller->id }}">
                                            <button type="submit" 
                                                class="relative inline-flex items-center cursor-pointer transition-colors w-11 h-6 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-aromas-highlight {{ $isOnline ? 'bg-green-500' : 'bg-gray-600' }}">
                                                <span class="sr-only">Toggle</span>
                                                <span class="translate-x-1 inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $isOnline ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Vista para gerentes sin permisos (Ej: Don Lalo) --}}
                                        <span class="text-xs text-gray-500 italic bg-gray-800 px-2 py-1 rounded">Solo lectura</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-500">
                                    No hay empleados configurados para aparecer en la cola de ventas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- SECCIÓN 3: FILA DE ESPERA (1 Columna en pantallas grandes) --}}
        <div>
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2 border-b border-aromas-tertiary/20 pb-2">
                <svg class="w-6 h-6 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                Fila de Espera
            </h2>
            
            <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden flex flex-col h-[400px]"> 
                <div class="overflow-y-auto flex-1 custom-scrollbar p-3 space-y-3">
                    @forelse($waitingClients as $client)
                        <div class="bg-black/40 border border-aromas-tertiary/10 rounded-lg p-3 flex justify-between items-center hover:bg-black/60 transition-colors shadow-sm">
                            <div class="flex flex-col">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-mono font-black text-aromas-highlight text-lg">{{ $client->turn_number ?? 'S/N' }}</span>
                                    
                                    {{-- Etiquetas de Prioridad --}}
                                    @php $clientType = $client->catalogClientType; @endphp
                                    @if($clientType?->usesPremiumAlert())
                                        <span class="text-[9px] bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">{{ $clientType->displayLabel() }}</span>
                                    @elseif($client->has_disability)
                                        <span class="text-[9px] bg-blue-500/20 text-blue-400 border border-blue-500/30 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">PREF</span>
                                    @endif
                                </div>
                                <span class="text-sm font-bold text-gray-200 truncate max-w-[150px]">{{ $client->client_name }}</span>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="text-[10px] text-gray-500 uppercase font-bold tracking-wider">Llegada</span>
                                <span class="text-sm font-mono text-gray-300">{{ \Carbon\Carbon::parse($client->queued_at)->format('H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-gray-500 p-6 text-center">
                            <svg class="w-12 h-12 mb-3 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <p class="text-sm font-medium">No hay clientes en espera en este momento.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-gerencia-layout>