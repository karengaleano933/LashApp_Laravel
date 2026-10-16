@include('layouts.header_trabajador', ['titulo' => 'Servicios'])
@include('layouts.sidebar_trabajador')
@vite(['resources/css/servicios.css', 'resources/css/servicio_trabajador.css'])

@php
    // Fallback de inicialización para carga directa o en pruebas
    if (!isset($servicios)) {
        $servicios = \App\Models\Servicio::with('categoria')->withCount('citas')->get();
        $categorias = \App\Models\Categoria::all();
        $totalServicios = $servicios->count();
        $serviciosActivos = $servicios->where('estado', 'activo')->count();
        $totalCategorias = $categorias->count();
        $serviciosInact = $servicios->where('estado', 'inactivo')->count();
        $serviciosDestacados = \App\Models\Servicio::with('categoria')
            ->withCount('citas')
            ->orderBy('citas_count', 'desc')
            ->take(4)
            ->get();
    }

    // Conteo por categoría para el gráfico y la leyenda
    $coloresCategorias = ['#7c1db8', '#c026a0', '#2563eb', '#10b981', '#f59e0b', '#ec4899', '#6366f1'];
    $datosCategorias = [];
    $offsetAcumulado = 0;

    foreach ($categorias as $idx => $cat) {
        $catId = $cat->id;
        $catNombre = $cat->nombre;
        $conteo = $servicios->where('categoria_id', $catId)->count();
        $porcentaje = $totalServicios > 0 ? round(($conteo / $totalServicios) * 100) : 0;
        $color = $coloresCategorias[$idx % count($coloresCategorias)];

        $datosCategorias[] = [
            'id' => $catId,
            'nombre' => $catNombre,
            'conteo' => $conteo,
            'porcentaje' => $porcentaje,
            'color' => $color,
            'offset' => -$offsetAcumulado,
        ];

        $offsetAcumulado += $porcentaje;
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

@if (session('error') || (isset($errors) && $errors->any()))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: "{{ session('error') ?? $errors->first() }}",
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#7c1db8'
    });
});
</script>
@endif

