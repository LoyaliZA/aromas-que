<x-bellaroma-layout>
    <div class="mb-8 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('bellaroma.dashboard') }}" class="text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-white tracking-tight">Historial de Remisiones</h1>
            </div>
            <p class="text-gray-400 text-sm mt-1 ml-9">Registro global de todas las operaciones realizadas.</p>
        </div>
    </div>

    <div class="bg-aromas-secondary border border-aromas-tertiary/50 rounded-xl shadow-sm overflow-hidden">
        <div class="p-6 border-b border-aromas-tertiary/50">
            <form method="GET" action="{{ route('bellaroma.history') }}" class="relative w-full md:w-1/3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por folio o cliente..." class="w-full bg-aromas-main border border-aromas-tertiary rounded-lg pl-10 pr-4 py-2 text-sm text-gray-200 focus:ring-aromas-highlight">
                <svg class="w-5 h-5 text-gray-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-aromas-main/50 text-gray-400 text-xs uppercase tracking-wider">
                        <th class="px-6 py-4 font-medium">Folio</th>
                        <th class="px-6 py-4 font-medium">Cliente</th>
                        <th class="px-6 py-4 font-medium">Monto</th>
                        <th class="px-6 py-4 font-medium">Estatus</th>
                        <th class="px-6 py-4 font-medium text-right">Fecha Captura</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aromas-tertiary/30">
                    @forelse($remissions as $rem)
                        <tr class="hover:bg-aromas-main/30 transition-colors">
                            <td class="px-6 py-4 font-bold text-white">#{{ $rem->ticket_folio }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-200">{{ $rem->client_name }}</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-300">${{ number_format($rem->amount, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full bg-{{ $rem->currentStatus->color ?? 'gray' }}-500/20 text-{{ $rem->currentStatus->color ?? 'gray' }}-400 border border-{{ $rem->currentStatus->color ?? 'gray' }}-500/30">
                                    {{ $rem->currentStatus->name ?? 'Desconocido' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm text-gray-400">{{ $rem->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr class="hover:bg-aromas-main/30">
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No se encontraron resultados.</td>
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