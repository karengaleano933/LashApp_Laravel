<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Historial de Citas'])
@include('layouts.sidebarcliente')

@vite(['resources/css/historilacliente.css'])

@php
    use Carbon\Carbon;

    $meses = [
        1 => 'ENE', 2 => 'FEB', 3 => 'MAR', 4 => 'ABR',
        5 => 'MAY', 6 => 'JUN', 7 => 'JUL', 8 => 'AGO',
        9 => 'SEP', 10 => 'OCT', 11 => 'NOV', 12 => 'DIC'
    ];

    $historialItems = [];

    if (isset($citas) && $citas->count() > 0) {
        $historialItems = $citas->map(function ($c) use ($meses) {
            $fechaObj = $c->fecha ? Carbon::parse($c->fecha) : Carbon::today();
            $mesNum = (int)$fechaObj->format('n');
            $mesStr = $meses[$mesNum] ?? strtoupper($fechaObj->format('M'));

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
            $badgeClass = match ($estadoLower) {
                'completado', 'completada', 'finalizado' => 'badge-completado',
                'confirmado', 'confirmada' => 'badge-confirmado',
                'cancelado', 'cancelada' => 'badge-cancelado',
                default => 'badge-pendiente',
            };

            $estadoLabel = match ($estadoLower) {
                'completado', 'completada', 'finalizado' => 'Completado',
                'confirmado', 'confirmada' => 'Confirmado',
                'cancelado', 'cancelada' => 'Cancelado',
                default => 'Pendiente',
            };

            $precio = (float)($c->pago?->monto ?? $c->servicio?->precio ?? 130000);

            return [
                'id' => $c->id,
                'dia' => $fechaObj->format('d'),
                'mes' => $mesStr,
                'ano' => $fechaObj->format('Y'),
                'fecha_raw' => $fechaObj->format('Y-m-d'),
                'hora' => $c->hora_inicio ? Carbon::parse($c->hora_inicio)->format('h:i a') : '10:00 am',
                'servicio' => $c->servicio?->nombre ?? 'Servicio de Belleza',
                'servicio_id' => $c->servicio_id ?? 1,
                'servicio_slug' => \Illuminate\Support\Str::slug($c->servicio?->nombre ?? 'servicio'),
                'profesional' => $profesional,
                'profesional_slug' => \Illuminate\Support\Str::slug($profesional),
                'ubicacion' => 'Oh La Lash Studio',
                'estado' => $estadoLabel,
                'estado_badge' => $badgeClass,
                'precio' => $precio,
                'precio_fmt' => '$' . number_format($precio, 0, ',', '.'),
                'img' => $img,
                'metodo_pago' => $c->pago?->metodo_pago ?? 'Tarjeta',
                'tipo' => 'citas',
            ];
        })->toArray();
    }

    // Estadísticas 100% conectadas a la base de datos
    $statTotalCitas = $totalCitas ?? count($historialItems);
    $statServiciosRealizados = $serviciosRealizados ?? 0;
    $statTotalInvertido = isset($totalInvertido) && $totalInvertido > 0 
        ? '$' . number_format($totalInvertido, 0, ',', '.') 
        : '$0';
    $statCalificacion = isset($calificacionPromedio) ? number_format($calificacionPromedio, 1) : '5.0';
@endphp

