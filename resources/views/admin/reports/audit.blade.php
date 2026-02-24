<x-admin-layout>
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-white flex items-center">
            <svg class="w-6 h-6 mr-3 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            Bóveda de Auditoría del Sistema
        </h2>
        <p class="text-gray-400 mt-1 text-sm">Registro inmutable de modificaciones críticas en el sistema. Estos datos no pueden ser alterados ni eliminados.</p>
    </div>

    <div class="bg-aromas-secondary rounded-lg border border-aromas-tertiary/30 shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-aromas-main/50 text-aromas-tertiary text-xs uppercase tracking-wider border-b border-aromas-tertiary/30">
                        <th class="p-4 font-semibold">Fecha y Hora</th>
                        <th class="p-4 font-semibold">Usuario (Rol)</th>
                        <th class="p-4 font-semibold">Módulo / ID</th>
                        <th class="p-4 font-semibold">Motivo de Edición</th>
                        <th class="p-4 font-semibold text-right">Detalle de Cambios</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-aromas-tertiary/20">
                    @forelse($audits as $audit)
                        <tr class="hover:bg-aromas-tertiary/10 transition-colors">
                            <td class="p-4 text-sm text-gray-300">
                                {{ $audit->created_at->format('d/m/Y') }} <br>
                                <span class="text-xs text-gray-500">{{ $audit->created_at->format('H:i:s') }}</span>
                            </td>
                            <td class="p-4 text-sm">
                                <span class="text-white font-medium">{{ $audit->user_name }}</span> <br>
                                <span class="text-xs px-2 py-0.5 rounded bg-aromas-main text-gray-400 border border-aromas-tertiary/50">
                                    {{ $audit->user_role }}
                                </span>
                            </td>
                            <td class="p-4 text-sm text-gray-300">
                                <span class="text-aromas-info font-semibold">Pickup (Logística)</span> <br>
                                <span class="text-xs text-gray-500">ID Paquete: #{{ $audit->pickup_id }}</span>
                            </td>
                            <td class="p-4 text-sm text-aromas-warning font-medium italic">
                                "{{ $audit->reason }}"
                            </td>
                            <td class="p-4 text-sm text-right">
                                <div class="inline-block text-left bg-aromas-main p-2 rounded border border-aromas-tertiary/30 text-xs text-gray-400 max-w-xs overflow-x-auto">
                                    @php
                                        $changes = json_decode($audit->changes, true);
                                    @endphp
                                    @if($changes)
                                        <ul class="list-disc list-inside space-y-1">
                                            @foreach($changes as $field => $values)
                                                <li>
                                                    <span class="text-gray-300 capitalize">{{ str_replace('_', ' ', $field) }}:</span> 
                                                    <span class="text-aromas-error line-through">{{ $values['old'] ?? 'N/A' }}</span> 
                                                    <svg class="w-3 h-3 inline text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                                    <span class="text-aromas-success">{{ $values['new'] ?? 'N/A' }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-gray-500">Sin detalles registrados</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-500">
                                No hay registros de auditoría en el sistema todavía.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($audits->hasPages())
            <div class="p-4 border-t border-aromas-tertiary/30 bg-aromas-main/30">
                {{ $audits->links() }}
            </div>
        @endif
    </div>
</x-admin-layout>