@forelse($sellers as $seller)
@php
$shift = $seller->todayShift;
$status = $shift->current_status ?? 'OFFLINE';
$isOnline = $status === 'ONLINE';
$isOnBreak = $status === 'BREAK';
$isRating = $status === 'RATING';

$currentClient = null;
if ($shift) {
$currentClient = App\Models\SalesQueue::where('assigned_shift_id', $shift->id)
->where('status', 'SERVING')
->first();
}
$isServing = !is_null($currentClient);

// Clases dinámicas respetando tu diseño original pero agregando el VIP
$cardClasses = 'bg-gray-800/50 border-gray-700 opacity-60 grayscale';
if ($isServing) {
if ($currentClient->client_type === 'VIP') {
$cardClasses = 'bg-gray-900 border-yellow-500 shadow-[0_0_20px_rgba(234,179,8,0.4)] transform scale-[1.02] z-10 ring-2 ring-yellow-500/30';
} else {
$cardClasses = 'bg-aromas-secondary border-blue-500/50 shadow-[0_0_20px_rgba(59,130,246,0.3)] transform scale-[1.02] z-10 ring-2 ring-blue-500/20';
}
} elseif ($isOnBreak) {
    $cardClasses = 'bg-gray-800 border-yellow-500/50 opacity-90';
} elseif ($isRating) {
    $cardClasses = 'bg-gray-900 border-purple-500/50 shadow-[0_0_20px_rgba(168,85,247,0.3)] transform scale-[1.02] z-10 ring-2 ring-purple-500/20';
} elseif ($isOnline) {
$cardClasses = 'bg-aromas-secondary border-aromas-highlight/50 shadow-[0_0_15px_rgba(253,201,116,0.15)]';
}

// --- CÁLCULO DE TIEMPO PARA LOS CRONÓMETROS ---
$serveStartTime = '';
if ($isServing) {
// Ajustado para leer started_serving_at
$serveStartTime = $currentClient->started_serving_at ? $currentClient->started_serving_at->timestamp * 1000 : now()->timestamp * 1000;
}

$breakStartTime = '';
if ($isOnBreak && $shift->last_status_change_at) {
$breakStartTime = $shift->last_status_change_at->timestamp * 1000;
}
@endphp

