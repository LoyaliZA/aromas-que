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
                        {{-- FOLIO --}}
                        <td class="px-6 py-3 font-mono font-medium">
                            <span class="{{ $pickup->created_at->isToday() ? 'text-aromas-highlight' : 'text-orange-400' }}">
                                {{ $pickup->ticket_folio }}
                            </span>
                            @if(!$pickup->created_at->isToday())
                                <span class="block text-[10px] text-orange-400/80 uppercase">Rezagado</span>
                            @endif
                        </td>
                        
                        {{-- CLIENTE --}}
                        <td class="px-6 py-3">
                            <div class="font-bold text-white">{{ $pickup->client_name }}</div>
                            @if($pickup->notes)
                                <div class="text-xs text-yellow-500/80 mt-1 flex items-center">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                                    Nota: {{ Str::limit($pickup->notes, 30) }}
                                </div>
                            @endif
                        </td>

                        {{-- ÁREA --}}
                        <td class="px-6 py-3 text-center">
                            @if($pickup->department === 'AROMAS')
                                <span class="px-2 py-1 bg-purple-900/40 text-purple-300 rounded text-xs border border-purple-500/20">Aromas</span>
                            @else
                                <span class="px-2 py-1 bg-pink-900/40 text-pink-300 rounded text-xs border border-pink-500/20">Bellaroma</span>
                            @endif
                        </td>

                        {{-- PIEZAS --}}
                        <td class="px-6 py-3 text-center text-white font-bold">{{ $pickup->pieces }}</td>

                        {{-- ESTADO --}}
                        <td class="px-6 py-3">
                            @if($pickup->status === 'IN_CUSTODY')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-900/30 text-yellow-500 border border-yellow-500/20 animate-pulse">
                                    <span class="w-1.5 h-1.5 rounded-full bg-yellow-500"></span> En Custodia
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-500 border border-green-500/20">
                                    Entregado
                                </span>
                            @endif
                        </td>

                        {{-- FECHA RECEPCIÓN --}}
                        <td class="px-6 py-3 text-right text-gray-400 text-xs">
                            {{ $pickup->created_at->format('d/m H:i') }}
                        </td>

                        {{-- FECHA ENTREGA --}}
                        <td class="px-6 py-3 text-right text-gray-400 text-xs">
                            @if($pickup->delivered_at)
                                {{ $pickup->delivered_at->format('d/m H:i') }}
                            @else
                                <span class="text-gray-600">---</span>
                            @endif
                        </td>

                        {{-- ACCIONES --}}
                        <td class="px-6 py-3 text-center">
                            @if($pickup->status === 'IN_CUSTODY' && $pickup->created_at->isToday())
                                {{-- BOTÓN EDITAR (Solo si es de HOY y está PENDIENTE) --}}
                                <button @click="openEditModal({ 
                                            id: {{ $pickup->id }}, 
                                            ticket_folio: '{{ $pickup->ticket_folio }}',
                                            client_name: '{{ $pickup->client_name }}',
                                            department: '{{ $pickup->department }}',
                                            pieces: {{ $pickup->pieces }},
                                            notes: '{{ $pickup->notes }}',
                                            is_third_party: {{ $pickup->is_third_party ? 'true' : 'false' }},
                                            receiver_name: '{{ $pickup->receiver_name }}'
                                        })"
                                        class="text-aromas-highlight hover:text-white hover:bg-aromas-highlight/20 p-2 rounded transition-colors" title="Editar / Corregir">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            @elseif($pickup->status === 'IN_CUSTODY')
                                {{-- REZAGADO (Solo Lectura) --}}
                                <span class="text-gray-600 cursor-not-allowed p-2" title="Solo Lectura (Registro de días anteriores)">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                </span>
                            @else
                                {{-- ENTREGADO --}}
                                <span class="text-green-500/50 p-2" title="Ya Entregado">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-aromas-tertiary">
                            No hay resguardos pendientes ni registrados hoy.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>