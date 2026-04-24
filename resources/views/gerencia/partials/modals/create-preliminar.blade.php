<div x-data="{ photoPreview: null }">
    <div x-show="showCreateModal" style="display: none;" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/80 backdrop-blur-sm p-0 sm:p-4">
        <div @click.away="showCreateModal = false" class="bg-aromas-secondary rounded-t-3xl sm:rounded-2xl shadow-2xl border-t border-aromas-tertiary/30 w-full max-w-lg overflow-y-auto max-h-[90vh]" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 opacity-0" x-transition:enter-end="translate-y-0 sm:scale-100 opacity-100">
            
            <div class="sticky top-0 p-5 border-b border-aromas-tertiary/30 flex justify-between items-center bg-aromas-secondary/95 backdrop-blur-sm z-10">
                <h3 class="text-xl font-black text-white uppercase tracking-wider">Check-In Express</h3>
                <button type="button" @click="showCreateModal = false" class="bg-gray-800 p-2 rounded-full text-gray-400 hover:text-white transition-colors"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
            
            <form action="{{ route('gerencia.pickups.storePreliminar') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-6">
                @csrf
                
                {{-- Folio Gigante --}}
                <div>
                    <label class="block text-xs font-black text-aromas-tertiary uppercase tracking-widest mb-2">Folio de Ticket <span class="text-red-500">*</span></label>
                    <input type="text" name="ticket_folio" required placeholder="0000" class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-xl px-5 py-4 text-white text-2xl font-mono text-center focus:ring-aromas-highlight focus:border-aromas-highlight shadow-inner">
                </div>

                {{-- Área --}}
                <div>
                    <label class="block text-xs font-black text-aromas-tertiary uppercase tracking-widest mb-2">Área de Origen <span class="text-red-500">*</span></label>
                    <select name="department" required class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-xl px-5 py-4 text-white text-lg focus:ring-aromas-highlight focus:border-aromas-highlight">
                        <option value="AROMAS">Aromas</option>
                        <option value="BELLAROMA">Bellaroma</option>
                        <option value="CALLCENTER">Call Center</option>
                    </select>
                </div>

                {{-- Cámara Gigante --}}
                <div>
                    <label class="block text-xs font-black text-aromas-tertiary uppercase tracking-widest mb-2 flex justify-between">
                        <span>Evidencia Inicial</span>
                        <span class="text-gray-500">(Opcional)</span>
                    </label>
                    <div class="relative flex flex-col items-center justify-center w-full h-40 border-2 border-aromas-highlight/50 border-dashed rounded-2xl cursor-pointer bg-aromas-highlight/5 hover:bg-aromas-highlight/10 transition-all overflow-hidden group shadow-inner" @click="$refs.fileInput.click()">
                        
                        <div class="flex flex-col items-center justify-center" x-show="!photoPreview">
                            <div class="bg-aromas-highlight text-black p-4 rounded-full mb-3 shadow-lg group-hover:scale-110 transition-transform">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <p class="text-sm text-aromas-highlight font-bold">Tomar Foto (Abrir Cámara)</p>
                        </div>

                        <template x-if="photoPreview">
                            <img :src="photoPreview" class="absolute inset-0 w-full h-full object-contain bg-black/60 p-2">
                        </template>

                        <input x-ref="fileInput" name="initial_evidence" type="file" accept="image/*" capture="environment" class="hidden"
                               @change="const file = $refs.fileInput.files[0]; if(file) { const reader = new FileReader(); reader.onload = (e) => { photoPreview = e.target.result; }; reader.readAsDataURL(file); }">
                    </div>
                </div>

                {{-- Notas Opcionales --}}
                <div>
                    <label class="block text-xs font-black text-aromas-tertiary uppercase tracking-widest mb-2">Observaciones</label>
                    <textarea name="notes" rows="2" placeholder="Solo si es estrictamente necesario..." class="w-full bg-black/40 border border-aromas-tertiary/50 rounded-xl px-5 py-3 text-white focus:ring-aromas-highlight"></textarea>
                </div>

                <div class="pt-2 pb-4">
                    <button type="submit" class="w-full bg-aromas-highlight hover:bg-yellow-500 text-black font-black py-5 rounded-xl shadow-[0_0_20px_rgba(253,201,116,0.3)] transition-transform active:scale-95 text-lg uppercase tracking-widest flex justify-center items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Registrar Paquete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>