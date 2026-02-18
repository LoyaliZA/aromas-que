<x-app-layout>
    {{-- 
        APP DE VENDEDOR (SALES DASHBOARD) - DISEÑO AROMAS DARK
    --}}
    <div x-data="salesApp({{ json_encode($shift) }}, {{ json_encode($currentClient) }})" 
         x-init="init()" 
         class="min-h-screen transition-colors duration-500 bg-gray-900 text-white font-sans"
         :class="{
             'bg-gray-900': status === 'OFFLINE', 
             'bg-gray-900': status === 'ONLINE',
             'bg-gray-800': status === 'BREAK'
         }">

        {{-- HEADER SUPERIOR --}}
        <div class="bg-aromas-secondary border-b border-aromas-tertiary/20 px-6 py-4 flex justify-between items-center sticky top-0 z-50 shadow-md">
            <div class="flex items-center gap-4">
                {{-- Indicador de Estado (Semáforo) --}}
                <div class="p-2 rounded-lg border border-white/5 shadow-inner transition-colors duration-300" 
                     :class="{
                         'bg-red-500/20 text-red-400 border-red-500/30': status === 'OFFLINE',
                         'bg-green-500/20 text-green-400 border-green-500/30': status === 'ONLINE',
                         'bg-yellow-500/20 text-yellow-400 border-yellow-500/30': status === 'BREAK'
                     }">
                     <svg x-show="status === 'OFFLINE'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                     <svg x-show="status === 'ONLINE'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
                     <svg x-show="status === 'BREAK'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-white leading-tight">{{ Auth::user()->name }}</h1>
                    <p class="text-[10px] font-bold tracking-widest uppercase opacity-70" x-text="statusLabel"></p>
                </div>
            </div>

            {{-- Reloj --}}
            <div class="text-right hidden md:block">
                <p class="text-[10px] text-aromas-highlight uppercase tracking-wider opacity-60">Hora Local</p>
                <p class="text-xl font-mono font-bold text-white" x-text="currentTime"></p>
            </div>
        </div>

        {{-- CONTENIDO PRINCIPAL --}}
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex items-center justify-center min-h-[80vh]">
            
            {{-- 1. PANTALLA OFFLINE --}}
            <div x-show="status === 'OFFLINE'" class="text-center space-y-8 animate-fade-in-up" style="display: none;">
                <div class="relative inline-block">
                    <div class="absolute inset-0 bg-aromas-highlight blur-2xl opacity-10 rounded-full"></div>
                    <div class="relative p-6 rounded-full bg-black/40 border border-white/10 shadow-2xl">
                        <svg class="w-20 h-20 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-white mb-2 tracking-tight">SISTEMA DE VENTAS</h2>
                    <p class="text-gray-400 text-lg font-light">Tu turno está cerrado. Inicia para recibir clientes.</p>
                </div>
                <button @click="updateStatus('ONLINE')" class="group relative px-8 py-4 bg-aromas-highlight text-aromas-main font-black text-lg rounded-xl shadow-lg hover:shadow-yellow-500/20 transition-all transform hover:scale-105 active:scale-95 overflow-hidden">
                    <div class="absolute inset-0 bg-white/20 group-hover:translate-x-full transition-transform duration-500 transform -skew-x-12 -translate-x-full"></div>
                    INICIAR MI TURNO
                </button>
            </div>

            {{-- 2. PANTALLA ONLINE (BUSCANDO) --}}
            <div x-show="status === 'ONLINE' && !client" class="text-center w-full max-w-2xl animate-fade-in" style="display: none;">
                <div class="bg-aromas-secondary/50 backdrop-blur-sm border border-aromas-tertiary/20 p-10 rounded-3xl shadow-2xl relative overflow-hidden">
                    
                    {{-- Radar Animation --}}
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -mt-20 w-96 h-96 bg-aromas-highlight/5 rounded-full blur-3xl animate-pulse"></div>

                    <div class="relative z-10">
                        <div class="mb-8">
                            <span class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-aromas-highlight/10 text-aromas-highlight ring-4 ring-aromas-highlight/20 animate-bounce">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                        </div>
                        <h2 class="text-2xl font-bold text-white mb-2">Buscando Cliente...</h2>
                        <p class="text-gray-400">El sistema te asignará el siguiente turno automáticamente.</p>
                        
                        {{-- Opciones de Break --}}
                        <div class="mt-10 pt-8 border-t border-white/5">
                            <p class="text-xs text-aromas-tertiary uppercase tracking-widest font-bold mb-4">Pausar Turno</p>
                            <div class="grid grid-cols-2 gap-3">
                                <button @click="takeBreak('BATHROOM')" class="flex items-center justify-center gap-2 p-3 rounded-lg border border-white/10 hover:bg-white/5 text-gray-300 hover:text-white transition group">
                                    <span class="group-hover:scale-110 transition-transform">🚽</span> Baño
                                </button>
                                <button @click="takeBreak('LUNCH')" class="flex items-center justify-center gap-2 p-3 rounded-lg border border-white/10 hover:bg-white/5 text-gray-300 hover:text-white transition group">
                                    <span class="group-hover:scale-110 transition-transform">🍔</span> Comida
                                </button>
                                <button @click="takeBreak('ERRAND')" class="flex items-center justify-center gap-2 p-3 rounded-lg border border-white/10 hover:bg-white/5 text-gray-300 hover:text-white transition group">
                                    <span class="group-hover:scale-110 transition-transform">🏃</span> Encargo
                                </button>
                                <button @click="updateStatus('OFFLINE')" class="col-span-2 p-3 rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 font-bold uppercase text-xs tracking-wider transition">
                                    Cerrar Sesión
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. PANTALLA ATENDIENDO (CLIENTE ASIGNADO) --}}
            <div x-show="client" class="w-full max-w-4xl animate-fade-in-up" style="display: none;">
                <div class="bg-aromas-secondary rounded-3xl shadow-2xl overflow-hidden border border-aromas-tertiary/30">
                    {{-- Header Cliente --}}
                    <div class="bg-gradient-to-r from-aromas-highlight to-yellow-500 px-8 py-6 flex justify-between items-center text-aromas-main">
                        <div>
                            <p class="text-aromas-main/70 text-xs uppercase tracking-widest font-bold mb-1">Cliente Asignado</p>
                            <h2 class="text-3xl font-black tracking-tight" x-text="client?.client_name"></h2>
                        </div>
                        <div class="bg-black/20 px-4 py-2 rounded-lg backdrop-blur-md border border-black/10">
                            <span class="text-aromas-main font-mono text-2xl font-bold" x-text="timer">00:00</span>
                        </div>
                    </div>

                    <div class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            {{-- Info --}}
                            <div class="col-span-2 space-y-6">
                                <div class="bg-black/20 p-6 rounded-xl border border-white/5">
                                    <h3 class="text-lg font-bold text-aromas-highlight mb-4">Detalles del Turno</h3>
                                    <div class="space-y-4 text-sm">
                                        <div class="flex justify-between border-b border-white/5 pb-2">
                                            <span class="text-gray-400">Origen:</span>
                                            <span class="font-bold text-white uppercase" x-text="client?.source"></span>
                                        </div>
                                        <div class="flex justify-between border-b border-white/5 pb-2">
                                            <span class="text-gray-400">Hora de Llegada:</span>
                                            <span class="font-bold text-white font-mono" x-text="formatTime(client?.queued_at)"></span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-400">Tiempo en Espera:</span>
                                            <span class="font-bold text-green-400 font-mono" x-text="getWaitTime()"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Acciones --}}
                            <div class="flex flex-col gap-4 justify-center">
                                <button @click="finishService()" class="w-full py-5 bg-green-600 hover:bg-green-500 text-white rounded-xl font-bold text-xl shadow-lg transition transform active:scale-95 flex flex-col items-center border border-green-400/30">
                                    <span>✅ VENTA REALIZADA</span>
                                    <span class="text-xs font-normal opacity-80 mt-1">Finalizar y volver a cola</span>
                                </button>
                                
                                <button @click="finishService()" class="w-full py-3 bg-transparent border-2 border-gray-600 text-gray-400 rounded-xl font-bold hover:border-gray-400 hover:text-white transition uppercase text-xs tracking-wider">
                                    Cliente No Compró
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. PANTALLA BREAK --}}
            <div x-show="status === 'BREAK'" class="text-center w-full max-w-lg animate-fade-in" style="display: none;">
                <div class="bg-aromas-secondary p-10 rounded-3xl shadow-xl border-t-4 border-yellow-500 relative overflow-hidden">
                    <div class="absolute inset-0 bg-yellow-500/5 repeating-linear-gradient-45"></div>
                    
                    <div class="relative z-10">
                        <div class="mb-6 animate-pulse">
                            <span class="text-6xl filter drop-shadow-lg">⏸️</span>
                        </div>
                        <h2 class="text-3xl font-black text-white mb-2">EN PAUSA</h2>
                        <div class="inline-block px-4 py-1 rounded-full bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 text-sm font-bold uppercase tracking-wide mb-8">
                            <span x-text="breakReasonLabel"></span>
                        </div>
                        
                        <button @click="updateStatus('ONLINE')" class="w-full py-4 bg-white text-gray-900 font-black rounded-xl text-lg shadow-lg hover:bg-gray-100 transition transform hover:-translate-y-1">
                            REGRESAR AL TRABAJO
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    {{-- LÓGICA JAVASCRIPT (SE MANTIENE IGUAL, SOLO CAMBIA EL DISEÑO) --}}
    <script>
        function salesApp(initialShift, initialClient) {
            return {
                status: initialShift.current_status,
                breakReason: initialShift.break_reason,
                client: initialClient,
                currentTime: '',
                timer: '00:00',
                timerInterval: null,

                init() {
                    setInterval(() => {
                        this.currentTime = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    }, 1000);

                    if (this.client) {
                        this.startServiceTimer();
                    }

                    // Polling cada 3 segundos
                    setInterval(() => {
                        this.checkQueue();
                    }, 3000);
                },

                get statusLabel() {
                    if (this.status === 'OFFLINE') return 'Desconectado';
                    if (this.status === 'BREAK') return 'En Pausa';
                    return this.client ? 'Atendiendo' : 'Disponible';
                },

                get breakReasonLabel() {
                    const map = { 'BATHROOM': 'Baño', 'LUNCH': 'Comida', 'ERRAND': 'Mandado', 'PACKAGING': 'Paquetería' };
                    return map[this.breakReason] || 'Descanso';
                },

                async updateStatus(newStatus, reason = null) {
                    try {
                        const res = await axios.post('/ventas/update-status', { status: newStatus, break_reason: reason });
                        if (res.data.success) {
                            this.status = res.data.new_status;
                            this.breakReason = reason;
                        }
                    } catch (error) { alert('Error de conexión'); }
                },

                takeBreak(reason) { this.updateStatus('BREAK', reason); },

                async checkQueue() {
                    try {
                        const res = await axios.get('/ventas/check-queue');
                        if (res.data.status === 'assigned' || res.data.status === 'serving') {
                            if (!this.client || this.client.id !== res.data.client.id) {
                                this.client = res.data.client;
                                this.status = 'ONLINE';
                                this.startServiceTimer();
                            }
                        }
                    } catch (error) { console.error('Error polling:', error); }
                },

                async finishService() {
                    if (!confirm('¿Finalizar atención?')) return;
                    try {
                        await axios.post('/ventas/finish-service');
                        this.client = null;
                        this.stopServiceTimer();
                    } catch (error) { alert('Error al finalizar.'); }
                },

                startServiceTimer() {
                    this.stopServiceTimer();
                    const start = new Date(this.client.started_serving_at).getTime();
                    this.timerInterval = setInterval(() => {
                        const now = new Date().getTime();
                        const diff = now - start;
                        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                        this.timer = (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
                    }, 1000);
                },

                stopServiceTimer() {
                    if (this.timerInterval) clearInterval(this.timerInterval);
                    this.timer = '00:00';
                },

                formatTime(dateString) {
                    if(!dateString) return '--:--';
                    return new Date(dateString).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                },

                getWaitTime() {
                    if(!this.client) return '0 min';
                    const queued = new Date(this.client.queued_at);
                    const start = new Date(this.client.started_serving_at);
                    const diffMs = start - queued;
                    const diffMins = Math.floor(diffMs / 60000);
                    return diffMins + ' min';
                }
            }
        }
    </script>
</x-app-layout>