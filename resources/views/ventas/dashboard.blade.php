<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tablero de Ventas - Aromas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white font-sans antialiased overflow-hidden">

    <div class="h-screen w-full flex flex-col" x-data="salesDashboard()">
        
        {{-- HEADER EXCLUSIVO VENDEDORES --}}
        <div class="bg-gray-900/90 backdrop-blur-md border-b border-gray-800 px-8 py-5 shadow-2xl sticky top-0 z-50">
            <div class="flex justify-between items-center w-full">
                
                {{-- TÍTULO --}}
                <div class="flex items-center gap-5">
                    <div class="bg-gradient-to-br from-aromas-highlight to-yellow-600 p-3 rounded-xl text-aromas-main shadow-lg shadow-yellow-500/20">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-black text-white tracking-tight uppercase">VENDEDORES</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="w-2 h-2 rounded-full" :class="isLoading ? 'bg-yellow-500 animate-pulse' : 'bg-green-500'"></span>
                            <p class="text-xs text-gray-400 font-medium" x-text="isLoading ? 'Sincronizando...' : 'Sistema en Línea'"></p>
                        </div>
                    </div>
                </div>

                {{-- CONTADOR DE ESPERA --}}
                <div class="flex items-center gap-6">
                    <div class="text-right">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-widest mb-1">En Fila</p>
                        <div class="flex items-baseline justify-end gap-1">
                            <span class="text-4xl font-black text-white" x-text="waitingCount">{{ $clientsWaiting }}</span>
                            <span class="text-sm text-gray-600">clientes</span>
                        </div>
                    </div>
                    
                    {{-- Botón Salir --}}
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="p-3 rounded-full bg-gray-800 hover:bg-red-500/20 text-gray-500 hover:text-red-400 transition-colors" title="Salir">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- GRID PRINCIPAL --}}
        <div class="flex-1 overflow-y-auto p-8">
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                     class="fixed top-24 right-8 z-50 bg-green-500 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 animate-fade-in-down">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('success') }}</span>
                </div>
            @endif
            
            @if(session('error'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" 
                     class="fixed top-24 right-8 z-50 bg-red-600 text-white px-6 py-4 rounded-xl shadow-2xl flex items-center gap-3 animate-fade-in-down">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <div id="sellers-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 content-start">
                @include('ventas.partials.sellers-grid', ['sellers' => $sellers])
            </div>
        </div>

        {{-- MODAL DE BREAKS --}}
        <div x-show="showBreakModal" style="display: none;" 
             class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm"
             x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            
            <div class="bg-gray-800 rounded-2xl border border-gray-700 p-8 w-full max-w-lg shadow-2xl transform transition-all" @click.away="showBreakModal = false">
                <h3 class="text-2xl font-black text-white mb-6 text-center uppercase tracking-wide">Selecciona Motivo</h3>
                
                <form id="break-form" action="{{ route('ventas.toggle-break') }}" method="POST" class="grid grid-cols-2 gap-4">
                    @csrf
                    <input type="hidden" name="shift_id" :value="breakShiftId">
                    <input type="hidden" name="reason" id="break-reason-input">
                    
                    <button type="button" @click="selectBreakReason('BATHROOM')" class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl hover:bg-aromas-highlight hover:text-aromas-main hover:border-aromas-highlight text-gray-300 font-bold flex flex-col items-center gap-3 transition-all duration-200 group">
                        <svg class="w-10 h-10 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S12 3 12 3s-4.5 4.03-4.5 9 2.015 9 4.5 9z"></path></svg>
                        <span class="text-sm uppercase tracking-wider">Baño</span>
                    </button>

                    <button type="button" @click="selectBreakReason('LUNCH')" 
                            :class="hasTakenLunch ? 'opacity-30 cursor-not-allowed border-red-500/30 bg-red-500/10' : 'hover:bg-aromas-highlight hover:text-aromas-main hover:border-aromas-highlight'"
                            class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl text-gray-300 font-bold flex flex-col items-center gap-3 transition-all duration-200 group">
                        <svg class="w-10 h-10 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15a2 2 0 01-2 2H5a2 2 0 01-2-2V9a2 2 0 012-2h14a2 2 0 012 2v6z"></path></svg>
                        <span class="text-sm uppercase tracking-wider">Comida</span>
                        <span x-show="hasTakenLunch" class="absolute bottom-2 text-[10px] text-red-400 font-black uppercase tracking-widest">Ya Tomado</span>
                    </button>

                    <button type="button" @click="selectBreakReason('ERRAND')" class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl hover:bg-aromas-highlight hover:text-aromas-main hover:border-aromas-highlight text-gray-300 font-bold flex flex-col items-center gap-3 transition-all duration-200 group">
                        <svg class="w-10 h-10 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        <span class="text-sm uppercase tracking-wider">Encargo</span>
                    </button>

                    <button type="button" @click="selectBreakReason('PACKAGING')" class="relative p-6 bg-gray-700/50 border border-gray-600 rounded-xl hover:bg-aromas-highlight hover:text-aromas-main hover:border-aromas-highlight text-gray-300 font-bold flex flex-col items-center gap-3 transition-all duration-200 group">
                        <svg class="w-10 h-10 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        <span class="text-sm uppercase tracking-wider">Paquetería</span>
                    </button>
                </form>
                
                <button @click="showBreakModal = false" class="mt-6 w-full py-4 text-gray-500 font-bold hover:text-white transition-colors uppercase text-sm tracking-widest">
                    Cancelar
                </button>
            </div>
        </div>

        {{-- NUEVO MODAL: CONFIRMACIÓN DE COMIDA ESTILIZADO --}}
        <div x-show="showLunchConfirmModal" style="display: none;" 
             class="fixed inset-0 z-[70] flex items-center justify-center p-4 bg-black/95 backdrop-blur-md"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            
            <div class="bg-gray-900 rounded-2xl border-2 border-aromas-highlight shadow-[0_0_50px_rgba(253,201,116,0.15)] p-8 w-full max-w-lg text-center" @click.away="showLunchConfirmModal = false">
                <div class="w-20 h-20 mx-auto bg-aromas-highlight/10 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-aromas-highlight animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-white mb-2 uppercase tracking-widest">¿Iniciar Comida?</h3>
                <p class="text-gray-300 mb-2">Recuerda que solo tienes <strong>un break de comida</strong> por jornada.</p>
                <p class="text-sm text-gray-500 mb-8 italic">Si deseas tomarlo ahora tienes 3 minutos para comenzar tu break ;3.</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <button @click="showLunchConfirmModal = false" type="button" class="w-full py-4 bg-gray-700 hover:bg-gray-600 text-white font-bold rounded-xl shadow-lg uppercase text-xs tracking-wider transition-transform active:scale-95">
                        Cancelar
                    </button>
                    <button @click="executeBreak('LUNCH')" type="button" class="w-full py-4 bg-aromas-highlight hover:bg-yellow-500 text-gray-900 font-black rounded-xl shadow-lg uppercase text-xs tracking-wider transition-transform active:scale-95">
                        Sí, Iniciar
                    </button>
                </div>
            </div>
        </div>

        {{-- MODAL: EXTENSIÓN DE TIEMPO DE 15 MINUTOS --}}
        <div x-show="showExtensionModal" style="display: none;" 
             class="fixed inset-0 z-[80] flex items-center justify-center p-4 bg-black/95 backdrop-blur-md"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            
            <div class="bg-gray-900 rounded-2xl border-2 border-yellow-500 shadow-[0_0_50px_rgba(234,179,8,0.2)] p-8 w-full max-w-lg text-center">
                <div class="w-20 h-20 mx-auto bg-yellow-500/10 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-yellow-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="text-2xl font-black text-yellow-400 mb-2 uppercase tracking-widest">Tiempo Excedido</h3>
                <p class="text-gray-300 mb-2">El vendedor <strong x-text="extensionSellerName" class="text-white font-bold"></strong> lleva más de 15 minutos con el cliente <strong x-text="extensionClientName" class="text-white font-bold"></strong>.</p>
                <p class="text-sm text-gray-500 mb-8 italic">¿El cliente sigue en atención o olvidaste finalizar el turno?</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <button @click="confirmExtension()" class="py-4 bg-yellow-600 hover:bg-yellow-500 text-white font-bold rounded-xl shadow-lg uppercase text-xs tracking-wider transition-transform active:scale-95">
                        Sí, sigue aquí
                    </button>
                    <form method="POST" action="{{ route('ventas.finish-service') }}" class="w-full">
                        @csrf
                        <input type="hidden" name="shift_id" :value="extensionShiftId">
                        <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl shadow-lg uppercase text-xs tracking-wider transition-transform active:scale-95">
                            Ya terminó
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- MEGA NOTIFICACIÓN --}}
        <div x-show="showMegaAlert" style="display: none;" 
             class="fixed inset-0 z-[100] flex items-center justify-center bg-blue-900/95 backdrop-blur-xl"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            
            <div class="text-center p-8 max-w-5xl w-full">
                <div class="mb-8 animate-bounce">
                    <span class="inline-block p-6 rounded-full bg-white/10 border-4 border-white/20 shadow-[0_0_50px_rgba(255,255,255,0.2)]">
                        <svg class="w-24 h-24 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </span>
                </div>
                
                <h2 class="text-3xl text-blue-200 uppercase tracking-[0.2em] font-bold mb-8">Nueva Asignación</h2>
                
                <div class="bg-white text-gray-900 rounded-[2rem] p-12 shadow-2xl mx-auto transform transition-all hover:scale-105 border-4 border-blue-400/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 divide-y md:divide-y-0 md:divide-x divide-gray-200">
                        <div>
                            <p class="text-sm text-gray-400 uppercase font-bold mb-2 tracking-widest">Vendedor</p>
                            <p class="text-5xl font-black text-blue-600 leading-tight" x-text="alertData.seller"></p>
                        </div>
                        <div class="pt-8 md:pt-0 md:pl-12">
                            <p class="text-sm text-gray-400 uppercase font-bold mb-2 tracking-widest">Cliente (Turno: <span x-text="alertData.folio"></span>)</p>
                            <p class="text-5xl font-black text-gray-800 leading-tight" x-text="alertData.client"></p>
                        </div>
                    </div>
                </div>

                <button @click="closeAlert()" :disabled="alertTimer > 0"
                        class="mt-12 px-12 py-5 rounded-2xl font-black text-xl transition-all duration-300 tracking-wider"
                        :class="alertTimer > 0 ? 'bg-white/10 text-white/50 cursor-not-allowed' : 'bg-white text-blue-900 hover:bg-blue-50 shadow-xl shadow-white/10 transform hover:-translate-y-1'">
                    <span x-show="alertTimer > 0">ESPERE (<span x-text="alertTimer"></span>)</span>
                    <span x-show="alertTimer <= 0">ENTERADO</span>
                </button>
            </div>
        </div>

    </div>

    {{-- AUDIO ALARMA --}}
    <audio id="bell-sound" src="{{ asset('audio/bell.mp3') }}" preload="auto"></audio>

    <script>
        function salesDashboard() {
            return {
                waitingCount: @json($clientsWaiting ?? 0),
                
                // Break Modal y Confirmación de Comida
                showBreakModal: false,
                showLunchConfirmModal: false, // <-- Nuevo estado para el modal de comida
                breakShiftId: null,
                hasTakenLunch: false,
                
                // Extension Modal
                showExtensionModal: false,
                extensionShiftId: null,
                extensionSellerName: '',
                extensionClientName: '',
                warnedShifts: [], 
                autoClosedShifts: [],
                
                // Mega Alert
                showMegaAlert: false,
                alertData: { seller: '', client: '', folio: '' },
                alertTimer: 5,
                isLoading: false,

                init() {
                    window.addEventListener('open-break-modal', event => {
                        this.breakShiftId = event.detail.id;
                        this.hasTakenLunch = event.detail.hasTakenLunch;
                        this.showBreakModal = true;
                    });

                    if ("Notification" in window && Notification.permission !== "granted" && Notification.permission !== "denied") {
                        Notification.requestPermission();
                    }

                    document.body.addEventListener('click', () => {
                        if ("Notification" in window && Notification.permission !== "granted") {
                            Notification.requestPermission();
                        }
                    }, { once: true });

                    setInterval(() => { this.fetchUpdates(); }, 3000);
                    setInterval(() => { this.updateTimers(); }, 1000);
                },

                // Lógica principal de intercepción de descansos
                selectBreakReason(reason) {
                    if (reason === 'LUNCH') {
                        if (this.hasTakenLunch) {
                            return; // No hace nada si ya tomó comida
                        }
                        
                        // Ocultamos el modal principal de opciones de break
                        this.showBreakModal = false;
                        
                        // Pequeño retraso para permitir que termine la animación de salida
                        // y se vea fluida la entrada del nuevo modal
                        setTimeout(() => {
                            this.showLunchConfirmModal = true;
                        }, 200);
                        
                        return;
                    }

                    // Si no es comida, ejecutamos la pausa directamente
                    this.executeBreak(reason);
                },

                // Envío real del formulario
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

                    // 1. CRONÓMETROS DE ATENCIÓN A CLIENTES
                    const servingCards = document.querySelectorAll('.seller-card[data-serving="true"]');
                    servingCards.forEach(card => {
                        let startTime = parseInt(card.dataset.startTime);
                        let shiftId = card.dataset.shiftId;
                        
                        let elapsedSecs = Math.floor((now - startTime) / 1000);
                        if (elapsedSecs < 0) elapsedSecs = 0;

                        let mins = Math.floor(elapsedSecs / 60);
                        let secs = elapsedSecs % 60;
                        let timeString = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;

                        let timerEl = card.querySelector('.seller-timer');
                        if (timerEl) {
                            timerEl.innerText = timeString;
                            
                            if (mins >= 15) {
                                timerEl.className = "seller-timer text-xl font-mono font-black text-red-500 tracking-wider animate-pulse";
                            } else if (mins >= 10) {
                                timerEl.className = "seller-timer text-xl font-mono font-bold text-yellow-400 tracking-wider";
                            } else {
                                timerEl.className = "seller-timer text-xl font-mono font-bold text-blue-300 tracking-wider";
                            }
                        }

                        // Alerta de 15 minutos (900s)
                        if (elapsedSecs >= 900 && elapsedSecs < 1200) {
                            if (!this.warnedShifts.includes(shiftId)) {
                                this.warnedShifts.push(shiftId);
                                let sellerName = card.querySelector('h3').innerText;
                                let clientName = card.querySelector('.text-2xl.font-black').innerText;
                                this.triggerExtensionAlert(shiftId, sellerName, clientName);
                            }
                        }

                        // Cierre automático a los 20 minutos (1200s)
                        if (elapsedSecs >= 1200) {
                            if (!this.autoClosedShifts.includes(shiftId)) {
                                this.autoClosedShifts.push(shiftId);
                                this.forceAutoCloseService(shiftId);
                            }
                        }
                    });

                    // 2. CRONÓMETROS DE PAUSAS
                    const breakCards = document.querySelectorAll('.seller-card[data-on-break="true"]');
                    breakCards.forEach(card => {
                        let breakStartTime = parseInt(card.dataset.breakStartTime);
                        if (!breakStartTime) return;

                        let elapsedSecs = Math.floor((now - breakStartTime) / 1000);
                        let timerEl = card.querySelector('.break-timer');

                        if (timerEl) {
                            if (elapsedSecs < 0) {
                                // MODO CUENTA REGRESIVA (Tiempo de Traslado)
                                let absSecs = Math.abs(elapsedSecs);
                                let mins = Math.floor(absSecs / 60);
                                let secs = absSecs % 60;
                                let timeString = `-${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                                
                                timerEl.innerText = timeString;
                                timerEl.className = "break-timer text-2xl font-mono font-black text-green-400 tracking-wider animate-pulse";
                            } else {
                                // MODO CRONÓMETRO NORMAL DE PAUSA
                                let mins = Math.floor(elapsedSecs / 60);
                                let secs = elapsedSecs % 60;
                                let timeString = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                                
                                timerEl.innerText = timeString;
                                
                                // Cambio de color si la pausa se extiende mucho
                                if (mins >= 30) {
                                    timerEl.className = "break-timer text-2xl font-mono font-black text-red-500 tracking-wider animate-pulse";
                                } else if (mins >= 25) {
                                    timerEl.className = "break-timer text-2xl font-mono font-bold text-yellow-500 tracking-wider";
                                } else {
                                    timerEl.className = "break-timer text-2xl font-mono font-bold text-yellow-300 tracking-wider";
                                }
                            }
                        }
                    });
                },

                triggerExtensionAlert(shiftId, sellerName, clientName) {
                    this.extensionShiftId = shiftId;
                    this.extensionSellerName = sellerName;
                    this.extensionClientName = clientName;
                    this.showExtensionModal = true;
                },

                confirmExtension() {
                    let shiftId = this.extensionShiftId;
                    
                    fetch("{{ route('ventas.extend-service') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ shift_id: shiftId })
                    })
                    .then(r => r.json())
                    .then(data => {
                        this.showExtensionModal = false;
                        this.warnedShifts = this.warnedShifts.filter(id => id != shiftId);
                        this.fetchUpdates(); 
                    });
                },

                forceAutoCloseService(shiftId) {
                    fetch("{{ route('ventas.finish-service') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ shift_id: shiftId })
                    })
                    .then(() => {
                        if (this.extensionShiftId == shiftId) {
                            this.showExtensionModal = false; 
                        }
                        this.fetchUpdates(); 
                    });
                },

                fetchUpdates() {
                    if(this.showMegaAlert) return; 

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
                        if(grid) {
                            grid.innerHTML = data.html;
                            this.updateTimers(); 
                        }

                        if (data.alert) this.triggerMegaAlert(data.alert);
                    })
                    .catch(e => console.error(e))
                    .finally(() => {
                        setTimeout(() => this.isLoading = false, 500);
                    });
                },

                triggerMegaAlert(data) {
                    this.alertData = data;
                    this.showMegaAlert = true;
                    this.alertTimer = 5;

                    let audio = document.getElementById('bell-sound');
                    if (audio) {
                        audio.play().catch(error => console.log("Audio bloqueado por el navegador:", error));
                    }

                    if ("Notification" in window && Notification.permission === "granted") {
                        new Notification("¡Nuevo Cliente Asignado!", {
                            body: `Turno/Folio: ${data.folio}\nCliente: ${data.client}\nVendedor asignado: ${data.seller}`,
                            icon: '/images/aromas_logo_recortado.png'
                        });
                    }
                    
                    let timerInterval = setInterval(() => {
                        this.alertTimer--;
                        if (this.alertTimer <= 0) clearInterval(timerInterval);
                    }, 1000);

                    setTimeout(() => { if(this.showMegaAlert) this.closeAlert(); }, 15000);
                },

                closeAlert() {
                    this.showMegaAlert = false;
                }
            }
        }
    </script>
</body>
</html>