<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Rendimiento — {{ $employee->full_name }}</title>
    <style>
        /* ─── Base ─────────────────────────────────────────────────────── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #ffffff;
        }

        /* ─── Encabezado ────────────────────────────────────────────────── */
        .header {
            background-color: #0f172a;
            color: #ffffff;
            padding: 22px 30px 18px;
            border-bottom: 4px solid #fdc974;
        }
        .header-top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
        .header h1 { font-size: 20px; color: #fdc974; text-transform: uppercase; letter-spacing: 1.5px; line-height: 1.2; }
        .header .subtitle { font-size: 10px; color: #cbd5e1; margin-top: 3px; }
        .header .badge-sistema { font-size: 9px; color: #fdc974; border: 1px solid #fdc974; padding: 3px 8px; border-radius: 20px; white-space: nowrap; }

        .seller-card {
            background-color: #1e3a5f;
            border: 1px solid #fdc974;
            border-radius: 6px;
            padding: 10px 16px;
            display: inline-block;
        }
        .seller-name { font-size: 15px; font-weight: bold; color: #ffffff; }
        .seller-code { font-size: 9px; color: #93c5fd; margin-top: 2px; }
        .period-badge {
            background-color: #1e3a5f;
            border: 1px solid #3b82f6;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 10px;
            color: #bfdbfe;
            text-align: right;
        }
        .period-badge strong { display: block; color: #ffffff; font-size: 12px; margin-bottom: 2px; }

        /* ─── Secciones incluidas ────────────────────────────────────────── */
        .sections-included {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 5px;
            padding: 6px 12px;
            margin: 10px 30px 0;
            font-size: 9px;
            color: #475569;
        }
        .sections-included strong { color: #0f172a; }
        .section-tag {
            display: inline-block;
            background: #e2e8f0;
            border-radius: 3px;
            padding: 1px 6px;
            margin: 0 2px;
            color: #334155;
            font-weight: bold;
        }

        /* ─── KPIs ──────────────────────────────────────────────────────── */
        .kpis-wrapper { padding: 16px 30px 0; }
        .kpis-grid { display: table; width: 100%; border-collapse: separate; border-spacing: 8px 0; }
        .kpi-cell { display: table-cell; width: 20%; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px 8px; text-align: center; vertical-align: middle; }
        .kpi-cell.highlight { background: #0f172a; border-color: #fdc974; }
        .kpi-label { font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px; display: block; margin-bottom: 5px; }
        .kpi-label.light { color: #94a3b8; }
        .kpi-value { font-size: 26px; font-weight: bold; line-height: 1; }
        .val-green  { color: #10b981; }
        .val-blue   { color: #3b82f6; }
        .val-yellow { color: #f59e0b; }
        .val-gold   { color: #fdc974; }
        .val-purple { color: #a78bfa; }
        .star-icon { font-size: 14px; }

        /* ─── Sección título ─────────────────────────────────────────────── */
        .section-block { padding: 14px 30px 0; }
        .section-title {
            font-size: 11px;
            color: #0f172a;
            text-transform: uppercase;
            border-bottom: 2px solid #fdc974;
            padding-bottom: 5px;
            margin-bottom: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .section-title .icon { color: #fdc974; margin-right: 4px; }

        /* ─── Tabla general ──────────────────────────────────────────────── */
        .data-table {
            border-collapse: collapse;
            width: 100%;
            font-size: 8.5px;
            margin-bottom: 6px;
        }
        .data-table th, .data-table td {
            border: 1px solid #e2e8f0;
            padding: 5px 7px;
            text-align: left;
            vertical-align: top;
        }
        .data-table th {
            background: #1e293b;
            color: #fdc974;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.4px;
            font-size: 8px;
        }
        .data-table tr:nth-child(even) { background: #f8fafc; }
        .data-table .center { text-align: center; }
        .data-table .right  { text-align: right; }

        /* ─── Badges ─────────────────────────────────────────────────────── */
        .badge-vip   { color: #b45309; background: #fef3c7; padding: 1px 4px; border-radius: 3px; font-weight: bold; }
        .badge-re    { color: #d97706; background: #fef3c7; padding: 1px 4px; border-radius: 3px; font-weight: bold; }
        .badge-pref  { color: #1d4ed8; background: #dbeafe; padding: 1px 4px; border-radius: 3px; font-weight: bold; }
        .text-warn   { color: #d97706; font-weight: bold; }
        .text-ok     { color: #10b981; font-weight: bold; }
        .text-blue   { color: #3b82f6; font-weight: bold; }
        .text-yellow { color: #f59e0b; font-weight: bold; }
        .text-purple { color: #7c3aed; font-weight: bold; }
        .text-muted  { color: #94a3b8; font-style: italic; }

        /* ─── Calificaciones ─────────────────────────────────────────────── */
        .stars-row { color: #f59e0b; font-weight: bold; font-size: 10px; }
        .tag-pill {
            display: inline-block;
            background: #ede9fe;
            color: #6d28d9;
            font-size: 7px;
            padding: 1px 4px;
            border-radius: 3px;
            margin: 1px 1px 0 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .rating-card {
            border: 1px solid #e2e8f0;
            border-left: 3px solid #fdc974;
            border-radius: 5px;
            padding: 8px 10px;
            margin-bottom: 6px;
            background: #fafafa;
        }
        .rating-card .rating-meta { font-size: 8px; color: #94a3b8; margin-bottom: 3px; }
        .rating-card .rating-client { font-size: 9px; font-weight: bold; color: #1e293b; margin-bottom: 3px; }
        .rating-card .rating-comment { font-size: 9px; color: #475569; font-style: italic; margin-top: 3px; }

        /* ─── Desglose pausas ────────────────────────────────────────────── */
        .break-date-header {
            background: #1e293b;
            color: #fdc974;
            font-weight: bold;
            font-size: 9px;
            padding: 4px 8px;
            border-radius: 3px;
            margin-bottom: 3px;
        }

        /* ─── Timeline ───────────────────────────────────────────────────── */
        .timeline-row-break { color: #d97706; }
        .timeline-row-ok    { color: #10b981; }

        /* ─── Aviso de modo seguro ───────────────────────────────────────── */
        .safe-mode-notice {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 5px;
            padding: 8px 12px;
            margin: 10px 30px 0;
            font-size: 9px;
            color: #92400e;
        }

        /* ─── Pie de pagina ──────────────────────────────────────────────── */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #64748b;
            background-color: #ffffff;
            padding: 5px 0 4px;
            border-top: 1px solid #e2e8f0;
        }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════════════════════════════════════════════
         ENCABEZADO
    ═══════════════════════════════════════════════════════════════════════ --}}
    <div class="header">
        <div class="header-top">
            <div>
                <h1>Reporte de Rendimiento</h1>
                <div class="subtitle">Sistema T.E.R.A. &mdash; Módulo Analítico</div>
            </div>
            <div class="badge-sistema">T.E.R.A. Analytics</div>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: flex-end;">
            <div class="seller-card">
                <div class="seller-name">{{ $employee->full_name }}</div>
                <div class="seller-code">Código: {{ $employee->employee_code ?? 'N/A' }}</div>
            </div>
            <div class="period-badge">
                <strong>Periodo analizado</strong>
                {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} &mdash; {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}
                <div style="margin-top:3px; font-size:8px; color:#94a3b8;">Generado: {{ $generatedAt }}</div>
            </div>
        </div>
    </div>

    {{-- Secciones incluidas --}}
    <div class="sections-included">
        <strong>Secciones incluidas:</strong>
        @foreach($sections as $sec)
            <span class="section-tag">
                @php $sectionLabels = ['kpis' => 'KPIs', 'breaks' => 'Pausas', 'timeline' => 'Bitácora', 'clients' => 'Historial Clientes', 'ratings' => 'Calificaciones']; @endphp
                {{ $sectionLabels[$sec] ?? $sec }}
            </span>
        @endforeach
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECCIÓN 1: KPIs
    ═══════════════════════════════════════════════════════════════════════ --}}
    @if(in_array('kpis', $sections) && !empty($kpis))
    <div class="kpis-wrapper">
        <table class="kpis-grid">
            <tr>
                <td class="kpi-cell highlight">
                    <span class="kpi-label light">Calificacion</span>
                    <span class="kpi-value val-gold">
                        {{ $kpis['avg_stars'] ?: '0' }} / 5
                    </span>
                </td>
                <td class="kpi-cell">
                    <span class="kpi-label">Atendidos</span>
                    <span class="kpi-value val-green">{{ $kpis['served'] }}</span>
                </td>
                <td class="kpi-cell">
                    <span class="kpi-label">Prom. Atención</span>
                    <span class="kpi-value val-blue" style="font-size:18px;">{{ $kpis['avg_time'] }}</span>
                </td>
                <td class="kpi-cell">
                    <span class="kpi-label">Tiempo Libre</span>
                    <span class="kpi-value val-yellow" style="font-size:18px;">{{ $kpis['total_available'] }}</span>
                </td>
                <td class="kpi-cell">
                    <span class="kpi-label">En Pausas</span>
                    <span class="kpi-value val-purple" style="font-size:18px;">{{ $kpis['total_break'] }}</span>
                </td>
            </tr>
        </table>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECCIÓN 2: DESGLOSE DE PAUSAS POR DÍA
    ═══════════════════════════════════════════════════════════════════════ --}}
    @if(in_array('breaks', $sections))
    <div class="section-block">
        <div class="section-title">Desglose de Pausas por Dia</div>

        @forelse($dailyBreaks as $date => $breaks)
        {{-- Bloque separado por cada día --}}
        <div style="margin-bottom: 12px;">
            {{-- Encabezado del día --}}
            <div style="background:#1e293b; color:#fdc974; font-weight:bold; font-size:10px;
                        padding:5px 10px; border-radius:4px 4px 0 0;
                        border-bottom: 2px solid #fdc974; letter-spacing:0.5px;">
                {{ \Carbon\Carbon::parse($date)->isoFormat('dddd D [de] MMMM [de] YYYY') }}
            </div>
            {{-- Mini-tabla del día --}}
            <table class="data-table" style="margin-bottom:0; border-top:none;">
                <thead>
                    <tr>
                        <th style="width:60%; background:#334155;">Tipo de Pausa</th>
                        <th style="width:40%; background:#334155;" class="right">Tiempo Acumulado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($breaks as $reasonName => $time)
                    <tr>
                        <td style="font-weight: {{ $reasonName === 'Tiempo Disponible' ? 'bold' : 'normal' }}">
                            {{ mb_strtoupper($reasonName) }}
                        </td>
                        <td class="right {{ $reasonName === 'Tiempo Disponible' ? 'text-ok' : 'text-warn' }}"
                            style="font-weight:bold;">
                            {{ $time }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @empty
        <p class="text-muted" style="text-align:center; padding:14px;">No se registraron pausas en este periodo.</p>
        @endforelse

        {{-- ── RESUMEN TOTALES DEL PERIODO ── --}}
        @if(!empty($breakTotals))
        <div style="margin-top: 16px; border: 2px solid #1e293b; border-radius: 6px; overflow: hidden;">
            <div style="background-color:#1e293b; color:#fdc974; font-weight:bold; font-size:10px;
                        padding: 6px 12px; letter-spacing: 0.5px; text-transform: uppercase;">
                Totales acumulados del periodo
            </div>
            <table style="border-collapse: collapse; width: 100%; font-size: 9px;">
                <tbody>
                    @foreach($breakTotals as $label => $time)
                    <tr style="{{ $loop->even ? 'background-color:#f8fafc;' : 'background-color:#ffffff;' }}">
                        <td style="padding: 6px 12px; border-bottom: 1px solid #e2e8f0;
                                   font-weight: {{ $label === 'Tiempo Disponible' ? 'bold' : 'normal' }};
                                   color: {{ $label === 'Tiempo Disponible' ? '#0f172a' : '#334155' }};">
                            {{ mb_strtoupper($label) }}
                        </td>
                        <td style="padding: 6px 12px; border-bottom: 1px solid #e2e8f0;
                                   text-align: right; font-weight: bold; font-family: monospace;
                                   color: {{ $label === 'Tiempo Disponible' ? '#10b981' : '#d97706' }};">
                            {{ $time }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECCIÓN 3: BITÁCORA DE ACTIVIDAD (TIMELINE)
    ═══════════════════════════════════════════════════════════════════════ --}}
    @if(in_array('timeline', $sections))
    <div class="section-block">
        <div class="section-title">Bitacora de Actividad y Estados</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:18%;">Fecha</th>
                    <th style="width:18%;">Hora</th>
                    <th style="width:64%;">Evento Registrado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($timeline as $log)
                <tr>
                    <td>{{ $log['date'] }}</td>
                    <td>{{ $log['time'] }}</td>
                    <td class="{{ str_contains($log['status'], 'PAUSA') ? 'text-warn' : 'text-ok' }}">
                        Cambio a estado: {{ mb_strtoupper($log['status']) }}
                    </td>
                </tr>
                @empty
                    <tr><td colspan="3" class="center text-muted" style="padding:12px;">No se registró actividad en este periodo.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECCIÓN 4: HISTORIAL DE CLIENTES ATENDIDOS
    ═══════════════════════════════════════════════════════════════════════ --}}
    @if(in_array('clients', $sections))
    @if(in_array('timeline', $sections) || in_array('breaks', $sections))
    <div class="page-break"></div>
    @endif
    <div class="section-block">
        <div class="section-title">Historial de Clientes Atendidos</div>

        @if(in_array('ratings', $sections))
        {{-- Tabla extendida con calificaciones --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:10%;">Turno</th>
                    <th style="width:18%;">Cliente</th>
                    <th style="width:8%;">No. Cte.</th>
                    <th style="width:6%;" class="center">Tipo</th>
                    <th style="width:8%;" class="center">T. Espera</th>
                    <th style="width:8%;" class="center">T. Atención</th>
                    <th style="width:8%;" class="center">Estado</th>
                    <th style="width:16%;" class="center">Cal. Cliente</th>
                    <th style="width:16%;" class="center">Cal. Vendedor</th>
                    <th style="width:12%;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientRows as $row)
                <tr>
                    <td style="font-family: monospace; font-weight:bold;">{{ $row['turn'] }}</td>
                    <td>{{ $row['client'] }}</td>
                    <td>{{ $row['no_cliente'] }}</td>
                    <td class="center">
                        @if($row['type'] === 'VIP') <span class="badge-vip">VIP</span>
                        @else NORMAL @endif
                    </td>
                    <td class="center text-warn">{{ $row['wait'] }}</td>
                    <td class="center text-blue">{{ $row['serve'] }}</td>
                    <td class="center">
                        @if($row['status'] === 'RE-ATENDIDO') <span class="badge-re">RE-AT.</span>
                        @else <span class="text-ok">Normal</span> @endif
                    </td>
                    <td class="center">
                        @if($row['cr_stars'])
                            <span class="stars-row">{{ $row['cr_stars'] }}/5</span>
                            @if(!empty($row['cr_tags']))
                                <div>@foreach($row['cr_tags'] as $t)<span class="tag-pill">{{ $t }}</span>@endforeach</div>
                            @endif
                        @else <span class="text-muted">N/A</span> @endif
                    </td>
                    <td class="center">
                        @if($row['sr_stars'])
                            <span class="stars-row" style="color:#a78bfa;">{{ $row['sr_stars'] }}/5</span>
                            @if(!empty($row['sr_tags']))
                                <div>@foreach($row['sr_tags'] as $t)<span class="tag-pill" style="background:#ede9fe;color:#6d28d9;">{{ $t }}</span>@endforeach</div>
                            @endif
                        @else <span class="text-muted">N/A</span> @endif
                    </td>
                    <td>{{ $row['date'] }}</td>
                </tr>
                @empty
                    <tr><td colspan="10" class="center text-muted" style="padding:12px;">Sin registros en este periodo.</td></tr>
                @endforelse
            </tbody>
        </table>

        @else
        {{-- Tabla simple sin calificaciones --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:12%;">Turno</th>
                    <th style="width:25%;">Cliente</th>
                    <th style="width:10%;">No. Cte.</th>
                    <th style="width:8%;" class="center">Tipo</th>
                    <th style="width:10%;" class="center">T. Espera</th>
                    <th style="width:10%;" class="center">T. Atención</th>
                    <th style="width:10%;" class="center">Estado</th>
                    <th style="width:15%;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientRows as $row)
                <tr>
                    <td style="font-family:monospace; font-weight:bold;">{{ $row['turn'] }}</td>
                    <td>{{ $row['client'] }}</td>
                    <td>{{ $row['no_cliente'] }}</td>
                    <td class="center">
                        @if($row['type'] === 'VIP') <span class="badge-vip">VIP</span>
                        @else NORMAL @endif
                    </td>
                    <td class="center text-warn">{{ $row['wait'] }}</td>
                    <td class="center text-blue">{{ $row['serve'] }}</td>
                    <td class="center">
                        @if($row['status'] === 'RE-ATENDIDO') <span class="badge-re">RE-AT.</span>
                        @else <span class="text-ok">Normal</span> @endif
                    </td>
                    <td>{{ $row['date'] }}</td>
                </tr>
                @empty
                    <tr><td colspan="8" class="center text-muted" style="padding:12px;">Sin registros en este periodo.</td></tr>
                @endforelse
            </tbody>
        </table>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════════════
         SECCIÓN 5: HISTORIAL DE CALIFICACIONES (sin tabla de clientes)
    ═══════════════════════════════════════════════════════════════════════ --}}
    @if(in_array('ratings', $sections) && !in_array('clients', $sections) && !empty($ratingsHistory))
    <div class="section-block">
        <div class="section-title">Historial de Calificaciones de Clientes</div>
        @foreach($ratingsHistory as $rating)
        <div class="rating-card">
            <div class="rating-meta">{{ $rating['date'] }} &nbsp;|&nbsp; Turno: <strong>{{ $rating['turn'] }}</strong></div>
            <div class="rating-client">{{ $rating['client_name'] }}</div>
            <div class="stars-row">{{ $rating['stars'] }} / 5</div>
            @if(!empty($rating['tags']))
                <div style="margin-top:3px;">
                    @foreach($rating['tags'] as $t)<span class="tag-pill">{{ $t }}</span>@endforeach
                </div>
            @endif
            @if($rating['comment'])
                <div class="rating-comment">"{{ $rating['comment'] }}"</div>
            @endif
        </div>
        @endforeach
    </div>
    @endif

    {{-- Pie de página fijo --}}
    <div class="footer">
        Generado por Sistema T.E.R.A. &mdash; {{ $generatedAt }} &nbsp;|&nbsp; Documento confidencial de uso interno.
    </div>

</body>
</html>
