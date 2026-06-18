<x-gerencia-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Agregar Vendedor</h1>
        <p class="text-aromas-tertiary text-sm mt-1">Registra un vendedor nuevo o reactiva uno que ya existía en el sistema.</p>
    </div>

    <div class="bg-aromas-secondary rounded-xl shadow-xl border border-aromas-tertiary/20 max-w-3xl" x-data="{ mode: '{{ old('registration_mode', 'new') }}' }">

        @if ($errors->any())
            <div class="m-8 mb-0 p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                <ul class="list-disc list-inside text-red-300 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('gerencia.staff.store') }}" method="POST" class="p-8">
            @csrf

            <div class="mb-8 p-4 bg-aromas-main border border-aromas-tertiary/30 rounded-lg">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Tipo de registro</p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="registration_mode" value="new" x-model="mode" class="text-aromas-highlight focus:ring-aromas-highlight">
                        <span class="ms-2 text-sm text-gray-200">Nuevo vendedor</span>
                    </label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="radio" name="registration_mode" value="existing" x-model="mode" class="text-aromas-highlight focus:ring-aromas-highlight">
                        <span class="ms-2 text-sm text-gray-200">Reactivar colaborador existente</span>
                    </label>
                </div>
            </div>

            <div x-show="mode === 'new'" x-cloak>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Nombre Completo</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" :required="mode === 'new'" :disabled="mode !== 'new'"
                            class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                            placeholder="Ej: Juan Pérez">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Código de Empleado</label>
                        <input type="text" name="employee_code" value="{{ old('employee_code') }}" :required="mode === 'new'" :disabled="mode !== 'new'"
                            class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white placeholder-gray-500 focus:ring-aromas-highlight focus:border-aromas-highlight p-3"
                            placeholder="Ej: AROM-001">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">Departamento / Área</label>
                        <select name="department" :required="mode === 'new'" :disabled="mode !== 'new'"
                            class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white focus:ring-aromas-highlight focus:border-aromas-highlight p-3">
                            @foreach($departments as $department)
                                <option value="{{ $department->name }}" {{ old('department', 'NONE') == $department->name ? 'selected' : '' }}>
                                    {{ $departmentLabels[$department->name] ?? $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <p class="text-xs text-gray-500 mb-6">El vendedor se registrará con puesto Vendedor (Piso) y aparecerá automáticamente en la pantalla de ventas.</p>
            </div>

            <div x-show="mode === 'existing'" x-cloak>
                @if($inactiveSellers->isEmpty())
                    <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-lg text-yellow-300 text-sm">
                        No hay vendedores inactivos disponibles para reactivar. Usa la opción "Nuevo vendedor" para registrar uno.
                    </div>
                @else
                    <div class="space-y-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Colaborador a reactivar</label>
                            <select name="employee_id" :required="mode === 'existing'" :disabled="mode !== 'existing'"
                                class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white focus:ring-aromas-highlight focus:border-aromas-highlight p-3">
                                <option value="">Selecciona un colaborador...</option>
                                @foreach($inactiveSellers as $seller)
                                    <option value="{{ $seller->id }}" {{ (string) old('employee_id') === (string) $seller->id ? 'selected' : '' }}>
                                        {{ $seller->full_name }} — {{ $seller->employee_code }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-2">Se reutilizará el mismo registro del colaborador.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Departamento / Área (opcional)</label>
                            <select name="reactivate_department" :disabled="mode !== 'existing'"
                                class="w-full bg-aromas-main border border-aromas-tertiary/50 rounded-lg text-white focus:ring-aromas-highlight focus:border-aromas-highlight p-3">
                                <option value="">Mantener departamento anterior</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->name }}" {{ old('reactivate_department') == $department->name ? 'selected' : '' }}>
                                        {{ $departmentLabels[$department->name] ?? $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end gap-4 border-t border-aromas-tertiary/20 pt-6">
                <a href="{{ route('gerencia.staff.index') }}" class="px-6 py-2 text-sm font-medium text-gray-400 hover:text-white transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                    @if($inactiveSellers->isEmpty())
                        x-bind:disabled="mode === 'existing'"
                        x-bind:class="mode === 'existing' ? 'opacity-50 cursor-not-allowed' : ''"
                    @endif
                    class="px-6 py-2 bg-aromas-highlight text-aromas-main font-bold rounded-lg hover:bg-white transition-all shadow-lg">
                    <span x-text="mode === 'existing' ? 'Reactivar Vendedor' : 'Guardar Vendedor'"></span>
                </button>
            </div>
        </form>
    </div>
</x-gerencia-layout>
