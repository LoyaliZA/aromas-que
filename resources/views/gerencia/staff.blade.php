<x-gerencia-layout>
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestión de Personal</h1>
            <p class="text-aromas-tertiary text-sm">Control de asistencia y activación de turnos de venta.</p>
        </div>
    </div>

    <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                    <th class="p-4">Colaborador</th>
                    <th class="p-4 text-center">Estado Actual</th>
                    <th class="p-4 text-center">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                @forelse($sellers as $seller)
                    @php
                        $shift = $seller->todayShift;
                        $isOnline = $shift && $shift->current_status !== 'OFFLINE';
                        $statusText = $shift->current_status ?? 'OFFLINE';
                        
                        // Traducción amigable
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
                                    <span class="sr-only">Toggle</span>
                                    <span class="translate-x-1 inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $isOnline ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="p-8 text-center text-gray-500">
                            No hay empleados configurados para aparecer en la cola de ventas.
                            <br>Ve al panel de Admin y activa la opción "Mostrar en Pantalla de Turnos".
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-gerencia-layout>