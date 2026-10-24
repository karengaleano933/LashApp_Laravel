<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Mis Citas'])
@include('layouts.sidebarcliente')

@vite([
    'resources/css/miscitas.css',
    'resources/js/modules/clientes/miscitas.js'
])

@php
    use Carbon\Carbon;

    $citasListado = [];

    // Mapear citas reales de la Base de Datos
    if (isset($citas) && $citas->count() > 0) {
        $citasListado = $citas->map(function ($c) {
            $fechaObj = $c->fecha ? Carbon::parse($c->fecha) : Carbon::today();
            $img = asset('img/volumen_ruso.jpg');
            if (!empty($c->servicio?->imagen_url)) {
                $img = filter_var($c->servicio->imagen_url, FILTER_VALIDATE_URL) 
                    ? $c->servicio->imagen_url 
                    : asset($c->servicio->imagen_url);
            } else {
                $sNom = strtolower($c->servicio?->nombre ?? '');
                if (str_contains($sNom, 'ceja') || str_contains($sNom, 'henna')) {
                    $img = asset('img/diseno_cejas.jpg');
                } elseif (str_contains($sNom, 'clásica') || str_contains($sNom, 'clasica')) {
                    $img = asset('img/extensiones_clasicas.jpg');
                } elseif (str_contains($sNom, 'mani') || str_contains($sNom, 'semi')) {
                    $img = asset('img/manicure_semi.jpg');
                }
            }

            $profesional = $c->trabajador?->user 
                ? trim($c->trabajador->user->name . ' ' . ($c->trabajador->user->apellido ?? '')) 
                : 'Lorena Ayala';

            $estadoLower = strtolower($c->estado ?? 'pendiente');
            $estadoLabel = match ($estadoLower) {
                'confirmada', 'confirmado' => 'Confirmada',
                'completada', 'completado', 'finalizado' => 'Completada',
                'cancelada', 'cancelado' => 'Cancelada',
                default => 'Pendiente',
            };

            // Determinar grupos de pestañas
            $hoy = Carbon::today();
            $grupos = [];
            if ($estadoLower === 'cancelado' || $estadoLower === 'cancelada') {
                $grupos[] = 'canceladas';
            } elseif (in_array($estadoLower, ['finalizado', 'completado', 'completada'])) {
                $grupos[] = 'completadas';
            } else {
                if ($fechaObj->greaterThanOrEqualTo($hoy) || in_array($estadoLower, ['confirmado', 'confirmada'])) {
                    $grupos[] = 'proximas';
                }
                if ($estadoLower === 'pendiente') {
                    $grupos[] = 'pendientes';
                }
            }
            if (empty($grupos)) {
                $grupos[] = 'proximas';
            }

            return [
                'id' => $c->id,
                'servicio' => $c->servicio?->nombre ?? 'Servicio de Belleza',
                'servicio_id' => $c->servicio_id ?? 1,
                'precio' => (float)($c->pago?->monto ?? $c->servicio?->precio ?? 130000),
                'profesional' => $profesional,
                'ubicacion' => 'Oh La Lash Studio',
                'fecha_fmt' => $fechaObj->format('d/m/Y'),
                'fecha_iso' => $fechaObj->format('Y-m-d'),
                'hora_fmt' => $c->hora_inicio ? Carbon::parse($c->hora_inicio)->format('h:i a') : '10:00 am',
                'estado_slug' => $estadoLower,
                'estado_label' => $estadoLabel,
                'grupo' => implode(' ', $grupos),
                'img' => $img,
                'metodo_pago' => $c->pago?->metodo_pago ?? 'Tarjeta',
            ];
        })->toArray();
    }

    $fechasCitasJson = json_encode(array_values(array_unique(array_column($citasListado, 'fecha_iso'))));
@endphp

