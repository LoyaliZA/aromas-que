@forelse($sellers as $seller)
    @php
        $shift = $seller->todayShift;
        $status = $shift->current_status ?? 'OFFLINE';
        $isOnline = $status === 'ONLINE';
        $isOnBreak = $status === 'BREAK';
        
        $currentClient = null;
        if ($shift) {
            $currentClient = App\Models\SalesQueue::where('assigned_shift_id', $shift->id)
                                                  ->where('status', 'SERVING')
                                                  ->first();
        }
        $isServing = !is_null($currentClient);

        // Clases dinámicas
        $cardClasses = 'bg-gray-800/50 border-gray-700 opacity-60 grayscale'; 
        if ($isServing) {
            $cardClasses = 'bg-aromas-secondary border-blue-500/50 shadow-[0_0_20px_rgba(59,130,246,0.3)] transform scale-[1.02] z-10 ring-2 ring-blue-500/20';
        } elseif ($isOnBreak) {
            $cardClasses = 'bg-gray-800 border-yellow-500/50 opacity-90';
        } elseif ($isOnline) {
            $cardClasses = 'bg-aromas-secondary border-aromas-highlight/50 shadow-[0_0_15px_rgba(253,201,116,0.15)]';
        }
    @endphp

    <div class="relative rounded-2xl border transition-all duration-500 {{ $cardClasses }} flex flex-col h-full overflow-hidden group animate-fade-in">
        
        {{-- Indicador de Estado --}}
        <div class="absolute top-4 right-4 flex items-center gap-2 z-20">
            @if($isServing)
                <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-blue-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
            @elseif($isOnline)
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            @elseif($isOnBreak)
                <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
            @else
                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
            @endif
        </div>

        <div class="p-6 text-center flex-1 flex flex-col">
            {{-- Avatar --}}
            <div class="w-16 h-16 mx-auto bg-black/30 rounded-full flex items-center justify-center border-2 {{ $isServing ? 'border-blue-500 text-blue-400' : ($isOnline ? 'border-aromas-highlight text-aromas-highlight' : 'border-gray-600 text-gray-500') }} mb-3 transition-colors duration-500">
                <span class="text-xl font-black">{{ substr($seller->full_name, 0, 2) }}</span>
            </div>

            {{-- Info Vendedor --}}
            <h3 class="text-lg font-bold text-white mb-0 leading-tight truncate">{{ $seller->full_name }}</h3>
            <p class="text-[10px] uppercase tracking-widest font-bold {{ $isServing ? 'text-blue-400' : ($isOnline ? 'text-aromas-tertiary' : 'text-gray-600') }} mb-4">
                @if($isServing) Atendiendo @elseif($isOnBreak) En Pausa ({{ $shift->break_reason }}) @elseif($isOnline) Disponible @else Inactivo @endif
            </p>

            {{-- ZONA CENTRAL --}}
            <div class="flex-1 flex flex-col justify-center min-h-[80px]">
                @if($isServing)
                    {{-- Tarjeta Cliente (LIMPIA: Sin source) --}}
                    <div class="bg-blue-500/10 border border-blue-500/20 rounded-lg p-4 animate-fade-in-up">
                        <span class="text-[10px] text-blue-300 uppercase block mb-1 tracking-wider">Cliente</span>
                        <div class="text-2xl font-black text-white leading-none">{{ $currentClient->client_name }}</div>
                    </div>
                @elseif($isOnline)
                    <div class="space-y-1">
                        <div class="text-3xl text-gray-700 opacity-20 group-hover:opacity-40 transition-opacity">⏳</div>
                    </div>
                @endif
            </div>

            {{-- Botones --}}
            <div class="mt-4 pt-4 border-t border-white/5">
                @if($isServing)
                    <form action="{{ route('ventas.finish-service') }}" method="POST">
                        @csrf
                        <input type="hidden" name="shift_id" value="{{ $shift->id }}">
                        <button type="submit" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg transition-transform active:scale-95">
                            Terminar Venta
                        </button>
                    </form>
                @elseif($isOnline || $isOnBreak)
                    @if($isOnBreak)
                        <form action="{{ route('ventas.toggle-break') }}" method="POST">
                            @csrf
                            <input type="hidden" name="shift_id" value="{{ $shift->id }}">
                            <button class="w-full py-2 bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 rounded-lg text-xs font-bold hover:bg-yellow-500/30">
                                ▶ Regresar
                            </button>
                        </form>
                    @else
                        {{-- Botón Break (Abre Modal) --}}
                        <button @click="$dispatch('open-break-modal', { id: {{ $shift->id }} })" 
                                class="w-full py-2 bg-gray-700 text-gray-300 rounded-lg text-xs font-bold hover:bg-gray-600 flex items-center justify-center gap-2">
                            <span>⏸</span> Pausar Turno
                        </button>
                    @endif
                @else
                    <span class="text-xs text-gray-600 italic">Esperando activación...</span>
                @endif
            </div>
        </div>
    </div>
@empty
    <div class="col-span-full text-center py-10 opacity-50">
        <p class="text-gray-400">No hay vendedores configurados.</p>
    </div>
@endforelse