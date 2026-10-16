@include('layouts.header_trabajador', ['titulo' => 'Mi Horario'])
@include('layouts.sidebar_trabajador')
@vite(['resources/css/horario_trabajador.css'])

@php
    use Carbon\Carbon;

    if (!isset($horarios)) {
        $user = auth()->user();
        $trabajador = $user?->trabajador ?? \App\Models\Trabajador::where('user_id', $user?->id)->first();
        $trabajadorId = $trabajador?->id;
        $horarios = \App\Models\Horario::when($trabajadorId, fn($q) => $q->where('trabajador_id', $trabajadorId))
            ->withCount('citas')
            ->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'asc')
            ->get();
        $totalHorarios = $horarios->count();
        $disponibles = $horarios->filter(fn($h) => in_array(strtolower($h->estado), ['activo', 'disponible']) && ($h->citas_count == 0))->count();
        $ocupados = $horarios->filter(fn($h) => $h->citas_count > 0 || strtolower($h->estado) === 'ocupado')->count();
        $proximoHorario = $horarios->filter(fn($h) => $h->fecha && Carbon::parse($h->fecha)->greaterThanOrEqualTo(Carbon::today()))->sortBy('fecha')->first();
        $hoy = Carbon::today()->toDateString();
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
        confirmButtonColor: '#7c1db8',
        confirmButtonText: 'Aceptar'
    });
});
</script>
@endif

@if (session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'warning',
        title: 'Aviso',
        text: "{{ session('error') }}",
        confirmButtonColor: '#7c1db8',
        confirmButtonText: 'Entendido'
    });
});
</script>
@endif

