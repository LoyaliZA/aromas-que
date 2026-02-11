<x-gerencia-layout>
    
    {{-- CONTENEDOR PRINCIPAL: Controla Modales Y Búsqueda --}}
    <div x-data="{ 
            // Estado de Modales
            showCreateModal: false, 
            showEditModal: false, 
            editData: { id: 0, ticket_folio: '', client_name: '', department: 'AROMAS', pieces: 1 },

            // Estado de Filtros
            search: '',
            status: 'ALL',
            department: 'ALL',
            isLoading: false,

            // Funciones de Modal
            openEditModal(data) {
                this.editData = data;
                this.showEditModal = true;
            },

            // Función de Búsqueda en Vivo (AJAX)
            async fetchResults() {
                this.isLoading = true;
                const params = new URLSearchParams({
                    search: this.search,
                    status: this.status,
                    department: this.department
                });
                const url = `{{ route('gerencia.daily') }}?${params.toString()}`;

                try {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const html = await response.text();
                    document.getElementById('daily-table-container').innerHTML = html;
                } catch (error) {
                    console.error('Error filtrando:', error);
                } finally {
                    this.isLoading = false;
                }
            }
         }">
        
        {{-- ENCABEZADO Y ACCIONES --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white">Operación Diaria</h1>
                <p class="text-aromas-tertiary text-sm">Registro y gestión de resguardos del día.</p>
            </div>

            <button @click="showCreateModal = true" 
                    class="bg-aromas-highlight hover:bg-white text-aromas-main font-bold py-2 px-6 rounded-lg transition-all shadow-lg flex items-center transform hover:scale-105 active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Nuevo Resguardo
            </button>
        </div>

        {{-- BARRA DE FILTROS (NUEVA) --}}
        <div class="bg-aromas-secondary rounded-xl shadow-lg p-3 border border-aromas-tertiary/20 mb-2">
            <div class="flex flex-col md:flex-row items-center gap-3">
                
                {{-- Buscador --}}
                <div class="relative w-full md:flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" x-model="search" @input.debounce.500ms="fetchResults()"
                        class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg pl-9 pr-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight placeholder-gray-500 transition-all"
                        placeholder="Buscar folio o cliente en los pedidos de hoy...">
                    
                    {{-- Spinner --}}
                    <div x-show="isLoading" class="absolute right-3 top-2.5">
                         <svg class="animate-spin h-4 w-4 text-aromas-highlight" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>

                <div class="hidden md:block w-px h-8 bg-aromas-tertiary/20"></div>

                {{-- Filtro Departamento --}}
                <div class="w-full md:w-auto">
                    <select x-model="department" @change="fetchResults()"
                        class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight cursor-pointer">
                        <option value="ALL">Área: Todas</option>
                        <option value="AROMAS">🟣 Aromas</option>
                        <option value="BELLAROMA">🌸 Bellaroma</option>
                    </select>
                </div>

                {{-- Filtro Estado --}}
                <div class="w-full md:w-auto">
                    <select x-model="status" @change="fetchResults()"
                        class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white text-sm focus:outline-none focus:border-aromas-highlight cursor-pointer">
                        <option value="ALL">Estado: Todos</option>
                        <option value="IN_CUSTODY">📦 En Custodia</option>
                        <option value="DELIVERED">✅ Entregados</option>
                    </select>
                </div>

                {{-- Botón Limpiar --}}
                <button x-show="search || status !== 'ALL' || department !== 'ALL'" 
                        @click="search=''; status='ALL'; department='ALL'; fetchResults()"
                        class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-full transition-colors" title="Limpiar Filtros">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        </div>

        {{-- CONTENEDOR DE LA TABLA (AJAX) --}}
        <div id="daily-table-container" class="transition-opacity duration-200" :class="isLoading ? 'opacity-50' : 'opacity-100'">
            @include('gerencia.partials.daily-table')
        </div>


        {{-- === MODAL CREAR === --}}
        <div x-show="showCreateModal" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showCreateModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black/80 transition-opacity backdrop-blur-sm" @click="showCreateModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showCreateModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-aromas-secondary rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-aromas-tertiary/20">
                    
                    <div class="px-6 pt-6 pb-4">
                        <h3 class="text-xl font-bold text-white mb-4 border-b border-aromas-tertiary/20 pb-2">Registrar Nuevo Paquete</h3>
                        <form id="createForm" action="{{ route('gerencia.store') }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-aromas-tertiary mb-1">Folio Ticket</label>
                                    <input type="text" name="ticket_folio" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-aromas-tertiary mb-1">Fecha Ticket</label>
                                        <input type="date" name="ticket_date" value="{{ date('Y-m-d') }}" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-aromas-tertiary mb-1">ID Cliente</label>
                                        <input type="text" name="client_ref_id" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-aromas-tertiary mb-1">Nombre Cliente</label>
                                    <input type="text" name="client_name" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-aromas-tertiary mb-1">Departamento</label>
                                        <select name="department" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors">
                                            <option value="AROMAS">Aromas</option>
                                            <option value="BELLAROMA">Bellaroma</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-aromas-tertiary mb-1">Piezas</label>
                                        <input type="number" name="pieces" value="1" min="1" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-colors">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="bg-black/20 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-aromas-tertiary/10">
                        <button type="submit" form="createForm" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-aromas-highlight text-base font-bold text-aromas-main hover:bg-white sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Guardar
                        </button>
                        <button type="button" @click="showCreateModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-aromas-tertiary/30 shadow-sm px-4 py-2 bg-transparent text-base font-medium text-gray-300 hover:text-white hover:bg-white/5 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- === MODAL EDITAR === --}}
        <div x-show="showEditModal" style="display: none;" 
             class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showEditModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-black/80 transition-opacity backdrop-blur-sm" @click="showEditModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showEditModal"
                     x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-aromas-secondary rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-yellow-500/30">
                    
                    <div class="bg-yellow-900/40 p-5 border-b border-yellow-500/20 flex items-start">
                        <div class="p-2 bg-yellow-500/20 rounded-full mr-3 shrink-0">
                            <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-yellow-400">Modo de Edición</h3>
                            <p class="text-xs text-yellow-200/80 mt-1 leading-relaxed">Estás modificando un registro existente. Esta acción será auditada y registrada en el historial de seguridad.</p>
                        </div>
                    </div>

                    <div class="px-6 py-6">
                        <form id="editForm" method="POST" :action="`{{ url('gerencia/update') }}/${editData.id}`">
                            @csrf
                            @method('PUT')
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-aromas-tertiary mb-1">Folio Ticket</label>
                                    <input type="text" name="ticket_folio" x-model="editData.ticket_folio" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-aromas-tertiary mb-1">Nombre Cliente</label>
                                    <input type="text" name="client_name" x-model="editData.client_name" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-aromas-tertiary mb-1">Departamento</label>
                                        <select name="department" x-model="editData.department" class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">
                                            <option value="AROMAS">Aromas</option>
                                            <option value="BELLAROMA">Bellaroma</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-aromas-tertiary mb-1">Piezas</label>
                                        <input type="number" name="pieces" x-model="editData.pieces" min="1" required class="w-full bg-black/20 border border-aromas-tertiary/30 rounded-lg px-3 py-2 text-white focus:border-yellow-500 focus:ring-1 focus:ring-yellow-500 transition-colors">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="bg-black/20 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-yellow-500/10">
                        <button type="submit" form="editForm" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-yellow-600 text-base font-bold text-white hover:bg-yellow-500 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Confirmar Cambio
                        </button>
                        <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-aromas-tertiary/30 shadow-sm px-4 py-2 bg-transparent text-base font-medium text-gray-300 hover:text-white hover:bg-white/5 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-gerencia-layout>