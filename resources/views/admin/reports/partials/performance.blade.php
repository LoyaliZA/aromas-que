{{-- ═══════════════════════════════════════════════════════════════════════
     RENDIMIENTO VENDEDOR — Con modal de configuración de PDF
═══════════════════════════════════════════════════════════════════════ --}}
<div
    x-data="performancePdf({
        employeeId: '{{ $selectedEmployeeId ?? '' }}',
        startDate: '{{ $start_date }}',
        endDate: '{{ $end_date }}',
        generateUrl: '{{ route('admin.reports.pdf.generate') }}',
        statusUrlBase: '{{ rtrim(url('/admin/reports/pdf/status'), '/') }}/',
        downloadUrlBase: '{{ rtrim(url('/admin/reports/pdf/download'), '/') }}/',
        csrfToken: '{{ csrf_token() }}'
    })"
>

{{-- ─── Cabecera ───────────────────────────────────────────────────────────── --}}
<div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl font-black text-white uppercase tracking-widest">Rendimiento por Vendedor</h3>
    @if($empData)
    <div class="flex gap-2 items-center">
        {{-- CSV (sin cambios) --}}
        <a href="{{ route('admin.reports.export', ['type' => 'employee', 'employee_id' => $selectedEmployeeId, 'format' => 'csv', 'period' => $period, 'start_date' => $start_date, 'end_date' => $end_date]) }}"
           class="bg-gray-800 hover:bg-gray-700 text-white font-bold px-4 py-2 rounded-lg text-sm border border-gray-600 transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            CSV
        </a>
        {{-- PDF → abre modal --}}
        <button @click="openModal()"
                class="bg-aromas-highlight hover:bg-yellow-400 text-gray-900 font-bold px-4 py-2 rounded-lg text-sm shadow-md transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0013 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            Exportar PDF
        </button>
    </div>
    @endif
</div>

{{-- ─── Buscador de empleado ───────────────────────────────────────────────── --}}
<div class="bg-gray-800 p-4 rounded-xl border border-gray-700 mb-6">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="flex items-center gap-4">
        <input type="hidden" name="tab" value="performance">
        <input type="hidden" name="period" value="{{ $period }}">
        <input type="hidden" name="start_date" value="{{ $start_date }}">
        <input type="hidden" name="end_date" value="{{ $end_date }}">
        <label class="text-sm font-bold text-gray-300">Seleccionar Vendedor:</label>
        <select name="employee_id" class="bg-gray-900 text-white border border-gray-600 rounded-lg px-4 py-2 flex-1 max-w-md focus:ring-aromas-highlight" onchange="this.form.submit()">
            <option value="">-- Elige un vendedor --</option>
            @foreach($employeesList as $emp)
            <option value="{{ $emp->id }}" {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}>{{ $emp->full_name }}</option>
            @endforeach
        </select>
    </form>
</div>

