<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            Gestión de Publicidad (TV)
        </h2>
    </x-slot>

    <div class="py-12 bg-aromas-main min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
                <div class="mb-4 bg-aromas-success/10 border-l-4 border-aromas-success text-aromas-success p-4 rounded shadow-lg">
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 bg-aromas-error/10 border-l-4 border-aromas-error text-aromas-error p-4 rounded shadow-lg">
                    <ul class="list-disc list-inside text-sm font-bold">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="bg-aromas-secondary shadow-xl rounded-lg overflow-hidden mb-8 border border-aromas-tertiary/20">
                <div class="bg-black/20 px-6 py-4 border-b border-aromas-tertiary/20">
                    <h3 class="text-lg font-bold text-aromas-highlight uppercase tracking-wider">Subir Nuevo Anuncio</h3>
                </div>
                
                <form action="{{ route('admin.tv_ads.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-300 uppercase tracking-wider mb-2">Título (Referencia)</label>
                            <input type="text" name="title" required placeholder="Ej. Promoción de Verano" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white placeholder-gray-600 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 uppercase tracking-wider mb-2">Archivo (Imagen o Video MP4)</label>
                            <input type="file" name="media_file" required accept="image/*,video/mp4" class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-bold file:bg-aromas-highlight file:text-aromas-main hover:file:bg-yellow-400 cursor-pointer border border-aromas-tertiary/30 rounded-lg bg-black/20 transition-colors">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-300 uppercase tracking-wider mb-2">Duración en pantalla (Segundos)</label>
                            <input type="number" name="duration_seconds" value="15" min="5" max="120" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors">
                            <p class="text-xs text-aromas-tertiary mt-1">Aplica para imágenes. Los videos durarán su tiempo nativo si es posible.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-300 uppercase tracking-wider mb-2">Fecha Inicio (Opcional)</label>
                                <input type="date" name="start_date" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors color-scheme-dark">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-300 uppercase tracking-wider mb-2">Fecha Fin (Opcional)</label>
                                <input type="date" name="end_date" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors color-scheme-dark">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-aromas-highlight text-aromas-main font-bold py-3 px-8 rounded-lg shadow-lg hover:bg-white transition-colors uppercase tracking-wider">
                            Subir y Activar
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-aromas-secondary shadow-xl rounded-lg overflow-hidden border border-aromas-tertiary/20">
                <div class="bg-black/20 px-6 py-4 border-b border-aromas-tertiary/20">
                    <h3 class="text-lg font-bold text-gray-300 uppercase tracking-wider">Anuncios en el Sistema</h3>
                </div>

                <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($ads as $ad)
                        <div class="bg-black/30 rounded-xl overflow-hidden border {{ $ad->is_active ? 'border-aromas-success/50' : 'border-aromas-error/50' }} flex flex-col relative group shadow-md hover:shadow-xl transition-shadow">
                            
                            <div class="h-48 bg-black flex items-center justify-center relative overflow-hidden">
                                @if($ad->media_type === 'VIDEO')
                                    <video src="{{ Storage::url($ad->media_path) }}" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition-opacity" muted loop playsinline></video>
                                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                        <span class="bg-black/80 text-white text-xs px-2 py-1 rounded border border-white/20 font-bold tracking-wider">VIDEO</span>
                                    </div>
                                @else
                                    <img src="{{ Storage::url($ad->media_path) }}" alt="{{ $ad->title }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                                @endif

                                <div class="absolute top-2 right-2">
                                    @if($ad->is_active)
                                        <span class="bg-aromas-success text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider shadow">Activo</span>
                                    @else
                                        <span class="bg-aromas-error text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider shadow">Inactivo</span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-4 flex-1 flex flex-col">
                                <h4 class="text-lg font-bold text-white mb-1 truncate">{{ $ad->title }}</h4>
                                <p class="text-xs text-aromas-tertiary mb-3 uppercase tracking-wider">Duración: {{ $ad->duration_seconds }}s</p>
                                
                                <div class="mt-auto space-y-2 text-xs font-medium text-gray-400">
                                    <div class="flex justify-between border-b border-aromas-tertiary/20 pb-1">
                                        <span>Inicia:</span>
                                        <span class="text-gray-300">{{ $ad->start_date ? \Carbon\Carbon::parse($ad->start_date)->format('d/m/Y') : 'Inmediato' }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-aromas-tertiary/20 pb-1">
                                        <span>Finaliza:</span>
                                        <span class="text-gray-300">{{ $ad->end_date ? \Carbon\Carbon::parse($ad->end_date)->format('d/m/Y') : 'Sin fecha límite' }}</span>
                                    </div>
                                </div>

                                <div class="mt-5 flex gap-2">
                                    <form action="{{ route('admin.tv_ads.toggle', $ad->id) }}" method="POST" class="flex-1">
                                        @csrf
                                        <button type="submit" class="w-full py-2 rounded-lg text-sm font-bold transition-colors border {{ $ad->is_active ? 'bg-black/20 text-aromas-highlight border-aromas-highlight/30 hover:bg-aromas-highlight/10' : 'bg-aromas-success/10 text-aromas-success border-aromas-success/30 hover:bg-aromas-success/20' }}">
                                            {{ $ad->is_active ? 'APAGAR' : 'ENCENDER' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.tv_ads.destroy', $ad->id) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este archivo del servidor? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-aromas-error/10 text-aromas-error border border-aromas-error/30 hover:bg-aromas-error hover:text-white transition-colors" title="Eliminar">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="col-span-full py-10 text-center text-aromas-tertiary">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="text-lg">No hay ningún anuncio en el sistema.</p>
                            <p class="text-sm mt-1">Sube el primero usando el formulario de arriba.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
    
    <style>
        /* Pequeño hack para que el icono del calendario en los inputs de fecha se vea claro sobre fondo oscuro */
        .color-scheme-dark::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.7;
            cursor: pointer;
        }
    </style>
</x-admin-layout>