<!-- Incluir Header superior de Admin -->
@include('layouts.header')
@include('layouts.sidebaradmin')

<!-- Contenedor del contenido -->
<div class="page-content">

<!-- ══════════════════ ENCABEZADO ══════════════════ -->
<div class="citas-header">
    <div>
        <h1>Citas</h1>
        <p>Gestiona y organiza tus citas</p>
    </div>
    <button
        type="button"
        class="btn-nueva-cita"
        id="abrirNuevaCita">
        <i class="fa-solid fa-plus"></i>
        Nueva cita
    </button>
</div>

@if (session('success'))
    <div class="alert alert-success" style="padding: 14px 18px; border-radius: 12px; background: #eaf6ef; color: #2e6946; border: 1px solid #cbe9d6; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
        <i class="fa-solid fa-circle-check" style="font-size: 18px; color: #16a34a;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="alert alert-danger" style="padding: 14px 18px; border-radius: 12px; background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; margin-bottom: 20px;">
        <ul style="margin: 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- ══════════════════ TARJETAS MÉTRICAS ══════════════════ -->
<div class="citas-cards">

    <div class="cita-card">
        <div class="cita-card-icon purple"><i class="fa-solid fa-calendar-days"></i></div>
        <div class="cita-card-body">
            <span class="cita-card-label">Citas Hoy</span>
            <strong class="cita-card-value">{{ $citasHoy ?? 0 }}</strong>
            <span class="cita-card-sub purple-text">+0 vs ayer</span>
        </div>
    </div>

    <div class="cita-card">
        <div class="cita-card-icon blue"><i class="fa-solid fa-clock"></i></div>
        <div class="cita-card-body">
            <span class="cita-card-label">Citas esta semana</span>
            <strong class="cita-card-value">{{ $citasSemana ?? 0 }}</strong>
            <span class="cita-card-sub blue-text">+0 vs semana pasada</span>
        </div>
    </div>

    <div class="cita-card">
        <div class="cita-card-icon green"><i class="fa-solid fa-circle-check"></i></div>
        <div class="cita-card-body">
            <span class="cita-card-label">Citas completadas</span>
            <strong class="cita-card-value">{{ $citasComplet ?? 0 }}</strong>
            <span class="cita-card-sub green-text">+0 este mes</span>
        </div>
    </div>

    <div class="cita-card">
        <div class="cita-card-icon red"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="cita-card-body">
            <span class="cita-card-label">Citas canceladas</span>
            <strong class="cita-card-value">{{ $citasCancelad ?? 0 }}</strong>
            <span class="cita-card-sub red-text">+0 este mes</span>
        </div>
    </div>

</div>

<!-- ══════════════════ FILA PRINCIPAL ══════════════════ -->
<div class="citas-main-grid">

    <!-- TABLA PRÓXIMAS CITAS -->
    <div class="citas-tabla-card">
        <div class="citas-tabla-header">
            <h3><i class="fa-solid fa-calendar-check"></i> Próximas citas</h3>
        </div>

        <div class="table-responsive">
            <table id="citasTable" class="citas-table">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Hora</th>
                        <th>Fecha</th>
                        <th>Profesional</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
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
                        <tr>
                            <td>
                                <div class="cliente-cell">
                                    <div class="cliente-avatar-badge">
                                        {{ $inicial }}
                                    </div>
                                    <strong>{{ $clienteNombre }}</strong>
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
                                    <i class="fa-solid fa-user-tie"></i> {{ $profesional }}
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
                            <td colspan="7" class="text-center" style="padding: 24px; color: var(--texto-sec);">
                                No hay citas próximas registradas
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="citas-tabla-footer">
            <button type="button" class="btn-ver-todas" id="btnVerTodasCitas">
                Ver todas las citas
            </button>
            <button type="button" class="btn-cancelar-sel" onclick="cancelarSeleccionadas()">
                Cancelar Citas
            </button>
        </div>
    </div>

    <!-- PANEL DERECHO: Calendario + Filtros -->
    <div class="citas-side">

        <!-- Calendario -->
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

        <!-- Buscar y Filtrar -->
        <div class="filtros-card">
            <h3>Buscar y Filtrar</h3>
            <div class="filtros-search">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="buscarCita" placeholder="Busque por cliente o servicio">
            </div>
            <div class="filtros-row">
                <select id="filtroProfesional" class="filtro-select">
                    <option value="">Todos los profesionales</option>
                    @foreach ($listaTrabajadores ?? [] as $t)
                        @php
                            $tNombre = is_array($t) ? ($t['nombre_completo'] ?? $t['name'] ?? '') : ($t->nombre_completo ?? $t->name ?? '');
                        @endphp
                        @if ($tNombre)
                            <option value="{{ $tNombre }}">{{ $tNombre }}</option>
                        @endif
                    @endforeach
                </select>
                <select id="filtroEstado" class="filtro-select">
                    <option value="">Todos los estados</option>
                    <option value="Confirmada">Confirmada</option>
                    <option value="Pendiente">Pendiente</option>
                    <option value="Cancelada">Cancelada</option>
                    <option value="Completada">Completada</option>
                </select>
                <input type="date" id="filtroFecha" class="filtro-select" value="{{ $hoy ?? date('Y-m-d') }}">
            </div>
        </div>

    </div>
