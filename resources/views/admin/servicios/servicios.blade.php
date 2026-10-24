<!-- Incluir Header superior de Admin -->
@include('layouts.header', ['titulo' => 'Gestión de Servicios'])
@include('layouts.sidebaradmin')

<!-- Estilos y Scripts específicos de servicios -->
@vite([
    'resources/css/servicios.css',
    'resources/js/modules/admin/servicios.js'
])

<!-- Contenido Principal -->
<div class="page-content" id="serviciosContainer" data-base-url="{{ url('/admin/servicios') }}">

<!-- ══════════════════ ALERTAS DE ACCIÓN ══════════════════ -->
@if (session('success'))
    <div class="alert alert-success" style="padding: 14px 18px; border-radius: 12px; background: #eaf6ef; color: #2e6946; border: 1px solid #cbe9d6; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
        <i class="fa-solid fa-circle-check" style="font-size: 18px; color: #16a34a;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" style="padding: 14px 18px; border-radius: 12px; background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 18px; color: #dc2626;"></i>
        <span>{{ session('error') }}</span>
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

<!-- ══════════════════ ENCABEZADO ══════════════════ -->
<div class="servicios-header">
    <div class="servicios-titulo">
        <div class="servicios-icono">
            <i class="fa-solid fa-spa"></i>
        </div>
        <div>
            <h1>Catálogo de Servicios</h1>
            <p>Gestiona los servicios, tarifas, imágenes y duraciones ofrecidas en LashApp</p>
        </div>
    </div>
    <button type="button" class="btn-nuevo-servicio" id="abrirNuevoServicio">
        <i class="fa-solid fa-plus"></i> Nuevo Servicio
    </button>
</div>

<!-- ==========================================
     TARJETAS DE ESTADÍSTICAS
========================================== -->
<div class="servicios-cards">

    <div class="servicio-card">
        <div class="servicio-card-icon purple">
            <i class="fa-solid fa-spa"></i>
        </div>
        <div class="servicio-card-body">
            <span>Total Servicios</span>
            <strong>{{ $totalServicios ?? (isset($servicios) ? count($servicios) : 0) }}</strong>
            <small class="purple-text">
                <i class="fa-solid fa-list"></i> En catálogo
            </small>
        </div>
    </div>

    <div class="servicio-card">
        <div class="servicio-card-icon green">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="servicio-card-body">
            <span>Servicios Activos</span>
            @php
                $activosCount = $serviciosActivos ?? (isset($servicios) ? collect($servicios)->filter(function($s) {
                    $est = is_array($s) ? ($s['estado'] ?? '') : ($s->estado ?? '');
                    return strtolower($est) === 'activo';
                })->count() : 0);
            @endphp
            <strong>{{ $activosCount }}</strong>
            <small class="green-text">
                <i class="fa-solid fa-check"></i> Disponibles para citas
            </small>
        </div>
    </div>

    <div class="servicio-card">
        <div class="servicio-card-icon pink">
            <i class="fa-solid fa-tags"></i>
        </div>
        <div class="servicio-card-body">
            <span>Categorías</span>
            <strong>{{ $totalCategorias ?? (isset($categorias) ? count($categorias) : 0) }}</strong>
            <small class="pink-text">
                <i class="fa-solid fa-layer-group"></i> Especialidades
            </small>
        </div>
    </div>

    <div class="servicio-card">
        <div class="servicio-card-icon blue">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="servicio-card-body">
            <span>Inactivos / Pausados</span>
            @php
                $inactivosCount = $serviciosInact ?? (isset($servicios) ? collect($servicios)->filter(function($s) {
                    $est = is_array($s) ? ($s['estado'] ?? '') : ($s->estado ?? '');
                    return strtolower($est) === 'inactivo';
                })->count() : 0);
            @endphp
            <strong>{{ $inactivosCount }}</strong>
            <small class="blue-text">Temporalmente pausados</small>
        </div>
    </div>

</div>

