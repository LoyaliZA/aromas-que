<x-tablet-layout>
    {{-- x-data principal: Controla Búsqueda y Modales --}}
    <div x-data="deliveryApp()" x-init="initSignaturePad()" class="pb-10">
        
        {{-- ========================================================== --}}
        {{--    ZONA SUPERIOR: BOTONES DE ACCIÓN (AMARILLOS)            --}}
        {{-- ========================================================== --}}
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Botón 1: Agregar a Cola --}}
            <button @click="showQueueModal = true; setTimeout(() => $refs.queueInput.focus(), 100)" 
                    class="bg-aromas-highlight text-aromas-main rounded-xl p-4 shadow-lg flex items-center justify-between group transform transition-all hover:scale-[1.01] hover:shadow-[0_0_15px_rgba(253,201,116,0.4)] border-2 border-transparent hover:border-white/20">
                <div class="flex items-center gap-4">
                    <div class="bg-aromas-main/10 p-3 rounded-lg text-aromas-main">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </div>
                    <div class="text-left">
                        <h2 class="text-xl font-bold leading-none">Ticket de Turno</h2>
                        <p class="text-aromas-main/70 text-sm font-medium mt-1">Ingresar cliente a Ventas</p>
                    </div>
                </div>
                {{-- Indicador de Fila Actual --}}
                <div class="bg-aromas-main text-aromas-highlight px-4 py-2 rounded-lg text-center shadow-inner">
                    <span class="block text-[10px] uppercase font-bold tracking-wider opacity-70">En Fila</span>
                    <span class="text-2xl font-bold leading-none">{{ $peopleInQueue }}</span>
                </div>
            </button>

            {{-- Botón 2: Escáner QR --}}
            <button disabled class="bg-aromas-highlight/50 text-aromas-main/50 border-2 border-dashed border-aromas-main/10 rounded-xl p-4 flex items-center justify-center gap-3 cursor-not-allowed">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span class="font-bold">Escanear QR (Próximamente)</span>
            </button>
        </div>

        {{-- ========================================================== --}}
        {{--        ZONA MEDIA: FILTROS Y LIVE SEARCH                   --}}
        {{-- ========================================================== --}}
        <div class="bg-aromas-secondary rounded-xl p-4 shadow-md border border-aromas-tertiary/20 mb-6 sticky top-2 z-30">
            <div class="flex flex-col md:flex-row gap-4">
                
                {{-- Live Search (Sin Refresh) --}}
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        {{-- Icono Lupa --}}
                        <svg class="h-5 w-5 text-aromas-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" 
                           x-model.debounce.500ms="search" 
                           class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg py-3 pl-10 pr-4 text-white placeholder-gray-500 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all"
                           placeholder="Buscar folio, cliente o receptor (Escribe para buscar...)">
                    
                    {{-- Spinner de carga --}}
                    <div x-show="isLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center" style="display: none;">
                        <svg class="animate-spin h-5 w-5 text-aromas-highlight" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>

                {{-- Filtros (Estos sí recargan por simplicidad, o podríamos hacerlos live también) --}}
                <form action="{{ route('recepcion.dashboard') }}" method="GET" class="contents">
                    <select name="department" onchange="this.form.submit()" class="bg-black/20 border border-aromas-tertiary/30 text-white rounded-lg px-4 py-3 focus:border-aromas-highlight cursor-pointer">
                        <option value="ALL">Depto: Todos</option>
                        <option value="AROMAS" {{ request('department') == 'AROMAS' ? 'selected' : '' }}>🟣 Aromas</option>
                        <option value="BELLAROMA" {{ request('department') == 'BELLAROMA' ? 'selected' : '' }}>🌸 Bellaroma</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- MENSAJES --}}
        @if(session('success'))
            <div class="mb-6 bg-green-500/10 border-l-4 border-green-500 text-green-400 p-4 rounded shadow-lg flex items-center animate-fade-in-down">
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- ========================================================== --}}
        {{--                CONTENEDOR DE CARDS (AJAX)                  --}}
        {{-- ========================================================== --}}
        {{-- Aquí se inyecta el HTML parcial cuando buscas --}}
        <div id="results-container">
            @include('recepcion.partials.card-grid', ['pickups' => $pickups])
        </div>


        {{-- ========================================================== --}}
        {{--           MODAL DE ENTREGA (VERTICAL Y FIXED)              --}}
        {{-- ========================================================== --}}
        <div x-show="showDeliveryModal" style="display: none;" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="closeModal()"></div>

            {{-- Modal Panel: Ancho fijo para ser vertical --}}
            <div class="bg-aromas-secondary w-full max-w-md rounded-xl shadow-2xl border border-aromas-tertiary/30 relative z-10 flex flex-col my-auto">
                
                {{-- Header --}}
                <div class="bg-black/20 p-4 border-b border-aromas-tertiary/20 flex justify-between items-center rounded-t-xl">
                    <div>
                        <h2 class="text-lg font-bold text-white">Confirmar Entrega</h2>
                        <p class="text-xs text-aromas-tertiary">Folio: <span class="text-aromas-highlight font-mono" x-text="pickup.ticket_folio"></span></p>
                    </div>
                    <button @click="closeModal()" class="text-gray-500 hover:text-white p-2 bg-white/5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>

                <form id="deliveryForm" method="POST" :action="`/recepcion/confirm/${pickup.id}`" class="p-5">
                    @csrf @method('PUT')
                    <input type="hidden" name="signature" x-model="signatureData">

                    {{-- Datos Receptor --}}
                    <div class="mb-5 space-y-3">
                        <label class="flex items-center space-x-3 cursor-pointer p-3 bg-black/20 rounded-lg border border-aromas-tertiary/10 hover:bg-white/5 transition">
                            <input type="checkbox" name="is_third_party" x-model="isThirdParty" class="w-5 h-5 rounded border-aromas-tertiary text-aromas-highlight focus:ring-aromas-highlight bg-transparent">
                            <div>
                                <span class="block text-sm font-bold text-white">¿Recoge otra persona?</span>
                            </div>
                        </label>

                        <div x-show="isThirdParty" x-transition>
                            <label class="block text-xs text-aromas-tertiary mb-1 uppercase tracking-wider font-bold">Nombre quien recibe</label>
                            <input type="text" name="receiver_name" x-model="receiverName" 
                                   class="w-full bg-aromas-main border border-aromas-tertiary/30 rounded-lg px-3 py-3 text-white focus:border-aromas-highlight focus:ring-1 transition-colors" 
                                   placeholder="Nombre completo...">
                        </div>
                        
                        <div x-show="!isThirdParty" class="p-3 bg-blue-900/10 border border-blue-500/10 rounded-lg text-center">
                            <p class="text-xs text-blue-300 mb-1">Entregando a Titular:</p>
                            <p class="text-sm font-bold text-white" x-text="pickup.client_name"></p>
                        </div>
                    </div>

                    {{-- Canvas Firma --}}
                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-2">
                            <label class="text-xs text-gray-400 uppercase tracking-wider font-bold">Firma Digital</label>
                            <button type="button" @click="clearSignature()" class="text-xs text-red-400 hover:text-red-300 underline">Borrar</button>
                        </div>
                        {{-- Altura fija para forzar aspecto vertical --}}
                        <div class="h-40 w-full bg-white rounded-lg overflow-hidden relative border-2" :class="hasSignature ? 'border-aromas-success' : 'border-gray-400'">
                            <canvas x-ref="canvas" class="absolute inset-0 w-full h-full touch-none cursor-crosshair"
                                    @mousedown="startDrawing" @mousemove="draw" @mouseup="stopDrawing" @mouseleave="stopDrawing"
                                    @touchstart.prevent="startDrawing" @touchmove.prevent="draw" @touchend.prevent="stopDrawing"></canvas>
                            <div x-show="!hasSignature && !isDrawing" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20">
                                <span class="text-xl font-handwriting text-gray-500">Firmar Aquí</span>
                            </div>
                        </div>
                    </div>

                    {{-- Botones --}}
                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="closeModal()" class="py-3 rounded-lg border border-aromas-tertiary/30 text-gray-400 font-bold hover:bg-white/5">CANCELAR</button>
                        <button type="button" @click="submitDelivery()" :disabled="!hasSignature" class="py-3 rounded-lg bg-aromas-highlight text-aromas-main font-bold shadow-lg hover:bg-white disabled:opacity-50 disabled:cursor-not-allowed">CONFIRMAR</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal de Cola (Mismo estilo) --}}
        <div x-show="showQueueModal" style="display: none;" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition>
             <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showQueueModal = false"></div>
             <div class="bg-aromas-secondary w-full max-w-md rounded-xl shadow-2xl border border-aromas-highlight/30 flex flex-col relative z-10">
                <div class="bg-aromas-highlight/10 p-4 border-b border-aromas-tertiary/20 rounded-t-xl">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Ingreso a Ventas
                    </h2>
                </div>
                <form action="{{ route('recepcion.queue.add') }}" method="POST" class="p-6">
                    @csrf
                    <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-2">Nombre del Cliente</label>
                    <input type="text" name="client_name" x-ref="queueInput" required
                           class="w-full bg-aromas-main border-2 border-aromas-tertiary/30 rounded-lg px-4 py-3 text-lg text-white placeholder-gray-600 focus:border-aromas-highlight focus:ring-0 transition-colors mb-6"
                           placeholder="Ej. María Pérez">
                    <div class="flex gap-3">
                        <button type="button" @click="showQueueModal = false" class="flex-1 py-3 rounded-lg border border-aromas-tertiary/30 text-gray-400 font-bold hover:bg-white/5">CANCELAR</button>
                        <button type="submit" class="flex-1 py-3 rounded-lg bg-aromas-highlight text-aromas-main font-bold shadow-lg hover:bg-white">REGISTRAR</button>
                    </div>
                </form>
             </div>
        </div>

    </div>

    {{-- SCRIPT ALPINE JS: Lógica de Búsqueda y Firma --}}
    <script>
        function deliveryApp() {
            return {
                // Search State
                search: '',
                isLoading: false,

                // Modal State
                showDeliveryModal: false,
                showQueueModal: false,
                pickup: {},
                isThirdParty: false,
                receiverName: '',
                
                // Signature State
                signatureData: '', isDrawing: false, hasSignature: false, ctx: null, canvas: null, lastX: 0, lastY: 0,

                init() {
                    // WATCHER DE BÚSQUEDA: Se activa al escribir con debounce
                    this.$watch('search', (value) => {
                        this.isLoading = true;
                        fetch(`{{ route('recepcion.dashboard') }}?search=${value}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(response => response.text())
                        .then(html => {
                            document.getElementById('results-container').innerHTML = html;
                            this.isLoading = false;
                        });
                    });
                },

                openDeliveryModal(data) {
                    this.pickup = data;
                    this.clearSignature();
                    
                    if (data.is_third_party) {
                        this.isThirdParty = true;
                        this.receiverName = data.receiver_name;
                    } else {
                        this.isThirdParty = false;
                        this.receiverName = '';
                    }

                    this.showDeliveryModal = true;
                    // IMPORTANTE: Esperar a que el modal sea visible para dimensionar el Canvas
                    setTimeout(() => this.resizeCanvas(), 100);
                },

                closeModal() { this.showDeliveryModal = false; },

                // --- CANVAS LOGIC ---
                initSignaturePad() {
                    this.canvas = this.$refs.canvas;
                    if(this.canvas) {
                        this.ctx = this.canvas.getContext('2d');
                        this.setupCanvas();
                        window.addEventListener('resize', () => this.resizeCanvas());
                    }
                },
                setupCanvas() {
                    this.ctx.strokeStyle = '#000000'; this.ctx.lineWidth = 3; this.ctx.lineCap = 'round'; this.ctx.lineJoin = 'round';
                },
                resizeCanvas() {
                    if(!this.canvas) return;
                    const rect = this.canvas.parentElement.getBoundingClientRect();
                    // Solo redimensionar si el modal es visible y tiene tamaño
                    if (rect.width > 0 && rect.height > 0) {
                        this.canvas.width = rect.width;
                        this.canvas.height = rect.height;
                        this.setupCanvas();
                    }
                },
                getCoords(e) {
                    const rect = this.canvas.getBoundingClientRect();
                    let cx = e.clientX, cy = e.clientY;
                    if(e.touches && e.touches.length > 0) { cx = e.touches[0].clientX; cy = e.touches[0].clientY; }
                    return { x: cx - rect.left, y: cy - rect.top };
                },
                startDrawing(e) { this.isDrawing = true; const c = this.getCoords(e); this.lastX = c.x; this.lastY = c.y; },
                draw(e) {
                    if(!this.isDrawing) return;
                    const c = this.getCoords(e);
                    this.ctx.beginPath(); this.ctx.moveTo(this.lastX, this.lastY); this.ctx.lineTo(c.x, c.y); this.ctx.stroke();
                    this.lastX = c.x; this.lastY = c.y; this.hasSignature = true;
                },
                stopDrawing() { if(this.isDrawing) { this.isDrawing = false; this.signatureData = this.canvas.toDataURL('image/png'); } },
                clearSignature() { if(this.ctx) { this.ctx.clearRect(0,0,this.canvas.width, this.canvas.height); this.hasSignature=false; this.signatureData=''; } },
                submitDelivery() { if(!this.hasSignature) return alert('La firma es obligatoria'); document.getElementById('deliveryForm').submit(); }
            }
        }
    </script>
</x-tablet-layout>