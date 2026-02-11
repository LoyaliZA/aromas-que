<div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden flex flex-col">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                    <th class="px-6 py-3 font-semibold">Folio</th>
                    <th class="px-6 py-3 font-semibold">Cliente</th>
                    <th class="px-6 py-3 font-semibold text-center">Área</th> 
                    <th class="px-6 py-3 font-semibold text-center">Piezas</th>
                    <th class="px-6 py-3 font-semibold">Recibido Por</th> {{-- NUEVA COLUMNA --}}
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold text-right">Fecha</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                @forelse($pickups as $pickup)
                    <tr class="hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-3 font-mono text-aromas-highlight font-medium">
                            {{ $pickup->ticket_folio }}
                        </td>
                        <td class="px-6 py-3">
                            <div class="font-bold text-white">{{ $pickup->client_name }}</div>
                            <span class="text-xs text-gray-500">ID: {{ $pickup->client_ref_id }}</span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($pickup->department === 'AROMAS')
                                <span class="px-2 py-1 bg-purple-900/40 text-purple-300 rounded text-xs border border-purple-500/20">Aromas</span>
                            @else
                                <span class="px-2 py-1 bg-pink-900/40 text-pink-300 rounded text-xs border border-pink-500/20">Bellaroma</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center text-white font-bold">
                            {{ $pickup->pieces }}
                        </td>
                        
                        {{-- NUEVA COLUMNA: DATOS DE QUIEN RECIBE --}}
                        <td class="px-6 py-3">
                            @if($pickup->status === 'DELIVERED')
                                @if($pickup->is_third_party)
                                    <div class="text-yellow-400 text-xs font-bold uppercase mb-0.5">Tercero:</div>
                                    <div class="text-white">{{ $pickup->receiver_name }}</div>
                                @else
                                    <div class="text-gray-400 text-xs uppercase mb-0.5">Titular:</div>
                                    <div class="text-white">{{ $pickup->receiver_name ?? $pickup->client_name }}</div>
                                @endif
                            @else
                                <span class="text-gray-600">---</span>
                            @endif
                        </td>

                        <td class="px-6 py-3">
                            @if($pickup->status === 'IN_CUSTODY')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-500 border border-yellow-500/20">
                                    En Custodia
                                </span>
                            @else
                                <div class="flex flex-col">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">
                                        Entregado
                                    </span>
                                    <span class="text-[10px] text-gray-500 mt-1">
                                        {{ $pickup->delivered_at ? $pickup->delivered_at->format('d/m H:i') : '' }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right text-aromas-tertiary">
                            {{ $pickup->created_at->format('d/m/Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-aromas-tertiary">
                            <p>No se encontraron resultados.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- PAGINACIÓN QUE MANTIENE AJAX --}}
    <div class="px-6 py-3 border-t border-aromas-tertiary/10 bg-black/10 text-xs"
         @click.prevent="if($event.target.tagName === 'A') { fetchResults($event.target.href) }">
        {{ $pickups->links() }} 
    </div>
</div>