<!-- Contenido Principal de Historial -->
<div class="page-content">

    <!-- ══════════════════════════════════════════
         TOP HEADER DE HISTORIAL
    ══════════════════════════════════════════ -->
    <div class="historial-header">
        <div class="historial-header-left">
            <div class="historial-header-icon">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <h1>Historial</h1>
                <p>Consulta el historial de todas tus citas y servicios realizados</p>
            </div>
        </div>
        <div class="historial-header-right">
            <a href="{{ route('cliente.agendar') }}" class="btn-agendar-hist-empty" style="margin: 0; padding: 8px 18px; font-size: 12px;">
                <i class="fa-solid fa-calendar-plus"></i> Agendar Cita
            </a>
            <button type="button" class="icon-btn-circle" title="Descargar o Imprimir Historial" onclick="descargarHistorialPDF()">
                <i class="fa-solid fa-print"></i>
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         BARRA DE PESTAÑAS (Todos, Citas, Servicios, Pagos)
    ══════════════════════════════════════════ -->
    <div class="hist-tabs-bar">
        <button type="button" class="hist-tab-btn active" data-tab="todos" onclick="filtrarHistorialTab('todos', this)">
            <i class="fa-solid fa-list"></i> Todos
        </button>
        <button type="button" class="hist-tab-btn" data-tab="citas" onclick="filtrarHistorialTab('citas', this)">
            <i class="fa-regular fa-calendar"></i> Citas
        </button>
        <button type="button" class="hist-tab-btn" data-tab="servicios" onclick="filtrarHistorialTab('servicios', this)">
            <i class="fa-solid fa-spa"></i> Servicios
        </button>
        <button type="button" class="hist-tab-btn" data-tab="pagos" onclick="filtrarHistorialTab('pagos', this)">
            <i class="fa-solid fa-credit-card"></i> Pagos
        </button>
    </div>

    <!-- ══════════════════════════════════════════
         LAYOUT PRINCIPAL (2 COLUMNAS)
    ══════════════════════════════════════════ -->
    <div class="hist-main-grid">

        <!-- COLUMNA IZQUIERDA: LISTA DE HISTORIAL COMPLETO -->
        <div>
            <div class="hist-section-top">
                <div class="hist-section-title">Historial completo</div>
                <div class="hist-select-box">
                    <span>Ordenar por</span>
                    <select id="ordenarHistorialSelect" onchange="aplicarFiltrosYOrden()">
                        <option value="recientes">Más recientes</option>
                        <option value="antiguos">Más antiguos</option>
                        <option value="mayor_precio">Mayor valor</option>
                        <option value="menor_precio">Menor valor</option>
                    </select>
                </div>
            </div>

            <!-- Contenedor de Tarjetas de Historial -->
            <div class="hist-list-container" id="histListContainer">
                @foreach ($historialItems as $item)
                    <div class="hist-card-item" 
                         data-id="{{ $item['id'] }}"
                         data-tipo="{{ $item['tipo'] }}"
                         data-tiene-cita="1"
                         data-tiene-servicio="1"
                         data-tiene-pago="{{ $item['precio'] > 0 ? '1' : '0' }}"
                         data-servicio="{{ $item['servicio_slug'] }}"
                         data-profesional="{{ $item['profesional_slug'] }}"
                         data-fecha="{{ $item['fecha_raw'] }}"
                         data-precio="{{ $item['precio'] }}">
                        
                        <div class="hist-date-box">
                            <div class="hist-date-day">{{ $item['dia'] }}</div>
                            <div class="hist-date-month">{{ $item['mes'] }}</div>
                            <div class="hist-date-year">{{ $item['ano'] }}</div>
                        </div>

                        <img src="{{ $item['img'] }}" class="hist-thumb-img" alt="{{ $item['servicio'] }}" onerror="this.src='{{ asset('img/volumen_ruso.jpg') }}'">

                        <div class="hist-item-body">
                            <div class="hist-item-title">{{ $item['servicio'] }}</div>
                            <div class="hist-item-meta">
                                <span><i class="fa-regular fa-clock"></i> {{ $item['hora'] }}</span>
                                <span>·</span>
                                <span><i class="fa-regular fa-user"></i> {{ $item['profesional'] }}</span>
                                <span>·</span>
                                <span><i class="fa-solid fa-credit-card"></i> {{ $item['metodo_pago'] }}</span>
                            </div>
                            <div class="hist-item-location">
                                <i class="fa-solid fa-location-dot"></i> {{ $item['ubicacion'] }}
                            </div>
                        </div>

                        <div class="hist-item-right">
                            <span class="{{ $item['estado_badge'] }}">{{ $item['estado'] }}</span>
                            <div class="hist-price-tag">{{ $item['precio_fmt'] }}</div>
                            <a href="{{ route('cliente.agendar') }}?servicio_id={{ $item['servicio_id'] }}" class="hist-arrow-btn" title="Agendar este servicio de nuevo">
                                <i class="fa-solid fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty State cuando no hay registros o no hay coincidencias -->
            <div class="hist-empty-state" id="histEmptyState" style="{{ empty($historialItems) ? 'display: block;' : 'display: none;' }}">
                <div class="hist-empty-icon">
                    <i class="fa-regular fa-calendar-xmark"></i>
                </div>
                <div class="hist-empty-title" id="histEmptyTitle">
                    {{ empty($historialItems) ? 'Aún no tienes citas en tu historial' : 'No se encontraron registros' }}
                </div>
                <p class="hist-empty-desc" id="histEmptyDesc">
                    {{ empty($historialItems) ? 'Aquí aparecerán todas las citas, servicios y pagos que realices en Oh La Lash Studio.' : 'No hay citas ni servicios que coincidan con los filtros aplicados. Intenta restablecer los filtros.' }}
                </p>
                <div id="histEmptyAction">
                    @if(empty($historialItems))
                        <a href="{{ route('cliente.agendar') }}" class="btn-agendar-hist-empty">
                            <i class="fa-solid fa-calendar-plus"></i> Agendar mi primera cita
                        </a>
                    @else
                        <button type="button" class="btn-agendar-hist-empty" onclick="resetFiltros()">
                            <i class="fa-solid fa-rotate-left"></i> Restablecer Filtros
                        </button>
                    @endif
                </div>
            </div>

            <div class="info-footer-banner">
                <i class="fa-solid fa-circle-info"></i>
                Aquí puedes ver el historial completo de todas tus citas, servicios y pagos realizados.
            </div>
        </div>

        <!-- COLUMNA DERECHA: SIDEBAR DE FILTROS & RESUMEN -->
        <div class="hist-sidebar-widgets">

            <!-- Widget 1: Filtros Interactivos -->
            <div class="widget-card">
                <div class="widget-title">
                    <i class="fa-solid fa-filter"></i> Filtros
                </div>

                <div class="filter-form-group">
                    <label>Rango de fechas</label>
                    <input type="text" id="filterFechaInput" class="filter-input-box" placeholder="Ej: 2026 o YYYY-MM-DD" oninput="aplicarFiltrosYOrden()">
                </div>

                <div class="filter-form-group">
                    <label>Tipo</label>
                    <select id="filterTipoSelect" class="filter-select-box" onchange="sincronizarTipoConTab(this.value)">
                        <option value="todos">Todos</option>
                        <option value="citas">Citas</option>
                        <option value="servicios">Servicios</option>
                        <option value="pagos">Pagos</option>
                    </select>
                </div>

                <div class="filter-form-group">
                    <label>Servicio</label>
                    <select id="filterServicioSelect" class="filter-select-box" onchange="aplicarFiltrosYOrden()">
                        <option value="todos">Todos los servicios</option>
                        @if(isset($serviciosFiltro) && $serviciosFiltro->count() > 0)
                            @foreach($serviciosFiltro as $sf)
                                <option value="{{ \Illuminate\Support\Str::slug($sf->nombre) }}">{{ $sf->nombre }}</option>
                            @endforeach
                        @else
                            <option value="volumen-ruso">Volumen Ruso</option>
                            <option value="extensiones-clasicas">Clásicas</option>
                            <option value="diseno-cejas">Diseño de Cejas</option>
                            <option value="manicure-semi">Manicure Semipermanente</option>
                            <option value="pedicure-spa">Pedicure Spa</option>
                            <option value="lifting-pestanas">Lifting de Pestañas</option>
                        @endif
                    </select>
                </div>

                <div class="filter-form-group">
                    <label>Profesional</label>
                    <select id="filterProfesionalSelect" class="filter-select-box" onchange="aplicarFiltrosYOrden()">
                        <option value="todas">Todas</option>
                        @if(isset($profesionalesFiltro) && $profesionalesFiltro->count() > 0)
                            @foreach($profesionalesFiltro as $pf)
                                <option value="{{ \Illuminate\Support\Str::slug($pf->user?->name ?? 'especialista') }}">
                                    {{ $pf->user?->name ?? 'Especialista' }} {{ $pf->user?->apellido ?? '' }}
                                </option>
                            @endforeach
                        @else
                            <option value="andrea">Andrea</option>
                            <option value="ana">Ana</option>
                        @endif
                    </select>
                </div>

                <button type="button" class="btn-limpiar-filtros" onclick="resetFiltros()">
                    <i class="fa-solid fa-rotate-left"></i> Limpiar filtros
                </button>
            </div>

            <!-- Widget 2: Resumen de tu historial -->
            <div class="widget-card">
                <div class="widget-title">
                    <i class="fa-solid fa-chart-line"></i> Resumen de tu historial
                </div>

                <div class="resumen-list">
                    <div class="resumen-row">
                        <div class="resumen-label"><i class="fa-regular fa-calendar"></i> Total de citas</div>
                        <div class="resumen-val purple">{{ $statTotalCitas }}</div>
                    </div>

                    <div class="resumen-row">
                        <div class="resumen-label"><i class="fa-regular fa-circle-check"></i> Servicios realizados</div>
                        <div class="resumen-val">{{ $statServiciosRealizados }}</div>
                    </div>

                    <div class="resumen-row">
                        <div class="resumen-label"><i class="fa-solid fa-dollar-sign"></i> Total invertido</div>
                        <div class="resumen-val purple">{{ $statTotalInvertido }}</div>
                    </div>

                    <div class="resumen-row">
                        <div class="resumen-label"><i class="fa-regular fa-star"></i> Calificación promedio</div>
                        <div class="resumen-val">
                            <span style="color:#eab308;"><i class="fa-solid fa-star"></i></span> {{ $statCalificacion }} / 5
                        </div>
                    </div>
                </div>
            </div>

            <!-- Widget 3: Descarga tu historial en PDF -->
            <div class="widget-card">
                <div class="widget-title">
                    <i class="fa-solid fa-file-pdf"></i> Descarga tu historial
                </div>
                <p style="font-size:11px; color:#7a5a90; margin-bottom:12px; line-height: 1.5;">
                    Puedes imprimir o guardar tu historial completo de citas y comprobantes en formato PDF.
                </p>

                <button type="button" class="btn-descargar-pdf" onclick="descargarHistorialPDF()">
                    <i class="fa-solid fa-download"></i> Descargar PDF
                </button>
            </div>

        </div>

    </div>

