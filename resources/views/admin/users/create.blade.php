<x-admin-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white">Nuevo Colaborador</h1>
        <p class="text-aromas-tertiary mt-1 text-sm">Registra un empleado y asigna sus credenciales de acceso si es necesario.</p>
    </div>

    <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 max-w-4xl">
        <form action="{{ route('admin.users.store') }}" method="POST" class="p-8">
            @csrf

            <div class="mb-8 border-b border-aromas-tertiary/20 pb-6">
                <h3 class="text-lg font-semibold text-aromas-highlight mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884.6 2 2 2h4.667M16 16l-4-4m0 0l-4 4m4-4v12"></path></svg>
                    Información Laboral
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nombre Completo</label>
                        <input type="text" name="full_name" required 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                               placeholder="Ej. Juan Pérez">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Código / Nómina</label>
                        <input type="text" name="employee_code" required 
                               class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                               placeholder="Ej. EMP-005">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Puesto Asignado</label>
                        <select name="job_position" id="job_position" required
                                class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white focus:ring-aromas-highlight focus:border-aromas-highlight p-3">
                            <option value="SELLER">Vendedor (Piso)</option>
                            <option value="CHECKER">Checador (Recepción)</option>
                            <option value="MANAGER">Gerente</option>
                            <option value="ADMIN">Administrador</option>
                        </select>
                    </div>
                </div>
            </div>

            <div x-data="{ needsLogin: false }" class="mb-6">
                <div class="flex items-center mb-6">
                    <input type="checkbox" id="enable_login" x-model="needsLogin" 
                           class="w-5 h-5 text-aromas-highlight bg-aromas-main border-aromas-tertiary rounded focus:ring-aromas-highlight">
                    <label for="enable_login" class="ml-3 text-sm font-medium text-gray-200 cursor-pointer">
                        ¿Otorgar acceso Web/Login? (Para Admins, Gerentes o Recepción)
                    </label>
                </div>

                <div x-show="needsLogin" x-transition class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-black/20 p-6 rounded-lg border border-aromas-tertiary/10">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Correo Electrónico</label>
                        <input type="email" name="email" 
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