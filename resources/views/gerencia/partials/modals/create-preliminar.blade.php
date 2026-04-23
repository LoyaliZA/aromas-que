<div x-data="{ 
        photoPreview: null,
        
        // Lógica de búsqueda de folio complementario
        folioSearchQuery: '',
        folioSearchResults: [],
        showFolioDropdown: false,
        
        async searchFolios() {
            if (this.folioSearchQuery.length < 2) {
                this.folioSearchResults = [];
                return;
            }
            try {
                const response = await fetch(`/gerencia/pickups/search-folio?q=${this.folioSearchQuery}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                this.folioSearchResults = await response.json();
                this.showFolioDropdown = true;
            } catch (e) { console.error(e); }
        },
        
        selectFolio(pickup) {
            this.folioSearchQuery = pickup.ticket_folio;
            this.showFolioDropdown = false;
        }
    }">
    
    <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm p-4">
        <div @click.away="showCreateModal = false" class="bg-aromas-secondary rounded-xl shadow-2xl border border-aromas-tertiary/30 w-full max-w-2xl overflow-y-auto max-h-[90vh]" x-transition>
            <div class="sticky top-0 p-6 border-b border-aromas-tertiary/30 flex justify-between items-center bg-aromas-secondary/95 backdrop-blur-sm z-10">
                <h3 class="text-xl font-bold text-white uppercase tracking-wider">Registro de Resguardo</h3>
                <button @click="showCreateModal = false" class="text-gray-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            
            <form action="{{ route('gerencia.pickups.storePreliminar') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1">Folio (Ticket) <span class="text-red-500">*</span></label>
                        <input type="text" name="ticket_folio" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1">Área Origen <span class="text-red-500">*</span></label>
                        <select name="department" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight">
                            <option value="AROMAS">Aromas</option>
                            <option value="BELLAROMA">Bellaroma</option>
                            <option value="CALLCENTER">Call Center</option>
                        </select>
                    </div>
                </div>

                {{-- SECCIÓN COMPLEMENTARIO CON BUSCADOR --}}
                <div class="bg-black/20 p-5 rounded-lg border border-aromas-tertiary/20">
                    <label class="flex items-center cursor-pointer mb-3">
                        <input type="checkbox" name="is_complementary" value="1" x-model="isComplementary" class="w-5 h-5 rounded border-aromas-tertiary/50 text-aromas-highlight focus:ring-aromas-highlight bg-black/40">
                        <span class="ml-3 text-sm text-gray-200 font-bold uppercase tracking-wider">Es un resguardo complementario (- C)</span>
                    </label>
                    
                    <div x-show="isComplementary" x-collapse class="relative">
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Buscar Folio Original</label>
                        <input type="text" name="parent_folio" x-model="folioSearchQuery" @input.debounce.300ms="searchFolios" @focus="showFolioDropdown = true" @click.away="showFolioDropdown = false" autocomplete="off" placeholder="Escribe el folio..." class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-2 text-white focus:ring-aromas-highlight text-sm">
                        
                        <div x-show="showFolioDropdown && folioSearchResults.length > 0" style="display: none;" class="absolute z-50 w-full mt-1 bg-gray-800 border border-gray-600 rounded-lg shadow-2xl max-h-48 overflow-y-auto">
                            <template x-for="pickup in folioSearchResults" :key="pickup.id">
                                <div @click="selectFolio(pickup)" class="px-4 py-3 hover:bg-aromas-highlight/20 cursor-pointer border-b border-gray-700 text-white transition-colors flex justify-between items-center">
                                    <div class="font-bold font-mono text-aromas-highlight" x-text="pickup.ticket_folio"></div>
                                    <div class="text-xs text-gray-300" x-text="pickup.client_name"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1">Cantidad Piezas <span class="text-red-500">*</span></label>
                        <input type="number" name="pieces" required min="1" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight">
                    </div>
                    
                    {{-- FOTO AHORA ES OPCIONAL Y USA OBJECT-CONTAIN --}}
                    <div>
                        <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1 flex justify-between">
                            <span>Evidencia Inicial</span>
                            <span class="text-gray-500 text-xs">(Opcional)</span>
                        </label>
                        <div class="relative flex flex-col items-center justify-center w-full h-32 border-2 border-aromas-tertiary/50 border-dashed rounded-lg cursor-pointer bg-black/40 hover:bg-black/60 transition-all overflow-hidden group" @click="$refs.fileInput.click()">
                            
                            <div class="flex flex-col items-center justify-center pt-5 pb-6" x-show="!photoPreview">
                                <svg class="w-8 h-8 mb-2 text-gray-500 group-hover:text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <p class="text-xs text-gray-400 font-bold">Subir foto de piezas</p>
                            </div>

                            <template x-if="photoPreview">
                                <img :src="photoPreview" class="absolute inset-0 w-full h-full object-contain p-1">
                            </template>

                            <input x-ref="fileInput" name="initial_evidence" type="file" accept="image/*" capture="environment" class="hidden"
                                   @change="const file = $refs.fileInput.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-300 uppercase tracking-widest mb-1">Observaciones</label>
                    <textarea name="notes" rows="2" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight"></textarea>
                </div>

                <div class="flex justify-end pt-4 border-t border-aromas-tertiary/30">
                    <button type="button" @click="showCreateModal = false" class="mr-3 px-6 py-3 text-gray-400 hover:text-white font-bold transition-colors uppercase">Cancelar</button>
                    <button type="submit" class="bg-aromas-highlight hover:bg-yellow-600 text-aromas-main font-black py-3 px-8 rounded-lg shadow-lg transition-all uppercase tracking-widest">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>