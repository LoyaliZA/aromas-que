<x-callcenter-layout>
    <div x-data="{ showForm: false }">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Panel de Call Center</h1>
                <p class="text-gray-400 text-sm mt-1">Gestión de remisiones y pedidos telefónicos.</p>
            </div>
            
            <button @click="showForm = !showForm" 
                :class="showForm ? 'bg-red-500 hover:bg-red-600 shadow-red-500/30' : 'bg-purple-600 hover:bg-purple-700 shadow-purple-500/30'"
                class="text-white font-semibold py-2 px-6 rounded-lg shadow-lg transition-all flex items-center">
                
                <svg x-show="!showForm" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                
                <svg x-show="showForm" style="display: none;" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                
                <span x-text="showForm ? 'Cancelar Registro' : 'Nueva Remisión'"></span>
            </button>
        </div>

        <div x-show="showForm" x-transition x-cloak class="mb-10">
            <livewire:logistica.create-remission-form origin="CALLCENTER" />
        </div>

        <div class="bg-aromas-secondary border border-aromas-tertiary/20 rounded-xl shadow-xl overflow-hidden">
            <div class="p-6 border-b border-aromas-tertiary/10 flex justify-between items-center">
                <h2 class="text-lg font-bold text-white">Mis Remisiones Activas</h2>
                <div class="relative">
                    <input type="text" placeholder="Buscar folio..." class="bg-aromas-main border border-aromas-tertiary rounded-lg px-4 py-1.5 text-sm text-white focus:ring-purple-500 focus:border-purple-500 pl-10">
                    <svg class="w-4 h-4 text-gray-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black/20 text-gray-400 text-xs uppercase tracking-wider">
                            <th class="px-6 py-4 font-medium">Folio</th>
                            <th class="px-6 py-4 font-medium">Cliente</th>
                            <th class="px-6 py-4 font-medium text-center">Piezas</th>
                            <th class="px-6 py-4 font-medium">Monto</th>
                            <th class="px-6 py-4 font-medium">Vendedora</th>
                            <th class="px-6 py-4 font-medium text-center">Estatus</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-aromas-tertiary/10">
                        @forelse($remissions as $rem)
                            <tr class="hover:bg-aromas-main/40 transition-colors">
                                <td class="px-6 py-4 font-bold text-purple-400">#{{ $rem->ticket_folio }}</td>
                                <td class="px-6 py-4">
                                    <p class="text-white text-sm font-medium">{{ $rem->client_name }}</p>
                                    <p class="text-xs text-gray-500">Captura: {{ $rem->created_at->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="px-6 py-4 text-center text-sm font-bold text-gray-300">{{ $rem->pieces }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-white">
                                    ${{ number_format($rem->amount ?? optional($rem->logistic)->note_amount ?? 0, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300">
                                    {{ $rem->seller ? $rem->seller->full_name : 'No asignada' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full bg-{{ $rem->currentStatus->color ?? 'gray' }}-500/20 text-{{ $rem->currentStatus->color ?? 'gray' }}-400 border border-{{ $rem->currentStatus->color ?? 'gray' }}-500/30">
                                        {{ $rem->currentStatus->name ?? 'Desconocido' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    Todo está al día. No hay remisiones en proceso.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4">
                {{ $remissions->links() }}
            </div>
        </div>
    </div>
</x-callcenter-layout>