@if($empData)
{{-- ─── KPIs ───────────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Calificación</p><p class="text-3xl font-black text-yellow-400"><svg class="w-6 h-6 inline pb-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $empData['kpis']['avg_stars'] }}</p></div>
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Atendidos</p><p class="text-3xl font-black text-green-400">{{ $empData['kpis']['served'] }}</p></div>
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Prom. Atención</p><p class="text-3xl font-black text-blue-400">{{ $empData['kpis']['avg_time'] }}</p></div>
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">Tiempo Libre</p><p class="text-3xl font-black text-aromas-highlight">{{ $empData['kpis']['total_available'] }}</p></div>
    <div class="bg-gray-900 p-4 rounded-xl border border-gray-700 text-center"><p class="text-[10px] text-gray-400 uppercase font-bold tracking-widest mb-1">En Pausa</p><p class="text-3xl font-black text-yellow-500">{{ $empData['kpis']['total_break'] }}</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Desglose de Pausas por Día --}}
    <div class="bg-gray-900 rounded-xl border border-gray-700 p-5 flex flex-col h-[260px]">
        <div class="border-b border-gray-800 pb-2 mb-3">
            <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Desglose (Día)</h3>
        </div>
        <div class="overflow-y-auto flex-1 custom-scrollbar space-y-4">
            @forelse($empData['daily_breaks'] as $date => $breaks)
                <div>
                    <h4 class="text-xs font-black text-white mb-2 bg-gray-800/80 border border-gray-700 p-1.5 rounded">{{ \Carbon\Carbon::parse($date)->format('d / m / Y') }}</h4>
                    <div class="space-y-1">
                        @foreach($breaks as $reasonName => $time)
                            <div class="flex justify-between px-2 py-1 border-b border-gray-800/50">
                                <span class="text-[11px] text-gray-400">{{ $reasonName }}</span>
                                <span class="text-[11px] font-mono {{ $reasonName === 'Tiempo Disponible' ? 'text-aromas-highlight' : 'text-yellow-500' }}">{{ $time }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty <p class="text-sm text-gray-500 text-center">No hay datos.</p> @endforelse
        </div>
    </div>

    {{-- Bitácora --}}
    <div class="lg:col-span-2 bg-gray-900 rounded-xl border border-gray-700 h-[260px] flex flex-col">
        <div class="sticky top-0 bg-gray-900 p-4 border-b border-gray-800">
            <h3 class="text-sm font-bold text-gray-300 uppercase tracking-widest">Línea de Tiempo</h3>
        </div>
        <div class="p-4 flex-1 overflow-y-auto custom-scrollbar">
            <ul class="space-y-3">
                @forelse($empData['timeline'] as $log)
                <li class="flex gap-4 text-sm items-center border-l-2 pl-3 {{ $log['color'] === 'text-yellow-500' ? 'border-yellow-500' : 'border-green-400' }}">
                    <span class="text-gray-500 font-mono w-24">{{ $log['date'] }} <br><span class="text-xs">{{ $log['time'] }}</span></span>
                    <span class="flex-1 font-bold {{ $log['color'] }}">Cambio a: {{ $log['status'] }}</span>
                </li>
                @empty <li class="text-gray-500 italic text-center py-4">Sin actividad.</li> @endforelse
            </ul>
        </div>
    </div>
</div>

{{-- Tabla de clientes con auditoría --}}
<div class="bg-gray-900 rounded-xl border border-gray-700 overflow-hidden shadow-md">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-800/50 border-b border-gray-700">
                <tr class="text-gray-400 text-[10px] uppercase tracking-wider">
                    <th class="p-4">Turno/Cliente</th>
                    <th class="p-4 text-center">Tiempos</th>
                    <th class="p-4">Auditoría / Calificaciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($empData['clients'] as $client)
                <tr class="hover:bg-gray-800/50">
                    <td class="p-4">
                        <span class="font-mono font-bold text-white block">{{ $client->turn_number }}</span>
                        <span class="text-sm text-gray-300 block mb-1">{{ $client->client_name }}</span>
                        @if($client->client_type === 'VIP') <span class="bg-yellow-500/20 text-yellow-400 text-[9px] px-1.5 py-0.5 rounded border border-yellow-500/30 uppercase font-bold">VIP</span>
                        @elseif($client->has_disability) <span class="bg-blue-500/20 text-blue-400 text-[9px] px-1.5 py-0.5 rounded border border-blue-500/30 uppercase font-bold">PREF</span>@endif
                    </td>
                    <td class="p-4 text-xs text-center">
                        <div class="text-yellow-400 mb-1">E: {{ $client->formatted_wait }}</div>
                        <div class="text-blue-400">A: {{ $client->formatted_serve }}</div>
                    </td>
                    <td class="p-4 text-xs">
                        @if($client->ratings && $client->ratings->count() > 0)
                            @php
                                $cr = $client->ratings->where('rater_type', 'CLIENT')->first();
                                $sr = $client->ratings->where('rater_type', 'SELLER')->first();
                            @endphp
                            @if($cr)
                            <div class="mb-1 pb-1 border-b border-gray-800 flex items-start gap-2">
                                <span class="bg-blue-900/50 text-blue-400 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase">Cliente</span>
                                <span class="text-yellow-400 font-bold whitespace-nowrap"><svg class="w-3 h-3 inline pb-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $cr->stars }}</span>
                                <span class="text-gray-400 truncate max-w-[150px]" title="{{ $cr->comments }}">{{ implode(', ', $cr->tags ?? []) }}</span>
                            </div>
                            @endif
                            @if($sr)
                            <div class="flex items-start gap-2">
                                <span class="bg-purple-900/50 text-purple-400 px-1.5 py-0.5 rounded font-bold text-[9px] uppercase">Vendedor</span>
                                <span class="text-yellow-400 font-bold whitespace-nowrap"><svg class="w-3 h-3 inline pb-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg> {{ $sr->stars }}</span>
                                <span class="text-gray-400 truncate max-w-[150px]" title="{{ $sr->comments }}">{{ implode(', ', $sr->tags ?? []) }}</span>
                            </div>
                            @endif
                        @else
                            <span class="text-gray-600 italic">Sin datos</span>
                        @endif
                    </td>
                </tr>
                @empty <tr><td colspan="3" class="p-8 text-center text-gray-500">Sin clientes.</td></tr> @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-700 bg-gray-900">{{ $empData['clients']->appends(['tab' => 'performance', 'employee_id' => $selectedEmployeeId])->links() }}</div>
</div>
@else
<div class="text-center py-20 bg-gray-900 rounded-xl border border-gray-700">
    <p class="text-gray-500">Selecciona un vendedor en la parte superior para ver sus estadísticas.</p>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════════
     MODAL DE CONFIGURACIÓN DE PDF
══════════════════════════════════════════════════════════════════════════════ --}}
<div
    x-show="modalOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
    style="display:none;"
    @click.self="closeModal()"
