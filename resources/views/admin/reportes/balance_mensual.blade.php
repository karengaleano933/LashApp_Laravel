<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos - {{ $nombreMes }} {{ $anioSeleccionado }} | {{ $configuracion->nombre_estudio ?? 'Oh La Lash Studio' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Mono:wght@700&display=swap" rel="stylesheet">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            color: #1e293b;
            padding: 30px 20px;
            -webkit-font-smoothing: antialiased;
        }

        .report-page-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }

        /* ── BARRA SUPERIOR DE ACCIONES ── */
        .report-toolbar {
            max-width: 900px;
            margin: 0 auto 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-print {
            background: #7c1db8;
            color: #ffffff;
        }

        .btn-print:hover {
            background: #69179d;
        }

        .btn-back {
            background: #e2e8f0;
            color: #475569;
        }

        .btn-back:hover {
            background: #cbd5e1;
        }

        /* ── CABECERA DEL REPORTE ── */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            border-bottom: 2px solid #7c1db8;
            margin-bottom: 24px;
        }

        .studio-title {
            font-size: 24px;
            font-weight: 800;
            color: #7c1db8;
            margin-bottom: 4px;
        }

        .studio-meta {
            font-size: 12px;
            color: #64748b;
            line-height: 1.5;
        }

        .report-badge-box {
            text-align: right;
        }

        .report-badge {
            background: #f6effb;
            color: #7c1db8;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            display: inline-block;
            margin-bottom: 6px;
        }

        .report-period {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
        }

        .report-emission-date {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }

        /* ── CARDS RESUMEN ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 28px;
        }

        .kpi-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            text-align: left;
        }

        .kpi-card label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            color: #64748b;
            display: block;
            margin-bottom: 6px;
        }

        .kpi-card strong {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            display: block;
        }

        .kpi-card.highlight {
            background: #faf5fc;
            border-color: #d8b4e2;
        }

        .kpi-card.highlight strong {
            color: #7c1db8;
        }

        /* ── SECCIONES Y TABLAS ── */
        .section-title {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 28px;
        }

        .report-table th {
            background: #f1f5f9;
            color: #475569;
            font-weight: 700;
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid #cbd5e1;
        }

        .report-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .report-table tr:last-child td {
            border-bottom: none;
        }

        .report-table .num-col {
            text-align: right;
            font-weight: 700;
        }

        .badge-method {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            background: #f6effb;
            color: #7c1db8;
            font-size: 11px;
            font-weight: 600;
        }

        .badge-status-completed {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            background: #dcfce7;
            color: #15803d;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-status-pending {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 6px;
            background: #fef3c7;
            color: #b45309;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        /* ── PIE DE FIRMAS Y CIERRE ── */
        .report-signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px dashed #cbd5e1;
        }

        .sign-block {
            text-align: center;
            width: 240px;
        }

        .sign-line {
            border-top: 1px solid #64748b;
            margin-bottom: 8px;
        }

        .sign-title {
            font-size: 12px;
            font-weight: 700;
            color: #334155;
        }

        .sign-subtitle {
            font-size: 11px;
            color: #64748b;
        }

        /* ── ESTILOS DE IMPRESIÓN (A4/CARTA) ── */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }

            .report-toolbar {
                display: none !important;
            }

            .report-page-container {
                box-shadow: none !important;
                border: none !important;
                padding: 20px 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body>

    <!-- BARRA DE ACCIONES (Pantalla) -->
    <div class="report-toolbar">
        <a href="{{ route('reportes.index') }}?mes={{ $mesSeleccionado }}&anio={{ $anioSeleccionado }}" class="btn-action btn-back">
            <i class="fa-solid fa-arrow-left"></i> Volver a Reportes
        </a>
        <button type="button" class="btn-action btn-print" onclick="window.print()">
            <i class="fa-solid fa-print"></i> Imprimir Reporte / Guardar PDF
        </button>
    </div>

    <!-- DOCUMENTO PRINCIPAL -->
    <div class="report-page-container">

        <!-- CABECERA -->
        <div class="report-header">
            <div>
                <div class="studio-title">{{ $configuracion->nombre_estudio ?? 'Oh La Lash Studio' }}</div>
                <div class="studio-meta">
                    NIT / RUT: 901.458.789-2 &bull; Sistema POS Financiero<br>
                    {{ $configuracion->direccion ?? 'Calle 123 #45-67, Local 2, Medellín, Colombia' }}<br>
                    Teléfono: {{ $configuracion->telefono ?? '+57 300 123 4567' }} &bull; Correo: {{ $configuracion->email_contacto ?? 'info@ohlalash.com' }}
                </div>
            </div>

            <div class="report-badge-box">
                <span class="report-badge"><i class="fa-solid fa-file-invoice-dollar"></i> BALANCE MENSUAL</span>
                <div class="report-period">{{ $nombreMes }} {{ $anioSeleccionado }}</div>
                <div class="report-emission-date">Generado el {{ now()->format('d/m/Y h:i A') }}</div>
            </div>
        </div>

        <!-- TARJETAS RESUMEN (KPIS) -->
        <div class="kpi-grid">
            <div class="kpi-card highlight">
                <label><i class="fa-solid fa-dollar-sign"></i> Ingresos Totales</label>
                <strong>${{ number_format($ingresosMes, 0, ',', '.') }}</strong>
            </div>

            <div class="kpi-card">
                <label><i class="fa-solid fa-receipt"></i> Tickets POS Emitidos</label>
                <strong>{{ $ticketsEmitidosMes }}</strong>
            </div>

            <div class="kpi-card">
                <label><i class="fa-solid fa-check-double"></i> Pagos Confirmados</label>
                <strong>{{ $pagosCompletadosMes }}</strong>
            </div>

            <div class="kpi-card">
                <label><i class="fa-solid fa-chart-line"></i> Ticket Promedio</label>
                <strong>${{ number_format($ticketPromedioMes, 0, ',', '.') }}</strong>
            </div>
        </div>

        <!-- DESGLOSE POR MÉTODO DE PAGO -->
        <div class="section-title">
            <i class="fa-solid fa-credit-card" style="color: #7c1db8;"></i> Desglose por Método de Pago en {{ $nombreMes }}
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th>Transacciones</th>
                    <th>Participación</th>
                    <th class="num-col">Monto Recaudado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($desgloseMetodosMes as $dm)
                    @php
                        $porcentaje = $ingresosMes > 0 ? round(($dm->total / $ingresosMes) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>
                            <span class="badge-method">{{ $dm->metodo_pago }}</span>
                        </td>
                        <td>{{ $dm->cantidad }} transacciones</td>
                        <td>{{ $porcentaje }}%</td>
                        <td class="num-col">${{ number_format($dm->total, 0, ',', '.') }} COP</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8;">
                            No hay transacciones registradas para este mes.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- LIBRO DIARIO DE TICKETS / FACTURAS POS -->
        <div class="section-title">
            <i class="fa-solid fa-list-check" style="color: #7c1db8;"></i> Relación Detallada de Facturación y Tickets POS
        </div>

        <table class="report-table">
            <thead>
                <tr>
                    <th>N° Ticket</th>
                    <th>Fecha / Hora</th>
                    <th>Cliente</th>
                    <th>Servicio / Tratamiento</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th class="num-col">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($facturasMes as $fac)
                    @php
                        $cliente = $fac->cita?->cliente?->user ? trim($fac->cita->cliente->user->name . ' ' . ($fac->cita->cliente->user->apellido ?? '')) : 'Cliente';
                        $servicio = $fac->cita?->servicio?->nombre ?? 'Servicio';
                        $ticketNumero = $fac->numero_ticket;
                        $fechaPago = $fac->fecha_pago ? $fac->fecha_pago->format('d/m/Y h:i A') : 'N/A';
                    @endphp
                    <tr>
                        <td><strong style="font-family:'Space Mono', monospace; color:#7c1db8;">{{ $ticketNumero }}</strong></td>
                        <td>{{ $fechaPago }}</td>
                        <td>{{ $cliente }}</td>
                        <td>{{ $servicio }}</td>
                        <td><span class="badge-method">{{ $fac->metodo_pago }}</span></td>
                        <td>
                            <span class="{{ $fac->estado == 'completado' ? 'badge-status-completed' : 'badge-status-pending' }}">
                                {{ ucfirst($fac->estado) }}
                            </span>
                        </td>
                        <td class="num-col">${{ number_format($fac->monto, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px; color: #94a3b8;">
                            No se generaron tickets en el mes de {{ $nombreMes }} de {{ $anioSeleccionado }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($facturasMes->count() > 0)
                <tfoot>
                    <tr style="background: #f8fafc; font-weight: 800; border-top: 2px solid #cbd5e1;">
                        <td colspan="6" style="text-align: right; font-size: 13px; color: #0f172a;">TOTAL RECAUDADO EN EL MES:</td>
                        <td class="num-col" style="font-size: 14px; color: #7c1db8;">${{ number_format($ingresosMes, 0, ',', '.') }} COP</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <!-- SECCIÓN DE FIRMAS -->
        <div class="report-signatures">
            <div class="sign-block">
                <div class="sign-line"></div>
                <div class="sign-title">Administración / Gerencia</div>
                <div class="sign-subtitle">{{ $configuracion->nombre_estudio ?? 'Oh La Lash Studio' }}</div>
            </div>

            <div class="sign-block">
                <div class="sign-line"></div>
                <div class="sign-title">Control de Caja & POS</div>
                <div class="sign-subtitle">Verificado y Auditado</div>
            </div>
        </div>

    </div>

</body>
</html>
