<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
    @forelse($pickups as $pickup)
        <div class="bg-gray-800/80 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl flex flex-col overflow-hidden group hover:border-aromas-highlight/50 transition-colors">
            
            {{-- CABECERA CARD --}}
            <div class="px-5 py-4 border-b border-gray-700 bg-gray-900/50 flex justify-between items-center">
                <div>
                    <span class="text-[10px] text-gray-400 uppercase tracking-widest font-bold block mb-1">Folio del Paquete</span>
                    <span class="font-mono text-2xl font-black text-aromas-highlight leading-none">{{ $pickup->ticket_folio }}</span>
                </div>
                <div class="text-right">
                    @if($pickup->department === 'AROMAS')
                        <span class="bg-purple-500/20 text-purple-300 border border-purple-500/30 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-purple-400"></span> Aromas
                        </span>
                    @else
                        <span class="bg-pink-500/20 text-pink-300 border border-pink-500/30 px-3 py-1.5 rounded-lg text-[10px] font-bold uppercase tracking-wider flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-pink-400"></span> Bellaroma
                        </span>
                    @endif
                </div>
            </div>

            {{-- CUERPO CARD --}}
            <div class="p-5 flex-1 flex flex-col gap-5">
                
                {{-- Info del Cliente --}}
                <div>
                    <span class="text-[10px] text-gray-500 uppercase tracking-widest font-bold flex justify-between">
                        <span>Titular del Paquete</span>
                        <span>{{ $pickup->created_at->format('d/M - h:i A') }}</span>
                    </span>
                    <h3 class="text-xl font-bold text-white leading-tight mt-1">{{ $pickup->client_name }}</h3>
                    
                    @if($pickup->is_third_party)
                        <div class="mt-2 inline-flex items-center gap-2 text-yellow-500 text-sm bg-yellow-500/10 border border-yellow-500/20 px-3 py-1.5 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <span><strong class="font-bold">Recibe:</strong> {{ $pickup->receiver_name }}</span>
                        </div>
                    @endif
                </div>

                {{-- Bloques Destacados (Piezas y No. Cliente) --}}
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-900/60 border border-gray-700 rounded-xl p-3 flex items-center gap-3">
                        <div class="bg-blue-500/20 p-2 rounded-lg text-blue-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                        <div>
                            <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold block">Piezas</span>
                            {{-- Aquí corregimos a $pickup->pieces --}}
                            <span class="text-xl font-black text-white leading-none">{{ $pickup->pieces }}</span>
                        </div>
                    </div>
                    
                    <div class="bg-gray-900/60 border border-gray-700 rounded-xl p-3 flex items-center gap-3">
                        <div class="bg-green-500/20 p-2 rounded-lg text-green-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-[10px] text-gray-500 uppercase tracking-wider font-bold block truncate">No. Cliente</span>
                            <span class="text-lg font-bold text-white leading-none truncate block">{{ $pickup->client_ref_id ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Notas --}}
                <div class="mt-auto flex flex-col gap-3">
                    @if($pickup->notes)
                        <div class="bg-red-500/10 border border-red-500/20 rounded-lg p-3 mt-3">
                            <span class="font-bold text-red-400 uppercase text-[10px] tracking-widest block mb-1">⚠️ Observaciones de Check-in</span>
                            <p class="text-sm text-red-200 leading-snug">{{ $pickup->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- FOOTER / ACCIÓN --}}
            <div class="p-4 pt-0">
                @if($pickup->status === 'IN_CUSTODY')
                    @if(is_null($pickup->received_by_checker_at))
                        {{-- BOTÓN: CONFIRMAR RECEPCIÓN (Bloquea entrega) --}}
                        <button @click="confirmReceipt({{ $pickup->id }})"
                                class="w-full py-4 rounded-xl bg-blue-500 hover:bg-blue-400 text-white font-black tracking-widest uppercase flex items-center justify-center gap-3 transition-all shadow-[0_0_20px_rgba(59,130,246,0.2)] active:scale-95">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Confirmar Recepción
                        </button>
                    @else
                        {{-- BOTÓN: ENTREGAR (Desbloqueado) --}}
                        <button @click="$dispatch('open-delivery-modal', {{ json_encode($pickup) }})"
                                class="w-full py-4 rounded-xl bg-aromas-highlight text-aromas-main font-black tracking-widest uppercase flex items-center justify-center gap-3 hover:bg-white transition-all shadow-[0_0_20px_rgba(253,201,116,0.15)] active:scale-95">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                            Entregar Paquete
                        </button>
                        <div class="text-center mt-2 text-[13px] text-gray-500 font-mono tracking-widest uppercase">
                            Recibido a las: {{ $pickup->received_by_checker_at->format('H:i:s') }}
                        </div>
                    @endif
                @else
                    <div class="w-full bg-gray-900/80 text-green-500 font-bold py-4 rounded-xl text-center border border-green-500/20 text-sm tracking-widest uppercase flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Entregado
                    </div>
                @endif
            </div>
            
        </div>
    @empty
        <div class="col-span-full py-20 flex flex-col items-center justify-center text-center">
            <div class="p-6 rounded-full bg-gray-800/50 mb-4 border border-gray-700">
                <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-400">Sin paquetes en resguardo</h3>
            <p class="text-gray-500 text-sm mt-2">No hay paquetes pendientes de entrega con los filtros actuales.</p>
        </div>
    @endforelse
</div>