<!-- Incluir Header superior de Admin -->
@include('layouts.header')
@include('layouts.sidebaradmin') <!-- que es tu barra superior con notificaciones y saludo -->

<!-- Aquí va el contenido del dashboard -->

<div class="page-content">

<!-- Bienvenida -->
<div class="welcome-header">
    <h1>Resumen general</h1>
    <p>Administra las citas, clientes y servicios de LashApp.</p>
</div>

<!-- Tarjetas métricas -->
<div class="cards-grid">
    <div class="metric-card purple">
        <div class="metric-top"><div class="metric-icon"><i class="fa-solid fa-users"></i></div></div>
        <span class="metric-label">Usuarios</span>
        <strong class="metric-value">{{ $totalUsuarios ?? 0 }}</strong>
        <span class="metric-description">Registrados en el sistema</span>
    </div>

    <div class="metric-card pink">
        <div class="metric-top"><div class="metric-icon"><i class="fa-solid fa-user-group"></i></div></div>
        <span class="metric-label">Clientes</span>
        <strong class="metric-value">{{ $totalClientes ?? 0 }}</strong>
        <span class="metric-description">Clientes registrados</span>
    </div>

    <div class="metric-card gold">
        <div class="metric-top"><div class="metric-icon"><i class="fa-solid fa-user-tie"></i></div></div>
        <span class="metric-label">Trabajadores</span>
        <strong class="metric-value">{{ $totalTrabajadores ?? 0 }}</strong>
        <span class="metric-description">Personal disponible</span>
    </div>

    <div class="metric-card green">
        <div class="metric-top"><div class="metric-icon"><i class="fa-solid fa-calendar-check"></i></div></div>
        <span class="metric-label">Citas Totales</span>
        <strong class="metric-value">{{ $totalCitas ?? 0 }}</strong>
        <span class="metric-description">Citas en sistema</span>
    </div>
</div>

<!-- Segunda fila: Próximas citas y resumen -->
<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h3><i class="fa-solid fa-calendar-days"></i> Próximas citas</h3>
                <p>Citas programadas recientemente</p>
            </div>
            <a href="{{ route('citas.index') }}">Ver todas <i class="fa-solid fa-arrow-right" style="font-size:11px;"></i></a>
        </div>

        @if (!empty($proximasCitas))
            <div class="citas-lista-dashboard">
                @foreach ($proximasCitas as $cita)
                    @php
                        $estadoClase = strtolower($cita['estado'] ?? 'pendiente');
                        $inicial = strtoupper(substr($cita['cliente'] ?? 'C', 0, 1));
                    @endphp
                    <div class="cita-item-dashboard">
                        <div class="cita-item-left">
                            <div class="cita-avatar">{{ $inicial }}</div>
                            <div class="cita-info">
                                <strong>{{ $cita['cliente'] ?? 'Cliente' }}</strong>
                                <span>
                                    <i class="fa-solid fa-spa" style="color:#7c1db8;"></i> {{ $cita['servicio'] ?? 'Servicio' }}
                                    &bull; <i class="fa-solid fa-user-tie"></i> {{ $cita['profesional'] ?? 'Profesional' }}
                                </span>
                            </div>
                        </div>

                        <div class="cita-item-right">
                            <span class="cita-fecha-tag">
                                <i class="fa-regular fa-clock"></i>
                                {{ \Carbon\Carbon::parse($cita['fecha_cita'] ?? now())->format('d/m/Y') }}
                            </span>
                            <span class="cita-estado-tag estado-{{ $estadoClase }}">
                                {{ $cita['estado'] ?? 'Pendiente' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon"><i class="fa-regular fa-calendar"></i></div>
                <h4>No hay próximas citas registradas</h4>
                <p>Las nuevas citas agendadas se mostrarán automáticamente en este listado.</p>
            </div>
        @endif
    </div>

    <div class="dashboard-card">
        <div class="card-header">
            <div>
                <h3><i class="fa-solid fa-chart-pie"></i> Resumen del negocio</h3>
                <p>Estado del sistema en tiempo real</p>
            </div>
        </div>
        <div class="summary-list">
            <div class="summary-item">
                <div class="summary-icon purple-icon"><i class="fa-solid fa-calendar"></i></div>
                <div><span>Citas pendientes</span><strong>{{ $citasPendientes ?? 0 }}</strong></div>
            </div>
            <div class="summary-item">
                <div class="summary-icon pink-icon"><i class="fa-solid fa-scissors"></i></div>
                <div><span>Servicios activos</span><strong>{{ $totalServicios ?? 0 }}</strong></div>
            </div>
            <div class="summary-item">
                <div class="summary-icon gold-icon"><i class="fa-solid fa-star"></i></div>
                <div><span>Reseñas registradas</span><strong>{{ $totalResenas ?? 0 }}</strong></div>
            </div>
        </div>
    </div>
</div>

<!-- Accesos rápidos -->
<div class="quick-section">
    <h3>Accesos rápidos</h3>
    <div class="quick-grid">
        <a href="{{ route('citas.index') }}" class="quick-card"><i class="fa-solid fa-calendar-plus"></i><span>Gestionar citas</span></a>
        <a href="{{ route('usuarios.index') }}" class="quick-card"><i class="fa-solid fa-users"></i><span>Gestionar Usuarios</span></a>
        <a href="#" class="quick-card"><i class="fa-solid fa-spa"></i><span>Gestionar servicios</span></a>
        <a href="#" class="quick-card"><i class="fa-solid fa-money-bill-wave"></i><span>Gestionar pagos</span></a>
    </div>
</div>

</div> <!-- Cierre de page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>
