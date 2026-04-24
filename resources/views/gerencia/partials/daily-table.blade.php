<div class="mt-6">

    {{-- ========================================== --}}
    {{-- VISTA MÓVIL: TARJETAS DE AUDITORÍA RÁPIDA  --}}
    {{-- ========================================== --}}
    <div class="grid grid-cols-1 gap-4 md:hidden">
        @forelse($todaysPickups as $pickup)
        <div class="bg-aromas-secondary rounded-2xl shadow-lg border border-aromas-tertiary/20 p-5 flex flex-col gap-4 relative {{ $pickup->currentStatus?->code === 'PENDING_CONFIRMATION' ? 'ring-1 ring-amber-500/30' : '' }}">

            {{-- Encabezado: Selección y Folio --}}
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-3">
                    @if($pickup->currentStatus?->code === 'PENDING_CONFIRMATION')
                    <div class="flex items-center justify-center p-1">
                        <input type="checkbox" value="{{ $pickup->id }}" x-model="selectedPickups"
                            class="pickup-checkbox w-6 h-6 rounded border-gray-500 bg-black/40 text-green-500 focus:ring-green-500 cursor-pointer shadow-inner">
                    </div>
                    @endif
                    <div>
                        <span class="font-mono text-xl font-black text-aromas-highlight">#{{ $pickup->ticket_folio }}</span>
                        <span class="block text-[10px] uppercase font-bold text-gray-400">{{ $pickup->department }}</span>
                    </div>
                </div>
                <span class="px-3 py-1 text-[9px] font-bold uppercase rounded-full bg-{{ $pickup->currentStatus?->color ?? 'gray' }}-500/20 text-{{ $pickup->currentStatus?->color ?? 'gray' }}-400 border border-{{ $pickup->currentStatus?->color ?? 'gray' }}-500/30">
                    {{ $pickup->currentStatus->name ?? 'Capturado' }}
                </span>
            </div>

            {{-- Cliente e Info --}}
            <div class="bg-black/20 rounded-xl p-3 border border-aromas-tertiary/10">
                <p class="text-white font-bold text-sm leading-tight mb-2">{{ $pickup->client_name }}</p>
                <div class="flex gap-4">
                    <span class="text-xs text-gray-400">Pzs: <strong class="text-white">{{ $pickup->pieces }}</strong></span>
                    <span class="text-xs text-sky-400">Bolsas: <strong class="text-sky-300">{{ $pickup->bags ?? 0 }}</strong></span>
                </div>
            </div>

            {{-- Evidencias Táctiles (Zoom al tocar) --}}
            <div class="flex gap-3 justify-center">
                @foreach(['initial_evidence_path' => 'Ticket', 'package_evidence_path' => 'Bolsas'] as $field => $label)
                @if($pickup->$field)
                <div @touchstart.passive="openImageViewer('{{ asset('storage/'.$pickup->$field) }}')"
                    @touchend.passive="showImageViewer = false"
                    class="relative w-1/2 aspect-video bg-black/40 rounded-lg border border-gray-700 overflow-hidden">
                    <img src="{{ asset('storage/'.$pickup->$field) }}" class="w-full h-full object-cover">
                    <span class="absolute bottom-1 right-2 text-[8px] bg-black/60 text-white px-1 rounded font-bold uppercase">{{ $label }}</span>
                </div>
                @endif
                @endforeach
            </div>

            {{-- Acciones Rápidas --}}
            @if($pickup->currentStatus?->code === 'PENDING_CONFIRMATION')
            <div class="grid grid-cols-2 gap-3 pt-2">
                <form action="/gerencia/pickups/{{ $pickup->id }}/approve" method="POST">
                    @csrf
                    <button type="submit" class="w-full py-3 bg-green-600 text-white font-black rounded-xl text-xs uppercase tracking-widest shadow-lg active:scale-95 transition-all">Confirmar</button>
                </form>
                <button @click="openRejectModal({{ $pickup->id }}, '{{ $pickup->ticket_folio }}')" class="w-full py-3 bg-red-600/20 text-red-400 border border-red-500/30 font-black rounded-xl text-xs uppercase tracking-widest active:scale-95 transition-all">Corregir</button>
            </div>
            @endif
        </div>
        @empty
        <div class="py-12 text-center text-gray-500 bg-aromas-secondary rounded-2xl border border-aromas-tertiary/20">No hay resguardos hoy.</div>
        @endforelse
    </div>


    {{-- ========================================== --}}
    {{-- VISTA ESCRITORIO: TABLA VISUAL DE AUDITORIA --}}
    {{-- ========================================== --}}
    <div class="hidden md:flex bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden flex-col">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                        <th class="px-4 py-3 font-semibold w-16 text-center">
                            <div class="flex flex-col items-center justify-center gap-1" title="Seleccionar Todos">
                                <input type="checkbox" @change="toggleAll($event)" class="w-5 h-5 rounded border-gray-400 bg-black/40 text-green-500 focus:ring-green-500 cursor-pointer hover:scale-110 transition-transform shadow-inner">
                                <span class="text-[9px] uppercase tracking-widest text-gray-400 font-bold">Todos</span>
                            </div>
                        </th>
                        <th class="px-4 py-3 font-semibold">Folio / Info</th>
                        <th class="px-4 py-3 font-semibold text-center">Cantidades</th>
                        <th class="px-4 py-3 font-semibold text-center">Evidencia (Hover Zoom)</th>
                        <th class="px-4 py-3 font-semibold text-center">Auditoria Rapida</th>
                        <th class="px-4 py-3 font-semibold text-center">Opciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                    @forelse($todaysPickups as $pickup)
                    <tr class="hover:bg-white/5 transition-colors group {{ $pickup->currentStatus?->code === 'PENDING_CONFIRMATION' ? 'bg-amber-500/5' : '' }}">

                        <td class="px-4 py-3 text-center">
                            @if($pickup->currentStatus?->code === 'PENDING_CONFIRMATION')
                            <div class="flex items-center justify-center">
                                <input type="checkbox" value="{{ $pickup->id }}" x-model="selectedPickups" 
                                       class="pickup-checkbox w-5 h-5 rounded border-gray-500 bg-black/40 text-green-500 focus:ring-green-500 cursor-pointer hover:scale-110 transition-transform shadow-inner">
                            </div>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-mono font-black text-aromas-highlight text-lg">#{{ $pickup->ticket_folio }}</span>
                                @if($pickup->is_complementary)
                                <span class="text-[10px] bg-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded font-black">- C</span>
                                @endif
                                <span class="px-2 py-0.5 text-[9px] font-bold uppercase rounded bg-gray-800 text-gray-300 border border-gray-700">{{ $pickup->department }}</span>
                            </div>
                            <div class="text-white font-bold text-xs truncate max-w-[200px]">{{ $pickup->client_name }}</div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col gap-1">
                                <span class="text-[10px] text-gray-400 uppercase font-bold">Pzs: <strong class="text-white text-sm">{{ $pickup->pieces }}</strong></span>
                                <span class="text-[10px] text-sky-400 uppercase font-bold">Bolsas: <strong class="text-sky-300 text-sm">{{ $pickup->bags ?? 0 }}</strong></span>
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            <div class="flex items-center justify-center gap-3">
                                @foreach(['initial_evidence_path' => 'Ticket', 'package_evidence_path' => 'Bolsas'] as $field => $label)
                                @if($pickup->$field)
                                <div @mouseenter="openImageViewer('{{ asset('storage/'.$pickup->$field) }}')"
                                    @mouseleave="showImageViewer = false"
                                    class="block border-2 border-gray-600 rounded-lg overflow-hidden hover:border-aromas-highlight hover:scale-110 transition-all w-16 h-16 bg-black/50 shadow-lg cursor-crosshair">
                                    <img src="{{ asset('storage/'.$pickup->$field) }}" class="w-full h-full object-cover">
                                </div>
                                @else
                                <div class="w-16 h-16 border-2 border-dashed border-gray-700 rounded-lg flex items-center justify-center text-gray-600 text-[8px] text-center uppercase">{{ $label }} <br>N/D</div>
                                @endif
                                @endforeach
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($pickup->currentStatus?->code === 'PENDING_CONFIRMATION')
                            <div class="flex flex-col gap-2">
                                <form action="/gerencia/pickups/{{ $pickup->id }}/approve" method="POST" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full py-1.5 bg-green-600 text-white border border-green-500 rounded text-[10px] font-black uppercase tracking-widest hover:bg-green-500 transition-colors shadow-lg">Confirmar</button>
                                </form>
                                <button @click="openRejectModal({{ $pickup->id }}, '{{ $pickup->ticket_folio }}')" class="w-full py-1.5 bg-red-600/10 text-red-400 border border-red-500/30 rounded text-[10px] font-black uppercase tracking-widest hover:bg-red-600 hover:text-white transition-colors">Corregir</button>
                            </div>
                            @else
                            <span class="text-gray-600 text-xs italic">Auditado</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <div x-data="{ openDropdown: false, menuTop: '0px', menuLeft: '0px', toggleMenu(e) { this.openDropdown = !this.openDropdown; if(this.openDropdown) { const rect = e.currentTarget.getBoundingClientRect(); this.menuTop = (rect.bottom + 5) + 'px'; this.menuLeft = (rect.right - 160) + 'px'; } } }">
                                <button @click="toggleMenu($event)" @click.away="openDropdown = false" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>
                                <div x-show="openDropdown" x-transition style="display: none; position: fixed;" :style="{ top: menuTop, left: menuLeft }" class="w-40 bg-gray-800 border border-gray-600 rounded-lg shadow-2xl z-[9999] text-sm overflow-hidden">
                                    <button @click="openDropdown = false; openDetailsModal({ ticket_folio: '{{ $pickup->ticket_folio }}', client_name: {{ json_encode($pickup->client_name) }}, pieces: {{ $pickup->pieces }}, status_name: '{{ $pickup->currentStatus->name ?? 'Capturado' }}', department: '{{ $pickup->department }}', notes: {{ json_encode($pickup->notes ?? '') }}, initial_evidence_url: {{ json_encode($pickup->initial_evidence_path ? asset('storage/'.$pickup->initial_evidence_path) : '') }}, package_evidence_url: {{ json_encode($pickup->package_evidence_path ? asset('storage/'.$pickup->package_evidence_path) : '') }}, evidence_url: {{ json_encode($pickup->evidence_path ? asset('storage/'.$pickup->evidence_path) : '') }} })" class="w-full text-left px-4 py-3 text-blue-400 hover:bg-white/5 border-b border-gray-700">Ver Detalles</button>
                                    <button @click="openDropdown = false; openEditModal({ id: {{ $pickup->id }}, ticket_folio: '{{ $pickup->ticket_folio }}', department: '{{ $pickup->department }}', pieces: {{ $pickup->pieces }}, notes: {{ json_encode($pickup->notes ?? '') }} })" class="w-full text-left px-4 py-3 text-aromas-highlight hover:bg-white/5 border-b border-gray-700">Editar</button>
                                    <button @click="openDropdown = false; openDeleteModal({{ $pickup->id }}, '{{ $pickup->ticket_folio }}')" class="w-full text-left px-4 py-3 text-red-400 hover:bg-red-500/10">Eliminar</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500 font-bold">No hay resguardos registrados.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>