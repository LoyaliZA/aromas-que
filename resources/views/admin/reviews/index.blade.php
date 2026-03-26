<x-admin-layout>
    {{-- Ajustamos la altura máxima de la vista al tamaño de la pantalla (descontando el header) --}}
    <div class="flex flex-col md:flex-row gap-6 md:h-[calc(100vh-8rem)]">

        {{-- DIRECTORIO LATERAL DE VENDEDORES --}}
        <div class="w-full md:w-80 flex-shrink-0 flex flex-col h-full">
            <div class="bg-aromas-secondary border border-aromas-tertiary/30 rounded-xl overflow-hidden shadow-lg flex flex-col h-full">
                <div class="p-5 bg-gray-900 border-b border-gray-800 shrink-0">
                    <h2 class="text-lg font-black text-white uppercase tracking-widest mb-1">Ranking de Calidad</h2>
                    <p class="text-xs text-gray-400 font-bold">Evaluaciones de clientes</p>
                </div>
                
                <div class="overflow-y-auto flex-1 custom-scrollbar p-3 space-y-2 bg-gray-900/50">
                    @foreach($sellers as $index => $seller)
                    <a href="{{ route('admin.reviews.seller', $seller->id) }}" class="block bg-gray-800 border border-gray-700 hover:border-yellow-500/50 hover:bg-gray-800/80 rounded-xl p-3 transition-all group">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-black text-gray-500 group-hover:text-yellow-500">#{{ $index + 1 }}</span>
                            @if($seller->avg_stars)
                                <span class="bg-yellow-500/10 text-yellow-400 font-bold px-2 py-0.5 rounded text-[10px] border border-yellow-500/20">
                                    {{ $seller->avg_stars }} ⭐
                                </span>
                            @else
                                <span class="bg-gray-700 text-gray-400 font-bold px-2 py-0.5 rounded text-[9px] uppercase">Sin datos</span>
                            @endif
                        </div>
                        <h3 class="font-bold text-white text-sm truncate">{{ $seller->full_name }}</h3>
                        <p class="text-[10px] text-gray-400 mt-1">{{ $seller->total_reviews }} evaluaciones</p>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- CONTENIDO PRINCIPAL --}}
        {{-- Forzamos a que respete el alto total y use min-h-0 para que el flex-1 interno pueda scrollear --}}
        <div class="flex-1 flex flex-col gap-4 h-full min-h-0">

            {{-- BUSCADOR GLOBAL DE EXPEDIENTES --}}
            <div x-data="reviewSearch()" class="relative z-50 shrink-0">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="query" @input.debounce.500ms="search()" @focus="show = true" @click.away="show = false"
                           placeholder="Buscar expediente de Vendedor (Código/Nombre) o Cliente (ID/Nombre)..." 
                           class="w-full bg-gray-900 border border-gray-700 rounded-xl py-3 pl-12 pr-12 text-sm text-white placeholder-gray-500 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all shadow-md">
                    
                    <div x-show="isLoading" class="absolute inset-y-0 right-0 pr-4 flex items-center" style="display: none;">
                        <svg class="animate-spin h-5 w-5 text-aromas-highlight" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>

                {{-- Resultados Flotantes --}}
                <div x-show="show && (results.sellers.length > 0 || results.customers.length > 0)" style="display: none;" 
                     class="absolute w-full mt-2 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl overflow-hidden divide-y divide-gray-700">
                    
                    {{-- Vendedores --}}
                    <div x-show="results.sellers.length > 0" class="p-2" style="display: none;">
                        <h4 class="text-[9px] uppercase font-bold text-gray-500 px-3 pb-1 tracking-widest">Vendedores</h4>
                        <template x-for="seller in results.sellers" :key="seller.id">
                            <a :href="`/admin/reviews/seller/${seller.id}`" class="flex items-center gap-3 p-2 hover:bg-gray-700/50 rounded-lg transition-colors group">
                                <div class="bg-gray-900 text-yellow-500 p-1.5 rounded-lg border border-gray-700 group-hover:border-yellow-500/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <p class="font-bold text-white text-xs" x-text="seller.full_name"></p>
                                    <p class="text-[10px] text-gray-400 font-mono">Código: <span x-text="seller.employee_code"></span></p>
                                </div>
                            </a>
                        </template>
                    </div>

                    {{-- Clientes --}}
                    <div x-show="results.customers.length > 0" class="p-2" style="display: none;">
                        <h4 class="text-[9px] uppercase font-bold text-gray-500 px-3 pb-1 pt-2 tracking-widest">Clientes</h4>
                        <template x-for="customer in results.customers" :key="customer.id">
                            <a :href="`/admin/reviews/customer/${customer.id}`" class="flex items-center gap-3 p-2 hover:bg-gray-700/50 rounded-lg transition-colors group">
                                <div class="bg-gray-900 text-blue-400 p-1.5 rounded-lg border border-gray-700 group-hover:border-blue-500/50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </div>
                                <div>
                                    <div class=\"flex items-center gap-2\">
                                        <p class="font-bold text-white text-xs" x-text="customer.name"></p>
                                        <span x-show="customer.client_type === 'VIP'" class="bg-yellow-500 text-yellow-900 text-[9px] px-1.5 rounded font-black uppercase tracking-wider" style="display: none;">VIP</span>
                                    </div>
                                    <p class="text-[10px] text-gray-400 font-mono">ID: <span x-text="customer.customer_number || 'S/N'"></span></p>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>
            </div>
            
            {{-- KPIs Globales --}}
            <div class="grid grid-cols-2 gap-4 shrink-0">
                <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Promedio de Calidad de Atención</p>
                        <p class="text-2xl font-black text-yellow-400">{{ $averageSystemRating ? round($averageSystemRating, 1) : '0.0' }} ⭐</p>
                    </div>
                    <div class="p-3 bg-yellow-500/10 rounded-full text-yellow-500">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    </div>
                </div>
                <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 shadow-md flex items-center justify-between">
                    <div>
                        <p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Total Reseñas</p>
                        <p class="text-2xl font-black text-white">{{ $totalReviews }}</p>
                    </div>
                    <div class="p-3 bg-blue-500/10 rounded-full text-blue-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                </div>
            </div>

            {{-- Feed de Auditoría --}}
            {{-- Añadimos flex-1 y min-h-0 al contenedor para que el scroll interno funcione --}}
            <div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md flex-1 flex flex-col min-h-0">
                <div class="p-4 border-b border-gray-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-3 bg-gray-800/30 shrink-0">
                    <h3 class="text-sm font-black text-white uppercase tracking-widest">Feed de Auditoría</h3>
                    
                    <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex gap-2 w-full md:w-auto">
                        <select name="type" class="bg-gray-800 text-white border border-gray-600 rounded-lg px-2 py-1.5 text-xs focus:border-aromas-highlight">
                            <option value="">Cualquier Origen</option>
                            <option value="CLIENT" {{ request('type') == 'CLIENT' ? 'selected' : '' }}>Cliente a Vendedor</option>
                            <option value="SELLER" {{ request('type') == 'SELLER' ? 'selected' : '' }}>Vendedor a Cliente</option>
                        </select>
                        <select name="stars" class="bg-gray-800 text-white border border-gray-600 rounded-lg px-2 py-1.5 text-xs focus:border-aromas-highlight">
                            <option value="">Todas ⭐</option>
                            <option value="5" {{ request('stars') == '5' ? 'selected' : '' }}>5 ⭐</option>
                            <option value="4" {{ request('stars') == '4' ? 'selected' : '' }}>4 ⭐</option>
                            <option value="3" {{ request('stars') == '3' ? 'selected' : '' }}>3 ⭐</option>
                            <option value="2" {{ request('stars') == '2' ? 'selected' : '' }}>2 ⭐</option>
                            <option value="1" {{ request('stars') == '1' ? 'selected' : '' }}>1 ⭐</option>
                        </select>
                        <button type="submit" class="bg-gray-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-gray-600 transition">Filtrar</button>
                        @if(request()->hasAny(['type', 'stars']))
                            <a href="{{ route('admin.reviews.index') }}" class="bg-red-500/20 text-red-400 border border-red-500/30 px-2 py-1.5 rounded-lg text-xs font-bold hover:bg-red-500 hover:text-white transition">X</a>
                        @endif
                    </form>
                </div>

                {{-- Zona que scrollea internamente --}}
                <div class="p-4 space-y-3 overflow-y-auto flex-1 custom-scrollbar">
                    @forelse($recentReviews as $review)
                    <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 flex flex-col md:flex-row gap-3 relative">
                        <div class="md:w-40 shrink-0 border-b md:border-b-0 md:border-r border-gray-700 pb-3 md:pb-0 md:pr-3">
                            <div class="text-xl font-black mb-1 {{ $review->stars >= 4 ? 'text-green-400' : ($review->stars == 3 ? 'text-yellow-400' : 'text-red-400') }}">
                                {{ $review->stars }} ⭐
                            </div>
                            <p class="text-[9px] text-gray-500 font-mono">{{ $review->created_at->format('d M, Y - H:i') }}</p>
                            <div class="mt-2 inline-block">
                                <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-widest {{ $review->rater_type === 'CLIENT' ? 'bg-blue-900/50 text-blue-400 border border-blue-500/30' : 'bg-purple-900/50 text-purple-400 border border-purple-500/30' }}">
                                    De: {{ $review->rater_type === 'CLIENT' ? 'Cliente' : 'Vendedor' }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-2">
                                <p class="text-xs font-bold text-gray-300">
                                    <span class="text-gray-500 font-normal">Vendedor:</span> 
                                    <a href="{{ route('admin.reviews.seller', $review->salesQueue->assignedShift->employee->id ?? 0) }}" class="text-white hover:text-yellow-400 hover:underline">
                                        {{ $review->salesQueue->assignedShift->employee->full_name ?? 'Desconocido' }}
                                    </a>
                                </p>
                                <span class="hidden sm:block text-gray-600">•</span>
                                <p class="text-xs font-bold text-gray-300">
                                    <span class="text-gray-500 font-normal">Cliente:</span> 
                                    @if(isset($review->salesQueue->customer_id))
                                        <a href="{{ route('admin.reviews.customer', $review->salesQueue->customer_id) }}" class="text-white hover:text-yellow-400 hover:underline">
                                            {{ $review->salesQueue->customer->name ?? $review->salesQueue->client_name }}
                                        </a>
                                    @else
                                        <span class="text-white">{{ $review->salesQueue->client_name ?? 'Desconocido' }}</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div class="flex flex-wrap gap-1 mb-2">
                                @foreach($review->tags ?? [] as $tag)
                                    <span class="bg-gray-900 text-gray-400 border border-gray-700 px-1.5 py-0.5 rounded text-[9px] uppercase font-bold tracking-wider">{{ $tag }}</span>
                                @endforeach
                            </div>
                            <p class="text-gray-300 bg-gray-900/50 p-2 rounded border border-gray-700/50 italic text-xs">
                                "{{ $review->comments ?: 'Sin comentarios.' }}"
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8">
                        <p class="text-gray-500 text-sm font-bold">No hay reseñas que coincidan con tu búsqueda.</p>
                    </div>
                    @endforelse
                </div>

                <div class="p-3 border-t border-gray-800 bg-gray-900/80 shrink-0">
                    {{ $recentReviews->links() }}
                </div>
            </div>

        </div>

    </div>

    <script>
        function reviewSearch() {
            return {
                query: '',
                results: { sellers: [], customers: [] },
                isLoading: false,
                show: false,
                search() {
                    if(this.query.length < 2) {
                        this.results = { sellers: [], customers: [] };
                        this.show = false;
                        return;
                    }
                    this.isLoading = true;
                    fetch(`/admin/reviews/search?q=${encodeURIComponent(this.query)}`)
                        .then(res => res.json())
                        .then(data => {
                            this.results = data;
                            this.show = true;
                        })
                        .finally(() => {
                            this.isLoading = false;
                        });
                }
            }
        }
    </script>
</x-admin-layout>