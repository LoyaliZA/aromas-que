{{-- Modal de entrega con firma (Operación Diaria, Rezagados, etc.) --}}
<div x-data="gerenciaPickupDelivery()"
    @open-gerencia-delivery.window="openModal($event.detail)"
    x-cloak>

    <div x-show="showDeliveryModal" style="display: none;"
        class="fixed inset-0 z-[120] flex items-center justify-center p-4 overflow-y-auto">
        <div class="fixed inset-0 bg-black/90 backdrop-blur-sm" @click="closeModal()"></div>

        <div class="bg-aromas-secondary w-full max-w-5xl rounded-2xl shadow-2xl border border-aromas-tertiary/30 relative z-10 flex flex-col my-auto max-h-[95vh] overflow-y-auto">
            <div class="bg-black/30 p-6 border-b border-aromas-tertiary/30 flex justify-between items-center sticky top-0 z-20">
                <div>
                    <h2 class="text-2xl font-black text-white tracking-wider uppercase">Confirmar Entrega</h2>
                    <p class="text-sm text-gray-400 mt-1">
                        Folio: <span class="font-mono text-aromas-highlight font-bold" x-text="pickup.ticket_folio"></span>
                    </p>
                </div>
                <button type="button" @click="closeModal()" class="text-gray-400 hover:text-white p-2 rounded-full hover:bg-white/10">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="gerenciaDeliveryForm" method="POST" enctype="multipart/form-data"
                :action="'/gerencia/pickups/' + pickup.id + '/deliver'"
                @submit.prevent="submitDelivery" class="p-6 md:p-8 space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="signature" x-model="signatureData">
                <input type="hidden" name="redirect_to" x-model="redirectTo">
                <input type="hidden" name="customer_id" x-model="selectedCustomerId">

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="space-y-4 bg-black/20 border border-aromas-tertiary/20 p-5 rounded-xl">
                        <h3 class="text-sm font-black text-aromas-highlight uppercase tracking-widest border-b border-gray-700 pb-2">Quién recibe</h3>

                        <label class="flex items-center gap-3 p-4 rounded-xl cursor-pointer transition-colors"
                            :class="isThirdParty ? 'bg-aromas-highlight/10 border border-aromas-highlight/30' : 'bg-gray-900 border border-gray-700'">
                            <input type="checkbox" name="is_third_party" value="1" x-model="isThirdParty"
                                @change="if(isThirdParty) { selectedCustomerId = ''; } else { clientSearchQuery = pickup.client_name || ''; }"
                                class="w-6 h-6 rounded border-gray-500 text-aromas-highlight focus:ring-aromas-highlight bg-gray-900">
                            <div>
                                <span class="block font-bold text-white">Recibe un tercero</span>
                                <span class="block text-xs text-gray-400">No es el titular del resguardo</span>
                            </div>
                        </label>

                        <div x-show="isThirdParty" x-transition>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Nombre de quien recibe *</label>
                            <input type="text" name="receiver_name" x-model="receiverName" required
                                placeholder="Nombre completo — INE"
                                class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight">
                        </div>

                        <div x-show="!isThirdParty" x-transition class="space-y-3">
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Titular (base de clientes)</label>
                            <input type="text" name="receiver_name" x-model="receiverName"
                                @input.debounce.300ms="clientSearchQuery = receiverName; searchCustomers()"
                                @focus="clientSearchQuery = receiverName; searchCustomers()"
                                @click.away="showClientDropdown = false"
                                autocomplete="off"
                                placeholder="Buscar por nombre o número de cliente..."
                                class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-3 text-white focus:ring-aromas-highlight">

                            <div x-show="showClientDropdown && clientSearchResults.length > 0" style="display: none;"
                                class="bg-gray-800 border border-gray-600 rounded-lg shadow-xl max-h-40 overflow-y-auto">
                                <template x-for="customer in clientSearchResults" :key="customer.id">
                                    <button type="button" @click="selectCustomer(customer)"
                                        class="w-full text-left px-4 py-2 hover:bg-aromas-highlight/20 border-b border-gray-700 text-sm text-white">
                                        <span class="font-bold" x-text="customer.name"></span>
                                        <span class="text-gray-400 text-xs" x-text="customer.customer_number ? ' #' + customer.customer_number : ''"></span>
                                    </button>
                                </template>
                            </div>

                            <p class="text-xs text-gray-500">Registrado en resguardo: <span class="text-white font-bold" x-text="pickup.client_name"></span></p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="bg-black/20 border border-dashed border-gray-600 rounded-xl p-5">
                            <label class="block text-xs font-black text-aromas-highlight uppercase tracking-widest mb-3">Evidencia fotográfica *</label>
                            <input type="file" name="evidence_file" id="gerencia_evidence_file" accept="image/*" capture="environment" class="sr-only"
                                @change="handleEvidenceChange($event)">
                            <label for="gerencia_evidence_file" x-show="!evidencePreview"
                                class="flex flex-col items-center justify-center h-28 bg-gray-900 rounded-lg border border-gray-700 cursor-pointer hover:border-aromas-highlight transition-colors">
                                <span class="text-sm font-bold text-white">Tocar para tomar foto</span>
                            </label>
                            <div x-show="evidencePreview" class="relative h-32 rounded-lg overflow-hidden border-2 border-aromas-highlight">
                                <img :src="evidencePreview" class="w-full h-full object-cover" alt="Evidencia">
                                <button type="button" @click="removeEvidence()" class="absolute top-2 right-2 bg-red-600 text-white p-1.5 rounded-lg text-xs font-bold">Quitar</button>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Observaciones</label>
                            <textarea name="notes" rows="2" class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-white resize-none"
                                placeholder="Estado del paquete, identificación presentada..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-900/80 border border-gray-700 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-black text-aromas-highlight uppercase tracking-widest">Firma digital *</span>
                        <button type="button" @click="Alpine.store('firmaStore').isFullScreen = true; setTimeout(() => initPad(), 200)"
                            class="text-xs font-black bg-aromas-highlight text-black px-4 py-2 rounded-lg uppercase">Firmar ahora</button>
                    </div>
                    <div class="bg-white rounded-xl h-36 flex items-center justify-center overflow-hidden">
                        <template x-if="Alpine.store('firmaStore').preview">
                            <img :src="Alpine.store('firmaStore').preview" class="max-h-full max-w-full object-contain p-2" alt="Firma">
                        </template>
                        <template x-if="!Alpine.store('firmaStore').preview">
                            <span class="text-gray-400 text-xs uppercase font-bold">Sin firma capturada</span>
                        </template>
                    </div>
                </div>

                <div class="flex gap-3 pt-2 border-t border-gray-700">
                    <button type="button" @click="closeModal()" class="flex-1 py-3 text-gray-400 font-bold uppercase text-xs hover:text-white">Cancelar</button>
                    <button type="submit" class="flex-1 py-3 bg-green-600 hover:bg-green-500 text-white font-black rounded-lg uppercase text-xs tracking-widest shadow-lg">
                        Confirmar entrega
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Canvas firma pantalla completa --}}
    <div :class="Alpine.store('firmaStore').isFullScreen ? 'opacity-100 z-[200] pointer-events-auto' : 'opacity-0 -z-50 pointer-events-none'"
        class="fixed inset-0 bg-gray-200 flex flex-col transition-opacity">
        <div class="bg-gray-900 p-4 flex justify-between items-center">
            <h3 class="text-lg font-black text-white uppercase">Firma del cliente</h3>
            <div class="flex gap-2">
                <button type="button" @click="clearPad()" class="px-4 py-2 bg-red-500/20 text-red-400 border border-red-500/50 rounded-lg text-xs font-bold uppercase">Borrar</button>
                <button type="button" @click="confirmSignature()" class="px-4 py-2 bg-green-600 text-white rounded-lg text-xs font-bold uppercase">Confirmar firma</button>
                <button type="button" @click="Alpine.store('firmaStore').isFullScreen = false" class="p-2 text-gray-400 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>
        <div class="flex-1 bg-white touch-none">
            <canvas x-ref="signature_canvas" class="w-full h-full"></canvas>
        </div>
    </div>
</div>
