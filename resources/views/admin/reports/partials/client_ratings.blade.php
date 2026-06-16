<div class="flex justify-between items-center mb-6 flex-wrap gap-4">
    <div>
        <h3 class="text-2xl font-black text-white uppercase tracking-widest">Expediente de Clientes</h3>
        <p class="text-xs text-yellow-500 font-bold mt-1">Estadísticas Históricas (No afectadas por filtro de fecha)</p>
    </div>
    
    {{-- Buscador Exclusivo de Clientes --}}
    <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-2 w-full md:w-auto">
        <input type="hidden" name="tab" value="client_ratings">
        <input type="text" name="client_search" value="{{ request('client_search') }}" placeholder="Buscar nombre o ID..." class="bg-gray-900 text-white border border-gray-700 rounded-lg px-4 py-2 text-sm focus:border-aromas-highlight w-full md:w-64">
        <button type="submit" class="bg-aromas-highlight text-aromas-main font-bold px-4 py-2 rounded-lg text-sm hover:bg-yellow-500 transition">Buscar</button>
        @if(request('client_search'))
            <a href="{{ route('admin.reports.index', ['tab' => 'client_ratings']) }}" class="bg-gray-800 text-gray-400 font-bold px-3 py-2 rounded-lg text-sm hover:text-white transition">X</a>
        @endif
    </form>
</div>

<div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-800/50 border-b border-gray-700">
                <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                    <th class="p-4">ID / Tipo</th>
                    <th class="p-4">Nombre del Cliente</th>
                    <th class="p-4 text-center">Calificación General</th>
                    <th class="p-4 text-right">Auditoría</th>
                </tr>
            </thead>
            @if($customersDirectory)
                @forelse($customersDirectory as $customer)
                {{-- ACORDEÓN ALPINE PARA CADA CLIENTE --}}
                <tbody x-data="{ expanded: false }" class="divide-y divide-gray-800 border-b border-gray-700/50">
                    <tr class="hover:bg-gray-800/30 transition-colors">
                        <td class="p-4">
                            <span class="font-mono text-gray-400 block">{{ $customer->customer_number ?? 'S/N' }}</span>
                            @if($customer->catalogClientType?->usesPremiumAlert()) <span class="bg-yellow-500/20 text-yellow-400 text-[9px] px-1.5 py-0.5 rounded border border-yellow-500/30 uppercase font-bold mt-1 inline-block">{{ $customer->catalogClientType->displayLabel() }}</span> @endif
                        </td>
                        <td class="p-4 font-bold text-white">{{ $customer->name }}</td>
                        <td class="p-4 text-center">
                            @if($customer->all_time_stars)
                                <div class="text-2xl font-black text-yellow-400">{{ $customer->all_time_stars }} ⭐</div>
                                <div class="text-[10px] text-gray-500">{{ $customer->comments_history->count() }} evaluaciones</div>
                            @else
                                <span class="text-gray-600 font-bold text-xl">--</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <button @click="expanded = !expanded" class="text-sm font-bold px-4 py-2 rounded-lg transition-colors border"
                                :class="expanded ? 'bg-gray-800 text-white border-gray-600' : 'bg-transparent text-blue-400 border-blue-500/30 hover:bg-blue-500/10'">
                                <span x-text="expanded ? 'Ocultar' : 'Ver Historial'"></span>
                            </button>
                        </td>
                    </tr>
                    
                    {{-- HISTORIAL DESPLEGABLE --}}
                    <tr x-show="expanded" style="display: none;" class="bg-black/40">
                        <td colspan="4" class="p-0">
                            <div class="p-6">
                                <h4 class="text-xs text-gray-500 uppercase tracking-widest font-bold mb-4 border-b border-gray-800 pb-2">Comentarios de Vendedores</h4>
                                @if($customer->comments_history->count() > 0)
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                        @foreach($customer->comments_history as $history)
                                            <div class="bg-gray-900 border border-gray-700 rounded-xl p-4 relative">
                                                <div class="absolute top-4 right-4 text-yellow-400 font-black">{{ $history->stars }} ⭐</div>
                                                <p class="text-[10px] text-gray-500 mb-2">{{ $history->created_at->format('d M, Y - H:i') }} | Turno: {{ $history->salesQueue->turn_number ?? 'N/A' }}</p>
                                                <p class="text-sm text-gray-300 font-bold mb-2">Evaluó: {{ $history->salesQueue->assignedShift->employee->full_name ?? 'Desconocido' }}</p>
                                                
                                                <div class="flex flex-wrap gap-1 mb-3">
                                                    @foreach($history->tags ?? [] as $tag)
                                                        <span class="bg-blue-900/40 text-blue-400 px-2 py-0.5 rounded text-[10px] uppercase font-bold">{{ $tag }}</span>
                                                    @endforeach
                                                </div>
                                                <p class="text-sm text-gray-400 italic">"{{ $history->comments ?: 'Sin comentario adicional.' }}"</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-gray-500 italic">Este cliente aún no ha sido evaluado por ningún vendedor.</p>
                                @endif
                            </div>
                        </td>
                    </tr>
                </tbody>
                @empty
                <tbody><tr><td colspan="4" class="p-8 text-center text-gray-500">No se encontraron clientes.</td></tr></tbody>
                @endforelse
            @endif
        </table>
    </div>
    @if($customersDirectory)
    <div class="p-4 border-t border-gray-700 bg-gray-900">{{ $customersDirectory->appends(request()->except('cd_page'))->links() }}</div>
    @endif
</div>