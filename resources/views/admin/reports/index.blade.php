<x-admin-layout>
    <div x-data="reportsDashboard()" x-init="initDashboard()" class="flex flex-col md:flex-row gap-6 min-h-[80vh]">

        {{-- MENÚ LATERAL --}}
        <div class="w-full md:w-64 flex-shrink-0">
            <div class="bg-aromas-secondary border border-aromas-tertiary/30 rounded-xl overflow-hidden shadow-lg sticky top-6">
                <div class="p-4 bg-aromas-main/50 border-b border-aromas-tertiary/30">
                    <h2 class="text-lg font-black text-white uppercase tracking-widest flex items-center gap-2">Módulo Analítico</h2>
                </div>
                <div class="p-2 space-y-1">
                    <button @click="switchTab('realtime')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'realtime' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        <span class="relative flex h-3 w-3"><span x-show="activeTab === 'realtime'" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-aromas-main opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3" :class="activeTab === 'realtime' ? 'bg-aromas-main' : 'bg-green-500'"></span></span> Monitor en Vivo
                    </button>
                    <div class="w-full h-px bg-aromas-tertiary/20 my-2"></div>
                    <button @click="switchTab('general')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'general' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        Reporte General
                    </button>
                    <button @click="switchTab('clients')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'clients' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        Clientes Atendidos
                    </button>
                    <button @click="switchTab('client_ratings')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'client_ratings' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        Opinión Clientes
                    </button>
                    <button @click="switchTab('seller_ratings')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'seller_ratings' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        Opinión Vendedores
                    </button>
                    <button @click="switchTab('abandoned')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'abandoned' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        Abandonos
                    </button>
                    <button @click="switchTab('incidents')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'incidents' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        Incidencias de Atención
                    </button>
                    <div class="w-full h-px bg-aromas-tertiary/20 my-2"></div>
                    <button @click="switchTab('performance')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'performance' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        Rendimiento Vendedor
                    </button>
                </div>
            </div>
        </div>

        {{-- CONTENIDO PRINCIPAL --}}
        <div class="flex-1 flex flex-col gap-6">

            {{-- BARRA DE FILTROS --}}
            <div x-show="activeTab !== 'realtime'" style="display: none;" class="bg-aromas-secondary p-4 rounded-xl border border-aromas-tertiary/30 shadow-md flex flex-col md:flex-row items-center justify-between gap-4" x-transition>
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-4 w-full">
                    <input type="hidden" name="tab" :value="activeTab">
                    @if($selectedEmployeeId) <input type="hidden" name="employee_id" value="{{ $selectedEmployeeId }}"> @endif

                    <div class="flex gap-2">
                        <button type="submit" name="period" value="today" class="px-4 py-2 text-sm font-bold rounded-lg transition-colors {{ $period === 'today' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white border border-gray-700' }}">Hoy</button>
                        <button type="submit" name="period" value="week" class="px-4 py-2 text-sm font-bold rounded-lg transition-colors {{ $period === 'week' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white border border-gray-700' }}">Semana</button>
                        <button type="submit" name="period" value="month" class="px-4 py-2 text-sm font-bold rounded-lg transition-colors {{ $period === 'month' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'bg-gray-800 text-gray-400 hover:bg-gray-700 hover:text-white border border-gray-700' }}">Mes</button>
                    </div>
                    <div class="hidden md:block w-px h-8 bg-aromas-tertiary/30 mx-2"></div>
                    <div class="flex items-center gap-3">
                        <input type="date" name="start_date" value="{{ $start_date }}" class="bg-gray-900 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm">
                        <span class="text-gray-500 font-bold">A</span>
                        <input type="date" name="end_date" value="{{ $end_date }}" class="bg-gray-900 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm">
                        <button type="submit" name="period" value="custom" class="bg-blue-600 text-white font-bold px-5 py-2 rounded-lg text-sm shadow-md hover:bg-blue-500">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="flex-1 bg-aromas-secondary/50 border border-aromas-tertiary/30 rounded-xl p-6 shadow-inner relative overflow-hidden">
                
                {{-- INCLUSIÓN DE PARCIALES --}}
                <div x-show="activeTab === 'realtime'" style="display: none;">
                    @include('admin.reports.partials.realtime')
                </div>

                <div x-show="activeTab === 'general'" style="display: none;">
                    @include('admin.reports.partials.general')
                </div>

                <div x-show="activeTab === 'clients'" style="display: none;">
                    @include('admin.reports.partials.clients')
                </div>

                <div x-show="activeTab === 'client_ratings'" style="display: none;">
                    @include('admin.reports.partials.client_ratings')
                </div>
                <div x-show="activeTab === 'seller_ratings'" style="display: none;">
                    @include('admin.reports.partials.seller_ratings')
                </div>

                <div x-show="activeTab === 'abandoned'" style="display: none;">
                    @include('admin.reports.partials.abandoned')
                </div>

                <div x-show="activeTab === 'incidents'" style="display: none;">
                    @include('admin.reports.partials.incidents')
                </div>

                <div x-show="activeTab === 'performance'" style="display: none;">
                    @include('admin.reports.partials.performance')
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT NATIVO DE GRÁFICAS Y ALPINE (Sin cambios) --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart !== 'undefined') {
                Chart.defaults.color = '#9ca3af';
                Chart.defaults.borderColor = 'rgba(75, 85, 99, 0.2)';
                Chart.defaults.font.family = "'Figtree', sans-serif";

                const ctxGlobal = document.getElementById('salesHourlyChart');
                if (ctxGlobal) {
                    const chartData = @json($chart_data);
                    new Chart(ctxGlobal, {
                        type: 'bar', // <--- CAMBIADO DE 'line' A 'bar'
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Turnos Atendidos',
                                data: chartData.data,
                                backgroundColor: 'rgba(59, 130, 246, 0.8)', // Color de la barra
                                borderRadius: 6, // Bordes redondeados
                                hoverBackgroundColor: '#FDC974' // Se pone dorado al pasar el mouse
                            }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
                    });
                }
            }
        });

        function reportsDashboard() {
            return {
                activeTab: '{{ $activeTab }}',
                isLoading: false,
                realTimeData: [],
                pollingInterval: null,
                clockInterval: null,
                currentTime: Date.now(),

                initDashboard() {
                    if (this.activeTab === 'realtime') this.startRealTime();
                },
                switchTab(tab) {
                    this.activeTab = tab;
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.history.replaceState(null, '', url.toString());
                    if (tab === 'realtime') this.startRealTime(); else this.stopRealTime();
                },
                startRealTime() {
                    this.fetchRealTimeData();
                    this.pollingInterval = setInterval(() => { this.fetchRealTimeData(); }, 5000);
                    this.clockInterval = setInterval(() => { this.currentTime = Date.now(); }, 1000);
                },
                stopRealTime() {
                    clearInterval(this.pollingInterval);
                    clearInterval(this.clockInterval);
                },
                fetchRealTimeData() {
                    fetch("{{ route('admin.reports.realtime') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(res => res.json())
                        .then(data => { this.realTimeData = data.sellers; })
                        .catch(err => console.error("Error", err));
                },
                formatTimer(startedAt) {
                    if (!startedAt) return "00:00";
                    let elapsedSecs = Math.floor((this.currentTime - startedAt) / 1000);
                    if (elapsedSecs < 0) elapsedSecs = 0;
                    let hrs = Math.floor(elapsedSecs / 3600);
                    let mins = Math.floor((elapsedSecs % 3600) / 60);
                    let secs = Math.floor(elapsedSecs % 60);
                    let result = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    if (hrs > 0) result = `${hrs.toString().padStart(2, '0')}:` + result;
                    return result;
                }
            }
        }
    </script>
</x-admin-layout>