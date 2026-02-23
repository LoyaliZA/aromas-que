<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white">Nuevo Colaborador</h1>
        <p class="text-aromas-tertiary mt-1 text-sm">Registra un empleado y asigna sus credenciales de acceso si es necesario.</p>
    </div>

    <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 max-w-4xl">
        
        @if ($errors->any())
            <div class="m-8 mb-0 p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                <div class="flex items-center mb-2">
                    <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <h3 class="text-red-400 font-bold text-sm">No se pudo guardar la información:</h3>
                </div>
                <ul class="list-disc list-inside text-red-300 text-sm ml-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST" class="p-8">
            @csrf

            <div class="mb-8 border-b border-aromas-tertiary/20 pb-6">
                <h3 class="text-lg font-semibold text-aromas-highlight mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884.6 2 2 2h4.667M16 16l-4-4-4 4"></path></svg>
                    Información Laboral
                </h3>
                
                {{-- ALPINE.JS: Reactive state para ocultar/mostrar opciones según el puesto --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6" x-data="{ role: '{{ old('job_position', 'SELLER') }}' }">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nombre Completo</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                               placeholder="Ej: Juan Pérez">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Código de Empleado</label>
                        <input type="text" name="employee_code" value="{{ old('employee_code') }}" required 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                               placeholder="Ej: AROM-001">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Puesto / Rol</label>
                        <select name="job_position" required x-model="role"
                                class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white focus:ring-aromas-highlight focus:border-aromas-highlight p-3">
                            <option value="SELLER">Vendedor (Piso)</option>
                            <option value="MANAGER">Gerente</option>
                            <option value="CHECKER">Checador (Recepción)</option>
                            <option value="ADMIN">Administrador</option>
                        </select>
                    </div>

                    <div class="flex items-center pt-8">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="appears_in_sales_queue" value="1" class="sr-only peer" {{ old('appears_in_sales_queue', true) ? 'checked' : '' }}>
                            <div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-aromas-highlight"></div>
                            <span class="ms-3 text-sm font-medium text-gray-300">Mostrar en Pantalla de Turnos</span>
                        </label>
                    </div>

                    {{-- NUEVO: Permiso Especial de Rezagados (Solo se muestra si es MANAGER) --}}
                    <div class="col-span-full" x-show="role === 'MANAGER'" x-cloak>
                        <div class="p-4 bg-aromas-main border border-yellow-500/30 rounded-lg shadow-inner">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="can_manage_rezagados" value="1" class="sr-only peer" {{ old('can_manage_rezagados') ? 'checked' : '' }}>
                                <div class="relative w-11 h-6 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                                <div class="ms-3">
                                    <span class="block text-sm font-bold text-yellow-400">Permiso Especial: Gestor de Rezagados</span>
                                    <span class="block text-xs text-gray-400 mt-1">Habilita a este gerente para entregar los paquetes que llevan más de 15 días en tienda.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="mb-6">
                <h3 class="text-lg font-semibold text-aromas-highlight mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    Credenciales de Acceso (Opcional)
                </h3>
                <p class="text-sm text-gray-500 mb-4">Solo llena esto si el empleado necesita entrar al sistema.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Correo Electrónico</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                               placeholder="correo@aromas.com">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Contraseña</label>
                        <input type="password" name="password" 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                               placeholder="********">
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 border-t border-aromas-tertiary/20 pt-6">
                <a href="{{ route('admin.users.index') }}" class="px-6 py-2 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                    Cancelar
                </a>
                <button type="submit" class="px-6 py-2 bg-aromas-highlight text-aromas-main font-bold rounded-lg hover:bg-white hover:scale-105 transition-all shadow-lg">
                    Guardar Colaborador
                </button>
            </div>
        </form>
    </div>
</x-admin-layout>