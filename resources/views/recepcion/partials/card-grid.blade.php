<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
    @forelse($pickups as $pickup)
        <div class="bg-aromas-secondary rounded-xl border border-aromas-tertiary/20 shadow-lg flex flex-col justify-between group hover:border-aromas-highlight/50 transition-all relative overflow-hidden">
            
            {{-- CABECERA CARD --}}
            <div class="p-5 pb-0">
                <div class="flex justify-between items-start mb-2">
                    <span class="font-mono text-2xl font-bold text-aromas-highlight tracking-wider">{{ $pickup->ticket_folio }}</span>
                    
                    {{-- Etiqueta de Depto (Badge Visible) --}}
                    @if($pickup->department === 'AROMAS')
                        <span class="bg-purple-500/20 text-purple-300 border border-purple-500/30 px-2 py-1 rounded text-xs font-bold uppercase tracking-wide">
                            🟣 Aromas
                        </span>
                    @else
                        <span class="bg-pink-500/20 text-pink-300 border border-pink-500/30 px-2 py-1 rounded text-xs font-bold uppercase tracking-wide">
                            🌸 Bellaroma
                        </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 mb-4">{{ $pickup->created_at->format('d/M h:i A') }}</div>
            </div>

            {{-- CUERPO CARD --}}
            <div class="px-5 flex-1">
                {{-- Quién Recoge (Destacado) --}}
                <div class="mb-4 p-3 rounded-lg bg-black/20 border border-aromas-tertiary/10">
                    @if($pickup->is_third_party)
                        <div class="text-[10px] text-yellow-500 uppercase font-bold tracking-wider mb-1 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Recoge Tercero
                        </div>
                        <div class="text-lg text-white font-bold leading-tight">{{ $pickup->receiver_name }}</div>
                        <div class="text-xs text-gray-400 mt-1">Titular: {{ $pickup->client_name }}</div>
                    @else
                        <div class="text-[10px] text-gray-400 uppercase tracking-wider mb-1">Cliente Titular</div>
                        <div class="text-lg text-white font-bold leading-tight">{{ $pickup->client_name }}</div>
                        <div class="text-xs text-gray-500 mt-1">ID: {{ $pickup->client_ref_id }}</div>
                    @endif
                </div>

                {{-- Notas --}}
                @if($pickup->notes)
                    <div class="flex gap-2 items-start mb-3">
                        <svg class="w-4 h-4 text-yellow-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        <p class="text-xs text-yellow-200/80 italic leading-snug">{{ $pickup->notes }}</p>
                    </div>
                @endif
                
                <div class="text-xs text-gray-400 mb-4 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    {{ $pickup->pieces }} Piezas
                </div>
            </div>

            {{-- BOTÓN ACCIÓN --}}
            @if($pickup->status === 'IN_CUSTODY')
                <button @click="openDeliveryModal({{ $pickup }})" 
                        class="w-full bg-aromas-tertiary/10 hover:bg-aromas-highlight hover:text-aromas-main text-gray-300 hover:font-bold py-4 transition-all border-t border-aromas-tertiary/20 flex items-center justify-center gap-2 group-hover:shadow-[0_-4px_20px_rgba(253,201,116,0.1)]">
                    <span>ENTREGAR</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                </button>
            @else
                <div class="w-full bg-green-900/20 text-green-500 font-bold py-3 text-center border-t border-green-500/20 text-xs tracking-widest uppercase">
                    Entregado
                </div>
            @endif
        </div>
    @empty
        <div class="col-span-full py-12 text-center">
            <div class="inline-block p-4 rounded-full bg-white/5 mb-3">
                <svg class="w-12 h-12 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <p class="text-xl text-gray-400 font-bold">Sin resultados</p>
        </div>
    @endforelse

    {{-- Paginación AJAX (Opcional si usas librerias de paginación ajax, o estándar) --}}
    @if($pickups->hasPages())
        <div class="col-span-full pt-4">
            {{ $pickups->links() }}
        </div>
    @endif
</div>