<!-- Incluir Header superior de Admin -->
@include('layouts.header', ['titulo' => 'Reseñas y Valoraciones'])
@include('layouts.sidebaradmin')

<!-- Estilos y Scripts específicos de reseñas -->
@vite([
    'resources/css/resenas.css',
    'resources/js/modules/admin/resenas.js'
])

<!-- Contenido Principal -->
<div class="page-content" id="resenasContainer" data-base-url="{{ url('/admin/resenas') }}">

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

<!-- ==========================================
     ENCABEZADO
========================================== -->
<div class="resenas-header">
    <div class="resenas-titulo">
        <div class="resenas-icono">
            <i class="fa-solid fa-star"></i>
        </div>
        <div>
            <h1>Reseñas y Valoraciones</h1>
            <p>Analiza el nivel de satisfacción y las opiniones registradas de tus clientes</p>
        </div>
    </div>
    <div class="resenas-actions">
        <button type="button" class="btn-exportar" onclick="window.print()">
            <i class="fa-solid fa-download"></i> Exportar Reporte
        </button>
    </div>
</div>

<!-- ==========================================
     TARJETAS DE MÉTRICAS
========================================== -->
<div class="resenas-cards">

    <div class="resena-card">
        <div class="resena-card-icon gold">
            <i class="fa-solid fa-star"></i>
        </div>
        <div class="resena-card-body">
            <span>Calificación Promedio</span>
            <strong>{{ $promedioCalificacion ?? 5.0 }} / 5</strong>
            <small class="gold-text">
                <i class="fa-solid fa-arrow-trend-up"></i> Excelencia en servicio
            </small>
        </div>
    </div>

    <div class="resena-card">
        <div class="resena-card-icon purple">
            <i class="fa-solid fa-comments"></i>
        </div>
        <div class="resena-card-body">
            <span>Total de Reseñas</span>
            <strong>{{ $totalResenas ?? 0 }}</strong>
            <small class="purple-text">
                <i class="fa-solid fa-check-double"></i> Testimonios registrados
            </small>
        </div>
    </div>

    <div class="resena-card">
        <div class="resena-card-icon green">
            <i class="fa-solid fa-thumbs-up"></i>
        </div>
        <div class="resena-card-body">
            <span>Reseñas Positivas (5★)</span>
            <strong>{{ $pct5 ?? 100 }}%</strong>
            <small class="green-text">Satisfacción garantizada</small>
        </div>
    </div>

    <div class="resena-card">
        <div class="resena-card-icon pink">
            <i class="fa-solid fa-heart"></i>
        </div>
        <div class="resena-card-body">
            <span>Fidelidad de Clientes</span>
            <strong>94%</strong>
            <small class="pink-text">Recomiendan LashApp</small>
        </div>
    </div>

</div>

<!-- ==========================================
     CONTENIDO PRINCIPAL
