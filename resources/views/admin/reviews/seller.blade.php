<x-admin-layout>
    <div class="mb-6">
        <a href="{{ route('admin.reviews.index') }}" class="text-gray-400 hover:text-white font-bold text-sm flex items-center gap-2 mb-4 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Volver al Dashboard
        </a>
        <h1 class="text-3xl font-black text-white uppercase tracking-widest">{{ $seller->full_name }}</h1>
        <p class="text-sm text-gray-400">Código: {{ $seller->employee_code }} | Evaluaciones de Clientes</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Resumen de KPIs --}}
        <div class="bg-gray-900 rounded-xl border border-gray-700 shadow-md p-6 flex flex-col justify-center items-center text-center">
            <h3 class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-2">Calificación Global</h3>
            @if($totalReviews > 0)
                <div class="text-6xl font-black text-yellow-400 mb-2">{{ $avgStars }}</div>
                <div class="flex gap-1 text-yellow-500 mb-4">
                    @for($i=1; $i<=5; $i++)
                        <svg class="w-6 h-6 {{ $i <= round($avgStars) ? 'text-yellow-400' : 'text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p class="text-sm font-bold text-gray-300">Basado en {{ $totalReviews }} reseñas</p>
            @else
                <div class="text-5xl font-black text-gray-600 mb-4">--</div>
                <p class="text-sm font-bold text-gray-500">Sin datos suficientes</p>
            @endif
        </div>

        {{-- Gráfica de Distribución --}}
        <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-700 shadow-md p-6">
            <h3 class="text-xs text-gray-400 uppercase font-bold tracking-widest mb-4">Distribución de Estrellas</h3>
            <div class="h-48 relative w-full">
                <canvas id="starsChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Historial de Reseñas --}}
    <div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
        <div class="p-5 border-b border-gray-800 flex justify-between items-center bg-gray-800/30">
            <h3 class="text-lg font-black text-white uppercase tracking-widest">Historial Detallado</h3>
            <form method="GET" action="{{ route('admin.reviews.seller', $seller->id) }}">
                <select name="sort" onchange="this.form.submit()" class="bg-gray-800 text-white border border-gray-600 rounded-lg px-3 py-1 text-sm focus:border-aromas-highlight">
                    <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Más Recientes</option>
                    <option value="best" {{ request('sort') == 'best' ? 'selected' : '' }}>Mejores (5 ⭐)</option>
                    <option value="worst" {{ request('sort') == 'worst' ? 'selected' : '' }}>Peores (1 ⭐)</option>
                </select>
            </form>
        </div>

        <div class="divide-y divide-gray-800">
            @forelse($reviews as $review)
            <div class="p-6 hover:bg-gray-800/30 transition-colors flex flex-col md:flex-row gap-6">
                <div class="md:w-48 shrink-0">
                    <div class="text-2xl font-black mb-1 {{ $review->stars >= 4 ? 'text-green-400' : ($review->stars == 3 ? 'text-yellow-400' : 'text-red-400') }}">
                        {{ $review->stars }} ⭐
                    </div>
                    <p class="text-[10px] text-gray-500 font-mono">{{ $review->created_at->format('d M, Y - H:i') }}</p>
                    <p class="text-xs text-gray-400 mt-2 font-bold truncate">Turno: {{ $review->salesQueue->turn_number ?? 'S/N' }}</p>
                </div>
                
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-300 mb-3">
                        <span class="text-gray-500">Cliente:</span> 
                        <a href="{{ isset($review->salesQueue->customer_id) ? route('admin.reviews.customer', $review->salesQueue->customer_id) : '#' }}" class="text-white hover:text-yellow-400 hover:underline">
                            {{ $review->salesQueue->customer->name ?? $review->salesQueue->client_name ?? 'Desconocido' }}
                        </a>
                    </p>
                    
                    <div class="flex flex-wrap gap-2 mb-3">
                        @foreach($review->tags ?? [] as $tag)
                            <span class="bg-blue-900/30 text-blue-400 border border-blue-500/30 px-2 py-1 rounded text-[10px] uppercase font-bold tracking-wider">{{ $tag }}</span>
                        @endforeach
                    </div>
                    <p class="text-gray-300 text-sm italic">
                        "{{ $review->comments ?: 'El cliente no dejó comentarios adicionales.' }}"
                    </p>
                </div>
            </div>
            @empty
            <div class="p-10 text-center text-gray-500 font-bold">
                Aún no hay evaluaciones para este vendedor.
            </div>
            @endforelse
        </div>
        <div class="p-4 border-t border-gray-800 bg-gray-900/80">
            {{ $reviews->links() }}
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart !== 'undefined') {
                const ctx = document.getElementById('starsChart');
                const distribution = @json($starsDistribution);
                
                // Mapear los datos (Asegurar que existan del 1 al 5)
                const data = [
                    distribution['5'] || 0,
                    distribution['4'] || 0,
                    distribution['3'] || 0,
                    distribution['2'] || 0,
                    distribution['1'] || 0,
                ];

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['5 Estrellas', '4 Estrellas', '3 Estrellas', '2 Estrellas', '1 Estrella'],
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)', // Verde
                                'rgba(132, 204, 22, 0.8)', // Verde Claro
                                'rgba(234, 179, 8, 0.8)',  // Amarillo
                                'rgba(249, 115, 22, 0.8)', // Naranja
                                'rgba(239, 68, 68, 0.8)'   // Rojo
                            ],
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y', // Gráfica horizontal
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }
        });
    </script>
</x-admin-layout>