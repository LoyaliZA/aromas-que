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
                <tbody class="divide-y divide-aromas-tertiary/10 text-gray-300 text-sm">
                    @forelse($employees as $employee)
                    <tr class="hover:bg-aromas-main/30 transition-colors duration-150">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 rounded-full bg-aromas-main flex shrink-0 items-center justify-center text-aromas-highlight font-bold border border-aromas-tertiary/30">
                                    {{ substr($employee->full_name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-white">{{ $employee->full_name }}</p>
                                    @if($employee->user)
                                        <p class="text-xs text-aromas-info">{{ $employee->user->email }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="p-4 font-mono text-aromas-highlight">{{ $employee->employee_code }}</td>

                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs font-semibold bg-aromas-main border border-aromas-tertiary text-gray-300">
                                {{ $employee->job_position }}
                            </span>
                        </td>

                        <td class="p-4 text-center">
                            @if($employee->is_active)
                                <span class="text-aromas-success text-xs">● Activo</span>
                            @else
                                <span class="text-aromas-error text-xs">● Inactivo</span>
                            @endif
                        </td>

                        <td class="p-4 text-right">
                            <a href="{{ route('admin.users.edit', $employee->id) }}" class="text-gray-400 hover:text-white p-2 inline-block" title="Editar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">Sin empleados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="md:hidden space-y-4 p-4">
            @forelse($employees as $employee)
            <div class="bg-aromas-main/40 rounded-lg p-4 border border-aromas-tertiary/20 flex flex-col gap-3">
                
                <div class="flex justify-between items-start">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-aromas-secondary flex items-center justify-center text-aromas-highlight font-bold border border-aromas-tertiary/30">
                            {{ substr($employee->full_name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-white text-sm">{{ $employee->full_name }}</p>
                            <p class="text-xs text-aromas-highlight font-mono">{{ $employee->employee_code }}</p>
                        </div>
                    </div>
                    @if($employee->is_active)
                        <div class="h-2 w-2 rounded-full bg-aromas-success mt-2" title="Activo"></div>
                    @else
                        <div class="h-2 w-2 rounded-full bg-aromas-error mt-2" title="Inactivo"></div>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs border-t border-aromas-tertiary/10 pt-2 mt-1">
                    <div>
                        <span class="text-gray-500 block">Puesto</span>
                        <span class="font-medium text-gray-300">{{ $employee->job_position }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block">Acceso</span>
                        <span class="font-medium text-aromas-info truncate">{{ $employee->user->email ?? 'N/A' }}</span>
                    </div>
                </div>

                <a href="{{ route('admin.users.edit', $employee->id) }}" class="mt-2 w-full py-2 bg-aromas-secondary hover:bg-aromas-tertiary/20 border border-aromas-tertiary/30 rounded text-center text-sm text-gray-300 font-medium transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                    Editar Información
                </a>

            </div>
            @empty
            <div class="text-center p-8 text-gray-500">Sin empleados registrados.</div>
            @endforelse
        </div>
        
        <div class="p-4 border-t border-aromas-tertiary/10 bg-black/10">
            {{ $employees->links() }}
        </div>
    </div>
</x-admin-layout>