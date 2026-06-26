<x-admin-layout>
    @php
        $catalogs = [
            'bank' => ['title' => 'Bancos', 'data' => $banks, 'column' => 'name'],
            'courier' => ['title' => 'Paqueterías', 'data' => $couriers, 'column' => 'name'],
            'warehouse' => ['title' => 'Almacenes', 'data' => $warehouses, 'column' => 'name'],
            'abandonment_reason' => ['title' => 'Motivos de Abandono', 'data' => $abandonmentReasons, 'column' => 'reason'],
            'role' => ['title' => 'Roles', 'data' => $roles, 'column' => 'name'],
            'department' => ['title' => 'Departamentos', 'data' => $departments, 'column' => 'name'],
            'job_position' => ['title' => 'Puestos', 'data' => $jobPositions, 'column' => 'name'],
            'client_type' => ['title' => 'Tipos de Cliente', 'data' => $clientTypes, 'column' => 'label'],
            'service_type' => ['title' => 'Tipos de Servicio', 'data' => $serviceTypes, 'column' => 'name'],
            'break_reason' => ['title' => 'Motivos de Pausa', 'data' => $breakReasons, 'column' => 'label']
        ];
    @endphp

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <div x-data="{
        activeTab: '{{ session('active_tab', 'bank') }}',
        showModal: false,
        modalMode: 'add',
        catalogType: '',
        formValue: '',
        modalAction: '{{ route('admin.settings.catalogs.store') }}',
        showClientTypeModal: false,
        clientTypeMode: 'add',
        clientTypeAction: '{{ route('admin.settings.catalogs.store') }}',
        clientTypeForm: {
            code: '',
            label: '',
            sort_order: 100,
            prioritize_in_queue: false,
            hide_on_public_tv: false,
            use_premium_alert: false,
        },
        openClientTypeAdd() {
            this.clientTypeMode = 'add';
            this.clientTypeAction = '{{ route('admin.settings.catalogs.store') }}';
            this.clientTypeForm = {
                code: '',
                label: '',
                sort_order: 100,
                prioritize_in_queue: false,
                hide_on_public_tv: false,
                use_premium_alert: false,
            };
            this.showClientTypeModal = true;
        },
        openClientTypeEdit(item) {
            this.clientTypeMode = 'edit';
            this.clientTypeAction = '{{ url('admin/settings/catalogs') }}/' + item.id;
            this.clientTypeForm = {
                code: item.code,
                label: item.label,
                sort_order: item.sort_order,
                prioritize_in_queue: !!item.prioritize_in_queue,
                hide_on_public_tv: !!item.hide_on_public_tv,
                use_premium_alert: !!item.use_premium_alert,
            };
            this.showClientTypeModal = true;
        }
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

        <div class="flex overflow-x-auto border-b border-aromas-tertiary/50 mb-6 space-x-6">
            @foreach($catalogs as $key => $cat)
                <button @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' ? 'border-aromas-highlight text-aromas-highlight' : 'border-transparent text-gray-400 hover:text-gray-200'"
                        class="py-3 px-1 border-b-2 font-medium transition-colors whitespace-nowrap text-sm uppercase tracking-wider">
                    {{ $cat['title'] }}
                </button>
            @endforeach
            <button @click="activeTab = 'system_settings'"
                    :class="activeTab === 'system_settings' ? 'border-aromas-highlight text-aromas-highlight' : 'border-transparent text-gray-400 hover:text-gray-200'"
                    class="py-3 px-1 border-b-2 font-medium transition-colors whitespace-nowrap text-sm uppercase tracking-wider">
                Sistema
            </button>
        </div>

        <div class="bg-aromas-secondary border border-aromas-tertiary/50 rounded-xl shadow-sm overflow-hidden p-6 min-h-[300px]">
            @foreach($catalogs as $key => $cat)
                @if($key === 'client_type')
                    <div x-show="activeTab === 'client_type'" x-cloak>
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                            <div>
                                <h2 class="text-lg font-semibold text-white">Listado de Tipos de Cliente</h2>
                                <p class="text-gray-400 text-sm mt-1">Un solo orden controla listas y fila de espera. Número más bajo = nivel más alto.</p>
                            </div>
                            <button @click="openClientTypeAdd()"
                                    class="bg-aromas-highlight hover:bg-yellow-600 text-aromas-main font-bold py-2 px-4 rounded-lg shadow-md transition-colors text-sm flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Agregar Nuevo
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-aromas-main/50 text-gray-400 text-xs uppercase tracking-wider border-b border-aromas-tertiary/30">
                                        <th class="px-4 py-4 font-medium">Orden</th>
                                        <th class="px-4 py-4 font-medium">Nivel</th>
                                        <th class="px-4 py-4 font-medium">Código</th>
                                        <th class="px-4 py-4 font-medium">Priorizar fila</th>
                                        <th class="px-4 py-4 font-medium">Ocultar TV</th>
                                        <th class="px-4 py-4 font-medium">Alerta premium</th>
                                        <th class="px-4 py-4 font-medium">Estado</th>
                                        <th class="px-4 py-4 font-medium text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-aromas-tertiary/30">
                                    @forelse($clientTypes as $item)
                                        <tr class="hover:bg-aromas-main/30 transition-colors">
                                            <td class="px-4 py-4 text-sm text-gray-300 font-mono">{{ $item->sort_order }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-100 font-medium">{{ $item->label }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-500 font-mono">{{ $item->code }}</td>
                                            <td class="px-4 py-4 text-sm">
                                                <span class="px-2 py-1 rounded text-xs {{ $item->prioritize_in_queue ? 'bg-yellow-500/20 text-yellow-300' : 'bg-gray-700 text-gray-400' }}">
                                                    {{ $item->prioritize_in_queue ? 'Sí ('.$item->sort_order.')' : 'No' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-sm text-gray-300">{{ $item->hide_on_public_tv ? 'Sí' : 'No' }}</td>
                                            <td class="px-4 py-4 text-sm text-gray-300">{{ $item->use_premium_alert ? 'Sí' : 'No' }}</td>
                                            <td class="px-4 py-4 text-sm">
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->is_active ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30' }}">
                                                    {{ $item->is_active ? 'Activo' : 'Inactivo' }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 text-right text-sm font-medium space-x-3">
                                                <button                                                 @click="openClientTypeEdit(@js([
                                                    'id' => $item->id,
                                                    'code' => $item->code,
                                                    'label' => $item->label,
                                                    'sort_order' => $item->sort_order,
                                                    'prioritize_in_queue' => $item->prioritize_in_queue,
                                                    'hide_on_public_tv' => $item->hide_on_public_tv,
                                                    'use_premium_alert' => $item->use_premium_alert,
                                                ]))"
                                                        class="text-blue-400 hover:text-blue-300 transition-colors">
                                                    Editar
                                                </button>

                                                <form action="{{ route('admin.settings.catalogs.toggle', $item->id) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    <input type="hidden" name="catalog_type" value="client_type">
                                                    <button type="submit" class="{{ $item->is_active ? 'text-yellow-500 hover:text-yellow-400' : 'text-green-400 hover:text-green-300' }} transition-colors">
                                                        {{ $item->is_active ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                </form>

                                                @if(!in_array($item->code, config('catalog_labels.protected_catalog_names.client_types', []), true))
                                                    <form action="{{ route('admin.settings.catalogs.destroy', $item->id) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar permanentemente este registro?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="catalog_type" value="client_type">
                                                        <button type="submit" class="text-red-500 hover:text-red-400 transition-colors">Borrar</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">No hay tipos de cliente configurados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
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
                @endif
            @endforeach

            <div x-show="activeTab === 'system_settings'" x-cloak>
                <div class="mb-6">
                    <h2 class="text-lg font-semibold text-white">Configuración del Sistema</h2>
                    <p class="text-gray-400 text-sm mt-1">Ajusta los parámetros globales de la aplicación.</p>
                </div>

                <form action="{{ route('admin.settings.system.update') }}" method="POST" class="max-w-2xl bg-gray-900/50 p-6 rounded-xl border border-gray-700">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Tiempo de Atención Base (Minutos)</label>
                            <input type="number" name="attention_time_minutes" min="1" max="120" value="{{ $systemSettings['attention_time_minutes'] ?? 20 }}" required
                                   class="w-full bg-gray-800 border border-gray-600 rounded-lg text-white px-4 py-2 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight">
                            <p class="text-xs text-gray-500 mt-1">Tiempo que tiene un vendedor antes de que la tarjeta se ponga roja.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-300 mb-2">Ventana para Solicitar Prórroga (Minutos)</label>
                            <input type="number" name="extension_time_minutes" min="1" max="60" value="{{ $systemSettings['extension_time_minutes'] ?? 4 }}" required
                                   class="w-full bg-gray-800 border border-gray-600 rounded-lg text-white px-4 py-2 focus:border-aromas-highlight focus:ring-1 focus:ring-aromas-highlight">
                            <p class="text-xs text-gray-500 mt-1">Tiempo que tiene el colaborador para solicitar una prórroga antes de que el sistema corte el turno. La prórroga otorgada dura igual al tiempo de atención base.</p>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-aromas-highlight hover:bg-yellow-600 text-aromas-main font-bold py-2 px-6 rounded-lg transition-colors">
                            Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal genérico --}}
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

        {{-- Modal tipos de cliente --}}
        <div x-show="showClientTypeModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showClientTypeModal" x-transition.opacity @click="showClientTypeModal = false" class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showClientTypeModal"
                     class="inline-block align-bottom bg-aromas-secondary rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full border border-aromas-tertiary/50">

                    <form :action="clientTypeAction" method="POST">
                        @csrf
                        <input type="hidden" name="_method" :value="clientTypeMode === 'add' ? 'POST' : 'PUT'">
                        <input type="hidden" name="catalog_type" value="client_type">

                        <div class="px-4 pt-5 pb-4 sm:p-6 sm:pb-4 space-y-4">
                            <h3 class="text-lg leading-6 font-semibold text-white" x-text="clientTypeMode === 'add' ? 'Agregar Tipo de Cliente' : 'Editar Tipo de Cliente'"></h3>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div x-show="clientTypeMode === 'add'">
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Código (inmutable)</label>
                                    <input type="text" name="code" x-model="clientTypeForm.code" :required="clientTypeMode === 'add'"
                                           class="w-full bg-aromas-main border border-aromas-tertiary rounded-lg px-4 py-2 text-sm text-gray-200 uppercase">
                                </div>
                                <div x-show="clientTypeMode === 'edit'">
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Código</label>
                                    <input type="text" x-model="clientTypeForm.code" readonly
                                           class="w-full bg-gray-800 border border-aromas-tertiary rounded-lg px-4 py-2 text-sm text-gray-400 uppercase">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Nombre visible</label>
                                    <input type="text" name="label" x-model="clientTypeForm.label" required
                                           class="w-full bg-aromas-main border border-aromas-tertiary rounded-lg px-4 py-2 text-sm text-gray-200">
                                </div>

                                <div class="sm:col-span-2">
                                    <label class="block text-sm font-medium text-gray-400 mb-2">Orden del nivel</label>
                                    <input type="number" name="sort_order" x-model="clientTypeForm.sort_order" required min="1" max="9998"
                                           class="w-full bg-aromas-main border border-aromas-tertiary rounded-lg px-4 py-2 text-sm text-gray-200">
                                    <p class="text-xs text-gray-500 mt-1">Número más bajo = nivel más alto. Define el orden en listas y, si activas la fila, quién se atiende primero.</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                                <label class="flex items-start gap-2 text-sm text-gray-300">
                                    <input type="hidden" name="prioritize_in_queue" value="0">
                                    <input type="checkbox" name="prioritize_in_queue" value="1" x-model="clientTypeForm.prioritize_in_queue" class="rounded border-gray-600 mt-0.5">
                                    <span>Priorizar en fila de espera</span>
                                </label>
                                <label class="flex items-start gap-2 text-sm text-gray-300">
                                    <input type="hidden" name="hide_on_public_tv" value="0">
                                    <input type="checkbox" name="hide_on_public_tv" value="1" x-model="clientTypeForm.hide_on_public_tv" class="rounded border-gray-600 mt-0.5">
                                    <span>
                                        Ocultar en TV
                                        <span class="block text-xs text-gray-500 mt-0.5">Oculta al cliente en la fila de espera. Al llamarlo, se anuncia por nombre (sin número de turno). Para estilo VIP/dorado, activa también «Alerta premium».</span>
                                    </span>
                                </label>
                                <label class="flex items-center gap-2 text-sm text-gray-300">
                                    <input type="hidden" name="use_premium_alert" value="0">
                                    <input type="checkbox" name="use_premium_alert" value="1" x-model="clientTypeForm.use_premium_alert" class="rounded border-gray-600">
                                    Alerta premium
                                </label>
                            </div>
                        </div>

                        <div class="px-4 py-4 bg-aromas-main/30 sm:px-6 sm:flex sm:flex-row-reverse border-t border-aromas-tertiary/30">
                            <button type="submit" class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-blue-600 text-white font-bold hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Guardar</button>
                            <button type="button" @click="showClientTypeModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-aromas-tertiary px-4 py-2 bg-transparent text-gray-300 hover:text-white sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
