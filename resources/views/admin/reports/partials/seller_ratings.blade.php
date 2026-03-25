<div class="flex justify-between items-center mb-6 flex-wrap gap-4">
    <h3 class="text-2xl font-black text-white uppercase tracking-widest">Opiniones de Clientes</h3>
    
    {{-- Filtro de orden --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2">
        <input type="hidden" name="tab" value="client_ratings">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="hidden" name="start_date" value="{{ $start_date }}">
        <input type="hidden" name="end_date" value="{{ $end_date }}">
        <select name="sort" onchange="this.form.submit()" class="bg-gray-900 text-white border border-gray-700 rounded-lg px-3 py-1.5 text-sm">
            <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Mejores Evaluados Primero</option>
            <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Peores Evaluados Primero</option>
        </select>
    </form>
</div>

<div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-800/50 border-b border-gray-700">
                <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                    <th class="p-4">Estrellas / Fecha</th>
                    <th class="p-4">Turno (Cliente)</th>
                    <th class="p-4">Atendió</th>
                    <th class="p-4 w-1/2">Comentarios Completos y Etiquetas</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @if($sellerRatings)
                    @forelse($sellerRatings as $rating)
                    <tr class="hover:bg-gray-800/50">
                        <td class="p-4">
                            <div class="text-lg font-black {{ $rating->stars >= 4 ? 'text-green-400' : ($rating->stars == 3 ? 'text-yellow-400' : 'text-red-400') }}">
                                {{ $rating->stars }} ⭐
                            </div>
                            <span class="text-[10px] text-gray-500">{{ $rating->created_at->format('d/m/y H:i') }}</span>
                        </td>
                        <td class="p-4">
                            <span class="font-mono font-bold text-white block">{{ $rating->salesQueue->turn_number ?? 'N/A' }}</span>
                            <span class="text-xs text-gray-400">{{ $rating->salesQueue->client_name ?? 'N/A' }}</span>
                        </td>
                        <td class="p-4 text-sm text-gray-300">{{ $rating->salesQueue->assignedShift->employee->full_name ?? 'Desconocido' }}</td>
                        <td class="p-4">
                            <div class="flex flex-wrap gap-1 mb-2">
                                @foreach($rating->tags ?? [] as $tag)
                                    <span class="bg-blue-900/40 text-blue-400 border border-blue-500/30 px-2 py-0.5 rounded text-[10px] uppercase font-bold">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <p class="text-sm text-gray-300 italic">"{{ $rating->comments ?: 'Sin comentario adicional.' }}"</p>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="p-8 text-center text-gray-500">No hay opiniones de clientes en este periodo.</td></tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>
    @if($sellerRatings)
    <div class="p-4 border-t border-gray-700 bg-gray-900">{{ $sellerRatings->appends(request()->except('cr_page'))->links() }}</div>
    @endif
</div>