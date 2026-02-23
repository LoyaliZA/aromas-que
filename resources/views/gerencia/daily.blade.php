<x-gerencia-layout>
    
    {{-- CONTENEDOR PRINCIPAL: Controla Modales, Búsqueda y Estado --}}
    <div x-data="{ 
            // Estado de Modales
            showCreateModal: false, 
            showEditModal: false, 
            showDetailsModal: false, 
            
            // Datos para Crear (Control local para la lógica de visualización)
            createState: {
                isThirdParty: false
            },

            // Datos para Editar (Se llenan al hacer click)
            editData: { 
                id: 0, 
                ticket_folio: '', 
                ticket_date: '',
                client_ref_id: '',
                client_name: '', 
                department: 'AROMAS', 
                pieces: 1, 
                notes: '', 
                is_third_party: false, 
                receiver_name: '' 
            },

            // Datos para Detalles de Entrega
            detailsData: {
                ticket_folio: '',
                client_name: '',
                receiver_name: '',
                is_third_party: false,
                delivered_at: '',
                signature_url: '',
                notes: '',
                evidence_url: ''
            },

            // Estado de Filtros y Carga
            search: '',
            status: 'ALL',
            department: 'ALL',
            isLoading: false,

            // ABRIR MODAL EDITAR (Prepara los datos)
            openEditModal(data) {
                // Convertimos el 1/0 de la BD a true/false de JS para que el checkbox funcione
                data.is_third_party = Boolean(data.is_third_party);
                this.editData = data;
                this.showEditModal = true;
            },

            // ABRIR MODAL DETALLES
            openDetailsModal(data) {
                this.detailsData = data;
                this.showDetailsModal = true;
            },

            // LIMPIAR MODAL CREAR
            resetCreateModal() {
                this.createState.isThirdParty = false;
                this.showCreateModal = false;
                // Opcional: limpiar inputs si quisieras
            },

            // FETCH AJAX (Búsqueda en vivo)
            async fetchResults() {
                this.isLoading = true;
                const params = new URLSearchParams({
                    search: this.search,
                    status: this.status,
                    department: this.department
                });
                const url = `{{ route('gerencia.daily') }}?${params.toString()}`;

                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await response.text();
                    document.getElementById('table-container').innerHTML = html;
                } catch (error) { console.error(error); } finally { this.isLoading = false; }
            }
         }">
        
        {{-- ENCABEZADO --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Operación Diaria</h1>
                <p class="text-aromas-tertiary text-sm">Registro y gestión de resguardos del día.</p>
            </div>
            <button @click="showCreateModal = true" class="bg-aromas-highlight hover:bg-white text-aromas-main font-bold py-2 px-6 rounded-lg transition-all shadow-lg flex items-center transform hover:scale-105 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Resguardo
            </button>
        </div>

        {{-- BARRA DE FILTROS --}}
        <div class="bg-aromas-secondary rounded-xl shadow-lg p-3 border border-aromas-tertiary/20 mb-2">
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
                    <input type="text" x-model="search" @input.debounce.500ms="fetchResults()" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg pl-9 pr-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight placeholder-gray-500 transition-all" placeholder="Buscar por folio, cliente o notas...">
                    <div x-show="isLoading" class="absolute right-3 top-2.5"><svg class="animate-spin h-4 w-4 text-aromas-highlight" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                </div>
                <div class="hidden md:block w-px h-8 bg-aromas-tertiary/20"></div>
                <div class="w-full md:w-auto"><select x-model="department" @change="fetchResults()" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight cursor-pointer"><option value="ALL">Área: Todas</option><option value="AROMAS">Aromas</option><option value="BELLAROMA">Bellaroma</option></select></div>
                <div class="w-full md:w-auto"><select x-model="status" @change="fetchResults()" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight cursor-pointer"><option value="ALL">Estado: Todos</option><option value="IN_CUSTODY">📦 En Custodia</option><option value="DELIVERED">✅ Entregados</option></select></div>
                <button x-show="search || status !== 'ALL' || department !== 'ALL'" @click="search=''; status='ALL'; department='ALL'; fetchResults()" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-full transition-colors" title="Limpiar"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
        </div>

        {{-- TABLA AJAX --}}
        <div id="table-container" class="transition-opacity duration-200" :class="isLoading ? 'opacity-50' : 'opacity-100'">
            @include('gerencia.partials.daily-table')
        </div>

        {{-- ========================================================== --}}
        {{--                    MODAL CREAR (ANIMADO)                   --}}
        {{-- ========================================================== --}}
        <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal" x-transition class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="resetCreateModal()"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showCreateModal" x-transition class="inline-block align-bottom bg-aromas-secondary rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-aromas-tertiary/20">
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-xl font-bold text-white mb-4 border-b border-aromas-tertiary/20 pb-2">Nuevo Resguardo</h3>
                        <form id="createForm" action="{{ route('gerencia.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Folio Ticket</label><input type="text" name="ticket_folio" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight"></div>
                                    <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Fecha</label><input type="date" name="ticket_date" value="{{ date('Y-m-d') }}" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight"></div>
                                </div>
                                <div class="grid grid-cols-12 gap-4">
                                    <div class="col-span-3"><label class="block text-sm font-medium text-aromas-tertiary mb-1">No. Cliente</label><input type="text" name="client_ref_id" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight"></div>
                                    <div class="col-span-9"><label class="block text-sm font-medium text-aromas-tertiary mb-1">Nombre Cliente</label><input type="text" name="client_name" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Departamento</label><select name="department" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight"><option value="AROMAS">Aromas</option><option value="BELLAROMA">Bellaroma</option></select></div>
                                    <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Piezas</label><input type="number" name="pieces" value="1" min="1" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight"></div>
                                </div>
                                <div class="pt-2 border-t border-aromas-tertiary/10">
                                    <label class="inline-flex items-center cursor-pointer mb-2">
                                        <input type="checkbox" name="is_third_party" value="1" x-model="createState.isThirdParty" class="form-checkbox bg-black/20 border-aromas-tertiary/30 text-aromas-highlight rounded focus:ring-aromas-highlight">
                                        <span class="ml-2 text-sm text-gray-300">¿Recoge otra persona?</span>
                                    </label>
                                    <div x-show="createState.isThirdParty" x-transition class="mt-2">
                                        <label class="block text-sm font-medium text-aromas-tertiary mb-1">Nombre de quien recoge</label>
                                        <input type="text" name="receiver_name" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight" placeholder="Ej. Familiar, Mensajero...">
                                    </div>
                                </div>
                                <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Notas / Comentarios</label><textarea name="notes" rows="2" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight" placeholder="Detalles adicionales..."></textarea></div>
                            </div>
                        </form>
                    </div>
                    <div class="bg-black/20 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-aromas-tertiary/10">
                        <button type="submit" form="createForm" class="w-full inline-flex justify-center rounded-lg bg-aromas-highlight text-aromas-main font-bold px-4 py-2 hover:bg-white sm:ml-3 sm:w-auto">Guardar</button>
                        <button type="button" @click="resetCreateModal()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-aromas-tertiary/30 px-4 py-2 text-gray-300 hover:text-white hover:bg-white/5 sm:mt-0 sm:ml-3 sm:w-auto">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{--                    MODAL EDITAR (ANIMADO)                  --}}
        {{-- ========================================================== --}}
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal" x-transition class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showEditModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showEditModal" x-transition class="inline-block align-bottom bg-aromas-secondary rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-yellow-500/30">
                    <div class="bg-yellow-900/40 p-5 border-b border-yellow-500/20 flex items-start">
                        <div class="p-2 bg-yellow-500/20 rounded-full mr-3 shrink-0"><svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
                        <div><h3 class="text-lg font-bold text-yellow-400">Modo de Edición</h3><p class="text-xs text-yellow-200/80 mt-1">Modificando registro existente. Acción registrada.</p></div>
                    </div>
                    <div class="px-6 py-6">
                        <form id="editForm" method="POST" :action="`{{ url('gerencia/update') }}/${editData.id}`">
                            @csrf @method('PUT')
                            <div class="space-y-4">
                                <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Folio Ticket</label><input type="text" name="ticket_folio" x-model="editData.ticket_folio" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500"></div>
                                <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Cliente</label><input type="text" name="client_name" x-model="editData.client_name" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500"></div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Departamento</label><select name="department" x-model="editData.department" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500"><option value="AROMAS">Aromas</option><option value="BELLAROMA">Bellaroma</option></select></div>
                                    <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Piezas</label><input type="number" name="pieces" x-model="editData.pieces" min="1" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500"></div>
                                </div>
                                <div class="pt-2 border-t border-aromas-tertiary/10">
                                    <label class="inline-flex items-center cursor-pointer mb-2">
                                        <input type="checkbox" name="is_third_party" value="1" x-model="editData.is_third_party" class="form-checkbox bg-black/20 border-aromas-tertiary/30 text-yellow-500 rounded focus:ring-yellow-500">
                                        <span class="ml-2 text-sm text-gray-300">¿Recoge otra persona?</span>
                                    </label>
                                    <div x-show="editData.is_third_party" x-transition class="mt-2">
                                        <label class="block text-sm font-medium text-aromas-tertiary mb-1">Nombre de quien recoge</label>
                                        <input type="text" name="receiver_name" x-model="editData.receiver_name" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500">
                                    </div>
                                </div>
                                <div><label class="block text-sm font-medium text-aromas-tertiary mb-1">Notas</label><textarea name="notes" x-model="editData.notes" rows="2" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500"></textarea></div>
                            </div>
                        </form>
                    </div>
                    <div class="bg-black/20 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-yellow-500/10">
                        <button type="submit" form="editForm" class="w-full inline-flex justify-center rounded-lg bg-yellow-600 text-white font-bold px-4 py-2 hover:bg-yellow-500 sm:ml-3 sm:w-auto">Confirmar Cambio</button>
                        <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-aromas-tertiary/30 px-4 py-2 text-gray-300 hover:text-white hover:bg-white/5 sm:mt-0 sm:ml-3 sm:w-auto">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================== --}}
        {{--           MODAL DE DETALLES DE ENTREGA                     --}}
        {{-- ========================================================== --}}
        <div x-show="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" x-transition>
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showDetailsModal = false"></div>
            
            <div class="bg-aromas-secondary w-full max-w-2xl rounded-xl shadow-2xl border border-aromas-tertiary/30 relative z-10 flex flex-col overflow-hidden">
                <div class="bg-black/20 p-5 border-b border-aromas-tertiary/20 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Detalles de Entrega
                        </h2>
                        <p class="text-sm text-aromas-tertiary">Folio: <span class="text-aromas-highlight font-mono" x-text="detailsData.ticket_folio"></span></p>
                    </div>
                    <button @click="showDetailsModal = false" class="text-gray-500 hover:text-white p-2 bg-white/5 rounded-lg transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[80vh]">
                    <div class="bg-black/20 p-4 rounded-xl border border-aromas-tertiary/10 mb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-1">Entregado el:</label><p class="text-white font-mono" x-text="detailsData.delivered_at"></p></div>
                            <div>
                                <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-1">Tipo Receptor:</label>
                                <span class="px-2 py-1 rounded text-xs font-bold uppercase" :class="detailsData.is_third_party ? 'bg-yellow-500/20 text-yellow-500' : 'bg-blue-500/20 text-blue-400'" x-text="detailsData.is_third_party ? 'Tercero' : 'Titular'"></span>
                            </div>
                            <div class="col-span-2 border-t border-aromas-tertiary/10 pt-3 mt-1">
                                <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-1">Nombre Quien Recibió:</label>
                                <p class="text-lg text-white font-bold leading-tight" x-text="detailsData.receiver_name"></p>
                                <p class="text-xs text-gray-500 mt-1" x-show="detailsData.is_third_party">(Titular de cuenta: <span x-text="detailsData.client_name"></span>)</p>
                            </div>
                        </div>
                    </div>

                    {{-- Notas del Checador --}}
                    <template x-if="detailsData.notes">
                        <div class="bg-black/20 p-4 rounded-xl border border-aromas-tertiary/10 mb-4">
                            <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-1">Observaciones / Notas:</label>
                            <p class="text-white text-sm whitespace-pre-line" x-text="detailsData.notes"></p>
                        </div>
                    </template>

                    {{-- Firmas y Evidencias --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-2">Firma del Cliente</label>
                            <div class="bg-white rounded-lg p-2 border-2 border-gray-300 flex items-center justify-center min-h-[12rem]">
                                <template x-if="detailsData.signature_url">
                                    <img :src="detailsData.signature_url" alt="Firma Digital" class="w-full h-auto object-contain max-h-48">
                                </template>
                                <template x-if="!detailsData.signature_url">
                                    <div class="text-gray-400 italic text-sm">Firma no disponible</div>
                                </template>
                            </div>
                        </div>

                        <div x-show="detailsData.evidence_url">
                            <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-2">Foto de Evidencia</label>
                            <div class="bg-white rounded-lg p-2 border-2 border-gray-300 flex items-center justify-center min-h-[12rem]">
                                <template x-if="detailsData.evidence_url">
                                    <img :src="detailsData.evidence_url" alt="Evidencia" class="w-full h-auto object-contain max-h-48">
                                </template>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="p-4 bg-black/20 border-t border-aromas-tertiary/20 flex justify-end">
                    <button @click="showDetailsModal = false" class="px-6 py-2 bg-aromas-tertiary/20 border border-aromas-tertiary/30 rounded-lg text-white hover:bg-white/10 transition-colors">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-gerencia-layout>