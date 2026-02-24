<x-admin-layout>
    <div x-data="{ activeTab: new URLSearchParams(window.location.search).get('page') ? 'audit' : '{{ $activeTab }}' }">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-white mb-4 md:mb-0">
                Centro de Reportes
            </h2>
            
            <div class="flex flex-wrap gap-2 bg-aromas-secondary p-1 rounded-lg border border-aromas-tertiary/30 shadow-sm">
                <button @click="activeTab = 'dashboard'" :class="activeTab === 'dashboard' ? 'bg-aromas-highlight text-aromas-main shadow' : 'text-gray-300 hover:bg-aromas-tertiary/30 hover:text-white'" class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    Dashboard
                </button>
                <button @click="activeTab = 'performance'" :class="activeTab === 'performance' ? 'bg-aromas-highlight text-aromas-main shadow' : 'text-gray-300 hover:bg-aromas-tertiary/30 hover:text-white'" class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Rendimiento
                </button>
                <button @click="activeTab = 'export'" :class="activeTab === 'export' ? 'bg-aromas-highlight text-aromas-main shadow' : 'text-gray-300 hover:bg-aromas-tertiary/30 hover:text-white'" class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Exportar
                </button>
                <button @click="activeTab = 'audit'" :class="activeTab === 'audit' ? 'bg-aromas-highlight text-aromas-main shadow' : 'text-gray-300 hover:bg-aromas-tertiary/30 hover:text-white'" class="px-3 py-2 text-sm font-medium rounded-md transition-colors focus:outline-none flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    Auditoría
                </button>
            </div>
        </div>

        <div x-show="activeTab === 'dashboard' || activeTab === 'performance'" class="flex justify-end mb-4">
            <div class="flex flex-wrap gap-2 bg-aromas-main p-1 rounded-lg border border-aromas-tertiary/20">
                <a href="{{ route('admin.reports.index', ['period' => 'today', 'tab' => $activeTab, 'employee_id' => $selectedEmployeeId]) }}" class="px-3 py-1 text-xs font-medium rounded transition-colors {{ $period === 'today' ? 'bg-aromas-secondary text-white shadow' : 'text-gray-400 hover:text-white' }}">Hoy</a>
                <a href="{{ route('admin.reports.index', ['period' => '7days', 'tab' => $activeTab, 'employee_id' => $selectedEmployeeId]) }}" class="px-3 py-1 text-xs font-medium rounded transition-colors {{ $period === '7days' ? 'bg-aromas-secondary text-white shadow' : 'text-gray-400 hover:text-white' }}">7 Días</a>
                <a href="{{ route('admin.reports.index', ['period' => 'month', 'tab' => $activeTab, 'employee_id' => $selectedEmployeeId]) }}" class="px-3 py-1 text-xs font-medium rounded transition-colors {{ $period === 'month' ? 'bg-aromas-secondary text-white shadow' : 'text-gray-400 hover:text-white' }}">Mes</a>
                <a href="{{ route('admin.reports.index', ['period' => 'all', 'tab' => $activeTab, 'employee_id' => $selectedEmployeeId]) }}" class="px-3 py-1 text-xs font-medium rounded transition-colors {{ $period === 'all' ? 'bg-aromas-secondary text-white shadow' : 'text-gray-400 hover:text-white' }}">Histórico</a>
            </div>
        </div>

        <div x-show="activeTab === 'dashboard'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">
                <div class="bg-aromas-secondary rounded-lg p-3 border border-aromas-tertiary/30 flex flex-col items-center justify-center text-center">
                    <p class="text-[10px] font-semibold text-aromas-tertiary uppercase tracking-wider mb-1">Atendidos</p>
                    <p class="text-xl font-bold text-aromas-success leading-none">{{ $totalAtendidos }}</p>
                </div>
                <div class="bg-aromas-secondary rounded-lg p-3 border border-aromas-tertiary/30 flex flex-col items-center justify-center text-center">
                    <p class="text-[10px] font-semibold text-aromas-tertiary uppercase tracking-wider mb-1">Espera (Min)</p>
                    <p class="text-xl font-bold text-aromas-warning leading-none">{{ $avgWaitTime }}</p>
                </div>
                <div class="bg-aromas-secondary rounded-lg p-3 border border-aromas-tertiary/30 flex flex-col items-center justify-center text-center">
                    <p class="text-[10px] font-semibold text-aromas-tertiary uppercase tracking-wider mb-1">Atención (Min)</p>
                    <p class="text-xl font-bold text-aromas-info leading-none">{{ $avgServiceTime }}</p>
                </div>
                <div class="bg-aromas-secondary rounded-lg p-3 border border-aromas-tertiary/30 flex flex-col items-center justify-center text-center">
                    <p class="text-[10px] font-semibold text-aromas-tertiary uppercase tracking-wider mb-1">Almacén (Min)</p>
                    <p class="text-xl font-bold text-[#D24749] leading-none">{{ $avgWarehouseTime }}</p> 
                </div>
                <div class="bg-aromas-secondary rounded-lg p-3 border border-aromas-tertiary/30 flex flex-col items-center justify-center text-center">
                    <p class="text-[10px] font-semibold text-aromas-tertiary uppercase tracking-wider mb-1">Abandonos</p>
                    <p class="text-xl font-bold text-aromas-error leading-none">{{ $totalAbandonos }}</p>
                </div>
                <div class="bg-aromas-secondary rounded-lg p-3 border border-aromas-tertiary/30 flex flex-col items-center justify-center text-center">
                    <p class="text-[10px] font-semibold text-aromas-tertiary uppercase tracking-wider mb-1">Extensiones</p>
                    <p class="text-xl font-bold text-aromas-highlight leading-none">{{ $peticionesTiempo }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="space-y-4">
                    <div class="bg-aromas-secondary rounded-lg p-4 border border-aromas-tertiary/30">
                        <h3 class="text-sm font-semibold text-white mb-2 border-b border-aromas-tertiary/30 pb-1">Top Vendedores</h3>
                        <div class="relative h-48 w-full"><canvas id="topSellersChart"></canvas></div>
                    </div>
                    <div class="bg-aromas-secondary rounded-lg p-4 border border-aromas-tertiary/30">
                        <h3 class="text-sm font-semibold text-white mb-2 border-b border-aromas-tertiary/30 pb-1">Motivos de Pausa Globales</h3>
                        <div class="relative h-56 w-full flex justify-center"><canvas id="breaksChart"></canvas></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4">
                    <div class="bg-aromas-secondary rounded-lg p-4 border border-aromas-tertiary/30">
                        <h3 class="text-sm font-semibold text-white mb-2 border-b border-aromas-tertiary/30 pb-1 text-center">Servicios Solicitados</h3>
                        <div class="relative h-40 w-full flex justify-center"><canvas id="servicesChart"></canvas></div>
                    </div>
                    <div class="bg-aromas-secondary rounded-lg p-4 border border-aromas-tertiary/30">
                        <h3 class="text-sm font-semibold text-white mb-2 border-b border-aromas-tertiary/30 pb-1 text-center">Entregas: Aromas vs Bellaroma</h3>
                        <div class="relative h-40 w-full flex justify-center"><canvas id="pickupsChart"></canvas></div>
                    </div>
                    <div class="bg-aromas-secondary rounded-lg p-4 border border-aromas-tertiary/30 md:col-span-2 lg:col-span-1">
                        <h3 class="text-sm font-semibold text-white mb-2 border-b border-aromas-tertiary/30 pb-1 text-center">Titular vs Terceros</h3>
                        <div class="relative h-40 w-full flex justify-center"><canvas id="thirdPartyChart"></canvas></div>
                    </div>
                </div>
            </div>
        </div>

        <div x-show="activeTab === 'performance'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            
            <div class="bg-aromas-secondary p-4 rounded-lg border border-aromas-tertiary/30 shadow-md mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-white font-semibold">Análisis de Vendedor</h3>
                    <p class="text-xs text-gray-400">Selecciona un empleado para ver sus métricas detalladas.</p>
                </div>
                <form action="{{ route('admin.reports.index') }}" method="GET" class="flex-shrink-0 w-64">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <input type="hidden" name="tab" value="performance">
                    <select name="employee_id" onchange="this.form.submit()" class="w-full bg-aromas-main border border-aromas-tertiary/50 text-white rounded-md text-sm focus:ring-aromas-highlight focus:border-aromas-highlight shadow-sm">
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
                    <div class="bg-aromas-main p-4 rounded-lg border border-aromas-tertiary/20 text-center shadow-inner">
                        <p class="text-xs text-aromas-tertiary uppercase font-bold mb-1">Clientes Atendidos</p>
                        <p class="text-2xl font-bold text-aromas-success">{{ $empKpis['served'] }}</p>
                    </div>
                    <div class="bg-aromas-main p-4 rounded-lg border border-aromas-tertiary/20 text-center shadow-inner">
                        <p class="text-xs text-aromas-tertiary uppercase font-bold mb-1">Tiempo Promedio (Min)</p>
                        <p class="text-2xl font-bold text-aromas-info">{{ $empKpis['avg_time'] }}</p>
                    </div>
                    <div class="bg-aromas-main p-4 rounded-lg border border-aromas-tertiary/20 text-center shadow-inner">
                        <p class="text-xs text-aromas-tertiary uppercase font-bold mb-1">Extensiones Solicitadas</p>
                        <p class="text-2xl font-bold text-aromas-highlight">{{ $empKpis['extensions'] }}</p>
                    </div>
                    <div class="bg-aromas-main p-4 rounded-lg border border-aromas-tertiary/20 text-center shadow-inner">
                        <p class="text-xs text-aromas-tertiary uppercase font-bold mb-1">Clientes Abandonados</p>
                        <p class="text-2xl font-bold text-aromas-error">{{ $empKpis['abandoned'] }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-aromas-secondary rounded-lg p-5 border border-aromas-tertiary/30 shadow-md">
                        <h3 class="text-sm font-semibold text-white mb-4 border-b border-aromas-tertiary/30 pb-2">Curva de Tiempos de Atención y Espera</h3>
                        <div class="relative h-64 w-full">
                            <canvas id="empLineChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-aromas-secondary rounded-lg p-5 border border-aromas-tertiary/30 shadow-md">
                        <h3 class="text-sm font-semibold text-white mb-4 border-b border-aromas-tertiary/30 pb-2">Desglose de Breaks</h3>
                        <div class="relative h-64 w-full flex justify-center">
                            @if(count($empBreaksData) > 0)
                                <canvas id="empBreaksChart"></canvas>
                            @else
                                <div class="flex items-center justify-center h-full text-gray-500 text-sm">Sin pausas registradas</div>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-gray-500 bg-aromas-secondary/50 rounded-lg border border-dashed border-aromas-tertiary/50">
                    <svg class="w-16 h-16 mb-4 opacity-50 text-aromas-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p>Selecciona un vendedor en el menú superior para cargar sus estadísticas.</p>
                </div>
            @endif
        </div>

        <div x-show="activeTab === 'export'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div class="bg-aromas-secondary rounded-lg p-6 border border-aromas-tertiary/30 shadow-md max-w-4xl mx-auto mt-4">
                <div class="mb-6 border-b border-aromas-tertiary/30 pb-3">
                    <h3 class="text-lg font-semibold text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-aromas-success" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Exportar Reporte Detallado (Excel)
                    </h3>
                    <p class="text-sm text-gray-400 mt-1">Genera un archivo .xlsx con el desglose turno por turno y tiempos exactos calculados.</p>
                </div>

                <form action="{{ route('admin.reports.export') }}" method="GET" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-300 mb-1">Fecha de Inicio</label>
                            <input type="date" id="start_date" name="start_date" class="w-full bg-aromas-main border border-aromas-tertiary/50 text-white rounded-md focus:ring-aromas-highlight focus:border-aromas-highlight">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-300 mb-1">Fecha de Fin</label>
                            <input type="date" id="end_date" name="end_date" class="w-full bg-aromas-main border border-aromas-tertiary/50 text-white rounded-md focus:ring-aromas-highlight focus:border-aromas-highlight">
                        </div>
                    </div>

                    <div>
                        <label for="employee_id_export" class="block text-sm font-medium text-gray-300 mb-1">Filtrar por Vendedor (Opcional)</label>
                        <select id="employee_id_export" name="employee_id" class="w-full bg-aromas-main border border-aromas-tertiary/50 text-white rounded-md focus:ring-aromas-highlight focus:border-aromas-highlight">
                            <option value="">Todos los vendedores</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="flex items-center px-6 py-2.5 bg-aromas-success text-white text-sm font-medium rounded-md hover:bg-opacity-80 transition-colors shadow">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Descargar Reporte en Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="activeTab === 'audit'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">
            <div class="bg-aromas-secondary rounded-lg border border-aromas-tertiary/30 shadow-md overflow-hidden mt-4">
                <div class="p-4 border-b border-aromas-tertiary/30 bg-aromas-main/30 flex justify-between items-center">
                    <p class="text-sm text-gray-400">Registro inmutable de modificaciones de entregas y estatus.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-aromas-main/50 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/30">
                                <th class="p-4 font-semibold">Fecha y Hora</th>
                                <th class="p-4 font-semibold">Usuario</th>
                                <th class="p-4 font-semibold">Paquete</th>
                                <th class="p-4 font-semibold">Motivo</th>
                                <th class="p-4 font-semibold text-right">Cambios</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-aromas-tertiary/20">
                            @forelse($audits as $audit)
                                <tr class="hover:bg-aromas-tertiary/10 transition-colors">
                                    <td class="p-4 text-xs text-gray-300">
                                        {{ $audit->created_at->format('d/m/Y') }} <br>
                                        <span class="text-gray-500">{{ $audit->created_at->format('H:i:s') }}</span>
                                    </td>
                                    <td class="p-4 text-xs">
                                        <span class="text-white font-medium">{{ $audit->user_name }}</span> <br>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-aromas-main text-gray-400 border border-aromas-tertiary/50">{{ $audit->user_role }}</span>
                                    </td>
                                    <td class="p-4 text-xs text-aromas-info font-medium">#{{ $audit->pickup_id }}</td>
                                    <td class="p-4 text-xs text-aromas-warning italic">"{{ $audit->reason }}"</td>
                                    <td class="p-4 text-xs text-right">
                                        <div class="inline-block text-left bg-aromas-main p-2 rounded border border-aromas-tertiary/30 text-[10px] text-gray-400 max-w-[200px] overflow-x-auto">
                                            @php $changes = json_decode($audit->changes, true); @endphp
                                            @if($changes)
                                                <ul class="space-y-1">
                                                    @foreach($changes as $field => $values)
                                                        <li>
                                                            <span class="text-gray-300">{{ $field }}:</span> 
                                                            <span class="text-aromas-error line-through">{{ $values['old'] ?? 'N/A' }}</span> &rarr;
                                                            <span class="text-aromas-success">{{ $values['new'] ?? 'N/A' }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span>Sin detalles</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-500 text-sm">No hay registros de auditoría.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($audits->hasPages())
                    <div class="p-4 border-t border-aromas-tertiary/30 bg-aromas-main/30">
                        {{ $audits->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.color = '#9ca3af'; 
            Chart.defaults.borderColor = 'rgba(100, 111, 123, 0.15)'; 
            Chart.defaults.font.family = "'Figtree', sans-serif";
            
            const polarColors = ['rgba(253, 201, 116, 0.7)', 'rgba(46, 132, 242, 0.7)', 'rgba(58, 165, 128, 0.7)', 'rgba(210, 71, 73, 0.7)', 'rgba(251, 192, 45, 0.7)'];

            // ================== GRÁFICAS DEL DASHBOARD ==================
            if(document.getElementById('topSellersChart')) {
                const sellersData = @json($topSellers);
                new Chart(document.getElementById('topSellersChart'), {
                    type: 'bar',
                    data: {
                        labels: sellersData.map(i => i.full_name.split(' ')[0]),
                        datasets: [{ data: sellersData.map(i => i.total_sales), backgroundColor: '#FDC974', borderRadius: 4, maxBarThickness: 30 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } } }
                });
            }

            if(document.getElementById('breaksChart')) {
                const breaksData = @json($breakReasons);
                new Chart(document.getElementById('breaksChart'), {
                    type: 'polarArea',
                    data: {
                        labels: breaksData.map(i => i.break_reason),
                        datasets: [{ data: breaksData.map(i => i.total), backgroundColor: polarColors, borderColor: '#22272E', borderWidth: 2 }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } }, scales: { r: { ticks: { display: false }, grid: { color: 'rgba(100, 111, 123, 0.2)' } } } }
                });
            }

            if(document.getElementById('servicesChart')) {
                const servicesData = @json($serviceTypes);
                new Chart(document.getElementById('servicesChart'), {
                    type: 'doughnut',
                    data: {
                        labels: servicesData.map(i => i.service_type === 'SALES' ? 'Ventas' : 'Cajas'),
                        datasets: [{ data: servicesData.map(i => i.total), backgroundColor: ['#2E84F2', '#3AA580'], borderWidth: 2, borderColor: '#394049' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } }, cutout: '70%' }
                });
            }

            if(document.getElementById('pickupsChart')) {
                const pickupsData = @json($pickupsByDept);
                new Chart(document.getElementById('pickupsChart'), {
                    type: 'doughnut',
                    data: {
                        labels: pickupsData.map(i => i.department),
                        datasets: [{ data: pickupsData.map(i => i.total), backgroundColor: ['#D24749', '#FDC974'], borderWidth: 2, borderColor: '#394049' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } }, cutout: '70%' }
                });
            }

            if(document.getElementById('thirdPartyChart')) {
                const thirdPartyData = @json($thirdPartyPickups);
                new Chart(document.getElementById('thirdPartyChart'), {
                    type: 'doughnut',
                    data: {
                        labels: thirdPartyData.map(i => Number(i.is_third_party) === 1 ? 'Tercero' : 'Titular'),
                        datasets: [{ data: thirdPartyData.map(i => i.total), backgroundColor: ['#FBC02D', '#9ca3af'], borderWidth: 2, borderColor: '#394049' }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } }, cutout: '70%' }
                });
            }

            // ================== GRÁFICAS INDIVIDUALES (RENDIMIENTO) ==================
            @if($selectedEmployeeId)
                if(document.getElementById('empLineChart')) {
                    const empPerfData = @json($empPerformanceData);
                    new Chart(document.getElementById('empLineChart'), {
                        type: 'line',
                        data: {
                            // Extraemos la hora para ponerla en la base X
                            labels: empPerfData.map(d => {
                                let date = new Date(d.queued_at);
                                return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                            }),
                            datasets: [
                                {
                                    label: 'Tiempo de Atención (min)',
                                    data: empPerfData.map(d => d.service_time),
                                    borderColor: '#3AA580', // Verde éxito
                                    backgroundColor: 'rgba(58, 165, 128, 0.1)',
                                    fill: true,
                                    tension: 0.4, // ESTO HACE LAS LÍNEAS CURVAS E INTERPOLADAS
                                    borderWidth: 2,
                                    pointRadius: 3
                                },
                                {
                                    label: 'Tiempo de Espera (min)',
                                    data: empPerfData.map(d => d.wait_time),
                                    borderColor: '#FDC974', // Dorado
                                    backgroundColor: 'rgba(253, 201, 116, 0.1)',
                                    fill: true,
                                    tension: 0.4, // ESTO HACE LAS LÍNEAS CURVAS E INTERPOLADAS
                                    borderWidth: 2,
                                    pointRadius: 3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: { mode: 'index', intersect: false }
                            },
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Minutos' } },
                                x: { grid: { display: false } }
                            },
                            interaction: { mode: 'nearest', axis: 'x', intersect: false }
                        }
                    });
                }

                if(document.getElementById('empBreaksChart')) {
                    const empBreaksData = @json($empBreaksData);
                    new Chart(document.getElementById('empBreaksChart'), {
                        type: 'polarArea',
                        data: {
                            labels: empBreaksData.map(i => i.break_reason),
                            datasets: [{ data: empBreaksData.map(i => i.total), backgroundColor: polarColors, borderColor: '#22272E', borderWidth: 2 }]
                        },
                        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 10, font: { size: 10 } } } }, scales: { r: { ticks: { display: false }, grid: { color: 'rgba(100, 111, 123, 0.2)' } } } }
                    });
                }
            @endif
        });
    </script>
</x-admin-layout>