<x-admin-layout>
    <div x-data="dashboardLive()" x-init="initDashboard()" class="flex flex-col gap-6 min-h-screen">
        
        {{-- ENCABEZADO --}}
        <div class="flex flex-col md:flex-row justify-between items-center bg-aromas-secondary p-6 rounded-xl border border-aromas-tertiary/30 shadow-lg">
            <div>
                <h1 class="text-3xl font-black text-white uppercase tracking-widest">Panel de Control</h1>
                <p class="text-gray-400 mt-1">Resumen operativo de la sucursal al día de hoy.</p>
            </div>
            <div class="flex gap-4 mt-4 md:mt-0">
                <a href="{{ route('tv.public') }}" target="_blank" class="bg-aromas-highlight hover:bg-yellow-500 text-gray-900 font-black px-6 py-3 rounded-lg shadow-md transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    TV Pública
                </a>
            </div>
        </div>

        {{-- 4 TARJETAS KPI EN VIVO --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 shadow-md">
                <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-1">Turnos Atendidos</p>
                <p class="text-4xl font-black text-green-400 transition-all duration-300" x-text="metrics.total_served"></p>
            </div>
            <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 shadow-md">
                <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-1">Promedio Espera</p>
                <p class="text-3xl font-black text-yellow-400 mt-1 transition-all duration-300" x-text="metrics.formatted_wait_time"></p>
            </div>
            <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 shadow-md">
                <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-1">Promedio Atención</p>
                <p class="text-3xl font-black text-blue-400 mt-1 transition-all duration-300" x-text="metrics.formatted_service_time"></p>
            </div>
            <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 shadow-md">
                <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-1">Abandonos</p>
                <p class="text-4xl font-black text-red-500 transition-all duration-300" x-text="metrics.total_abandoned"></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            {{-- MONITOR EN VIVO DE VENDEDORES --}}
            <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-700 p-6 shadow-md flex flex-col h-[500px]">
                <div class="flex justify-between items-center mb-4 border-b border-gray-800 pb-3">
                    <h3 class="text-lg font-black text-white uppercase tracking-widest">Monitor de Vendedores</h3>
                    <div class="text-[10px] font-bold text-green-400 bg-green-500/10 border border-green-500/20 px-2 py-1 rounded-full flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> EN VIVO
                    </div>
                </div>
                
                <div class="overflow-y-auto flex-1 custom-scrollbar pr-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <template x-for="seller in realTimeData" :key="seller.id">
                            <div class="bg-gray-800/50 border rounded-xl p-4 shadow-sm transition-all"
                                 :class="{ 'border-blue-500/50': seller.state === 'SERVING', 'border-green-500/30': seller.state === 'ONLINE', 'border-yellow-500/50': seller.state === 'BREAK', 'border-gray-700 opacity-50': seller.state === 'OFFLINE' }">
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="font-bold text-sm text-white truncate pr-2" x-text="seller.name"></h4>
                                    <span class="text-[9px] px-2 py-0.5 rounded font-black uppercase tracking-widest"
                                          :class="{ 'bg-blue-500/20 text-blue-400 border-blue-500/30': seller.state === 'SERVING', 'bg-green-500/20 text-green-400 border-green-500/30': seller.state === 'ONLINE', 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30': seller.state === 'BREAK', 'bg-gray-700 text-gray-400': seller.state === 'OFFLINE' }" 
                                          x-text="seller.state === 'SERVING' ? 'ATENDIENDO' : (seller.state === 'ONLINE' ? 'DISPONIBLE' : (seller.state === 'BREAK' ? 'EN PAUSA' : 'INACTIVO'))">
                                    </span>
                                </div>
                                <div class="space-y-2">
                                    <div x-show="seller.state === 'SERVING'">
                                        <p class="text-[10px] text-gray-500 uppercase font-bold">Cliente</p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <p class="text-sm font-bold text-blue-300 truncate" x-text="seller.client_name"></p>
                                            {{-- Banderas VIP / Discapacidad --}}
                                            <span x-show="seller.use_premium_alert" class="text-[8px] text-yellow-500 bg-yellow-500/20 border border-yellow-500/30 px-1.5 py-0.5 rounded font-black uppercase" x-text="seller.client_type_label || 'Premium'"></span>
                                            <span x-show="seller.has_disability" class="text-[8px] text-blue-400 bg-blue-500/20 border border-blue-500/30 px-1.5 py-0.5 rounded font-black uppercase">PREF</span>
                                        </div>
                                    </div>
                                    <div x-show="seller.state === 'BREAK'"><p class="text-[10px] text-gray-500 uppercase font-bold">Motivo</p><p class="text-sm font-bold text-yellow-400 truncate" x-text="seller.break_reason"></p></div>
                                    <div x-show="seller.state !== 'OFFLINE'" class="flex justify-between items-center mt-2 pt-2 border-t border-gray-700">
                                        <span class="text-[10px] text-gray-500 uppercase font-bold" x-text="seller.state === 'ONLINE' ? 'Disponible hace:' : 'Tiempo Transcurrido'"></span>
                                        <span class="text-lg font-mono font-black" :class="{'text-blue-400': seller.state === 'SERVING', 'text-green-400': seller.state === 'ONLINE', 'text-yellow-400': seller.state === 'BREAK'}" x-text="formatTimer(seller.state_started_at)"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="realTimeData.length === 0" class="col-span-full py-12 text-center text-gray-500">No hay vendedores activos.</div>
                    </div>
                </div>
            </div>

            {{-- COLA ACTUAL DE LA TIENDA EN VIVO --}}
            <div class="bg-gray-900 rounded-xl border border-gray-700 shadow-md flex flex-col h-[500px]">
                <div class="p-4 border-b border-gray-800 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Fila de la Tienda</h3>
                    <span class="bg-gray-800 text-gray-300 text-xs px-2 py-1 rounded font-bold transition-all"><span x-text="currentQueue.length"></span> Esperando</span>
                </div>
                <div class="overflow-y-auto flex-1 custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-800/50 sticky top-0 z-10">
                            <tr class="text-gray-400 text-[9px] uppercase tracking-wider">
                                <th class="py-2 px-4 font-bold">Turno</th>
                                <th class="py-2 px-2 font-bold">Cliente</th>
                                <th class="py-2 px-4 font-bold text-right">Llegada</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800">
                            <template x-for="client in currentQueue" :key="client.id">
                                <tr class="hover:bg-gray-800 transition-colors">
                                    <td class="py-3 px-4 font-mono font-bold text-white flex items-center gap-2">
                                        <span x-text="client.turn_number"></span>
                                        {{-- Banderas VIP / Discapacidad --}}
                                        <span x-show="client.use_premium_alert" class="text-[8px] text-yellow-500 bg-yellow-500/20 border border-yellow-500/30 px-1 rounded font-black uppercase" x-text="client.client_type_label || 'Premium'"></span>
                                        <span x-show="client.has_disability" class="text-[8px] text-blue-400 bg-blue-500/20 border border-blue-500/30 px-1 rounded font-black uppercase">PREF</span>
                                    </td>
                                    <td class="py-3 px-2 text-sm text-gray-300 truncate max-w-[100px]" x-text="client.client_name"></td>
                                    <td class="py-3 px-4 text-xs text-gray-500 font-mono text-right" x-text="client.queued_at"></td>
                                </tr>
                            </template>
                            <tr x-show="currentQueue.length === 0">
                                <td colspan="3" class="py-8 text-center text-sm text-gray-500">No hay clientes en espera.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPT NATIVO DE ALPINE PARA EL DASHBOARD TOTAL --}}
    <script>
        function dashboardLive() {
            return {
                realTimeData: @json($sellers ?? []), 
                currentQueue: @json($currentQueue ?? []),
                metrics: @json($metrics ?? []),
                pollingInterval: null,
                clockInterval: null,
                currentTime: Date.now(),

                initDashboard() {
                    this.startRealTime();
                },
                startRealTime() {
                    // Petición AJAX cada 5 segundos para refrescar TODA la pantalla sin recargar
                    this.pollingInterval = setInterval(() => { this.fetchRealTimeData(); }, 5000);
                    // Actualiza los cronómetros visuales cada segundo
                    this.clockInterval = setInterval(() => { this.currentTime = Date.now(); }, 1000);
                },
                fetchRealTimeData() {
                    fetch("{{ route('admin.dashboard.realtime') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                    .then(res => res.json())
                    .then(data => { 
                        this.realTimeData = data.sellers; 
                        this.currentQueue = data.queue;
                        this.metrics = data.metrics;
                    })
                    .catch(err => console.error("Error obteniendo monitor", err));
                },
                formatTimer(startedAt) {
                    if (!startedAt) return "00:00";
                    let elapsedSecs = Math.floor((this.currentTime - startedAt) / 1000);
                    if (elapsedSecs < 0) elapsedSecs = 0;
                    let hrs = Math.floor(elapsedSecs / 3600); let mins = Math.floor((elapsedSecs % 3600) / 60); let secs = Math.floor(elapsedSecs % 60);
                    let result = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    if (hrs > 0) result = `${hrs.toString().padStart(2, '0')}:` + result;
                    return result;
                }
            }
        }
    </script>
</x-admin-layout>