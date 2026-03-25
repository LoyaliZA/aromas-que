<!DOCTYPE html>
<html lang="es-MX" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tablero de Ventas - Aromas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                    @if(in_array(auth()->user()->role, ['MANAGER', 'ADMIN']))
                    <button @click="openRetentionModal()" class="bg-blue-600 hover:bg-blue-500 text-white px-5 py-3 rounded-xl font-bold text-sm flex items-center gap-2 shadow-[0_0_15px_rgba(37,99,235,0.3)] transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        RETENCIÓN
                    </button>
                    <div class="w-px h-10 bg-gray-700"></div>
                    @endif

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

        {{-- MODALES BREAK Y COMIDA OCULTOS (SIN CAMBIOS AL HTML PREVIO) --}}
        <div x-show="showBreakModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" x-transition>
            <div class="bg-gray-800 rounded-2xl border border-gray-700 p-8 w-full max-w-lg shadow-2xl" @click.away="showBreakModal = false">
                <h3 class="text-2xl font-black text-white mb-6 text-center uppercase tracking-wide">Selecciona Motivo</h3>
                <form id="break-form" action="{{ route('ventas.toggle-break') }}" method="POST" class="grid grid-cols-2 gap-4">
                    @csrf
                    <input type="hidden" name="shift_id" :value="breakShiftId">
                    <input type="hidden" name="reason" id="break-reason-input">
                    <button type="button" @click="selectBreakReason('BATHROOM')" class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl hover:bg-aromas-highlight hover:text-aromas-main text-gray-300 font-bold flex flex-col items-center gap-3 transition-all">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S12 3 12 3s-4.5 4.03-4.5 9 2.015 9 4.5 9z"></path>
                        </svg>
                        Baño
                    </button>
                    <button type="button" @click="selectBreakReason('LUNCH')" :class="hasTakenLunch ? 'opacity-30 cursor-not-allowed' : 'hover:bg-aromas-highlight hover:text-aromas-main'" class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl text-gray-300 font-bold flex flex-col items-center gap-3 transition-all">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h14a2 2 0 012 2v6z"></path>
                        </svg>
                        Comida <span x-show="hasTakenLunch" class="absolute bottom-2 text-[10px] text-red-400 font-black">Ya Tomado</span>
                    </button>
                    <button type="button" @click="selectBreakReason('ERRAND')" class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl hover:bg-aromas-highlight hover:text-aromas-main text-gray-300 font-bold flex flex-col items-center gap-3 transition-all">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg> Encargo
                    </button>
                    <button type="button" @click="selectBreakReason('PACKAGING')" class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl hover:bg-aromas-highlight hover:text-aromas-main text-gray-300 font-bold flex flex-col items-center gap-3 transition-all">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg> Paquetería
                    </button>
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
        @if(in_array(auth()->user()->role, ['MANAGER', 'ADMIN']))
        <div x-show="showRetentionModal" style="display: none;" class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/95 backdrop-blur-md" x-transition>
            <div class="bg-gray-900 rounded-2xl border-2 border-blue-500 shadow-[0_0_50px_rgba(37,99,235,0.2)] p-6 w-full max-w-2xl flex flex-col max-h-[85vh]">
                <div class="flex justify-between items-center mb-6 border-b border-gray-800 pb-4">
                    <h3 class="text-2xl font-black text-blue-400 uppercase tracking-widest flex items-center gap-3">Re-Atención</h3>
                    <button @click="showRetentionModal = false" class="text-gray-500 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
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
            :class="alertData.client_type === 'VIP' ? 'bg-yellow-900/90' : 'bg-blue-900/95'" x-transition>
            <div class="text-center p-8 max-w-5xl w-full">

                <h2 class="text-3xl uppercase tracking-[0.2em] font-bold mb-8"
                    :class="alertData.client_type === 'VIP' ? 'text-yellow-200' : 'text-blue-200'">Nueva Asignación</h2>

                {{-- Contenedor dinámico Dorado/Azul --}}
                <div class="rounded-[2rem] p-12 shadow-2xl mx-auto transform transition-all border-4"
                    :class="alertData.client_type === 'VIP' ? 'border-yellow-400 bg-gradient-to-br from-yellow-50 to-white shadow-[0_0_50px_rgba(234,179,8,0.3)]' : 'border-blue-400/50 bg-white'">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div>
                            <p class="text-sm text-gray-400 uppercase font-bold mb-2 tracking-widest">Vendedor</p>
                            <p class="text-5xl font-black leading-tight"
                                :class="alertData.client_type === 'VIP' ? 'text-yellow-600' : 'text-blue-600'"
                                x-text="alertData.seller"></p>
                        </div>
                        <div class="pt-8 md:pt-0 md:pl-12">
                            <p class="text-sm text-gray-400 uppercase font-bold mb-2 tracking-widest">Cliente (Turno: <span x-text="alertData.folio"></span>)</p>
                            <div class="flex justify-center items-center gap-3 mb-2">
                                <span x-show="alertData.client_type === 'VIP'" class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider">VIP</span>
                                <span x-show="alertData.has_disability" class="bg-blue-500 text-white px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider">PRIORIDAD</span>
                            </div>
                            <p class="text-4xl font-black text-gray-800 leading-tight" x-text="alertData.client"></p>
                        </div>
                    </div>
                </div>

                <button @click="closeAlert()" :disabled="alertTimer > 0" class="mt-12 px-12 py-5 rounded-2xl font-black text-xl transition-all duration-300 tracking-wider bg-white shadow-xl transform hover:-translate-y-1"
                    :class="alertData.client_type === 'VIP' ? 'text-yellow-900 hover:bg-yellow-50 shadow-white/20' : 'text-blue-900 hover:bg-blue-50 shadow-white/10'">
                    <span x-show="alertTimer > 0">ESPERE (<span x-text="alertTimer"></span>)</span>
                    <span x-show="alertTimer <= 0">ENTERADO</span>
                </button>
            </div>
        </div>

    </div>

    {{-- AUDIOS --}}
    <audio id="bell" src="{{ asset('audio/bell.mp3') }}" preload="auto"></audio>
    <audio id="bell_vip" src="{{ asset('audio/bell_vip.mp3') }}" preload="auto"></audio>

    <script>
        function salesDashboard() {
            return {
                waitingCount: @json($clientsWaiting ?? 0),
                showBreakModal: false,
                showLunchConfirmModal: false,
                breakShiftId: null,
                hasTakenLunch: false,

                showRetentionModal: false,
                retentionList: [],
                availableSellers: [], // <-- NUEVA VARIABLE

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
                    client_type: 'REGULAR',
                    has_disability: false
                },
                alertTimer: 5,
                isLoading: false,
                spanishVoice: null, // <-- NUEVA VARIABLE AQUÍ

                init() {

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
                        this.hasTakenLunch = event.detail.hasTakenLunch;
                        this.showBreakModal = true;
                    });

                    // Solicitar permisos para Notificaciones y Voz al hacer el primer clic
                    document.body.addEventListener('click', () => {
                        if ("Notification" in window && Notification.permission !== "granted") {
                            Notification.requestPermission();
                        }
                    }, {
                        once: true
                    });

                    setInterval(() => {
                        this.fetchUpdates();
                    }, 3000);
                    setInterval(() => {
                        this.updateTimers();
                    }, 1000);
                },

                openRetentionModal() {
                    this.fetchRetentionList();
                    this.showRetentionModal = true;
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
                                this.showRetentionModal = false;
                                this.fetchUpdates();
                            } else {
                                alert(data.message || 'Error al procesar la retención.');
                            }
                        });
                },

                selectBreakReason(reason) {
                    if (reason === 'LUNCH') {
                        if (this.hasTakenLunch) return;
                        this.showBreakModal = false;
                        setTimeout(() => {
                            this.showLunchConfirmModal = true;
                        }, 200);
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
                        let startTime = parseInt(card.dataset.startTime);
                        let elapsedSecs = Math.floor((now - startTime) / 1000);
                        if (elapsedSecs < 0) elapsedSecs = 0;

                        let mins = Math.floor(elapsedSecs / 60);
                        let secs = elapsedSecs % 60;
                        let timerEl = card.querySelector('.seller-timer');
                        if (timerEl) {
                            timerEl.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                            timerEl.className = mins >= 15 ? "seller-timer text-xl font-mono font-bold text-yellow-500 tracking-wider" : "seller-timer text-xl font-mono font-bold text-gray-300 tracking-wider";
                        }
                    });

                    const breakCards = document.querySelectorAll('.seller-card[data-on-break="true"]');
                    breakCards.forEach(card => {
                        let breakStartTime = parseInt(card.dataset.breakStartTime);
                        if (!breakStartTime) return;
                        let elapsedSecs = Math.floor((now - breakStartTime) / 1000);
                        let timerEl = card.querySelector('.break-timer');
                        if (timerEl) {
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
                },

                fetchUpdates() {
                    if (this.showMegaAlert) return;
                    this.isLoading = true;

                    fetch("{{ route('ventas.poll') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            this.waitingCount = data.waiting;
                            const grid = document.getElementById('sellers-grid');
                            if (grid) {
                                grid.innerHTML = data.html;
                                this.updateTimers();
                            }
                            if (data.alert) this.triggerMegaAlert(data.alert);
                        })
                        .finally(() => {
                            setTimeout(() => this.isLoading = false, 500);
                        });
                },

                triggerMegaAlert(data) {
                    this.alertData = data;
                    this.showMegaAlert = true;
                    this.alertTimer = 5;

                    
                    // Función separada para el Texto a Voz (TTS) con tu mensaje personalizado
                    const hablarMensaje = () => {
                        if ('speechSynthesis' in window) {
                            // 1. Limpiar cualquier voz trabada anterior
                            window.speechSynthesis.cancel();

                            let mensaje = data.seller + " ¡tienes un nuevo cliente asignado!. " + data.client;

                            // 2. Usar variable global para evitar que Chrome corte el audio
                            window.currentUtterance = new SpeechSynthesisUtterance(mensaje);
                            
                            if (this.spanishVoice) {
                                window.currentUtterance.voice = this.spanishVoice;
                            } else {
                                window.currentUtterance.lang = 'es-MX'; // Fallback
                            }
                            
                            window.currentUtterance.rate = 0.9; // Velocidad cómoda
                            window.speechSynthesis.speak(window.currentUtterance);
                        }
                    };

                    // 1. REPRODUCIR SONIDO (Dependiendo si es VIP)
                    let audioId = data.client_type === 'VIP' ? 'bell_vip' : 'bell';
                    let audio = document.getElementById(audioId);

                    if (!audio) audio = document.getElementById('bell');

                    if (audio) {
                        // Asegurarnos de limpiar eventos anteriores
                        audio.onended = null;
                        
                        // Rebobinar el audio al inicio (VITAL para que suene más de una vez)
                        audio.currentTime = 0; 
                        
                        // Asignar el evento de hablar justo cuando termine el timbre
                        audio.onended = hablarMensaje;

                        // Reproducir
                        audio.play().catch(e => {
                            console.log("Audio bloqueado por el navegador:", e);
                            hablarMensaje(); // Si el navegador bloquea el timbre, que al menos hable
                        });
                    } else {
                        // Si por algún motivo no encuentra la etiqueta <audio> en el HTML
                        hablarMensaje();
                    }

                    // 3. NOTIFICACIÓN DE SISTEMA OPERATIVO
                    if ("Notification" in window && Notification.permission === "granted") {
                        new Notification(data.client_type === 'VIP' ? "⭐ ¡Cliente VIP Asignado!" : "¡Nuevo Cliente Asignado!", {
                            body: `Turno: ${data.folio}\nCliente: ${data.client}\nVendedor: ${data.seller}`,
                            icon: '/images/aromas_logo_recortado.png'
                        });
                    }

                    let timerInterval = setInterval(() => {
                        this.alertTimer--;
                        if (this.alertTimer <= 0) clearInterval(timerInterval);
                    }, 1000);

                    setTimeout(() => {
                        if (this.showMegaAlert) this.closeAlert();
                    }, 15000);
                },

                closeAlert() {
                    this.showMegaAlert = false;
                },

                processFinishService(shiftId, queueId) {
                    // Terminamos el servicio por AJAX para no recargar la página
                    fetch("{{ route('ventas.finish-service') }}", {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Content-Type': 'application/json' },
                        body: JSON.stringify({ shift_id: shiftId })
                    }).then(r => r.json()).then(data => {
                        if(data.success) {
                            this.fetchUpdates(); // Refresca el Grid para mostrar estado morado
                            this.openRatingModal(shiftId, queueId);
                        }
                    });
                },

                openRatingModal(shiftId, queueId) {
                    this.ratingShiftId = shiftId;
                    this.ratingQueueId = queueId;
                    this.ratingStars = 0;
                    this.ratingTags = [];
                    this.ratingComment = '';
                    this.showRatingModal = true;
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
                        this.fetchUpdates(); // Refresca el Grid para volver a estado Disponible (verde)
                    });
                }
            }
        }
    </script>
</body>

</html>