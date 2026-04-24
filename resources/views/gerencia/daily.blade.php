<x-gerencia-layout>
    <div x-data="{ 
            showEditModal: false, 
            showDetailsModal: false,
            showDeleteModal: false,
            isLoading: false,
            showImageViewer: false,
            imageViewerUrl: '',
            openImageViewer(url) {
                this.imageViewerUrl = url;
                this.showImageViewer = true;
            },
            search: '', status: 'ALL', department: 'ALL',

            // Lógica de Confirmación Masiva (Con filtro anti-duplicados)
            selectedPickups: [],
            toggleAll(event) {
                if (event.target.checked) {
                    let ids = new Set();
                    document.querySelectorAll('.pickup-checkbox').forEach(cb => ids.add(cb.value));
                    this.selectedPickups = Array.from(ids);
                } else {
                    this.selectedPickups = [];
                }
            },

            // Lógica de Rechazo Rápido
            showRejectModal: false,
            rejectData: { id: 0, ticket_folio: '' },
            openRejectModal(id, folio) {
                this.rejectData = { id, ticket_folio: folio };
                this.showRejectModal = true;
            },

            // Resto de variables
            editData: { id: 0, ticket_folio: '', department: 'AROMAS', pieces: 1, notes: '' },
            detailsData: { ticket_folio: '', client_name: '', pieces: 0, status_name: '', notes: '', initial_evidence_url: '', package_evidence_url: '', evidence_url: '', signature_url: '', delivered_at: '' },
            deleteId: null, deleteFolio: '',

            openEditModal(data) {
                this.editData = { id: data.id, ticket_folio: data.ticket_folio, department: data.department, pieces: data.pieces, notes: data.notes || '' };
                this.showEditModal = true;
            },

            openDetailsModal(data) {
                let rawNotes = data.notes || '';
                if (!rawNotes.includes('\n') && rawNotes.includes('[Checador]:')) {
                    rawNotes = rawNotes.split('[Checador]:').join('\n[Checador]:');
                }
                let parsed = [];
                let lines = rawNotes.split('\n');
                lines.forEach((line, index) => {
                    let text = line.trim();
                    if(text !== '') {
                        let isChecker = text.startsWith('[Checador]:');
                        let cleanText = text.replace('[Checador]:', '').trim();
                        parsed.push({ id: index, isChecker: isChecker, author: isChecker ? 'Checador' : 'Gerencia', text: cleanText });
                    }
                });
                data.parsedNotes = parsed;
                this.detailsData = data;
                this.showDetailsModal = true;
            },

            openDeleteModal(id, folio) { this.deleteId = id; this.deleteFolio = folio; this.showDeleteModal = true; },

            async fetchResults() {
                this.isLoading = true;
                const params = new URLSearchParams({ search: this.search, status: this.status, department: this.department });
                const url = `{{ route('gerencia.daily') }}?${params.toString()}`;
                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    document.getElementById('table-container').innerHTML = await response.text();
                } catch (e) { console.error(e); }
                this.isLoading = false;
            }
        }" class="pb-20">

        {{-- CABECERA --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Operación Diaria</h1>
                <p class="text-gray-400 text-sm mt-1">Auditoría visual de resguardos y control de entregas.</p>
            </div>

            {{-- BOTÓN DE APROBACIÓN MASIVA --}}
            <form action="{{ route('gerencia.pickups.bulkApprove') }}" method="POST" x-show="selectedPickups.length > 0" class="flex items-center" x-transition>
                @csrf
                <template x-for="id in selectedPickups" :key="id">
                    <input type="hidden" name="pickup_ids[]" :value="id">
                </template>
                <button type="submit" class="bg-green-600 hover:bg-green-500 text-white font-bold py-2.5 px-6 rounded-lg shadow-lg transition-all flex items-center shadow-green-500/20 active:scale-95">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Confirmar Seleccionados (<span x-text="selectedPickups.length"></span>)
                </button>
            </form>
        </div>

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="bg-aromas-secondary p-4 rounded-xl border border-aromas-tertiary/20 mb-6 flex flex-wrap gap-4 items-center shadow-lg">
            <div class="flex-1 min-w-[250px] relative">
                <input type="text" x-model="search" @input.debounce.500ms="fetchResults()" placeholder="Buscar por folio o cliente..." class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg pl-10 pr-4 py-2 text-white text-sm focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all">
                <svg class="w-4 h-4 text-gray-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <select x-model="department" @change="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all cursor-pointer">
                <option value="ALL">Todas las Areas</option>
                <option value="AROMAS">Aromas</option>
                <option value="BELLAROMA">Bellaroma</option>
                <option value="CALLCENTER">Call Center</option>
            </select>
            <select x-model="status" @change="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all cursor-pointer">
                <option value="ALL">Todos los Estados</option>
                <option value="PENDING_CONFIRMATION">Por Confirmar</option>
                <option value="IN_CUSTODY">En Custodia</option>
                <option value="DELIVERED">Entregados</option>
            </select>
        </div>

        {{-- BOTÓN "SELECCIONAR TODOS" EXCLUSIVO PARA MÓVIL --}}
        <div class="md:hidden flex justify-between items-center bg-gray-900 border border-gray-700 p-3 rounded-xl mb-4 shadow-lg">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Selección Masiva</span>
            <label class="flex items-center gap-2 cursor-pointer">
                <span class="text-xs font-bold text-green-500 uppercase tracking-widest">Todos</span>
                <input type="checkbox" @change="toggleAll($event)" class="w-6 h-6 rounded border-gray-500 bg-black/40 text-green-500 focus:ring-green-500 shadow-inner transition-transform active:scale-90">
            </label>
        </div>

        {{-- TABLA DE RESULTADOS --}}
        <div id="table-container" :class="isLoading ? 'opacity-50 pointer-events-none' : ''" class="transition-opacity">
            @include('gerencia.partials.daily-table')
        </div>

        {{-- ========================================================== --}}
        {{-- MODALES                                                    --}}
        {{-- ========================================================== --}}

        {{-- MODAL DE RECHAZO RAPIDO --}}
        <div x-show="showRejectModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4" x-transition>
            <div @click.away="showRejectModal = false" class="bg-aromas-secondary border border-red-500/30 rounded-xl p-6 max-w-md w-full shadow-2xl relative">
                <h3 class="text-xl font-bold text-white mb-2 uppercase tracking-widest">Solicitar Correccion</h3>
                <p class="text-gray-400 text-sm mb-4">Indica el problema del folio <span class="text-white font-bold" x-text="rejectData.ticket_folio"></span> para el checador.</p>
                <form :action="'/gerencia/pickups/' + rejectData.id + '/reject'" method="POST" class="space-y-4">
                    @csrf
                    <textarea name="correction_notes" rows="3" required placeholder="Escribe el motivo del rechazo..." class="w-full bg-black/40 border border-gray-600 rounded-lg px-4 py-2 text-white focus:ring-red-500 transition-all"></textarea>
                    <div class="flex gap-3">
                        <button type="button" @click="showRejectModal = false" class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-bold transition-colors uppercase tracking-widest text-xs">Cancelar</button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-500 text-white rounded-lg font-bold transition-colors shadow-lg uppercase tracking-widest text-xs">Enviar a Checador</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL EDITAR --}}
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4" x-transition>
            <div @click.away="showEditModal = false" class="bg-aromas-secondary rounded-xl shadow-2xl border border-aromas-tertiary/30 w-full max-w-lg overflow-hidden">
                <div class="p-6 border-b border-aromas-tertiary/30 flex justify-between items-center bg-black/20">
                    <h3 class="text-xl font-bold text-white uppercase tracking-wider">Editar Resguardo <span x-text="'#'+editData.ticket_folio" class="text-aromas-highlight"></span></h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form :action="'{{ url('/gerencia/update') }}/' + editData.id" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1">Folio del Ticket</label>
                        <input type="text" name="ticket_folio" x-model="editData.ticket_folio" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1">Área</label>
                            <select name="department" x-model="editData.department" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight transition-all">
                                <option value="AROMAS">Aromas</option>
                                <option value="BELLAROMA">Bellaroma</option>
                                <option value="CALLCENTER">Call Center</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1">Piezas</label>
                            <input type="number" name="pieces" x-model="editData.pieces" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1">Notas</label>
                        <textarea name="notes" x-model="editData.notes" rows="3" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight transition-all"></textarea>
                    </div>
                    <div class="flex justify-end pt-4 border-t border-aromas-tertiary/30 gap-3">
                        <button type="button" @click="showEditModal = false" class="py-3 px-6 text-gray-400 hover:text-white font-bold transition-colors uppercase tracking-widest text-xs">Cancelar</button>
                        <button type="submit" class="bg-aromas-highlight hover:bg-yellow-600 text-black font-black py-3 px-6 rounded-lg uppercase tracking-widest text-xs transition-colors shadow-lg">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL DETALLES (VISUALIZAR) --}}
        <div x-show="showDetailsModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4" x-transition>
            <div @click.away="showDetailsModal = false" class="bg-aromas-secondary rounded-2xl shadow-2xl border border-aromas-tertiary/30 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 p-6 border-b border-aromas-tertiary/30 flex justify-between items-center bg-aromas-secondary/95 backdrop-blur-md z-10">
                    <div>
                        <h3 class="text-2xl font-black text-white uppercase tracking-tighter" x-text="'Resguardo #' + detailsData.ticket_folio"></h3>
                        <p class="text-xs text-aromas-highlight font-bold uppercase tracking-widest mt-1" x-text="detailsData.status_name"></p>
                    </div>
                    <button @click="showDetailsModal = false" class="p-2 bg-white/5 hover:bg-white/10 rounded-full text-gray-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-6">
                        <div class="bg-black/20 p-4 rounded-xl border border-white/5 shadow-inner">
                            <label class="block text-[10px] font-black text-aromas-highlight uppercase tracking-widest mb-1">Cliente / Propietario</label>
                            <p class="text-xl font-bold text-white" x-text="detailsData.client_name || 'No asignado'"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5 shadow-inner">
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Total Piezas</label>
                                <p class="text-lg font-bold text-white" x-text="detailsData.pieces"></p>
                            </div>
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5 shadow-inner">
                                <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-1">Área Origen</label>
                                <p class="text-lg font-bold text-white" x-text="detailsData.department"></p>
                            </div>
                        </div>
                        <div class="bg-black/20 p-4 rounded-xl border border-white/5 flex flex-col h-48 shadow-inner">
                            <label class="block text-[10px] font-black text-gray-500 uppercase tracking-widest mb-3 border-b border-gray-700 pb-2">Historial de Observaciones</label>
                            <div class="flex-1 overflow-y-auto space-y-3 pr-2 custom-scrollbar">
                                <template x-if="detailsData.parsedNotes && detailsData.parsedNotes.length === 0">
                                    <div class="text-center text-gray-500 text-xs italic mt-4">Sin observaciones registradas.</div>
                                </template>
                                <template x-for="note in detailsData.parsedNotes" :key="note.id">
                                    <div class="flex flex-col w-full" :class="note.isChecker ? 'items-end' : 'items-start'">
                                        <div class="max-w-[85%] p-3 rounded-2xl text-sm"
                                            :class="note.isChecker ? 'bg-sky-900/40 border border-sky-500/30 text-sky-100 rounded-tr-sm' : 'bg-gray-800 border border-gray-600 text-gray-200 rounded-tl-sm'">
                                            <span class="block text-[9px] font-black uppercase mb-1 opacity-60 tracking-widest" :class="note.isChecker ? 'text-sky-300 text-right' : 'text-gray-400'" x-text="note.author"></span>
                                            <span x-text="note.text" class="leading-relaxed"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <label class="block text-[12px] font-black text-aromas-highlight uppercase tracking-widest text-center">Evidencias Registradas</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <span class="text-[9px] text-gray-500 font-bold uppercase tracking-widest mb-2 block">Foto Ticket Base</span>
                                <div class="aspect-square bg-black/40 rounded-lg border border-white/5 overflow-hidden flex items-center justify-center p-2 shadow-inner">
                                    <template x-if="detailsData.initial_evidence_url">
                                        <img :src="detailsData.initial_evidence_url" class="w-full h-full object-contain cursor-pointer" @click="openImageViewer(detailsData.initial_evidence_url)">
                                    </template>
                                    <template x-if="!detailsData.initial_evidence_url">
                                        <span class="text-xs text-gray-500 font-bold italic">N/D</span>
                                    </template>
                                </div>
                            </div>
                            <div class="text-center flex flex-col">
                                <span class="text-[9px] text-gray-500 font-bold uppercase tracking-widest mb-2 block" x-text="detailsData.evidence_url ? 'Foto de Entrega' : 'Foto de Bolsas'"></span>
                                <div class="flex-1 bg-black/40 rounded-lg border border-white/5 overflow-hidden flex items-center justify-center p-2 min-h-[200px] shadow-inner">
                                    <template x-if="detailsData.evidence_url">
                                        <img :src="detailsData.evidence_url" class="w-full h-full object-contain cursor-pointer" @click="openImageViewer(detailsData.evidence_url)">
                                    </template>
                                    <template x-if="!detailsData.evidence_url && detailsData.package_evidence_url">
                                        <img :src="detailsData.package_evidence_url" class="w-full h-full object-contain cursor-pointer" @click="openImageViewer(detailsData.package_evidence_url)">
                                    </template>
                                    <template x-if="!detailsData.evidence_url && !detailsData.package_evidence_url">
                                        <span class="text-xs text-gray-500 font-bold italic">N/D</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- MODAL ELIMINAR --}}
        <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4" x-transition>
            <div class="bg-aromas-secondary border border-red-500/30 rounded-xl p-8 max-w-sm w-full text-center shadow-2xl relative">
                <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-500/20">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2 uppercase tracking-widest">¿Eliminar Resguardo?</h3>
                <p class="text-gray-400 text-sm mb-8">Estás a punto de borrar el folio <span class="text-white font-bold" x-text="deleteFolio"></span>. Esta acción no se puede deshacer.</p>
                <form :action="'{{ url('/gerencia/destroy') }}/' + deleteId" method="POST" class="flex gap-3">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-bold transition-colors uppercase tracking-widest text-xs">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-500 text-white rounded-lg font-bold transition-colors shadow-lg shadow-red-600/20 uppercase tracking-widest text-xs">Eliminar</button>
                </form>
            </div>
        </div>

        {{-- VISOR DE IMÁGENES AL PASAR EL MOUSE O TOCAR (HOVER PREVIEW) --}}
        <div x-show="showImageViewer" style="display: none;"
            class="fixed inset-0 z-[150] flex items-center justify-center pointer-events-none p-4"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <div class="relative max-w-4xl max-h-[85vh] bg-black/90 p-2 md:p-4 rounded-2xl shadow-[0_0_50px_rgba(0,0,0,0.8)] border border-gray-700">
                <img :src="imageViewerUrl" class="w-full h-full object-contain rounded-xl">
            </div>
        </div>

    </div>
</x-gerencia-layout>