<!-- ==========================================
     CONTENIDO PRINCIPAL
========================================== -->
<div class="servicios-main-grid">

    <!-- TABLA DE SERVICIOS -->
    <div class="servicios-tabla-card">

        <div class="servicios-tabla-header">
            <h3>
                <i class="fa-solid fa-table-list"></i>
                Lista de Servicios Registrados
            </h3>

            <div class="servicios-header-actions">
                <select id="filtroCategoria">
                    <option value="">Todas las categorías</option>
                    @foreach ($categorias ?? [] as $cat)
                        @php
                            $catNombre = is_array($cat) ? ($cat['nombre'] ?? '') : ($cat->nombre ?? '');
                        @endphp
                        @if ($catNombre)
                            <option value="{{ $catNombre }}">{{ $catNombre }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        </div>

        <!-- BUSCADOR -->
        <div class="servicios-buscador">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="buscarServicio" placeholder="Buscar servicio por nombre, categoría o precio...">
        </div>

        <!-- TABLA -->
        <div class="table-responsive">
            <table id="tablaServicios" class="servicios-table">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Servicio</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Duración</th>
                        <th>Estado</th>
                        <th class="text-center" style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($servicios ?? [] as $srv)
                        @php
                            $srvId = is_array($srv) ? ($srv['id'] ?? $srv['id_servicio'] ?? '') : ($srv->id ?? $srv->id_servicio ?? '');
                            $srvNombre = is_array($srv) ? ($srv['nombre'] ?? 'Servicio') : ($srv->nombre ?? 'Servicio');
                            $srvDesc = is_array($srv) ? ($srv['descripcion'] ?? '') : ($srv->descripcion ?? '');
                            
                            $srvCatNombre = is_array($srv)
                                ? ($srv['categoria'] ?? $srv['categoria_nombre'] ?? 'General')
                                : ($srv->categoria->nombre ?? $srv->categoria ?? 'General');
                            
                            $srvCatId = is_array($srv)
                                ? ($srv['categoria_id'] ?? $srv['id_categoria'] ?? '')
                                : ($srv->categoria_id ?? $srv->id_categoria ?? '');

                            $srvPrecio = is_array($srv) ? ($srv['precio'] ?? 0) : ($srv->precio ?? 0);

                            $rawDur = is_array($srv) ? ($srv['duracion_minuto'] ?? $srv['duracion_minutos'] ?? 30) : ($srv->duracion_minuto ?? $srv->duracion_minutos ?? 30);
                            if (is_string($rawDur) && str_contains($rawDur, ':')) {
                                $partes = explode(':', $rawDur);
                                $srvDuracion = ((int)$partes[0] * 60) + (int)($partes[1] ?? 0);
                            } else {
                                $srvDuracion = (int)$rawDur;
                            }
                            if ($srvDuracion <= 0) $srvDuracion = 30;

                            $srvEstado = strtolower(is_array($srv) ? ($srv['estado'] ?? 'activo') : ($srv->estado ?? 'activo'));
                            $srvImagen = is_array($srv) ? ($srv['imagen_url'] ?? $srv['imagen'] ?? '') : ($srv->imagen_url ?? $srv->imagen ?? '');
                        @endphp
                        <tr data-categoria="{{ $srvCatNombre }}">
                            <td style="width: 50px;">
                                @if (!empty($srvImagen))
                                    @php
                                        $imgSrc = str_starts_with($srvImagen, 'http') ? $srvImagen : asset($srvImagen);
                                    @endphp
                                    <img src="{{ $imgSrc }}" alt="{{ $srvNombre }}" style="width: 42px; height: 42px; border-radius: 10px; object-fit: cover; border: 1px solid #eadff0; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                                @else
                                    <div style="width: 42px; height: 42px; border-radius: 10px; background: #f2e4f8; color: #7c1db8; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                                        <i class="fa-solid fa-spa"></i>
                                    </div>
                                @endif
                            </td>

                            <td>
                                <strong>{{ $srvNombre }}</strong>
                                @if (!empty($srvDesc))
                                    <small style="display:block; color:#8d7b96; font-size:11px;">{{ $srvDesc }}</small>
                                @endif
                            </td>

                            <td>
                                <span class="categoria-badge">
                                    <i class="fa-solid fa-tag"></i>
                                    {{ $srvCatNombre }}
                                </span>
                            </td>

                            <td>
                                <strong>${{ number_format((float)$srvPrecio, 0, ',', '.') }}</strong>
                            </td>

                            <td>
                                <span><i class="fa-solid fa-clock" style="color:#7c1db8;"></i> {{ $srvDuracion }} min</span>
                            </td>

                            <td>
                                <span class="estado-badge estado-{{ $srvEstado }}">
                                    {{ ucfirst($srvEstado) }}
                                </span>
                            </td>

                            <td style="text-align: center;">
                                <button type="button" class="btn-accion btn-editar-servicio" 
                                    data-id="{{ $srvId }}" 
                                    data-nombre="{{ $srvNombre }}"
                                    data-categoria="{{ $srvCatId }}"
                                    data-precio="{{ $srvPrecio }}"
                                    data-duracion="{{ $srvDuracion }}"
                                    data-descripcion="{{ $srvDesc }}"
                                    data-estado="{{ $srvEstado }}"
                                    title="Editar Servicio">
                                    <i class="fa-solid fa-pen"></i>
                                </button>

                                <button type="button" class="btn-accion btn-eliminar-servicio" 
                                    data-id="{{ $srvId }}" 
                                    data-nombre="{{ $srvNombre }}"
                                    title="Eliminar Servicio">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="tabla-vacia">
                                No hay servicios registrados en el catálogo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- PANEL DERECHO: CATEGORÍAS -->
    <div class="servicios-side">
        <div class="categorias-resumen-card">
            <h3><i class="fa-solid fa-layer-group"></i> Categorías de Servicios</h3>

            @forelse ($categorias ?? [] as $cat)
                @php
                    $catNombre = is_array($cat) ? ($cat['nombre'] ?? '') : ($cat->nombre ?? '');
                    $catDesc = is_array($cat) ? ($cat['descripcion'] ?? 'Especialidad LashApp') : ($cat->descripcion ?? 'Especialidad LashApp');
                @endphp
                <div class="categoria-item">
                    <div class="categoria-item-info">
                        <div class="categoria-item-icon"><i class="fa-solid fa-spa"></i></div>
                        <div>
                            <strong>{{ $catNombre }}</strong>
                            <span>{{ $catDesc }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <p style="color:#8d7b96; font-size:13px; text-align:center; padding:15px 0;">No hay categorías registradas.</p>
            @endforelse
        </div>
    </div>

</div>

<!-- ==========================================
     MODAL CREAR NUEVO SERVICIO
========================================== -->
<div class="modal-servicio-overlay" id="modalCrearServicio">
    <div class="modal-servicio-container">
        <div class="modal-servicio-header">
            <h3>
                <i class="fa-solid fa-spa"></i>
                Crear Nuevo Servicio
            </h3>
            <button type="button" class="btn-cerrar-modal" id="cerrarModalServicio">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-servicio-body">
            <form action="{{ Route::has('servicios.store') ? route('servicios.store') : url('/admin/servicios') }}" method="POST" enctype="multipart/form-data" id="formCrearServicio">
                @csrf

                <div class="campo-servicio">
                    <label>Nombre del Servicio</label>
                    <input type="text" name="nombre" placeholder="Ej. Extensiones Pestañas Punto a Punto" required>
                </div>

                <div class="campo-servicio">
                    <label>Categoría</label>
                    <select name="categoria_id" required>
                        <option value="">Selecciona una categoría...</option>
                        @foreach ($categorias ?? [] as $cat)
                            @php
                                $cId = is_array($cat) ? ($cat['id'] ?? $cat['id_categoria'] ?? '') : ($cat->id ?? $cat->id_categoria ?? '');
                                $cNombre = is_array($cat) ? ($cat['nombre'] ?? '') : ($cat->nombre ?? '');
                            @endphp
                            <option value="{{ $cId }}">{{ $cNombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-servicio">
                    <label>Precio ($)</label>
                    <input type="number" step="0.01" name="precio" placeholder="Ej. 65000" required>
                </div>

                <div class="campo-servicio">
                    <label>Duración estimada (en minutos)</label>
                    <input type="number" name="duracion_minuto" placeholder="Ej. 60" min="5" max="480" required>
                </div>

                <div class="campo-servicio">
                    <label>Imagen del Servicio (Opcional)</label>
                    <input type="file" name="imagen" accept="image/*">
                    <small style="color:#8d7b96; display:block; margin-top:4px;">Formatos recomendados: JPG, PNG o WebP.</small>
                </div>

                <div class="campo-servicio">
                    <label>Descripción (Opcional)</label>
                    <textarea name="descripcion" rows="3" placeholder="Detalles de la técnica, productos usados..."></textarea>
                </div>

                <div class="campo-servicio">
                    <label>Estado</label>
                    <select name="estado" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="servicio-form-buttons">
                    <button type="button" class="btn-cancelar-servicio" id="cancelarModalServicio">Cancelar</button>
                    <button type="submit" class="btn-guardar-servicio">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Servicio
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL EDITAR SERVICIO
========================================== -->
<div class="modal-servicio-overlay" id="modalEditarServicio">
    <div class="modal-servicio-container">
        <div class="modal-servicio-header">
            <h3>
                <i class="fa-solid fa-pen-to-square"></i>
                Editar Servicio
            </h3>
            <button type="button" class="btn-cerrar-modal" id="cerrarModalEditarServicio">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-servicio-body">
            <form action="" method="POST" enctype="multipart/form-data" id="formEditarServicio">
                @csrf
                @method('PUT')

                <input type="hidden" name="id" id="edit_id_servicio">

                <div class="campo-servicio">
                    <label>Nombre del Servicio</label>
                    <input type="text" name="nombre" id="edit_nombre_servicio" required>
                </div>

                <div class="campo-servicio">
                    <label>Categoría</label>
                    <select name="categoria_id" id="edit_id_categoria" required>
                        @foreach ($categorias ?? [] as $cat)
                            @php
                                $cId = is_array($cat) ? ($cat['id'] ?? $cat['id_categoria'] ?? '') : ($cat->id ?? $cat->id_categoria ?? '');
                                $cNombre = is_array($cat) ? ($cat['nombre'] ?? '') : ($cat->nombre ?? '');
                            @endphp
                            <option value="{{ $cId }}">{{ $cNombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-servicio">
                    <label>Precio ($)</label>
                    <input type="number" step="0.01" name="precio" id="edit_precio_servicio" required>
                </div>

                <div class="campo-servicio">
                    <label>Duración estimada (en minutos)</label>
                    <input type="number" name="duracion_minuto" id="edit_duracion_servicio" min="5" max="480" required>
                </div>

                <div class="campo-servicio">
                    <label>Actualizar Imagen (Opcional)</label>
                    <input type="file" name="imagen" accept="image/*">
                </div>

                <div class="campo-servicio">
                    <label>Descripción</label>
                    <textarea name="descripcion" id="edit_descripcion_servicio" rows="3"></textarea>
                </div>

                <div class="campo-servicio">
                    <label>Estado</label>
                    <select name="estado" id="edit_estado_servicio" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="servicio-form-buttons">
                    <button type="button" class="btn-cancelar-servicio" id="cancelarModalEditarServicio">Cancelar</button>
                    <button type="submit" class="btn-guardar-servicio">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario invisible para Eliminar Servicio -->
<form id="formEliminarServicio" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

</div> <!-- Cierre de page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->
</body>
</html>

