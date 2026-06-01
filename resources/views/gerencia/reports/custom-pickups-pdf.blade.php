<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Resguardos - Aromas</title>
    <style>
        @page {
            margin: 40px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #334155;
            margin: 0;
            padding: 0;
            line-height: 1.5;
        }
        .header {
            background-color: #0f172a;
            color: #ffffff;
            padding: 24px;
            border-bottom: 4px solid #eab308;
            border-radius: 8px 8px 0 0;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
        }
        .header td {
            border: none;
            padding: 0;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #fef08a;
            text-transform: uppercase;
        }
        .header p {
            margin: 4px 0 0;
            font-size: 11px;
            color: #94a3b8;
        }
        .meta-info {
            text-align: right;
            font-size: 9px;
            color: #cbd5e1;
        }
        .filter-badge {
            background-color: #1e293b;
            color: #fef08a;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: bold;
        }
        .section-title {
            font-size: 12px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 20px 0 10px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 4px;
        }
        .stats-grid {
            margin-bottom: 20px;
        }
        .stats-grid table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
        }
        .stats-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px;
            text-align: center;
        }
        .stats-card.highlight {
            background-color: #fef9c3;
            border-color: #fef08a;
        }
        .stats-card .value {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .stats-card .label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-top: 10px;
        }
        .data-table th {
            background-color: #1e293b;
            color: #fef08a;
            text-transform: uppercase;
            font-weight: 700;
            font-size: 8px;
            padding: 6px 8px;
            border: 1px solid #334155;
            text-align: left;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-custody { background-color: #d1fae5; color: #065f46; }
        .badge-delivered { background-color: #dbeafe; color: #1e40af; }
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-correction { background-color: #fee2e2; color: #991b1b; }
        .badge-default { background-color: #f1f5f9; color: #334155; }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: center;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td>
                    <h1>Reporte Personalizado de Resguardos</h1>
                    <p>Filtros aplicados: 
                        Estado: <span class="filter-badge">{{ $filters['status'] }}</span> | 
                        Área: <span class="filter-badge">{{ $filters['department'] }}</span>
                    </p>
                </td>
                <td class="meta-info">
                    <p><strong>Periodo:</strong> {{ $dateLabel }}</p>
                    <p><strong>Gerente:</strong> {{ $managerName }}</p>
                    <p><strong>Generado:</strong> {{ $generatedAt->format('d/m/Y H:i:s') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="stats-grid">
        <table>
            <tr>
                <td style="width: 25%;">
                    <div class="stats-card highlight">
                        <div class="value">{{ $totalCount }}</div>
                        <div class="label">Total Resguardos</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="stats-card">
                        <div class="value">
                            {{ $pickups->filter(fn($p) => ($p->currentStatus->code ?? '') === 'IN_CUSTODY')->count() }}
                        </div>
                        <div class="label">En Custodia</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="stats-card">
                        <div class="value">
                            {{ $pickups->filter(fn($p) => ($p->currentStatus->code ?? '') === 'DELIVERED')->count() }}
                        </div>
                        <div class="label">Entregados</div>
                    </div>
                </td>
                <td style="width: 25%;">
                    <div class="stats-card">
                        <div class="value">{{ $totalPieces }} / {{ $totalBags }}</div>
                        <div class="label">Piezas / Bolsas</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    @if($statusCounts->count() > 0)
    <div class="section-title">Resumen por Estatus</div>
    <table class="data-table" style="width: 50%; margin-bottom: 20px;">
        <thead>
            <tr>
                <th>Estatus</th>
                <th style="text-align: right;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statusCounts as $statusName => $count)
            <tr>
                <td>{{ $statusName }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $count }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="section-title">Detalle de Resguardos</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 10%;">Folio</th>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 10%;">Área</th>
                <th style="width: 12%;">Estatus</th>
                <th style="width: 8%; text-align: center;">Piezas/Bolsas</th>
                <th style="width: 12%;">Fecha Registro</th>
                <th style="width: 10%;">Permanencia</th>
                <th style="width: 8%;">Entrega</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pickups as $index => $pickup)
            <tr>
                <td style="text-align: center; font-weight: bold; color: #64748b;">{{ $index + 1 }}</td>
                <td style="font-family: monospace; font-weight: bold;">{{ $pickup->ticket_folio }}</td>
                <td>
                    <strong>{{ $pickup->client_name }}</strong>
                    @if($pickup->client_ref_id)
                        <div style="font-size: 7px; color: #64748b;">Ref: {{ $pickup->client_ref_id }}</div>
                    @endif
                </td>
                <td>{{ $pickup->department }}</td>
                <td>
                    @php
                        $code = $pickup->currentStatus->code ?? '';
                        $badgeClass = 'badge-default';
                        if ($code === 'IN_CUSTODY') $badgeClass = 'badge-custody';
                        elseif ($code === 'DELIVERED') $badgeClass = 'badge-delivered';
                        elseif (in_array($code, ['PENDING_CONFIRMATION', 'PRE_REGISTERED'])) $badgeClass = 'badge-pending';
                        elseif ($code === 'NEEDS_CORRECTION') $badgeClass = 'badge-correction';
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $pickup->currentStatus->name ?? 'N/A' }}</span>
                </td>
                <td style="text-align: center;">
                    {{ $pickup->pieces }} / {{ $pickup->bags ?? 0 }}
                </td>
                <td>{{ $pickup->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    {{ \App\Support\CustodyDurationFormatter::inQueue($pickup->created_at, $pickup->delivered_at) }}
                </td>
                <td>
                    @if($pickup->delivered_at)
                        {{ $pickup->delivered_at->format('d/m/Y H:i') }}
                        @if($pickup->receiver_name)
                            <div style="font-size: 7px; color: #64748b;">Recibe: {{ $pickup->receiver_name }}</div>
                        @endif
                    @else
                        <span style="color: #94a3b8; font-style: italic;">Pendiente</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #64748b; padding: 20px;">
                    No se encontraron resguardos con los filtros seleccionados.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistema T.E.R.A. — Reporte generado automáticamente
    </div>
</body>
</html>
