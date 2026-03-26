<div class="flex justify-between items-center mb-6">
    <div>
        <h3 class="text-2xl font-black text-white uppercase tracking-widest">Expediente de Vendedores</h3>
        <p class="text-xs text-yellow-500 font-bold mt-1">Estadísticas Históricas (No afectadas por filtro de fecha)</p>
    </div>
</div>

<div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-800/50 border-b border-gray-700">
                <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                    <th class="p-4">Código</th>
                    <th class="p-4">Vendedor</th>
                    <th class="p-4 text-center">Calificación General</th>
                    <th class="p-4 text-right">Auditoría</th>
                </tr>
            </thead>
            @if($sellersDirectory)
                @foreach($sellersDirectory as $seller)
                {{-- ACORDEÓN ALPINE PARA CADA VENDEDOR --}}
                <tbody x-data="{ expanded: false }" class="divide-y divide-gray-800 border-b border-gray-700/50">
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <td class="p-4 font-mono text-gray-400">{{ $seller->employee_code }}</td>
                        <td class="p-4 font-bold text-white">{{ $seller->full_name }}</td>
                        <td class="p-4 text-center">
                            @if($seller->all_time_stars)
                                <div class="text-2xl font-black text-yellow-400">{{ $seller->all_time_stars }} ⭐</div>
                                <div class="text-[10px] text-gray-500">{{ $seller->comments_history->count() }} evaluaciones de clientes</div>
                            @else
                                <span class="text-gray-600 font-bold text-xl">--</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <button @click="expanded = !expanded" class="text-sm font-bold px-4 py-2 rounded-lg transition-colors border"
                                :class="expanded ? 'bg-gray-800 text-white border-gray-600' : 'bg-transparent text-purple-400 border-purple-500/30 hover:bg-purple-500/10'">
                                <span x-text="expanded ? 'Ocultar' : 'Ver Historial'"></span>
                            </button>
                        </td>
                    </tr>
                    
                    {{-- HISTORIAL DESPLEGABLE --}}
                    <tr x-show="expanded" style="display: none;" class="bg-black/40">
                        <td colspan="4" class="p-0">
                            <div class="p-6">
                                <h4 class="text-xs text-gray-500 uppercase tracking-widest font-bold mb-4 border-b border-gray-800 pb-2">Comentarios de Clientes</h4>
                                @if($seller->comments_history->count() > 0)
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        @foreach($seller->comments_history as $history)
                                            <div class="bg-gray-900 border border-gray-700 rounded-xl p-4 relative">
                                                <div class="absolute top-4 right-4 text-yellow-400 font-black">{{ $history->stars }} ⭐</div>
                                                <p class="text-[10px] text-gray-500 mb-2">{{ $history->created_at->format('d M, Y - H:i') }} | Turno: {{ $history->salesQueue->turn_number ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-300 font-bold mb-2">Cliente: {{ $history->salesQueue->customer->name ?? $history->salesQueue->client_name ?? 'Desconocido' }}</p>
                                                
                                                <div class="flex flex-wrap gap-1 mb-3">
                                                    @foreach($history->tags ?? [] as $tag)
                                                        <span class="bg-purple-900/40 text-purple-400 px-2 py-0.5 rounded text-[10px] uppercase font-bold">{{ $tag }}</span>
                                                    @endforeach
                                                </div>
                                                <p class="text-sm text-gray-400 italic">"{{ $history->comments ?: 'Sin comentario adicional.' }}"</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 italic">Este vendedor aún no ha sido evaluado por los clientes en la tablet.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                </tbody>
                @endforeach
            @else
                <tbody><tr><td colspan="4" class="p-8 text-center text-gray-500">No hay vendedores configurados.</td></tr></tbody>
            @endif
        </table>
    </div>
</div>