>
    <div
        x-show="modalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="bg-gray-900 border border-gray-700 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden"
    >
        {{-- Cabecera del modal --}}
        <div class="bg-gray-800 px-6 py-4 border-b border-gray-700 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-aromas-highlight/20 border border-aromas-highlight/30 flex items-center justify-center">
                    <svg class="w-5 h-5 text-aromas-highlight" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h4 class="text-white font-black text-base uppercase tracking-wide">Configurar Reporte PDF</h4>
                    <p class="text-gray-400 text-xs mt-0.5">Elige las secciones a incluir</p>
                </div>
            </div>
            <button @click="closeModal()" class="text-gray-500 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="p-6 space-y-5">

            {{-- Aviso período largo --}}
            <div x-show="isLongPeriod" class="bg-amber-500/10 border border-amber-500/40 rounded-xl p-4 flex gap-3">
                <svg class="w-5 h-5 text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div>
                    <p class="text-amber-400 font-bold text-sm">Periodo extendido detectado</p>
                    <p class="text-amber-300/80 text-xs mt-1">El periodo seleccionado es mayor a 7 días. El reporte se generará en segundo plano para evitar errores de tiempo de espera. Se te notificará cuando esté listo para descargar.</p>
                </div>
            </div>

            {{-- Secciones --}}
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Secciones a incluir</p>
                <div class="space-y-2">
                    <template x-for="section in sectionOptions" :key="section.key">
                        <label class="flex items-start gap-3 p-3 bg-gray-800 border border-gray-700 rounded-xl cursor-pointer hover:border-aromas-highlight/50 transition-all group"
                               :class="{ 'border-aromas-highlight/60 bg-aromas-highlight/5': sections.includes(section.key) }">
                            <input type="checkbox"
                                   :value="section.key"
                                   x-model="sections"
                                   class="mt-0.5 accent-yellow-400 w-4 h-4 flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-bold text-white group-hover:text-aromas-highlight transition" x-text="section.label"></span>
                                    <span x-show="section.heavy" class="text-[9px] bg-amber-500/20 text-amber-400 border border-amber-500/30 px-1.5 py-0.5 rounded uppercase font-bold">Pesado</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5" x-text="section.desc"></p>
                            </div>
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition"
                                 :class="sections.includes(section.key) ? 'bg-aromas-highlight/20 text-aromas-highlight' : 'bg-gray-700 text-gray-500'"
                                 x-html="section.icon">
                            </div>
                        </label>
                    </template>
                </div>

                {{-- Botones de selección rápida --}}
                <div class="flex gap-2 mt-3">
                    <button @click="sections = sectionOptions.map(s => s.key)" class="text-xs text-gray-400 hover:text-white transition px-3 py-1.5 bg-gray-800 border border-gray-700 rounded-lg">Seleccionar todo</button>
                    <button @click="sections = ['kpis']" class="text-xs text-gray-400 hover:text-white transition px-3 py-1.5 bg-gray-800 border border-gray-700 rounded-lg">Solo KPIs</button>
                    <button @click="sections = []" class="text-xs text-red-400 hover:text-red-300 transition px-3 py-1.5 bg-gray-800 border border-gray-700 rounded-lg">Limpiar</button>
                </div>
            </div>

            {{-- ─── Estado: Generando (modo async) ─────────────────────────── --}}
            <div x-show="state === 'generating'" class="space-y-3">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-300 font-bold">Generando reporte...</span>
                    <span class="text-aromas-highlight font-mono font-bold" x-text="progress + '%'"></span>
                </div>
                <div class="w-full bg-gray-800 rounded-full h-2.5 overflow-hidden">
                    <div class="h-2.5 rounded-full bg-aromas-highlight transition-all duration-500"
                         :style="`width: ${progress}%`"></div>
                </div>
                <p class="text-xs text-gray-500 text-center animate-pulse">Por favor espera, procesando datos en segundo plano...</p>
            </div>

            {{-- ─── Estado: Listo para descargar ────────────────────────────── --}}
            <div x-show="state === 'done'" class="bg-green-500/10 border border-green-500/40 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-6 h-6 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-green-400 font-bold text-sm">¡Reporte listo!</p>
                    <p class="text-green-300/80 text-xs">Tu PDF fue generado exitosamente. Haz clic en Descargar.</p>
                </div>
            </div>

            {{-- ─── Estado: Error ───────────────────────────────────────────── --}}
            <div x-show="state === 'failed'" class="bg-red-500/10 border border-red-500/40 rounded-xl p-4 flex items-center gap-3">
                <svg class="w-6 h-6 text-red-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div>
                    <p class="text-red-400 font-bold text-sm">Error al generar el reporte</p>
                    <p class="text-red-300/80 text-xs" x-text="errorMessage || 'Ocurrió un error inesperado. Intenta de nuevo.'"></p>
                </div>
            </div>

        </div>

        {{-- Pie del modal --}}
        <div class="px-6 pb-6 flex gap-3 justify-end">
            <button @click="closeModal()" :disabled="state === 'generating'"
                    class="px-5 py-2.5 text-sm font-bold text-gray-400 bg-gray-800 border border-gray-700 rounded-xl hover:bg-gray-700 hover:text-white transition disabled:opacity-50">
                Cancelar
            </button>

            {{-- Botón generar --}}
            <button
                x-show="state === 'idle' || state === 'failed'"
                @click="generate()"
                :disabled="sections.length === 0"
                class="px-6 py-2.5 text-sm font-black bg-aromas-highlight text-gray-900 rounded-xl hover:bg-yellow-400 transition shadow-lg disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-text="isLongPeriod ? 'Generar en segundo plano' : 'Generar PDF'"></span>
            </button>

            {{-- Botón descargar (solo cuando está listo) --}}
            <a x-show="state === 'done'"
               :href="downloadUrlBase + currentToken"
               class="px-6 py-2.5 text-sm font-black bg-green-500 text-white rounded-xl hover:bg-green-400 transition shadow-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Descargar PDF
            </a>
        </div>
    </div>