<!-- Contenido Principal -->
<div class="page-content" id="misCitasContainer" 
    data-fechas-citas="{{ $fechasCitasJson ?? '[]' }}" 
    data-session-success="{{ session('success') ?? '' }}">

    <!-- ══════════════════════════════════════════
         TOP HEADER: MIS CITAS
    ══════════════════════════════════════════ -->
    <div class="miscitas-header">
        <div class="miscitas-header-icon">
            <i class="fa-regular fa-calendar-check"></i>
        </div>
        <div class="miscitas-header-text">
            <h1>Mis Citas</h1>
            <p>Consulta y gestiona todas tus citas</p>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         BARRA DE PESTAÑAS (Próximas, Pendientes, Completadas, Canceladas)
    ══════════════════════════════════════════ -->
    <div class="miscitas-tabs-bar">
        <button type="button" class="miscitas-tab-btn active" data-tab="proximas" onclick="cambiarPestanaCitas('proximas', this)">
            <i class="fa-regular fa-calendar"></i> Próximas
        </button>
        <button type="button" class="miscitas-tab-btn" data-tab="pendientes" onclick="cambiarPestanaCitas('pendientes', this)">
            <i class="fa-regular fa-clock"></i> Pendientes
        </button>
        <button type="button" class="miscitas-tab-btn" data-tab="completadas" onclick="cambiarPestanaCitas('completadas', this)">
            <i class="fa-regular fa-circle-check"></i> Completadas
        </button>
        <button type="button" class="miscitas-tab-btn" data-tab="canceladas" onclick="cambiarPestanaCitas('canceladas', this)">
            <i class="fa-regular fa-circle-xmark"></i> Canceladas
        </button>
    </div>

    <!-- ══════════════════════════════════════════
         LAYOUT PRINCIPAL (2 COLUMNAS)
    ══════════════════════════════════════════ -->
    <div class="miscitas-layout-grid">

        <!-- COLUMNA IZQUIERDA: LISTA DE CITAS -->
        <div>
            <div class="miscitas-section-title" id="tituloPestanaActiva">
                Próximas citas
            </div>

            <div class="citas-cards-list" id="listaCitasContenedor">
                @foreach ($citasListado as $item)
                    <div class="cita-card-box" 
                         data-tab-grupo="{{ $item['grupo'] }}"
                         data-id="{{ $item['id'] }}"
                         style="{{ str_contains($item['grupo'], 'proximas') ? 'display: block;' : 'display: none;' }}">

                        <div class="cita-card-main">
                            <div class="cita-card-img-wrap">
                                <img src="{{ $item['img'] }}" alt="{{ $item['servicio'] }}" class="cita-card-img" loading="lazy">
                                <div class="cita-card-img-arrow">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </div>
                            </div>

                            <div class="cita-card-content">
                                <div class="cita-card-top-row">
                                    <span class="badge-cita-status {{ $item['estado_slug'] }}">
                                        {{ $item['estado_label'] }}
                                    </span>
                                    <div class="cita-card-price">
                                        ${{ number_format($item['precio'], 0, ',', '.') }}
                                    </div>
                                </div>

                                <div class="cita-card-title">
                                    {{ $item['servicio'] }}
                                </div>

                                <div class="cita-card-meta">
                                    <span><i class="fa-regular fa-user"></i> {{ $item['profesional'] }}</span>
                                    <span><i class="fa-solid fa-location-dot"></i> {{ $item['ubicacion'] }}</span>
                                    <span><i class="fa-regular fa-calendar"></i> {{ $item['fecha_fmt'] }}</span>
                                    <span><i class="fa-regular fa-clock"></i> {{ $item['hora_fmt'] }}</span>
                                </div>

                                <div class="cita-card-notice">
                                    <i class="fa-solid fa-circle-info"></i> Te recomendamos llegar 10 minutos antes de tu cita.
                                </div>
                            </div>
                        </div>

                        <div class="cita-card-actions">
                            <button type="button" class="btn-card-action" onclick="abrirModalDetalle('{{ $item['id'] }}', '{{ addslashes($item['servicio']) }}', '{{ addslashes($item['profesional']) }}', '{{ $item['fecha_fmt'] }}', '{{ $item['hora_fmt'] }}', '{{ number_format($item['precio'], 0, ',', '.') }}', '{{ $item['estado_label'] }}', '{{ $item['metodo_pago'] }}')">
                                Ver detalles
                            </button>

                            <a href="{{ route('cliente.agendar', ['servicio_id' => $item['servicio_id']]) }}" class="btn-card-action">
                                Reprogramar
                            </a>

                            @if (in_array($item['estado_slug'], ['pendiente', 'confirmada', 'confirmado']))
                                <a href="{{ route('citas.cancelar', $item['id']) }}" 
                                   class="btn-card-action btn-danger" 
                                   onclick="return confirmarCancelarCita(event, this.href)">
                                    <i class="fa-regular fa-trash-can"></i> Cancelar cita
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach

                <!-- Mensaje cuando una pestaña no tiene citas -->
                <div id="citasVaciasMsg" style="{{ ($countProximas ?? 0) === 0 ? 'display: block;' : 'display: none;' }} background: #ffffff; border-radius: 22px; border: 1px solid #ebdcf5; padding: 40px 20px; text-align: center;">
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #f6effb; color: #7c1db8; display: flex; align-items: center; justify-content: center; font-size: 22px; margin: 0 auto 12px;">
                        <i class="fa-regular fa-calendar-xmark"></i>
                    </div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #2a0d3e; margin-bottom: 4px;">No tienes citas en esta categoría</h4>
                    <p style="font-size: 12.5px; color: #7a5a90; margin-bottom: 16px;">Revisa las otras pestañas o reserva una nueva cita cuando gustes.</p>
                    <a href="{{ route('cliente.agendar') }}" class="btn-card-action" style="background: #7c1db8; color: #fff; border-color: #7c1db8;">
                        <i class="fa-solid fa-calendar-plus"></i> Agendar cita
                    </a>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: SIDEBAR DE WIDGETS -->
        <div class="miscitas-sidebar">

            <!-- ── WIDGET 1: CALENDARIO RÁPIDO ── -->
            <div class="widget-box">
                <div class="widget-box-header">
                    <i class="fa-regular fa-calendar"></i> Calendario rápido
                </div>

                <div class="cal-nav-row">
                    <button type="button" onclick="cambiarMesRapido(-1)" title="Mes anterior">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <div class="cal-month-name" id="calRapidoMesNombre">
                        Junio 2026
                    </div>
                    <button type="button" onclick="cambiarMesRapido(1)" title="Mes siguiente">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="cal-week-names">
                    <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                </div>

                <div class="cal-grid-days" id="calRapidoGrid">
                    <!-- Días generados por JavaScript -->
                </div>

                <div class="cal-legend-bar">
                    <div class="legend-item"><span class="dot green"></span> Disponible</div>
                    <div class="legend-item"><span class="dot orange"></span> Pocos cupos</div>
                    <div class="legend-item"><span class="dot gray"></span> No disponible</div>
                </div>
            </div>

            <!-- ── WIDGET 2: RESUMEN DE CITAS ── -->
            <div class="widget-box">
                <div class="widget-box-header">
                    <i class="fa-solid fa-chart-pie"></i> Resumen de citas
                </div>

                <div class="resumen-citas-list">
                    <div class="resumen-citas-row">
                        <div class="resumen-citas-label">
                            <i class="fa-regular fa-calendar purple"></i> Próximas
                        </div>
                        <div class="resumen-citas-num purple">
                            {{ $countProximas ?? 0 }}
                        </div>
                    </div>

                    <div class="resumen-citas-row">
                        <div class="resumen-citas-label">
                            <i class="fa-regular fa-clock orange"></i> Pendientes
                        </div>
                        <div class="resumen-citas-num orange">
                            {{ $countPendientes ?? 0 }}
                        </div>
                    </div>

                    <div class="resumen-citas-row">
                        <div class="resumen-citas-label">
                            <i class="fa-regular fa-circle-check green"></i> Completadas
                        </div>
                        <div class="resumen-citas-num green">
                            {{ $countCompletadas ?? 0 }}
                        </div>
                    </div>

                    <div class="resumen-citas-row">
                        <div class="resumen-citas-label">
                            <i class="fa-regular fa-circle-xmark red"></i> Canceladas
                        </div>
                        <div class="resumen-citas-num red">
                            {{ $countCanceladas ?? 0 }}
                        </div>
                    </div>
                </div>

                <a href="{{ route('cliente.agendar') }}" class="btn-agendar-nueva">
                    <i class="fa-regular fa-calendar-plus"></i> Agendar nueva cita
                </a>
            </div>

        </div>

    </div>

