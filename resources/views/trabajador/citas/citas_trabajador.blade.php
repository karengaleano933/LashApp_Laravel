@include('layouts.header_trabajador', ['titulo' => 'Mis Citas'])
@include('layouts.sidebar_trabajador')
@vite(['resources/css/citas.css', 'resources/css/citas_trabajador.css'])

<style>
    .btn-accion.btn-completar {
        background: #d1fae5 !important;
        color: #059669 !important;
        border: 1px solid #a7f3d0 !important;
        width: 28px !important;
        height: 28px !important;
        border-radius: 8px !important;
        cursor: pointer !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: all .2s ease !important;
        font-size: 11.5px !important;
    }
    .btn-accion.btn-completar:hover {
        background: #059669 !important;
        color: #ffffff !important;
        transform: scale(1.08) !important;
        box-shadow: 0 4px 10px rgba(5, 150, 105, 0.28) !important;
    }
    .badge-realizada-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }
    .badge-cancelada-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 600;
    }
</style>

@php
    // Fallback de inicialización para carga directa o en pruebas
    if (!isset($citasHoy)) {
        $user = auth()->user();
        $trabajador = $user?->trabajador ?? \App\Models\Trabajador::where('user_id', $user?->id)->first();
        $id_trabajador = $trabajador?->id ?? 0;

        $hoy = \Carbon\Carbon::today()->format('Y-m-d');
        $inicioSemana = \Carbon\Carbon::now()->startOfWeek()->format('Y-m-d');
        $finSemana = \Carbon\Carbon::now()->endOfWeek()->format('Y-m-d');

        $tieneCitasAsignadas = ($id_trabajador > 0) && \App\Models\Cita::where('trabajador_id', $id_trabajador)->exists();
        $citasBase = \App\Models\Cita::query();
        if ($tieneCitasAsignadas) {
            $citasBase->where('trabajador_id', $id_trabajador);
        }

        $citasHoy = (clone $citasBase)->whereDate('fecha', $hoy)->count();
        $citasSemana = (clone $citasBase)->whereBetween('fecha', [$inicioSemana, $finSemana])->count();
        $citasComplet = (clone $citasBase)->whereIn('estado', ['finalizado', 'completado', 'completada', 'realizada'])->count();
        $citasCancelad = (clone $citasBase)->whereIn('estado', ['cancelado', 'cancelada'])->count();

        $citasQuery = (clone $citasBase)
            ->with(['cliente.user', 'trabajador.user', 'servicio'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        $estadoMap = [
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmada',
            'confirmada' => 'Confirmada',
            'cancelado' => 'Cancelada',
            'cancelada' => 'Cancelada',
            'finalizado' => 'Completada',
            'completado' => 'Completada',
            'completada' => 'Completada',
            'realizada' => 'Completada',
        ];

        $proximasCitas = $citasQuery->map(function ($cita) use ($estadoMap) {
            $clienteNombre = $cita->cliente?->user
                ? trim($cita->cliente->user->name . ' ' . ($cita->cliente->user->apellido ?? ''))
                : 'Cliente';

            $trabajadorNombre = $cita->trabajador?->user
                ? trim($cita->trabajador->user->name . ' ' . ($cita->trabajador->user->apellido ?? ''))
                : 'Profesional';

            return [
                'id_cita' => $cita->id,
                'cliente' => $clienteNombre,
                'servicio' => $cita->servicio?->nombre ?? 'Servicio',
                'hora_inicio' => $cita->hora_inicio,
                'fecha_cita' => $cita->fecha ? $cita->fecha->format('Y-m-d') : date('Y-m-d'),
                'profesional' => $trabajadorNombre,
                'estado' => $estadoMap[strtolower($cita->estado)] ?? ucfirst($cita->estado),
            ];
        })->toArray();

        $listaClientes = \App\Models\Cliente::with('user')->get()->map(function ($c) {
            return [
                'id_cliente' => $c->id,
                'nombre_completo' => $c->user ? trim($c->user->name . ' ' . $c->user->apellido) : 'Cliente #' . $c->id,
            ];
        })->toArray();

        $listaServicios = \App\Models\Servicio::where('estado', 'activo')->get()->map(function ($s) {
            return [
                'id_servicio' => $s->id,
                'nombre' => $s->nombre,
                'precio' => $s->precio,
            ];
        })->toArray();

        $listaTrabajadores = \App\Models\Trabajador::where('estado', 'activo')->with('user')->get()->map(function ($t) {
            return [
                'id_trabajador' => $t->id,
                'nombre_completo' => $t->user ? trim($t->user->name . ' ' . $t->user->apellido) : 'Profesional #' . $t->id,
            ];
        })->toArray();
    }
@endphp

<!-- ══════════════════ ALERTAS SWEETALERT ══════════════════ -->
@if (session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: '¡Operación Exitosa!',
        text: "{{ session('success') }}",
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#7c1db8'
    });
});
</script>
@endif

