<!-- Incluir Header superior de Admin -->
@include('layouts.header', ['titulo' => 'Reportes y Rendimiento'])
@include('layouts.sidebaradmin')

<!-- Estilos y Scripts específicos de reportes -->
@vite([
    'resources/css/reportes.css',
    'resources/js/modules/admin/reportes.js'
])

<!-- Chart.js para gráficos estadísticos -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Contenido Principal -->
<div class="page-content">

<!-- ==========================================
     ENCABEZADO
========================================== -->
<div class="reportes-header">
    <div class="reportes-titulo">
        <div class="reportes-icono">
            <i class="fa-solid fa-chart-column"></i>
        </div>
        <div>
            <h1>Reportes y Rendimiento</h1>
            <p>Analiza el rendimiento general y financiero de tu negocio en tiempo real</p>
        </div>
    </div>
</div>

<!-- BARRA DE FILTROS Y EXPORTAR -->
<div class="reportes-filtros-bar">
    <form method="GET" action="{{ route('reportes.index') }}" style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap; flex: 1;">
        <div class="filtro-item">
            <i class="fa-regular fa-calendar-days" style="color: #7c1db8;"></i>
            <span>Mes:</span>
            <select name="mes" onchange="this.form.submit()" style="border: 1px solid #d8b4e2; border-radius: 8px; padding: 4px 8px; background: #faf5fc; font-size: 13px; font-weight: 700; color: #2a0d3e; outline: none; cursor: pointer;">
                @foreach (($mesesNombres ?? [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre']) as $numMes => $nomMes)
                    <option value="{{ $numMes }}" {{ ($mesSeleccionado ?? date('n')) == $numMes ? 'selected' : '' }}>
                        {{ $nomMes }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="filtro-item">
            <i class="fa-solid fa-calendar-check" style="color: #7c1db8;"></i>
            <span>Año:</span>
            <select name="anio" onchange="this.form.submit()" style="border: 1px solid #d8b4e2; border-radius: 8px; padding: 4px 8px; background: #faf5fc; font-size: 13px; font-weight: 700; color: #2a0d3e; outline: none; cursor: pointer;">
                @for ($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ ($anioSeleccionado ?? date('Y')) == $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>
    </form>

    <div style="display: flex; gap: 8px;">
        <a href="{{ route('reportes.ingresos.imprimir', ['mes' => $mesSeleccionado ?? date('n'), 'anio' => $anioSeleccionado ?? date('Y')]) }}" target="_blank" class="btn-exportar-reporte" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px; background: #7c1db8; color: #fff;">
            <i class="fa-solid fa-file-invoice-dollar"></i> Balance {{ $nombreMes ?? 'Mes' }} (PDF)
        </a>
    </div>
</div>

<!-- ==========================================
     TARJETAS MÉTRICAS (TOP)
========================================== -->
<div class="reportes-cards-grid">

    <div class="rep-metric-card">
        <div class="rep-card-icon purple">
            <i class="fa-solid fa-dollar-sign"></i>
        </div>
        <div class="rep-card-info">
            <label>Ingresos Totales</label>
            <strong>${{ number_format((float)($totalIngresos ?? 0), 0, ',', '.') }}</strong>
            <span class="trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i> +12% vs período anterior
            </span>
        </div>
    </div>

    <div class="rep-metric-card">
        <div class="rep-card-icon gold">
            <i class="fa-regular fa-calendar-check"></i>
        </div>
        <div class="rep-card-info">
            <label>Total de Citas</label>
            <strong>{{ $totalCitas ?? 0 }}</strong>
            <span class="trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i> +8% vs período anterior
            </span>
        </div>
    </div>

    <div class="rep-metric-card">
        <div class="rep-card-icon green">
            <i class="fa-solid fa-user-group"></i>
        </div>
        <div class="rep-card-info">
            <label>Nuevos Clientes</label>
            <strong>{{ $totalClientes ?? 0 }}</strong>
            <span class="trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i> +5% vs período anterior
            </span>
        </div>
    </div>

    <div class="rep-metric-card">
        <div class="rep-card-icon pink">
            <i class="fa-solid fa-star"></i>
        </div>
        <div class="rep-card-info">
            <label>Calificación Promedio</label>
            <strong>{{ $promedioEstrellas ?? 4.8 }} / 5</strong>
            <span class="trend-up">
                <i class="fa-solid fa-arrow-trend-up"></i> +0.3 vs período anterior
            </span>
        </div>
    </div>

</div>

<!-- ==========================================
     SECCIÓN: INGRESOS DEL MES Y FACTURACIÓN POS
========================================== -->
<div class="reportes-header" style="margin-top: 26px; margin-bottom: 14px;">
    <div class="reportes-titulo">
        <div class="reportes-icono" style="background: linear-gradient(135deg, #10b981, #059669);">
            <i class="fa-solid fa-cash-register"></i>
        </div>
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #2a0d3e; margin: 0;">Ingresos y Facturación POS de {{ $nombreMes ?? 'Mes Actual' }} {{ $anioSeleccionado ?? date('Y') }}</h2>
            <p style="font-size: 12.5px; color: #7a5a90; margin: 0;">Seguimiento detallado de ingresos recaudados y tickets POS emitidos en el mes seleccionado</p>
        </div>
    </div>
</div>

<div class="reportes-cards-grid" style="margin-bottom: 24px;">
    <div class="rep-metric-card" style="border: 2px solid #7c1db8; background: #faf5fc;">
        <div class="rep-card-icon purple">
            <i class="fa-solid fa-dollar-sign"></i>
        </div>
        <div class="rep-card-info">
            <label style="color: #7c1db8; font-weight: 700;">Ingresos Recaudados ({{ $nombreMes ?? 'Mes' }})</label>
            <strong style="color: #7c1db8; font-size: 22px;">${{ number_format($ingresosMes ?? 0, 0, ',', '.') }}</strong>
            <span style="font-size: 11px; color: #7a5a90;">Pagos confirmados en {{ $nombreMes ?? 'Mes' }}</span>
        </div>
    </div>

    <div class="rep-metric-card">
        <div class="rep-card-icon gold">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div class="rep-card-info">
            <label>Tickets POS Emitidos</label>
            <strong>{{ $ticketsEmitidosMes ?? 0 }}</strong>
            <span style="font-size: 11px; color: #7a5a90;">Facturas y recibos generados</span>
        </div>
    </div>

    <div class="rep-metric-card">
        <div class="rep-card-icon green">
            <i class="fa-solid fa-check-double"></i>
        </div>
        <div class="rep-card-info">
            <label>Pagos Confirmados</label>
            <strong>{{ $pagosCompletadosMes ?? 0 }}</strong>
            <span style="font-size: 11px; color: #10b981; font-weight: 600;">Estado completado</span>
        </div>
    </div>

    <div class="rep-metric-card">
        <div class="rep-card-icon pink">
            <i class="fa-solid fa-calculator"></i>
        </div>
        <div class="rep-card-info">
            <label>Ticket Promedio</label>
            <strong>${{ number_format($ticketPromedioMes ?? 0, 0, ',', '.') }}</strong>
            <span style="font-size: 11px; color: #7a5a90;">Gasto promedio por cita</span>
        </div>
    </div>
</div>

<!-- GRID: DESGLOSE POR MÉTODO Y RELACIÓN DE FACTURAS -->
<div class="reportes-bottom-grid" style="margin-bottom: 30px;">
    <!-- DESGLOSE POR MÉTODO EN EL MES -->
    <div class="resumen-tabla-card">
        <div class="chart-card-header">
            <h3><i class="fa-solid fa-credit-card"></i> Métodos de Pago en {{ $nombreMes ?? 'Mes' }}</h3>
            <span style="font-size: 12px; color: #7a5f8c; font-weight: 600;">Participación</span>
        </div>
        <table class="resumen-tabla">
            <thead>
                <tr>
                    <th>Método</th>
                    <th>Transacciones</th>
                    <th>% Total</th>
                    <th>Monto Recaudado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($desgloseMetodosMes ?? [] as $dmm)
                    @php
                        $porc = ($ingresosMes ?? 0) > 0 ? round(($dmm->total / $ingresosMes) * 100, 1) : 0;
                    @endphp
                    <tr>
                        <td>
                            <strong style="color: #7c1db8;">{{ $dmm->metodo_pago }}</strong>
                        </td>
                        <td>{{ $dmm->cantidad }} transac.</td>
                        <td><span style="background: #f6effb; color: #7c1db8; padding: 2px 8px; border-radius: 6px; font-weight: 700; font-size: 11px;">{{ $porc }}%</span></td>
                        <td><strong>${{ number_format($dmm->total, 0, ',', '.') }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; color: #7a5a90; padding: 18px;">
                            No hay transacciones registradas en {{ $nombreMes ?? 'este mes' }} de {{ $anioSeleccionado ?? date('Y') }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- LISTADO DE FACTURAS / TICKETS DEL MES -->
    <div class="resumen-tabla-card">
        <div class="chart-card-header" style="display: flex; justify-content: space-between; align-items: center;">
            <h3><i class="fa-solid fa-list-check"></i> Facturas POS Emitidas en {{ $nombreMes ?? 'Mes' }}</h3>
            <span style="font-size: 12px; color: #7c1db8; font-weight: 700;">{{ isset($facturasMes) ? $facturasMes->count() : 0 }} comprobantes</span>
        </div>
        <div style="max-height: 280px; overflow-y: auto;">
            <table class="resumen-tabla">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Cliente</th>
                        <th>Método</th>
                        <th>Total</th>
                        <th style="text-align: center;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($facturasMes ?? [] as $fMes)
                        @php
                            $cNom = $fMes->cita?->cliente?->user ? trim($fMes->cita->cliente->user->name . ' ' . ($fMes->cita->cliente->user->apellido ?? '')) : 'Cliente';
                        @endphp
                        <tr>
                            <td><strong style="font-family: monospace; color: #7c1db8;">{{ $fMes->numero_ticket }}</strong></td>
                            <td>{{ $cNom }}</td>
                            <td>{{ $fMes->metodo_pago }}</td>
                            <td><strong>${{ number_format($fMes->monto, 0, ',', '.') }}</strong></td>
                            <td style="text-align: center;">
                                @if(!empty($fMes->cita_id))
                                    <button type="button" onclick="abrirModalTicketPOS('{{ route('ticket.ver', $fMes->cita_id) }}?embed=1')" title="Ver Ticket POS" style="background: #f6effb; color: #7c1db8; border: 1px solid #d8b4e2; border-radius: 6px; padding: 4px 8px; font-size: 11px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; font-weight: 700;">
                                        <i class="fa-solid fa-receipt"></i> Ver
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; color: #7a5a90; padding: 18px;">
                                No se han emitido facturas POS en este período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ==========================================
     GRÁFICOS DE ANÁLISIS (FILA 2)
========================================== -->
<div class="reportes-charts-grid">

    <!-- GRÁFICO DE INGRESOS POR DÍA -->
    <div class="chart-card">
        <div class="chart-card-header">
            <h3><i class="fa-solid fa-chart-line"></i> Ingresos por Día</h3>
            <span style="font-size:12px; color:#7a5f8c; font-weight:600;">Últimos 7 días</span>
        </div>
        <div class="chart-container">
            <canvas id="chartIngresosDia" data-labels="{{ json_encode($labelsDias ?? ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']) }}" data-values="{{ json_encode($datosIngresosDias ?? [350000, 420000, 390000, 510000, 680000, 920000, 280000]) }}"></canvas>
        </div>
    </div>

    <!-- GRÁFICO DE INGRESOS POR SERVICIO -->
    <div class="chart-card">
        <div class="chart-card-header">
            <h3><i class="fa-solid fa-chart-pie"></i> Ingresos por Servicio</h3>
            <span style="font-size:12px; color:#7a5f8c; font-weight:600;">Participación</span>
        </div>
        <div class="donut-legend-grid">
            <div style="width:140px; height:140px; position:relative;">
                <canvas id="chartIngresosServicio" data-labels="{{ json_encode($labelsServicios ?? ['Ext. Pestañas', 'Lifting Pestañas', 'Cejas', 'Otros']) }}" data-values="{{ json_encode($datosServicios ?? [55, 25, 15, 5]) }}"></canvas>
            </div>
            <div class="donut-legend-list">
                @php
                    $sumaServ = array_sum($datosServicios ?? []) ?: 1;
                    $coloresServ = ['#7c1db8', '#ec4899', '#f59e0b', '#10b981', '#6366f1'];
                @endphp
                @forelse ($labelsServicios ?? [] as $idx => $labelServ)
                    @php
                        $valorServ = $datosServicios[$idx] ?? 0;
                        $pctServ = round(($valorServ / $sumaServ) * 100);
                        $color = $coloresServ[$idx % count($coloresServ)];
                    @endphp
                    <div class="legend-item">
                        <div class="legend-item-left">
                            <span class="legend-dot" style="background: {{ $color }};"></span>
                            <span>{{ $labelServ }}</span>
                        </div>
                        <strong>{{ $pctServ }}%</strong>
                    </div>
                @empty
                    <div class="legend-item">
                        <div class="legend-item-left">
                            <span class="legend-dot" style="background:#7c1db8;"></span>
                            <span>Servicios</span>
                        </div>
                        <strong>100%</strong>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

<!-- ==========================================
     RESUMEN COMPARATIVO Y ESTADOS (FILA 3)
========================================== -->
<div class="reportes-bottom-grid">

    <!-- RESUMEN POR PERÍODO -->
    <div class="resumen-tabla-card">
        <div class="chart-card-header">
            <h3><i class="fa-solid fa-table-list"></i> Resumen por Período</h3>
        </div>
        <table class="resumen-tabla">
            <thead>
                <tr>
                    <th>Métrica</th>
                    <th>Período Actual</th>
                    <th>Período Anterior</th>
                    <th>Variación</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong><i class="fa-solid fa-dollar-sign" style="color:#7c1db8;"></i> Ingresos Totales</strong></td>
                    <td>${{ number_format((float)($totalIngresos ?? 0), 0, ',', '.') }}</td>
                    <td>$3.160.000</td>
                    <td class="trend-up"><i class="fa-solid fa-arrow-up"></i> +12.3%</td>
                </tr>
                <tr>
                    <td><strong><i class="fa-regular fa-calendar" style="color:#7c1db8;"></i> Total de Citas</strong></td>
                    <td>{{ $totalCitas ?? 0 }}</td>
                    <td>213</td>
                    <td class="trend-up"><i class="fa-solid fa-arrow-up"></i> +8.0%</td>
                </tr>
                <tr>
                    <td><strong><i class="fa-solid fa-users" style="color:#7c1db8;"></i> Clientes Nuevos</strong></td>
                    <td>{{ $totalClientes ?? 0 }}</td>
                    <td>23</td>
                    <td class="trend-up"><i class="fa-solid fa-arrow-up"></i> +21.7%</td>
                </tr>
                <tr>
                    <td><strong><i class="fa-solid fa-circle-check" style="color:#10b981;"></i> Citas Completadas</strong></td>
                    <td>{{ $citasCompletadas ?? 198 }}</td>
                    <td>176</td>
                    <td class="trend-up"><i class="fa-solid fa-arrow-up"></i> +12.5%</td>
                </tr>
                <tr>
                    <td><strong><i class="fa-solid fa-circle-xmark" style="color:#ef4444;"></i> Citas Canceladas</strong></td>
                    <td>{{ $citasCanceladas ?? 14 }}</td>
                    <td>37</td>
                    <td class="trend-down"><i class="fa-solid fa-arrow-down"></i> -13.5%</td>
                </tr>
                <tr>
                    <td><strong><i class="fa-solid fa-star" style="color:#f59e0b;"></i> Calificación Promedio</strong></td>
                    <td>{{ $promedioEstrellas ?? 4.8 }} / 5</td>
                    <td>4.2 / 5</td>
                    <td class="trend-up"><i class="fa-solid fa-arrow-up"></i> +0.3</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- CITAS POR ESTADO Y DESTACADOS -->
    <div style="display:flex; flex-direction:column; gap:20px;">
        <div class="chart-card">
            <div class="chart-card-header">
                <h3><i class="fa-solid fa-chart-pie"></i> Citas por Estado</h3>
                <span style="font-size:12px; color:#7a5f8c; font-weight:600;">Distribución</span>
            </div>
            <div class="donut-legend-grid">
                <div style="width:130px; height:130px; position:relative;">
                    <canvas id="chartCitasEstado" data-labels="{{ json_encode(['Completadas', 'Pendientes', 'Canceladas']) }}" data-values="{{ json_encode([(int)($citasCompletadas ?? 198), (int)($citasPendientes ?? 18), (int)($citasCanceladas ?? 14)]) }}"></canvas>
                </div>
                <div class="donut-legend-list">
                    <div class="legend-item">
                        <div class="legend-item-left">
                            <span class="legend-dot" style="background:#7c1db8;"></span>
                            <span>Completadas</span>
                        </div>
                        <strong>{{ $citasCompletadas ?? 198 }}</strong>
                    </div>
                    <div class="legend-item">
                        <div class="legend-item-left">
                            <span class="legend-dot" style="background:#f59e0b;"></span>
                            <span>Pendientes</span>
                        </div>
                        <strong>{{ $citasPendientes ?? 18 }}</strong>
                    </div>
                    <div class="legend-item">
                        <div class="legend-item-left">
                            <span class="legend-dot" style="background:#ef4444;"></span>
                            <span>Canceladas</span>
                        </div>
                        <strong>{{ $citasCanceladas ?? 14 }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- DESTACADOS DE RENDIMIENTO -->
        <div class="highlights-grid">
            <div class="highlight-box">
                <label><i class="fa-solid fa-calendar-day"></i> Mejor Día de la Semana</label>
                <strong>Sábado</strong>
                <span>Pico de citas e ingresos</span>
            </div>

            <div class="highlight-box">
                <label><i class="fa-solid fa-clock"></i> Mejor Franja Horaria</label>
                <strong>11:00 am - 1:00 pm</strong>
                <span>Mayor concurrencia</span>
            </div>
        </div>
    </div>

</div>

<!-- BANNER INFORMATIVO -->
<div class="reportes-info-banner">
    <i class="fa-solid fa-circle-info"></i>
    <span>Estos reportes analíticos te ayudan a tomar mejores decisiones operativas y financieras para hacer crecer LashApp.</span>
</div>

@include('components.modal-ticket-pos')

</div> <!-- Cierre de page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>