</div> <!-- Cierre de .page-content -->

<!-- ══════════════════════════════════════════
     MODAL VER DETALLES DE CITA
══════════════════════════════════════════ -->
<div class="modal-detalles-overlay" id="modalDetallesCita" style="display: none;">
    <div class="modal-detalles-container">
        <div class="modal-detalles-header">
            <h3><i class="fa-regular fa-calendar-check"></i> Detalles de la Cita</h3>
            <button type="button" class="modal-detalles-close" onclick="cerrarModalDetalle()">&times;</button>
        </div>
        <div class="modal-detalles-body">
            <div class="detalles-row-item">
                <span class="detalles-label">Servicio</span>
                <span class="detalles-val" id="detServicio" style="text-transform: capitalize;">Volumen Ruso</span>
            </div>
            <div class="detalles-row-item">
                <span class="detalles-label">Profesional asignada</span>
                <span class="detalles-val" id="detProfesional">Isabella Sifuentes Perdomo</span>
            </div>
            <div class="detalles-row-item">
                <span class="detalles-label">Fecha</span>
                <span class="detalles-val" id="detFecha">30/09/2026</span>
            </div>
            <div class="detalles-row-item">
                <span class="detalles-label">Hora</span>
                <span class="detalles-val" id="detHora">05:40 pm</span>
            </div>
            <div class="detalles-row-item">
                <span class="detalles-label">Ubicación</span>
                <span class="detalles-val">Oh La Lash Studio (Calle 123 #45-67, Medellín)</span>
            </div>
            <div class="detalles-row-item">
                <span class="detalles-label">Estado</span>
                <span class="detalles-val" id="detEstado" style="color: #7c1db8;">Confirmada</span>
            </div>
            <div class="detalles-row-item">
                <span class="detalles-label">Valor del servicio</span>
                <span class="detalles-val" id="detPrecio" style="color: #4a0c6d; font-size: 15px;">$130.000</span>
            </div>
            <div class="detalles-row-item">
                <span class="detalles-label">Método de pago</span>
                <span class="detalles-val" id="detMetodoPago">Tarjeta de Crédito</span>
            </div>

            <div style="margin-top: 20px; text-align: right;">
                <button type="button" class="btn-card-action" onclick="cerrarModalDetalle()" style="background: #7c1db8; color: #fff; border-color: #7c1db8;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts interactivos extraídos al módulo Vite: resources/js/modules/clientes/miscitas.js -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper (abierto en headercliente.blade.php) -->
</div> <!-- Cierre de .app-layout (abierto en headercliente.blade.php) -->

</body>
</html>
