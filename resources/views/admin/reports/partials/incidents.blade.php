<div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl font-black text-white uppercase tracking-widest">Incidencias de Atención</h3>
    <div class="flex gap-2">
        <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'incidents', 'format' => 'csv'])) }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm border border-gray-600 transition">Descargar CSV</a>
        <a href="{{ route('admin.reports.export', array_merge(request()->query(), ['type' => 'incidents', 'format' => 'pdf'])) }}" class="bg-aromas-highlight hover:bg-yellow-500 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm shadow-md transition">Exportar PDF</a>
    </div>
</div>

<form method="GET" action="{{ route('admin.reports.index') }}" class="bg-aromas-secondary/80 p-4 rounded-xl border border-aromas-tertiary/30 shadow-md mb-6 flex flex-col md:flex-row gap-4 items-end">
    <input type="hidden" name="tab" value="incidents">
    @if(request('period')) <input type="hidden" name="period" value="{{ request('period') }}"> @endif
    @if(request('start_date')) <input type="hidden" name="start_date" value="{{ request('start_date') }}"> @endif
    @if(request('end_date')) <input type="hidden" name="end_date" value="{{ request('end_date') }}"> @endif

    <div class="w-full md:w-64">
        <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Filtrar por Vendedor</label>
        <select name="seller_id_filter" class="w-full bg-gray-900 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:border-aromas-highlight transition">
            <option value="">Todos los Vendedores</option>
            @foreach($employeesList as $emp)
                <option value="{{ $emp->id }}" {{ request('seller_id_filter') == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="flex gap-2 w-full md:w-auto">
        <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-500 text-white font-bold px-5 py-2 rounded-lg text-sm shadow-md transition">Filtrar</button>
        <a href="{{ route('admin.reports.index', ['tab' => 'incidents', 'period' => request('period', 'today'), 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="w-full md:w-auto bg-gray-700 hover:bg-gray-600 text-center text-white font-bold px-4 py-2 rounded-lg text-sm transition">Limpiar</a>
    </div>
</form>

<div class="grid grid-cols-2 md:grid-cols-2 gap-4 mb-6">
    <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center shadow-md">
        <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Incidencias Registradas</p>
        <p class="text-4xl font-black text-yellow-400">{{ $total_incidents }}</p>
    </div>
    <div class="bg-gray-900 p-5 rounded-xl border border-gray-700 text-center shadow-md">
        <p class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Promedio Atención General</p>
        <p class="text-2xl font-black text-blue-400 mt-2">{{ $metrics['formatted_service_time'] }}</p>
    </div>
</div>

<div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
    <div class="p-4 border-b border-gray-800 bg-gray-800/30">
        <h4 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Detalle de Incidencias</h4>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-left border-collapse">
            <thead class="bg-gray-800/50 border-b border-gray-700">
                <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                    <th class="p-3">Fecha / Hora</th>
                    <th class="p-3">Turno / Cliente</th>
                    <th class="p-3">Vendedor</th>
                    <th class="p-3">Razón / Detalle</th>
                    <th class="p-3 text-center">Prórrogas</th>
                    <th class="p-3 text-center">Tiempo Atendido</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($detailedIncidents as $incident)
                @php
                    $queue = $incident->salesQueue;
                    $prorrogas = $queue ? ($queue->extension_count ?? 0) : 0;
                    $tiempoAtendido = 'N/A';
                    if ($queue && $queue->started_serving_at) {
                        $end = $queue->completed_at ? \Carbon\Carbon::parse($queue->completed_at) : \Carbon\Carbon::parse($incident->created_at);
                        $diff = \Carbon\Carbon::parse($queue->started_serving_at)->diffInSeconds($end);
                        $tiempoAtendido = gmdate($diff >= 3600 ? "H:i:s" : "i:s", $diff);
                    }
                @endphp
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="p-3 text-xs text-gray-300">{{ optional($incident->created_at)->format('d/m/Y H:i') }}</td>
                    <td class="p-3 text-xs text-gray-300">
                        <span class="font-bold text-white block">{{ $incident->turn_number ?? 'N/A' }}</span>
                        <span class="text-gray-400">{{ $incident->client_name ?? optional($incident->customer)->full_name ?? 'N/A' }}</span>
                    </td>
                    <td class="p-3 text-xs text-gray-300">{{ optional($incident->employee)->full_name ?? 'Desconocido' }}</td>
                    <td class="p-3 text-xs text-gray-300">
                        <span class="font-bold text-yellow-400 block">{{ $incident->reason }}</span>
                        <span class="text-gray-400">{{ $incident->details ?? '-' }}</span>
                    </td>
                    <td class="p-3 text-xs text-gray-300 text-center font-bold">{{ $prorrogas }}</td>
                    <td class="p-3 text-xs text-gray-300 text-center font-mono">{{ $tiempoAtendido }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="p-4 text-sm text-gray-400 text-center">No se encontraron incidencias en este periodo.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 bg-gray-900 border-t border-gray-800">
        {{ $detailedIncidents->links() }}
    </div>
</div>
