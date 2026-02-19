<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white">Editar Colaborador</h1>
        <p class="text-aromas-tertiary mt-1 text-sm">Actualiza la información o permisos de {{ $employee->full_name }}.</p>
    </div>

    <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 max-w-4xl">
        <form action="{{ route('admin.users.update', $employee->id) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')

            <div class="mb-8 border-b border-aromas-tertiary/20 pb-6">
                <h3 class="text-lg font-semibold text-aromas-highlight mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Información Laboral
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nombre Completo</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $employee->full_name) }}" required 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Código de Empleado</label>
                        <input type="text" name="employee_code" value="{{ old('employee_code', $employee->employee_code) }}" required 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Puesto / Rol</label>
                        <select name="job_position" required 
                                class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white focus:ring-aromas-highlight focus:border-aromas-highlight p-3">
                            <option value="SELLER" {{ $employee->job_position == 'SELLER' ? 'selected' : '' }}>Vendedor (Piso)</option>
                            <option value="MANAGER" {{ $employee->job_position == 'MANAGER' ? 'selected' : '' }}>Gerente</option>
                            <option value="CHECKER" {{ $employee->job_position == 'CHECKER' ? 'selected' : '' }}>Checador (Recepción)</option>
                            <option value="ADMIN" {{ $employee->job_position == 'ADMIN' ? 'selected' : '' }}>Administrador</option>
                        </select>
                    </div>

                    {{-- NUEVO SWITCH EDITABLE --}}
                    <div class="flex items-center pt-8">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="appears_in_sales_queue" value="1" class="sr-only peer" 
                                   {{ $employee->appears_in_sales_queue ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-aromas-highlight"></div>
                            <span class="ms-3 text-sm font-medium text-gray-300">Mostrar en Pantalla de Turnos</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold text-aromas-highlight mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                    Credenciales de Acceso
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email', $employee->user->email ?? '') }}" 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                               placeholder="Dejar vacío para quitar acceso">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nueva Contraseña</label>
                        <input type="password" name="password" 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                               placeholder="Llenar solo si se desea cambiar">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-aromas-tertiary/20 pt-6">
                <button type="button" onclick="document.getElementById('delete-form-{{ $employee->id }}').submit();"
                        class="text-aromas-error hover:text-red-400 text-sm font-bold flex items-center transition-colors">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                    {{ $employee->is_active ? 'Desactivar Empleado' : 'Reactivar Empleado' }}
                </button>

                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.users.index') }}" class="px-6 py-2 text-sm font-medium text-gray-400 hover:text-white transition-colors">Cancelar</a>
                    <button type="submit" class="px-6 py-2 bg-aromas-highlight text-aromas-main font-bold rounded-lg hover:bg-white hover:scale-105 transition-all shadow-lg">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- FORMULARIO DE DESACTIVACIÓN OCULTO --}}
    <form id="delete-form-{{ $employee->id }}" action="{{ route('admin.users.destroy', $employee->id) }}" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</x-admin-layout>