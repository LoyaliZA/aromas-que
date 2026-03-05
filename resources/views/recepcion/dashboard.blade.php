<x-tablet-layout>
    <div x-data="deliveryApp({{ $peopleInQueue }})" x-init="init()" class="pb-10 relative">

        {{-- ========================================================== --}}
        {{-- SECCIÓN 1: GESTIÓN DE FILA Y TURNOS                     --}}
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
            {{-- Botón 1: Ticket de Turno --}}
            <button @click="openQueueModal()"
                class="bg-aromas-highlight text-aromas-main rounded-xl p-4 shadow-lg flex items-center justify-between group transform transition-all hover:scale-[1.01] hover:shadow-[0_0_15px_rgba(253,201,116,0.4)] border-2 border-transparent hover:border-white/20">
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

            {{-- Botón 2: Ver Fila (Reemplaza al antiguo botón QR) --}}
            <button @click="openQueueListModal()"
                class="bg-gray-800 border border-gray-700 hover:border-aromas-highlight text-white rounded-xl p-4 shadow-lg flex items-center justify-between group transform transition-all hover:scale-[1.01]">
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
        {{-- SECCIÓN 2: PAQUETES EN RESGUARDO                        --}}
        {{-- ========================================================== --}}
        <div class="mb-4 flex items-center gap-3">
            <div class="p-2 bg-blue-500/20 rounded-lg text-blue-400 shadow-inner">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
            </div>
            <h2 class="text-xl font-black text-white uppercase tracking-widest">Paquetes en Resguardo</h2>
        </div>

        {{-- Barra de Búsqueda y Filtros --}}
        <div class="bg-aromas-secondary rounded-xl p-4 shadow-md border border-aromas-tertiary/20 mb-6 sticky top-2 z-30">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-aromas-tertiary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <input type="text" x-model.debounce.500ms="search"
                        class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg py-3 pl-10 pr-4 text-white placeholder-gray-500 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all"
                        placeholder="Buscar folio, cliente o receptor...">

                    <div x-show="isLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center" style="display: none;">
                        <svg class="animate-spin h-5 w-5 text-aromas-highlight" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
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

        {{-- Alertas Flash --}}
        @if(session('new_turn'))
        <div x-data="{ showTurn: true }" x-show="showTurn" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showTurn = false"></div>
            <div class="bg-aromas-secondary border-2 border-aromas-highlight rounded-2xl shadow-[0_0_30px_rgba(253,201,116,0.3)] p-8 max-w-sm w-full text-center relative z-10 animate-fade-in-down">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-500/20 mb-4">
                    <svg class="h-10 w-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
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

        {{-- CONTENEDOR CARDS (AJAX) Y PAGINACIÓN --}}
        <div id="results-container">
            @include('recepcion.partials.card-grid', ['pickups' => $pickups])
        </div>

        <div class="mt-6">
            {{ $pickups->links() }}
        </div>


        {{-- ========================================================== --}}
        {{-- MODALES                                                 --}}
        {{-- ========================================================== --}}

        {{-- MODAL 1: CONFIRMAR ENTREGA --}}
        <div x-show="showDeliveryModal" style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto"
            x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="closeModal()"></div>

            {{-- CONTENEDOR PRINCIPAL DEL MODAL (Ahora es max-w-5xl para ser muy amplio) --}}
            <div class="bg-aromas-secondary w-full max-w-5xl rounded-2xl shadow-2xl border border-aromas-tertiary/30 relative z-10 flex flex-col my-auto max-h-[95vh] overflow-y-auto">

                {{-- CABECERA DEL MODAL --}}
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

                {{-- CUERPO DEL FORMULARIO --}}
                <form id="deliveryForm" method="POST" enctype="multipart/form-data" :action="'/recepcion/confirm/' + pickup.id" class="p-8" @submit.prevent="submitDelivery">
                    @csrf @method('PUT')
                    <input type="hidden" name="signature" x-model="signatureData">

                    {{-- PARTE SUPERIOR: DATOS Y EVIDENCIA (2 Columnas) --}}
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">

                        {{-- COLUMNA IZQUIERDA: Receptor --}}
                        <div class="space-y-6">
                            <div class="space-y-4 bg-gray-900 border border-gray-700 p-6 rounded-2xl h-full">
                                <h3 class="text-lg font-bold text-aromas-highlight uppercase tracking-wider mb-4 border-b border-gray-700 pb-2">Datos de Recepción</h3>

                                <label class="flex items-center justify-between p-4 rounded-xl cursor-pointer transition-colors"
                                    :class="isThirdParty ? 'bg-aromas-highlight/10 border border-aromas-highlight/30' : 'bg-gray-800 border border-gray-700'">
                                    <div class="flex items-center gap-4">
                                        <input type="checkbox" name="is_third_party" x-model="isThirdParty" class="w-7 h-7 rounded border-gray-500 text-aromas-highlight focus:ring-aromas-highlight bg-gray-900 cursor-pointer">
                                        <div>
                                            <span class="block text-lg font-bold text-white">¿Recoge otra persona?</span>
                                            <span class="block text-sm text-gray-400">Marcar si no es el titular</span>
                                        </div>
                                    </div>
                                </label>

                                <div x-show="isThirdParty" x-transition class="pt-4">
                                    <label class="block text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Nombre completo de quien recibe</label>
                                    <input type="text" name="receiver_name" x-model="receiverName" placeholder="Ej. Juan Pérez - INE"
                                        class="w-full bg-gray-800 border border-gray-600 rounded-xl px-5 py-4 text-lg text-white focus:border-aromas-highlight focus:ring-aromas-highlight shadow-inner">
                                </div>

                                <div x-show="!isThirdParty" class="p-5 bg-blue-900/20 border border-blue-500/20 rounded-xl mt-4">
                                    <p class="text-sm text-blue-400 uppercase font-bold tracking-wider mb-1">Entregando a Titular Registrado:</p>
                                    <p class="text-2xl font-bold text-white" x-text="pickup.client_name"></p>
                                </div>
                            </div>
                        </div>

                        {{-- COLUMNA DERECHA: Evidencia Fotográfica y Notas --}}
                        <div class="space-y-6">
                            <div class="bg-gray-900 border-2 border-dashed border-gray-600 hover:border-aromas-highlight/50 rounded-2xl p-6 transition-colors">
                                <label class="block text-sm font-bold text-aromas-highlight uppercase tracking-wider mb-4 flex items-center gap-2">
                                    Evidencia Fotográfica *
                                </label>
                                <input type="file" name="evidence_file" id="evidence_file" accept="image/*" capture="environment" class="sr-only" @change="handleEvidenceChange">

                                <div class="relative">
                                    {{-- Sin Imagen --}}
                                    <label for="evidence_file" x-show="!evidencePreview"
                                        class="flex flex-col items-center justify-center gap-3 w-full h-32 bg-gray-800 rounded-xl border border-gray-700 cursor-pointer hover:bg-gray-700 transition-all group shadow-inner">
                                        <div class="p-3 bg-gray-900 rounded-full text-gray-400 group-hover:text-aromas-highlight group-hover:scale-110 transition-all">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        <span class="font-bold text-white text-lg">Tocar para tomar foto</span>
                                    </label>

                                    {{-- Con Imagen (Vista Previa) --}}
                                    <div x-show="evidencePreview" x-cloak class="relative h-32 rounded-xl overflow-hidden border-2 border-aromas-highlight shadow-lg">
                                        <img :src="evidencePreview" class="w-full h-full object-cover">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent p-3 flex flex-col justify-end">
                                            <p class="text-xs text-white truncate" x-text="evidenceName"></p>
                                            <button type="button" @click="removeEvidence()" class="absolute top-2 right-2 p-2 rounded-lg bg-red-500 text-white hover:bg-red-600 shadow-xl">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-gray-400 uppercase tracking-widest mb-2">Observaciones / Estado de entrega</label>
                                <textarea name="notes" rows="2" class="w-full bg-gray-900 border border-gray-700 rounded-xl px-5 py-4 text-white focus:border-aromas-highlight focus:ring-aromas-highlight shadow-inner resize-none text-lg" placeholder="Añadir notas..."></textarea>
                            </div>
                        </div>
                    </div>

                    {{-- PARTE INFERIOR: FIRMA GIGANTE --}}
                    <div class="bg-gray-800/80 border border-gray-700 rounded-3xl p-6 shadow-inner mb-8">
                        <div class="flex items-center justify-between mb-4">
                            <label class="text-xl font-bold text-aromas-highlight uppercase tracking-widest flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                                Firma Digital del Cliente *
                            </label>
                            <button type="button" @click="clearPad()" class="text-sm font-bold text-red-400 hover:text-white bg-red-500/10 hover:bg-red-500 px-5 py-2.5 rounded-xl transition-colors border border-red-500/30">
                                Limpiar Firma
                            </button>
                        </div>

                        {{-- CANVAS GIGANTE (Altura aumentada a h-72 para mucho espacio) --}}
                        <div class="relative bg-white rounded-2xl overflow-hidden border-4 border-gray-400 focus-within:border-aromas-highlight transition-colors shadow-inner">
                            <canvas x-ref="signature_canvas" class="w-full h-72 touch-none cursor-crosshair"></canvas>
                            <div x-show="isPadEmpty" class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-20">
                                <span class="text-6xl font-bold text-gray-500 uppercase tracking-widest opacity-50">Firmar Aquí</span>
                            </div>
                        </div>
                    </div>

                    {{-- BOTONES DE ACCIÓN GIGANTES --}}
                    <div class="pt-6 border-t border-gray-700 flex flex-col md:flex-row-reverse gap-6">
                        <button type="submit" class="w-full md:w-auto px-10 py-6 rounded-2xl bg-aromas-highlight text-aromas-main font-black text-xl tracking-widest uppercase hover:bg-white transition-all shadow-[0_0_30px_rgba(253,201,116,0.25)] active:scale-95 flex items-center justify-center gap-3">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            CONFIRMAR ENTREGA
                        </button>
                        <button type="button" @click="closeModal()" class="w-full md:w-auto px-10 py-6 rounded-2xl border-2 border-gray-600 text-gray-300 font-bold text-xl hover:bg-gray-700 hover:text-white transition-colors uppercase tracking-widest">
                            CANCELAR
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: TICKET DE TURNO --}}
        <div x-show="showQueueModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showQueueModal = false"></div>

            <div class="bg-aromas-secondary w-full max-w-md rounded-xl shadow-2xl border border-aromas-highlight/30 flex flex-col relative z-10">
                <div class="bg-aromas-highlight/10 p-4 border-b border-aromas-tertiary/20 rounded-t-xl flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Nuevo Ticket
                    </h2>
                    <button @click="showQueueModal = false" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
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
                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                <span class="font-bold text-sm">VENTAS</span>
                            </button>

                            <button type="button" @click="queueType = 'CASHIER'"
                                :class="queueType === 'CASHIER' ? 'bg-green-500 text-white ring-2 ring-green-500 ring-offset-2 ring-offset-gray-900' : 'bg-black/20 text-gray-400 hover:bg-white/5'"
                                class="p-3 rounded-xl border border-transparent flex flex-col items-center justify-center transition-all h-24">
                                <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
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

        {{-- MODAL 3: GESTIÓN DE LA FILA (VER Y ABANDONAR) --}}
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
                        <div class="bg-gray-800 p-4 rounded-full mb-3">
                            <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
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
                                        </div>
                                    </div>
                                </div>

                                <button @click="markAbandoned(client.id, client.client_name)" class="flex items-center gap-2 bg-red-500/10 text-red-400 border border-red-500/30 px-4 py-3 rounded-lg text-sm font-bold hover:bg-red-500 hover:text-white transition-colors">
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
                showQueueListModal: false, // <-- NUEVO ESTADO

                pickup: {},
                isThirdParty: false,
                receiverName: '',
                signaturePad: null,
                signatureData: '',
                isPadEmpty: true,

                queueType: 'SALES',
                waitingClients: [], // <-- ARREGLO PARA CLIENTES EN FILA
                evidencePreview: null,
                evidenceName: '', // Nuevas variables para manejo de evidencia

                init() {
                    this.$watch('search', (value) => {
                        this.fetchData(value);
                    });

                    setInterval(() => {
                        // Pausamos el refresco si CUALQUIER modal está abierto
                        if (this.showDeliveryModal || this.showQueueModal || this.showQueueListModal || this.search.length > 0) return;
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

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
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
                        if (this.$refs.queueInput) this.$refs.queueInput.focus();
                    }, 100);
                },

                // --- NUEVAS FUNCIONES PARA GESTIÓN DE FILA ---
                openQueueListModal() {
                    this.fetchQueueList();
                    this.showQueueListModal = true;
                },

                fetchQueueList() {
                    fetch("{{ route('recepcion.queue.list') }}", {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.clients) {
                                this.waitingClients = data.clients;
                                this.queueCount = data.clients.length;
                            }
                        });
                },

                markAbandoned(id, name) {
                    if (!confirm(`¿Estás seguro de que el cliente ${name} ya se retiró de la fila?`)) return;

                    fetch(`/recepcion/queue/${id}/abandon`, {
                            method: 'PUT',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                this.fetchQueueList(); // Refresca el modal interior
                                this.fetchData(this.search); // Refresca los conteos del dashboard
                            } else {
                                alert(data.message || 'Error al procesar la solicitud.');
                            }
                        });
                },

                // NUEVA FUNCIÓN: Confirmar Recepción en Almacén
                confirmReceipt(id) {
                    fetch(`/recepcion/receive/${id}`, {
                            method: 'PUT',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                this.fetchData(this.search); // Refresca las tarjetas visualmente
                            } else {
                                alert(data.message || 'Error al confirmar recepción.');
                            }
                        });
                },
                // ---------------------------------------------

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
                    setTimeout(() => {
                        this.initPad();
                    }, 100);
                },

                closeModal() {
                    this.showDeliveryModal = false;
                    // Limpiamos la evidencia al cerrar
                    this.evidencePreview = null;
                    this.evidenceName = '';
                    const fileInput = document.getElementById('evidence_file');
                    if (fileInput) fileInput.value = '';
                },

                // NUEVA: Manejar la foto
                handleEvidenceChange(event) {
                    const file = event.target.files[0];
                    if (!file) {
                        this.evidencePreview = null;
                        this.evidenceName = '';
                        return;
                    }
                    this.evidenceName = file.name;
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.evidencePreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                },

                // NUEVA: Borrar la foto
                removeEvidence() {
                    this.evidencePreview = null;
                    this.evidenceName = '';
                    const fileInput = document.getElementById('evidence_file');
                    if (fileInput) fileInput.value = '';
                },

                initPad() {
                    const canvas = this.$refs.signature_canvas;
                    if (!canvas) return;

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
                        this.signaturePad.addEventListener("beginStroke", () => {
                            this.isPadEmpty = false;
                        });
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