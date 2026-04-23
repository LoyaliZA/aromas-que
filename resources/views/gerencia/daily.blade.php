<x-gerencia-layout>
    {{-- CONTEXTO GLOBAL DE ALPINE --}}
    <div x-data="{ 
            showCreateModal: false,
            showEditModal: false, 
            showDetailsModal: false,
            showDeleteModal: false,
            isComplementary: false,
            isLoading: false,
            search: '', status: 'ALL', department: 'ALL',

            // Datos para Auditoría
            showAuditModal: false,
            auditData: { 
                id: 0, ticket_folio: '', client_name: '', pieces: 0, bags: 0, 
                department: '', notes: '', initial_evidence_url: '', package_evidence_url: '' 
            },

            // Datos para Edición
            editData: { id: 0, ticket_folio: '', department: 'AROMAS', pieces: 1, notes: '' },

            // Datos para Visualización
            detailsData: { 
                ticket_folio: '', client_name: '', pieces: 0, status_name: '', 
                notes: '', initial_evidence_url: '', package_evidence_url: '',
                evidence_url: '', signature_url: '', delivered_at: ''
            },

            // Datos para Eliminación
            deleteId: null,
            deleteFolio: '',

            openEditModal(data) {
                this.editData = {
                    id: data.id,
                    ticket_folio: data.ticket_folio,
                    department: data.department,
                    pieces: data.pieces,
                    notes: data.notes || ''
                };
                this.showEditModal = true;
            },

            openDetailsModal(data) {
                let rawNotes = data.notes || '';
                
                // Blindaje: Si el texto viene pegado de pruebas anteriores, forzamos la separación
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
                        parsed.push({ 
                            id: index, 
                            isChecker: isChecker, 
                            author: isChecker ? 'Checador' : 'Gerencia', 
                            text: cleanText 
                        });
                    }
                });

                data.parsedNotes = parsed;
                this.detailsData = data;
                this.showDetailsModal = true;
            },

            openDeleteModal(id, folio) {
                this.deleteId = id;
                this.deleteFolio = folio;
                this.showDeleteModal = true;
            },

            openAuditModal(data) {
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
                        parsed.push({ 
                            id: index, 
                            isChecker: isChecker, 
                            author: isChecker ? 'Checador' : 'Gerencia', 
                            text: cleanText 
                        });
                    }
                });

                data.parsedNotes = parsed;
                this.auditData = data;
                this.showAuditModal = true;
            },

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

        {{-- CABECERA Y FILTROS --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Operación Diaria</h1>
                <p class="text-gray-400 text-sm mt-1">Gestión de resguardos preliminares y entregas en tienda.</p>
            </div>

            <button @click="showCreateModal = true" class="bg-aromas-highlight hover:bg-yellow-600 text-aromas-main font-bold py-2.5 px-6 rounded-lg shadow-lg transition-all flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nuevo Resguardo
            </button>
        </div>

        {{-- BARRA DE BÚSQUEDA --}}
        <div class="bg-aromas-secondary p-4 rounded-xl border border-aromas-tertiary/20 mb-6 flex flex-wrap gap-4 items-center">
            <div class="flex-1 min-w-[250px] relative">
                <input type="text" x-model="search" @input.debounce.500ms="fetchResults()" placeholder="Buscar por folio o cliente..." class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg pl-10 pr-4 py-2 text-white text-sm focus:border-aromas-highlight">
                <svg class="w-4 h-4 text-gray-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
            <select x-model="department" @change="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm">
                <option value="ALL">Todas las Áreas</option>
                <option value="AROMAS">Aromas</option>
                <option value="BELLAROMA">Bellaroma</option>
                <option value="CALLCENTER">Call Center</option>
            </select>
            <select x-model="status" @change="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm">
                <option value="ALL">Todos los Estados</option>
                <option value="IN_CUSTODY">📦 En Custodia</option>
                <option value="DELIVERED">✅ Entregados</option>
            </select>
        </div>

        {{-- TABLA DE RESULTADOS --}}
        <div id="table-container" :class="isLoading ? 'opacity-50 pointer-events-none' : ''" class="transition-opacity">
            @include('gerencia.partials.daily-table')
        </div>

        {{-- ========================================================== --}}
        {{-- MODALES                                                    --}}
        {{-- ========================================================== --}}

        {{-- 1. MODAL CREAR PRELIMINAR --}}
        @include('gerencia.partials.modals.create-preliminar')

        {{-- 2. MODAL EDITAR --}}
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
            <div @click.away="showEditModal = false" class="bg-aromas-secondary rounded-xl shadow-2xl border border-aromas-tertiary/30 w-full max-w-lg overflow-hidden">
                <div class="p-6 border-b border-aromas-tertiary/30 flex justify-between items-center bg-black/20">
                    <h3 class="text-xl font-bold text-white">Editar Resguardo <span x-text="'#'+editData.ticket_folio" class="text-aromas-highlight"></span></h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>
                <form :action="'{{ url('/gerencia/update') }}/' + editData.id" method="POST" class="p-6 space-y-4">
                    @csrf @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Folio del Ticket</label>
                        <input type="text" name="ticket_folio" x-model="editData.ticket_folio" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Área</label>
                            <select name="department" x-model="editData.department" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white">
                                <option value="AROMAS">Aromas</option>
                                <option value="BELLAROMA">Bellaroma</option>
                                <option value="CALLCENTER">Call Center</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Piezas</label>
                            <input type="number" name="pieces" x-model="editData.pieces" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300">Notas</label>
                        <textarea name="notes" x-model="editData.notes" rows="3" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white"></textarea>
                    </div>
                    <div class="flex justify-end pt-4 border-t border-aromas-tertiary/30">
                        <button type="button" @click="showEditModal = false" class="mr-4 text-gray-400 hover:text-white">Cancelar</button>
                        <button type="submit" class="bg-aromas-highlight hover:bg-yellow-600 text-black font-bold py-2 px-6 rounded-lg">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 3. MODAL DETALLES (VISUALIZAR) --}}
        <div x-show="showDetailsModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4">
            <div @click.away="showDetailsModal = false" class="bg-aromas-secondary rounded-2xl shadow-2xl border border-aromas-tertiary/30 w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 p-6 border-b border-aromas-tertiary/30 flex justify-between items-center bg-aromas-secondary/95 backdrop-blur-md z-10">
                    <div>
                        <h3 class="text-2xl font-black text-white uppercase tracking-tighter" x-text="'Resguardo #' + detailsData.ticket_folio"></h3>
                        <p class="text-xs text-aromas-highlight font-bold" x-text="detailsData.status_name"></p>
                    </div>
                    <button @click="showDetailsModal = false" class="p-2 bg-white/5 hover:bg-white/10 rounded-full text-gray-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>

                <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- Información --}}
                    <div class="space-y-6">
                        <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                            <label class="block text-[10px] font-black text-aromas-highlight uppercase mb-1">Cliente / Propietario</label>
                            <p class="text-xl font-bold text-white" x-text="detailsData.client_name || 'No asignado'"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1">Total Piezas</label>
                                <p class="text-lg font-bold text-white" x-text="detailsData.pieces"></p>
                            </div>
                            <div class="bg-black/20 p-4 rounded-xl border border-white/5">
                                <label class="block text-[10px] font-black text-gray-500 uppercase mb-1">Área Origen</label>
                                <p class="text-lg font-bold text-white" x-text="detailsData.department"></p>
                            </div>
                        </div>
                        <div class="bg-black/20 p-4 rounded-xl border border-white/5 flex flex-col h-48">
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

                    {{-- Evidencias --}}
                    <div class="space-y-6">
                        <label class="block text-[10px] font-black text-aromas-highlight uppercase tracking-widest text-center">Comparativa de Evidencias</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="text-center">
                                <span class="text-[9px] text-gray-500 font-bold uppercase mb-2 block">Foto Gerencia (Captura)</span>
                                <div class="aspect-square bg-black/40 rounded-lg border border-white/5 overflow-hidden">
                                    <template x-if="detailsData.initial_evidence_url">
                                        <img :src="detailsData.initial_evidence_url" class="w-full h-full object-contain p-2">
                                    </template>
                                </div>
                            </div>
                            <div class="text-center flex flex-col">
                                <span class="text-[9px] text-gray-500 font-bold uppercase mb-2 block" x-text="detailsData.evidence_url ? 'Foto Checador (Entrega)' : 'Foto Checador (Recepción)'"></span>
                                <div class="flex-1 bg-black/40 rounded-lg border border-white/5 overflow-hidden flex items-center justify-center p-2 min-h-[200px]">
                                    <template x-if="detailsData.evidence_url">
                                        <img :src="detailsData.evidence_url" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!detailsData.evidence_url && detailsData.package_evidence_url">
                                        <img :src="detailsData.package_evidence_url" class="w-full h-full object-contain">
                                    </template>
                                    <template x-if="!detailsData.evidence_url && !detailsData.package_evidence_url">
                                        <span class="text-xs text-gray-500 font-bold italic">Pendiente...</span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. MODAL ELIMINAR --}}
        <div x-show="showDeleteModal" style="display: none;" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/90 p-4">
            <div class="bg-aromas-secondary border border-red-500/30 rounded-xl p-8 max-w-sm w-full text-center shadow-2xl">
                <div class="w-20 h-20 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-500/20">
                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-white mb-2">¿Eliminar Resguardo?</h3>
                <p class="text-gray-400 text-sm mb-8">Estás a punto de borrar el folio <span class="text-white font-bold" x-text="deleteFolio"></span>. Esta acción no se puede deshacer.</p>
                <form :action="'{{ url('/gerencia/destroy') }}/' + deleteId" method="POST" class="flex gap-3">
                    @csrf @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="flex-1 px-4 py-3 bg-gray-700 hover:bg-gray-600 text-white rounded-lg font-bold transition-colors">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-500 text-white rounded-lg font-bold transition-colors shadow-lg shadow-red-600/20">Eliminar</button>
                </form>
            </div>
        </div>

        {{-- MODAL FASE 3: AUDITORÍA DE GERENCIA --}}
        <div x-show="showAuditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4" x-transition>
            <div @click.away="showAuditModal = false" class="bg-aromas-secondary rounded-2xl shadow-2xl border-2 border-amber-500/50 w-full max-w-5xl max-h-[95vh] overflow-y-auto flex flex-col relative">

                <div class="sticky top-0 p-6 border-b border-aromas-tertiary/30 flex justify-between items-center bg-aromas-secondary/95 backdrop-blur-md z-10">
                    <div>
                        <h3 class="text-2xl font-black text-white uppercase tracking-tighter flex items-center gap-2">
                            <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Auditoría de Resguardo <span x-text="'#' + auditData.ticket_folio" class="text-amber-500 ml-2"></span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-1 font-bold">Revisa la evidencia antes de mandar el paquete a custodia final.</p>
                    </div>
                    <button @click="showAuditModal = false" class="p-2 bg-white/5 hover:bg-white/10 rounded-full text-gray-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>

                <div class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                    {{-- Columna 1: Información --}}
                    <div class="space-y-6">
                        <div class="bg-gray-900 p-5 rounded-xl border border-gray-700">
                            <label class="block text-[10px] font-black text-amber-500 uppercase tracking-widest mb-1">Cliente Destino</label>
                            <p class="text-2xl font-bold text-white" x-text="auditData.client_name"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-900 p-4 rounded-xl border border-gray-700">
                                <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Área Origen</label>
                                <p class="text-lg font-bold text-white" x-text="auditData.department"></p>
                            </div>
                            <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 flex justify-between items-center">
                                <div>
                                    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Piezas (Tú)</label>
                                    <p class="text-xl font-bold text-white" x-text="auditData.pieces"></p>
                                </div>
                                <div class="text-right">
                                    <label class="block text-[10px] font-black text-sky-400 uppercase tracking-widest mb-1">Bolsas (Checador)</label>
                                    <p class="text-xl font-black text-sky-400" x-text="auditData.bags"></p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 flex flex-col h-48">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 border-b border-gray-700 pb-2">Historial de Observaciones</label>

                            <div class="flex-1 overflow-y-auto space-y-3 pr-2 custom-scrollbar">
                                <template x-if="auditData.parsedNotes && auditData.parsedNotes.length === 0">
                                    <div class="text-center text-gray-500 text-xs italic mt-4">Sin observaciones registradas.</div>
                                </template>

                                <template x-for="note in auditData.parsedNotes" :key="note.id">
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

                        {{-- Formulario para Rechazar (Requiere notas de corrección) --}}
                        <div class="bg-red-500/10 border border-red-500/30 p-5 rounded-xl mt-4">
                            <form :action="'/gerencia/pickups/' + auditData.id + '/reject'" method="POST" class="space-y-3">
                                @csrf
                                <label class="block text-[11px] font-black text-red-400 uppercase tracking-widest">¿Notaste alguna anomalía?</label>
                                <textarea name="correction_notes" rows="2" required placeholder="Escribe aquí qué debe corregir el checador..." class="w-full bg-gray-900 border border-red-500/50 rounded-lg px-4 py-2 text-white focus:ring-red-500 text-sm"></textarea>
                                <button type="submit" class="w-full py-2.5 bg-red-600 hover:bg-red-500 text-white font-bold rounded-lg text-sm uppercase tracking-widest transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Rechazar y pedir corrección
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Columna 2: Evidencias Fotográficas --}}
                    <div class="space-y-4">
                        <label class="block text-[12px] font-black text-amber-500 uppercase tracking-widest text-center mb-2">Comparativa de Evidencias</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 h-full">
                            <div class="flex flex-col">
                                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-2 text-center">Tú captura (Pre-Registro)</span>
                                <div class="flex-1 bg-black/50 rounded-xl border border-gray-700 overflow-hidden flex items-center justify-center p-2 min-h-[250px]">
                                    <template x-if="auditData.initial_evidence_url">
                                        <img :src="auditData.initial_evidence_url" class="w-full h-full object-contain">
                                    </template>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <span class="text-[10px] text-sky-400 font-bold uppercase tracking-widest mb-2 text-center">Captura del Checador</span>
                                <div class="flex-1 bg-black/50 rounded-xl border border-sky-500/30 overflow-hidden flex items-center justify-center p-2 min-h-[250px] shadow-[0_0_15px_rgba(14,165,233,0.15)]">
                                    <template x-if="auditData.package_evidence_url">
                                        <img :src="auditData.package_evidence_url" class="w-full h-full object-contain">
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Formulario para Aprobar --}}
                        <div class="pt-4">
                            <form :action="'/gerencia/pickups/' + auditData.id + '/approve'" method="POST">
                                @csrf
                                <button type="submit" class="w-full py-4 bg-green-600 hover:bg-green-500 text-white font-black rounded-xl text-lg uppercase tracking-widest shadow-[0_0_20px_rgba(22,163,74,0.3)] transition-transform active:scale-95 flex items-center justify-center gap-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Aprobar Resguardo
                                </button>
                                <p class="text-center text-gray-500 text-xs mt-2 font-bold">Al aprobar, el paquete pasará oficialmente a "En Custodia" y desaparecerá de esta lista.</p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-gerencia-layout>