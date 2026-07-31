@php
    $attentionMins = (int)\App\Models\SystemSetting::getVal('attention_time_minutes', 20);
    $extensionMins = (int)\App\Models\SystemSetting::getVal('extension_time_minutes', 4);
@endphp
<!DOCTYPE html>
<html lang="es-MX" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tablero de Ventas - Aromas</title>
    @vite(['resources/css/app.css', 'resources/js/ventas/dashboard-pip.js', 'resources/js/app.js'])
</head>

<body class="bg-gray-900 text-white font-sans antialiased overflow-hidden">

    <div class="h-screen w-full flex flex-col" x-data="salesDashboard()">

        {{-- HEADER --}}
        <div class="bg-gray-900/90 backdrop-blur-md border-b border-gray-800 px-8 py-5 shadow-2xl sticky top-0 z-50">
            <div class="flex justify-between items-center w-full">

                <div class="flex items-center gap-5">
                    <div class="bg-gradient-to-br from-aromas-highlight to-yellow-600 p-3 rounded-xl text-aromas-main shadow-lg shadow-yellow-500/20">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-white tracking-tight uppercase">VENDEDORES</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="w-2 h-2 rounded-full" :class="isLoading ? 'bg-yellow-500 animate-pulse' : 'bg-green-500'"></span>
                            <p class="text-xs text-gray-400 font-medium" x-text="isLoading ? 'Sincronizando...' : 'Sistema en Línea'"></p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-6">
                    @if(in_array(auth()->user()->resolveRoleName(), ['MANAGER', 'ADMIN']))
                    <button @click="openRetentionModal()" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-3 rounded-xl font-bold text-sm flex items-center gap-2 shadow-[0_0_15px_rgba(37,99,235,0.3)] transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        RETENCIÓN
                    </button>
                    <div class="w-px h-10 bg-gray-700"></div>
                    @endif

                    <button @click="openPipSelector()" type="button"
                        class="bg-emerald-600 hover:bg-emerald-500 text-white px-5 py-3 rounded-xl font-bold text-sm flex items-center gap-2 shadow-[0_0_15px_rgba(16,185,129,0.3)] transition-all"
                        :class="pipActive ? 'ring-2 ring-emerald-300' : ''"
                        :title="pipSupported ? 'Abrir ventana flotante always-on-top' : 'Disponible en Chrome o Edge 116+'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        <span x-text="pipActive ? 'PiP Activo' : 'Extraer Dashboard'"></span>
                    </button>
                    <div class="w-px h-10 bg-gray-700"></div>

                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-1">En Fila</p>
                        <div class="flex items-baseline justify-end gap-1">
                            <span class="text-4xl font-black text-white" x-text="waitingCount">{{ $clientsWaiting }}</span>
                            <span class="text-sm text-gray-600">clientes</span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-3 rounded-full bg-gray-800 hover:bg-red-500/20 text-gray-500 hover:text-red-400 transition-colors" title="Salir">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- GRID PRINCIPAL --}}
        <div class="flex-1 overflow-y-auto p-8">
            <div id="sellers-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 content-start">
                @include('ventas.partials.sellers-grid', ['sellers' => $sellers])
            </div>
        </div>

        {{-- MODAL SELECTOR PiP --}}
        <div x-show="showPipSelectorModal" style="display: none;" class="fixed inset-0 z-[85] flex items-center justify-center p-4 bg-black/95 backdrop-blur-md" x-transition>
            <div class="bg-gray-900 rounded-2xl border-2 border-emerald-500 shadow-[0_0_50px_rgba(16,185,129,0.15)] p-6 w-full max-w-lg flex flex-col max-h-[85vh]">
                <div class="flex justify-between items-center mb-4 border-b border-gray-800 pb-4">
                    <h3 class="text-xl font-black text-emerald-400 uppercase tracking-widest">Ventana Flotante</h3>
                    <button @click="showPipSelectorModal = false" type="button" class="text-gray-500 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <p class="text-sm mb-4" :class="pipSupportInfo.mode === 'document-pip' ? 'text-gray-400' : 'text-yellow-300/90'" x-text="pipSupportInfo.hint"></p>
                <p class="text-xs text-emerald-300/80 mb-4">Desde la ventana puedes terminar venta, calificar y solicitar prórroga sin volver al tablero principal.</p>
                <div class="flex gap-2 mb-4">
                    <button @click="selectAllPipSellers()" type="button" class="text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700">Todos</button>
                    <button @click="selectNonePipSellers()" type="button" class="text-xs font-bold uppercase tracking-wider px-3 py-1.5 rounded-lg bg-gray-800 text-gray-300 hover:bg-gray-700">Ninguno</button>
                </div>
                <div class="overflow-y-auto flex-1 space-y-2 pr-1 mb-6">
                    <template x-for="seller in pipSellers" :key="seller.id">
                        <label class="flex items-center gap-3 p-3 rounded-xl bg-gray-800 border border-gray-700 hover:border-emerald-500/40 cursor-pointer transition-colors">
                            <input type="checkbox" class="rounded border-gray-600 text-emerald-500 focus:ring-emerald-500 bg-gray-900"
                                :value="Number(seller.id)"
                                x-model="selectedPipSellerIds">
                            <span class="text-white font-bold text-sm" x-text="seller.name"></span>
                        </label>
                    </template>
                </div>
                <div class="flex gap-3">
                    <button @click="showPipSelectorModal = false" type="button" class="flex-1 py-3 bg-gray-800 text-gray-400 font-bold rounded-xl uppercase text-sm hover:bg-gray-700">Cancelar</button>
                    <button @click="confirmOpenPip()" type="button" class="flex-1 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-black rounded-xl uppercase text-sm" x-text="pipSupportInfo.buttonLabel">Abrir ventana</button>
                </div>
            </div>
        </div>

        {{-- MODALES BREAK Y COMIDA OCULTOS (SIN CAMBIOS AL HTML PREVIO) --}}
        <div x-show="showBreakModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" x-transition>
            <div class="bg-gray-800 rounded-2xl border border-gray-700 p-8 w-full max-w-lg shadow-2xl" @click.away="showBreakModal = false">
                <h3 class="text-2xl font-black text-white mb-6 text-center uppercase tracking-wide">Selecciona Motivo</h3>
                <form id="break-form" action="{{ route('ventas.toggle-break') }}" method="POST" class="grid grid-cols-2 gap-4">
                    @csrf
                    <input type="hidden" name="shift_id" :value="breakShiftId">
                    <input type="hidden" name="reason" id="break-reason-input">
                    @foreach($breakReasons as $breakReason)
                    <button type="button" @click="selectBreakReason('{{ $breakReason->code }}')"
                        class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl text-gray-300 font-bold flex flex-col items-center gap-3 transition-all @if(!$breakReason->is_lunch) hover:bg-aromas-highlight hover:text-aromas-main @endif"
                        @if($breakReason->is_lunch) :class="lunchSecondsLeft <= 0 ? 'opacity-30 cursor-not-allowed' : 'hover:bg-aromas-highlight hover:text-aromas-main'" @endif>
                        <span class="text-sm uppercase tracking-wide">{{ $breakReason->label }}</span>
                        @if($breakReason->is_lunch)
                        <span x-show="lunchSecondsLeft <= 0" class="absolute bottom-2 text-[10px] text-red-400 font-black">Agotado</span>
                        <span x-show="lunchSecondsLeft > 0 && lunchSecondsLeft < 1800" class="absolute bottom-2 text-[10px] text-yellow-400 font-black" x-text="Math.floor(lunchSecondsLeft/60) + ' min' + (lunchSecondsLeft < 60 ? ' (Poco tiempo)' : '')"></span>
                        @endif
                    </button>
                    @endforeach
                </form>
                <button @click="showBreakModal = false" class="mt-6 w-full py-4 text-gray-500 font-bold hover:text-white transition-colors uppercase text-sm">Cancelar</button>
            </div>
        </div>

        <div x-show="showLunchConfirmModal" style="display: none;" class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/95 backdrop-blur-md" x-transition>
            <div class="bg-gray-900 rounded-2xl border-2 border-aromas-highlight p-8 w-full max-w-lg text-center" @click.away="showLunchConfirmModal = false">
                <h3 class="text-2xl font-black text-white mb-2 uppercase tracking-widest">¿Iniciar Comida?</h3>
                <div class="grid grid-cols-2 gap-4 mt-8">
                    <button @click="showLunchConfirmModal = false" class="w-full py-4 bg-gray-700 text-white font-bold rounded-xl uppercase">Cancelar</button>
                    <button @click="executeBreak('LUNCH')" class="w-full py-4 bg-aromas-highlight text-gray-900 font-black rounded-xl uppercase">Sí, Iniciar</button>
                </div>
            </div>
        </div>

        {{-- MODAL RETENCIÓN (ACTUALIZADO CON VENDEDORES DINÁMICOS) --}}
        @if(in_array(auth()->user()->resolveRoleName(), ['MANAGER', 'ADMIN']))
        <div x-show="showRetentionModal" style="display: none;" class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/95 backdrop-blur-md" x-transition>
            <div class="bg-gray-900 rounded-2xl border-2 border-blue-500 shadow-[0_0_50px_rgba(37,99,235,0.2)] p-6 w-full max-w-2xl flex flex-col max-h-[85vh]">
                <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                    <h3 class="text-2xl font-black text-blue-400 uppercase tracking-widest flex items-center gap-3">Re-Atención</h3>
                    <button @click="closeRetentionModal()" class="text-gray-500 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <p class="text-xs text-blue-300/80 mb-4 -mt-2">Asignación automática en pausa mientras este panel está abierto.</p>
                <div class="overflow-y-auto flex-1 pr-2 space-y-4">
                    <div x-show="retentionList.length === 0" class="text-center py-10">
                        <p class="text-gray-500 font-bold text-lg">No hay clientes recientes.</p>
                    </div>

                    <template x-for="client in retentionList" :key="client.id">
                        <div class="bg-gray-800 border border-gray-700 hover:border-blue-500/50 transition-colors rounded-xl p-5 flex flex-col gap-4">
                            <div class="flex justify-between items-start">
                                <div>
                                    <span class="text-white font-bold text-xl block leading-tight" x-text="client.client_name"></span>
                                    <span class="text-sm text-gray-400">Atendido por: <strong x-text="client.assigned_shift ? client.assigned_shift.employee.full_name : 'Desconocido'"></strong></span>
                                </div>
                                <span class="bg-blue-900/30 text-blue-400 border border-blue-500/30 px-3 py-1.5 rounded-lg text-sm font-mono font-bold" x-text="client.turn_number"></span>
                            </div>

                            <div class="flex flex-col sm:flex-row gap-3 items-center mt-2 pt-4 border-t border-gray-700">
                                {{-- AHORA EL SELECT SE LLENA DINAMICAMENTE CON LOS VENDEDORES DISPONIBLES --}}
                                <select :id="'seller-select-' + client.id" class="w-full sm:flex-1 bg-gray-900 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-blue-500 cursor-pointer">
                                    <option value="" disabled selected>Reasignar a vendedor libre...</option>
                                    <template x-for="sellerShift in availableSellers" :key="sellerShift.id">
                                        <option :value="sellerShift.id" x-text="sellerShift.employee.full_name"></option>
                                    </template>
                                </select>
                                <button @click="confirmRetention(client.id)" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-500 text-white px-6 py-3 rounded-xl font-bold transition-all">Re-Atender</button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
        @endif

        {{-- MODAL DE CALIFICACIÓN TIPO UBER --}}
        <div x-show="showRatingModal" style="display: none;" class="fixed inset-0 z-[90] flex items-center justify-center p-4 bg-black/90 backdrop-blur-md" x-transition>
            <div class="bg-gray-900 rounded-2xl border border-purple-500 p-8 w-full max-w-lg shadow-[0_0_50px_rgba(168,85,247,0.15)]">
                <h3 class="text-2xl font-black text-white text-center mb-1">Califica tu Venta</h3>
                <p class="text-gray-400 text-sm text-center mb-6">¿Cómo fue tu experiencia con el cliente?</p>
                
                {{-- Estrellas Dinámicas --}}
                <div class="flex justify-center gap-2 mb-6">
                    <template x-for="star in 5">
                        <button @click="ratingStars = star" class="focus:outline-none transition-transform hover:scale-110">
                            <svg class="w-12 h-12 transition-colors" :class="ratingStars >= star ? 'text-yellow-400' : 'text-gray-700'" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                            </svg>
                        </button>
                    </template>
                </div>

                {{-- Etiquetas Rápidas (Aparecen al seleccionar estrellas) --}}
                <div x-show="ratingStars > 0" class="flex flex-wrap justify-center gap-2 mb-6" x-transition>
                    <template x-for="tag in availableTags()" :key="tag">
                        <button @click="toggleTag(tag)" 
                                :class="ratingTags.includes(tag) ? 'bg-purple-600 text-white border-purple-500' : 'bg-gray-800 text-gray-400 border-gray-700 hover:border-purple-500'"
                                class="px-3 py-1.5 border rounded-full text-xs font-bold transition-colors uppercase tracking-wider" x-text="tag"></button>
                    </template>
                </div>

                <textarea x-model="ratingComment" class="w-full bg-gray-800 border border-gray-700 rounded-xl p-4 text-white focus:border-purple-500 text-sm mb-6" rows="3" placeholder="Comentarios adicionales (Opcional)..."></textarea>

                <div class="flex gap-4">
                    <button @click="skipRating()" class="w-1/3 py-3 text-gray-500 hover:text-white font-bold transition-colors bg-gray-800 rounded-xl hover:bg-gray-700">Omitir</button>
                    <button @click="submitRating()" class="w-2/3 py-3 bg-purple-600 hover:bg-purple-500 rounded-xl text-white font-black shadow-lg transition-transform active:scale-95 disabled:opacity-50" :disabled="ratingStars === 0">Enviar Calificación</button>
                </div>
            </div>
        </div>

        {{-- MEGA NOTIFICACIÓN (ACTUALIZADA PARA VIP DORADO) --}}
        <div x-show="showMegaAlert" style="display: none;"
            class="fixed inset-0 z-[100] flex items-center justify-center backdrop-blur-xl"
            :class="alertData.use_premium_alert ? 'bg-yellow-900/90' : 'bg-blue-900/95'" x-transition>
            <div class="text-center p-8 max-w-5xl w-full">

                <h2 class="text-3xl uppercase tracking-[0.2em] font-bold mb-8"
                    :class="alertData.use_premium_alert ? 'text-yellow-200' : 'text-blue-200'">Nueva Asignación</h2>

                {{-- Contenedor dinámico Dorado/Azul --}}
                <div class="rounded-[2rem] p-12 shadow-2xl mx-auto transform transition-all border-4"
                    :class="alertData.use_premium_alert ? 'border-yellow-400 bg-gradient-to-br from-yellow-50 to-white shadow-[0_0_50px_rgba(234,179,8,0.3)]' : 'border-blue-400/50 bg-white'">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div>
                            <p class="text-sm text-gray-400 uppercase font-bold mb-2 tracking-widest">Vendedor</p>
                            <p class="text-5xl font-black leading-tight"
                                :class="alertData.use_premium_alert ? 'text-yellow-600' : 'text-blue-600'"
                                x-text="alertData.seller"></p>
                        </div>
                        <div class="pt-8 md:pt-0 md:pl-12">
                            <p class="text-sm text-gray-400 uppercase font-bold mb-2 tracking-widest">Cliente (Turno: <span x-text="alertData.folio"></span>)</p>
                            <div class="flex justify-center items-center gap-3 mb-2">
                                <span x-show="alertData.use_premium_alert" class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider" x-text="alertData.client_type_label || 'Premium'"></span>
                                <span x-show="alertData.has_disability" class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider">PRIORIDAD</span>
                            </div>
                            <p class="text-4xl font-black text-gray-800 leading-tight" x-text="alertData.client"></p>
                        </div>
                    </div>
                </div>

                <button @click="closeAlert()" class="mt-12 px-12 py-5 rounded-2xl font-black text-xl transition-all duration-300 tracking-wider bg-white shadow-xl transform hover:-translate-y-1"
                    :class="alertData.use_premium_alert ? 'text-yellow-900 hover:bg-yellow-50 shadow-white/20' : 'text-blue-900 hover:bg-blue-50 shadow-white/10'">
                    <span>ENTERADO</span>
                </button>
            </div>
        </div>

    </div>

    {{-- AUDIOS --}}
    <audio id="bell" src="{{ asset('audio/bell.mp3') }}" preload="auto"></audio>
    <audio id="bell_vip" src="{{ asset('audio/bell_vip.mp3') }}" preload="auto"></audio>
    <audio id="soft_alert" src="{{ asset('audio/soft_alert.mp3') }}" preload="auto"></audio>

    <script>
        function salesDashboard() {
            return {
                waitingCount: @json($clientsWaiting ?? 0),
                showBreakModal: false,
                showLunchConfirmModal: false,
                breakShiftId: null,
                lunchSecondsLeft: 1800,

                showRetentionModal: false,
                retentionList: [],
                availableSellers: [], // <-- NUEVA VARIABLE
                retentionFreezeAt: null,

                showRatingModal: false,
                ratingShiftId: null,
                ratingQueueId: null,
                ratingStars: 0,
                ratingTags: [],
                ratingComment: '',

                showMegaAlert: false,
                alertData: {
                    seller: '',
                    client: '',
                    folio: '',
                    client_type: 'CLIENTES',
                    client_type_label: 'Clientes',
                    use_premium_alert: false,
                    has_disability: false
                },
                alertTimer: 5,
                alertedFolios: [],
                alertedAssignmentKeys: [],
                alertedIncidents: [],
                prorrogaAlerted: {},
                prorrogaAnnouncing: {},
                prorrogaIntervalActive: {},
                mediaUnlocked: false,
                extensionOverrides: {},
                timingSettings: {
                    attentionMins: {{ $attentionMins }},
                    extensionMins: {{ $extensionMins }},
                },
                softAlertIntervalMs: 3000,
                receivesProrrogaAlerts: {{ auth()->user()->receivesProrrogaAlerts() ? 'true' : 'false' }},
                serveTimerAnchors: {},
                speechSessionId: 0,
                isLoading: false,
                spanishVoice: null,
                requestExtensionUrl: '{{ route('ventas.request-extension') }}',

                taskQueue: [],
                isProcessingTask: false,
                _keepaliveAudio: null,

                pipSupported: true,
                pipActive: false,
                pipMode: null,
                pipWindow: null,
                pipSupportInfo: {
                    canUseDocumentPip: false,
                    canUsePopupFallback: true,
                    isSecureContext: true,
                    mode: 'popup',
                    hint: '',
                    buttonLabel: 'Abrir ventana',
                },
                showPipSelectorModal: false,
                pipSellers: @json($pipSellers ?? []),
                linkedEmployeeId: @json($linkedEmployeeId),
                selectedPipSellerIds: [],

                init() {
                    this.refreshPipSupportInfo();
                    this.selectedPipSellerIds = this.loadPipSelectionLocal();

                window.addEventListener('finish-service', event => {
                        this.processFinishService(event.detail.shift_id, event.detail.queue_id);
                    });
                    window.addEventListener('open-rating-modal', event => {
                        this.openRatingModal(event.detail.shift_id, event.detail.queue_id);
                    });

                    
                    // CARGAR LA MEJOR VOZ FEMENINA (TU CÓDIGO)
                    const loadVoices = () => {
                        const voices = window.speechSynthesis.getVoices();
                        this.spanishVoice = voices.find(v => v.name.includes('Google') && v.lang.includes('es')) ||
                            voices.find(v => v.name.includes('Natural') && v.lang.includes('es')) ||
                            voices.find(v => v.lang.includes('es-MX')) ||
                            voices.find(v => v.lang.includes('es'));
                    };

                    // Ejecutar de inmediato por si ya están cacheadas
                    loadVoices();
                    // Y escuchar cuando el navegador termine de descargarlas
                    window.speechSynthesis.onvoiceschanged = loadVoices;
                    window.addEventListener('open-break-modal', event => {
                        this.breakShiftId = event.detail.id;
                        this.lunchSecondsLeft = event.detail.lunchLeft;
                        this.showBreakModal = true;
                    });

                    if (sessionStorage.getItem('ventas_media_unlocked') === '1') {
                        this.unlockMedia();
                        this.ensureNotificationPermission();
                    }

                    document.body.addEventListener('click', () => {
                        this.unlockMedia();
                        this.ensureNotificationPermission();
                    });

                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) {
                            this.fetchUpdates();
                            this.updateTimers();
                        }
                    });

                    setInterval(() => {
                        this.fetchUpdates();
                    }, 3000);
                    setInterval(() => {
                        this.updateTimers();
                    }, 1000);

                    setTimeout(() => this.captureServeTimerAnchors(), 0);

                    const sellersGrid = document.getElementById('sellers-grid');
                    if (sellersGrid) {
                        sellersGrid.addEventListener('click', (e) => {
                            const btn = e.target.closest('.request-extension-btn');
                            if (!btn || btn.classList.contains('hidden')) return;
                            const card = btn.closest('.seller-card');
                            if (!card?.dataset.queueId) return;
                            e.preventDefault();
                            this.requestExtension({
                                queue_id: Number(card.dataset.queueId),
                                seller_name: card.dataset.sellerName || 'Vendedor',
                            });
                        });
                    }
                },

                openRetentionModal() {
                    this.retentionFreezeAt = Date.now();
                    this.showRetentionModal = true;
                    this.fetchRetentionList();
                },

                closeRetentionModal() {
                    this.showRetentionModal = false;
                    this.retentionFreezeAt = null;
                    this.resumeRetentionMatchmaker();
                },

                resumeRetentionMatchmaker() {
                    fetch("{{ route('ventas.retention.resume-matchmaker') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                        },
                    }).catch(() => {});
                },

                refreshPipSupportInfo() {
                    if (window.VentasPip?.resolvePipSupport) {
                        this.pipSupportInfo = window.VentasPip.resolvePipSupport();
                    } else {
                        const isSecure = window.isSecureContext === true;
                        this.pipSupportInfo = {
                            canUseDocumentPip: 'documentPictureInPicture' in window,
                            canUsePopupFallback: true,
                            isSecureContext: isSecure,
                            mode: isSecure && 'documentPictureInPicture' in window ? 'document-pip' : 'popup',
                            hint: isSecure
                                ? 'Elige qué vendedores ver en la ventana flotante.'
                                : 'PiP always-on-top requiere HTTPS. En HTTP se usará una ventana separada.',
                            buttonLabel: isSecure && 'documentPictureInPicture' in window
                                ? 'Abrir ventana flotante'
                                : 'Abrir ventana separada',
                        };
                    }
                    this.pipSupported = this.pipSupportInfo.canUseDocumentPip || this.pipSupportInfo.canUsePopupFallback;
                },

                openPipSelector() {
                    this.refreshPipSupportInfo();
                    if (!window.VentasPip) {
                        alert('No se cargó el módulo PiP. Ejecute npm run dev o npm run build y recargue la página.');
                        return;
                    }
                    this.selectedPipSellerIds = window.VentasPip.loadPipSelection(this.pipSellers, this.linkedEmployeeId);
                    this.showPipSelectorModal = true;
                },

                loadPipSelectionLocal() {
                    if (window.VentasPip) {
                        return window.VentasPip.loadPipSelection(this.pipSellers, this.linkedEmployeeId);
                    }
                    try {
                        const stored = localStorage.getItem('ventas_pip_seller_ids');
                        if (stored) {
                            const ids = JSON.parse(stored);
                            if (Array.isArray(ids) && ids.length > 0) {
                                return ids.map(Number);
                            }
                        }
                    } catch {
                        // ignore
                    }
                    if (this.linkedEmployeeId) {
                        return [Number(this.linkedEmployeeId)];
                    }
                    return (this.pipSellers || []).map((s) => Number(s.id));
                },

                selectAllPipSellers() {
                    this.selectedPipSellerIds = this.pipSellers.map((s) => Number(s.id));
                },

                selectNonePipSellers() {
                    this.selectedPipSellerIds = [];
                },

                async confirmOpenPip() {
                    if (this.selectedPipSellerIds.length === 0) {
                        alert('Selecciona al menos un vendedor.');
                        return;
                    }
                    window.VentasPip.persistPipSelection(this.selectedPipSellerIds);
                    this.showPipSelectorModal = false;
                    this.unlockMedia();
                    await window.VentasPip.openExtractWindow(this);
                },

                syncPipFromPoll(html) {
                    if (!this.pipActive || !this.pipWindow || this.pipWindow.closed) return;
                    window.VentasPip.syncPipGrid(this.pipWindow, html, this.selectedPipSellerIds, this);
                    window.VentasPip.updatePipHeaderLabel(this.pipWindow, this.selectedPipSellerIds, this.pipSellers);
                    window.VentasPip.syncPipWaitingCount(this.pipWindow, this.waitingCount);
                },

                isAlertRelevantForPip(alert) {
                    if (!this.pipActive) return true;
                    return window.VentasPip.isAlertForSelectedSeller(alert, this.selectedPipSellerIds);
                },

                markAlertAsSeen(alert) {
                    if (!alert?.id) return;
                    const startedAt = Number(alert.started_serving_at || 0);
                    const key = `${alert.id}_${startedAt}`;
                    if (!this.alertedAssignmentKeys.includes(key)) {
                        this.alertedAssignmentKeys.push(key);
                    }
                    if (alert.folio && !this.alertedFolios.includes(alert.folio)) {
                        this.alertedFolios.push(alert.folio);
                    }
                },

                fetchRetentionList() {
                    fetch("{{ route('ventas.retention.list') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.clients) {
                                this.retentionList = data.clients;
                                this.availableSellers = data.available_sellers; // Guardamos los vendedores libres
                            }
                        });
                },

                confirmRetention(queueId) {
                    let selectEl = document.getElementById('seller-select-' + queueId);
                    let shiftId = selectEl ? selectEl.value : null;

                    if (!shiftId) {
                        alert("Por favor, selecciona un vendedor libre de la lista.");
                        return;
                    }

                    fetch("{{ route('ventas.retention.reassign') }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                queue_id: queueId,
                                shift_id: shiftId
                            })
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                this.closeRetentionModal();
                                this.fetchUpdates();
                            } else {
                                alert(data.message || 'Error al procesar la retención.');
                            }
                        });
                },

                selectBreakReason(reason) {
                    if (reason === 'LUNCH') {
                        if (this.lunchSecondsLeft <= 0) return; // Ya no tiene tiempo
                        this.showBreakModal = false;
                        setTimeout(() => { this.showLunchConfirmModal = true; }, 200);
                        return;
                    }
                    this.executeBreak(reason);
                },

                executeBreak(reason) {
                    let input = document.getElementById('break-reason-input');
                    let form = document.getElementById('break-form');
                    if (input && form) {
                        input.value = reason;
                        form.submit();
                    }
                },

                updateTimers() {
                    const now = Date.now();
                    const servingCards = document.querySelectorAll('.seller-card[data-serving="true"]');
                    servingCards.forEach(card => {
                        const queueId = card.dataset.queueId;
                        let startTime = queueId && this.serveTimerAnchors[queueId]
                            ? this.serveTimerAnchors[queueId]
                            : parseInt(card.dataset.startTime, 10);

                        if (queueId && Number.isFinite(startTime) && startTime > 0) {
                            const existing = this.serveTimerAnchors[queueId];
                            if (!existing || startTime < existing) {
                                this.serveTimerAnchors[queueId] = startTime;
                            } else {
                                startTime = existing;
                            }
                            card.dataset.startTime = String(startTime);
                        }

                        if (!Number.isFinite(startTime) || startTime <= 0) return;

                        let elapsedSecs = Math.floor((now - startTime) / 1000);
                        if (elapsedSecs < 0) elapsedSecs = 0;

                        let mins = Math.floor(elapsedSecs / 60);
                        let secs = elapsedSecs % 60;
                        let timerEl = card.querySelector('.seller-timer');
                        if (timerEl) {
                            timerEl.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                            timerEl.className = mins >= this.timingSettings.attentionMins ? "seller-timer text-xl font-mono font-bold text-yellow-500 tracking-wider" : "seller-timer text-xl font-mono font-bold text-gray-300 tracking-wider";
                        }

                        let turnNumber = card.dataset.turnNumber;
                        let extensionCount = parseInt(card.dataset.extensionCount) || 0;
                        let lastExtendedAt = parseInt(card.dataset.lastExtendedAt, 10) || 0;
                        let warningEl = card.querySelector('.extension-warning');

                        const phase = this.resolveProrrogaPhase(startTime, extensionCount, lastExtendedAt, now);
                        const { inRequestWindow, inExtensionGrace } = phase;
                        const shouldSoftAlert = inRequestWindow && this.receivesProrrogaAlerts;

                        if (shouldSoftAlert) {
                            if (!this.prorrogaIntervalActive[queueId]) {
                                this.prorrogaIntervalActive[queueId] = true;
                                this.playSoftAlertIntervals(queueId);
                            }
                        } else if (this.prorrogaIntervalActive[queueId]) {
                            this.prorrogaIntervalActive[queueId] = false;
                        }

                        if (inRequestWindow) {
                            if (warningEl) {
                                warningEl.innerText = 'Tiempo expirado. Prórroga requerida';
                                warningEl.classList.remove('hidden');
                            }
                            let prorrogaBtn = card.querySelector('.request-extension-btn');
                            let prorrogaLabel = card.querySelector('.request-extension-label');
                            if (prorrogaBtn) prorrogaBtn.classList.remove('hidden');
                            if (prorrogaLabel) prorrogaLabel.classList.add('hidden');

                            const alertKey = queueId + '_' + extensionCount;
                            if (this.receivesProrrogaAlerts && queueId && !this.prorrogaAlerted[alertKey] && !this.prorrogaAnnouncing[alertKey]) {
                                let sellerName = card.dataset.sellerName || 'Vendedor';
                                this.prorrogaAnnouncing[alertKey] = true;
                                const msg = 'Usuario, tienes una nueva notificación';
                                this.speakMessage(msg, {
                                    desktopNotification: {
                                        title: 'Prórroga requerida',
                                        body: `El vendedor ${sellerName} necesita prórroga de atención.`,
                                        tag: `prorroga-alert-${queueId}-${extensionCount}`,
                                        requireInteraction: true,
                                        renotify: true,
                                        onlyWhenHidden: true,
                                    },
                                    onComplete: (success) => {
                                        this.prorrogaAnnouncing[alertKey] = false;
                                        if (success) this.prorrogaAlerted[alertKey] = true;
                                    },
                                });
                            }
                        } else if (inExtensionGrace) {
                            if (warningEl) {
                                const remainingSecs = Math.max(0, Math.ceil((phase.grantedDeadlineMs - now) / 1000));
                                const rMins = Math.floor(remainingSecs / 60);
                                const rSecs = remainingSecs % 60;
                                warningEl.innerText = `Prórroga activa: ${rMins.toString().padStart(2, '0')}:${rSecs.toString().padStart(2, '0')} restantes`;
                                warningEl.classList.remove('hidden');
                            }
                            let prorrogaBtn = card.querySelector('.request-extension-btn');
                            let prorrogaLabel = card.querySelector('.request-extension-label');
                            if (prorrogaBtn) prorrogaBtn.classList.add('hidden');
                            if (prorrogaLabel) {
                                prorrogaLabel.classList.remove('hidden');
                                prorrogaLabel.innerText = `Prórroga solicitada (${extensionCount})`;
                            }
                        } else {
                            if (warningEl) warningEl.classList.add('hidden');
                            let prorrogaBtn = card.querySelector('.request-extension-btn');
                            let prorrogaLabel = card.querySelector('.request-extension-label');
                            if (prorrogaBtn) prorrogaBtn.classList.add('hidden');
                            if (prorrogaLabel) prorrogaLabel.classList.add('hidden');
                        }
                    });

                    const breakCards = document.querySelectorAll('.seller-card[data-on-break="true"]');
                    breakCards.forEach(card => {
                        let breakStartTime = parseInt(card.dataset.breakStartTime);
                        let breakReason = card.dataset.breakReason;
                        let lunchLeft = parseInt(card.dataset.lunchLeft) || 1800;
                        if (!breakStartTime) return;
                        
                        let timerEl = card.querySelector('.break-timer');
                        if (!timerEl) return;

                        let elapsedSecs = Math.floor((now - breakStartTime) / 1000);

                        if (breakReason === 'LUNCH') {
                            // LÓGICA COMIDA: Ya cuenta de sus 30 mins
                            let remaining = lunchLeft - elapsedSecs;
                            
                            // ALERTA DE 5 MINUTOS RESTANTES
                            if (remaining === 300) {
                                if (!card.dataset.lunchAlerted) {
                                    card.dataset.lunchAlerted = "true";
                                    let sellerName = card.dataset.sellerName || 'Vendedor';
                                    this.speakMessage(`Atención ${sellerName}, te quedan 5 minutos de comida para regresar al trabajo.`);
                                }
                            }

                            if (remaining < 0) {
                                let excess = Math.abs(remaining);
                                let eMins = Math.floor(excess / 60);
                                let eSecs = excess % 60;
                                timerEl.innerText = `-${eMins.toString().padStart(2, '0')}:${eSecs.toString().padStart(2, '0')}`;
                                timerEl.className = "break-timer text-2xl font-mono font-black text-red-500 tracking-wider animate-pulse";
                            } else {
                                let rMins = Math.floor(remaining / 60);
                                let rSecs = remaining % 60;
                                timerEl.innerText = `${rMins.toString().padStart(2, '0')}:${rSecs.toString().padStart(2, '0')}`;
                                timerEl.className = "break-timer text-2xl font-mono font-bold text-yellow-400 tracking-wider";
                            }
                        } else {
                            // PAUSAS NORMALES: Cuenta hacia arriba
                            let absSecs = Math.abs(elapsedSecs);
                            let mins = Math.floor(absSecs / 60);
                            let secs = absSecs % 60;
                            let timeStr = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

                            if (elapsedSecs < 0) {
                                timerEl.innerText = "-" + timeStr;
                                timerEl.className = "break-timer text-2xl font-mono font-black text-green-400 tracking-wider animate-pulse";
                            } else {
                                timerEl.innerText = timeStr;
                                if (mins >= 30) timerEl.className = "break-timer text-2xl font-mono font-black text-red-500 tracking-wider animate-pulse";
                                else if (mins >= 25) timerEl.className = "break-timer text-2xl font-mono font-bold text-yellow-500 tracking-wider";
                                else timerEl.className = "break-timer text-2xl font-mono font-bold text-yellow-300 tracking-wider";
                            }
                        }
                    });

                    // --- NUEVA LÓGICA: CRONÓMETRO DE DELAY (10s) AL ESTAR DISPONIBLE ---
                    const onlineCards = document.querySelectorAll('.seller-card[data-online="true"]');
                    onlineCards.forEach(card => {
                        let lastActionAt = parseInt(card.dataset.lastActionAt);
                        if (!lastActionAt) return;

                        let elapsedSecs = Math.floor((now - lastActionAt) / 1000);
                        // Congela el tiempo de recuperación mientras Re-Atención está abierto
                        if (this.showRetentionModal && this.retentionFreezeAt) {
                            elapsedSecs = Math.floor((this.retentionFreezeAt - lastActionAt) / 1000);
                        }
                        let delayContainer = card.querySelector('.delay-container');
                        let onlineDots = card.querySelector('.online-dots');
                        let delayTimerEl = card.querySelector('.delay-timer');

                        if (elapsedSecs < 10) {
                            if (delayContainer) delayContainer.style.display = 'block';
                            if (onlineDots) onlineDots.style.display = 'none';
                            if (delayTimerEl) delayTimerEl.innerText = Math.max(0, 10 - elapsedSecs) + "s";
                        } else {
                            if (delayContainer) delayContainer.style.display = 'none';
                            if (onlineDots) onlineDots.style.display = 'block';
                        }
                    });

                    if (this.pipActive && this.pipWindow && !this.pipWindow.closed) {
                        window.VentasPip.updateVisualTimersInDocument(this.pipWindow.document, this);
                    }
                },

                applyServeTimerAnchorsInDocument(doc) {
                    doc.querySelectorAll('.seller-card[data-serving="true"]').forEach((card) => {
                        const queueId = card.dataset.queueId;
                        if (queueId && this.serveTimerAnchors[queueId]) {
                            card.dataset.startTime = String(this.serveTimerAnchors[queueId]);
                        }
                    });
                },

                applyExtensionOverridesToGridInDocument(doc) {
                    doc.querySelectorAll('.seller-card[data-serving="true"]').forEach((card) => {
                        const queueId = card.dataset.queueId;
                        if (!queueId || !this.extensionOverrides[queueId]) return;
                        const override = this.extensionOverrides[queueId];
                        card.dataset.extensionCount = String(override.extensionCount);
                        card.dataset.lastExtendedAt = String(override.lastExtendedAt);
                    });
                },

                captureServeTimerAnchors() {
                    document.querySelectorAll('.seller-card[data-serving="true"]').forEach((card) => {
                        const queueId = card.dataset.queueId;
                        const startTime = parseInt(card.dataset.startTime, 10);
                        if (!queueId || !Number.isFinite(startTime) || startTime <= 0) return;
                        const existing = this.serveTimerAnchors[queueId];
                        if (!existing || startTime < existing) {
                            this.serveTimerAnchors[queueId] = startTime;
                        }
                    });
                },

                mergeServingTimers(timers) {
                    if (!timers || typeof timers !== 'object') return;
                    Object.entries(timers).forEach(([queueId, startMs]) => {
                        const ms = Number(startMs);
                        if (!Number.isFinite(ms) || ms <= 0) return;
                        const existing = this.serveTimerAnchors[queueId];
                        if (!existing || ms < existing) {
                            this.serveTimerAnchors[queueId] = ms;
                        }
                    });
                },

                applyServeTimerAnchors() {
                    document.querySelectorAll('.seller-card[data-serving="true"]').forEach((card) => {
                        const queueId = card.dataset.queueId;
                        if (queueId && this.serveTimerAnchors[queueId]) {
                            card.dataset.startTime = String(this.serveTimerAnchors[queueId]);
                        }
                    });

                    const activeQueueIds = new Set(
                        Array.from(document.querySelectorAll('.seller-card[data-serving="true"]'))
                            .map((card) => card.dataset.queueId)
                            .filter(Boolean)
                    );
                    Object.keys(this.serveTimerAnchors).forEach((queueId) => {
                        if (!activeQueueIds.has(queueId)) {
                            delete this.serveTimerAnchors[queueId];
                            if (this.prorrogaIntervalActive) delete this.prorrogaIntervalActive[queueId];
                        }
                    });
                },

                restoreTimerDisplays(displays) {
                    if (!displays) return;
                    document.querySelectorAll('.seller-card[data-serving="true"]').forEach((card) => {
                        const queueId = card.dataset.queueId;
                        const saved = displays[queueId];
                        const timerEl = card.querySelector('.seller-timer');
                        if (queueId && timerEl && saved && saved !== '00:00' && saved !== '--:--') {
                            timerEl.innerText = saved;
                        }
                    });
                },

                shouldAnnounceAssignment(alert) {
                    if (!alert?.id) return false;

                    const startedAt = Number(alert.started_serving_at || 0);
                    if (startedAt > 0) {
                        const ageSecs = (Date.now() / 1000) - startedAt;
                        if (ageSecs > 20) {
                            return false;
                        }
                    }

                    const key = `${alert.id}_${startedAt}`;
                    if (this.alertedAssignmentKeys.includes(key)) return false;
                    this.alertedAssignmentKeys.push(key);
                    if (alert.folio && !this.alertedFolios.includes(alert.folio)) {
                        this.alertedFolios.push(alert.folio);
                    }
                    return true;
                },

                resolveProrrogaPhase(startTime, extensionCount, lastExtendedAt, now) {
                    const attentionMs = this.timingSettings.attentionMins * 60 * 1000;
                    const requestGraceMs = this.timingSettings.extensionMins * 60 * 1000;
                    const grantedMs = attentionMs;
                    const attentionDeadlineMs = startTime + attentionMs;

                    if (extensionCount === 0) {
                        const requestWindowEndMs = attentionDeadlineMs + requestGraceMs;
                        const inRequestWindow = now >= attentionDeadlineMs && now < requestWindowEndMs;
                        return {
                            inRequestWindow,
                            inExtensionGrace: false,
                            grantedDeadlineMs: attentionDeadlineMs,
                            requestWindowEndMs,
                        };
                    }

                    const grantedAnchorMs = lastExtendedAt > 0 ? lastExtendedAt : attentionDeadlineMs;
                    const grantedDeadlineMs = grantedAnchorMs + grantedMs;
                    const requestWindowEndMs = grantedDeadlineMs + requestGraceMs;
                    const inExtensionGrace = now >= grantedAnchorMs && now < grantedDeadlineMs;
                    const inRequestWindow = now >= grantedDeadlineMs && now < requestWindowEndMs;

                    return {
                        inRequestWindow,
                        inExtensionGrace,
                        grantedDeadlineMs,
                        requestWindowEndMs,
                    };
                },

                applyExtensionToCard(queueId, extensionCount, lastExtendedAt) {
                    this.extensionOverrides[queueId] = {
                        extensionCount,
                        lastExtendedAt: lastExtendedAt || Date.now(),
                    };

                    const docs = [document];
                    if (this.pipWindow && !this.pipWindow.closed) {
                        docs.push(this.pipWindow.document);
                    }

                    docs.forEach((doc) => {
                        const card = doc.querySelector(`.seller-card[data-queue-id="${queueId}"]`);
                        if (!card) return;

                        card.dataset.extensionCount = String(extensionCount);
                        card.dataset.lastExtendedAt = String(lastExtendedAt || Date.now());

                        const warningEl = card.querySelector('.extension-warning');
                        const prorrogaBtn = card.querySelector('.request-extension-btn');
                        const prorrogaLabel = card.querySelector('.request-extension-label');

                        if (warningEl) warningEl.classList.add('hidden');
                        if (prorrogaBtn) prorrogaBtn.classList.add('hidden');
                        if (prorrogaLabel) {
                            prorrogaLabel.classList.remove('hidden');
                            prorrogaLabel.innerText = `Prórroga solicitada (${extensionCount})`;
                        }
                    });

                    if (this.prorrogaIntervalActive[queueId]) {
                        this.prorrogaIntervalActive[queueId] = false;
                    }
                },

                applyExtensionOverridesToGrid() {
                    Object.entries(this.extensionOverrides).forEach(([queueId, patch]) => {
                        const card = document.querySelector(`.seller-card[data-queue-id="${queueId}"]`);
                        if (!card) return;

                        const serverCount = parseInt(card.dataset.extensionCount, 10) || 0;
                        if (serverCount >= patch.extensionCount) {
                            delete this.extensionOverrides[queueId];
                            return;
                        }

                        card.dataset.extensionCount = String(patch.extensionCount);
                        card.dataset.lastExtendedAt = String(patch.lastExtendedAt);
                    });
                },

                refreshSellersGrid(html) {
                    const grid = document.getElementById('sellers-grid');
                    if (!grid) return;

                    const timerDisplays = {};
                    document.querySelectorAll('.seller-card[data-serving="true"]').forEach((card) => {
                        const queueId = card.dataset.queueId;
                        const timerEl = card.querySelector('.seller-timer');
                        if (queueId && timerEl) {
                            timerDisplays[queueId] = timerEl.innerText;
                        }
                    });

                    this.captureServeTimerAnchors();
                    grid.innerHTML = html;
                    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                        window.Alpine.initTree(grid);
                    }
                    this.mergeServingTimers(this._pendingServingTimers || {});
                    this._pendingServingTimers = null;
                    this.applyExtensionOverridesToGrid();
                    this.applyServeTimerAnchors();
                    this.restoreTimerDisplays(timerDisplays);
                    this.updateTimers();
                    this.syncPipFromPoll(html);
                },

                isTabInBackground() {
                    return document.visibilityState === 'hidden';
                },

                ensureNotificationPermission() {
                    if (!('Notification' in window) || Notification.permission !== 'default') return;
                    Notification.requestPermission();
                },

                showDesktopNotification(title, body, options = {}) {
                    if (!('Notification' in window) || Notification.permission !== 'granted') return null;
                    try {
                        const notification = new Notification(title, {
                            body,
                            icon: '/images/aromas_logo_recortado.png',
                            tag: options.tag || undefined,
                            renotify: options.renotify === true,
                            requireInteraction: options.requireInteraction === true,
                            silent: false,
                        });
                        notification.onclick = () => {
                            window.focus();
                            notification.close();
                        };
                        return notification;
                    } catch (e) {
                        return null;
                    }
                },

                startAudioKeepalive() {
                    if (this._keepaliveAudio) return;
                    const el = document.createElement('audio');
                    el.loop = true;
                    el.volume = 0.001;
                    el.setAttribute('aria-hidden', 'true');
                    el.src = 'data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA';
                    el.play().then(() => {
                        this._keepaliveAudio = el;
                    }).catch(() => {});
                },

                unlockMedia() {
                    if (this.mediaUnlocked) {
                        this.startAudioKeepalive();
                        return;
                    }
                    this.mediaUnlocked = true;
                    sessionStorage.setItem('ventas_media_unlocked', '1');
                    this.startAudioKeepalive();
                    ['bell', 'bell_vip', 'soft_alert'].forEach((id) => {
                        const el = document.getElementById(id);
                        if (!el) return;
                        const prevVolume = el.volume;
                        el.volume = 0.01;
                        el.currentTime = 0;
                        el.play().then(() => {
                            el.pause();
                            el.currentTime = 0;
                            el.volume = prevVolume || 0.8;
                        }).catch(() => {});
                    });
                    if ('speechSynthesis' in window) {
                        window.speechSynthesis.cancel();
                        const warmup = new SpeechSynthesisUtterance(' ');
                        warmup.volume = 0.01;
                        if (this.spanishVoice) warmup.voice = this.spanishVoice;
                        else warmup.lang = 'es-MX';
                        window.speechSynthesis.speak(warmup);
                    }
                },

                // Sonar intermitente durante la ventana de solicitud de prórroga
                playSoftAlertIntervals(key) {
                    const el = document.getElementById('soft_alert');
                    if (!el) return;
                    const pulse = () => {
                        // Si ya no está en prórroga o cambió de key, detener
                        if (!this.prorrogaIntervalActive || !this.prorrogaIntervalActive[key]) return;
                        el.volume = 0.8;
                        el.currentTime = 0;
                        el.play().catch((e) => { console.warn('Audio autoplay blocked:', e); });
                        setTimeout(pulse, this.softAlertIntervalMs);
                    };
                    pulse();
                },

                stopAlertAudio() {
                    ['bell', 'bell_vip'].forEach((id) => {
                        const el = document.getElementById(id);
                        if (!el) return;
                        el.onended = null;
                        el.pause();
                        el.currentTime = 0;
                    });
                },

                waitForAnnouncementIdle() {
                    return new Promise((resolve) => {
                        let checks = 0;
                        const check = () => {
                            checks++;
                            const bell = document.getElementById('bell');
                            const bellVip = document.getElementById('bell_vip');
                            const audioBusy = (bell && !bell.paused && !bell.ended) || (bellVip && !bellVip.paused && !bellVip.ended);
                            const speechBusy = window.speechSynthesis && (window.speechSynthesis.speaking || window.speechSynthesis.pending);
                            if ((!audioBusy && !speechBusy) || checks > 100) { // Timeout de 15s aprox
                                resolve();
                                return;
                            }
                            setTimeout(check, 150);
                        };
                        check();
                    });
                },

                finishQueuedTask(callback) {
                    this.waitForAnnouncementIdle().then(() => {
                        const delay = this.taskQueue.length > 0 ? 3000 : 5000;
                        setTimeout(callback, delay);
                    });
                },

                fetchUpdates() {
                    this.isLoading = true;

                    fetch("{{ route('ventas.poll') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.timing) {
                                this.timingSettings.attentionMins = Number(data.timing.attention_minutes) || this.timingSettings.attentionMins;
                                this.timingSettings.extensionMins = Number(data.timing.extension_minutes) || this.timingSettings.extensionMins;
                            }
                            this.waitingCount = data.waiting;
                            if (this.pipActive && this.pipWindow && !this.pipWindow.closed) {
                                window.VentasPip.syncPipWaitingCount(this.pipWindow, this.waitingCount);
                            }
                            if (data.html) {
                                this._pendingServingTimers = data.serving_timers;
                                this.refreshSellersGrid(data.html);
                            }

                            if (this.showRetentionModal) {
                                this.fetchRetentionList();
                            }

                            if (data.incidents && data.incidents.length > 0) {
                                data.incidents.forEach(inc => {
                                    if (!this.alertedIncidents.includes(inc.id)) {
                                        this.alertedIncidents.push(inc.id);
                                        this.enqueueTask({
                                            type: 'speech',
                                            message: inc.message,
                                            desktopNotification: {
                                                title: 'Incidente en ventas',
                                                body: inc.message,
                                                tag: `incident-${inc.id}`,
                                                renotify: true,
                                                onlyWhenHidden: true,
                                            },
                                        });
                                    }
                                });
                            }

                            if (data.alerts && data.alerts.length > 0) {
                                data.alerts.forEach(alert => {
                                    if (!this.shouldAnnounceAssignment(alert)) {
                                        return;
                                    }
                                    if (this.pipActive && !this.isAlertRelevantForPip(alert)) {
                                        this.markAlertAsSeen(alert);
                                        return;
                                    }
                                    this.enqueueTask({ type: 'megaAlert', data: alert });
                                });
                            }
                        })
                        .finally(() => {
                            setTimeout(() => this.isLoading = false, 500);
                        });
                },

                enqueueTask(task) {
                    this.taskQueue.push(task);
                    this.processTaskQueue();
                },

                processTaskQueue() {
                    if (this.isProcessingTask || this.taskQueue.length === 0) return;
                    this.isProcessingTask = true;
                    
                    const task = this.taskQueue.shift();

                    this.waitForAnnouncementIdle().then(() => {
                        this.speechSessionId += 1;
                        const sessionId = this.speechSessionId;
                        this.stopAlertAudio();
                        
                        if (task.type === 'megaAlert') {
                            this.executeMegaAlert(task.data, sessionId, () => {
                                this.showMegaAlert = false;
                                this.isProcessingTask = false;
                                this.finishQueuedTask(() => this.processTaskQueue());
                            });
                        } else if (task.type === 'speech') {
                            if (task.desktopNotification) {
                                const n = task.desktopNotification;
                                if (!n.onlyWhenHidden || this.isTabInBackground()) {
                                    this.showDesktopNotification(
                                        n.title || 'Aromas Ventas',
                                        n.body || task.message,
                                        n
                                    );
                                }
                            } else if (this.isTabInBackground()) {
                                this.showDesktopNotification('Aromas Ventas', task.message, {
                                    tag: 'ventas-speech',
                                    renotify: true,
                                });
                            }
                            this.executeSpeech(task.message, sessionId, task.useBell === true, (success) => {
                                if (typeof task.onComplete === 'function') {
                                    task.onComplete(success);
                                }
                                this.isProcessingTask = false;
                                this.finishQueuedTask(() => this.processTaskQueue());
                            });
                        } else {
                            this.isProcessingTask = false;
                            this.processTaskQueue();
                        }
                    });
                },

                executeMegaAlert(data, sessionId, callback) {
                    this.alertData = data;
                    const usePipAlert = this.pipActive && this.pipWindow && !this.pipWindow.closed && this.isAlertRelevantForPip(data);

                    let hasCalledDone = false;
                    let fallbackTimer = null;
                    let speechRetryCount = 0;
                    const done = (reason) => {
                        if (hasCalledDone || sessionId !== this.speechSessionId) return;
                        hasCalledDone = true;
                        clearTimeout(fallbackTimer);
                        if (usePipAlert) {
                            window.VentasPip.hidePipMegaAlert(this.pipWindow);
                        }
                        callback();
                    };

                    if (usePipAlert) {
                        this.showMegaAlert = false;
                        window.VentasPip.showPipMegaAlert(this.pipWindow, data, () => done('pip-dismiss'));
                    } else {
                        this.showMegaAlert = true;
                    }
                    this.alertTimer = 5;

                    fallbackTimer = setTimeout(() => done('fallback-timeout'), 25000);

                    let audioId = data.use_premium_alert ? 'bell_vip' : 'bell';
                    let audio = document.getElementById(audioId) || document.getElementById('bell');

                    const speakClientAssignment = () => {
                        if (sessionId !== this.speechSessionId) return;
                        if (audio) audio.onended = null;
                        
                        if (!('speechSynthesis' in window)) {
                            done('no-tts');
                            return;
                        }

                        if (window.speechSynthesis.speaking || window.speechSynthesis.pending) {
                            window.speechSynthesis.cancel();
                        }

                        let sellerLabel = (data.seller || '').trim().split(/\s+/)[0] || 'Vendedor';
                            let mensaje = sellerLabel + " ¡tienes un nuevo cliente asignado!. " + data.client;
                        window.currentUtterance = new SpeechSynthesisUtterance(mensaje);
                        
                        if (this.spanishVoice) window.currentUtterance.voice = this.spanishVoice;
                        else window.currentUtterance.lang = 'es-MX';
                        
                        window.currentUtterance.rate = 0.9;
                        
                        window.currentUtterance.onend = () => done('speech-onend');
                        window.currentUtterance.onerror = (event) => {
                            if ((event.error === 'interrupted' || event.error === 'canceled') && speechRetryCount < 1) {
                                speechRetryCount += 1;
                                setTimeout(speakClientAssignment, 200);
                                return;
                            }
                            if (event.error === 'interrupted' || event.error === 'canceled') return;
                            done('speech-onerror');
                        };
                        
                        window.speechSynthesis.speak(window.currentUtterance);
                    };

                    if (audio) {
                        audio.onended = speakClientAssignment;
                        audio.currentTime = 0; 
                        audio.volume = 1.0;
                        audio.play().catch(() => {
                            speakClientAssignment();
                        });
                    } else {
                        speakClientAssignment();
                    }

                    this.showDesktopNotification(
                        data.use_premium_alert ? '⭐ ¡Cliente Premium Asignado!' : '¡Nuevo Cliente Asignado!',
                        `Turno: ${data.folio}\nCliente: ${data.client}\nVendedor: ${data.seller}`,
                        {
                            tag: `assignment-${data.folio || data.id}`,
                            requireInteraction: true,
                            renotify: true,
                        }
                    );

                    let timerInterval = setInterval(() => {
                        this.alertTimer--;
                        if (this.alertTimer <= 0) clearInterval(timerInterval);
                    }, 1000);
                },

                closeAlert() {
                    this.showMegaAlert = false;
                    if (this.pipWindow && !this.pipWindow.closed) {
                        window.VentasPip.hidePipMegaAlert(this.pipWindow);
                    }
                },

                processFinishService(shiftId, queueId) {
                    // Terminamos el servicio por AJAX para no recargar la página
                    fetch("{{ route('ventas.finish-service') }}", {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ shift_id: shiftId })
                    }).then(r => r.json()).then(data => {
                        if(data.success) {
                            this.speakMessage('Venta terminada.');
                            this.fetchUpdates(); // Refresca el Grid para mostrar estado morado
                            this.openRatingModal(shiftId, queueId);
                        }
                    });
                },

                requestExtension(payload) {
                    this.unlockMedia();
                    fetch(this.requestExtensionUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ queue_id: payload.queue_id })
                    }).then(async (r) => {
                        const data = await r.json().catch(() => ({}));
                        if (!r.ok || !data.success) {
                            alert(data.message || 'Error al solicitar la prórroga.');
                            return;
                        }
                        this.applyExtensionToCard(payload.queue_id, data.extension_count, data.last_extended_at);
                        this.updateTimers();
                        if (this.receivesProrrogaAlerts) {
                            const extensionMsg = 'Usuario, tienes una nueva notificación';
                            this.enqueueTask({
                                type: 'speech',
                                message: extensionMsg,
                                desktopNotification: {
                                    title: 'Prórroga solicitada',
                                    body: `El vendedor ${payload.seller_name || 'Vendedor'} solicitó una prórroga de atención.`,
                                    tag: `extension-request-${payload.queue_id}`,
                                    renotify: true,
                                    onlyWhenHidden: true,
                                },
                            });
                        }
                        this.fetchUpdates();
                    }).catch(() => {
                        alert('Error al solicitar la prórroga.');
                    });
                },

                speakMessage(message, options = {}) {
                    this.enqueueTask({
                        type: 'speech',
                        message: message,
                        useBell: options.useBell === true,
                        onComplete: options.onComplete,
                        desktopNotification: options.desktopNotification || null,
                    });
                },

                executeSpeech(message, sessionId, useBell, callback) {
                    if (!('speechSynthesis' in window)) {
                        callback(false);
                        return;
                    }

                    let hasCalledCallback = false;
                    let fallbackTimer = null;
                    let speechRetryCount = 0;
                    const done = (reason) => {
                        if (hasCalledCallback || sessionId !== this.speechSessionId) return;
                        hasCalledCallback = true;
                        clearTimeout(fallbackTimer);
                        callback(reason === 'speech-onend');
                    };

                    const speakNow = () => {
                        if (sessionId !== this.speechSessionId) return;
                        if (window.speechSynthesis.speaking || window.speechSynthesis.pending) {
                            window.speechSynthesis.cancel();
                        }

                        const utterance = new SpeechSynthesisUtterance(message);
                        if (this.spanishVoice) {
                            utterance.voice = this.spanishVoice;
                        } else {
                            utterance.lang = 'es-MX';
                        }
                        utterance.rate = 0.9;
                        
                        utterance.onend = () => {
                            done('speech-onend');
                        };
                        utterance.onerror = (event) => {
                            if (event.error === 'not-allowed' && !this.mediaUnlocked) {
                                done('speech-not-allowed');
                                return;
                            }
                            if ((event.error === 'interrupted' || event.error === 'canceled') && speechRetryCount < 1) {
                                speechRetryCount += 1;
                                setTimeout(speakNow, 200);
                                return;
                            }
                            if (event.error === 'interrupted' || event.error === 'canceled') return;
                            done('speech-onerror');
                        };
                        
                        window.currentUtterance = utterance;
                        window.speechSynthesis.speak(utterance);
                        fallbackTimer = setTimeout(() => done('fallback-timeout'), 20000);
                    };

                    if (useBell) {
                        let audio = document.getElementById('bell');
                        if (audio) {
                            audio.onended = speakNow;
                            audio.currentTime = 0;
                            audio.volume = 0.5;
                            audio.play().catch(() => speakNow());
                        } else {
                            speakNow();
                        }
                    } else {
                        speakNow();
                    }
                },

                openRatingModal(shiftId, queueId) {
                    this.ratingShiftId = shiftId;
                    this.ratingQueueId = queueId;
                    this.ratingStars = 0;
                    this.ratingTags = [];
                    this.ratingComment = '';
                    if (this.pipActive && this.pipWindow && !this.pipWindow.closed && window.VentasPip?.showExtractRatingModal) {
                        window.VentasPip.showExtractRatingModal(this.pipWindow, this);
                    } else {
                        this.showRatingModal = true;
                    }
                },

                availableTags() {
                    // Lógica tipo Uber: Tags cambian según si la calificación es buena o mala
                    if (this.ratingStars >= 4) return ['Decisión Rápida', 'Amable', 'Directo al Grano', 'Excelente Actitud'];
                    if (this.ratingStars === 3) return ['Indeciso', 'Poca Interacción', 'Lento'];
                    if (this.ratingStars > 0) return ['Mucho en el Celular', 'Grosero', 'No sabía qué quería', 'Muy Lento'];
                    return [];
                },

                toggleTag(tag) {
                    if (this.ratingTags.includes(tag)) {
                        this.ratingTags = this.ratingTags.filter(t => t !== tag);
                    } else {
                        this.ratingTags.push(tag);
                    }
                },

                skipRating() {
                    this.ratingStars = 0; // 0 cuenta como "Omitir" en el backend
                    this.submitRating();
                },

                submitRating() {
                    fetch("{{ route('ventas.submit-rating') }}", {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            shift_id: this.ratingShiftId,
                            queue_id: this.ratingQueueId,
                            stars: this.ratingStars,
                            tags: this.ratingTags,
                            comments: this.ratingComment
                        })
                    }).then(r => r.json()).then(() => {
                        this.showRatingModal = false;
                        if (this.pipWindow && !this.pipWindow.closed && window.VentasPip?.hideExtractRatingModal) {
                            window.VentasPip.hideExtractRatingModal(this.pipWindow);
                        }
                        this.fetchUpdates();
                    });
                }
            }
        }
    </script>
</body>

</html>