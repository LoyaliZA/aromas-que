<x-cedis-layout> {{-- O crea un cedis-layout si prefieres --}}
    <div class="mb-8">
        <h1 class="text-3xl font-black text-white uppercase tracking-tight">Centro de Distribución (CEDIS)</h1>
        <p class="text-aromas-tertiary mt-1 text-sm">Bandeja de entrada unificada de remisiones para empaque y despacho.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        {{-- Stats rápidos --}}
        <div class="bg-aromas-secondary p-6 rounded-xl border border-aromas-tertiary/20 shadow-lg">
            <p class="text-xs font-bold text-gray-500 uppercase">Pendientes Bellaroma</p>
            <p class="text-3xl font-black text-blue-400 mt-2">
                {{ $remissions->where('department', 'BELLAROMA')->count() }}
            </p>
        </div>
        <div class="bg-aromas-secondary p-6 rounded-xl border border-aromas-tertiary/20 shadow-lg">
            <p class="text-xs font-bold text-gray-500 uppercase">Pendientes Call Center</p>
            <p class="text-3xl font-black text-purple-400 mt-2">
                {{ $remissions->where('department', 'CALLCENTER')->count() }}
            </p>
        </div>
        <div class="bg-aromas-secondary p-6 rounded-xl border border-aromas-tertiary/20 shadow-lg">
            <p class="text-xs font-bold text-gray-500 uppercase">Total en Fila</p>
            <p class="text-3xl font-black text-aromas-highlight mt-2">{{ $remissions->count() }}</p>
        </div>
    </div>

    {{-- Tabla Unificada --}}
    <div class="bg-aromas-secondary border border-aromas-tertiary/20 rounded-xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-black/30 text-gray-400 text-xs uppercase tracking-widest border-b border-aromas-tertiary/20">
                        <th class="px-6 py-4">Origen</th>
                        <th class="px-6 py-4">Folio</th>
                        <th class="px-6 py-4">Cliente</th>
                        <th class="px-6 py-4 text-center">Cajas</th>
                        <th class="px-6 py-4 text-center">Estatus</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aromas-tertiary/10">
                    @foreach($remissions as $rem)
                    <tr class="hover:bg-white/5 transition-colors">
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded text-[10px] font-black uppercase {{ $rem->department === 'BELLAROMA' ? 'bg-blue-500/20 text-blue-400 border border-blue-500/30' : 'bg-purple-500/20 text-purple-400 border border-purple-500/30' }}">
                                {{ $rem->department }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-white">#{{ $rem->ticket_folio }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-white">{{ $rem->customer_name }}</div>
                            <div class="text-xs text-gray-500">Capturado hace {{ $rem->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 text-center font-bold text-aromas-highlight">{{ $rem->pieces }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 text-[10px] font-bold rounded-full uppercase border bg-{{ $rem->currentStatus->color }}-500/20 text-{{ $rem->currentStatus->color }}-400 border-{{ $rem->currentStatus->color }}-500/30">
                                {{ $rem->currentStatus->name ?? 'Sin Estatus' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="bg-aromas-highlight text-aromas-main px-4 py-2 rounded-lg font-bold text-xs hover:bg-white transition-colors">
                                Gestionar Empaque
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-cedis-layout>