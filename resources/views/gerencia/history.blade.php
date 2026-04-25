<x-gerencia-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Historial de Resguardos</h1>
        <p class="text-aromas-tertiary text-sm">Consulta el archivo histórico de paquetería.</p>
    </div>

    {{-- CONTEXTO ALPINE PARA HISTORIAL --}}
    <div x-data="{
        search: '{{ request('search') }}',
        status: '{{ request('status', 'ALL') }}',
        department: '{{ request('department', 'ALL') }}',
        date_start: '{{ request('date_start') }}',
        date_end: '{{ request('date_end') }}',
        isLoading: false,
        showImageViewer: false,
        imageViewerUrl: '',
        
        openImageViewer(url) {
            if(!url) return;
            this.imageViewerUrl = url;
            this.showImageViewer = true;
        },
        
        // MODAL DE DETALLES
        showDetailsModal: false,
        detailsData: { 
            ticket_folio: '', client_name: '', receiver_name: '', is_third_party: false, 
            delivered_at: '', signature_url: '', notes: '', 
            evidence_url: '', initial_evidence_url: '', package_evidence_url: '' 
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

        async fetchResults(url = null) {
            this.isLoading = true;
            if (!url) {
                const params = new URLSearchParams({ search: this.search, status: this.status, department: this.department, date_start: this.date_start, date_end: this.date_end });
                url = `{{ route('gerencia.history') }}?${params.toString()}`;
            }
            try {
                const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                const html = await response.text();
                document.getElementById('history-table-container').innerHTML = html;
            } catch (error) { console.error(error); } finally { this.isLoading = false; }
        }
    }">

        {{-- BARRA DE FILTROS --}}
        <div class="bg-aromas-secondary p-4 rounded-xl shadow-lg border border-aromas-tertiary/20 mb-6">
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full"><label class="text-xs text-aromas-tertiary mb-1 block">Buscar</label><input type="text" x-model.debounce.500ms="search" @input="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white w-full" placeholder="Folio, Cliente..."></div>
                <div class="w-full md:w-auto"><label class="text-xs text-aromas-tertiary mb-1 block">Estado</label><select x-model="status" @change="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white w-full">
                        <option value="ALL">Todos</option>
                        <option value="IN_STORE">En Tienda</option>
                        <option value="IN_CUSTODY">En Custodia</option>
                        <option value="DELIVERED">Entregados</option>
                    </select></div>
                <div class="w-full md:w-auto"><label class="text-xs text-aromas-tertiary mb-1 block">Depto</label><select x-model="department" @change="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white w-full">
                        <option value="ALL">Todos</option>
                        <option value="AROMAS">Aromas</option>
                        <option value="BELLAROMA">Bellaroma</option>
                        <option value="CALLCENTER">Call Center</option>
                    </select></div>
                <div class="flex gap-2 w-full md:w-auto">
                    <div class="flex-1"><label class="text-xs text-aromas-tertiary mb-1 block">Desde</label><input type="date" x-model="date_start" @change="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-2 py-2 text-white w-full"></div>
                    <div class="flex-1"><label class="text-xs text-aromas-tertiary mb-1 block">Hasta</label><input type="date" x-model="date_end" @change="fetchResults()" class="bg-black/20 border border-aromas-tertiary/30 rounded-lg px-2 py-2 text-white w-full"></div>
                </div>
                <button x-show="search || status !== 'ALL' || department !== 'ALL' || date_start || date_end" @click="search=''; status='ALL'; department='ALL'; date_start=''; date_end=''; fetchResults()" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-full transition-colors" title="Limpiar Filtros"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg></button>
            </div>
        </div>

        {{-- CONTENEDOR TABLA --}}
        <div id="history-table-container">
            @include('gerencia.partials.history-table', ['pickups' => $pickups])
        </div>

        {{-- MODAL DE DETALLES --}}
        <div x-show="showDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" x-transition>
            <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="showDetailsModal = false"></div>

            <div class="bg-aromas-secondary w-full max-w-2xl rounded-xl shadow-2xl border border-aromas-tertiary/30 relative z-10 flex flex-col overflow-hidden">
                <div class="bg-black/20 p-5 border-b border-aromas-tertiary/20 flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-white flex items-center gap-2">
                            <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Detalles de Entrega
                        </h2>
                        <p class="text-sm text-aromas-tertiary">Folio: <span class="text-aromas-highlight font-mono" x-text="detailsData.ticket_folio"></span></p>
                    </div>
                    <button @click="showDetailsModal = false" class="text-gray-500 hover:text-white p-2"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg></button>
                </div>

                <div class="p-6 overflow-y-auto max-h-[80vh]">
                    <div class="bg-black/20 p-4 rounded-xl border border-aromas-tertiary/10 mb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-1">Entregado el:</label>
                                <p class="text-white font-mono" x-text="detailsData.delivered_at"></p>
                            </div>
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

                    {{-- Notas Historial (Chat) --}}
                    <div class="bg-black/20 p-4 rounded-xl border border-aromas-tertiary/10 mb-4 flex flex-col h-48">
                        <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-3 border-b border-gray-700 pb-2">Historial de Observaciones</label>
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

                    {{-- Firmas y Evidencias --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-aromas-tertiary uppercase tracking-wider font-bold mb-2">Firma del Cliente</label>
                            <div class="bg-white rounded-lg p-2 border-2 border-gray-300 flex items-center justify-center min-h-[12rem]">
                                <template x-if="detailsData.signature_url">
                                    <img :src="detailsData.signature_url" alt="Firma Digital" class="w-full h-auto object-contain max-h-48 cursor-crosshair hover:scale-105 transition-transform" @mouseenter="openImageViewer(detailsData.signature_url)" @mouseleave="showImageViewer = false">
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
                                    <img :src="detailsData.evidence_url" alt="Evidencia" class="w-full h-auto object-contain max-h-48 cursor-crosshair hover:scale-105 transition-transform" @mouseenter="openImageViewer(detailsData.evidence_url)" @mouseleave="showImageViewer = false">
                                </template>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="p-4 bg-black/20 border-t border-aromas-tertiary/20 flex justify-end"><button @click="showDetailsModal = false" class="px-6 py-2 bg-aromas-tertiary/20 border border-aromas-tertiary/30 rounded-lg text-white hover:bg-white/10 transition-colors">Cerrar</button></div>
            </div>
        </div>

        {{-- VISOR DE IMÁGENES MOVIDO AQUÍ DENTRO DEL X-DATA --}}
        <div x-show="showImageViewer" style="display: none;" class="fixed inset-0 z-[150] flex items-center justify-center pointer-events-none p-4 md:p-10" x-transition>
            <div class="relative w-full h-full flex items-center justify-center bg-black/95 p-2 rounded-2xl shadow-[0_0_50px_rgba(0,0,0,0.8)] border border-gray-700">
                <img :src="imageViewerUrl" class="max-w-full max-h-full object-contain rounded-xl bg-white p-2">
            </div>
        </div>
        
    </div>
</x-gerencia-layout>