</div>

</div>{{-- Cierre x-data --}}

{{-- ─── Script Alpine de performancePdf ──────────────────────────────────── --}}
<script>
function performancePdf(config) {
    return {
        // — Config —
        employeeId:      config.employeeId,
        startDate:       config.startDate,
        endDate:         config.endDate,
        generateUrl:     config.generateUrl,
        statusUrlBase:   config.statusUrlBase,
        downloadUrlBase: config.downloadUrlBase,
        csrfToken:       config.csrfToken,

        // — Estado del modal —
        modalOpen: false,
        state:     'idle',   // idle | generating | done | failed
        progress:  0,
        errorMessage: '',
        currentToken: null,
        pollingTimer: null,

        // — Secciones —
        sections: ['kpis', 'breaks', 'timeline', 'clients', 'ratings'],
        sectionOptions: [
            {
                key:   'kpis',
                label: 'KPIs Resumen',
                desc:  'Clientes atendidos, tiempos promedio, calificación, pausas y tiempo libre.',
                heavy: false,
                icon:  '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>'
            },
            {
                key:   'breaks',
                label: 'Desglose de Pausas por Día',
                desc:  'Comida, baño, mandado, empaque y tiempo disponible por fecha.',
                heavy: false,
                icon:  '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            },
            {
                key:   'timeline',
                label: 'Bitácora de Actividad',
                desc:  'Registro cronológico de cambios de estado: disponible, pausa, inactivo.',
                heavy: false,
                icon:  '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>'
            },
            {
                key:   'clients',
                label: 'Historial de Clientes Atendidos',
                desc:  'Tabla completa de turnos: tiempos, tipo de cliente y estado de la venta.',
                heavy: true,
                icon:  '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>'
            },
            {
                key:   'ratings',
                label: 'Calificaciones y Comentarios',
                desc:  'Estrellas y comentarios de clientes y del propio vendedor por cada turno.',
                heavy: true,
                icon:  '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>'
            },
        ],

        get isLongPeriod() {
            if (!this.startDate || !this.endDate) return false;
            const diff = (new Date(this.endDate) - new Date(this.startDate)) / (1000 * 60 * 60 * 24);
            return diff > 7;
        },

        openModal() {
            this.state = 'idle';
            this.progress = 0;
            this.errorMessage = '';
            this.currentToken = null;
            this.modalOpen = true;
        },

        closeModal() {
            if (this.state === 'generating') return;
            this.modalOpen = false;
            if (this.pollingTimer) clearInterval(this.pollingTimer);
        },

        async generate() {
            if (this.sections.length === 0) return;

            this.state    = 'generating';
            this.progress = 5;
            this.errorMessage = '';

            const body = new URLSearchParams();
            body.append('_token',      this.csrfToken);
            body.append('employee_id', this.employeeId);
            body.append('start_date',  this.startDate);
            body.append('end_date',    this.endDate);
            this.sections.forEach(s => body.append('sections[]', s));

            try {
                const res = await fetch(this.generateUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken, 'Accept': 'application/json' },
                    body: body,
                });

                // — Periodo corto: respuesta directa (PDF) —
                if (res.headers.get('Content-Type')?.includes('application/pdf')) {
                    const blob   = await res.blob();
                    const url    = URL.createObjectURL(blob);
                    const a      = document.createElement('a');
                    const cd     = res.headers.get('Content-Disposition') ?? '';
                    const match  = cd.match(/filename="?([^"]+)"?/);
                    a.href       = url;
                    a.download   = match ? match[1] : 'reporte_vendedor.pdf';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                    this.state    = 'idle';
                    this.progress = 0;
                    this.closeModal();
                    return;
                }

                // — Periodo largo: token de job —
                const json = await res.json();
                if (!res.ok || !json.token) throw new Error(json.message || 'Error al lanzar el job.');
                this.currentToken = json.token;
                this.startPolling();

            } catch (err) {
                this.state        = 'failed';
                this.errorMessage = err.message;
            }
        },

        startPolling() {
            if (this.pollingTimer) clearInterval(this.pollingTimer);
            this.pollingTimer = setInterval(() => this.checkStatus(), 2500);
        },

        async checkStatus() {
            if (!this.currentToken) return;
            try {
                const res  = await fetch(this.statusUrlBase + this.currentToken, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                this.progress = data.progress ?? this.progress;

                if (data.status === 'done') {
                    clearInterval(this.pollingTimer);
                    this.state    = 'done';
                    this.progress = 100;
                } else if (data.status === 'failed') {
                    clearInterval(this.pollingTimer);
                    this.state        = 'failed';
                    this.errorMessage = data.message || 'El proceso falló inesperadamente.';
                }
            } catch (_) {}
        },
    };
}
</script>