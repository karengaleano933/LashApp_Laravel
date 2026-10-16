@include('layouts.header_trabajador', ['titulo' => 'Mi Panel'])
@include('layouts.sidebar_trabajador')

@php
    // Fallback de inicialización en caso de llamada directa a la vista
    if (!isset($totalCitas)) {
        $user = auth()->user();
        $trabajador = $user?->trabajador ?? \App\Models\Trabajador::where('user_id', $user?->id)->first();
        $trabajadorId = $trabajador?->id;

        $citasQuery = \App\Models\Cita::query();
        if ($trabajadorId) {
            $citasQuery->where('trabajador_id', $trabajadorId);
        }

        $hoy = \Carbon\Carbon::today()->toDateString();
        $totalCitas = (clone $citasQuery)->count();
        $citasHoy = (clone $citasQuery)->whereDate('fecha', $hoy)->count();
        $citasPendientes = (clone $citasQuery)->where('estado', 'pendiente')->count();
        $citasCompletadas = (clone $citasQuery)->whereIn('estado', ['confirmado', 'finalizado', 'completado', 'completada', 'realizada'])->count();
        $totalClientes = (clone $citasQuery)->distinct('cliente_id')->count('cliente_id');
        $proximasCitas = $proximasCitas ?? [];
        $calificacionPromedio = $calificacionPromedio ?? 4.9;
        $serviciosPopulares = $serviciosPopulares ?? [];
    }
@endphp

