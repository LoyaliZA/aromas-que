<x-gerencia-layout>
    <div x-data="{ 
            showEditModal: false, 
            showDetailsModal: false, 
            
            editData: { 
                id: 0, ticket_folio: '', ticket_date: '', client_ref_id: '', client_name: '', 
                department: 'AROMAS', pieces: 1, notes: '', is_third_party: false, receiver_name: '' 
            },

            detailsData: {
                ticket_folio: '', client_name: '', receiver_name: '', is_third_party: false,
                delivered_at: '', signature_url: '', notes: '', evidence_url: ''
            },

            search: '', status: 'ALL', department: 'ALL', isLoading: false,

            openEditModal(data) {
                data.is_third_party = Boolean(data.is_third_party);
                this.editData = data;
                this.showEditModal = true;
            },

            openDetailsModal(data) {
                this.detailsData = data;
                this.showDetailsModal = true;
            },

            async fetchResults() {
                this.isLoading = true;
                const params = new URLSearchParams({ search: this.search, status: this.status, department: this.department });
                const url = `{{ route('gerencia.daily') }}?${params.toString()}`;
                try {
                    const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    document.getElementById('table-container').innerHTML = await response.text();
                } catch (error) { console.error(error); } finally { this.isLoading = false; }
            }
         }">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Recepción de Resguardos PDV</h1>
                <p class="text-aromas-tertiary text-sm">Auditoría y traslado de paquetes hacia el área de entregas.</p>
            </div>
        </div>

        <div class="bg-aromas-secondary rounded-xl shadow-lg p-3 border border-aromas-tertiary/20 mb-2">
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="relative w-full md:flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"><svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg></div>
                    <input type="text" x-model="search" @input.debounce.500ms="fetchResults()" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg pl-9 pr-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight placeholder-gray-500 transition-all" placeholder="Buscar por folio o cliente...">
                    <div x-show="isLoading" class="absolute right-3 top-2.5"><svg class="animate-spin h-4 w-4 text-aromas-highlight" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>
                </div>
                <div class="hidden md:block w-px h-8 bg-aromas-tertiary/20"></div>
                <div class="w-full md:w-auto"><select x-model="department" @change="fetchResults()" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight cursor-pointer"><option value="ALL">Área: Todas</option><option value="AROMAS">Aromas</option><option value="BELLAROMA">Bellaroma</option><option value="CALLCENTER">Call Center</option></select></div>
                <div class="w-full md:w-auto"><select x-model="status" @change="fetchResults()" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight cursor-pointer"><option value="ALL">Estado: Todos</option><option value="IN_STORE">🏪 En Tienda (Recibidos)</option><option value="IN_CUSTODY">📦 En Custodia (Checador)</option><option value="DELIVERED">✅ Entregados</option></select></div>
                <button x-show="search || status !== 'ALL' || department !== 'ALL'" @click="search=''; status='ALL'; department='ALL'; fetchResults()" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-full transition-colors" title="Limpiar"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
        </div>

        <div id="table-container" class="transition-opacity duration-200" :class="isLoading ? 'opacity-50' : 'opacity-100'">
            @include('gerencia.partials.daily-table')
        </div>

        {{-- Modales de Edición y Detalles (se mantienen igual, oculto el código extra por brevedad, no los borres de tu archivo original si ya los tenías armados, solo borramos el de Create) --}}
    </div>
</x-gerencia-layout>