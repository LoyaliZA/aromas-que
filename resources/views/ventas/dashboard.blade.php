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

    {{-- APP CONTAINER --}}
    <div class="min-h-screen flex flex-col" x-data="salesDashboard()">
        
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

            <div id="sellers-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 h-full content-start">
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
                
                <form action="{{ route('ventas.toggle-break') }}" method="POST" class="grid grid-cols-2 gap-4">
                    @csrf
                    <input type="hidden" name="shift_id" :value="breakShiftId">
                    
                    @foreach([
                        ['val' => 'BATHROOM', 'icon' => '🚽', 'label' => 'Baño'],
                        ['val' => 'LUNCH', 'icon' => '🍔', 'label' => 'Comida'],
                        ['val' => 'ERRAND', 'icon' => '🏃', 'label' => 'Encargo'],
                        ['val' => 'PACKAGING', 'icon' => '📦', 'label' => 'Paquetería']
                    ] as $opt)
                        <button type="submit" name="reason" value="{{ $opt['val'] }}" 
                                class="p-6 bg-gray-700/50 border border-gray-600 rounded-xl hover:bg-aromas-highlight hover:text-aromas-main hover:border-aromas-highlight text-gray-300 font-bold flex flex-col items-center gap-3 transition-all duration-200 group">
                            <span class="text-4xl group-hover:scale-110 transition-transform">{{ $opt['icon'] }}</span>
                            <span class="text-sm uppercase tracking-wider">{{ $opt['label'] }}</span>
                        </button>
                    @endforeach
                </form>
                
                <button @click="showBreakModal = false" class="mt-6 w-full py-4 text-gray-500 font-bold hover:text-white transition-colors uppercase text-sm tracking-widest">
                    Cancelar
                </button>
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
                            <p class="text-sm text-gray-400 uppercase font-bold mb-2 tracking-widest">Cliente</p>
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

    {{-- SCRIPT ACTUALIZADO CON PREVENCIÓN DE FLICKER --}}
    <script>
        function salesDashboard() {
            return {
                waitingCount: @json($clientsWaiting ?? 0),
                
                // Break Modal
                showBreakModal: false,
                breakShiftId: null,
                
                // Extension Modal
                showExtensionModal: false,
                extensionShiftId: null,
                extensionSellerName: '',
                extensionClientName: '',
                warnedShifts: [], 
                autoClosedShifts: [],
                
                // Mega Alert
                showMegaAlert: false,
                alertData: { seller: '', client: '' },
                alertTimer: 5,
                isLoading: false,

                init() {
                    window.addEventListener('open-break-modal', event => {
                        this.breakShiftId = event.detail.id;
                        this.showBreakModal = true;
                    });

                    // Polling cada 3 segundos
                    setInterval(() => { this.fetchUpdates(); }, 3000);

                    // Refresco de Cronómetros cada segundo
                    setInterval(() => { this.updateTimers(); }, 1000);
                },

                updateTimers() {
                    const cards = document.querySelectorAll('.seller-card[data-serving="true"]');
                    const now = Date.now();

                    cards.forEach(card => {
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
                            
                            // ---- SOLUCIÓN AL FLICKER ----
                            // Forzamos el cálculo de tiempo EXACTAMENTE al momento de inyectar el HTML
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