<!-- Contenido Principal -->
<div class="page-content">

    <!-- Bienvenida / Cabecera -->
    <div class="welcome-header">
        <h1>Mi Panel de Trabajo</h1>
        <p>Visualiza tus citas programadas, clientes atendidos y rendimiento profesional.</p>
    </div>

    <!-- ══════ MÉTRICAS DE CABECERA ══════ -->
    <div class="trabajador-grid-cards">

        <!-- Card 1: Citas Hoy -->
        <a href="{{ route('citas.index') }}" class="card-design-sparkle" style="text-decoration:none; color:inherit; display:block;">
            <div class="card-sparkle-icon">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
            <div class="card-sparkle-title">Citas Hoy</div>
            <div class="card-sparkle-value">{{ $citasHoy ?? 0 }}</div>
            <div class="card-sparkle-sub">
                <i class="fa-solid fa-arrow-trend-up"></i> Programadas para hoy
            </div>
        </a>

        <!-- Card 2: Clientes Atendidos -->
        <a href="{{ route('citas.index') }}" class="card-design-sparkle" style="text-decoration:none; color:inherit; display:block;">
            <div class="card-sparkle-icon">
                <i class="fa-solid fa-users-line"></i>
            </div>
            <div class="card-sparkle-title">Clientes Atendidos</div>
            <div class="card-sparkle-value">{{ $totalClientes ?? 0 }}</div>
            <div class="card-sparkle-sub">
                <i class="fa-solid fa-user-check"></i> Clientes registrados
            </div>
        </a>

        <!-- Card 3: Servicios Realizados -->
        <a href="{{ route('servicios.index') }}" class="card-design-sparkle" style="text-decoration:none; color:inherit; display:block;">
            <div class="card-sparkle-icon">
                <i class="fa-solid fa-scissors"></i>
            </div>
            <div class="card-sparkle-title">Servicios Realizados</div>
            <div class="card-sparkle-value">{{ $citasCompletadas ?? ($citasCompletas ?? 0) }}</div>
            <div class="card-sparkle-sub">
                <i class="fa-solid fa-circle-check"></i> Citas completadas
            </div>
        </a>

        <!-- Card 4: Calificación -->
        <a href="{{ route('resenas.index') }}" class="card-design-sparkle" style="text-decoration:none; color:inherit; display:block;">
            <div class="card-sparkle-icon">
                <i class="fa-regular fa-star"></i>
            </div>
            <div class="card-sparkle-title">Calificación</div>
            <div class="card-sparkle-value">{{ number_format($calificacionPromedio ?? 4.9, 1) }}</div>
            <div class="card-sparkle-sub" style="color: #d97706;">
                <i class="fa-solid fa-star"></i> Excelente atención
            </div>
        </a>

    </div>

    <!-- ══════ FILA PRINCIPAL: TABLA DE CITAS & SERVICIOS POPULARES ══════ -->
    <div class="dashboard-grid">

        <!-- COLUMNA IZQUIERDA: TABLA PRÓXIMAS CITAS -->
        <div class="dashboard-card">
            <div class="card-header">
                <div>
                    <h3><i class="fa-regular fa-calendar-days"></i> Próximas citas</h3>
                    <p>Citas asignadas y agendadas recientemente</p>
                </div>
                <a href="{{ route('citas.index') }}">Ver todas <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i></a>
            </div>

            @if (!empty($proximasCitas) && count($proximasCitas) > 0)
                <div class="table-citas-wrap">
                    <table class="table-citas-custom">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Hora</th>
                                <th>Fecha</th>
                                <th>Profesional</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($proximasCitas as $cita)
                                @php
                                    $estado = strtolower($cita['estado'] ?? 'pendiente');
                                    $badgeClass = 'pendiente';
                                    if (in_array($estado, ['confirmada', 'confirmado'])) $badgeClass = 'confirmada';
                                    elseif (in_array($estado, ['cancelada', 'cancelado'])) $badgeClass = 'cancelada';
                                    elseif (in_array($estado, ['completada', 'completado', 'realizada', 'finalizado', 'finalizada'])) $badgeClass = 'completada';
                                @endphp
                                <tr>
                                    <td data-label="Cliente">
                                        <strong>{{ $cita['cliente'] ?? 'Cliente LashApp' }}</strong>
                                    </td>
                                    <td data-label="Servicio">
                                        <span style="color:#7c1db8; font-weight:600;">
                                            {{ $cita['servicio'] ?? ($cita['nombre_servicio'] ?? 'Servicio') }}
                                        </span>
                                    </td>
                                    <td data-label="Hora">
                                        {{ !empty($cita['hora_inicio']) ? \Carbon\Carbon::parse($cita['hora_inicio'])->format('g:i a') : '09:00 AM' }}
                                    </td>
                                    <td data-label="Fecha">
                                        {{ !empty($cita['fecha_cita']) ? \Carbon\Carbon::parse($cita['fecha_cita'])->format('d/m/Y') : now()->format('d/m/Y') }}
                                    </td>
                                    <td data-label="Profesional">
                                        {{ $cita['profesional'] ?? ($cita['trabajador'] ?? 'Profesional') }}
                                    </td>
                                    <td data-label="Estado">
                                        <span class="badge-pill-status {{ $badgeClass }}">
                                            {{ ucfirst($cita['estado'] ?? 'Pendiente') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="text-align:center; padding: 40px; color: #7a5a90;">
                    <i class="fa-regular fa-calendar-xmark" style="font-size: 36px; margin-bottom: 12px; color: #dcc8ee; display:block;"></i>
                    <p style="margin: 0; font-weight: 500;">No hay próximas citas registradas.</p>
                </div>
            @endif
        </div>

        <!-- COLUMNA DERECHA: SERVICIOS POPULARES -->
        <div class="dashboard-card">
            <div class="card-header">
                <div>
                    <h3><i class="fa-solid fa-fire" style="color: #f59e0b;"></i> Servicios Populares</h3>
                    <p>Demanda de servicios este mes</p>
                </div>
                <a href="{{ route('servicios.index') }}">Ver catálogo <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i></a>
            </div>

            <div class="popular-services-list">
                @forelse ($serviciosPopulares ?? [] as $serv)
                    <div class="popular-service-item">
                        <div class="service-icon-box">
                            <i class="{{ $serv['icono'] ?? 'fa-solid fa-spa' }}"></i>
                        </div>
                        <div class="service-info-flex">
                            <div class="service-title-text">{{ $serv['nombre'] ?? 'Servicio' }}</div>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width: {{ $serv['porcentaje'] ?? 50 }}%;"></div>
                            </div>
                        </div>
                        <div class="service-percent-num">{{ $serv['porcentaje'] ?? 50 }} %</div>
                    </div>
                @empty
                    <div class="popular-service-item">
                        <div class="service-icon-box">
                            <i class="fa-solid fa-eye"></i>
                        </div>
                        <div class="service-info-flex">
                            <div class="service-title-text">Pestañas Volumen Ruso</div>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width: 88%;"></div>
                            </div>
                        </div>
                        <div class="service-percent-num">88 %</div>
                    </div>

                    <div class="popular-service-item">
                        <div class="service-icon-box">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <div class="service-info-flex">
                            <div class="service-title-text">Lifting de Pestañas</div>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width: 70%;"></div>
                            </div>
                        </div>
                        <div class="service-percent-num">70 %</div>
                    </div>

                    <div class="popular-service-item">
                        <div class="service-icon-box">
                            <i class="fa-solid fa-hand-sparkles"></i>
                        </div>
                        <div class="service-info-flex">
                            <div class="service-title-text">Manicure & Uñas</div>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width: 55%;"></div>
                            </div>
                        </div>
                        <div class="service-percent-num">55 %</div>
                    </div>

                    <div class="popular-service-item">
                        <div class="service-icon-box">
                            <i class="fa-solid fa-spa"></i>
                        </div>
                        <div class="service-info-flex">
                            <div class="service-title-text">Diseño de Cejas</div>
                            <div class="progress-bar-wrap">
                                <div class="progress-bar-fill" style="width: 45%;"></div>
                            </div>
                        </div>
                        <div class="service-percent-num">45 %</div>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- ══════ ACCESOS RÁPIDOS ══════ -->
    <div class="quick-section">
        <h3>Accesos rápidos</h3>
        <div class="quick-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <a href="{{ route('citas.index') }}" class="quick-card">
                <i class="fa-solid fa-calendar-check"></i><span>Mis citas</span>
            </a>
            <a href="{{ route('servicios.index') }}" class="quick-card">
                <i class="fa-solid fa-spa"></i><span>Servicios</span>
            </a>
            <a href="{{ route('resenas.index') }}" class="quick-card">
                <i class="fa-solid fa-star"></i><span>Reseñas</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="quick-card">
                <i class="fa-solid fa-user"></i><span>Mi perfil</span>
            </a>
        </div>
    </div>

</div> <!-- Cierre de .page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>
