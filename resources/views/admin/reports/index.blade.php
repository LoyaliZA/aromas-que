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
                    <button @click="switchTab('abandoned')" class="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-bold transition-all duration-200" :class="activeTab === 'abandoned' ? 'bg-aromas-highlight text-aromas-main shadow-md' : 'text-gray-400 hover:bg-aromas-tertiary/20 hover:text-white'">
                        Abandonos
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
                    {{-- Si estamos en empleado, mantén su ID --}}
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

                {{-- 1. VISTA: MONITOR EN TIEMPO REAL --}}
                <div x-show="activeTab === 'realtime'" style="display: none;">
                    <h3 class="text-2xl font-black text-white uppercase tracking-widest mb-6">Punto de Venta AHORA</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                        <template x-for="seller in realTimeData" :key="seller.id">
                            <div class="bg-aromas-main border rounded-xl p-5 shadow-lg transition-all" :class="{ 'border-blue-500/50': seller.state === 'SERVING', 'border-green-500/30': seller.state === 'ONLINE', 'border-yellow-500/50': seller.state === 'BREAK', 'border-gray-700 opacity-50': seller.state === 'OFFLINE' }">
                                <div class="flex justify-between mb-4 border-b border-aromas-tertiary/20 pb-3">
                                    <h4 class="font-bold text-lg text-white pr-2" x-text="seller.name"></h4>
                                    <span class="text-[10px] px-2 py-1 rounded font-black uppercase" :class="{ 'bg-blue-500/20 text-blue-400 border-blue-500/30': seller.state === 'SERVING', 'bg-green-500/20 text-green-400 border-green-500/30': seller.state === 'ONLINE', 'bg-yellow-500/20 text-yellow-400 border-yellow-500/30': seller.state === 'BREAK', 'bg-gray-700 text-gray-400': seller.state === 'OFFLINE' }" x-text="seller.state === 'SERVING' ? 'ATENDIENDO' : (seller.state === 'ONLINE' ? 'DISPONIBLE' : (seller.state === 'BREAK' ? 'EN PAUSA' : 'INACTIVO'))"></span>
                                </div>
                                <div class="space-y-4">
                                    <div x-show="seller.state !== 'OFFLINE'" class="bg-black/30 rounded-lg p-3 border border-aromas-tertiary/10 flex justify-between">
                                        <span class="text-xs text-gray-500 uppercase font-bold" x-text="seller.state === 'ONLINE' ? 'Tiempo Esperando' : 'Tiempo Transcurrido'"></span>
                                        <span class="text-xl font-mono font-black" :class="{'text-blue-400': seller.state === 'SERVING', 'text-green-400': seller.state === 'ONLINE', 'text-yellow-400': seller.state === 'BREAK'}" x-text="formatTimer(seller.state_started_at)"></span>
                                    </div>
                                    <div class="text-right pt-2"><span class="text-[10px] text-gray-500 uppercase">Ventas hoy: <strong class="text-white" x-text="seller.sales_today"></strong></span></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- 2. VISTA: REPORTE GENERAL --}}
                <div x-show="activeTab === 'general'" style="display: none;">
                    <h3 class="text-2xl font-black text-white uppercase tracking-widest mb-6">Métricas Globales</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-gray-800/50 p-5 rounded-xl border border-gray-700">
                            <p class="text-xs text-gray-400 uppercase font-bold text-center">Atendidos</p>
                            <p class="text-4xl font-black text-green-400 text-center">{{ $metrics['total_served'] }}</p>
                        </div>
                        <div class="bg-gray-800/50 p-5 rounded-xl border border-gray-700">
                            <p class="text-xs text-gray-400 uppercase font-bold text-center">Prom. Espera</p>
                            <p class="text-3xl font-black text-yellow-400 text-center">{{ $metrics['formatted_wait_time'] }}</p>
                        </div>
                        <div class="bg-gray-800/50 p-5 rounded-xl border border-gray-700">
                            <p class="text-xs text-gray-400 uppercase font-bold text-center">Prom. Atención</p>
                            <p class="text-3xl font-black text-blue-400 text-center">{{ $metrics['formatted_service_time'] }}</p>
                        </div>
                        <div class="bg-gray-800/50 p-5 rounded-xl border border-gray-700">
                            <p class="text-xs text-gray-400 uppercase font-bold text-center">Abandonos</p>
                            <p class="text-4xl font-black text-red-500 text-center">{{ $metrics['total_abandoned'] }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 bg-gray-900 rounded-xl p-6 border border-gray-700 shadow-md">
                            <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest mb-6">{{ $chart_title }}</h3>
                            <div class="relative h-72 w-full"><canvas id="salesHourlyChart"></canvas></div>
                        </div>
                        <div class="bg-gray-900 rounded-xl border border-gray-700 shadow-md flex flex-col h-full max-h-[380px]">
                            <div class="p-5 border-b border-gray-800">
                                <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Desempeño Vendedores</h3>
                            </div>
                            <div class="overflow-y-auto flex-1 custom-scrollbar">
                                <table class="w-full text-left border-collapse">
                                    <thead class="bg-gray-800/50 sticky top-0 z-10">
                                        <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                                            <th class="py-3 px-4">Vendedor</th>
                                            <th class="py-3 px-2 text-center">Ventas</th>
                                            <th class="py-3 px-2 text-center">Prom. Atn.</th>
                                            <th class="py-3 px-4 text-right">Pausas</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800">
                                        @forelse($employees_metrics as $emp)
                                        <tr class="hover:bg-gray-800">
                                            <td class="py-3 px-4 text-sm text-white truncate max-w-[120px]">{{ $emp['name'] }}</td>
                                            <td class="py-3 px-2 text-sm text-green-400 font-bold text-center">{{ $emp['served'] }}</td>
                                            <td class="py-3 px-2 text-xs text-blue-300 text-center">{{ $emp['formatted_avg_service'] }}</td>
                                            <td class="py-3 px-4 text-xs text-yellow-500 text-right">{{ $emp['formatted_break_time'] }}</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="py-8 text-center text-sm text-gray-500">No hay datos.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. VISTA: CLIENTES ATENDIDOS --}}
                <div x-show="activeTab === 'clients'" style="display: none;">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-black text-white uppercase tracking-widest">Clientes Atendidos</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.reports.export', ['type' => 'clients', 'format' => 'csv', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm border border-gray-600 transition">Descargar CSV</a>
                            <a href="{{ route('admin.reports.export', ['type' => 'clients', 'format' => 'pdf', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-aromas-highlight hover:bg-yellow-500 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm shadow-md transition">Exportar PDF</a>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-800/50 border-b border-gray-700">
                                    <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                                        <th class="p-4">Turno</th>
                                        <th class="p-4">Cliente</th>
                                        <th class="p-4">Vendedor</th>
                                        <th class="p-4 text-center">Espera</th>
                                        <th class="p-4 text-center">Atención</th>
                                        <th class="p-4 text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @forelse($detailedClients as $client)
                                    <tr class="hover:bg-gray-800/50">
                                        <td class="p-4 font-mono font-bold text-white">{{ $client->turn_number }}</td>
                                        <td class="p-4 text-sm text-gray-300">{{ $client->client_name }}</td>
                                        <td class="p-4 text-sm text-gray-300">{{ $client->assignedShift->employee->full_name ?? 'N/A' }}</td>
                                        <td class="p-4 text-sm text-yellow-400 font-mono text-center">{{ $client->formatted_wait }}</td>
                                        <td class="p-4 text-sm text-blue-400 font-mono text-center">{{ $client->formatted_serve }}</td>
                                        <td class="p-4 text-center">@if($client->is_reattended) <span class="bg-blue-500/20 text-blue-400 text-[10px] px-2 py-1 rounded border border-blue-500/30 uppercase font-bold">RE-ATENDIDO</span> @else <span class="bg-green-500/20 text-green-400 text-[10px] px-2 py-1 rounded border border-green-500/30 uppercase font-bold">NORMAL</span> @endif</td>
                                    </tr>
                                    @empty <tr>
                                        <td colspan="6" class="p-8 text-center text-gray-500">No hay registros.</td>
                                    </tr> @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-700 bg-gray-900">{{ $detailedClients->links() }}</div>
                    </div>
                </div>

                {{-- 4. VISTA: ABANDONOS --}}
                <div x-show="activeTab === 'abandoned'" style="display: none;">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-black text-white uppercase tracking-widest">Abandonos</h3>
                        <div class="flex gap-2">
                            <a href="{{ route('admin.reports.export', ['type' => 'abandoned', 'format' => 'csv', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm border border-gray-600 transition">Descargar CSV</a>
                            <a href="{{ route('admin.reports.export', ['type' => 'abandoned', 'format' => 'pdf', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-aromas-highlight hover:bg-yellow-500 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm shadow-md transition">Exportar PDF</a>
                        </div>
                    </div>
                    <div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-800/50 border-b border-gray-700">
                                    <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                                        <th class="p-4">Turno</th>
                                        <th class="p-4">Cliente</th>
                                        <th class="p-4">Fecha / Hora</th>
                                        <th class="p-4">Motivo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @forelse($detailedAbandoned as $abandoned)
                                    <tr class="hover:bg-gray-800/50">
                                        <td class="p-4 font-mono font-bold text-white">{{ $abandoned->turn_number }}</td>
                                        <td class="p-4 text-sm text-gray-300">{{ $abandoned->client_name }}</td>
                                        <td class="p-4 text-sm text-gray-500">{{ \Carbon\Carbon::parse($abandoned->queued_at)->format('d/m/Y H:i') }}</td>
                                        <td class="p-4 text-sm text-red-400 italic">
                                            @if(!empty($abandoned->custom_abandonment_reason))
                                            {{ $abandoned->custom_abandonment_reason }}
                                            @elseif($abandoned->abandonment_reason_id)
                                            {{ $abandoned->abandonmentReason->reason }}
                                            @else
                                            Cancelado por inactividad
                                            @endif
                                        </td>
                                    </tr>
                                    @empty <tr>
                                        <td colspan="4" class="p-8 text-center text-gray-500">No hay abandonos.</td>
                                    </tr> @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-700 bg-gray-900">{{ $detailedAbandoned->links() }}</div>
                    </div>
                </div>

                {{-- 5. VISTA: RENDIMIENTO INDIVIDUAL --}}
                <div x-show="activeTab === 'performance'" style="display: none;">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-black text-white uppercase tracking-widest">Rendimiento por Vendedor</h3>
                        @if($empData)
                        <div class="flex gap-2">
                            <a href="{{ route('admin.reports.export', ['type' => 'employee', 'employee_id' => $selectedEmployeeId, 'format' => 'csv', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm border border-gray-600 transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg> CSV
                            </a>
                            <a href="{{ route('admin.reports.export', ['type' => 'employee', 'employee_id' => $selectedEmployeeId, 'format' => 'pdf', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-aromas-highlight hover:bg-yellow-500 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm shadow-md transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg> PDF
                            </a>
                        </div>
                        @endif
                    </div>

                    {{-- Buscador de Empleado --}}
                    <div class="bg-gray-800 p-4 rounded-xl border border-gray-700 mb-6">
                        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-4">
                            <input type="hidden" name="tab" value="performance">
                            <input type="hidden" name="period" value="{{ $period }}">
                            <input type="hidden" name="start_date" value="{{ $start_date }}">
                            <input type="hidden" name="end_date" value="{{ $end_date }}">
                            <label class="text-sm font-bold text-gray-300">Seleccionar Vendedor:</label>
                            <select name="employee_id" class="bg-gray-900 text-white border border-gray-600 rounded-lg px-4 py-2 flex-1 max-w-md focus:ring-aromas-highlight" onchange="this.form.submit()">
                                <option value="">-- Elige un vendedor --</option>
                                @foreach($employeesList as $emp)
                                <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    @if($empData)
                    {{-- KPIs Empleado --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center">
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Clientes Atendidos</p>
                            <p class="text-4xl font-black text-green-400">{{ $empData['kpis']['served'] }}</p>
                        </div>
                        <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center">
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Promedio Atención</p>
                            <p class="text-4xl font-black text-blue-400">{{ $empData['kpis']['avg_time'] }}</p>
                        </div>
                        <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center">
                            <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Tiempo en Pausa (Total)</p>
                            <p class="text-4xl font-black text-yellow-500">{{ $empData['kpis']['total_break'] }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                        {{-- Desglose de Pausas por Día --}}
                        <div class="bg-gray-900 rounded-xl border border-gray-700 p-5 flex flex-col h-[260px]">
                            <div class="border-b border-gray-800 pb-2 mb-3">
                                <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Desglose de Pausas (Por Día)</h3>
                            </div>
                            <div class="overflow-y-auto flex-1 custom-scrollbar space-y-4">
                                @forelse($empData['daily_breaks'] as $date => $breaks)
                                <div>
                                    <h4 class="text-xs font-black text-aromas-highlight mb-2 bg-gray-800/50 p-1.5 rounded uppercase tracking-widest">{{ \Carbon\Carbon::parse($date)->format('d / m / Y') }}</h4>
                                    <div class="space-y-1">
                                        @foreach($breaks as $reasonName => $time)
                                        <div class="flex justify-between items-center px-2 py-1 border-b border-gray-800/50 last:border-0">
                                            <span class="text-[11px] text-gray-400 font-bold uppercase">{{ $reasonName }}</span>
                                            <span class="text-[11px] text-yellow-500 font-mono">{{ $time }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @empty
                                <p class="text-sm text-gray-500 italic text-center py-4">No registró pausas en este periodo.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Bitácora/Timeline CON SCROLL FIJO --}}
                        <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-700 h-[260px] flex flex-col overflow-hidden relative">
                            <div class="sticky top-0 bg-gray-900 z-10 p-4 border-b border-gray-800 shadow-sm">
                                <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Línea de Tiempo (Actividad)</h3>
                            </div>
                            <div class="p-4 flex-1 overflow-y-auto custom-scrollbar">
                                <ul class="space-y-3">
                                    @forelse($empData['timeline'] as $log)
                                    <li class="flex gap-4 text-sm items-center border-l-2 pl-3 {{ $log['color'] === 'text-yellow-500' ? 'border-yellow-500' : 'border-green-400' }}">
                                        <span class="text-gray-500 font-mono w-24 flex-shrink-0">{{ $log['date'] }} <br><span class="text-xs">{{ $log['time'] }}</span></span>
                                        <span class="flex-1 font-bold {{ $log['color'] }}">Cambio a: {{ $log['status'] }}</span>
                                    </li>
                                    @empty
                                    <li class="text-gray-500 italic text-center py-4 border-l-2 border-gray-700 pl-3">No hay registro de actividad.</li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla Empleado --}}
                    <div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
                        <div class="p-4 border-b border-gray-700 bg-gray-800/50">
                            <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Historial de Clientes</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead class="bg-gray-800/50 border-b border-gray-700">
                                    <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                                        <th class="p-4">Turno</th>
                                        <th class="p-4">Cliente</th>
                                        <th class="p-4 text-center">Tipo</th>
                                        <th class="p-4 text-center">Espera</th>
                                        <th class="p-4 text-center">Atención</th>
                                        <th class="p-4 text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    @forelse($empData['clients'] as $client)
                                    <tr class="hover:bg-gray-800/50">
                                        <td class="p-4 font-mono font-bold text-white">{{ $client->turn_number }}</td>
                                        <td class="p-4 text-sm text-gray-300">{{ $client->client_name }}</td>
                                        <td class="p-4 text-center">
                                            @if($client->client_type === 'VIP') <span class="bg-yellow-500/20 text-yellow-400 text-[10px] px-2 py-1 rounded border border-yellow-500/30 uppercase font-bold">VIP</span>
                                            @elseif($client->has_disability) <span class="bg-blue-500/20 text-blue-400 text-[10px] px-2 py-1 rounded border border-blue-500/30 uppercase font-bold">PREF</span>
                                            @else <span class="text-gray-500 text-xs font-bold uppercase">Regular</span> @endif
                                        </td>
                                        <td class="p-4 text-sm text-yellow-400 font-mono text-center">{{ $client->formatted_wait }}</td>
                                        <td class="p-4 text-sm text-blue-400 font-mono text-center">{{ $client->formatted_serve }}</td>
                                        <td class="p-4 text-center">@if($client->is_reattended) <span class="bg-blue-500/20 text-blue-400 text-[10px] px-2 py-1 rounded border border-blue-500/30 uppercase font-bold">RE-ATENDIDO</span> @else <span class="bg-green-500/20 text-green-400 text-[10px] px-2 py-1 rounded border border-green-500/30 uppercase font-bold">NORMAL</span> @endif</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="p-8 text-center text-gray-500">Sin clientes atendidos.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-700 bg-gray-900">{{ $empData['clients']->links() }}</div>
                    </div>
                    @else
                    <div class="text-center py-20 bg-gray-900 rounded-xl border border-gray-700">
                        <p class="text-gray-500">Selecciona un vendedor en la parte superior para ver sus estadísticas.</p>
                    </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT NATIVO --}}
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
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Turnos Atendidos',
                                data: chartData.data,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                fill: true,
                                tension: 0.4,
                                borderWidth: 3,
                                pointRadius: 4,
                                pointBackgroundColor: '#FDC974'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        }
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
                    if (this.activeTab === 'realtime') {
                        this.startRealTime();
                    }
                },
                switchTab(tab) {
                    this.activeTab = tab;
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', tab);
                    window.history.replaceState(null, '', url.toString());
                    if (tab === 'realtime') {
                        this.startRealTime();
                    } else {
                        this.stopRealTime();
                    }
                },
                startRealTime() {
                    this.fetchRealTimeData();
                    this.pollingInterval = setInterval(() => {
                        this.fetchRealTimeData();
                    }, 5000);
                    this.clockInterval = setInterval(() => {
                        this.currentTime = Date.now();
                    }, 1000);
                },
                stopRealTime() {
                    clearInterval(this.pollingInterval);
                    clearInterval(this.clockInterval);
                },
                fetchRealTimeData() {
                    fetch("{{ route('admin.reports.realtime') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.realTimeData = data.sellers;
                        })
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