</div>

<!-- ══════════════════ MODAL NUEVA CITA ══════════════════ -->
<div id="modalNuevaCita" class="modal-lash-overlay" aria-hidden="true" role="dialog" aria-labelledby="modalTitulo">

    <div class="modal-lash-contenido">

        <!-- CABECERA -->
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

            <button
                type="button"
                class="modal-lash-cerrar"
                id="cerrarNuevaCita"
                aria-label="Cerrar modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- FORMULARIO -->
        <form
            action="{{ Route::has('citas.store') ? route('citas.store') : '#' }}"
            method="POST"
            class="modal-lash-form"
            id="formNuevaCita">
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
                                <option value="" disabled selected>Seleccionar cliente...</option>
                                @foreach ($listaClientes ?? [] as $c)
                                    @php
                                        $cId = is_array($c) ? ($c['id_cliente'] ?? $c['id'] ?? '') : ($c->id_cliente ?? $c->id ?? '');
                                        $cNombre = is_array($c) ? ($c['nombre_completo'] ?? $c['name'] ?? '') : ($c->nombre_completo ?? $c->name ?? '');
                                        $cCorreo = is_array($c) ? ($c['correo'] ?? $c['email'] ?? '') : ($c->correo ?? $c->email ?? '');
                                    @endphp
                                    <option value="{{ $cId }}">
                                        {{ $cNombre }} {{ $cCorreo ? '(' . $cCorreo . ')' : '' }}
                                    </option>
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
                                <option value="" disabled selected>Seleccionar servicio...</option>
                                @foreach ($listaServicios ?? [] as $s)
                                    @php
                                        $sId = is_array($s) ? ($s['id_servicio'] ?? $s['id'] ?? '') : ($s->id_servicio ?? $s->id ?? '');
                                        $sNombre = is_array($s) ? ($s['nombre'] ?? '') : ($s->nombre ?? '');
                                        $sPrecio = is_array($s) ? ($s['precio'] ?? 0) : ($s->precio ?? 0);
                                        $sDuracion = is_array($s) ? ($s['duracion_minutos'] ?? 0) : ($s->duracion_minutos ?? 0);
                                    @endphp
                                    <option value="{{ $sId }}">
                                        {{ $sNombre }} — ${{ number_format((float)$sPrecio, 0, ',', '.') }} ({{ $sDuracion }} min)
                                    </option>
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
                                <option value="" disabled selected>Seleccionar profesional...</option>
                                @foreach ($listaTrabajadores ?? [] as $t)
                                    @php
                                        $tId = is_array($t) ? ($t['id_trabajador'] ?? $t['id'] ?? '') : ($t->id_trabajador ?? $t->id ?? '');
                                        $tNombre = is_array($t) ? ($t['nombre_completo'] ?? $t['name'] ?? '') : ($t->nombre_completo ?? $t->name ?? '');
                                        $tEsp = is_array($t) ? ($t['especialidad'] ?? '') : ($t->especialidad ?? '');
                                    @endphp
                                    <option value="{{ $tId }}">
                                        {{ $tNombre }} {{ $tEsp ? '(' . $tEsp . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down select-chevron"></i>
                        </div>
                    </div>

                    <!-- ESTADO -->
                    <div class="modal-lash-campo">
                        <label for="estado">
                            <i class="fa-solid fa-circle-info"></i> Estado <span class="required">*</span>
                        </label>
                        <div class="input-icon-wrap">
                            <select name="estado" id="estado">
                                <option value="Pendiente" selected>🟡 Pendiente</option>
                                <option value="Confirmada">🟢 Confirmada</option>
                            </select>
                            <i class="fa-solid fa-chevron-down select-chevron"></i>
                        </div>
                    </div>

                    <!-- FECHA -->
                    <div class="modal-lash-campo">
                        <label for="fecha_cita">
                            <i class="fa-solid fa-calendar-day"></i> Fecha de la cita <span class="required">*</span>
                        </label>
                        <input
                            type="date"
                            name="fecha_cita"
                            id="fecha_cita"
                            min="{{ $hoy ?? date('Y-m-d') }}"
                            value="{{ $hoy ?? date('Y-m-d') }}"
                            required>
                    </div>

                    <!-- HORA -->
                    <div class="modal-lash-campo">
                        <label for="hora_inicio">
                            <i class="fa-solid fa-clock"></i> Hora de inicio <span class="required">*</span>
                        </label>
                        <input
                            type="time"
                            name="hora_inicio"
                            id="hora_inicio"
                            required>
                    </div>

                    <!-- OBSERVACIONES -->
                    <div class="modal-lash-campo modal-lash-completo">
                        <label for="observaciones">
                            <i class="fa-solid fa-comment-dots"></i> Observaciones o notas adicionales
                        </label>
                        <textarea
                            name="observaciones"
                            id="observaciones"
                            rows="2"
                            placeholder="Detalles sobre el diseño, alergias, o solicitudes del cliente (opcional)..."></textarea>
                    </div>

                </div>

            </div>

            <!-- FOOTER -->
            <div class="modal-lash-footer">
                <button
                    type="button"
                    class="modal-lash-btn-cancelar"
                    id="cancelarNuevaCita">
                    Cancelar
                </button>

                <button
                    type="submit"
                    class="modal-lash-btn-agendar">
                    <i class="fa-solid fa-calendar-check"></i>
                    Agendar cita
                </button>
            </div>

        </form>

    </div>

</div>

</div> <!-- Cierre de page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    // Inicialización DataTables
    if ($.fn.DataTable.isDataTable('#citasTable')) {
        $('#citasTable').DataTable().destroy();
    }
    $.fn.dataTable.ext.errMode = 'none';
    const citasTableDt = $('#citasTable').DataTable({
        language: { 
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            emptyTable: 'No hay citas próximas registradas',
            zeroRecords: 'No se encontraron citas coincidentes'
        },
        responsive: true,
        dom: 'rt',
        searching: true,
        lengthChange: false,
        info: false,
        paging: false
    });

    // Filtro búsqueda en tiempo real conectado a DataTables
    const buscarCitaInput = document.getElementById('buscarCita');
    if (buscarCitaInput) {
        buscarCitaInput.addEventListener('input', function() {
            citasTableDt.search(this.value).draw();
        });
    }

    // Filtro por profesional
    $('#filtroProfesional').on('change', function() {
        citasTableDt.column(4).search(this.value).draw();
    });

    // Filtro por estado
    $('#filtroEstado').on('change', function() {
        citasTableDt.column(5).search(this.value).draw();
    });
});

