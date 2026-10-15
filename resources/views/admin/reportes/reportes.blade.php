<!-- Incluir Header superior de Admin -->
@include('layouts.header', ['titulo' => 'Reportes y Rendimiento'])
@include('layouts.sidebaradmin')

<!-- Estilos específicos de reportes -->
@vite(['resources/css/reportes.css'])

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
    <div class="filtro-item">
        <i class="fa-regular fa-calendar-days"></i>
        <span>Período:</span>
        <input type="text" value="{{ now()->subMonth()->format('d/m/Y') }} - {{ now()->format('d/m/Y') }}" readonly>
    </div>

    <div class="filtro-item">
        <i class="fa-solid fa-code-compare"></i>
        <span>Comparar con:</span>
        <select>
            <option value="anterior">Período anterior</option>
            <option value="ano_anterior">Año anterior</option>
        </select>
    </div>

    <button type="button" class="btn-exportar-reporte" onclick="window.print()">
        <i class="fa-solid fa-file-pdf"></i> Exportar Reporte
    </button>
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
            <canvas id="chartIngresosDia"></canvas>
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
                <canvas id="chartIngresosServicio"></canvas>
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
                    <canvas id="chartCitasEstado"></canvas>
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

</div> <!-- Cierre de page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

<!-- JAVASCRIPT INICIALIZACIÓN DE GRÁFICOS -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Gráfico Ingresos por Día (Línea)
    const ctxIngresos = document.getElementById('chartIngresosDia');
    if (ctxIngresos) {
        new Chart(ctxIngresos, {
            type: 'line',
            data: {
                labels: {!! json_encode($labelsDias ?? ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']) !!},
                datasets: [{
                    label: 'Ingresos ($)',
                    data: {!! json_encode($datosIngresosDias ?? [350000, 420000, 390000, 510000, 680000, 920000, 280000]) !!},
                    borderColor: '#7c1db8',
                    backgroundColor: 'rgba(124, 29, 184, 0.08)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#7c1db8',
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + (value / 1000) + 'k';
                            }
                        },
                        grid: { color: '#f0e6f8' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // 2. Gráfico Ingresos por Servicio (Dona)
    const ctxServicios = document.getElementById('chartIngresosServicio');
    if (ctxServicios) {
        new Chart(ctxServicios, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($labelsServicios ?? ['Ext. Pestañas', 'Lifting Pestañas', 'Cejas', 'Otros']) !!},
                datasets: [{
                    data: {!! json_encode($datosServicios ?? [55, 25, 15, 5]) !!},
                    backgroundColor: ['#7c1db8', '#ec4899', '#f59e0b', '#10b981', '#6366f1'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    }

    // 3. Gráfico Citas por Estado (Dona)
    const ctxCitas = document.getElementById('chartCitasEstado');
    if (ctxCitas) {
        new Chart(ctxCitas, {
            type: 'doughnut',
            data: {
                labels: ['Completadas', 'Pendientes', 'Canceladas'],
                datasets: [{
                    data: [{{ (int)($citasCompletadas ?? 198) }}, {{ (int)($citasPendientes ?? 18) }}, {{ (int)($citasCanceladas ?? 14) }}],
                    backgroundColor: ['#7c1db8', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '70%'
            }
        });
    }
});
</script>
</body>
</html>