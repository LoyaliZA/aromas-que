<div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden flex flex-col mt-6">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                    <th class="px-6 py-3 font-semibold">Folio</th>
                    <th class="px-6 py-3 font-semibold">Cliente</th>
                    <th class="px-6 py-3 font-semibold text-center">Área</th>
                    <th class="px-6 py-3 font-semibold text-center">Piezas</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold text-right">Recibido</th>
                    <th class="px-6 py-3 font-semibold text-right">Entregado</th>
                    <th class="px-6 py-3 font-semibold text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                @forelse($todaysPickups as $pickup)
                    <tr class="hover:bg-white/5 transition-colors group">
                        <td class="px-6 py-3 font-mono text-aromas-highlight font-medium">
                            {{ $pickup->ticket_folio }}
                        </td>
                        <td class="px-6 py-3">
                            <div class="font-bold text-white">{{ $pickup->client_name }}</div>
                            <span class="text-xs text-aromas-tertiary">ID: {{ $pickup->client_ref_id }}</span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($pickup->department === 'AROMAS')
                                <span class="inline-block px-2 py-1 rounded bg-purple-900/30 border border-purple-500/30 text-purple-300 text-[10px] font-bold uppercase tracking-wide">Aromas</span>
                            @else
                                <span class="inline-block px-2 py-1 rounded bg-pink-900/30 border border-pink-500/30 text-pink-300 text-[10px] font-bold uppercase tracking-wide">Bellaroma</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center text-white font-bold">
                            {{ $pickup->pieces }}
                        </td>
                        <td class="px-6 py-3">
                            @if($pickup->status === 'IN_CUSTODY')
                                <span class="text-xs text-yellow-500 bg-yellow-500/10 px-2 py-1 rounded border border-yellow-500/20">En Custodia</span>
                            @else
                                <span class="text-xs text-green-500 bg-green-500/10 px-2 py-1 rounded border border-green-500/20">Entregado</span>
                            @endif
                        </td>
                        
                        <td class="px-6 py-3 text-right text-gray-300 font-mono text-xs">
                            {{ $pickup->created_at->format('H:i:s') }}
                        </td>
                        
                        <td class="px-6 py-3 text-right text-gray-300 font-mono text-xs">
                            @if($pickup->delivered_at)
                                {{ $pickup->delivered_at->format('H:i:s') }}
                            @else
                                <span class="text-gray-600">--:--:--</span>
                            @endif
                        </td>

                        {{-- BOTÓN EDITAR --}}
                        <td class="px-6 py-3 text-center">
                            <button @click="openEditModal({ 
                                        id: {{ $pickup->id }}, 
                                        ticket_folio: '{{ $pickup->ticket_folio }}',
                                        client_name: '{{ $pickup->client_name }}',
                                        department: '{{ $pickup->department }}',
                                        pieces: {{ $pickup->pieces }}
                                    })"
                                    class="text-gray-500 hover:text-white hover:bg-gray-700 p-2 rounded transition-colors group-hover:text-gray-300" title="Editar / Corregir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-aromas-tertiary">
                            No se encontraron registros.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>