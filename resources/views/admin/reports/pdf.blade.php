<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte TERA</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0;}
        
        /* Encabezado con estilo T.E.R.A */
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #fdc974; padding-bottom: 15px; background-color: #111827; color: #ffffff; padding-top: 20px;}
        .header h1 { margin: 0; color: #fdc974; text-transform: uppercase; font-size: 22px; letter-spacing: 1px;}
        .header p { margin: 5px 0 0; color: #9ca3af; font-size: 12px;}
        
        /* Sección de Filtros Activos */
        .filters-container { background-color: #f8fafc; border: 1px dashed #cbd5e1; padding: 10px; margin-bottom: 15px; border-radius: 5px; font-size: 10px; color: #475569;}
        .filters-container strong { color: #0f172a; }

        /* Tarjetas de Métricas (Para Vendedores) */
        .metrics-table { width: 100%; margin-bottom: 15px; text-align: center; border-collapse: separate; border-spacing: 15px 0; }
        .metrics-table td { background-color: #f1f5f9; padding: 15px; border-radius: 8px; border: 1px solid #e2e8f0; width: 33%; box-shadow: 0 1px 3px rgba(0,0,0,0.1);}
        .metric-title { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: bold; display: block; margin-bottom: 5px; }
        .metric-value { font-size: 28px; font-weight: bold; }
        .val-green { color: #10b981; }
        .val-blue { color: #3b82f6; }
        .val-yellow { color: #f59e0b; }

        .section-title { font-size: 13px; color: #111827; text-transform: uppercase; border-bottom: 2px solid #fdc974; padding-bottom: 5px; margin-top: 20px; margin-bottom: 10px; font-weight: bold;}

        /* Tabla principal ajustada para más columnas */
        .data-table { border-collapse: collapse; width: 100%; font-size: 9px; margin-bottom: 20px;}
        .data-table th, .data-table td { border: 1px solid #cbd5e1; padding: 6px; text-align: left; }
        .data-table th { background-color: #1f2937; color: #fdc974; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;}
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .badge-reattended { color: #d97706; font-weight: bold; background-color: #fef3c7; padding: 2px 4px; border-radius: 3px;}
        .text-warning { color: #d97706; font-weight: bold; }
        .text-success { color: #10b981; font-weight: bold; }

        .footer { position: fixed; bottom: -20px; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; padding-top: 10px; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE DE {{ strtoupper($type === 'clients' ? 'Clientes Atendidos' : ($type === 'employee' ? 'Rendimiento de Vendedor' : 'Abandonos')) }}</h1>
        <p>Periodo Generado: {{ \Carbon\Carbon::parse($start)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($end)->format('d/m/Y') }}</p>
    </div>

    {{-- Mostrar filtros si existen en el request (Especial para el reporte de clientes) --}}
    @if($type === 'clients' && (request('search_client') || request('client_type')))
        <div class="filters-container">
            <strong>Filtros aplicados en este reporte:</strong>
            @if(request('search_client')) 
                | <strong>Búsqueda:</strong> "{{ request('search_client') }}" 
            @endif
            @if(request('client_type')) 
                | <strong>Tipo de Cliente:</strong> {{ request('client_type') }} 
            @endif
            @if(request('seller_id_filter')) 
                | <strong>Vendedor filtrado:</strong> (Por ID de sistema: {{ request('seller_id_filter') }}) 
            @endif
        </div>
    @endif

    @if($type === 'employee')
        <div style="margin-bottom: 15px; text-align: center;">
            <h3 style="font-size: 16px; margin: 0; color: #111827; text-transform: uppercase;">Vendedor: <span style="color: #3b82f6;">{{ $employee->full_name }}</span></h3>
        </div>

        <table class="metrics-table">
            <tr>
                <td>
                    <span class="metric-title">Clientes Atendidos</span>
                    <span class="metric-value val-green">{{ $stats['served'] }}</span>
                </td>
                <td>
                    <span class="metric-title">Promedio de Atención</span>
                    <span class="metric-value val-blue">{{ $stats['avg_time'] }}</span>
                </td>
                <td>
                    <span class="metric-title">Tiempo en Pausas</span>
                    <span class="metric-value val-yellow">{{ $stats['break_time'] }}</span>
                </td>
            </tr>
        </table>

        <div class="section-title">Tiempos Acumulados de Pausas (Por Día)</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Fecha</th>
                    <th style="width: 50%;">Tipo de Pausa</th>
                    <th style="width: 30%;">Tiempo Acumulado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dailyBreaks as $date => $breaks)
                    @foreach($breaks as $reasonName => $time)
                        <tr>
                            <td><strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong></td>
                            <td>{{ mb_strtoupper($reasonName) }}</td>
                            <td class="text-warning">{{ $time }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #64748b; padding: 15px;">No registró pausas en este periodo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title">Bitácora de Actividad y Estados</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Fecha</th>
                    <th style="width: 15%;">Hora</th>
                    <th style="width: 70%;">Evento Registrado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($timeline as $log)
                    <tr>
                        <td>{{ $log['date'] }}</td>
                        <td>{{ $log['time'] }}</td>
                        <td class="{{ str_contains($log['status'], 'PAUSA') ? 'text-warning' : 'text-success' }}">
                            Cambio a estado: {{ mb_strtoupper($log['status']) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" style="text-align: center; color: #64748b; padding: 15px;">No se registró actividad en este periodo.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="section-title" style="page-break-before: always;">Historial de Clientes Atendidos</div>
    @endif

    {{-- TABLA PRINCIPAL DINÁMICA --}}
    <table class="data-table">
        <thead>
            <tr>
                @foreach($headers as $header)
                    <th>{{ mb_strtoupper($header) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $cell)
                        <td class="{{ $cell === 'RE-ATENDIDO' ? 'badge-reattended' : '' }}">{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" style="text-align: center; color: #64748b; padding: 20px;">No hay registros en esta tabla con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado por Sistema T.E.R.A. - Fecha de emisión: {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>