<div class="page-content">

    <!-- ══════════════════ TARJETAS MÉTRICAS ══════════════════ -->
    <div class="servicios-cards-grid">

        <!-- Card 1: Servicios Activos -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon">
                <i class="fa-solid fa-scissors"></i>
            </div>
            <div class="card-sparkle-title">Servicios activos</div>
            <div class="card-sparkle-value">{{ $serviciosActivos ?? 0 }}</div>
            <div class="card-sparkle-sub">
                <i class="fa-solid fa-circle-check"></i> Disponibles en catálogo
            </div>
        </div>

        <!-- Card 2: Categorías -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div class="card-sparkle-title">Categorías registradas</div>
            <div class="card-sparkle-value">{{ $totalCategorias ?? 0 }}</div>
            <div class="card-sparkle-sub" style="color: #6366f1;">
                <i class="fa-solid fa-layer-group"></i> Especialidades
            </div>
        </div>

        <!-- Card 3: Total en Catálogo -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon">
                <i class="fa-solid fa-spa"></i>
            </div>
            <div class="card-sparkle-title">Total en Catálogo</div>
            <div class="card-sparkle-value">{{ $totalServicios ?? 0 }}</div>
            <div class="card-sparkle-sub" style="color: #7c1db8;">
                <i class="fa-solid fa-list-check"></i> Catálogo completo
            </div>
        </div>

        <!-- Card 4: Inactivos / Pausados -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon">
                <i class="fa-solid fa-eye-slash"></i>
            </div>
            <div class="card-sparkle-title">Inactivos / Pausados</div>
            <div class="card-sparkle-value">{{ $serviciosInact ?? 0 }}</div>
            <div class="card-sparkle-sub" style="color: #dc2626;">
                <i class="fa-solid fa-pause"></i> No disponibles
            </div>
        </div>

    </div>

    <!-- ══════════════════ FILA PRINCIPAL: TABLA & PANEL DERECHO ══════════════════ -->
    <div class="servicios-main-grid">

        <!-- COLUMNA IZQUIERDA: LISTA DE SERVICIOS -->
        <div class="servicios-tabla-card">

            <div class="servicios-tabla-header">
                <h3>
                    <i class="fa-solid fa-scissors"></i> Lista de Servicios
                </h3>

                <div class="servicios-header-filtros">
                    <select id="filtroCategoria" class="filtro-categoria-select">
                        <option value="">Todas las categorías</option>
                        @foreach ($categorias as $cat)
                            <option value="{{ strtolower($cat->nombre) }}">{{ $cat->nombre }}</option>
                        @endforeach
                    </select>

                    <button type="button" id="abrirNuevoServicio" class="btn-nuevo-servicio-trigger">
                        <i class="fa-solid fa-plus"></i> Nuevo servicio
                    </button>
                </div>
            </div>

            <!-- Buscador -->
            <div class="servicios-buscador">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="buscarServicio" placeholder="Buscar por nombre, categoría o precio...">
            </div>

            <div class="table-responsive">
                <table class="servicios-table">
                    <thead>
                        <tr>
                            <th>Servicio</th>
                            <th>Categoría</th>
                            <th>Duración</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th style="text-align:center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="serviciosTbody">
                        @forelse ($servicios as $s)
                            @php
                                $imgSrc = !empty($s->imagen_url) ? asset($s->imagen_url) : asset('img/logo.png');
                                $estadoLower = strtolower($s->estado ?? 'activo');
                                $catNombre = $s->categoria?->nombre ?? 'General';

                                // Formatear duración de HH:MM:SS a formato legible
                                $duracionTxt = '30m';
                                if (!empty($s->duracion_minuto)) {
                                    $partes = explode(':', $s->duracion_minuto);
                                    $horas = (int)($partes[0] ?? 0);
                                    $mins  = (int)($partes[1] ?? 0);
                                    $duracionTxt = ($horas > 0 ? $horas . 'h ' : '') . ($mins > 0 ? $mins . 'm' : ($horas > 0 ? '' : '0m'));
                                }
                            @endphp
                            <tr data-nombre="{{ strtolower($s->nombre) }}"
                                data-categoria="{{ strtolower($catNombre) }}"
                                data-precio="{{ $s->precio }}">
                                <td>
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img src="{{ $imgSrc }}" alt="{{ $s->nombre }}" class="servicio-thumb-img" onerror="this.src='{{ asset('img/logo.png') }}';">
                                        <div class="servicio-info-text">
                                            <strong title="{{ $s->nombre }}">{{ $s->nombre }}</strong>
                                            <span>{{ Str::limit($s->descripcion ?? 'Servicio especializado', 40) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="servicio-cat-pill">
                                        {{ $catNombre }}
                                    </span>
                                </td>
                                <td style="color:#2a0d3e; font-weight:600;">
                                    <i class="fa-regular fa-clock" style="color:#7a5a90; font-size:11px;"></i> {{ $duracionTxt }}
                                </td>
                                <td style="color:#2a0d3e; font-weight:700;">
                                    ${{ number_format($s->precio, 0, ',', '.') }}
                                </td>
                                <td>
                                    <span class="servicio-estado-badge {{ $estadoLower === 'activo' ? 'activo' : 'inactivo' }}">
                                        {{ ucfirst($s->estado) }}
                                    </span>
                                </td>
                                <td style="text-align:center; white-space:nowrap;">
                                    <button type="button" class="btn-accion btn-editar"
                                        onclick='abrirModalEditar(@json($s))'
                                        title="Editar Servicio">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <button type="button" class="btn-accion btn-eliminar"
                                        onclick="confirmarEliminarServicio({{ $s->id }})"
                                        title="Eliminar Servicio">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:40px; color:#7a5a90;">
                                    <i class="fa-solid fa-spa" style="font-size:32px; color:#dcc8ee; margin-bottom:10px; display:block;"></i>
                                    No hay servicios registrados en el catálogo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:16px; flex-wrap:wrap; gap:10px; font-size:12.5px; color:#7a5a90;">
                <div id="infoConteoServicios">
                    Mostrando {{ count($servicios) }} de {{ $totalServicios }} servicios
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: GRÁFICO POR CATEGORÍA & DESTACADOS -->
        <div class="servicios-side">

            <!-- Card: Servicios por Categoría -->
            <div class="servicios-side-card">
                <div class="servicios-side-header">
                    <h3>Servicios por categoría</h3>
                    <span>{{ count($categorias) }} categorías</span>
                </div>

                <div class="chart-donut-wrap">
                    <div class="donut-svg-box">
                        <svg viewBox="0 0 36 36" style="width:100%; height:100%; transform:rotate(-90deg);">
                            <circle cx="18" cy="18" r="15.915" fill="none" stroke="#f0e6f8" stroke-width="4.5"></circle>
                            @foreach ($datosCategorias as $catDato)
                                @if ($catDato['porcentaje'] > 0)
                                    <circle cx="18" cy="18" r="15.915" fill="none" 
                                        stroke="{{ $catDato['color'] }}" 
                                        stroke-width="4.5" 
                                        stroke-dasharray="{{ $catDato['porcentaje'] }} 100" 
                                        stroke-dashoffset="{{ $catDato['offset'] }}">
                                    </circle>
                                @endif
                            @endforeach
                        </svg>
                        <div class="donut-center-text">
                            <strong>{{ $totalServicios }}</strong>
                            <span>TOTAL</span>
                        </div>
                    </div>

                    <div class="cat-legend-list">
                        @foreach ($datosCategorias as $catDato)
                            <div class="cat-legend-item">
                                <span>
                                    <span class="cat-dot" style="background: {{ $catDato['color'] }};"></span>
                                    {{ $catDato['nombre'] }}
                                </span>
                                <strong>{{ $catDato['conteo'] }} ({{ $catDato['porcentaje'] }}%)</strong>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Card: Servicios destacados -->
            <div class="servicios-side-card">
                <div class="servicios-side-header">
                    <h3>Servicios más solicitados</h3>
                    <span>Populares</span>
                </div>

                <div class="destacados-list">
                    @forelse ($serviciosDestacados ?? [] as $dest)
                        @php
                            $imgDest = !empty($dest->imagen_url) ? asset($dest->imagen_url) : asset('img/logo.png');
                        @endphp
                        <div class="destacado-item">
                            <img src="{{ $imgDest }}" class="destacado-img" alt="{{ $dest->nombre }}" onerror="this.src='{{ asset('img/logo.png') }}';">
                            <span class="destacado-title">{{ $dest->nombre }}</span>
                            <span class="destacado-badge">
                                {{ $dest->citas_count ?? 0 }} citas
                            </span>
                        </div>
                    @empty
                        <p style="font-size:12px; color:#7a5a90; margin:0; text-align:center;">No hay servicios solicitados aún.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</div> <!-- Cierre de .page-content -->

<!-- ══════════════════ MODAL NUEVO SERVICIO ══════════════════ -->
<div id="modalNuevoServicio" class="modal-lash-overlay" aria-hidden="true" role="dialog">
    <div class="modal-lash-contenido">
        <div class="modal-lash-header">
            <div class="modal-lash-titulo">
                <i class="fa-solid fa-plus-circle"></i>
                <span>Nuevo Servicio</span>
            </div>
            <button type="button" class="modal-lash-cerrar" id="cerrarNuevoServicio" aria-label="Cerrar">&times;</button>
        </div>

        <form action="{{ route('servicios.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-lash-body">
                <div class="modal-lash-grid">

                    <div class="modal-lash-campo">
                        <label for="nombre">Nombre del Servicio *</label>
                        <input type="text" name="nombre" id="nombre" required placeholder="Ej. Extensiones Volumen Ruso">
                    </div>

                    <div class="modal-lash-campo">
                        <label for="categoria_id">Categoría *</label>
                        <select name="categoria_id" id="categoria_id" required>
                            <option value="" disabled selected>Seleccione una categoría</option>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="precio">Precio ($) *</label>
                        <input type="number" step="1000" min="0" name="precio" id="precio" required placeholder="Ej. 180000">
                    </div>

                    <div class="modal-lash-campo">
                        <label for="duracion_minuto">Duración (Minutos) *</label>
                        <input type="number" min="1" name="duracion_minuto" id="duracion_minuto" required placeholder="Ej. 90">
                    </div>

                    <div class="modal-lash-campo">
                        <label for="estado">Estado *</label>
                        <select name="estado" id="estado" required>
                            <option value="activo" selected>Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="imagen">Imagen del Servicio</label>
                        <input type="file" name="imagen" id="imagen" accept="image/*">
                    </div>

                </div>

                <div class="modal-lash-campo full" style="margin-top: 10px;">
                    <label for="descripcion">Descripción</label>
                    <textarea name="descripcion" id="descripcion" rows="3" placeholder="Detalles sobre el procedimiento, beneficios..."></textarea>
                </div>
            </div>

            <div class="modal-lash-footer">
                <button type="button" class="btn-secundario" id="cancelarNuevoServicio">Cancelar</button>
                <button type="submit" class="btn-primario">Guardar Servicio</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════ MODAL EDITAR SERVICIO ══════════════════ -->
<div id="modalEditarServicio" class="modal-lash-overlay" aria-hidden="true" role="dialog">
    <div class="modal-lash-contenido">
        <div class="modal-lash-header">
            <div class="modal-lash-titulo">
                <i class="fa-solid fa-pen-to-square"></i>
                <span>Editar Servicio</span>
            </div>
            <button type="button" class="modal-lash-cerrar" id="cerrarEditarServicio" aria-label="Cerrar">&times;</button>
        </div>

        <form id="formEditarServicio" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="id_servicio" id="edit_id_servicio">

            <div class="modal-lash-body">
                <div class="modal-lash-grid">

                    <div class="modal-lash-campo">
                        <label for="edit_nombre">Nombre del Servicio *</label>
                        <input type="text" name="nombre" id="edit_nombre" required>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="edit_categoria_id">Categoría *</label>
                        <select name="categoria_id" id="edit_categoria_id" required>
                            @foreach ($categorias as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="edit_precio">Precio ($) *</label>
                        <input type="number" step="1000" min="0" name="precio" id="edit_precio" required>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="edit_duracion_minuto">Duración (Minutos) *</label>
                        <input type="number" min="1" name="duracion_minuto" id="edit_duracion_minuto" required>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="edit_estado">Estado *</label>
                        <select name="estado" id="edit_estado" required>
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </select>
                    </div>

                    <div class="modal-lash-campo">
                        <label for="edit_imagen">Cambiar Imagen</label>
                        <input type="file" name="imagen" id="edit_imagen" accept="image/*">
                    </div>

                </div>

                <div class="modal-lash-campo full" style="margin-top: 10px;">
                    <label for="edit_descripcion">Descripción</label>
                    <textarea name="descripcion" id="edit_descripcion" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-lash-footer">
                <button type="button" class="btn-secundario" id="cancelarEditarServicio">Cancelar</button>
                <button type="submit" class="btn-primario">Actualizar Servicio</button>
            </div>
        </form>
    </div>
</div>

<!-- Formulario Oculto para Eliminar Servicio -->
<form id="formEliminarServicio" action="" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- ══════════════════ JAVASCRIPT DE INTERACTIVIDAD ══════════════════ -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // ── Modal Nuevo Servicio ──
    const modalNuevo  = document.getElementById('modalNuevoServicio');
    const btnAbrir    = document.getElementById('abrirNuevoServicio');
    const btnCerrar   = document.getElementById('cerrarNuevoServicio');
    const btnCancelar = document.getElementById('cancelarNuevoServicio');

    function abrirModalNuevo() {
        if (modalNuevo) {
            modalNuevo.classList.add('activo');
            modalNuevo.setAttribute('aria-hidden', 'false');
        }
    }

    function cerrarModalNuevo() {
        if (modalNuevo) {
            modalNuevo.classList.remove('activo');
            modalNuevo.setAttribute('aria-hidden', 'true');
        }
    }

    if (btnAbrir) btnAbrir.addEventListener('click', abrirModalNuevo);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModalNuevo);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModalNuevo);

    // ── Modal Editar Servicio ──
    const modalEditar = document.getElementById('modalEditarServicio');
    const btnCerrarEd = document.getElementById('cerrarEditarServicio');
    const btnCancEd   = document.getElementById('cancelarEditarServicio');
    const formEditar  = document.getElementById('formEditarServicio');

    function cerrarModalEditar() {
        if (modalEditar) {
            modalEditar.classList.remove('activo');
            modalEditar.setAttribute('aria-hidden', 'true');
        }
    }

    if (btnCerrarEd) btnCerrarEd.addEventListener('click', cerrarModalEditar);
    if (btnCancEd) btnCancEd.addEventListener('click', cerrarModalEditar);

    // Cerrar con Escape o clic fuera
    [modalNuevo, modalEditar].forEach(modal => {
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.remove('activo');
                    modal.setAttribute('aria-hidden', 'true');
                }
            });
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            cerrarModalNuevo();
            cerrarModalEditar();
        }
    });

    // Abrir Modal Editar con Datos
    window.abrirModalEditar = function(servicio) {
        if (!modalEditar || !formEditar) return;

        formEditar.action = "{{ route('servicios.update', ':id') }}".replace(':id', servicio.id);

        document.getElementById('edit_id_servicio').value = servicio.id;
        document.getElementById('edit_nombre').value = servicio.nombre || '';
        document.getElementById('edit_categoria_id').value = servicio.categoria_id || '';
        document.getElementById('edit_precio').value = Math.round(servicio.precio || 0);

        // Convertir formato de duración HH:MM:SS a minutos para el input
        let minutos = 60;
        if (servicio.duracion_minuto) {
            const partes = String(servicio.duracion_minuto).split(':');
            minutos = (parseInt(partes[0], 10) * 60) + parseInt(partes[1], 10);
        }
        document.getElementById('edit_duracion_minuto').value = minutos;
        document.getElementById('edit_estado').value = (servicio.estado || 'activo').toLowerCase();
        document.getElementById('edit_descripcion').value = servicio.descripcion || '';

        modalEditar.classList.add('activo');
        modalEditar.setAttribute('aria-hidden', 'false');
    };

    // ── Filtro y Buscador en Tiempo Real ──
    const buscarInput = document.getElementById('buscarServicio');
    const filtroCat   = document.getElementById('filtroCategoria');
    const tbody       = document.getElementById('serviciosTbody');
    const filas       = tbody ? tbody.querySelectorAll('tr[data-nombre]') : [];
    const infoConteo  = document.getElementById('infoConteoServicios');

    function filtrarServicios() {
        const query = (buscarInput ? buscarInput.value : '').toLowerCase().trim();
        const cat   = (filtroCat ? filtroCat.value : '').toLowerCase().trim();

        let visibles = 0;

        filas.forEach(row => {
            const nombre    = (row.getAttribute('data-nombre') || '').toLowerCase();
            const categoria = (row.getAttribute('data-categoria') || '').toLowerCase();
            const precio    = (row.getAttribute('data-precio') || '').toLowerCase();

            const matchQuery = !query || nombre.includes(query) || categoria.includes(query) || precio.includes(query);
            const matchCat   = !cat || categoria === cat;

            if (matchQuery && matchCat) {
                row.style.display = '';
                visibles++;
            } else {
                row.style.display = 'none';
            }
        });

        if (infoConteo) {
            infoConteo.textContent = `Mostrando ${visibles} de {{ $totalServicios }} servicios`;
        }
    }

    if (buscarInput) buscarInput.addEventListener('input', filtrarServicios);
    if (filtroCat) filtroCat.addEventListener('change', filtrarServicios);
});

// Confirmación para Eliminar Servicio
function confirmarEliminarServicio(idServicio) {
    Swal.fire({
        title: '¿Eliminar servicio?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#7a5a90',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('formEliminarServicio');
            if (form) {
                form.action = "{{ route('servicios.destroy', ':id') }}".replace(':id', idServicio);
                form.submit();
            }
        }
    });
}
</script>

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>
