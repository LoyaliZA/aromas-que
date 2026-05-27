<x-gerencia-layout>
    <div class="mb-8 border-l-4 border-yellow-500 pl-4">
        <div class="flex items-center gap-3 mb-1">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h1 class="text-2xl font-bold text-white tracking-tight">Bóveda de Rezagados</h1>
        </div>
        <p class="text-gray-400 text-sm">Paquetes con más de 15 días en tienda. Todas las entregas en esta sección son auditadas rigurosamente.</p>
    </div>

    <div class="bg-aromas-secondary rounded-xl shadow-2xl border border-yellow-500/20 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black/40 text-yellow-500 border-b border-yellow-500/20 text-xs uppercase tracking-widest font-bold">
                        <th class="p-4">Folio / Ref</th>
                        <th class="p-4">Cliente</th>
                        <th class="p-4">Depto / Piezas</th>
                        <th class="p-4">Ingreso</th>
                        <th class="p-4">Tiempo Olvidado</th>
                        <th class="p-4 text-center">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                    @forelse($rezagados as $pickup)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="p-4">
                                <div class="font-black text-white text-lg tracking-wider">{{ $pickup->ticket_folio }}</div>
                                <div class="text-xs text-gray-500 font-mono">REF: {{ $pickup->client_ref_id ?? 'N/A' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-gray-200">{{ $pickup->client_name }}</div>
                            </td>
                            <td class="p-4">
                                @if($pickup->department === 'CALLCENTER')
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">Call Center</span>
                                @elseif($pickup->department === 'AROMAS')
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-500/20 text-purple-400 border border-purple-500/30">Aromas</span>
                                @else
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider bg-pink-500/20 text-pink-400 border border-pink-500/30">Bellaroma</span>
                                @endif
                                <div class="mt-2 text-xs font-bold text-gray-400">{{ $pickup->pieces }} Piezas</div>
                            </td>
                            <td class="p-4 text-gray-400">
                                {{ $pickup->created_at->format('d/m/Y') }}
                            </td>
                            <td class="p-4">
                                <span class="text-red-400 font-bold flex items-center gap-1">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    {{ \App\Support\CustodyDurationFormatter::label($pickup->created_at) }}
                                </span>
                            </td>
                            <td class="p-4 text-center">
                                <button type="button"
                                    @click="$dispatch('open-gerencia-delivery', {{ \Illuminate\Support\Js::from([
                                        'id' => $pickup->id,
                                        'ticket_folio' => $pickup->ticket_folio,
                                        'client_name' => $pickup->client_name,
                                        'client_ref_id' => $pickup->client_ref_id,
                                        'is_third_party' => (bool) $pickup->is_third_party,
                                        'receiver_name' => $pickup->receiver_name,
                                    ]) }})"
                                    class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white font-bold rounded-lg text-xs uppercase tracking-wider shadow-lg shadow-yellow-600/20 transition-transform active:scale-95">
                                    Entregar
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="text-lg font-bold">Bóveda Limpia</p>
                                    <p class="text-sm mt-1">No hay paquetes con más de 15 días de antigüedad.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('gerencia.partials.pickup-delivery-modal')
    @include('gerencia.partials.delivery-scripts', ['deliveryRedirectTo' => route('gerencia.rezagados.index')])
</x-gerencia-layout>
