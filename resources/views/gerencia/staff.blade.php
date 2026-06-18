<x-gerencia-layout>
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Personal</h1>
            <p class="text-aromas-tertiary text-sm">Administra el roster de vendedores y los turnos del piso de ventas.</p>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->canManageSellers())
            <a href="{{ route('gerencia.staff.create') }}"
                class="bg-aromas-highlight text-aromas-main px-5 py-2.5 rounded-xl font-bold uppercase tracking-widest shadow-lg hover:bg-white transition-all">
                Nuevo vendedor
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg text-green-400 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-lg text-red-300 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(auth()->user()->isAdmin() || auth()->user()->canManageSellers())
        <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden mb-8">
            <div class="p-4 border-b border-aromas-tertiary/10 bg-black/20">
                <h2 class="text-lg font-bold text-white">Roster de Vendedores</h2>
                <p class="text-xs text-gray-400 mt-1">Controla quién aparece en la pantalla de ventas y retira colaboradores.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                            <th class="p-4">Colaborador</th>
                            <th class="p-4 text-center">En pantalla de ventas</th>
                            <th class="p-4 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                        @forelse($rosterSellers as $seller)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-white">{{ $seller->full_name }}</div>
                                    <span class="block text-xs text-gray-500 font-normal">{{ $seller->employee_code }}</span>
                                </td>
                                <td class="p-4 text-center">
                                    <form action="{{ route('gerencia.staff.toggle-queue') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="employee_id" value="{{ $seller->id }}">
                                        <button type="submit"
                                            class="relative inline-flex items-center cursor-pointer transition-colors w-11 h-6 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-aromas-highlight {{ $seller->appears_in_sales_queue ? 'bg-aromas-highlight' : 'bg-gray-600' }}">
                                            <span class="sr-only">Toggle pantalla</span>
                                            <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $seller->appears_in_sales_queue ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                </td>
                                <td class="p-4 text-center">
                                    <form action="{{ route('gerencia.staff.deactivate', $seller->id) }}" method="POST"
                                        onsubmit="return confirm('¿Retirar a {{ $seller->full_name }} del personal de ventas?');">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 text-xs font-bold uppercase tracking-wider rounded-lg bg-red-500/20 text-red-400 hover:bg-red-500 hover:text-white transition-colors">
                                            Retirar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-500">
                                    No hay vendedores activos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(auth()->user()->isAdmin() || auth()->user()->canManageShifts())
        <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden">
            <div class="p-4 border-b border-aromas-tertiary/10 bg-black/20">
                <h2 class="text-lg font-bold text-white">Turnos del Día</h2>
                <p class="text-xs text-gray-400 mt-1">Activa o cierra el turno de los vendedores visibles en pantalla.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                            <th class="p-4">Colaborador</th>
                            <th class="p-4 text-center">Estado Actual</th>
                            <th class="p-4 text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                        @forelse($shiftSellers as $seller)
                            @php
                                $shift = $seller->todayShift;
                                $isOnline = $shift && $shift->current_status !== 'OFFLINE';
                                $statusText = $shift->current_status ?? 'OFFLINE';

                                if ($statusText === 'ONLINE') $statusText = 'Activo en Piso';
                                if ($statusText === 'BREAK') $statusText = 'En Descanso';
                                if ($statusText === 'OFFLINE') $statusText = 'Fuera de Turno';
                            @endphp
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="p-4">
                                    <div class="font-bold text-white">{{ $seller->full_name }}</div>
                                    <span class="block text-xs text-gray-500 font-normal">{{ $seller->employee_code }}</span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isOnline ? ($shift->current_status === 'BREAK' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-green-500/20 text-green-400') : 'bg-gray-700 text-gray-400' }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <form action="{{ route('gerencia.staff.toggle') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="employee_id" value="{{ $seller->id }}">
                                        <button type="submit"
                                            class="relative inline-flex items-center cursor-pointer transition-colors w-11 h-6 rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-aromas-highlight {{ $isOnline ? 'bg-green-500' : 'bg-gray-600' }}">
                                            <span class="sr-only">Toggle turno</span>
                                            <span class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $isOnline ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="p-8 text-center text-gray-500">
                                    No hay vendedores configurados para aparecer en la cola de ventas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-gerencia-layout>
