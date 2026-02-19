<x-tablet-layout>
    {{-- x-data: Inicializamos la app. Importante: $peopleInQueue debe llegar desde el controlador --}}
    <div x-data="deliveryApp({{ $peopleInQueue }})" x-init="init()" class="pb-10 relative">

        {{-- ========================================================== --}}
        {{--    ZONA SUPERIOR: BOTONES (DISEÑO RESTAURADO)              --}}
        {{-- ========================================================== --}}
        <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <button @click="openQueueModal()" 
                    class="bg-aromas-highlight text-aromas-main rounded-xl p-4 shadow-lg flex items-center justify-between group transform transition-all hover:scale-[1.01] hover:shadow-[0_0_15px_rgba(253,201,116,0.4)] border-2 border-transparent hover:border-white/20">
                <div class="flex items-center gap-4">
                    <div class="bg-aromas-main/10 p-3 rounded-lg text-aromas-main">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>
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

            <button disabled class="bg-aromas-highlight/50 text-aromas-main/50 border-2 border-dashed border-aromas-main/10 rounded-xl p-4 flex items-center justify-center gap-3 cursor-not-allowed">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                <span class="font-bold">Escanear QR (Próximamente)</span>
            </button>
        </div>

        {{-- ========================================================== --}}
        {{--    ZONA MEDIA: FILTROS Y BÚSQUEDA (DISEÑO ORIGINAL)        --}}
        {{-- ========================================================== --}}
        <div class="bg-aromas-secondary rounded-xl p-4 shadow-md border border-aromas-tertiary/20 mb-6 sticky top-2 z-30">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-aromas-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model.debounce.500ms="search" 
                           class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg py-3 pl-10 pr-4 text-white placeholder-gray-500 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all"
                           placeholder="Buscar folio, cliente o receptor...">
                    
                    <div x-show="isLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center" style="display: none;">
                        <svg class="animate-spin h-5 w-5 text-aromas-highlight" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>

                <div class="contents">
                    <select id="deptFilter" @change="fetchData(search)" class="bg-black/20 border border-aromas-tertiary/30 text-white rounded-lg px-4 py-3 focus:border-aromas-highlight cursor-pointer">
                        <option value="ALL">Todos</option>
                        <option value="AROMAS" {{ request('department') == 'AROMAS' ? 'selected' : '' }}>Aromas</option>
                        <option value="BELLAROMA" {{ request('department') == 'BELLAROMA' ? 'selected' : '' }}>Bellaroma</option>
                    </select>
                    <input type="hidden" id="statusFilter" value="{{ request('status', 'IN_CUSTODY') }}">
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{--    MENSAJES FLASH Y ALERTA DE TURNO (NUEVO)                --}}
        {{-- ========================================================== --}}
        
        @if(session('new_turn'))
            <div x-data="{ showTurn: true }" x-show="showTurn" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-transition>
                
                <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showTurn = false"></div>
                
                <div class="bg-aromas-secondary border-2 border-aromas-highlight rounded-2xl shadow-[0_0_30px_rgba(253,201,116,0.3)] p-8 max-w-sm w-full text-center relative z-10 animate-fade-in-down">
                    
                    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-500/20 mb-4">
                        <svg class="h-10 w-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    
                    <h3 class="text-xl font-bold text-white mb-2">Turno Asignado</h3>
                    <p class="text-aromas-tertiary text-sm mb-6">Indíquele al cliente su número:</p>
                    
                    <div class="bg-gray-900 rounded-xl py-6 border border-gray-700 mb-6 shadow-inner">
                        <div class="text-5xl font-black text-aromas-highlight tracking-widest mb-2">{{ session('new_turn') }}</div>
                        <div class="text-white font-bold text-lg">{{ session('client_name') }}</div>
                        <div class="text-gray-400 text-sm uppercase tracking-wider mt-1">Destino: <span class="{{ session('destination') == 'Caja' ? 'text-green-400' : 'text-yellow-400' }}">{{ session('destination') }}</span></div>
                    </div>

                    <button @click="showTurn = false" class="w-full bg-aromas-highlight text-aromas-main font-bold text-lg py-3 rounded-xl hover:bg-white transition-all shadow-lg">
                        Cerrar y Continuar
                    </button>
                </div>
            </div>
        @elseif(session('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" 
                 class="mb-6 bg-green-500/10 border-l-4 border-green-500 text-green-400 p-4 rounded shadow-lg flex items-center animate-fade-in-down">
                <span class="font-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- ========================================================== --}}
        {{--    CONTENEDOR CARDS (AJAX)                                 --}}
        {{-- ========================================================== --}}
        <div id="results-container">
            @include('recepcion.partials.card-grid', ['pickups' => $pickups])
        </div>

        <div class="mt-6">
            {{ $pickups->links() }}
        </div>


        {{-- ========================================================== --}}
        {{--    MODAL 1: CONFIRMAR ENTREGA                              --}}
        {{-- ========================================================== --}}
        <div x-show="showDeliveryModal" style="display: none;" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
             x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="closeModal()"></div>

            <div class="bg-aromas-secondary w-full max-w-2xl rounded-xl shadow-2xl border border-aromas-tertiary/30 relative z-10 flex flex-col my-auto max-h-[90vh] overflow-y-auto">
                
                <div class="bg-black/20 p-4 border-b border-aromas-tertiary/20 flex justify-between items-center sticky top-0 backdrop-blur-md z-20">
                    <div>
                        <h2 class="text-lg font-bold text-white">Confirmar Entrega</h2>
                        <p class="text-xs text-aromas-tertiary">Folio: <span class="text-aromas-highlight font-mono" x-text="pickup.ticket_folio"></span></p>
                    </div>
                    <button @click="closeModal()" class="text-gray-500 hover:text-white p-2 bg-white/5 rounded-lg"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>

                <form id="deliveryForm" method="POST" enctype="multipart/form-data" :action="'/recepcion/confirm/' + pickup.id" class="p-5" @submit.prevent="submitDelivery">
                    @csrf @method('PUT')
                    <input type="hidden" name="signature" x-model="signatureData">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                        <div class="bg-black/20 p-3 rounded-lg border border-aromas-tertiary/10">
                            <label class="block text-xs font-bold text-aromas-highlight uppercase mb-2">Evidencia Fotográfica</label>
                            <input type="file" name="evidence_file" accept="image/*" capture="environment"
                                   class="block w-full text-xs text-gray-400 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-aromas-highlight file:text-aromas-main hover:file:bg-yellow-400 cursor-pointer">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-aromas-tertiary uppercase mb-1">Observaciones</label>
                            <textarea name="notes" rows="2" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg text-sm text-white focus:border-aromas-highlight focus:ring-1" placeholder="Ej: Caja dañada..."></textarea>
                        </div>
                    </div>

                    <div class="mb-5 space-y-3">
                        <label class="flex items-center space-x-3 cursor-pointer p-3 bg-black/20 rounded-lg border border-aromas-tertiary/10 hover:bg-white/5 transition">
                            <input type="checkbox" name="is_third_party" x-model="isThirdParty" class="w-5 h-5 rounded border-aromas-tertiary text-aromas-highlight focus:ring-aromas-highlight bg-transparent">
                            <div><span class="block text-sm font-bold text-white">¿Recoge otra persona?</span></div>
                        </label>

                        <div x-show="isThirdParty" x-transition>
                            <label class="block text-xs text-aromas-tertiary mb-1 uppercase tracking-wider font-bold">Nombre quien recibe</label>
                            <input type="text" name="receiver_name" x-model="receiverName" class="w-full bg-aromas-main border border-aromas-tertiary/30 rounded-lg px-3 py-3 text-white focus:border-aromas-highlight focus:ring-1 transition-colors" placeholder="Nombre completo...">
                        </div>
                        
                        <div x-show="!isThirdParty" class="p-3 bg-blue-900/10 border border-blue-500/10 rounded-lg text-center">
                            <p class="text-xs text-blue-300 mb-1">Entregando a Titular:</p>
                            <p class="text-sm font-bold text-white" x-text="pickup.client_name"></p>
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-2">
                            <label class="text-xs text-gray-400 uppercase tracking-wider font-bold">Firma Digital *</label>
                            <button type="button" @click="clearPad()" class="text-xs text-red-400 hover:text-red-300 underline">Borrar Firma</button>
                        </div>
                        <div class="h-48 w-full bg-white rounded-lg overflow-hidden relative border-2 border-gray-400">
                            <canvas x-ref="signature_canvas" class="absolute inset-0 w-full h-full cursor-crosshair touch-none"></canvas>
                            <div x-show="isPadEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20">
                                <span class="text-2xl font-handwriting text-gray-500">Firmar Aquí</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button type="button" @click="closeModal()" class="py-3 rounded-lg border border-aromas-tertiary/30 text-gray-400 font-bold hover:bg-white/5">CANCELAR</button>
                        <button type="submit" class="py-3 rounded-lg bg-aromas-highlight text-aromas-main font-bold shadow-lg hover:bg-white transition-all">CONFIRMAR</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{--    MODAL 2: TICKET DE TURNO                                --}}
        {{-- ========================================================== --}}
        <div x-show="showQueueModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showQueueModal = false"></div>
            
            <div class="bg-aromas-secondary w-full max-w-md rounded-xl shadow-2xl border border-aromas-highlight/30 flex flex-col relative z-10">
                <div class="bg-aromas-highlight/10 p-4 border-b border-aromas-tertiary/20 rounded-t-xl">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Nuevo Ticket
                    </h2>
                </div>

                <form action="{{ route('recepcion.queue.add') }}" method="POST" class="p-6 space-y-6">
                    @csrf
                    
                    <div>
                        <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-2">Nombre del Cliente</label>
                        <input type="text" name="client_name" x-ref="queueInput" required 
                               class="w-full bg-aromas-main border-2 border-aromas-tertiary/30 rounded-lg px-4 py-3 text-lg text-white placeholder-gray-600 focus:border-aromas-highlight focus:ring-0 transition-colors" 
                               placeholder="Ej. María Pérez">
                    </div>

                    <div>
                        <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-3">Destino</label>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="hidden" name="service_type" x-model="queueType">

                            <button type="button" @click="queueType = 'SALES'"
                                    :class="queueType === 'SALES' ? 'bg-aromas-highlight text-aromas-main ring-2 ring-aromas-highlight ring-offset-2 ring-offset-gray-900' : 'bg-black/20 text-gray-400 hover:bg-white/5'"
                                    class="p-3 rounded-xl border border-transparent flex flex-col items-center justify-center transition-all h-24">
                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                <span class="font-bold text-sm">VENTAS</span>
                            </button>

                            <button type="button" @click="queueType = 'CASHIER'"
                                    :class="queueType === 'CASHIER' ? 'bg-green-500 text-white ring-2 ring-green-500 ring-offset-2 ring-offset-gray-900' : 'bg-black/20 text-gray-400 hover:bg-white/5'"
                                    class="p-3 rounded-xl border border-transparent flex flex-col items-center justify-center transition-all h-24">
                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="font-bold text-sm">SOLO CAJA</span>
                            </button>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" @click="showQueueModal = false" class="flex-1 py-3 rounded-lg border border-aromas-tertiary/30 text-gray-400 font-bold hover:bg-white/5">CANCELAR</button>
                        <button type="submit" class="flex-1 py-3 rounded-lg bg-aromas-highlight text-aromas-main font-bold shadow-lg hover:bg-white transition-all transform hover:-translate-y-1">REGISTRAR</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        function deliveryApp(initialQueueCount) {
            return {
                search: '', 
                isLoading: false,
                queueCount: initialQueueCount, 

                showDeliveryModal: false, 
                showQueueModal: false,

                pickup: {}, 
                isThirdParty: false, 
                receiverName: '',
                signaturePad: null, 
                signatureData: '', 
                isPadEmpty: true,

                queueType: 'SALES',

                init() {
                    this.$watch('search', (value) => { this.fetchData(value); });

                    setInterval(() => {
                        // Respetamos la recarga solo si no hay modales abiertos
                        if (this.showDeliveryModal || this.showQueueModal || this.search.length > 0) return;
                        this.fetchData('');
                    }, 5000);

                    window.addEventListener('open-delivery-modal', event => {
                        this.openDeliveryModal(event.detail);
                    });
                },

                fetchData(searchValue) {
                    this.isLoading = true;
                    let dept = document.getElementById('deptFilter') ? document.getElementById('deptFilter').value : 'ALL';
                    let status = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : 'IN_CUSTODY';
                    
                    let url = `{{ route('recepcion.dashboard') }}?search=${searchValue}&department=${dept}&status=${status}`;

                    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(r => r.json())
                        .then(data => {
                            document.getElementById('results-container').innerHTML = data.html;
                            this.queueCount = data.queueCount;
                            this.isLoading = false;
                        })
                        .catch(err => console.error('Error polling:', err));
                },

                openQueueModal() {
                    this.showQueueModal = true;
                    this.queueType = 'SALES';
                    setTimeout(() => { 
                        if(this.$refs.queueInput) this.$refs.queueInput.focus(); 
                    }, 100);
                },

                openDeliveryModal(data) {
                    this.pickup = data;
                    if (data.is_third_party) { 
                        this.isThirdParty = true; 
                        this.receiverName = data.receiver_name; 
                    } else { 
                        this.isThirdParty = false; 
                        this.receiverName = ''; 
                    }
                    this.showDeliveryModal = true;
                    setTimeout(() => { this.initPad(); }, 100);
                },

                closeModal() { 
                    this.showDeliveryModal = false; 
                },

                initPad() {
                    const canvas = this.$refs.signature_canvas;
                    if(!canvas) return;

                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);

                    if (this.signaturePad) { 
                        this.signaturePad.clear(); 
                    } else {
                        this.signaturePad = new SignaturePad(canvas, { 
                            backgroundColor: 'rgba(255,255,255,0)', 
                            penColor: 'rgb(0,0,0)', 
                            velocityFilterWeight: 0.7 
                        });
                        this.signaturePad.addEventListener("beginStroke", () => { this.isPadEmpty = false; });
                    }
                    this.isPadEmpty = true;
                },

                clearPad() { 
                    if (this.signaturePad) { 
                        this.signaturePad.clear(); 
                        this.isPadEmpty = true; 
                        this.signatureData = ''; 
                    } 
                },

                submitDelivery() {
                    if (!this.signaturePad || this.signaturePad.isEmpty()) {
                        alert('La firma es obligatoria.');
                        return;
                    }
                    this.signatureData = this.signaturePad.toDataURL('image/png');
                    
                    this.$nextTick(() => {
                        document.getElementById('deliveryForm').submit();
                    });
                }
            }
        }
    </script>
</x-tablet-layout>