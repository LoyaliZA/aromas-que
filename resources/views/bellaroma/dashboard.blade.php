<x-bellaroma-layout>
    <div x-data="{ showForm: false }">

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Dashboard Bellaroma</h1>
                <p class="text-gray-400 text-sm mt-1">Gestión y seguimiento de remisiones en tiempo real.</p>
            </div>

            <div class="flex gap-3">
                <a href="{{ route('bellaroma.history') }}" class="bg-gray-700 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded-lg shadow-lg transition-all flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Historial
                </a>

                <button @click="showForm = !showForm"
                    :class="showForm ? 'bg-red-500 hover:bg-red-600 shadow-red-500/30' : 'bg-aromas-success hover:bg-green-600 shadow-aromas-success/30'"
                    class="text-white font-semibold py-2 px-6 rounded-lg shadow-lg transition-all flex items-center">
                    <svg x-show="!showForm" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <svg x-show="showForm" style="display: none;" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    <span x-text="showForm ? 'Cancelar' : 'Crear Remisión'"></span>
                </button>
            </div>
        </div>

        <div x-show="showForm" x-transition.opacity.duration.300ms style="display: none;" class="mb-8">
            <livewire:logistica.create-remission-form origin="BELLAROMA" />
        </div>
    </div>

    <div class="bg-aromas-secondary border border-aromas-tertiary/50 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-aromas-tertiary/50 flex flex-col sm:flex-row justify-between items-center gap-4">
            <h2 class="text-lg font-semibold text-white">Remisiones Activas</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-aromas-main/50 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-medium">Folio</th>
                        <th class="px-6 py-4 font-medium">Cliente</th>
                        <th class="px-6 py-4 font-medium">Monto</th>
                        <th class="px-6 py-4 font-medium">Vendedora</th>
                        <th class="px-6 py-4 font-medium">Estatus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aromas-tertiary/30">
                    @forelse($remissions as $rem)
                    <tr class="hover:bg-aromas-main/30 transition-colors">
                        <td class="px-6 py-4 font-bold text-white">#{{ $rem->ticket_folio }}</td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-medium text-gray-200">{{ $rem->client_name }}</p>
                            <p class="text-xs text-gray-500">{{ $rem->created_at->format('d/m/Y H:i') }}</p>
                        </td>
                        <td class="px-6 py-4 text-sm font-bold text-aromas-highlight">
                            ${{ number_format($rem->amount ?? optional($rem->logistic)->note_amount ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-300">
                            {{-- Accedemos al nombre completo a través de la relación --}}
                            {{ $rem->seller->full_name ?? 'No asignada' }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full bg-{{ $rem->currentStatus->color ?? 'gray' }}-500/20 text-{{ $rem->currentStatus->color ?? 'gray' }}-400 border border-{{ $rem->currentStatus->color ?? 'gray' }}-500/30">
                                {{ $rem->currentStatus->name ?? 'Desconocido' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr class="hover:bg-aromas-main/30 transition-colors">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No hay remisiones activas. Todo está al día.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4">
            {{ $remissions->links() }}
        </div>
    </div>
</x-bellaroma-layout>