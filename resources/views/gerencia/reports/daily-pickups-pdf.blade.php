<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Diario de Resguardos</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 18px; border-bottom: 3px solid #fdc974; padding: 16px 12px; background-color: #111827; color: #fff; }
        .header h1 { margin: 0; color: #fdc974; text-transform: uppercase; font-size: 18px; }
        .header p { margin: 4px 0 0; color: #9ca3af; font-size: 11px; }
        .meta { background: #f8fafc; border: 1px dashed #cbd5e1; padding: 8px; margin-bottom: 12px; font-size: 9px; }
        .section-title { font-size: 12px; color: #111827; text-transform: uppercase; border-bottom: 2px solid #fdc974; padding-bottom: 4px; margin: 16px 0 8px; font-weight: bold; }
        .data-table { border-collapse: collapse; width: 100%; font-size: 8px; margin-bottom: 14px; }
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 5px; text-align: left; }
        .data-table th { background: #1f2937; color: #fdc974; text-transform: uppercase; }
        .data-table tr:nth-child(even) { background: #f8fafc; }
        .summary-table td { padding: 4px 8px; }
        .warning-box { background: #fef3c7; border: 2px solid #f59e0b; padding: 10px; margin-bottom: 12px; }
        .warning-box strong { color: #b45309; }
        .footer { margin-top: 20px; text-align: center; font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte Diario de Resguardos</h1>
        <p>Fecha: {{ $reportDate->format('d/m/Y') }} | Gerente: {{ $managerName }}</p>
    </div>

    <div class="meta">
        <strong>Total registrados el día:</strong> {{ $dayPickups->count() }} |
        <strong>No auditados al cierre:</strong> {{ $unauditedPickups->count() }} |
        <strong>Emitido:</strong> {{ $generatedAt->format('d/m/Y H:i:s') }}
    </div>

    @if($unauditedPickups->count() > 0)
    <div class="warning-box">
        <strong>Atención:</strong> Hay {{ $unauditedPickups->count() }} resguardo(s) sin auditar o en corrección al momento de generar este reporte.
    </div>
    @endif

    <div class="section-title">Resumen por estatus (día)</div>
    <table class="data-table summary-table">
        <thead>
            <tr><th>Estatus</th><th>Cantidad</th></tr>
        </thead>
        <tbody>
            @forelse($statusSummary as $statusName => $count)
            <tr>
                <td>{{ $statusName }}</td>
                <td>{{ $count }}</td>
            </tr>
            @empty
            <tr><td colspan="2" style="text-align:center;color:#64748b;">Sin registros del día.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">Resguardos del día</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Cliente</th>
                <th>Área</th>
                <th>Piezas</th>
                <th>Bolsas</th>
                <th>Estatus</th>
                <th>Hora</th>
                <th>Auditado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dayPickups as $pickup)
            <tr>
                <td>{{ $pickup->ticket_folio }}</td>
                <td>{{ $pickup->client_name }}</td>
                <td>{{ $pickup->department }}</td>
                <td>{{ $pickup->pieces }}</td>
                <td>{{ $pickup->bags ?? 0 }}</td>
                <td>{{ $pickup->currentStatus->name ?? 'N/A' }}</td>
                <td>{{ $pickup->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $pickup->currentStatus?->code === 'PENDING_CONFIRMATION' ? 'No' : 'Sí' }}</td>
            </tr>
            @empty
            <tr><td colspan="8" style="text-align:center;color:#64748b;">No hay resguardos registrados en esta fecha.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title" style="page-break-before: always;">No auditados al cierre</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Cliente</th>
                <th>Área</th>
                <th>Estatus</th>
                <th>Fecha registro</th>
                <th>Días pendiente</th>
            </tr>
        </thead>
        <tbody>
            @forelse($unauditedPickups as $pickup)
            <tr>
                <td>{{ $pickup->ticket_folio }}</td>
                <td>{{ $pickup->client_name }}</td>
                <td>{{ $pickup->department }}</td>
                <td>{{ $pickup->currentStatus->name ?? 'N/A' }}</td>
                <td>{{ $pickup->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $pickup->created_at->diffInDays(today()) }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;color:#10b981;">Todos los resguardos en cola están auditados o en custodia procesada.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Sistema T.E.R.A. — Reporte generado automáticamente
    </div>
</body>
</html>
