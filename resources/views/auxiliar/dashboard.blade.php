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
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
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

        {{-- GRID DE ANUNCIOS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
            @forelse($ads as $ad)
                @php
                    $isRealActive = $ad->is_active && !$ad->is_expired;
                    $badgeColor = $isRealActive ? 'bg-aromas-success' : 'bg-aromas-error';
                    $badgeText = $isRealActive ? 'ACTIVO' : ($ad->is_expired ? 'EXPIRADO' : 'INACTIVO');
                    $buttonText = ($ad->is_expired || !$ad->is_active) ? 'REACTIVAR' : 'APAGAR';
                @endphp

                <div class="bg-aromas-secondary rounded-xl border {{ $isRealActive ? 'border-aromas-success/30' : 'border-aromas-tertiary/20' }} overflow-hidden flex flex-col shadow-lg hover:shadow-xl transition-all group">
                    
                    {{-- Badge Status --}}
                    <div class="absolute top-3 right-3 z-10">
                        <span class="{{ $badgeColor }} text-white text-[10px] font-black px-2.5 py-1 rounded shadow-md uppercase tracking-wider">
                            {{ $badgeText }}
                        </span>
                    </div>

                    {{-- Media --}}
                    <div class="aspect-video bg-black relative flex items-center justify-center overflow-hidden">
                        @if($ad->isVideo())
                            <div class="absolute bg-black/70 text-white text-[10px] font-bold px-2 py-1 rounded border border-white/20 uppercase tracking-wider z-10 pointer-events-none">VIDEO</div>
                            <video src="{{ $ad->media_url }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" muted preload="metadata"></video>
                        @else
                            <img src="{{ $ad->media_url }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity" alt="{{ $ad->title }}">
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="p-5 flex-grow border-b border-aromas-tertiary/20">
                        <h3 class="text-white font-bold text-lg truncate mb-1">{{ $ad->title }}</h3>
                        <p class="text-xs text-aromas-highlight font-bold uppercase tracking-wider mb-4">Duración: {{ $ad->duration_seconds }}s</p>
                        
                        <div class="space-y-2 text-xs">
                            <div class="flex justify-between items-center border-b border-aromas-tertiary/10 pb-1.5">
                                <span class="text-gray-400 font-bold">Inicia:</span>
                                <span class="text-white">{{ $ad->start_date ? $ad->start_date->format('d/m/Y H:i') : 'Inmediato' }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-400 font-bold">Finaliza:</span>
                                <span class="{{ $ad->is_expired ? 'text-aromas-error font-bold' : 'text-white' }}">
                                    {{ $ad->end_date ? $ad->end_date->format('d/m/Y H:i') : 'Sin fecha límite' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="p-4 bg-black/10 flex gap-2">
                        {{-- Toggle --}}
                        <form action="{{ route('auxiliar.tv_ads.toggle', $ad) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full py-2.5 rounded-lg text-xs font-bold uppercase tracking-widest transition-colors border {{ $isRealActive ? 'bg-black/20 text-aromas-highlight border-aromas-highlight/30 hover:bg-aromas-highlight/10' : 'bg-aromas-success/10 text-aromas-success border-aromas-success/30 hover:bg-aromas-success/20' }}">
                                {{ $buttonText }}
                            </button>
                        </form>

                        {{-- Editar --}}
                        <button @click="
                                editData = {
                                    id: '{{ $ad->id }}',
                                    title: '{{ addslashes($ad->title) }}',
                                    duration: {{ $ad->duration_seconds }},
                                    start: '{{ $ad->start_date ? $ad->start_date->format('Y-m-d\TH:i') : '' }}',
                                    end: '{{ $ad->end_date ? $ad->end_date->format('Y-m-d\TH:i') : '' }}',
                                    isVideo: {{ $ad->isVideo() ? 'true' : 'false' }}
                                }; 
                                editModalOpen = true;
                            " 
                            class="p-2.5 rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/30 hover:bg-blue-500 hover:text-white transition-colors" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>

                        {{-- Eliminar --}}
                        <form action="{{ route('auxiliar.tv_ads.destroy', $ad) }}" method="POST" onsubmit="return confirm('¿Eliminar este anuncio permanentemente?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2.5 rounded-lg bg-aromas-error/10 text-aromas-error border border-aromas-error/30 hover:bg-aromas-error hover:text-white transition-colors" title="Eliminar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center text-aromas-tertiary">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <p class="text-xl font-bold text-gray-300">No hay anuncios para mostrar.</p>
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
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
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

    {{-- Script para calcular duración del video en el modal de SUBIDA --}}
    <script>
        document.getElementById('create_media_file').addEventListener('change', function(e) {
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
                    durationHint.innerHTML = `<span class="text-aromas-success">Duración calculada: ${exactDuration}s</span>`;
                };
                video.src = URL.createObjectURL(file);
            } else {
                durationInput.readOnly = false;
                durationInput.classList.remove('opacity-50', 'cursor-not-allowed');
                durationInput.value = 15;
                durationHint.textContent = 'Aplica para imágenes. Los videos calcularán su tiempo.';
            }
        });
    </script>
</x-auxiliar-layout>