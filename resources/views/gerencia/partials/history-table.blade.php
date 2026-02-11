<div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden flex flex-col">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                    <th class="px-6 py-3 font-semibold">Folio</th>
                    <th class="px-6 py-3 font-semibold">Cliente</th>
                    {{-- NUEVA COLUMNA SEPARADA --}}
                    <th class="px-6 py-3 font-semibold text-center">Área</th> 
                    <th class="px-6 py-3 font-semibold text-center">Piezas</th>
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
                            <span class="text-xs text-aromas-tertiary">ID: {{ $pickup->client_ref_id }}</span>
                        </td>
                        {{-- ÁREA SEPARADA Y LIMPIA --}}
                        <td class="px-6 py-3 text-center">
                            @if($pickup->department === 'AROMAS')
                                <span class="inline-block px-2 py-1 rounded bg-purple-900/30 border border-purple-500/30 text-purple-300 text-[10px] font-bold uppercase tracking-wide">
                                    Aromas
                                </span>
                            @else
                                <span class="inline-block px-2 py-1 rounded bg-pink-900/30 border border-pink-500/30 text-pink-300 text-[10px] font-bold uppercase tracking-wide">
                                    Bellaroma
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="text-white font-bold">{{ $pickup->pieces }}</span>
                        </td>
                        <td class="px-6 py-3">
                            @if($pickup->status === 'IN_CUSTODY')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-500/10 text-yellow-500 border border-yellow-500/20">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span>
                                    En Resguardo
                                </span>
                            @else
                                <div class="flex flex-col">
                                    <span class="inline-flex w-max items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">
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
                        <td colspan="6" class="px-6 py-12 text-center text-aromas-tertiary">
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
         {{-- El click.prevent intercepta los clicks en la paginación para que no recarguen la página --}}
        {{ $pickups->links() }} 
    </div>
</div>