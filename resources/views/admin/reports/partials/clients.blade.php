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
                    <th class="p-4">Turno / Cliente</th>
                    <th class="p-4">Vendedor</th>
                    <th class="p-4 text-center">Tiempos</th>
                    <th class="p-4">Auditoría / Calificaciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($detailedClients as $client)
                <tr class="hover:bg-gray-800/50">
                    <td class="p-4">
                        <span class="font-mono font-bold text-white block">{{ $client->turn_number }}</span>
                        <span class="text-sm text-gray-300">{{ $client->client_name }}</span>
                    </td>
                    <td class="p-4 text-sm text-gray-300">{{ $client->assignedShift->employee->full_name ?? 'N/A' }}</td>
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
                @empty 
                <tr><td colspan="4" class="p-8 text-center text-gray-500">No hay registros.</td></tr> 
                @endforelse
            </tbody>
        </table>
    </div>
    {{-- AQUI ESTÁ LA MAGIA PARA QUE NO RECARGUE Y PIERDA LA PESTAÑA --}}
    <div class="p-4 border-t border-gray-700 bg-gray-900">{{ $detailedClients->appends(['tab' => 'clients'])->links() }}</div>
</div>