@if (session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: "{{ session('error') }}",
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#7c1db8'
    });
});
</script>
@endif

<div class="page-content">

    <!-- ══════════════════ ENCABEZADO ══════════════════ -->
    <div class="citas-header">
        <div>
            <h1>Mis Citas Asignadas</h1>
            <p>Gestiona y organiza tus citas programadas</p>
        </div>
        <button type="button" class="btn-nueva-cita" id="abrirNuevaCita">
            <i class="fa-solid fa-plus"></i> Nueva cita
        </button>
    </div>

    <!-- ══════════════════ TARJETAS MÉTRICAS ══════════════════ -->
    <div class="citas-cards">

        <div class="cita-card">
            <div class="cita-card-icon purple"><i class="fa-solid fa-calendar-days"></i></div>
            <div class="cita-card-body">
                <span class="cita-card-label">CITAS HOY</span>
                <strong class="cita-card-value">{{ $citasHoy ?? 0 }}</strong>
                <span class="cita-card-sub purple-text">Citas para hoy</span>
            </div>
        </div>

        <div class="cita-card">
            <div class="cita-card-icon blue"><i class="fa-solid fa-clock"></i></div>
            <div class="cita-card-body">
                <span class="cita-card-label">CITAS ESTA SEMANA</span>
                <strong class="cita-card-value">{{ $citasSemana ?? 0 }}</strong>
                <span class="cita-card-sub blue-text">Esta semana</span>
            </div>
        </div>

        <div class="cita-card">
            <div class="cita-card-icon green"><i class="fa-solid fa-circle-check"></i></div>
            <div class="cita-card-body">
                <span class="cita-card-label">CITAS COMPLETADAS</span>
                <strong class="cita-card-value">{{ $citasComplet ?? 0 }}</strong>
                <span class="cita-card-sub green-text">Total realizadas</span>
            </div>
        </div>

        <div class="cita-card">
            <div class="cita-card-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
            <div class="cita-card-body">
                <span class="cita-card-label">CITAS CANCELADAS</span>
                <strong class="cita-card-value">{{ $citasCancelad ?? 0 }}</strong>
                <span class="cita-card-sub red-text">Canceladas</span>
            </div>
        </div>

    </div>

    <!-- ══════════════════ FILA PRINCIPAL ══════════════════ -->
    <div class="citas-main-grid">

        <!-- TABLA PRÓXIMAS CITAS (COLUMNA IZQUIERDA) -->
        <div class="citas-tabla-card">
            <div class="citas-tabla-header">
                <div class="tabla-header-title">
                    <i class="fa-solid fa-calendar-check"></i>
                    <h3>Próximas Citas</h3>
                </div>
                <span class="badge-total-citas">{{ count($proximasCitas ?? []) }} registradas</span>
            </div>

            <!-- Toolbar: Mostrar registros y Buscar -->
            <div class="tabla-toolbar">
                <div class="toolbar-registros">
                    <span>Mostrar</span>
                    <select id="selectRegistros">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>registros</span>
                </div>
                <div class="toolbar-buscar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="buscarTabla" placeholder="Buscar por cliente, servicio...">
                </div>
            </div>

            <div class="table-responsive">
                <table id="citasTable" class="citas-table">
                    <thead>
                        <tr>
                            <th>CLIENTE</th>
                            <th>SERVICIO</th>
                            <th>HORA</th>
                            <th>FECHA</th>
                            <th>PROFESIONAL</th>
                            <th>ESTADO</th>
                            <th class="text-center">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="citasTbody">
                        @forelse ($proximasCitas ?? [] as $cita)
                            @php
                                $clienteNombre  = is_array($cita) ? ($cita['cliente'] ?? 'Cliente') : ($cita->cliente ?? 'Cliente');
                                $servicioNombre = is_array($cita) ? ($cita['servicio'] ?? 'Servicio') : ($cita->servicio ?? 'Servicio');
                                $horaInicio     = is_array($cita) ? ($cita['hora_inicio'] ?? '00:00') : ($cita->hora_inicio ?? '00:00');
                                $fechaCita      = is_array($cita) ? ($cita['fecha_cita'] ?? date('Y-m-d')) : ($cita->fecha_cita ?? date('Y-m-d'));
                                $profesional    = is_array($cita) ? ($cita['profesional'] ?? 'Profesional') : ($cita->profesional ?? 'Profesional');
                                $estado         = is_array($cita) ? ($cita['estado'] ?? 'Pendiente') : ($cita->estado ?? 'Pendiente');
                                $idCita         = is_array($cita) ? ($cita['id_cita'] ?? $cita['id'] ?? 0) : ($cita->id_cita ?? ($cita->id ?? 0));
                                $inicial        = strtoupper(substr($clienteNombre, 0, 1));
                                $estadoClase    = strtolower($estado);
                            @endphp
                            <tr data-cliente="{{ strtolower($clienteNombre) }}"
                                data-servicio="{{ strtolower($servicioNombre) }}"
                                data-profesional="{{ strtolower($profesional) }}"
                                data-estado="{{ $estado }}"
                                data-fecha="{{ $fechaCita }}">
                                <td>
                                    <div class="cliente-cell">
                                        <div class="cliente-avatar-badge">{{ $inicial }}</div>
                                        <strong title="{{ $clienteNombre }}">{{ $clienteNombre }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="servicio-pill" title="{{ $servicioNombre }}">
                                        <i class="fa-solid fa-spa"></i> {{ $servicioNombre }}
                                    </span>
                                </td>
                                <td>
                                    <span class="hora-badge">
                                        <i class="fa-regular fa-clock"></i> {{ date('g:i a', strtotime($horaInicio)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fecha-badge">
                                        <i class="fa-regular fa-calendar"></i> {{ date('d/m/Y', strtotime($fechaCita)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="profesional-pill" title="{{ $profesional }}">
                                        <i class="fa-solid fa-user-tie"></i> {{ $profesional }}
                                    </span>
                                </td>
                                <td>
                                    <span class="estado-badge estado-{{ $estadoClase }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div style="display:inline-flex; gap:6px; justify-content:center; align-items:center;">
                                        @if (!in_array(strtolower($estado), ['finalizado', 'completado', 'completada', 'realizada', 'cancelado', 'cancelada']))
                                            <button type="button" class="btn-accion btn-completar"
                                                onclick="confirmarCompletar({{ $idCita }})"
                                                title="Marcar como Realizada">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button type="button" class="btn-accion btn-cancelar"
                                                onclick="confirmarCancelar({{ $idCita }})"
                                                title="Cancelar Cita">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @elseif (in_array(strtolower($estado), ['finalizado', 'completado', 'completada', 'realizada']))
                                            <span class="badge-realizada-pill" title="Cita realizada con éxito">
                                                <i class="fa-solid fa-circle-check"></i> Realizada
                                            </span>
                                        @else
                                            <span class="badge-cancelada-pill" title="Cita cancelada">
                                                <i class="fa-solid fa-circle-xmark"></i> Cancelada
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="tabla-vacia-msg">
                                    <i class="fa-regular fa-calendar-xmark"></i>
                                    <p>No hay citas registradas en tu agenda.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación y contador -->
            <div class="citas-tabla-footer">
                <div id="infoPaginacion" class="info-paginacion-txt">
                    Mostrando {{ count($proximasCitas ?? []) }} registros
                </div>
                <div class="tabla-footer-actions">
                    <button type="button" class="btn-ver-todas" onclick="limpiarFiltros()">
                        <i class="fa-solid fa-arrows-rotate"></i> Ver todas las citas
                    </button>
                    <button type="button" class="btn-cancelar-sel" onclick="limpiarFiltros()">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- PANEL DERECHO: Calendario + Buscar y Filtrar -->
        <div class="citas-side">

            <!-- Calendario Widget -->
            <div class="calendario-card">
                <div class="calendario-header">
                    <h3><i class="fa-solid fa-calendar-days"></i> Calendario</h3>
                    <div class="cal-nav">
                        <button type="button" id="calPrev"><i class="fa-solid fa-chevron-left"></i></button>
                        <span id="calMesAnio"></span>
                        <button type="button" id="calNext"><i class="fa-solid fa-chevron-right"></i></button>
                    </div>
                </div>
                <div class="cal-grid-names">
                    <span>Lu</span><span>Mar</span><span>Mié</span>
                    <span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                </div>
                <div class="cal-grid" id="calGrid"></div>
                <div class="cal-leyenda">
                    <span><i class="fa-solid fa-circle c-confirmada"></i> Confirmada</span>
                    <span><i class="fa-solid fa-circle c-cancelada"></i> Cancelada</span>
                    <span><i class="fa-solid fa-circle c-pendiente"></i> Pendiente</span>
                </div>
            </div>

            <!-- Buscar y Filtrar Card -->
            <div class="filtros-card">
                <h3>Buscar y Filtrar</h3>
                <div class="filtros-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="buscarCita" placeholder="Busque por cliente o servicio">
                </div>
                <div class="filtros-row">
                    <select id="filtroProfesional" class="filtro-select">
                        <option value="">Profesionales</option>
                        @foreach ($listaTrabajadores ?? [] as $tr)
                            @php
                                $trNom = is_array($tr) ? ($tr['nombre_completo'] ?? '') : ($tr->nombre_completo ?? '');
                            @endphp
                            <option value="{{ $trNom }}">{{ $trNom }}</option>
                        @endforeach
                    </select>
                    <select id="filtroEstado" class="filtro-select">
                        <option value="">Estados</option>
                        <option value="Confirmada">Confirmada</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Cancelada">Cancelada</option>
                        <option value="Completada">Completada</option>
                    </select>
                    <input type="date" id="filtroFecha" class="filtro-select" value="" title="Filtrar por fecha">
                </div>
            </div>

        </div>
    </div>

</div> <!-- Cierre de .page-content -->

<!-- ══════════════════ MODAL NUEVA CITA ══════════════════ -->
<div id="modalNuevaCita" class="modal-lash-overlay" aria-hidden="true" role="dialog" aria-labelledby="modalTitulo">
    <div class="modal-lash-contenido">
        
        <!-- CABECERA DEL MODAL -->
        <div class="modal-lash-header">
            <div class="modal-lash-header-left">
                <div class="modal-lash-icon-badge">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>
                <div>
                    <h2 class="modal-lash-titulo" id="modalTitulo">Nueva Cita</h2>
                    <p class="modal-lash-subtitulo">Programa una cita para tus clientes</p>
                </div>
            </div>
            <button type="button" class="modal-lash-cerrar" id="cerrarNuevaCita" aria-label="Cerrar modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- FORMULARIO -->
        <form action="{{ route('citas.store') }}" method="POST" class="modal-lash-form" id="formNuevaCita">
            @csrf
            <div class="modal-lash-body">
                <div class="modal-lash-grid">

                    <!-- CLIENTE -->
                    <div class="modal-lash-campo">
                        <label for="id_cliente">
                            <i class="fa-solid fa-user"></i> Cliente <span class="required">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <select name="id_cliente" id="id_cliente" required>
                                <option value="" disabled selected>Seleccione un cliente...</option>
                                @foreach ($listaClientes ?? [] as $c)
                                    @php
                                        $cId = is_array($c) ? ($c['id_cliente'] ?? $c['id'] ?? '') : ($c->id_cliente ?? $c->id ?? '');
                                        $cNom = is_array($c) ? ($c['nombre_completo'] ?? '') : ($c->nombre_completo ?? '');
                                    @endphp
                                    <option value="{{ $cId }}">{{ $cNom }}</option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down select-chevron"></i>
                        </div>
                    </div>

                    <!-- SERVICIO -->
                    <div class="modal-lash-campo">
                        <label for="id_servicio">
                            <i class="fa-solid fa-spa"></i> Servicio <span class="required">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <select name="id_servicio" id="id_servicio" required>
                                <option value="" disabled selected>Seleccione un servicio...</option>
                                @foreach ($listaServicios ?? [] as $s)
                                    @php
                                        $sId = is_array($s) ? ($s['id_servicio'] ?? $s['id'] ?? '') : ($s->id_servicio ?? $s->id ?? '');
                                        $sNom = is_array($s) ? ($s['nombre'] ?? '') : ($s->nombre ?? '');
                                        $sPrecio = is_array($s) ? ($s['precio'] ?? 0) : ($s->precio ?? 0);
                                    @endphp
                                    <option value="{{ $sId }}">{{ $sNom }} &mdash; ${{ number_format($sPrecio, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down select-chevron"></i>
                        </div>
                    </div>

                    <!-- PROFESIONAL -->
                    <div class="modal-lash-campo">
                        <label for="id_trabajador">
                            <i class="fa-solid fa-user-tie"></i> Profesional <span class="required">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <select name="id_trabajador" id="id_trabajador" required>
                                @foreach ($listaTrabajadores ?? [] as $tr)
                                    @php
                                        $trId = is_array($tr) ? ($tr['id_trabajador'] ?? $tr['id'] ?? '') : ($tr->id_trabajador ?? $tr->id ?? '');
                                        $trNom = is_array($tr) ? ($tr['nombre_completo'] ?? '') : ($tr->nombre_completo ?? '');
                                    @endphp
                                    <option value="{{ $trId }}" {{ ($trId == ($id_trabajador ?? 0)) ? 'selected' : '' }}>
                                        {{ $trNom }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down select-chevron"></i>
                        </div>
                    </div>

                    <!-- FECHA -->
                    <div class="modal-lash-campo">
                        <label for="fecha_cita">
                            <i class="fa-solid fa-calendar-day"></i> Fecha de la cita <span class="required">*</span>
                        </label>
                        <input type="date" name="fecha_cita" id="fecha_cita" value="{{ $hoy ?? date('Y-m-d') }}" required>
                    </div>

                    <!-- HORA -->
                    <div class="modal-lash-campo">
                        <label for="hora_inicio">
                            <i class="fa-solid fa-clock"></i> Hora de inicio <span class="required">*</span>
                        </label>
                        <input type="time" name="hora_inicio" id="hora_inicio" value="09:00" required>
                    </div>

                    <!-- ESTADO -->
                    <div class="modal-lash-campo">
                        <label for="estado">
                            <i class="fa-solid fa-circle-info"></i> Estado <span class="required">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <select name="estado" id="estado">
                                <option value="pendiente" selected>🟡 Pendiente</option>
                                <option value="confirmado">🟢 Confirmada</option>
                                <option value="finalizado">🟣 Completada</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-chevron"></i>
                        </div>
                    </div>

                    <!-- MÉTODO DE PAGO -->
                    <div class="modal-lash-campo">
                        <label for="metodo_pago">
                            <i class="fa-solid fa-credit-card"></i> Método de Pago <span class="required">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <select name="metodo_pago" id="metodo_pago">
                                <option value="efectivo" selected>💵 Efectivo</option>
                                <option value="transferencia">📱 Transferencia</option>
                                <option value="tarjeta">💳 Tarjeta</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-chevron"></i>
                        </div>
                    </div>

                </div>

                <!-- OBSERVACIONES -->
                <div class="modal-lash-campo modal-lash-completo" style="margin-top: 14px;">
                    <label for="observaciones">
                        <i class="fa-solid fa-comment-dots"></i> Observaciones o notas adicionales
                    </label>
                    <textarea name="observaciones" id="observaciones" rows="3" placeholder="Detalles sobre el diseño, alergias o notas adicionales del cliente (opcional)..."></textarea>
                </div>
            </div>

            <!-- FOOTER DEL MODAL -->
            <div class="modal-lash-footer">
                <button type="button" class="modal-lash-btn-cancelar" id="cancelarNuevaCita">
                    Cancelar
                </button>
                <button type="submit" class="modal-lash-btn-agendar">
                    <i class="fa-solid fa-calendar-check"></i> Guardar Cita
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════ JAVASCRIPT DE INTERACTIVIDAD & FILTROS ══════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Abrir/Cerrar Modal Nueva Cita ──
    const modalNueva = document.getElementById('modalNuevaCita');
    const btnAbrir   = document.getElementById('abrirNuevaCita');
    const btnCerrar  = document.getElementById('cerrarNuevaCita');
    const btnCancel  = document.getElementById('cancelarNuevaCita');

    function abrirModal() {
        if (modalNueva) {
            modalNueva.classList.add('activo');
            modalNueva.setAttribute('aria-hidden', 'false');
        }
    }

    function cerrarModal() {
        if (modalNueva) {
            modalNueva.classList.remove('activo');
            modalNueva.setAttribute('aria-hidden', 'true');
        }
    }

    if (btnAbrir)  btnAbrir.addEventListener('click', abrirModal);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModal);
    if (btnCancel) btnCancel.addEventListener('click', cerrarModal);

    // Cerrar al dar clic fuera del contenido o con tecla Escape
    if (modalNueva) {
        modalNueva.addEventListener('click', function(e) {
            if (e.target === modalNueva) cerrarModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalNueva && modalNueva.classList.contains('activo')) {
            cerrarModal();
        }
    });

    // ── Filtrar Tabla en Tiempo Real y Paginación ──
    const buscarTabla     = document.getElementById('buscarTabla');
    const buscarCita      = document.getElementById('buscarCita');
    const filtroProf      = document.getElementById('filtroProfesional');
    const filtroEst       = document.getElementById('filtroEstado');
    const filtroFec       = document.getElementById('filtroFecha');
    const selectRegistros = document.getElementById('selectRegistros');
    const infoPaginacion  = document.getElementById('infoPaginacion');
    const tbody           = document.getElementById('citasTbody');
    const filas           = tbody ? Array.from(tbody.querySelectorAll('tr[data-cliente]')) : [];

    function filtrar() {
        const queryT = (buscarTabla ? buscarTabla.value : '').toLowerCase().trim();
        const queryC = (buscarCita ? buscarCita.value : '').toLowerCase().trim();
        const query  = queryT || queryC;

        const prof = (filtroProf ? filtroProf.value : '').toLowerCase().trim();
        const est  = (filtroEst ? filtroEst.value : '').toLowerCase().trim();
        const fec  = (filtroFec ? filtroFec.value : '').trim();
        const limite = selectRegistros ? parseInt(selectRegistros.value, 10) : 10;

        let coincidentes = 0;

        filas.forEach(row => {
            const cliente     = (row.getAttribute('data-cliente') || '').toLowerCase();
            const servicio    = (row.getAttribute('data-servicio') || '').toLowerCase();
            const profesional = (row.getAttribute('data-profesional') || '').toLowerCase();
            const estado      = (row.getAttribute('data-estado') || '').toLowerCase();
            const fecha       = (row.getAttribute('data-fecha') || '').trim();

            const matchQuery = !query || cliente.includes(query) || servicio.includes(query);
            const matchProf  = !prof || profesional.includes(prof);
            const matchEst   = !est || estado.includes(est);
            const matchFec   = !fec || fecha === fec;

            if (matchQuery && matchProf && matchEst && matchFec) {
                coincidentes++;
                row.style.display = coincidentes <= limite ? '' : 'none';
            } else {
                row.style.display = 'none';
            }
        });

        if (infoPaginacion) {
            const mostrados = Math.min(coincidentes, limite);
            infoPaginacion.textContent = `Mostrando ${mostrados} de ${coincidentes} registros`;
        }
    }

    if (buscarTabla)     buscarTabla.addEventListener('input', filtrar);
    if (buscarCita)      buscarCita.addEventListener('input', filtrar);
    if (filtroProf)      filtroProf.addEventListener('change', filtrar);
    if (filtroEst)       filtroEst.addEventListener('change', filtrar);
    if (filtroFec)       filtroFec.addEventListener('change', filtrar);
    if (selectRegistros) selectRegistros.addEventListener('change', filtrar);

    window.limpiarFiltros = function() {
        if (buscarTabla)     buscarTabla.value = '';
        if (buscarCita)      buscarCita.value = '';
        if (filtroProf)      filtroProf.value = '';
        if (filtroEst)       filtroEst.value = '';
        if (filtroFec)       filtroFec.value = '';
        if (selectRegistros) selectRegistros.value = '10';
        filtrar();
    };

    // Aplicar límite inicial
    filtrar();

    // ── Mini Calendario ──
    const calGrid    = document.getElementById('calGrid');
    const calMesAnio = document.getElementById('calMesAnio');
    const calPrev    = document.getElementById('calPrev');
    const calNext    = document.getElementById('calNext');

    let currDate = new Date();

    function renderCalendar() {
        if (!calGrid || !calMesAnio) return;

        const year  = currDate.getFullYear();
        const month = currDate.getMonth();

        const nombresMeses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        calMesAnio.textContent = `${nombresMeses[month]} ${year}`;

        const firstDay = (new Date(year, month, 1).getDay() + 6) % 7;
        const totalDays = new Date(year, month + 1, 0).getDate();

        calGrid.innerHTML = '';

        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'cal-cell cal-empty';
            calGrid.appendChild(emptyCell);
        }

        const today = new Date();

        for (let d = 1; d <= totalDays; d++) {
            const dayCell = document.createElement('div');
            const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === d;
            dayCell.className = 'cal-cell' + (isToday ? ' cal-today' : '');
            dayCell.textContent = d;
            calGrid.appendChild(dayCell);
        }
    }

    renderCalendar();

    if (calPrev) calPrev.addEventListener('click', () => { currDate.setMonth(currDate.getMonth() - 1); renderCalendar(); });
    if (calNext) calNext.addEventListener('click', () => { currDate.setMonth(currDate.getMonth() + 1); renderCalendar(); });
});

function confirmarCompletar(idCita) {
    Swal.fire({
        title: '¿Marcar cita como realizada?',
        text: 'La cita cambiará a estado Completada y la clienta podrá calificar el servicio.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#059669',
        cancelButtonColor: '#7a5a90',
        confirmButtonText: 'Sí, realizada',
        cancelButtonText: 'Volver'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('citas.completar', ':id') }}".replace(':id', idCita);
        }
    });
}

function confirmarCancelar(idCita) {
    Swal.fire({
        title: '¿Cancelar cita?',
        text: "La cita cambiará a estado Cancelada.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#7a5a90',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Volver'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "{{ route('citas.cancelar', ':id') }}".replace(':id', idCita);
        }
    });
}
</script>

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>
