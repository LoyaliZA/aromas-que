<div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl font-black text-white uppercase tracking-widest">Abandonos y Cancelaciones</h3>
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
                    <th class="p-4">Turno / Cliente</th>
                    <th class="p-4">Llegada</th>
                    <th class="p-4">Tiempo Esperado</th>
                    <th class="p-4">Motivo / Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($detailedAbandoned as $ab)
                <tr class="hover:bg-gray-800/50 transition-colors">
                    <td class="p-4">
                        <span class="font-mono font-bold text-white block">{{ $ab->turn_number }}</span>
                        <span class="text-sm text-gray-300">{{ $ab->client_name }}</span>
                    </td>
                    <td class="p-4 text-sm text-gray-400">{{ \Carbon\Carbon::parse($ab->queued_at)->format('H:i A') }}</td>
                    <td class="p-4 text-sm text-yellow-400 font-mono">
                        @if($ab->abandoned_at)
                            {{ gmdate('H:i:s', \Carbon\Carbon::parse($ab->queued_at)->diffInSeconds(\Carbon\Carbon::parse($ab->abandoned_at))) }}
                        @else
                            --:--
                        @endif
                    </td>
                    <td class="p-4 text-xs">
                        <span class="bg-red-900/50 text-red-400 px-2 py-1 rounded border border-red-500/30 uppercase font-bold">
                            {{ $ab->status === 'CANCELED' ? 'Cancelado por Recepción' : 'Abandonó la Fila' }}
                        </span>
                        @if($ab->abandonmentReason)
                            <span class="block mt-1 text-gray-500">{{ $ab->abandonmentReason->reason }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="p-8 text-center text-gray-500">No hay abandonos registrados en este periodo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- Paginación AJAX --}}
    <div class="p-4 border-t border-gray-700 bg-gray-900">
        {{ $detailedAbandoned->appends(['tab' => 'abandoned'])->links() }}
    </div>
</div>