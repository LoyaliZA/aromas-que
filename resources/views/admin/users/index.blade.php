<x-admin-layout>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-white tracking-tight">Gestión de Personal</h1>
            <p class="text-aromas-tertiary mt-1 text-sm">Administra vendedores, gerentes y accesos.</p>
        </div>
        
        <a href="{{ route('admin.users.create') }}" class="w-full md:w-auto text-center px-6 py-3 bg-aromas-highlight text-aromas-main font-bold rounded-lg hover:bg-white transition-colors shadow-lg">
            + Nuevo Empleado
        </a>
    </div>

    <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 overflow-hidden">
        
        {{-- Versión Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-black/20 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/10">
                        <th class="p-4 font-semibold">Colaborador</th>
                        <th class="p-4 font-semibold">Código</th>
                        <th class="p-4 font-semibold">Puesto</th>
                        <th class="p-4 font-semibold text-center">Estado</th>
                        <th class="p-4 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aromas-tertiary/10 text-sm">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-white/5 transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-white">{{ $employee->full_name }}</div>
                                <div class="text-xs text-gray-500">{{ $employee->user->email ?? 'Sin Acceso Web' }}</div>
                            </td>
                            <td class="p-4 text-gray-300 font-mono">{{ $employee->employee_code }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-xs font-bold
                                    {{ $employee->job_position === 'ADMIN' ? 'bg-red-500/20 text-red-400' : 
                                      ($employee->job_position === 'MANAGER' ? 'bg-purple-500/20 text-purple-400' : 
                                      ($employee->job_position === 'SELLER' ? 'bg-green-500/20 text-green-400' : 'bg-blue-500/20 text-blue-400')) }}">
                                    {{ $employee->job_position }}
                                </span>
                                {{-- INDICADOR DE VENTAS (NUEVO) --}}
                                @if($employee->appears_in_sales_queue)
                                    <span class="ml-2 text-aromas-highlight text-xs" title="Visible en Ventas">
                                        🛒 En Cola
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($employee->is_active)
                                    <span class="inline-block w-3 h-3 bg-green-500 rounded-full shadow-[0_0_10px_rgba(34,197,94,0.5)]"></span>
                                @else
                                    <span class="inline-block w-3 h-3 bg-gray-600 rounded-full"></span>
                                @endif
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.users.edit', $employee->id) }}" class="text-gray-400 hover:text-white transition-colors">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">No hay empleados registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Versión Móvil (Cards) --}}
        <div class="md:hidden p-4 space-y-4">
            @foreach($employees as $employee)
                <div class="bg-black/20 p-4 rounded-lg border border-aromas-tertiary/10">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h3 class="font-bold text-white">{{ $employee->full_name }}</h3>
                            <span class="text-xs text-aromas-tertiary">{{ $employee->employee_code }}</span>
                        </div>
                        <span class="px-2 py-1 rounded text-xs font-bold
                            {{ $employee->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                            {{ $employee->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    
                    <div class="border-t border-aromas-tertiary/10 pt-2 mt-1">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500 text-sm">Puesto</span>
                            <div>
                                <span class="font-medium text-gray-300 text-sm">{{ $employee->job_position }}</span>
                                @if($employee->appears_in_sales_queue)
                                    <span class="ml-1 text-aromas-highlight text-xs">🛒</span>
                                @endif
                            </div>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-gray-500 text-sm">Acceso</span>
                            <span class="font-medium text-aromas-info text-sm truncate max-w-[150px]">{{ $employee->user->email ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.users.edit', $employee->id) }}" class="mt-3 w-full py-2 bg-aromas-secondary hover:bg-aromas-tertiary/20 border border-aromas-tertiary/30 rounded text-center text-sm text-gray-300 font-medium transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Editar Información
                    </a>
                </div>
            @endforeach
        </div>

        {{-- Paginación --}}
        <div class="p-4 border-t border-aromas-tertiary/10">
            {{ $employees->links() }}
        </div>
    </div>
</x-admin-layout>