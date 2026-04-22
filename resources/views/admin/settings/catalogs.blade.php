<x-admin-layout>
    @php
        // Estructura de catálogos para mantener el código limpio y escalable
        $catalogs = [
            'bank' => ['title' => 'Bancos', 'data' => $banks, 'column' => 'name'],
            'courier' => ['title' => 'Paqueterías', 'data' => $couriers, 'column' => 'name'],
            'warehouse' => ['title' => 'Almacenes', 'data' => $warehouses, 'column' => 'name'],
            'abandonment_reason' => ['title' => 'Motivos de Abandono', 'data' => $abandonmentReasons, 'column' => 'reason']
        ];
    @endphp

    <style>
        [x-cloak] { display: none !important; }
    </style>

    {{-- Lógica integrada directamente en x-data para evitar scripts externos --}}
    <div x-data="{ 
        activeTab: '{{ session('active_tab', 'bank') }}', 
        showModal: false, 
        modalMode: 'add', 
        catalogType: '', 
        formValue: '', 
        modalAction: '{{ route('admin.settings.catalogs.store') }}' 
    }">
        
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-white tracking-tight">Configuraciones Generales</h1>
            <p class="text-gray-400 text-sm mt-1">Gestión directa de catálogos y tablas de la base de datos.</p>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-500/20 border-l-4 border-red-500 text-white p-4 rounded shadow-sm">
                <ul class="list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Pestañas de Navegación --}}
        <div class="flex overflow-x-auto border-b border-aromas-tertiary/50 mb-6 space-x-6">
            @foreach($catalogs as $key => $cat)
                <button @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' ? 'border-aromas-highlight text-aromas-highlight' : 'border-transparent text-gray-400 hover:text-gray-200'"
                        class="py-3 px-1 border-b-2 font-medium transition-colors whitespace-nowrap text-sm uppercase tracking-wider">
                    {{ $cat['title'] }}
                </button>
            @endforeach
        </div>

        {{-- Contenedores de Tablas --}}
        <div class="bg-aromas-secondary border border-aromas-tertiary/50 rounded-xl shadow-sm overflow-hidden p-6 min-h-[300px]">
            @foreach($catalogs as $key => $cat)
                <div x-show="activeTab === '{{ $key }}'" x-cloak>
                    
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <h2 class="text-lg font-semibold text-white">Listado de {{ $cat['title'] }}</h2>
                        <button @click="modalMode = 'add'; catalogType = '{{ $key }}'; formValue = ''; modalAction = '{{ route('admin.settings.catalogs.store') }}'; showModal = true" 
                                class="bg-aromas-highlight hover:bg-yellow-600 text-aromas-main font-bold py-2 px-4 rounded-lg shadow-md transition-colors text-sm flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Agregar Nuevo
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-aromas-main/50 text-gray-400 text-xs uppercase tracking-wider border-b border-aromas-tertiary/30">
                                    <th class="px-6 py-4 font-medium">ID</th>
                                    <th class="px-6 py-4 font-medium">Descripción</th>
                                    <th class="px-6 py-4 font-medium">Estado</th>
                                    <th class="px-6 py-4 font-medium text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-aromas-tertiary/30">
                                @forelse($cat['data'] as $item)
                                    <tr class="hover:bg-aromas-main/30 transition-colors">
                                        <td class="px-6 py-4 text-sm text-gray-500">{{ $item->id }}</td>
                                        <td class="px-6 py-4 text-sm text-gray-100 font-medium">{{ $item->{$cat['column']} }}</td>
                                        <td class="px-6 py-4 text-sm">
                                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->is_active ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30' }}">
                                                {{ $item->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-medium space-x-3">
                                            <button @click="modalMode = 'edit'; catalogType = '{{ $key }}'; formValue = '{{ addslashes($item->{$cat['column']}) }}'; modalAction = '{{ url('admin/settings/catalogs') }}/{{ $item->id }}'; showModal = true" 
                                                    class="text-blue-400 hover:text-blue-300 transition-colors">
                                                Editar
                                            </button>
                                            
                                            <form action="{{ route('admin.settings.catalogs.toggle', $item->id) }}" method="POST" class="inline-block">
                                                @csrf
                                                <input type="hidden" name="catalog_type" value="{{ $key }}">
                                                <button type="submit" class="{{ $item->is_active ? 'text-yellow-500 hover:text-yellow-400' : 'text-green-400 hover:text-green-300' }} transition-colors">
                                                    {{ $item->is_active ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.settings.catalogs.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar permanentemente este registro?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="catalog_type" value="{{ $key }}">
                                                <button type="submit" class="text-red-500 hover:text-red-400 transition-colors">Borrar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">No hay registros en este catálogo.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Modal Unificado --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showModal" x-transition.opacity @click="showModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                
                <div x-show="showModal" 
                     x-transition:enter="ease-out duration-300" 
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                     class="inline-block align-bottom bg-aromas-secondary rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-aromas-tertiary/50">
                    
                    <form :action="modalAction" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="modalMode === 'add' ? 'POST' : 'PUT'">
                        <input type="hidden" name="catalog_type" x-model="catalogType">
                        
                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-semibold text-white mb-5" x-text="modalMode === 'add' ? 'Agregar Nuevo Registro' : 'Editar Registro'"></h3>
                            <div>
                                <label class="block text-sm font-medium text-gray-400 mb-2">Descripción / Nombre</label>
                                <input type="text" name="value" x-model="formValue" required 
                                       class="w-full bg-aromas-main border border-aromas-tertiary rounded-lg px-4 py-2 text-sm text-gray-200 focus:ring-aromas-highlight focus:border-aromas-highlight">
                            </div>
                        </div>
                        
                        <div class="px-4 py-4 bg-aromas-main/30 sm:px-6 sm:flex sm:flex-row-reverse border-t border-aromas-tertiary/30">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-blue-600 text-white font-bold hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Guardar</button>
                            <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-aromas-tertiary px-4 py-2 bg-transparent text-gray-300 hover:text-white sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>