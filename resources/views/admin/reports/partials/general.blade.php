<div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl font-black text-white uppercase tracking-widest">Reporte General</h3>
    <div class="flex gap-2">
        <a href="{{ route('admin.reports.export', ['type' => 'general', 'format' => 'csv', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm border border-gray-600 transition">Descargar CSV</a>
        <a href="{{ route('admin.reports.export', ['type' => 'general', 'format' => 'pdf', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-aromas-highlight hover:bg-yellow-500 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm shadow-md transition">Exportar PDF</a>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center shadow-md"><p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Atendidos</p><p class="text-4xl font-black text-green-400">{{ $metrics['total_served'] }}</p></div>
    <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center shadow-md"><p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Abandonos</p><p class="text-4xl font-black text-red-400">{{ $metrics['total_abandoned'] }}</p></div>
    <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center shadow-md"><p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Prom. Espera</p><p class="text-2xl font-black text-yellow-400 mt-2">{{ $metrics['formatted_wait_time'] }}</p></div>
    <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center shadow-md"><p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Prom. Atención</p><p class="text-2xl font-black text-blue-400 mt-2">{{ $metrics['formatted_service_time'] }}</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Gráfica --}}
    <div class="lg:col-span-2 bg-gray-900 p-5 rounded-xl border border-gray-700 shadow-md">
        <h4 class="text-sm font-bold text-gray-300 uppercase tracking-widest mb-4">{{ $chart_title }}</h4>
        <div class="h-64 relative w-full"><canvas id="salesHourlyChart"></canvas></div>
    </div>
    
    {{-- Top Vendedores --}}
    <div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md flex flex-col">
        <div class="p-4 border-b border-gray-800 bg-gray-800/30">
            <h4 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Desempeño Global</h4>
        </div>
        <div class="overflow-y-auto flex-1 custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-800/50 border-b border-gray-700 sticky top-0">
                    <tr class="text-gray-400 text-[9px] uppercase tracking-wider">
                        <th class="p-3">Vendedor</th>
                        <th class="p-3 text-center">⭐</th>
                        <th class="p-3 text-center">Turnos</th>
                        <th class="p-3 text-center">Promedio</th>
                        <th class="p-3 text-center">Pausas</th>
                        <th class="p-3 text-center">Incidencias</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800">
                    @foreach($employees_metrics as $em)
                    <tr class="hover:bg-gray-800/50 transition-colors">
                        <td class="p-3 text-xs text-gray-300 font-bold truncate max-w-[120px]">{{ $em['name'] }}</td>
                        <td class="p-3 text-xs text-center text-yellow-400 font-bold">{{ $em['avg_stars'] }}</td>
                        <td class="p-3 text-xs text-center text-green-400 font-mono">{{ $em['served'] }}</td>
                        <td class="p-3 text-xs text-center text-blue-400 font-mono">{{ $em['formatted_avg_service'] }}</td>
                        <td class="p-3 text-[10px] text-center text-yellow-500 font-mono">{{ $em['formatted_break_time'] }}</td>
                        <td class="p-3 text-xs text-center text-red-400 font-mono">{{ $em['incidents'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>