</div>

<!-- ══════════════════════════════════════════
     SCRIPTS DE FILTRADO, ORDEN Y PDF
══════════════════════════════════════════ -->
<script>
let tabActiva = 'todos';

// 1. Filtrar por Pestañas Principales (Todos, Citas, Servicios, Pagos)
function filtrarHistorialTab(tipo, btn) {
    tabActiva = tipo;
    document.querySelectorAll('.hist-tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    // Sincronizar el select de tipo en la barra lateral
    const tipoSelect = document.getElementById('filterTipoSelect');
    if (tipoSelect) tipoSelect.value = tipo;

    aplicarFiltrosYOrden();
}

function sincronizarTipoConTab(tipo) {
    tabActiva = tipo;
    document.querySelectorAll('.hist-tab-btn').forEach(b => {
        if (b.getAttribute('data-tab') === tipo) {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });
    aplicarFiltrosYOrden();
}

// 2. Aplicar todos los filtros combinados y ordenación en el DOM
function aplicarFiltrosYOrden() {
    const container = document.getElementById('histListContainer');
    const emptyState = document.getElementById('histEmptyState');
    if (!container) return;

    const cards = Array.from(container.querySelectorAll('.hist-card-item'));
    const fechaQuery = (document.getElementById('filterFechaInput')?.value || '').toLowerCase().trim();
    const servicioFiltro = document.getElementById('filterServicioSelect')?.value || 'todos';
    const profesionalFiltro = document.getElementById('filterProfesionalSelect')?.value || 'todas';
    const orden = document.getElementById('ordenarHistorialSelect')?.value || 'recientes';

    let visibles = 0;

    cards.forEach(card => {
        const cardTipo = card.getAttribute('data-tipo') || '';
        const cardServicio = card.getAttribute('data-servicio') || '';
        const cardProfesional = card.getAttribute('data-profesional') || '';
        const cardFecha = card.getAttribute('data-fecha') || '';
        const cardText = card.textContent.toLowerCase();

        // 1. Condición de Pestaña / Tipo
        let matchTab = true;
        if (tabActiva === 'citas') {
            matchTab = card.getAttribute('data-tiene-cita') === '1' || cardTipo === 'citas';
        } else if (tabActiva === 'servicios') {
            matchTab = card.getAttribute('data-tiene-servicio') === '1' || cardTipo === 'servicios';
        } else if (tabActiva === 'pagos') {
            matchTab = card.getAttribute('data-tiene-pago') === '1' || cardTipo === 'pagos';
        }

        // 2. Condición de Fecha
        let matchFecha = !fechaQuery || cardFecha.includes(fechaQuery) || cardText.includes(fechaQuery);

        // 3. Condición de Servicio
        let matchServicio = (servicioFiltro === 'todos' || cardServicio.includes(servicioFiltro) || servicioFiltro.includes(cardServicio));

        // 4. Condición de Profesional
        let matchProfesional = (profesionalFiltro === 'todas' || cardProfesional.includes(profesionalFiltro) || profesionalFiltro.includes(cardProfesional));

        if (matchTab && matchFecha && matchServicio && matchProfesional) {
            card.style.display = 'flex';
            visibles++;
        } else {
            card.style.display = 'none';
        }
    });

    // Ordenar elementos en el DOM
    cards.sort((a, b) => {
        const fechaA = new Date(a.getAttribute('data-fecha') || '1970-01-01').getTime();
        const fechaB = new Date(b.getAttribute('data-fecha') || '1970-01-01').getTime();
        const precioA = parseFloat(a.getAttribute('data-precio') || 0);
        const precioB = parseFloat(b.getAttribute('data-precio') || 0);

        if (orden === 'recientes') return fechaB - fechaA;
        if (orden === 'antiguos') return fechaA - fechaB;
        if (orden === 'mayor_precio') return precioB - precioA;
        if (orden === 'menor_precio') return precioA - precioB;
        return 0;
    });

    cards.forEach(card => container.appendChild(card));

    if (emptyState) {
        emptyState.style.display = (visibles === 0) ? 'block' : 'none';
    }
}

// 3. Resetear Filtros a Valores Predeterminados
function resetFiltros() {
    tabActiva = 'todos';
    document.querySelectorAll('.hist-tab-btn').forEach(b => {
        if (b.getAttribute('data-tab') === 'todos') b.classList.add('active');
        else b.classList.remove('active');
    });

    const fechaInput = document.getElementById('filterFechaInput');
    if (fechaInput) fechaInput.value = '';

    const tipoSelect = document.getElementById('filterTipoSelect');
    if (tipoSelect) tipoSelect.value = 'todos';

    const servSelect = document.getElementById('filterServicioSelect');
    if (servSelect) servSelect.value = 'todos';

    const profSelect = document.getElementById('filterProfesionalSelect');
    if (profSelect) profSelect.value = 'todas';

    const ordSelect = document.getElementById('ordenarHistorialSelect');
    if (ordSelect) ordSelect.value = 'recientes';

    aplicarFiltrosYOrden();
}

// 4. Descargar o Imprimir Historial en PDF
function descargarHistorialPDF() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generando Reporte PDF',
            text: 'Se abrirá la vista de impresión optimizada para guardar tu historial en PDF.',
            icon: 'info',
            confirmButtonColor: '#7c1db8',
            confirmButtonText: 'Imprimir / Guardar PDF',
            showCancelButton: true,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.print();
            }
        });
    } else {
        window.print();
    }
}
</script>

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper (abierto en headercliente.blade.php) -->
</div> <!-- Cierre de .app-layout (abierto en headercliente.blade.php) -->

</body>
</html>