// Cancelar cita con SweetAlert2
function confirmarCancelar(id) {
    Swal.fire({
        title: '¿Cancelar esta cita?',
        text: 'Esta acción actualizará el estado de la cita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
    }).then((result) => {
        if (result.isConfirmed) {
            @if (Route::has('citas.cancelar'))
                window.location.href = "{{ route('citas.cancelar', ':id') }}".replace(':id', id);
            @else
                Swal.fire({
                    icon: 'info',
                    title: 'Acción registrada',
                    text: 'ID de cita ' + id + ' marcada para cancelación.',
                    confirmButtonColor: '#7c1db8'
                });
            @endif
        }
    });
}

function cancelarSeleccionadas() {
    Swal.fire({
        icon: 'info',
        title: 'Selecciona las citas',
        text: 'Marca las citas que deseas cancelar en la tabla.',
        confirmButtonColor: '#7c1db8'
    });
}

// ── Calendario Dinámico ──
const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
let calYear = new Date().getFullYear();
let calMonth = new Date().getMonth();

function renderCal() {
    const grid  = document.getElementById('calGrid');
    const label = document.getElementById('calMesAnio');
    if (!grid || !label) return;

    label.textContent = meses[calMonth] + ' ' + calYear;
    grid.innerHTML = '';

    const firstDay = (new Date(calYear, calMonth, 1).getDay() + 6) % 7; // Lunes = 0
    const days     = new Date(calYear, calMonth + 1, 0).getDate();
    const today    = new Date();

    for (let i = 0; i < firstDay; i++) {
        const el = document.createElement('div');
        el.className = 'cal-cell cal-empty';
        grid.appendChild(el);
    }

    for (let d = 1; d <= days; d++) {
        const el = document.createElement('div');
        const isToday = today.getFullYear() === calYear && today.getMonth() === calMonth && today.getDate() === d;
        el.className = 'cal-cell' + (isToday ? ' cal-today' : '');
        el.textContent = d;
        grid.appendChild(el);
    }
}

const calPrevBtn = document.getElementById('calPrev');
const calNextBtn = document.getElementById('calNext');

if (calPrevBtn) {
    calPrevBtn.addEventListener('click', () => {
        calMonth--;
        if (calMonth < 0) { calMonth = 11; calYear--; }
        renderCal();
    });
}

if (calNextBtn) {
    calNextBtn.addEventListener('click', () => {
        calMonth++;
        if (calMonth > 11) { calMonth = 0; calYear++; }
        renderCal();
    });
}

renderCal();

// ── Manejo del Modal Nueva Cita ──
document.addEventListener('DOMContentLoaded', function () {
    const modal    = document.getElementById('modalNuevaCita');
    const abrir    = document.getElementById('abrirNuevaCita');
    const cerrar   = document.getElementById('cerrarNuevaCita');
    const cancelar = document.getElementById('cancelarNuevaCita');

    if (!modal || !abrir || !cerrar || !cancelar) return;

    function abrirModal() {
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        modal.classList.remove('activo');
        document.body.style.overflow = '';
    }

    abrir.addEventListener('click', abrirModal);
    cerrar.addEventListener('click', cerrarModal);
    cancelar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            cerrarModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('activo')) {
            cerrarModal();
        }
    });
});
</script>
</body>
</html>
