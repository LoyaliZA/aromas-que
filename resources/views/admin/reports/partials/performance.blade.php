<div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl font-black text-white uppercase tracking-widest">Rendimiento por Vendedor</h3>
    @if($empData)
    <div class="flex gap-2">
        <a href="{{ route('admin.reports.export', ['type' => 'employee', 'employee_id' => $selectedEmployeeId, 'format' => 'csv', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-gray-800 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm border border-gray-600 transition flex items-center gap-2">CSV</a>
        <a href="{{ route('admin.reports.export', ['type' => 'employee', 'employee_id' => $selectedEmployeeId, 'format' => 'pdf', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}" class="bg-aromas-highlight hover:bg-yellow-500 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm shadow-md transition flex items-center gap-2">PDF</a>
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
{{-- KPIs Empleado (CON NUEVO PROMEDIO ESTRELLAS) --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Calificación</p><p class="text-3xl font-black text-yellow-400"><svg class="w-6 h-6 inline pb-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $empData['kpis']['avg_stars'] }}</p></div>
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Atendidos</p><p class="text-3xl font-black text-green-400">{{ $empData['kpis']['served'] }}</p></div>
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Prom. Atención</p><p class="text-3xl font-black text-blue-400">{{ $empData['kpis']['avg_time'] }}</p></div>
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Tiempo Libre</p><p class="text-3xl font-black text-aromas-highlight">{{ $empData['kpis']['total_available'] }}</p></div>
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">En Pausa</p><p class="text-3xl font-black text-yellow-500">{{ $empData['kpis']['total_break'] }}</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Desglose de Pausas por Día --}}
    <div class="bg-gray-900 rounded-xl border border-gray-700 p-5 flex flex-col h-[260px]">
        <div class="border-b border-gray-800 pb-2 mb-3">
            <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Desglose (Día)</h3>
        </div>
        <div class="overflow-y-auto flex-1 custom-scrollbar space-y-4">
            @forelse($empData['daily_breaks'] as $date => $breaks)
                <div>
                    <h4 class="text-xs font-black text-white mb-2 bg-gray-800/80 border border-gray-700 p-1.5 rounded">{{ \Carbon\Carbon::parse($date)->format('d / m / Y') }}</h4>
                    <div class="space-y-1">
                        @foreach($breaks as $reasonName => $time)
                            <div class="flex justify-between px-2 py-1 border-b border-gray-800/50">
                                <span class="text-[11px] text-gray-400">{{ $reasonName }}</span>
                                <span class="text-[11px] font-mono {{ $reasonName === 'Tiempo Disponible' ? 'text-aromas-highlight' : 'text-yellow-500' }}">{{ $time }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty <p class="text-sm text-gray-500 text-center">No hay datos.</p> @endforelse
        </div>
    </div>

    {{-- Bitácora --}}
    <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-700 h-[260px] flex flex-col">
        <div class="sticky top-0 bg-gray-900 p-4 border-b border-gray-800">
            <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Línea de Tiempo</h3>
        </div>
        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar">
            <ul class="space-y-3">
                @forelse($empData['timeline'] as $log)
                <li class="flex gap-4 text-sm items-center border-l-2 pl-3 {{ $log['color'] === 'text-yellow-500' ? 'border-yellow-500' : 'border-green-400' }}">
                    <span class="text-gray-500 font-mono w-24">{{ $log['date'] }} <br><span class="text-xs">{{ $log['time'] }}</span></span>
                    <span class="flex-1 font-bold {{ $log['color'] }}">Cambio a: {{ $log['status'] }}</span>
                </li>
                @empty <li class="text-gray-500 italic text-center py-4">Sin actividad.</li> @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- Tabla Empleado con Auditoría --}}
<div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-800/50 border-b border-gray-700">
                <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                    <th class="p-4">Turno/Cliente</th>
                    <th class="p-4 text-center">Tiempos</th>
                    <th class="p-4">Auditoría / Calificaciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($empData['clients'] as $client)
                <tr class="hover:bg-gray-800/50">
                    <td class="p-4">
                        <span class="font-mono font-bold text-white block">{{ $client->turn_number }}</span>
                        <span class="text-sm text-gray-300 block mb-1">{{ $client->client_name }}</span>
                        @if($client->client_type === 'VIP') <span class="bg-yellow-500/20 text-yellow-400 text-[9px] px-1.5 py-0.5 rounded border border-yellow-500/30 uppercase font-bold">VIP</span>
                        @elseif($client->has_disability) <span class="bg-blue-500/20 text-blue-400 text-[9px] px-1.5 py-0.5 rounded border border-blue-500/30 uppercase font-bold">PREF</span>@endif
                    </td>
                    <td class="p-4 text-xs text-center">
                        <div class="text-yellow-400 mb-1">E: {{ $client->formatted_wait }}</div>
                        <div class="text-blue-400">A: {{ $client->formatted_serve }}</div>
                    </td>
                    <td class="p-4 text-xs">
                        @if($client->ratings && $client->ratings->count() > 0)
                            @php
                                $cr = $client->ratings->where('rater_type', 'CLIENT')->first();
                                $sr = $client->ratings->where('rater_type', 'SELLER')->first();
                            @endphp
                            
                            @if($cr)
                            <div class="mb-1 pb-1 border-b border-gray-800 flex items-start gap-2">
                                <span class="bg-blue-900/50 text-blue-400 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase">Cliente</span>
                                <span class="text-yellow-400 font-bold whitespace-nowrap"><svg class="w-3 h-3 inline pb-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $cr->stars }}</span>
                                <span class="text-gray-400 truncate max-w-[150px]" title="{{ $cr->comments }}">{{ implode(', ', $cr->tags ?? []) }}</span>
                            </div>
                            @endif

                            @if($sr)
                            <div class="flex items-start gap-2">
                                <span class="bg-purple-900/50 text-purple-400 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase">Vendedor</span>
                                <span class="text-yellow-400 font-bold whitespace-nowrap"><svg class="w-3 h-3 inline pb-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $sr->stars }}</span>
                                <span class="text-gray-400 truncate max-w-[150px]" title="{{ $sr->comments }}">{{ implode(', ', $sr->tags ?? []) }}</span>
                            </div>
                            @endif
                        @else
                            <span class="text-gray-600 italic">Sin datos</span>
                        @endif
                    </td>
                </tr>
                @empty <tr><td colspan="3" class="p-8 text-center text-gray-500">Sin clientes.</td></tr> @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-700 bg-gray-900">{{ $empData['clients']->appends(['tab' => 'performance', 'employee_id' => $selectedEmployeeId])->links() }}</div>
</div>
@else
<div class="text-center py-20 bg-gray-900 rounded-xl border border-gray-700">
    <p class="text-gray-500">Selecciona un vendedor en la parte superior para ver sus estadísticas.</p>
</div>
@endif