<x-admin-layout>
    <div x-data="{ activeTab: '{{ $activeTab }}' }" 
         x-init="$watch('activeTab', value => {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', value);
            window.history.replaceState(null, '', url.toString());
         })">

        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
            <h2 class="text-2xl font-bold text-white">Centro de Análisis</h2>
            
            <div class="flex flex-wrap gap-2 bg-aromas-secondary p-1 rounded-lg border border-aromas-tertiary/30 shadow-sm">
                <button @click="activeTab = 'dashboard'" :class="activeTab === 'dashboard' ? 'bg-aromas-highlight text-aromas-main shadow' : 'text-gray-300 hover:bg-aromas-tertiary/30 hover:text-white'" class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none">
                    Dashboard Global
                </button>
                <button @click="activeTab = 'performance'" :class="activeTab === 'performance' ? 'bg-aromas-highlight text-aromas-main shadow' : 'text-gray-300 hover:bg-aromas-tertiary/30 hover:text-white'" class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none">
                    Rendimiento Personal
                </button>
            </div>
        </div>

        <div class="mb-6 bg-aromas-secondary p-3 rounded-lg border border-aromas-tertiary/30 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-3 w-full">
                <input type="hidden" name="tab" :value="activeTab">
                @if($selectedEmployeeId)
                    <input type="hidden" name="employee_id" value="{{ $selectedEmployeeId }}">
                @endif

                <div class="flex gap-1">
                    <button type="submit" name="period" value="today" class="px-3 py-1.5 text-sm rounded transition-colors {{ $period === 'today' ? 'bg-aromas-highlight text-aromas-main font-bold shadow' : 'text-gray-300 hover:bg-aromas-tertiary/50 hover:text-white' }}">Hoy</button>
                    <button type="submit" name="period" value="week" class="px-3 py-1.5 text-sm rounded transition-colors {{ $period === 'week' ? 'bg-aromas-highlight text-aromas-main font-bold shadow' : 'text-gray-300 hover:bg-aromas-tertiary/50 hover:text-white' }}">Esta Semana</button>
                    <button type="submit" name="period" value="month" class="px-3 py-1.5 text-sm rounded transition-colors {{ $period === 'month' ? 'bg-aromas-highlight text-aromas-main font-bold shadow' : 'text-gray-300 hover:bg-aromas-tertiary/50 hover:text-white' }}">Este Mes</button>
                </div>

                <div class="hidden md:block w-px h-6 bg-aromas-tertiary/50 mx-1"></div>

                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $start_date }}" class="bg-aromas-main text-white border border-aromas-tertiary/50 rounded px-2 py-1 text-sm focus:ring-aromas-highlight focus:border-aromas-highlight">
                    <span class="text-gray-400">-</span>
                    <input type="date" name="end_date" value="{{ $end_date }}" class="bg-aromas-main text-white border border-aromas-tertiary/50 rounded px-2 py-1 text-sm focus:ring-aromas-highlight focus:border-aromas-highlight">
                    <button type="submit" name="period" value="custom" class="bg-aromas-info text-white px-3 py-1.5 rounded text-sm hover:bg-opacity-80 transition shadow">Filtrar Rango</button>
                </div>
            </form>
        </div>

        <div x-show="activeTab === 'dashboard'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 shadow-sm flex flex-col items-center justify-center">
                    <p class="text-[11px] text-aromas-tertiary uppercase font-bold tracking-wider mb-1 text-center">Turnos Atendidos</p>
                    <p class="text-3xl font-bold text-aromas-success">{{ $metrics['total_served'] }}</p>
                </div>
                <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 shadow-sm flex flex-col items-center justify-center">
                    <p class="text-[11px] text-aromas-tertiary uppercase font-bold tracking-wider mb-1 text-center">Promedio Espera</p>
                    <p class="text-3xl font-bold text-aromas-warning">{{ $metrics['formatted_wait_time'] }}</p>
                </div>
                <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 shadow-sm flex flex-col items-center justify-center">
                    <p class="text-[11px] text-aromas-tertiary uppercase font-bold tracking-wider mb-1 text-center">Promedio Atención</p>
                    <p class="text-3xl font-bold text-aromas-info">{{ $metrics['formatted_service_time'] }}</p>
                </div>
                <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 shadow-sm flex flex-col items-center justify-center">
                    <p class="text-[11px] text-aromas-tertiary uppercase font-bold tracking-wider mb-1 text-center">Turnos Abandonados</p>
                    <p class="text-3xl font-bold text-aromas-error">{{ $metrics['total_abandoned'] }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 bg-aromas-secondary rounded-lg p-5 border border-aromas-tertiary/30 shadow-sm">
                    <div class="flex justify-between items-center mb-4 border-b border-aromas-tertiary/30 pb-2">
                        <h3 class="text-sm font-semibold text-white">{{ $chart_title }}</h3>
                    </div>
                    <div class="relative h-72 w-full">
                        <canvas id="salesHourlyChart"></canvas>
                    </div>
                </div>

                <div class="bg-aromas-secondary rounded-lg border border-aromas-tertiary/30 shadow-sm flex flex-col h-full max-h-[350px]">
                    <div class="p-4 border-b border-aromas-tertiary/30">
                        <h3 class="text-sm font-semibold text-white">Desempeño General</h3>
                    </div>
                    <div class="overflow-y-auto flex-1 p-0 custom-scrollbar">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-aromas-main/50 sticky top-0 z-10">
                                <tr class="text-aromas-tertiary text-[10px] uppercase tracking-wider">
                                    <th class="py-3 px-4 font-semibold">Vendedor</th>
                                    <th class="py-3 px-4 font-semibold text-center">Ventas</th>
                                    <th class="py-3 px-4 font-semibold text-right">Pausas</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-aromas-tertiary/20">
                                @forelse($employees_metrics as $emp)
                                    <tr class="hover:bg-aromas-tertiary/10 transition-colors">
                                        <td class="py-3 px-4 text-sm text-white font-medium">{{ $emp['name'] }}</td>
                                        <td class="py-3 px-4 text-sm text-aromas-success font-bold text-center">{{ $emp['served'] }}</td>
                                        <td class="py-3 px-4 text-sm text-aromas-warning font-bold text-right">{{ $emp['formatted_break_time'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="py-8 px-4 text-center text-sm text-gray-500">No hay datos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'performance'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            
            <div class="bg-aromas-main p-4 rounded-lg border border-aromas-tertiary/50 shadow-inner mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-white font-semibold">Análisis Detallado por Vendedor</h3>
                    <p class="text-xs text-gray-400">Selecciona un miembro del equipo para ver sus métricas.</p>
                </div>
                <form action="{{ route('admin.reports.index') }}" method="GET" class="w-full md:w-72">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="start_date" value="{{ $start_date }}">
                    <input type="hidden" name="end_date" value="{{ $end_date }}">
                    <input type="hidden" name="tab" value="performance">
                    
                    <select name="employee_id" onchange="this.form.submit()" class="w-full bg-aromas-secondary border border-aromas-tertiary/50 text-white rounded-md text-sm focus:ring-aromas-highlight focus:border-aromas-highlight shadow-sm">
                        <option value="">-- Seleccionar Vendedor --</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string)$selectedEmployeeId === (string)$employee->id ? 'selected' : '' }}>
                                {{ $employee->full_name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            @if($selectedEmployeeId)
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 text-center shadow-sm">
                        <p class="text-xs text-aromas-tertiary uppercase font-bold mb-1">Clientes Atendidos</p>
                        <p class="text-2xl font-bold text-aromas-success">{{ $empKpis['served'] }}</p>
                    </div>
                    <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 text-center shadow-sm">
                        <p class="text-xs text-aromas-tertiary uppercase font-bold mb-1">Promedio Atención</p>
                        <p class="text-2xl font-bold text-aromas-info">{{ $empKpis['formatted_avg_time'] }}</p>
                    </div>
                    <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 text-center shadow-sm">
                        <p class="text-xs text-aromas-tertiary uppercase font-bold mb-1">Extensiones (SLA)</p>
                        <p class="text-2xl font-bold text-aromas-highlight">{{ $empKpis['extensions'] }}</p>
                    </div>
                    <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 text-center shadow-sm">
                        <p class="text-xs text-aromas-tertiary uppercase font-bold mb-1">Clientes Abandonados</p>
                        <p class="text-2xl font-bold text-aromas-error">{{ $empKpis['abandoned'] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <div class="lg:col-span-2 bg-aromas-secondary rounded-lg p-5 border border-aromas-tertiary/30 shadow-sm">
                        <h3 class="text-sm font-semibold text-white mb-4 border-b border-aromas-tertiary/30 pb-2">Distribución de Tiempos por Cliente</h3>
                        <div class="relative h-64 w-full">
                            @if(count($empPerformanceData) > 0)
                                <canvas id="empStackedBarChart"></canvas>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-500 text-sm">Sin suficientes datos de ventas.</div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-aromas-secondary rounded-lg p-5 border border-aromas-tertiary/30 shadow-sm">
                        <h3 class="text-sm font-semibold text-white mb-4 border-b border-aromas-tertiary/30 pb-2">Desglose de Pausas</h3>
                        <div class="relative h-64 w-full flex justify-center">
                            @if(count($empBreaksData) > 0)
                                <canvas id="empBreaksDoughnut"></canvas>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-500 text-sm">Sin pausas registradas.</div>
                            @endif
                        </div>
                    </div>
                </div>

                @if(isset($empClientsPaginated) && $empClientsPaginated->count() > 0)
                    <div class="bg-aromas-secondary rounded-lg border border-aromas-tertiary/30 shadow-sm overflow-hidden mb-6">
                        <div class="p-4 border-b border-aromas-tertiary/30">
                            <h3 class="text-sm font-semibold text-white">Historial Detallado de Clientes</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-aromas-main/50">
                                    <tr class="text-aromas-tertiary text-[10px] uppercase tracking-wider">
                                        <th class="py-3 px-4 font-semibold">Cliente</th>
                                        <th class="py-3 px-4 font-semibold text-center">Hora de Llegada</th>
                                        <th class="py-3 px-4 font-semibold text-center">Espera Exacta</th>
                                        <th class="py-3 px-4 font-semibold text-center">Atención Exacta</th>
                                        <th class="py-3 px-4 font-semibold text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-aromas-tertiary/20">
                                    @foreach($empClientsPaginated as $client)
                                        <tr class="hover:bg-aromas-tertiary/10 transition-colors">
                                            <td class="py-3 px-4 text-sm text-white font-medium">{{ $client->client_name }}</td>
                                            <td class="py-3 px-4 text-sm text-gray-400 text-center">{{ \Carbon\Carbon::parse($client->queued_at)->format('d/m/Y H:i:s') }}</td>
                                            <td class="py-3 px-4 text-sm text-aromas-warning text-center">{{ $client->formatted_wait }}</td>
                                            <td class="py-3 px-4 text-sm text-aromas-info text-center">{{ $client->formatted_serve }}</td>
                                            <td class="py-3 px-4 text-center">
                                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-aromas-success/20 text-aromas-success">COMPLETADO</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-aromas-tertiary/30">
                            {{ $empClientsPaginated->links() }}
                        </div>
                    </div>
                @endif

            @else
                <div class="flex flex-col items-center justify-center py-16 text-gray-500 bg-aromas-secondary/50 rounded-lg border border-dashed border-aromas-tertiary/50">
                    <svg class="w-16 h-16 mb-4 opacity-50 text-aromas-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p>Selecciona un vendedor en el menú superior para cargar sus estadísticas.</p>
                </div>
            @endif
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.color = '#9ca3af';
            Chart.defaults.borderColor = 'rgba(100, 111, 123, 0.15)';
            Chart.defaults.font.family = "'Figtree', sans-serif";
            const pieColors = ['#FDC974', '#3AA580', '#2E84F2', '#D24749', '#FBC02D'];

            const ctxGlobal = document.getElementById('salesHourlyChart');
            if (ctxGlobal) {
                const chartData = @json($chart_data);
                new Chart(ctxGlobal, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Turnos Atendidos',
                            data: chartData.data,
                            borderColor: '#FDC974',
                            backgroundColor: 'rgba(253, 201, 116, 0.1)',
                            fill: true, tension: 0.4, borderWidth: 3, pointRadius: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
                });
            }

            @if($selectedEmployeeId)
                const ctxEmpBar = document.getElementById('empStackedBarChart');
                if(ctxEmpBar) {
                    const empPerfData = @json($empPerformanceData);
                    const isSingleDay = @json($is_single_day); // VARIABLE MAGICA AQUI
                    
                    new Chart(ctxEmpBar, {
                        type: 'bar',
                        data: {
                            labels: empPerfData.map(d => {
                                let date = new Date(d.queued_at);
                                if (isSingleDay) {
                                    return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                                } else {
                                    return date.toLocaleDateString('es-ES', {day: '2-digit', month: '2-digit'}) + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                                }
                            }),
                            datasets: [
                                {
                                    label: 'Tiempo de Espera (min)',
                                    data: empPerfData.map(d => d.wait_time),
                                    backgroundColor: 'rgba(253, 201, 116, 0.8)',
                                    borderColor: '#FDC974',
                                    borderWidth: 1
                                },
                                {
                                    label: 'Tiempo de Atención (min)',
                                    data: empPerfData.map(d => d.service_time),
                                    backgroundColor: 'rgba(58, 165, 128, 0.8)',
                                    borderColor: '#3AA580',
                                    borderWidth: 1
                                }
                            ]
                        },
                        options: {
                            responsive: true, maintainAspectRatio: false,
                            scales: {
                                x: { stacked: true, grid: { display: false } },
                                y: { stacked: true, beginAtZero: true, title: { display: true, text: 'Minutos Totales' } }
                            },
                            plugins: {
                                tooltip: { mode: 'index', intersect: false }
                            }
                        }
                    });
                }

                const ctxEmpBreaks = document.getElementById('empBreaksDoughnut');
                if(ctxEmpBreaks) {
                    const empBreaksData = @json($empBreaksData);
                    new Chart(ctxEmpBreaks, {
                        type: 'doughnut',
                        data: {
                            labels: empBreaksData.map(i => i.break_reason),
                            datasets: [{ 
                                data: empBreaksData.map(i => i.total), 
                                backgroundColor: pieColors, 
                                borderColor: '#22272E',
                                borderWidth: 3 
                            }]
                        },
                        options: { 
                            responsive: true, 
                            maintainAspectRatio: false, 
                            cutout: '70%',
                            plugins: { 
                                legend: { position: 'right', labels: { boxWidth: 15, font: { size: 12 } } } 
                            } 
                        }
                    });
                }
            @endif
        });
    </script>
</x-admin-layout>