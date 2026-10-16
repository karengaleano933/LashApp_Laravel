@include('layouts.header_trabajador', ['titulo' => 'Mis Citas'])
@include('layouts.sidebar_trabajador')
@vite(['resources/css/citas.css', 'resources/css/citas_trabajador.css'])

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
            <div class="citas-tabla-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h3><i class="fa-solid fa-calendar-check"></i> Próximas citas</h3>
            </div>

            <!-- Barra de Mostrar registros y Buscar -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; flex-wrap:wrap; gap:10px; font-size:13px; color:#7a5a90;">
                <div>
                    Mostrar 
                    <select id="selectRegistros" style="padding:4px 8px; border-radius:8px; border:1px solid #dcc8ee; color:#2a0d3e; outline:none;">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    registros
                </div>
                <div style="display:flex; align-items:center; gap:6px;">
                    <label for="buscarTabla">Buscar:</label>
                    <input type="text" id="buscarTabla" style="padding:6px 12px; border-radius:20px; border:1px solid #dcc8ee; background:#fbf8fe; outline:none; font-size:13px;" placeholder="Filtrar citas...">
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
                                        <div class="cliente-avatar-badge">
                                            {{ $inicial }}
                                        </div>
                                        <strong style="margin-left:8px;">{{ $clienteNombre }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="servicio-pill">
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
                                    <span class="profesional-pill">
                                        <i class="fa-solid fa-user-tie" style="color:#7c1db8;"></i> {{ $profesional }}
                                    </span>
                                </td>
                                <td>
                                    <span class="estado-badge estado-{{ $estadoClase }}">
                                        {{ $estado }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn-accion btn-cancelar"
                                        onclick="confirmarCancelar({{ $idCita }})"
                                        title="Cancelar Cita">
                                        <i class="fa-solid fa-ban"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align:center; padding:40px; color:#9070a8;">
                                    No hay citas registradas en tu agenda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación y contador -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:15px; flex-wrap:wrap; gap:10px; font-size:13px; color:#7a5a90;">
                <div id="infoPaginacion">
                    Mostrando {{ count($proximasCitas ?? []) }} registros
                </div>
            </div>

            <div class="citas-tabla-footer" style="margin-top:20px; display:flex; gap:12px;">
                <button type="button" class="btn-ver-todas" onclick="limpiarFiltros()" style="padding:10px 20px; border-radius:25px; border:1px solid #7c1db8; background:#fff; color:#7c1db8; font-weight:700; cursor:pointer;">
                    Ver todas las citas
                </button>
                <button type="button" class="btn-cancelar-sel" onclick="limpiarFiltros()" style="padding:10px 20px; border-radius:25px; border:1px solid #dcc8ee; background:#fff; color:#7a5a90; font-weight:600; cursor:pointer;">
                    Limpiar Filtros
                </button>
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
<div id="modalNuevaCita" class="modal-lash-overlay">
    <div class="modal-lash-contenido">
        <div class="modal-lash-header">
            <div class="modal-lash-titulo">
                <i class="fa-solid fa-calendar-plus"></i>
                <span>Nueva Cita</span>
            </div>
            <button type="button" class="modal-lash-cerrar" id="cerrarNuevaCita" aria-label="Cerrar">&times;</button>
        </div>

        <form action="{{ route('citas.store') }}" method="POST">
            @csrf
            <div class="modal-lash-body">
                <div class="modal-lash-grid">

                    <div class="modal-lash-campo">
                        <label for="id_cliente">Cliente</label>
                        <select name="id_cliente" id="id_cliente" required>
                            <option value="">Seleccione un cliente</option>
                            @foreach ($listaClientes ?? [] as $c)
                                @php
                                    $cId = is_array($c) ? ($c['id_cliente'] ?? $c['id'] ?? '') : ($c->id_cliente ?? $c->id ?? '');
                                    $cNom = is_array($c) ? ($c['nombre_completo'] ?? '') : ($c->nombre_completo ?? '');
                                @endphp
                                <option value="{{ $cId }}">{{ $cNom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="id_servicio">Servicio</label>
                        <select name="id_servicio" id="id_servicio" required>
                            <option value="">Seleccione un servicio</option>
                            @foreach ($listaServicios ?? [] as $s)
                                @php
                                    $sId = is_array($s) ? ($s['id_servicio'] ?? $s['id'] ?? '') : ($s->id_servicio ?? $s->id ?? '');
                                    $sNom = is_array($s) ? ($s['nombre'] ?? '') : ($s->nombre ?? '');
                                    $sPrecio = is_array($s) ? ($s['precio'] ?? 0) : ($s->precio ?? 0);
                                @endphp
                                <option value="{{ $sId }}">{{ $sNom }} - ${{ number_format($sPrecio, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="id_trabajador">Profesional</label>
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
                    </div>

                    <div class="modal-lash-campo">
                        <label for="fecha_cita">Fecha</label>
                        <input type="date" name="fecha_cita" id="fecha_cita" value="{{ $hoy ?? date('Y-m-d') }}" required>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="hora_inicio">Hora</label>
                        <input type="time" name="hora_inicio" id="hora_inicio" value="09:00" required>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado">
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmado">Confirmada</option>
                            <option value="finalizado">Completada</option>
                        </select>
                    </div>

                </div>

                <div class="modal-lash-campo full">
                    <label for="observaciones">Observaciones</label>
                    <textarea name="observaciones" id="observaciones" rows="3" placeholder="Notas sobre el servicio o cliente..."></textarea>
                </div>
            </div>

            <div class="modal-lash-footer">
                <button type="button" class="btn-secundario" id="cancelarNuevaCita">Cancelar</button>
                <button type="submit" class="btn-primario">Guardar Cita</button>
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

    if (btnAbrir && modalNueva) {
        btnAbrir.addEventListener('click', () => modalNueva.classList.add('activo'));
    }
    if (btnCerrar && modalNueva) {
        btnCerrar.addEventListener('click', () => modalNueva.classList.remove('activo'));
    }
    if (btnCancel && modalNueva) {
        btnCancel.addEventListener('click', () => modalNueva.classList.remove('activo'));
    }

    // ── Filtrar Tabla en Tiempo Real ──
    const buscarTabla    = document.getElementById('buscarTabla');
    const buscarCita     = document.getElementById('buscarCita');
    const filtroProf     = document.getElementById('filtroProfesional');
    const filtroEst      = document.getElementById('filtroEstado');
    const filtroFec      = document.getElementById('filtroFecha');
    const tbody          = document.getElementById('citasTbody');
    const filas          = tbody ? tbody.querySelectorAll('tr') : [];

    function filtrar() {
        const queryT = (buscarTabla ? buscarTabla.value : '').toLowerCase().trim();
        const queryC = (buscarCita ? buscarCita.value : '').toLowerCase().trim();
        const query  = queryT || queryC;

        const prof = (filtroProf ? filtroProf.value : '').toLowerCase().trim();
        const est  = (filtroEst ? filtroEst.value : '').toLowerCase().trim();
        const fec  = (filtroFec ? filtroFec.value : '').trim();

        filas.forEach(row => {
            const cliente = (row.getAttribute('data-cliente') || '').toLowerCase();
            const servicio = (row.getAttribute('data-servicio') || '').toLowerCase();
            const profesional = (row.getAttribute('data-profesional') || '').toLowerCase();
            const estado = (row.getAttribute('data-estado') || '').toLowerCase();
            const fecha = (row.getAttribute('data-fecha') || '').trim();

            const matchQuery = !query || cliente.includes(query) || servicio.includes(query);
            const matchProf  = !prof || profesional.includes(prof);
            const matchEst   = !est || estado.includes(est);
            const matchFec   = !fec || fecha === fec;

            if (matchQuery && matchProf && matchEst && matchFec) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (buscarTabla) buscarTabla.addEventListener('input', filtrar);
    if (buscarCita) buscarCita.addEventListener('input', filtrar);
    if (filtroProf) filtroProf.addEventListener('change', filtrar);
    if (filtroEst) filtroEst.addEventListener('change', filtrar);
    if (filtroFec) filtroFec.addEventListener('change', filtrar);

    window.limpiarFiltros = function() {
        if (buscarTabla) buscarTabla.value = '';
        if (buscarCita) buscarCita.value = '';
        if (filtroProf) filtroProf.value = '';
        if (filtroEst) filtroEst.value = '';
        if (filtroFec) filtroFec.value = '';
        filtrar();
    };

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
