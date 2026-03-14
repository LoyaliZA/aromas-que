<x-auxiliar-layout>
    {{-- Contenedor principal con Alpine para manejar los modales --}}
    <div class="space-y-8 pb-10" x-data="{ 
        uploadModalOpen: false, 
        editModalOpen: false, 
        editData: { id: '', title: '', duration: 15, start: '', end: '', isVideo: false } 
    }">

        {{-- CABECERA Y FILTROS --}}
        <div class="bg-aromas-secondary shadow-xl rounded-xl border border-aromas-tertiary/20 overflow-hidden">
            <div class="p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 border-b border-aromas-tertiary/20">
                <div>
                    <h2 class="text-2xl font-black text-white uppercase tracking-wider">Gestión de Publicidad</h2>
                    <p class="text-aromas-highlight text-xs font-bold uppercase tracking-widest mt-1">Pantalla Principal T.E.R.A.</p>
                </div>

                <button @click="uploadModalOpen = true" class="bg-aromas-highlight text-aromas-main font-bold py-3 px-6 rounded-lg shadow-lg hover:bg-white transition-all uppercase tracking-wider flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Subir Nuevo Anuncio
                </button>
            </div>

            <div class="bg-black/20 p-4">
                <form method="GET" action="{{ route('auxiliar.dashboard') }}" class="flex flex-wrap items-center gap-3">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por título..."
                        class="bg-black/30 border border-aromas-tertiary/30 rounded-lg text-sm text-white focus:border-aromas-highlight focus:ring-aromas-highlight p-2.5 w-full md:w-64">

                    <input type="date" name="date" value="{{ request('date') }}"
                        class="bg-black/30 border border-aromas-tertiary/30 rounded-lg text-sm text-white focus:border-aromas-highlight focus:ring-aromas-highlight p-2.5 [color-scheme:dark]">

                    <button type="submit" class="bg-aromas-tertiary/30 hover:bg-aromas-tertiary/50 text-white text-sm font-bold py-2.5 px-5 rounded-lg transition-colors border border-aromas-tertiary/50">
                        Filtrar
                    </button>

                    @if(request()->has('search') || request()->has('date'))
                    <a href="{{ route('auxiliar.dashboard') }}" class="text-sm text-aromas-error hover:text-red-400 font-bold ml-2">Limpiar</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- GRID DE ANUNCIOS (CON ID PARA SORTABLE) --}}
        <div id="ads-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($ads as $index => $ad)
            <div class="tv-ad-card relative bg-aromas-main border border-aromas-tertiary/20 rounded-xl overflow-hidden shadow-lg transition-transform flex flex-col"
                data-id="{{ $ad->id }}"
                x-data="{ volume: {{ $ad->volume ?? 100 }} }">

                {{-- REFERENCIA VISUAL DEL ORDEN (NÚMERO) --}}
                <div class="absolute top-3 left-3 z-20 bg-black/80 text-white w-8 h-8 flex items-center justify-center rounded-full font-black border-2 border-aromas-highlight cursor-move order-badge shadow-md">
                    {{ $index + 1 }}
                </div>

                {{-- ICONO DE ARRASTRAR --}}
                <div class="absolute top-3 right-3 z-20 text-white cursor-move drag-handle p-1.5 bg-black/70 rounded hover:bg-aromas-highlight hover:text-black transition shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </div>

                {{-- MINIATURA RECONSTRUIDA (IMAGEN O VIDEO) --}}
                <div class="relative h-48 bg-black w-full overflow-hidden flex-shrink-0 group">
                    @if($ad->media_type === 'VIDEO')
                    <video class="w-full h-full object-cover opacity-75 group-hover:opacity-100 transition-opacity" src="{{ asset('storage/' . $ad->media_path) }}#t=1" muted></video>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <svg class="w-12 h-12 text-white/60 drop-shadow-lg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4l12 6-12 6z"></path>
                        </svg>
                    </div>
                    @else
                    <img class="w-full h-full object-cover" src="{{ asset('storage/' . $ad->media_path) }}" alt="{{ $ad->title }}">
                    @endif

                    {{-- ESTADO --}}
                    <div class="absolute bottom-3 right-3">
                        @if($ad->is_active)
                        <span class="bg-green-500 text-white text-[10px] px-2 py-1 rounded font-black tracking-widest shadow-md">ACTIVO</span>
                        @else
                        <span class="bg-red-500 text-white text-[10px] px-2 py-1 rounded font-black tracking-widest shadow-md">INACTIVO</span>
                        @endif
                    </div>
                </div>

                <div class="p-4 flex flex-col flex-1">
                    <h3 class="font-bold text-white text-lg truncate">{{ $ad->title }}</h3>
                    <p class="text-xs text-gray-400 mt-1">Duración: <span class="font-mono text-gray-300">{{ $ad->duration_seconds }}s</span></p>

                    {{-- BOTONES DE ACCIÓN --}}
                    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-aromas-tertiary/20">
                        <form method="POST" action="{{ route('auxiliar.tv_ads.toggle', $ad->id) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-1.5 text-xs font-bold rounded {{ $ad->is_active ? 'bg-gray-700 text-gray-300 hover:bg-gray-600' : 'bg-green-600 text-white hover:bg-green-500' }} transition">
                                {{ $ad->is_active ? 'Pausar' : 'Activar' }}
                            </button>
                        </form>

                        <button type="button" @click="editData = { id: {{ $ad->id }}, title: '{{ addslashes($ad->title) }}', duration: {{ $ad->duration_seconds }}, start: '{{ $ad->start_date ? \Carbon\Carbon::parse($ad->start_date)->format('Y-m-d\TH:i') : '' }}', end: '{{ $ad->end_date ? \Carbon\Carbon::parse($ad->end_date)->format('Y-m-d\TH:i') : '' }}', isVideo: {{ $ad->media_type === 'VIDEO' ? 'true' : 'false' }} }; editModalOpen = true" class="bg-blue-600 hover:bg-blue-500 text-white p-1.5 rounded transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </button>

                        <form method="POST" action="{{ route('auxiliar.tv_ads.destroy', $ad->id) }}" onsubmit="return confirm('¿Seguro que deseas eliminar este anuncio?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="bg-red-600 hover:bg-red-500 text-white p-1.5 rounded transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>

                    {{-- CONTROL DE VOLUMEN (SOLO PARA VIDEOS) --}}
                    @if($ad->media_type === 'VIDEO')
                    <div class="mt-4 pt-4 border-t border-aromas-tertiary/30">
                        <div class="flex justify-between items-center mb-1">
                            <label class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Volumen Video</label>
                            <span class="text-xs font-mono text-aromas-highlight font-bold" x-text="volume + '%'"></span>
                        </div>
                        <input type="range" min="0" max="100" x-model.number="volume"
                            @change="
                                fetch('{{ route('auxiliar.tv_ads.volume', $ad->id) }}', {
                                    method: 'POST',
                                    headers: { 
                                        'Content-Type': 'application/json', 
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 
                                        'Accept': 'application/json' 
                                    },
                                    body: JSON.stringify({ volume: volume })
                                }).catch(err => console.error('Error guardando volumen', err))
                            "
                            class="w-full h-2 bg-gray-700 rounded-lg appearance-none cursor-pointer accent-aromas-highlight">
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full py-12 text-center text-gray-500 bg-black/20 rounded-xl border border-dashed border-gray-700">
                <svg class="w-12 h-12 mx-auto text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"></path>
                </svg>
                <p class="text-lg font-bold">No hay anuncios registrados</p>
                <p class="text-sm mt-1">Usa el botón de arriba para subir el primero.</p>
            </div>
            @endforelse
        </div>

        {{-- MODAL DE SUBIDA --}}
        <div x-show="uploadModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="uploadModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" @click="uploadModalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="uploadModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-aromas-secondary rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-aromas-tertiary/20">

                    <form method="post" action="{{ route('auxiliar.tv_ads.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="bg-black/20 px-6 py-4 border-b border-aromas-tertiary/20 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-aromas-highlight uppercase tracking-wider">Subir Nuevo Anuncio</h3>
                            <button type="button" @click="uploadModalOpen = false" class="text-gray-400 hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Título (Referencia)</label>
                                <input type="text" name="title" required placeholder="Ej. Promoción de Verano" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight p-3">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Archivo (Imagen o Video MP4)</label>
                                <input type="file" id="create_media_file" name="media_file" required accept="image/*,video/mp4" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2.5 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-aromas-highlight file:text-aromas-main hover:file:bg-yellow-400 cursor-pointer border border-aromas-tertiary/30 rounded-lg bg-black/20 transition-colors">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Duración en pantalla (Segundos)</label>
                                <input type="number" id="create_duration" name="duration_seconds" value="15" min="5" max="120" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight p-3 transition-opacity">
                                <p id="create_duration_hint" class="text-xs text-aromas-tertiary mt-1">Aplica para imágenes. Los videos calcularán su tiempo.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Fecha Inicio (Opcional)</label>
                                <input type="datetime-local" name="start_date" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight p-3 [color-scheme:dark]">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Fecha Fin (Opcional)</label>
                                <input type="datetime-local" name="end_date" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight p-3 [color-scheme:dark]">
                            </div>
                        </div>

                        <div class="bg-black/20 px-6 py-4 border-t border-aromas-tertiary/20 flex justify-end gap-3">
                            <button type="button" @click="uploadModalOpen = false" class="px-6 py-2.5 rounded-lg text-sm font-bold text-gray-400 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="bg-aromas-highlight text-aromas-main font-bold py-2.5 px-6 rounded-lg shadow-lg hover:bg-white transition-colors uppercase tracking-wider">Publicar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL DE EDICIÓN --}}
        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/70 backdrop-blur-sm transition-opacity" @click="editModalOpen = false" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="editModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-aromas-secondary rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-aromas-tertiary/20">

                    <form method="post" x-bind:action="'/auxiliar/tv-ads/' + editData.id">
                        @csrf
                        @method('PUT')
                        <div class="bg-black/20 px-6 py-4 border-b border-aromas-tertiary/20 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-blue-400 uppercase tracking-wider">Editar Anuncio</h3>
                            <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-white">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Título (Referencia)</label>
                                <input type="text" name="title" x-model="editData.title" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-blue-400 focus:ring-1 focus:ring-blue-400 p-3">
                            </div>

                            <div class="sm:col-span-2" x-show="!editData.isVideo">
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Duración en pantalla (Segundos)</label>
                                <input type="number" name="duration_seconds" x-model="editData.duration" min="5" max="120" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-blue-400 focus:ring-1 focus:ring-blue-400 p-3">
                            </div>

                            <div class="sm:col-span-2" x-show="editData.isVideo">
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Duración en pantalla (Segundos)</label>
                                <input type="number" name="duration_seconds" x-model="editData.duration" readonly class="w-full bg-black/10 border border-aromas-tertiary/30 rounded-lg text-gray-400 p-3 cursor-not-allowed">
                                <p class="text-xs text-aromas-tertiary mt-1">La duración de los videos no puede modificarse manualmente.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Fecha Inicio (Opcional)</label>
                                <input type="datetime-local" name="start_date" x-model="editData.start" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-blue-400 focus:ring-1 focus:ring-blue-400 p-3 [color-scheme:dark]">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-300 uppercase tracking-wider mb-2">Fecha Fin (Opcional)</label>
                                <input type="datetime-local" name="end_date" x-model="editData.end" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-blue-400 focus:ring-1 focus:ring-blue-400 p-3 [color-scheme:dark]">
                            </div>
                        </div>

                        <div class="bg-black/20 px-6 py-4 border-t border-aromas-tertiary/20 flex justify-end gap-3">
                            <button type="button" @click="editModalOpen = false" class="px-6 py-2.5 rounded-lg text-sm font-bold text-gray-400 hover:text-white transition-colors">Cancelar</button>
                            <button type="submit" class="bg-blue-500 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg hover:bg-blue-400 transition-colors uppercase tracking-wider">Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- SCRIPT PARA CALCULAR DURACIÓN --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mediaInput = document.getElementById('create_media_file');
            if(mediaInput) {
                mediaInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    const durationInput = document.getElementById('create_duration');
                    const durationHint = document.getElementById('create_duration_hint');

                    if (!file) return;

                    if (file.type.startsWith('video/')) {
                        durationInput.readOnly = true;
                        durationInput.classList.add('opacity-50', 'cursor-not-allowed');
                        durationHint.innerHTML = '<span class="text-aromas-highlight">Calculando duración nativa del video...</span>';

                        const video = document.createElement('video');
                        video.preload = 'metadata';
                        video.onloadedmetadata = function() {
                            window.URL.revokeObjectURL(video.src);
                            const exactDuration = Math.round(video.duration);
                            durationInput.value = exactDuration;
                            durationHint.innerHTML = `<span class="text-green-400 font-bold">Duración calculada: ${exactDuration}s</span>`;
                        };
                        video.src = URL.createObjectURL(file);
                    } else {
                        durationInput.readOnly = false;
                        durationInput.classList.remove('opacity-50', 'cursor-not-allowed');
                        durationInput.value = 15;
                        durationHint.textContent = 'Aplica para imágenes. Los videos calcularán su tiempo.';
                    }
                });
            }
        });
    </script>

    {{-- SORTABLE Y FETCH AJAX --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var grid = document.getElementById('ads-grid');
            if (grid) {
                new Sortable(grid, {
                    animation: 150,
                    ghostClass: 'opacity-50',
                    handle: '.drag-handle',
                    onEnd: function() {
                        // 1. Recolectar el nuevo orden
                        let newOrder = [];
                        document.querySelectorAll('.tv-ad-card').forEach((card, index) => {
                            newOrder.push(card.getAttribute('data-id'));
                            // 2. Actualizar números visuales
                            card.querySelector('.order-badge').innerText = index + 1;
                        });

                        // 3. Enviar a Laravel
                        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                        if (csrfMeta) {
                            fetch('{{ route('auxiliar.tv_ads.reorder') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfMeta.content,
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({
                                    order: newOrder
                                })
                            }).catch(error => console.error('Error guardando el orden:', error));
                        } else {
                            console.error('No se encontró el CSRF token en el Layout.');
                        }
                    }
                });
            }
        });
    </script>
</x-auxiliar-layout>