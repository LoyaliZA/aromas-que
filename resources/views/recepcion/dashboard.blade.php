<x-tablet-layout>
    {{-- Inicialización de Alpine con el nuevo path y configuración de rutas --}}
    <div x-data="deliveryApp({ 
            queueCount: {{ $peopleInQueue }}, 
            routes: { 
                dashboard: '{{ route('recepcion.dashboard') }}', 
                queueList: '{{ route('recepcion.queue.list') }}' 
            } 
        })" x-init="init()" class="pb-10 relative">

        {{-- ========================================================== --}}
        {{-- SECCIÓN 1: GESTIÓN DE FILA Y TURNOS                        --}}
        {{-- ========================================================== --}}
        <div class="mb-4 flex items-center gap-3">
            <div class="p-2 bg-aromas-highlight/20 rounded-lg text-aromas-highlight shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h2 class="text-xl font-black text-white uppercase tracking-widest">Gestión de Fila</h2>
        </div>

        <div class="mb-10 grid grid-cols-1 md:grid-cols-2 gap-4">
            <button @click="openQueueModal()" class="bg-aromas-highlight text-aromas-main rounded-xl p-4 shadow-lg flex items-center justify-between group transform transition-all hover:scale-[1.01] hover:shadow-[0_0_15px_rgba(253,201,116,0.4)] border-2 border-transparent hover:border-white/20">
                <div class="flex items-center gap-4">
                    <div class="bg-aromas-main/10 p-3 rounded-lg text-aromas-main">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h2 class="text-xl font-bold leading-none">Ticket de Turno</h2>
                        <p class="text-aromas-main/70 text-sm font-medium mt-1">Ingresar cliente</p>
                    </div>
                </div>
                <div class="bg-aromas-main text-aromas-highlight px-4 py-2 rounded-lg text-center shadow-inner">
                    <span class="block text-[10px] uppercase font-bold tracking-wider opacity-70">En Fila</span>
                    <span class="text-2xl font-bold leading-none" x-text="queueCount">0</span>
                </div>
            </button>

            <button @click="openQueueListModal()" class="bg-gray-800 border border-gray-700 hover:border-aromas-highlight text-white rounded-xl p-4 shadow-lg flex items-center justify-between group transform transition-all hover:scale-[1.01]">
                <div class="flex items-center gap-4">
                    <div class="bg-gray-900 p-3 rounded-lg text-gray-400 group-hover:text-aromas-highlight transition-colors border border-gray-700">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                        </svg>
                    </div>
                    <div class="text-left">
                        <h2 class="text-xl font-bold leading-none text-white group-hover:text-aromas-highlight transition-colors">Ver Fila Actual</h2>
                        <p class="text-gray-400 text-sm font-medium mt-1">Revisar clientes y abandonos</p>
                    </div>
                </div>
                <div>
                    <svg class="w-6 h-6 text-gray-600 group-hover:text-aromas-highlight group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </button>
        </div>

        {{-- ========================================================== --}}
        {{-- SECCIÓN 2: PAQUETES EN RESGUARDO / EN CAMINO               --}}
        {{-- ========================================================== --}}
        <div class="mb-4 flex items-center gap-3">
            <div class="p-2 bg-blue-500/20 rounded-lg text-blue-400 shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h2 class="text-xl font-black text-white uppercase tracking-widest">Paquetes en Resguardo</h2>
        </div>

        {{-- NUEVA SECCIÓN: PAQUETES EN CAMINO (Auditados desde CEDIS) --}}
        @if($incomingPickups->count() > 0)
        <div class="mb-8 bg-blue-900/20 border border-blue-500/30 rounded-xl p-4 shadow-lg overflow-hidden">
            <h3 class="text-blue-400 font-bold uppercase tracking-widest text-xs flex items-center mb-4">
                <svg class="w-5 h-5 mr-2 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                </svg>
                En camino a sucursal ({{ $incomingPickups->count() }})
            </h3>
            <div class="flex overflow-x-auto gap-4 pb-2">
                @foreach($incomingPickups as $incoming)
                <div class="bg-gray-800 border border-gray-700 rounded-lg p-3 min-w-[250px] shrink-0">
                    <div class="flex justify-between items-start mb-2">
                        <span class="text-xs font-mono text-gray-400">#{{ $incoming->ticket_folio }}</span>
                        <span class="text-[10px] bg-blue-500/20 text-blue-300 px-2 py-0.5 rounded uppercase font-bold">{{ $incoming->department }}</span>
                    </div>
                    <p class="text-white font-bold text-sm truncate">{{ $incoming->client_name }}</p>
                    <p class="text-xs text-gray-400 mt-1">Cajas: <span class="text-white font-bold">{{ $incoming->pieces }}</span></p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="bg-aromas-secondary rounded-xl p-4 shadow-md border border-aromas-tertiary/20 mb-6 sticky top-2 z-30">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-aromas-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model.debounce.500ms="search" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg py-3 pl-10 pr-4 text-white placeholder-gray-500 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all" placeholder="Buscar folio, cliente o receptor...">
                </div>

                <div class="contents">
                    <select id="deptFilter" @change="fetchData(search)" class="bg-black/20 border border-aromas-tertiary/30 text-white rounded-lg px-4 py-3 focus:border-aromas-highlight cursor-pointer">
                        <option value="ALL">Todos los Deptos</option>
                        <option value="BELLAROMA">Bellaroma</option>
                        <option value="CALLCENTER">Call Center</option>
                        <option value="AROMAS">Aromas</option>
                    </select>
                    <input type="hidden" id="statusFilter" value="ALL">
                </div>
            </div>
        </div>

        {{-- Alertas Flash --}}
        @if(session('new_turn'))
        {{-- Modal de Turno Asignado (Mismo diseño que ya tenías) --}}
        @elseif(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="mb-6 bg-green-500/10 border-l-4 border-green-500 text-green-400 p-4 rounded shadow-lg flex items-center animate-fade-in-down">
            <span class="font-bold">{{ session('success') }}</span>
        </div>
        @endif

        {{-- CONTENEDOR DE CARDS --}}
        <div id="results-container">
            @include('recepcion.partials.card-grid', ['pickups' => $pickups])
        </div>

        <div class="mt-6">
            {{ $pickups->links() }}
        </div>

        {{-- ========================================================== --}}
        {{-- MODALES (Carga de componentes Alpine)                      --}}
        {{-- ========================================================== --}}

        {{-- ========================================================== --}}
        {{-- MODALES                                                 --}}
        {{-- ========================================================== --}}

        {{-- MODAL 1: CONFIRMAR ENTREGA --}}
        <div x-show="showDeliveryModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto" x-transition>
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="closeModal()"></div>
            <div class="bg-aromas-secondary w-full max-w-5xl rounded-2xl shadow-2xl border border-aromas-tertiary/30 relative z-10 flex flex-col my-auto max-h-[95vh] overflow-y-auto">
                {{-- Contenido de entrega (Se mantiene idéntico al anterior) --}}
                <div class="bg-gray-900 p-6 border-b border-gray-700 flex justify-between items-center sticky top-0 z-20">
                    <div>
                        <h2 class="text-3xl font-black text-white tracking-wider uppercase">Confirmar Entrega</h2>
                        <p class="text-base text-gray-400 mt-1">Folio de Resguardo: <span class="font-bold text-aromas-highlight font-mono text-lg" x-text="pickup.ticket_folio"></span></p>
                    </div>
                    <button @click="closeModal()" class="text-gray-500 hover:text-white p-3 bg-gray-800 hover:bg-red-900/50 hover:text-red-400 rounded-full transition-colors">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="deliveryForm" method="POST" enctype="multipart/form-data" :action="'/recepcion/confirm/' + pickup.id" class="p-8" @submit.prevent="submitDelivery">
                    @csrf @method('PUT')
                    <input type="hidden" name="signature" x-model="signatureData">

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                        <div class="space-y-6">
                            <div class="space-y-4 bg-gray-900 border border-gray-700 p-6 rounded-2xl h-full">
                                <h3 class="text-lg font-bold text-aromas-highlight uppercase tracking-wider mb-4 border-b border-gray-700 pb-2">Datos de Recepción</h3>
                                <label class="flex items-center justify-between p-4 rounded-xl cursor-pointer transition-colors" :class="isThirdParty ? 'bg-aromas-highlight/10 border border-aromas-highlight/30' : 'bg-gray-800 border border-gray-700'">
                                    <div class="flex items-center gap-4">
                                        <input type="checkbox" name="is_third_party" value="1" x-model="isThirdParty" class="w-7 h-7 rounded border-gray-500 text-aromas-highlight focus:ring-aromas-highlight bg-gray-900 cursor-pointer">
                                        <div>
                                            <span class="block text-lg font-bold text-white">¿Recoge otra persona?</span>
                                            <span class="block text-sm text-gray-400">Marcar si no es el titular</span>
                                        </div>
                                    </div>
                                </label>
                                <div x-show="isThirdParty" x-transition class="pt-4">
                                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Nombre completo de quien recibe</label>
                                    <input type="text" name="receiver_name" x-model="receiverName" placeholder="Ej. Juan Pérez - INE" class="w-full bg-gray-800 border border-gray-600 rounded-xl px-5 py-4 text-lg text-white focus:border-aromas-highlight focus:ring-aromas-highlight shadow-inner">
                                </div>
                                <div x-show="!isThirdParty" class="p-5 bg-blue-900/20 border border-blue-500/20 rounded-xl mt-4">
                                    <p class="text-sm text-blue-400 uppercase font-bold tracking-wider mb-1">Entregando a Titular Registrado:</p>
                                    <p class="text-2xl font-bold text-white" x-text="pickup.client_name"></p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="bg-gray-900 border-2 border-dashed border-gray-600 hover:border-aromas-highlight/50 rounded-2xl p-6 transition-colors">
                                <label class="block text-sm font-bold text-aromas-highlight uppercase tracking-wider mb-4 flex items-center gap-2">Evidencia Fotográfica *</label>
                                <input type="file" name="evidence_file" id="evidence_file" accept="image/*" capture="environment" class="sr-only" @change="handleEvidenceChange">
                                <div class="relative">
                                    <label for="evidence_file" x-show="!evidencePreview" class="flex flex-col items-center justify-center gap-3 w-full h-32 bg-gray-800 rounded-xl border border-gray-700 cursor-pointer hover:bg-gray-700 transition-all group shadow-inner">
                                        <div class="p-3 bg-gray-900 rounded-full text-gray-400 group-hover:text-aromas-highlight group-hover:scale-110 transition-all">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <span class="font-bold text-white text-lg">Tocar para tomar foto</span>
                                    </label>
                                    <div x-show="evidencePreview" x-cloak class="relative h-32 rounded-xl overflow-hidden border-2 border-aromas-highlight shadow-lg">
                                        <img :src="evidencePreview" class="w-full h-full object-cover">
                                        <button type="button" @click="removeEvidence()" class="absolute top-2 right-2 p-2 rounded-lg bg-red-500 text-white hover:bg-red-600 shadow-xl">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Observaciones / Estado de entrega</label>
                                <textarea name="notes" rows="2" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-5 py-4 text-white focus:border-aromas-highlight focus:ring-aromas-highlight shadow-inner resize-none text-lg" placeholder="Añadir notas..."></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN DE FIRMA DIGITAL CON VISTA PREVIA Y FULLSCREEN --}}
                    <div class="bg-gray-800/80 border border-gray-700 rounded-3xl p-6 shadow-inner mb-8">

                        <div class="flex items-center justify-between mb-4">
                            <label class="text-xl font-bold text-aromas-highlight uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg> Firma Digital *
                            </label>
                            <button type="button" @click="$store.firmaStore.isFullScreen = true; setTimeout(() => window.dispatchEvent(new Event('resize')), 200);" class="text-sm font-black text-aromas-main bg-aromas-highlight hover:bg-white px-5 py-2.5 rounded-xl transition-colors shadow-[0_0_15px_rgba(253,201,116,0.3)] uppercase tracking-widest active:scale-95">
                                Firmar Ahora
                            </button>
                        </div>

                        {{-- CAJA DE VISTA PREVIA (Mini Panel Blanco) --}}
                        <div class="relative bg-white rounded-2xl overflow-hidden border-4 border-gray-400 h-48 flex items-center justify-center shadow-inner cursor-not-allowed">
                            <template x-if="$store.firmaStore.preview">
                                {{-- Aquí aplicamos w-full h-full object-contain para que crezca al máximo posible --}}
                                <img :src="$store.firmaStore.preview" class="w-full h-full object-contain p-4">
                            </template>
                            <template x-if="!$store.firmaStore.preview">
                                <div class="text-center text-gray-400 flex flex-col items-center">
                                    <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                    <span class="font-bold uppercase tracking-widest text-sm opacity-80">Presione 'Firmar Ahora'</span>
                                </div>
                            </template>
                        </div>

                        {{-- CANVAS A PANTALLA COMPLETA (Oculto usando opacidad para preservar la referencia) --}}
                        <div :class="$store.firmaStore.isFullScreen ? 'opacity-100 z-[150] pointer-events-auto' : 'opacity-0 -z-50 pointer-events-none'" class="fixed inset-0 bg-gray-200 flex flex-col transition-opacity duration-300">

                            {{-- Cabecera del Canvas Fullscreen --}}
                            <div class="bg-gray-900 p-6 flex justify-between items-center shadow-2xl z-10">
                                <div class="flex items-center gap-4">
                                    {{-- BOTÓN CERRAR (Nuevo) --}}
                                    <button type="button" @click="$store.firmaStore.isFullScreen = false" class="p-3 bg-gray-800 hover:bg-red-500 text-gray-400 hover:text-white rounded-full transition-colors" title="Cerrar">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>

                                    <h3 class="text-2xl md:text-3xl font-black text-white tracking-widest uppercase flex items-center gap-3">
                                        <svg class="w-8 h-8 text-aromas-highlight hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Firma del Cliente
                                    </h3>
                                </div>

                                <div class="flex gap-3 md:gap-4">
                                    <button type="button" @click="clearPad(); $store.firmaStore.preview = null;" class="px-5 py-3 md:px-6 md:py-4 rounded-xl bg-red-500/20 text-red-400 border border-red-500/50 font-bold uppercase tracking-widest hover:bg-red-500 hover:text-white transition-colors text-sm md:text-base">
                                        Borrar
                                    </button>
                                    <button type="button" @click="
    if(!isPadEmpty) { 
        // 1. Mandamos el canvas a recortar
        const croppedSignature = cropSignatureCanvas($refs.signature_canvas); 
        
        // 2. Guardamos la imagen recortada
        $store.firmaStore.preview = croppedSignature; 
        signatureData = croppedSignature; 
        
        // 3. Cerramos pantalla completa
        $store.firmaStore.isFullScreen = false; 
    } else { 
        alert('Por favor, firme el documento antes de confirmar.'); 
    }
" class="px-6 py-3 md:px-8 md:py-4 rounded-xl bg-green-500 text-white font-black uppercase tracking-widest shadow-lg shadow-green-500/30 hover:bg-green-400 transition-all active:scale-95 text-sm md:text-base">
                                        Confirmar
                                    </button>
                                </div>
                            </div>

                            {{-- Área de firma --}}
                            <div class="relative flex-1 bg-white cursor-crosshair">
                                <canvas x-ref="signature_canvas" class="absolute inset-0 w-full h-full touch-none" @end="isPadEmpty = false"></canvas>
                                <div x-show="isPadEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20">
                                    <span class="text-4xl md:text-6xl font-black text-gray-400 uppercase tracking-widest opacity-50">Firmar Aquí</span>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-700 flex flex-col md:flex-row-reverse gap-6">
                        <button type="submit" class="w-full md:w-auto px-10 py-6 rounded-2xl bg-aromas-highlight text-aromas-main font-black text-xl tracking-widest uppercase hover:bg-white transition-all shadow-[0_0_30px_rgba(253,201,116,0.25)] flex items-center justify-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg> CONFIRMAR ENTREGA
                        </button>
                        <button type="button" @click="closeModal()" class="w-full md:w-auto px-10 py-6 rounded-2xl border-2 border-gray-600 text-gray-300 font-bold text-xl hover:bg-gray-700 hover:text-white transition-colors uppercase tracking-widest">CANCELAR</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: TICKET DE TURNO (NUEVO DISEÑO CON REPRESENTANTE) --}}
        <div x-show="showQueueModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="closeQueueModal()"></div>

            <div class="bg-aromas-secondary w-full max-w-md rounded-xl shadow-2xl border border-aromas-highlight/30 flex flex-col relative z-10 overflow-visible">
                <div class="bg-aromas-highlight/10 p-4 border-b border-aromas-tertiary/20 rounded-t-xl flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nuevo Ticket
                    </h2>
                    <button type="button" @click="closeQueueModal()" class="text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('recepcion.queue.add') }}" method="POST" class="p-6 space-y-6" @submit="if(!validateQueueForm($event)) $event.preventDefault()">
                    @csrf

                    {{-- SWITCH DE CLIENTE NUEVO --}}
                    <label class="flex items-center justify-between cursor-pointer group bg-gray-800/50 border border-gray-700 p-3 rounded-lg hover:bg-gray-800 transition-colors">
                        <span class="text-sm text-gray-300 font-bold">Es cliente nuevo (Sin registro previo)</span>
                        <div class="relative inline-flex items-center">
                            <input type="checkbox" name="is_new_customer" value="1" x-model="isNewCustomerQueue" @change="if(isNewCustomerQueue) clearSelectedCustomer()" class="sr-only peer">
                            <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-aromas-highlight"></div>
                        </div>
                    </label>

                    {{-- SECCIÓN: CLIENTE (Búsqueda o Seleccionado) --}}
                    <div x-show="!isNewCustomerQueue">
                        <label class="block text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">BUSCAR CLIENTE REGISTRADO</label>

                        <div class="relative" x-show="!selectedCustomerObj">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" x-model="clientSearchQuery" @input.debounce.300ms="searchCustomers" @focus="showClientDropdown = true" @click.away="showClientDropdown = false" autocomplete="off"
                                class="w-full bg-gray-900 border border-gray-700 rounded-lg py-3 pl-10 pr-4 text-white placeholder-gray-500 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all"
                                placeholder="Ingresar número de cliente o nombre...">
                            <input type="hidden" name="customer_id" x-model="selectedCustomerId">

                            <div x-show="showClientDropdown && clientSearchResults.length > 0" class="absolute z-[100] w-full mt-1 bg-gray-800 border border-gray-700 rounded-lg shadow-2xl max-h-60 overflow-y-auto" style="display: none;">
                                <template x-for="customer in clientSearchResults" :key="customer.id">
                                    <div @click="selectCustomer(customer)" class="px-4 py-3 hover:bg-aromas-highlight/20 cursor-pointer border-b border-gray-700 last:border-0 flex justify-between items-center transition-colors">
                                        <div>
                                            <div class="text-white font-bold" x-text="customer.name"></div>
                                            <div class="text-xs text-gray-400" x-text="customer.customer_number ? '# ' + customer.customer_number : 'Registrado'"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div x-show="selectedCustomerObj" style="display: none;" class="bg-gray-800 border border-gray-600 rounded-xl p-4">
                            <div class="flex items-center justify-between border-b border-gray-700 pb-3 mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="bg-gray-700 p-2 rounded-full text-gray-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-white font-bold" x-text="selectedCustomerObj ? selectedCustomerObj.name : ''"></p>
                                    </div>
                                </div>
                                <button type="button" @click="clearSelectedCustomer()" class="text-gray-400 hover:text-red-400 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>

                            <label class="flex items-center justify-between cursor-pointer group">
                                <span class="text-sm text-gray-300 font-medium group-hover:text-white transition-colors">¿Asiste alguien más a nombre del cliente?</span>
                                <div class="relative inline-flex items-center">
                                    <input type="checkbox" name="is_third_party" value="1" x-model="isThirdPartyQueue" class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-aromas-highlight"></div>
                                </div>
                            </label>

                            <div x-show="isThirdPartyQueue" x-transition class="mt-3">
                                <input type="text" name="representative_name" x-model="representativeNameQueue" placeholder="Nombre de quien asiste..."
                                    class="w-full bg-gray-900 border border-gray-600 rounded-lg px-3 py-2 text-sm text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight">
                            </div>
                        </div>
                    </div>

                    {{-- INPUT MANUAL (SOLO SI ES NUEVO) --}}
                    <div x-show="isNewCustomerQueue" style="display: none;" x-transition>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider font-bold mb-2">NOMBRE DEL CLIENTE NUEVO</label>
                        <input type="text" name="new_client_name" x-model="newClientName" placeholder="Escribe el nombre completo..."
                            class="w-full bg-gray-900 border border-aromas-highlight/50 rounded-lg py-3 px-4 text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all">
                    </div>

                    {{-- CHECKBOX DISCAPACIDAD --}}
                    <label class="flex items-center gap-3 p-3 bg-gray-800/50 border border-gray-700 rounded-lg cursor-pointer hover:bg-gray-800 transition-colors">
                        <input type="checkbox" name="has_disability" value="1" x-model="hasDisabilityQueue" class="w-5 h-5 rounded border-gray-600 text-aromas-highlight focus:ring-aromas-highlight bg-gray-900">
                        <span class="text-white font-medium text-sm">Presenta Discapacidad</span>
                    </label>

                    {{-- SECCIÓN: DESTINO --}}
                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider font-bold mb-3">DESTINO</label>
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <input type="hidden" name="service_type" x-model="queueType">

                            <button type="button" @click="queueType = 'SALES'"
                                :class="queueType === 'SALES' ? 'bg-aromas-highlight text-aromas-main ring-2 ring-aromas-highlight ring-offset-2 ring-offset-gray-900' : 'bg-black/20 text-gray-400 hover:bg-white/5'"
                                class="p-3 rounded-xl border border-transparent flex flex-col items-center justify-center transition-all h-24">
                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <span class="font-bold text-sm">VENTAS</span>
                            </button>

                            <button type="button" @click="queueType = 'CASHIER'"
                                :class="queueType === 'CASHIER' ? 'bg-green-500 text-white ring-2 ring-green-500 ring-offset-2 ring-offset-gray-900' : 'bg-black/20 text-gray-400 hover:bg-white/5'"
                                class="p-3 rounded-xl border border-transparent flex flex-col items-center justify-center transition-all h-24">
                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08-.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-bold text-sm">SOLO CAJA</span>
                            </button>
                        </div>
                    </div>

                    {{-- BOTONES INFERIORES --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="closeQueueModal()" class="flex-1 py-3 rounded-lg border border-gray-600 text-gray-300 font-bold hover:bg-gray-800 transition-colors">CANCELAR</button>
                        <button type="submit" class="flex-1 py-3 rounded-lg bg-aromas-highlight text-aromas-main font-bold shadow-lg hover:bg-white transition-all transform hover:-translate-y-1">REGISTRAR</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 3: GESTIÓN DE LA FILA --}}
        <div x-show="showQueueListModal" style="display: none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showQueueListModal = false"></div>

            <div class="bg-aromas-secondary w-full max-w-2xl rounded-2xl shadow-2xl border border-gray-700 flex flex-col relative z-10 max-h-[85vh]">
                <div class="p-6 border-b border-gray-700 flex justify-between items-center bg-gray-900/50 rounded-t-2xl">
                    <div>
                        <h2 class="text-2xl font-black text-white uppercase tracking-wider flex items-center gap-3">
                            <svg class="w-7 h-7 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Fila Actual
                        </h2>
                        <p class="text-sm text-gray-400 mt-1">Total esperando: <strong class="text-white" x-text="queueCount"></strong></p>
                    </div>
                    <button @click="showQueueListModal = false" class="text-gray-400 hover:text-white p-2 rounded-lg bg-gray-800"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 bg-black/20">
                    <div x-show="waitingClients.length === 0" class="text-center py-10 flex flex-col items-center">
                        <div class="bg-gray-800 p-4 rounded-full mb-3"><svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg></div>
                        <p class="text-gray-400 font-bold text-lg">No hay clientes esperando actualmente.</p>
                    </div>

                    <div class="space-y-4">
                        <template x-for="client in waitingClients" :key="client.id">
                            <div class="bg-gray-800/80 border border-gray-700 rounded-xl p-4 flex justify-between items-center hover:border-aromas-tertiary transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="bg-gray-900 border border-gray-700 px-4 py-3 rounded-lg text-center min-w-[80px]">
                                        <span class="text-2xl font-black text-aromas-highlight leading-none" x-text="client.turn_number"></span>
                                    </div>
                                    <div>
                                        <h4 class="text-lg font-bold text-white leading-tight" x-text="client.client_name"></h4>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                                                :class="client.service_type === 'SALES' ? 'bg-blue-500/20 text-blue-300' : 'bg-green-500/20 text-green-300'"
                                                x-text="client.service_type === 'SALES' ? 'Ventas' : 'Caja'"></span>

                                            <span x-show="client.customer && client.customer.client_type === 'VIP'" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-aromas-highlight text-aromas-main">VIP</span>
                                        </div>
                                    </div>
                                </div>

                                <button @click="openAbandonModal(client)" class="flex items-center gap-2 bg-red-500/10 text-red-400 border border-red-500/30 px-4 py-3 rounded-lg text-sm font-bold hover:bg-red-500 hover:text-white transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zm11-2l-4-4m0 0l-4 4m4-4v12"></path>
                                    </svg>
                                    <span class="hidden md:inline">Abandonó Fila</span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL 4: MOTIVO DE ABANDONO --}}
        <div x-show="showAbandonModal" style="display: none;" class="fixed inset-0 z-[80] flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showAbandonModal = false"></div>

            <div class="bg-gray-900 w-full max-w-sm rounded-2xl shadow-2xl border border-gray-700 flex flex-col relative z-10">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-white mb-2 text-center">Motivo de Abandono</h3>
                    <p class="text-gray-400 text-sm text-center mb-6">¿Por qué se retiró <strong class="text-aromas-highlight" x-text="abandoningClient ? abandoningClient.client_name : ''"></strong>?</p>

                    <select x-model="abandonReasonId" class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-red-500 focus:ring-1 focus:ring-red-500 mb-6">
                        <option value="" disabled selected>Selecciona un motivo...</option>
                        <option value="1">Tiempo de espera muy largo</option>
                        <option value="2">Solo venía a preguntar / ver</option>
                        <option value="3">Emergencia personal / Prisa</option>
                        <option value="4">Otro motivo</option>
                    </select>
                    <div x-show="abandonReasonId == '4'" x-transition class="mb-6">
                        <input type="text" x-model="customAbandonReason" placeholder="Escribe el motivo específico..." class="w-full bg-black/50 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight">
                    </div>

                    <div class="flex gap-3">
                        <button @click="showAbandonModal = false" class="flex-1 py-3 rounded-xl border border-gray-600 text-gray-400 font-bold hover:bg-gray-800">Cancelar</button>
                        <button @click="confirmAbandon()" class="flex-1 py-3 rounded-xl bg-red-600 text-white font-bold hover:bg-red-500 transition-colors shadow-lg">Confirmar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL FASE 2: CHECADOR COMPLETA DATOS --}}
        <div x-data="{
        showCompleteModal: false,
        pickupData: {},
        evidencePreview: null,
        
        // Variables para el Buscador
        clientSearchQuery: '',
        clientSearchResults: [],
        selectedCustomerId: null,
        showClientDropdown: false,

        openModal(data) {
            // Filtrar notas para mostrar SOLO las del Gerente (Ignorar lo que ya haya escrito el checador antes)
            if (data.notes) {
                data.manager_notes = data.notes.split('[Checador]:')[0].trim();
            } else {
                data.manager_notes = '';
            }

            this.pickupData = data;
            this.evidencePreview = null;
            this.clientSearchQuery = '';
            this.selectedCustomerId = null;
            
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            this.pickupData.current_datetime = now.toISOString().slice(0,16);

            this.showCompleteModal = true;
        },

        async searchCustomers() {
            if (this.clientSearchQuery.length < 2 && !/^\d+$/.test(this.clientSearchQuery)) {
                this.clientSearchResults = [];
                return;
            }
            try {
                const response = await fetch(`/recepcion/customers/search?q=${this.clientSearchQuery}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.clientSearchResults = await response.json();
                this.showClientDropdown = true;
            } catch (e) { console.error(e); }
        },

        selectCustomer(customer) {
            this.selectedCustomerId = customer.id;
            this.clientSearchQuery = customer.name;
            this.showClientDropdown = false;
        }
    }" @open-complete-modal.window="openModal($event.detail)">

            <div x-show="showCompleteModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" x-transition>
                <div @click.away="showCompleteModal = false" class="bg-aromas-secondary rounded-2xl border-2 border-sky-500/50 p-6 w-full max-w-2xl shadow-2xl relative max-h-[90vh] overflow-y-auto">

                    <button @click="showCompleteModal = false" class="absolute top-4 right-4 text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>

                    <h3 class="text-2xl font-black text-white uppercase tracking-wider mb-1">Recepción de Resguardo</h3>
                    <p class="text-sky-400 text-sm mb-6 font-mono font-bold">Folio Base: <span class="text-white text-lg" x-text="pickupData.ticket_folio"></span></p>

                    <form :action="'/recepcion/preliminar/' + pickupData.id + '/complete'" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf

                        {{-- SECCIÓN 1: DATOS REGISTRADOS POR EL GERENTE (Solo Lectura) --}}
                        <div class="bg-gray-900 border border-gray-700 rounded-xl p-4">
                            <h4 class="text-[10px] text-gray-500 font-bold uppercase tracking-widest mb-3">Información de Gerencia</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-3">
                                    <div><span class="text-xs text-gray-400 block">Departamento Origen:</span> <span class="text-white font-bold" x-text="pickupData.department"></span></div>
                                    <div><span class="text-xs text-gray-400 block">Piezas Esperadas:</span> <span class="text-white font-bold text-lg" x-text="pickupData.pieces"></span></div>
                                    <div>
                                        <span class="text-xs text-gray-400 block mb-1">Notas de Gerencia:</span>
                                        <p class="text-sm text-gray-300 italic bg-black/30 p-2 rounded" x-text="pickupData.manager_notes || 'Ninguna'"></p>
                                    </div>
                                </div>
                                <div>
                                    <span class="text-xs text-gray-400 block mb-2 text-center">Evidencia de Origen:</span>
                                    <div class="w-full h-32 bg-black/50 rounded-lg border border-gray-700 overflow-hidden flex items-center justify-center">
                                        <template x-if="pickupData.initial_evidence">
                                            <img :src="pickupData.initial_evidence" class="w-full h-full object-contain">
                                        </template>
                                        <template x-if="!pickupData.initial_evidence">
                                            <span class="text-gray-500 text-xs">Sin foto origen</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN 2: DATOS A RELLENAR POR CHECADOR --}}
                        <div class="space-y-5 border-t border-gray-700 pt-5">

                            {{-- BUSCADOR DE CLIENTES (Misma lógica que Ventas/Kiosco) --}}
                            <div class="relative">
                                <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-2">Cliente Destino <span class="text-red-500">*</span></label>
                                <input type="text" name="client_name" x-model="clientSearchQuery" required autocomplete="off" @input.debounce.300ms="searchCustomers" @focus="showClientDropdown = true" @click.away="showClientDropdown = false" placeholder="Buscar por número o nombre de cliente..."
                                    class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-sky-500 focus:ring-sky-500">
                                <input type="hidden" name="customer_id" x-model="selectedCustomerId">

                                {{-- Dropdown Resultados --}}
                                <div x-show="showClientDropdown && clientSearchResults.length > 0" style="display: none;" class="absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-2xl max-h-48 overflow-y-auto">
                                    <template x-for="customer in clientSearchResults" :key="customer.id">
                                        <div @click="selectCustomer(customer)" class="px-4 py-3 hover:bg-sky-600 cursor-pointer border-b border-gray-700 text-white transition-colors">
                                            <div class="font-bold" x-text="customer.name"></div>
                                            <div class="text-xs text-gray-300" x-text="customer.customer_number ? 'No. Cliente: ' + customer.customer_number : 'Cliente Registrado'"></div>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            {{-- FECHA Y HORA MANUAL --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-2">Fecha y Hora de Recepción FÍSICA <span class="text-red-500">*</span></label>
                                <input type="datetime-local" name="received_at" x-model="pickupData.current_datetime" required
                                    class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-sky-500 focus:ring-sky-500">
                                <div class="grid grid-cols-2 gap-4 mt-4">
                                    <div>
                                        <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-2">Piezas Esp. <span class="text-red-500">*</span></label>
                                        <input type="number" readonly :value="pickupData.pieces" class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-gray-400 cursor-not-allowed">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-2">Bolsas Recibidas <span class="text-red-500">*</span></label>
                                        <input type="number" name="bags" required min="1" class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-sky-500 focus:ring-sky-500">
                                    </div>
                                </div>
                            </div>

                            {{-- ZONA DE CAPTURA DE FOTO DEL CHECADOR --}}
                            <div>
                                <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-2">Evidencia de Recepción (Foto) <span class="text-red-500">*</span></label>
                                <div class="relative flex flex-col items-center justify-center w-full h-48 border-2 border-sky-500/30 border-dashed rounded-xl cursor-pointer bg-gray-900 hover:bg-gray-800 transition-all overflow-hidden group" @click="$refs.evidenceInput.click()">

                                    <div x-show="!evidencePreview" class="flex flex-col items-center justify-center">
                                        <svg class="w-12 h-12 mb-2 text-sky-500/50 group-hover:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-300 font-bold">Tocar para abrir cámara</p>
                                        <p class="text-xs text-sky-400 mt-1">Sube la foto del paquete sellado</p>
                                    </div>

                                    {{-- IMPORTANTE: object-contain para no recortar la imagen --}}
                                    <template x-if="evidencePreview">
                                        <img :src="evidencePreview" class="absolute inset-0 w-full h-full object-contain bg-black/40 p-2">
                                    </template>

                                    <input x-ref="evidenceInput" type="file" name="package_evidence" accept="image/*" capture="environment" class="hidden" required
                                        @change="
                                            const file = $refs.evidenceInput.files[0];
                                            if(file) {
                                                const reader = new FileReader(); 
                                                reader.onload = (e) => { evidencePreview = e.target.result; }; 
                                                reader.readAsDataURL(file);
                                            }
                                       ">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-2">Observaciones de Recepción</label>
                                <textarea name="notes" rows="2" class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-sky-500 focus:ring-sky-500" placeholder="Añadir nota si hubo alguna irregularidad (opcional)..."></textarea>
                            </div>
                        </div>

                        <div class="pt-4 mt-4 border-t border-gray-700 flex justify-end gap-4">
                            <button type="button" @click="showCompleteModal = false" class="py-3 px-6 text-gray-400 hover:text-white font-bold transition-colors uppercase">Cancelar</button>
                            <button type="submit" class="flex-1 py-4 bg-sky-600 hover:bg-sky-500 text-white font-black rounded-xl uppercase tracking-widest shadow-lg transition-transform active:scale-95 flex justify-center items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Confirmar y Auditar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>

    {{-- NUEVO: Store global y Función de Auto-Recorte --}}
    <script>
        // Función para recortar el espacio vacío del Canvas
        function cropSignatureCanvas(canvas) {
            const ctx = canvas.getContext('2d');
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;

            let minX = canvas.width,
                minY = canvas.height,
                maxX = 0,
                maxY = 0;
            let hasContent = false;

            // Escanear píxel por píxel buscando trazos (donde la transparencia/alpha > 0)
            for (let y = 0; y < canvas.height; y++) {
                for (let x = 0; x < canvas.width; x++) {
                    const alpha = data[(y * canvas.width + x) * 4 + 3];
                    if (alpha > 0) {
                        minX = Math.min(minX, x);
                        minY = Math.min(minY, y);
                        maxX = Math.max(maxX, x);
                        maxY = Math.max(maxY, y);
                        hasContent = true;
                    }
                }
            }

            if (!hasContent) return canvas.toDataURL(); // Si está vacío, regresa el original

            // Añadir un margen de respiración (padding) de 20px al recorte
            const padding = 20;
            minX = Math.max(0, minX - padding);
            minY = Math.max(0, minY - padding);
            maxX = Math.min(canvas.width, maxX + padding);
            maxY = Math.min(canvas.height, maxY + padding);

            // Crear un canvas temporal solo con el tamaño de la firma
            const croppedCanvas = document.createElement('canvas');
            croppedCanvas.width = maxX - minX;
            croppedCanvas.height = maxY - minY;
            const croppedCtx = croppedCanvas.getContext('2d');

            // Dibujar el área recortada en el nuevo canvas
            croppedCtx.drawImage(canvas, minX, minY, croppedCanvas.width, croppedCanvas.height, 0, 0, croppedCanvas.width, croppedCanvas.height);

            return croppedCanvas.toDataURL();
        }

        document.addEventListener('alpine:init', () => {
            Alpine.store('firmaStore', {
                isFullScreen: false,
                preview: null
            });
        });

        // Detectar el botón 'atrás' del navegador/tablet para cerrar el canvas
        window.addEventListener('popstate', function(event) {
            if (Alpine.store('firmaStore').isFullScreen) {
                Alpine.store('firmaStore').isFullScreen = false;
                history.pushState(null, null, window.location.pathname);
            }
        }, false);
        history.pushState(null, null, window.location.pathname);
    </script>


</x-tablet-layout>