<div class="seller-card relative rounded-2xl border transition-all duration-500 {{ $cardClasses }} flex flex-col h-full overflow-hidden group animate-fade-in"
    @if($isServing)
    data-serving="true"
    data-shift-id="{{ $shift->id }}"
    data-start-time="{{ $serveStartTime }}"
    @endif
    @if($isOnBreak)
    data-on-break="true"
    data-break-start-time="{{ $breakStartTime }}"
    @endif
    @if($isOnline)
    data-online="true"
    data-last-action-at="{{ $shift->last_action_at ? $shift->last_action_at->timestamp * 1000 : 0 }}"
    @endif>


    {{-- Indicador de Estado (Punto titilante) --}}
    <div class="absolute top-4 right-4 flex items-center gap-2 z-20">
        @if($isServing)
        @if($currentClient->client_type === 'VIP')
        <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-yellow-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
        @else
        <span class="animate-ping absolute inline-flex h-3 w-3 rounded-full bg-blue-400 opacity-75"></span>
        <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
        @endif
        @elseif($isOnline)
        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
        @elseif($isOnBreak)
        <span class="relative inline-flex rounded-full h-3 w-3 bg-yellow-500"></span>
        @else
        <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
        @endif
    </div>

    <div class="p-6 text-center flex-1 flex flex-col">
        {{-- Avatar Original --}}
        <div class="w-16 h-16 mx-auto bg-black/30 rounded-full flex items-center justify-center border-2 {{ $isServing ? ($currentClient->client_type === 'VIP' ? 'border-yellow-500 text-yellow-400' : 'border-blue-500 text-blue-400') : ($isOnline ? 'border-aromas-highlight text-aromas-highlight' : 'border-gray-600 text-gray-500') }} mb-3 transition-colors duration-500">
            <span class="text-xl font-black">{{ substr($seller->full_name, 0, 2) }}</span>
        </div>

        {{-- Info Vendedor --}}
        <h3 class="text-lg font-bold text-white mb-0 leading-tight truncate">{{ $seller->full_name }}</h3>
        <p class="text-[10px] uppercase tracking-widest font-bold {{ $isServing ? ($currentClient->client_type === 'VIP' ? 'text-yellow-400' : 'text-blue-400') : ($isOnline ? 'text-aromas-tertiary' : ($isRating ? 'text-purple-400' : 'text-gray-600')) }} mb-4">
            @if($isServing) Atendiendo @elseif($isOnBreak) En Pausa ({{ $shift->break_reason }}) @elseif($isRating) Calificando @elseif($isOnline) Disponible @else Inactivo @endif
        </p>

        {{-- ZONA CENTRAL ORIGINAL --}}
        <div class="flex-1 flex flex-col justify-center min-h-[80px]">
            @if($isServing)
            {{-- Tarjeta Cliente con Cronómetro de Atención --}}
            <div class="border rounded-lg p-4 animate-fade-in-up {{ $currentClient->client_type === 'VIP' ? 'bg-yellow-500/10 border-yellow-500/20' : 'bg-blue-500/10 border-blue-500/20' }}">

                {{-- ENCABEZADO: Turno y Badges --}}
                <div class="flex justify-between items-start mb-1 gap-2">
                    <span class="text-[10px] uppercase tracking-wider font-bold {{ $currentClient->client_type === 'VIP' ? 'text-yellow-300' : 'text-blue-300' }}">
                        Turno: {{ $currentClient->turn_number }}
                    </span>
                    <div class="flex gap-1 flex-wrap justify-end">
                        @if($currentClient->client_type === 'VIP')
                        <span class="bg-yellow-500 text-yellow-900 text-[9px] px-1.5 py-0.5 rounded font-black uppercase tracking-wider">VIP</span>
                        @endif
                        @if($currentClient->has_disability)
                        <span class="bg-blue-500 text-white text-[9px] px-1.5 py-0.5 rounded font-black uppercase tracking-wider">PRIORIDAD</span>
                        @endif
                    </div>
                </div>

                {{-- NOMBRE CLIENTE --}}
                <div class="text-xl font-black text-white leading-tight break-words line-clamp-2" title="{{ $currentClient->client_name }}">
                    {{ $currentClient->client_name }}
                </div>

                {{-- CRONÓMETRO --}}
                <div class="mt-4 bg-black/30 border rounded py-2 px-3 {{ $currentClient->client_type === 'VIP' ? 'border-yellow-500/20' : 'border-blue-500/20' }}">
                    <span class="text-[9px] text-gray-400 uppercase tracking-widest block mb-1">Tiempo de Atención</span>
                    <span class="seller-timer text-xl font-mono font-bold tracking-wider {{ $currentClient->client_type === 'VIP' ? 'text-yellow-300' : 'text-blue-300' }}">00:00</span>
                </div>
            </div>
            @elseif($isOnBreak)
            {{-- Tarjeta de Cronómetro de Pausa --}}
            <div class="bg-yellow-500/10 border border-yellow-500/20 rounded-lg p-4 animate-fade-in-up">
                <span class="text-[10px] text-yellow-300 uppercase block mb-1 tracking-wider">Tiempo en Pausa</span>
                <div class="mt-2 bg-black/30 border border-yellow-500/20 rounded py-2 px-3">
                    <span class="break-timer text-2xl font-mono font-bold text-yellow-300 tracking-wider">--:--</span>
                </div>
            </div>
            @elseif($isOnline)
            {{-- Contenedor del cronómetro de Delay (Oculto por defecto) --}}
            <div class="space-y-1 delay-container" style="display: none;">
                <span class="text-[10px] text-aromas-highlight uppercase block mb-1 tracking-wider">Asignación en</span>
                <div class="bg-black/30 border border-aromas-highlight/20 rounded py-1 px-3 inline-block">
                    <span class="delay-timer text-lg font-mono font-bold text-aromas-highlight tracking-wider">--</span>
                </div>
            </div>
            {{-- Puntitos normales --}}
            <div class="space-y-1 online-dots">
                <div class="text-xs text-gray-500 uppercase tracking-widest font-bold opacity-50">. . .</div>
            </div>
            @endif
        </div>

        {{-- Botones (Originales) --}}
        <div class="mt-4 pt-4 border-t border-white/5">
            @if($isServing)
                {{-- NUEVO: Botón AJAX para terminar venta sin recargar la página --}}
                <button type="button" @click="$dispatch('finish-service', { shift_id: {{ $shift->id }}, queue_id: {{ $currentClient->id }} })" class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-sm shadow-lg transition-transform active:scale-95">
                    Terminar Venta
                </button>
                
            @elseif($isRating)
                {{-- NUEVO: Botón AJAX para abrir el modal de calificación --}}
                @php 
                    $lastClient = App\Models\SalesQueue::where('assigned_shift_id', $shift->id)->where('status', 'COMPLETED')->latest('completed_at')->first(); 
                @endphp
                <button type="button" @click="$dispatch('open-rating-modal', { shift_id: {{ $shift->id }}, queue_id: {{ $lastClient->id ?? 0 }} })" class="w-full py-3 rounded-xl bg-purple-600 hover:bg-purple-500 text-white font-bold text-sm shadow-lg animate-pulse">
                    Calificar al Cliente
                </button>
                
            @elseif($isOnline || $isOnBreak)
                {{-- TUS BOTONES ORIGINALES DE PAUSA (Intactos) --}}
                @if($isOnBreak)
                    <form action="{{ route('ventas.toggle-break') }}" method="POST">
                        @csrf
                        <input type="hidden" name="shift_id" value="{{ $shift->id }}">
                        <button class="w-full py-2 bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 rounded-lg text-xs font-bold hover:bg-yellow-500/30">
                            Regresar a Activo
                        </button>
                    </form>
                @else
                    <button @click="$dispatch('open-break-modal', { id: {{ $shift->id }}, hasTakenLunch: {{ $shift->has_taken_lunch ? 'true' : 'false' }} })"
                        class="w-full py-2 bg-gray-700 text-gray-300 rounded-lg text-xs font-bold hover:bg-gray-600 flex items-center justify-center gap-2">
                        Pausar Turno
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