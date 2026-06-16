<x-admin-layout>
    <div x-data="customersApp()" class="pb-10">
        
        {{-- Encabezado --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-3xl font-black text-white tracking-wider uppercase">Gestión de Clientes</h1>
                <p class="text-gray-400 mt-1">Administra la base de datos de clientes y sus niveles de prioridad.</p>
            </div>
            
            <button @click="openImportModal()" class="bg-aromas-highlight text-aromas-main px-6 py-3 rounded-xl font-bold uppercase tracking-widest shadow-[0_0_15px_rgba(253,201,116,0.3)] hover:bg-white transition-all flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Importar CSV
            </button>
        </div>

        {{-- Alertas de Error (Importación CSV) --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-500/10 border-l-4 border-red-500 p-4 rounded-lg">
                <div class="flex items-center gap-2 text-red-400 font-bold mb-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Atención: Errores en la importación
                </div>
                <ul class="list-disc list-inside text-sm text-red-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Barra de Búsqueda y Filtros --}}
        <div class="bg-aromas-secondary border border-aromas-tertiary/20 rounded-xl p-4 mb-6 shadow-lg">
            <form action="{{ route('admin.customers.index') }}" method="GET" class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" class="w-full bg-gray-900 border border-gray-700 rounded-lg py-3 pl-10 pr-4 text-white placeholder-gray-500 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight transition-all" placeholder="Buscar por nombre, número o teléfono...">
                </div>
                
                <div class="md:w-64">
                    <select name="client_type" class="w-full bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-3 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight cursor-pointer" onchange="this.form.submit()">
                        <option value="ALL">Todos los tipos</option>
                        @foreach($clientTypes as $type)
                            <option value="{{ $type->code }}" {{ request('client_type') == $type->code ? 'selected' : '' }}>{{ $type->label }}</option>
                        @endforeach
                    </select>
                </div>
                
                <button type="submit" class="bg-gray-700 text-white px-6 py-3 rounded-lg font-bold hover:bg-gray-600 transition-colors">Buscar</button>
                <a href="{{ route('admin.customers.index') }}" class="flex items-center justify-center px-4 py-3 border border-gray-600 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition-colors" title="Limpiar Filtros">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </a>
            </form>
        </div>

        {{-- Tabla de Clientes --}}
        <div class="bg-aromas-secondary border border-aromas-tertiary/20 rounded-xl shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-900/80 border-b border-gray-700 text-xs uppercase tracking-wider text-gray-400">
                            <th class="p-4 font-bold"># Cliente</th>
                            <th class="p-4 font-bold">Nombre</th>
                            <th class="p-4 font-bold">Contacto</th>
                            <th class="p-4 font-bold text-center">Tipo</th>
                            <th class="p-4 font-bold text-center">Registro</th>
                            <th class="p-4 font-bold text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-gray-800/50 transition-colors">
                                <td class="p-4 font-mono text-white">{{ $customer->customer_number ?? 'S/N' }}</td>
                                <td class="p-4 font-bold text-white">{{ $customer->name }}</td>
                                <td class="p-4 text-sm text-gray-400">
                                    @if($customer->phone) <div class="flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg> {{ $customer->phone }}</div> @endif
                                    @if($customer->email) <div class="flex items-center gap-1 mt-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> {{ $customer->email }}</div> @endif
                                </td>
                                <td class="p-4 text-center">
                                    <span class="bg-gray-800 text-gray-300 border border-gray-700 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider">
                                        {{ $customer->catalogClientType?->displayLabel() ?? $customer->resolveClientTypeLabel() ?? 'Clientes' }}
                                    </span>
                                </td>
                                <td class="p-4 text-center text-xs text-gray-500">
                                    {{ $customer->created_at->format('d/m/Y') }}
                                </td>
                                <td class="p-4 text-center">
                                    <button @click="openEditModal({{ json_encode(array_merge($customer->toArray(), ['client_type' => $customer->resolveClientTypeCode() ?? \App\Models\ClientType::DEFAULT_CODE])) }})" class="text-aromas-highlight hover:text-white p-2 rounded-lg bg-aromas-highlight/10 hover:bg-aromas-highlight transition-colors" title="Editar Cliente">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <p class="text-lg font-bold">No se encontraron clientes</p>
                                    <p class="text-sm">Intenta buscar con otros términos o importa tu base de datos.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($customers->hasPages())
                <div class="p-4 border-t border-gray-800 bg-black/20">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>

        {{-- ========================================== --}}
        {{-- MODAL: IMPORTAR CSV                        --}}
        {{-- ========================================== --}}
        <div x-show="showImportModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showImportModal = false"></div>
            
            <div class="bg-aromas-secondary w-full max-w-lg rounded-2xl shadow-2xl border border-aromas-highlight/30 flex flex-col relative z-10">
                <div class="p-6 border-b border-gray-700 flex justify-between items-center bg-gray-900/50 rounded-t-2xl">
                    <h2 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        Importar Clientes
                    </h2>
                    <button @click="showImportModal = false" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>

                <form action="{{ route('admin.customers.import') }}" method="POST" enctype="multipart/form-data" class="p-6" @submit="isImporting = true">
                    @csrf
                    
                    <div class="mb-6 bg-gray-900/50 border border-gray-700 p-4 rounded-xl text-sm text-gray-300">
                        <p class="font-bold text-aromas-highlight mb-2">Estructura requerida del CSV:</p>
                        <ul class="list-disc list-inside space-y-1 ml-2 text-xs font-mono">
                            <li><span class="text-white">numero_cliente</span> (Obligatorio)</li>
                            <li><span class="text-white">nombre</span> (Obligatorio en altas)</li>
                            <li><span class="text-white">telefono</span> (Opcional)</li>
                            <li><span class="text-white">email</span> (Opcional)</li>
                            <li><span class="text-white">codigo_lista</span> (Lista del sistema externo)</li>
                        </ul>
                        <p class="text-xs text-gray-400 mt-3 mb-2">Valores aceptados en <span class="text-white font-mono">codigo_lista</span>:</p>
                        <div class="overflow-x-auto">
                            <table class="w-full text-[10px] font-mono text-gray-300 border border-gray-700 rounded-lg">
                                <thead class="bg-gray-800 text-gray-400">
                                    <tr>
                                        <th class="px-2 py-1 text-left">Código</th>
                                        <th class="px-2 py-1 text-left">Lista asignada</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800">
                                    <tr><td class="px-2 py-1">PG</td><td class="px-2 py-1">Clientes (Público General)</td></tr>
                                    <tr><td class="px-2 py-1">1 / ORO / MAYOREO ORO</td><td class="px-2 py-1">Oro</td></tr>
                                    <tr><td class="px-2 py-1">2 / BRONCE / MAYOREO BRONCE</td><td class="px-2 py-1">Bronce</td></tr>
                                    <tr><td class="px-2 py-1">3 / PLATA / MAYOREO PLATA</td><td class="px-2 py-1">Plata</td></tr>
                                    <tr><td class="px-2 py-1">4 / DIAMANTE / MAYOREO DIAMANTE</td><td class="px-2 py-1">Diamante</td></tr>
                                    <tr><td class="px-2 py-1">5 / PLATAFORMAS</td><td class="px-2 py-1">Plataformas</td></tr>
                                    <tr><td class="px-2 py-1">7 / COLABORADORES</td><td class="px-2 py-1">Colaboradores</td></tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-2">Códigos no reconocidos se asignan a Clientes (Público General).</p>
                    </div>

                    <div class="mb-6">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-600 border-dashed rounded-xl cursor-pointer bg-gray-800 hover:bg-gray-700 hover:border-aromas-highlight transition-all group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-gray-400 group-hover:text-aromas-highlight transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                <p class="mb-2 text-sm text-gray-400"><span class="font-bold text-white group-hover:text-aromas-highlight">Haz clic para buscar</span> o arrastra el archivo</p>
                                <p class="text-xs text-gray-500" x-text="fileName ? fileName : 'Solo archivos .csv'"></p>
                            </div>
                            <input type="file" name="csv_file" class="hidden" accept=".csv" @change="fileName = $event.target.files[0].name" required />
                        </label>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" @click="showImportModal = false" class="flex-1 py-3 border border-gray-600 rounded-lg text-gray-300 font-bold hover:bg-gray-800 transition-colors">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-aromas-highlight text-aromas-main rounded-lg font-bold shadow-lg hover:bg-white transition-all flex justify-center items-center" :disabled="isImporting">
                            <span x-show="!isImporting">Cargar Datos</span>
                            <span x-show="isImporting" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-aromas-main" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================== --}}
        {{-- MODAL: EDITAR CLIENTE                      --}}
        {{-- ========================================== --}}
        <div x-show="showEditModal" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" @click="showEditModal = false"></div>
            
            <div class="bg-aromas-secondary w-full max-w-lg rounded-2xl shadow-2xl border border-gray-700 flex flex-col relative z-10">
                <div class="p-6 border-b border-gray-700 flex justify-between items-center bg-gray-900/50 rounded-t-2xl">
                    <h2 class="text-xl font-bold text-white">Editar Cliente</h2>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-white"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                </div>

                <form :action="`/admin/customers/${editingCustomer.id}`" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Número / ID</label>
                            <input type="text" name="customer_number" x-model="editingCustomer.customer_number" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-aromas-highlight">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Tipo de Cliente *</label>
                            <select name="client_type" x-model="editingCustomer.client_type" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-aromas-highlight">
                                @foreach($clientTypes as $type)
                                    <option value="{{ $type->code }}">{{ $type->label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Nombre Completo *</label>
                        <input type="text" name="name" x-model="editingCustomer.name" required class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-aromas-highlight">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Teléfono</label>
                            <input type="text" name="phone" x-model="editingCustomer.phone" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-aromas-highlight">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-400 uppercase tracking-wider font-bold mb-1">Email</label>
                            <input type="email" name="email" x-model="editingCustomer.email" class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white focus:border-aromas-highlight">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-gray-800 mt-6">
                        <button type="button" @click="showEditModal = false" class="flex-1 py-3 border border-gray-600 rounded-lg text-gray-300 font-bold hover:bg-gray-800 transition-colors">Cancelar</button>
                        <button type="submit" class="flex-1 py-3 bg-aromas-highlight text-aromas-main rounded-lg font-bold shadow-lg hover:bg-white transition-all">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function customersApp() {
            return {
                showImportModal: false,
                showEditModal: false,
                fileName: '',
                isImporting: false,
                editingCustomer: {},

                openImportModal() {
                    this.showImportModal = true;
                    this.fileName = '';
                    this.isImporting = false;
                },

                openEditModal(customer) {
                    this.editingCustomer = JSON.parse(JSON.stringify(customer)); // Clonar para no mutar directo
                    this.showEditModal = true;
                }
            }
        }
    </script>
</x-admin-layout>