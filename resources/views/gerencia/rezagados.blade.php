<x-gerencia-layout>
    <div class="mb-8 border-l-4 border-yellow-500 pl-4">
        <div class="flex items-center gap-3 mb-1">
            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h1 class="text-2xl font-bold text-white tracking-tight">Bóveda de Rezagados</h1>
        </div>
        <p class="text-gray-400 text-sm">Paquetes con más de 15 días en tienda. Todas las entregas en esta sección son auditadas rigurosamente.</p>
    </div>

    {{-- Contenedor principal con Alpine.js para gestionar el modal de entrega --}}
    <div x-data="{ 
            showModal: false, 
            pickupId: null, 
            pickupFolio: '', 
            clientName: '',
            daysOld: 0
        }">

        {{-- TABLA DE REZAGADOS --}}
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
                            @php
                                // Calculamos cuántos días han pasado desde que se creó
                                $daysInCustody = $pickup->created_at->diffInDays(now());
                            @endphp
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="p-4">
                                    <div class="font-black text-white text-lg tracking-wider">{{ $pickup->ticket_folio }}</div>
                                    <div class="text-xs text-gray-500 font-mono">REF: {{ $pickup->client_ref_id ?? 'N/A' }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-gray-200">{{ $pickup->client_name }}</div>
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase tracking-wider 
                                        {{ $pickup->department === 'BELLAROMA' ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                                        {{ $pickup->department }}
                                    </span>
                                    <div class="mt-2 text-xs font-bold text-gray-400">{{ $pickup->pieces }} Piezas</div>
                                </td>
                                <td class="p-4 text-gray-400">
                                    {{ $pickup->created_at->format('d/m/Y') }}
                                </td>
                                <td class="p-4">
                                    <span class="text-red-400 font-bold flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Hace {{ $daysInCustody }} días
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <button @click="showModal = true; pickupId = {{ $pickup->id }}; pickupFolio = '{{ $pickup->ticket_folio }}'; clientName = '{{ addslashes($pickup->client_name) }}'; daysOld = {{ $daysInCustody }};" 
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

        {{-- MODAL DE ENTREGA EXCEPCIONAL --}}
        <div x-show="showModal" style="display: none;" 
             class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            
            <div class="bg-aromas-secondary rounded-2xl border-2 border-yellow-500 p-8 w-full max-w-md shadow-[0_0_50px_rgba(234,179,8,0.15)] transform transition-all" @click.away="showModal = false">
                
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-yellow-500/20 rounded-full mb-4">
                    <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>

                <h3 class="text-xl font-black text-white text-center uppercase tracking-wider mb-2">Entregar Rezago</h3>
                <p class="text-center text-gray-400 text-sm mb-6">Autorización especial requerida. Esta acción quedará registrada en la bitácora a tu nombre.</p>
                
                <div class="bg-black/30 p-4 rounded-lg border border-yellow-500/30 mb-6 text-center">
                    <span class="block text-xs text-yellow-500 uppercase tracking-widest font-bold mb-1">Folio</span>
                    <span class="block text-3xl font-black text-white" x-text="pickupFolio"></span>
                    <span class="block text-sm text-gray-400 mt-2">Cliente: <span class="font-bold text-white" x-text="clientName"></span></span>
                    <span class="block text-xs text-red-400 mt-1 font-bold">Tiempo olvidado: <span x-text="daysOld"></span> días</span>
                </div>

                <form :action="'/gerencia/rezagados/' + pickupId + '/entregar'" method="POST" class="space-y-5">
                    @csrf
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">¿A quién se le entrega? <span class="text-red-500">*</span></label>
                        <input type="text" name="receiver_name" required :placeholder="clientName"
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-600 focus:ring-yellow-500 focus:border-yellow-500 p-3">
                        <p class="text-[10px] text-gray-500 mt-1">Nombre de la persona física que recibe el paquete hoy.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Motivo / Identificación <span class="text-red-500">*</span></label>
                        <textarea name="notes" required rows="2"
                                  class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-600 focus:ring-yellow-500 focus:border-yellow-500 p-3"
                                  placeholder="Ej: Presentó INE, se disculpó por la demora..."></textarea>
                    </div>
                    
                    <div class="flex gap-4 pt-4 border-t border-aromas-tertiary/20">
                        <button type="button" @click="showModal = false" class="w-1/2 py-3 text-sm font-bold text-gray-400 hover:text-white transition-colors uppercase tracking-wider">
                            Cancelar
                        </button>
                        <button type="submit" class="w-1/2 py-3 bg-yellow-600 hover:bg-yellow-500 text-white font-black rounded-lg uppercase tracking-wider shadow-lg transition-transform active:scale-95">
                            Confirmar
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-gerencia-layout>