<main class="horario-page-container">

    <!-- ── Encabezado y Botón de Acción ── -->
    <div class="horario-header-bar">
        <div class="horario-title-group">
            <h1>Mi Horario y Disponibilidad</h1>
            <p>Configura tus turnos de trabajo, descansos y bloques de atención para tus clientes</p>
        </div>
        <button type="button" class="btn-crear-horario" id="btnAbrirModalHorario">
            <i class="fa-solid fa-plus"></i> Agregar Bloque Horario
        </button>
    </div>

    <!-- ── Tarjetas de Métricas ── -->
    <div class="horario-stats-grid">
        <div class="stat-card-horario">
            <div class="stat-horario-icon purple">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div class="stat-horario-content">
                <h4>Total Bloques</h4>
                <div class="stat-val">{{ $totalHorarios ?? count($horarios) }}</div>
            </div>
        </div>

        <div class="stat-card-horario">
            <div class="stat-horario-icon green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div class="stat-horario-content">
                <h4>Disponibles</h4>
                <div class="stat-val">{{ $disponibles ?? 0 }}</div>
            </div>
        </div>

        <div class="stat-card-horario">
            <div class="stat-horario-icon amber">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="stat-horario-content">
                <h4>Ocupados / Con Cita</h4>
                <div class="stat-val">{{ $ocupados ?? 0 }}</div>
            </div>
        </div>

        <div class="stat-card-horario">
            <div class="stat-horario-icon blue">
                <i class="fa-regular fa-calendar-days"></i>
            </div>
            <div class="stat-horario-content">
                <h4>Próxima Fecha</h4>
                <div class="stat-val" style="font-size: 16px; font-weight: 700; margin-top: 4px;">
                    {{ isset($proximoHorario) && $proximoHorario->fecha ? Carbon::parse($proximoHorario->fecha)->format('d/m/Y') : 'Sin definir' }}
                </div>
            </div>
        </div>
    </div>

    <!-- ── Tabla Principal de Horarios ── -->
    <div class="horario-card-wrap">
        <div class="horario-card-toolbar">
            <div class="horario-filter-tabs">
                <button type="button" class="horario-tab-btn active" data-filter="all">Todos ({{ count($horarios) }})</button>
                <button type="button" class="horario-tab-btn" data-filter="disponible">Disponibles ({{ $disponibles ?? 0 }})</button>
                <button type="button" class="horario-tab-btn" data-filter="ocupado">Ocupados ({{ $ocupados ?? 0 }})</button>
            </div>

            <div class="horario-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="horarioSearchInput" placeholder="Filtrar por fecha u hora...">
            </div>
        </div>

        <div class="table-horarios-responsive">
            <table class="table-horarios-custom" id="tablaHorarios">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Horario</th>
                        <th>Duración</th>
                        <th>Estado</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($horarios as $h)
                        @php
                            $fechaFormateada = $h->fecha ? Carbon::parse($h->fecha)->translatedFormat('l, d \d\e F \d\e Y') : 'Fecha no especificada';
                            $fechaCorta = $h->fecha ? Carbon::parse($h->fecha)->format('d/m/Y') : '';
                            $horaIni = $h->hora_inicio ? date('g:i a', strtotime($h->hora_inicio)) : '--:--';
                            $horaFin = $h->hora_fin ? date('g:i a', strtotime($h->hora_fin)) : '--:--';

                            $esOcupado = ($h->citas_count > 0) || (strtolower($h->estado ?? '') === 'ocupado');
                            $filtroTipo = $esOcupado ? 'ocupado' : 'disponible';

                            $duracionTxt = '1h 00m';
                            if ($h->hora_inicio && $h->hora_fin) {
                                try {
                                    $ini = Carbon::parse($h->hora_inicio);
                                    $fin = Carbon::parse($h->hora_fin);
                                    $diffMin = $ini->diffInMinutes($fin);
                                    $horas = floor($diffMin / 60);
                                    $mins = $diffMin % 60;
                                    $duracionTxt = ($horas > 0 ? $horas . 'h ' : '') . ($mins > 0 ? $mins . 'm' : ($horas > 0 ? '00m' : '0m'));
                                } catch (\Exception $e) {}
                            }
                        @endphp
                        <tr data-filter-type="{{ $filtroTipo }}" data-search-text="{{ strtolower($fechaFormateada . ' ' . $fechaCorta . ' ' . $horaIni . ' ' . $horaFin) }}">
                            <td>
                                <div class="fecha-cell">
                                    <div class="fecha-calendar-icon">
                                        <i class="fa-regular fa-calendar"></i>
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: #2a0d3e;">{{ ucfirst($fechaFormateada) }}</div>
                                        <small style="color: #7a5a90;">{{ $fechaCorta }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="hora-badge">
                                    <i class="fa-regular fa-clock" style="color: #7c1db8;"></i>
                                    {{ $horaIni }} &mdash; {{ $horaFin }}
                                </span>
                            </td>
                            <td>
                                <span style="font-weight: 600; color: #7a5a90;">
                                    <i class="fa-solid fa-hourglass-half" style="color: #c9a84c; margin-right: 4px;"></i>
                                    {{ $duracionTxt }}
                                </span>
                            </td>
                            <td>
                                @if ($esOcupado)
                                    <span class="badge-ocupado">
                                        <i class="fa-solid fa-circle-dot" style="font-size: 8px;"></i>
                                        {{ $h->citas_count > 0 ? 'Cita Asignada (' . $h->citas_count . ')' : 'Ocupado' }}
                                    </span>
                                @else
                                    <span class="badge-disponible">
                                        <i class="fa-solid fa-circle-check" style="font-size: 8px;"></i>
                                        Disponible
                                    </span>
                                @endif
                            </td>
                            <td style="text-align: center;">
                                <form action="{{ route('trabajador.horario.destroy', $h->id) }}" method="POST" class="form-eliminar-horario" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-eliminar-horario" title="Eliminar este bloque" onclick="confirmarEliminarHorario(this)">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 48px; color: #7a5a90;">
                                <i class="fa-regular fa-calendar-xmark" style="font-size: 38px; color: #dcc8ee; margin-bottom: 12px; display: block;"></i>
                                <strong style="font-size: 15px; color: #2a0d3e; display: block; margin-bottom: 6px;">No tienes bloques de horario registrados</strong>
                                <p style="font-size: 13px; margin: 0 0 16px;">Comienza agregando tus primeros bloques de disponibilidad para que tus clientas puedan agendar.</p>
                                <button type="button" class="btn-crear-horario" onclick="document.getElementById('modalNuevoHorario').classList.add('activo')">
                                    <i class="fa-solid fa-plus"></i> Agregar Horario Ahora
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- ══════════════════ MODAL AGREGAR HORARIO ══════════════════ -->
<div id="modalNuevoHorario" class="modal-lash-overlay">
    <div class="modal-lash-contenido">
        <div class="modal-lash-header">
            <div class="modal-lash-titulo">
                <i class="fa-solid fa-clock"></i>
                <span>Nuevo Bloque Horario</span>
            </div>
            <button type="button" class="modal-lash-cerrar" id="btnCerrarModalHorario">&times;</button>
        </div>

        <form action="{{ route('trabajador.horario.store') }}" method="POST">
            @csrf
            <div class="modal-lash-body">
                <div class="modal-lash-grid">
                    <div class="modal-lash-campo full-width">
                        <label for="fecha">Fecha del Turno <span style="color:#dc2626;">*</span></label>
                        <input type="date" name="fecha" id="fecha" value="{{ $hoy }}" min="{{ $hoy }}" required>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="hora_inicio">Hora de Inicio <span style="color:#dc2626;">*</span></label>
                        <input type="time" name="hora_inicio" id="hora_inicio" value="09:00" required>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="hora_fin">Hora de Fin <span style="color:#dc2626;">*</span></label>
                        <input type="time" name="hora_fin" id="hora_fin" value="10:30" required>
                    </div>

                    <div class="modal-lash-campo full-width">
                        <label for="estado">Estado Inicial</label>
                        <select name="estado" id="estado">
                            <option value="activo" selected>Disponible para agendar</option>
                            <option value="ocupado">Ocupado / Reservado</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-lash-footer">
                <button type="button" class="btn-secundario" id="btnCancelarModalHorario">Cancelar</button>
                <button type="submit" class="btn-primario">
                    <i class="fa-solid fa-check"></i> Guardar Bloque
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Control del Modal
    const modal = document.getElementById('modalNuevoHorario');
    const btnAbrir = document.getElementById('btnAbrirModalHorario');
    const btnCerrar = document.getElementById('btnCerrarModalHorario');
    const btnCancelar = document.getElementById('btnCancelarModalHorario');

    function abrirModal() {
        if (modal) modal.classList.add('activo');
    }

    function cerrarModal() {
        if (modal) modal.classList.remove('activo');
    }

    if (btnAbrir) btnAbrir.addEventListener('click', abrirModal);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModal);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModal);

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) cerrarModal();
        });
    }

    // 2. Filtros por Tabs (Todos / Disponibles / Ocupados)
    const tabBtns = document.querySelectorAll('.horario-tab-btn');
    const searchInput = document.getElementById('horarioSearchInput');
    const tableRows = document.querySelectorAll('#tablaHorarios tbody tr');

    let currentFilter = 'all';

    function aplicarFiltros() {
        const query = (searchInput?.value || '').trim().toLowerCase();

        tableRows.forEach(row => {
            const filterType = row.getAttribute('data-filter-type');
            const searchText = row.getAttribute('data-search-text') || '';

            if (!filterType) return; // Fila de estado vacío

            const matchesFilter = (currentFilter === 'all') || (filterType === currentFilter);
            const matchesSearch = !query || searchText.includes(query);

            row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
        });
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter') || 'all';
            aplicarFiltros();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', aplicarFiltros);
    }

    // 3. Confirmación SweetAlert para eliminar horario
    window.confirmarEliminarHorario = function(btn) {
        Swal.fire({
            title: '¿Eliminar este bloque horario?',
            text: 'Esta acción no se puede deshacer si no tiene citas asignadas.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#7a5a90',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.closest('form').submit();
            }
        });
    };
});
</script>

@include('layouts.footer')