========================================== -->
<div class="resenas-main-grid">

    <div>
        <!-- GRÁFICO / BREAKDOWN DE ESTRELLAS -->
        <div class="resenas-breakdown-card">
            <div class="resenas-breakdown-header">
                <h3><i class="fa-solid fa-chart-bar"></i> Distribución de Calificaciones</h3>
            </div>
            <div class="breakdown-grid">
                <div class="score-big">
                    <h2>{{ $promedioCalificacion ?? 5.0 }}</h2>
                    <div class="score-stars">
                        @php
                            $ratingVal = (float)($promedioCalificacion ?? 5.0);
                        @endphp
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($ratingVal >= $i)
                                <i class="fa-solid fa-star"></i>
                            @elseif ($ratingVal >= ($i - 0.5))
                                <i class="fa-solid fa-star-half-stroke"></i>
                            @else
                                <i class="fa-regular fa-star"></i>
                            @endif
                        @endfor
                    </div>
                    <span>{{ $totalResenas ?? 0 }} valoraciones globales</span>
                </div>

                <div class="breakdown-lines">
                    <div class="linea-estrella">
                        <span>5 Estrellas</span>
                        <div class="bar-bg"><div class="bar-fill" style="width: {{ $pct5 ?? 100 }}%;"></div></div>
                        <strong>{{ $pct5 ?? 100 }}%</strong>
                    </div>
                    <div class="linea-estrella">
                        <span>4 Estrellas</span>
                        <div class="bar-bg"><div class="bar-fill" style="width: {{ $pct4 ?? 0 }}%;"></div></div>
                        <strong>{{ $pct4 ?? 0 }}%</strong>
                    </div>
                    <div class="linea-estrella">
                        <span>3 Estrellas</span>
                        <div class="bar-bg"><div class="bar-fill" style="width: {{ $pct3 ?? 0 }}%;"></div></div>
                        <strong>{{ $pct3 ?? 0 }}%</strong>
                    </div>
                    <div class="linea-estrella">
                        <span>2 Estrellas</span>
                        <div class="bar-bg"><div class="bar-fill" style="width: {{ $pct2 ?? 0 }}%;"></div></div>
                        <strong>{{ $pct2 ?? 0 }}%</strong>
                    </div>
                    <div class="linea-estrella">
                        <span>1 Estrella</span>
                        <div class="bar-bg"><div class="bar-fill" style="width: {{ $pct1 ?? 0 }}%;"></div></div>
                        <strong>{{ $pct1 ?? 0 }}%</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE RESEÑAS -->
        <div class="resenas-tabla-card">

            <div class="resenas-tabla-header">
                <h3>
                    <i class="fa-solid fa-list-check"></i>
                    Comentarios y Testimonios Recientes
                </h3>

                <select id="filtroEstrellas">
                    <option value="">Todas las calificaciones</option>
                    <option value="5">5 Estrellas (★★★★★)</option>
                    <option value="4">4 Estrellas (★★★★☆)</option>
                    <option value="3">3 Estrellas (★★★☆☆)</option>
                    <option value="2">2 Estrellas (★★☆☆☆)</option>
                    <option value="1">1 Estrella (★☆☆☆☆)</option>
                </select>
            </div>

            <!-- BUSCADOR -->
            <div class="resenas-buscador">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="buscarResena" placeholder="Buscar por cliente, servicio o comentario...">
            </div>

            <!-- TABLA -->
            <div class="table-responsive">
                <table id="tablaResenas" class="resenas-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Calificación</th>
                            <th>Comentario</th>
                            <th>Fecha</th>
                            <th class="text-center" style="text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($resenas ?? [] as $r)
                            @php
                                $rId = is_array($r) ? ($r['id'] ?? $r['id_resena'] ?? '') : ($r->id ?? $r->id_resena ?? '');
                                
                                $clienteNombre = is_array($r)
                                    ? ($r['cliente'] ?? 'Cliente')
                                    : ($r->cliente?->user ? ($r->cliente->user->name . ' ' . ($r->cliente->user->apellido ?? '')) : 'Cliente');

                                $servicioNombre = is_array($r)
                                    ? ($r['servicio'] ?? 'Servicio')
                                    : ($r->cita?->servicio?->nombre ?? 'Servicio');

                                $stars = (int)(is_array($r) ? ($r['calificacion'] ?? 5) : ($r->calificacion ?? 5));
                                $comentario = is_array($r) ? ($r['comentario'] ?? '') : ($r->comentario ?? '');
                                
                                $rawFecha = is_array($r) ? ($r['fecha_resena'] ?? null) : ($r->fecha_resena ?? null);
                                $fechaFormat = $rawFecha ? \Carbon\Carbon::parse($rawFecha)->format('d/m/Y, h:i a') : 'Reciente';
                                $inicial = strtoupper(substr($clienteNombre, 0, 1));
                            @endphp
                            <tr data-estrellas="{{ $stars }}">
                                <td>
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <div class="cliente-avatar-circle">{{ $inicial }}</div>
                                        <strong>{{ $clienteNombre }}</strong>
                                    </div>
                                </td>

                                <td>
                                    <span style="font-weight:600; color:#7c1db8;">
                                        <i class="fa-solid fa-spa"></i> {{ $servicioNombre }}
                                    </span>
                                </td>

                                <td>
                                    <div class="estrellas-badge">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa-{{ $i <= $stars ? 'solid' : 'regular' }} fa-star"></i>
                                        @endfor
                                    </div>
                                </td>

                                <td>
                                    <small style="color:#5e4d66; font-style:italic;">
                                        "{{ !empty($comentario) ? $comentario : 'Sin comentario' }}"
                                    </small>
                                </td>

                                <td>
                                    {{ $fechaFormat }}
                                </td>

                                <td style="text-align: center;">
                                    <button type="button" class="btn-accion-danger btn-eliminar-resena" 
                                        data-id="{{ $rId }}" 
                                        data-cliente="{{ $clienteNombre }}"
                                        title="Eliminar Reseña">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align:center; padding:40px; color:#9070a8;">
                                    No hay reseñas registradas en el sistema.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- PANEL DERECHO: FORMULARIO REGISTRAR RESEÑA -->
    <div class="resenas-side">
        <div class="dejar-resena-card">
            <h3><i class="fa-solid fa-pen-to-square"></i> Registrar Valoración</h3>
            <p>Registra la opinión de un cliente que asistió a su cita</p>

            <form action="{{ route('resenas.store') }}" method="POST" id="formResena">
                @csrf

                <div class="campo-resena">
                    <label>Cita Atendida / Cliente</label>
                    <select name="id_cita" required>
                        <option value="" disabled selected>Selecciona una cita atendida</option>
                        @php
                            $listaCitasSelect = !empty($citasSinResena) && count($citasSinResena) > 0 ? $citasSinResena : ($todasCitas ?? []);
                        @endphp
                        @forelse ($listaCitasSelect as $c)
                            @php
                                $cId = is_array($c) ? ($c['id_cita'] ?? $c['id'] ?? '') : ($c->id ?? '');
                                $cCliente = is_array($c) ? ($c['cliente'] ?? 'Cliente') : ($c->cliente?->user ? ($c->cliente->user->name . ' ' . ($c->cliente->user->apellido ?? '')) : 'Cliente');
                                $cServicio = is_array($c) ? ($c['servicio'] ?? 'Servicio') : ($c->servicio?->nombre ?? 'Servicio');
                            @endphp
                            <option value="{{ $cId }}">
                                Cita #{{ $cId }} - {{ $cCliente }} ({{ $cServicio }})
                            </option>
                        @empty
                            <option value="" disabled>No hay citas registradas en el sistema</option>
                        @endforelse
                    </select>
                </div>

                <div class="campo-resena">
                    <label>Calificación (Estrellas)</label>
                    <input type="hidden" name="calificacion" id="input_calificacion" value="5">
                    <div class="star-rating-selector" id="starSelector">
                        <i class="fa-solid fa-star active" data-value="1"></i>
                        <i class="fa-solid fa-star active" data-value="2"></i>
                        <i class="fa-solid fa-star active" data-value="3"></i>
                        <i class="fa-solid fa-star active" data-value="4"></i>
                        <i class="fa-solid fa-star active" data-value="5"></i>
                    </div>
                </div>

                <div class="campo-resena">
                    <label>Comentario / Opinión del cliente</label>
                    <textarea name="comentario" rows="4" placeholder="Escribe el testimonio o comentario de la cliente sobre el servicio recibido..." required></textarea>
                </div>

                <button type="submit" class="btn-guardar-resena">
                    <i class="fa-solid fa-star"></i> Guardar Valoración
                </button>

            </form>
        </div>
    </div>

</div>

<!-- Formulario invisible para Eliminar Reseña -->
<form id="formEliminarResena" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

</div> <!-- Cierre de page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>