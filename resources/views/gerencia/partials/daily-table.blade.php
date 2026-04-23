<div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden flex flex-col mt-6">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                    <th class="px-6 py-3 font-semibold">Folio</th>
                    <th class="px-6 py-3 font-semibold">Cliente</th>
                    <th class="px-6 py-3 font-semibold text-center">Área</th>
                    <th class="px-6 py-3 font-semibold text-center">Piezas</th>
                    <th class="px-6 py-3 font-semibold">Estado</th>
                    <th class="px-6 py-3 font-semibold text-center">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                @forelse($todaysPickups as $pickup)
                <tr class="hover:bg-white/5 transition-colors group">
                    <td class="px-6 py-3 font-mono font-medium text-aromas-highlight">
                        {{ $pickup->ticket_folio }}
                        @if($pickup->is_complementary)
                        <span class="ml-1 text-[10px] bg-blue-500/20 text-blue-400 px-1.5 py-0.5 rounded font-black">- C</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-white font-bold">{{ $pickup->client_name ?? 'Pendiente por Checador' }}</td>
                    <td class="px-6 py-3 text-center text-gray-400">{{ $pickup->department }}</td>
                    <td class="px-6 py-3 text-center font-bold text-gray-300">{{ $pickup->pieces }}</td>
                    <td class="px-6 py-3">
                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded-full bg-{{ $pickup->currentStatus->color ?? 'gray' }}-500/20 text-{{ $pickup->currentStatus->color ?? 'gray' }}-400 border border-{{ $pickup->currentStatus->color ?? 'gray' }}-500/30">
                            {{ $pickup->currentStatus->name ?? 'Capturado' }}
                        </span>
                    </td>
                    <td class="px-6 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">

                            {{-- BOTÓN AUDITAR (Prioridad Alta - Visible si está esperando confirmación) --}}
                            @if($pickup->currentStatus?->code === 'PENDING_CONFIRMATION')
                            <button @click="openAuditModal({
                                    id: {{ $pickup->id }},
                                    ticket_folio: '{{ $pickup->ticket_folio }}',
                                    client_name: {{ json_encode($pickup->client_name) }},
                                    pieces: {{ $pickup->pieces }},
                                    bags: {{ $pickup->bags ?? 0 }},
                                    department: '{{ $pickup->department }}',
                                    notes: {{ json_encode($pickup->notes ?? '') }},
                                    initial_evidence_url: {{ json_encode($pickup->initial_evidence_path ? asset('storage/'.$pickup->initial_evidence_path) : '') }},
                                    package_evidence_url: {{ json_encode($pickup->package_evidence_path ? asset('storage/'.$pickup->package_evidence_path) : '') }}
                                })" class="px-3 py-1.5 bg-amber-500/20 text-amber-500 hover:bg-amber-500 hover:text-black font-bold uppercase text-[10px] rounded-lg transition-colors border border-amber-500/50 flex items-center gap-1 shadow-lg shadow-amber-500/20" title="Auditar Resguardo">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Auditar
                            </button>
                            @endif

                            {{-- MENÚ DESPLEGABLE (3 PUNTITOS) --}}
                            <div x-data="{ 
                                openDropdown: false, 
                                menuTop: '0px', 
                                menuLeft: '0px',
                                toggleMenu(e) {
                                    this.openDropdown = !this.openDropdown;
                                    if(this.openDropdown) {
                                        const rect = e.currentTarget.getBoundingClientRect();
                                        this.menuTop = (rect.bottom + 5) + 'px'; 
                                        this.menuLeft = (rect.right - 160) + 'px';
                                    }
                                }
                            }">

                                <button @click="toggleMenu($event)" @click.away="openDropdown = false" @scroll.window="openDropdown = false" class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                                    </svg>
                                </button>

                                <div x-show="openDropdown" x-transition.opacity.duration.200ms style="display: none; position: fixed;" :style="{ top: menuTop, left: menuLeft }" class="w-40 bg-aromas-secondary border border-aromas-tertiary/30 rounded-lg shadow-2xl z-[9999] overflow-hidden text-sm font-medium">

                                    {{-- 1. Visualizar Detalles --}}
                                    <button @click="openDropdown = false; openDetailsModal({
                                            ticket_folio: '{{ $pickup->ticket_folio }}',
                                            client_name: {{ json_encode($pickup->client_name) }},
                                            pieces: {{ $pickup->pieces }},
                                            status_name: '{{ $pickup->currentStatus->name ?? 'Capturado' }}',
                                            department: '{{ $pickup->department }}',
                                            notes: {{ json_encode($pickup->notes ?? '') }},
                                            initial_evidence_url: {{ json_encode($pickup->initial_evidence_path ? asset('storage/'.$pickup->initial_evidence_path) : '') }},
                                            package_evidence_url: {{ json_encode($pickup->package_evidence_path ? asset('storage/'.$pickup->package_evidence_path) : '') }},
                                            evidence_url: {{ json_encode($pickup->evidence_path ? asset('storage/'.$pickup->evidence_path) : '') }}
                                        })" class="w-full text-left px-4 py-2.5 text-blue-400 hover:bg-white/5 flex items-center gap-2 border-b border-aromas-tertiary/10 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        Ver Detalles
                                    </button>

                                    {{-- 2. Editar --}}
                                    <button @click="openDropdown = false; openEditModal({
                                            id: {{ $pickup->id }},
                                            ticket_folio: '{{ $pickup->ticket_folio }}',
                                            department: '{{ $pickup->department }}',
                                            pieces: {{ $pickup->pieces }},
                                            notes: {{ json_encode($pickup->notes ?? '') }}
                                        })" class="w-full text-left px-4 py-2.5 text-aromas-highlight hover:bg-white/5 flex items-center gap-2 border-b border-aromas-tertiary/10 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Editar
                                    </button>

                                    {{-- 3. Eliminar --}}
                                    <button @click="openDropdown = false; openDeleteModal({{ $pickup->id }}, '{{ $pickup->ticket_folio }}')" class="w-full text-left px-4 py-2.5 text-red-400 hover:bg-red-500/10 flex items-center gap-2 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Eliminar
                                    </button>
                                </div>
                            </div>

                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-gray-500">No hay resguardos registrados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>