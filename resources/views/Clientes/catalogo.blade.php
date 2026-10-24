<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Catálogo de Servicios'])
@include('layouts.sidebarcliente')

@vite([
    'resources/css/catalogoclient.css',
    'resources/js/modules/clientes/catalogo.js'
])

@php
    // Preparar listado de servicios para el catálogo
    if (!isset($itemsCatalogo)) {
        if (isset($servicios) && $servicios->count() > 0) {
            $itemsCatalogo = $servicios->map(function ($s) {
                // Manejo seguro de imagen
                $img = asset('img/volumen_ruso.jpg');
                if (!empty($s->imagen_url)) {
                    $img = filter_var($s->imagen_url, FILTER_VALIDATE_URL) ? $s->imagen_url : asset($s->imagen_url);
                }

                // Manejo de duración
                $duracionTexto = '45 min';
                if (!empty($s->duracion_minuto)) {
                    if (is_string($s->duracion_minuto) && str_contains($s->duracion_minuto, ':')) {
                        $partes = explode(':', $s->duracion_minuto);
                        $duracionTexto = ((int)$partes[0] > 0 ? (int)$partes[0] . 'h ' : '') . (int)$partes[1] . 'm';
                    } else {
                        $duracionTexto = $s->duracion_minuto . ' min';
                    }
                }

                $categoriaNombre = $s->categoria?->nombre ?? 'Pestañas';
                $catSlug = \Illuminate\Support\Str::slug($categoriaNombre);

                return [
                    'id' => $s->id,
                    'cat' => $categoriaNombre,
                    'cat_slug' => $catSlug,
                    'name' => $s->nombre,
                    'precio' => (float)$s->precio,
                    'duracion' => $duracionTexto,
                    'rating' => '4.9',
                    'reviews' => rand(15, 60),
                    'img' => $img,
                    'popularidad' => rand(10, 50),
                ];
            })->toArray();
        } else {
            // Servicios predeterminados para LashApp Studio con imágenes existentes
            $itemsCatalogo = [
                [
                    'id' => 1,
                    'cat' => 'Pestañas',
                    'cat_slug' => 'pestanas',
                    'name' => 'Extensiones Clásicas 1 a 1',
                    'precio' => 95000,
                    'duracion' => '1h 30m',
                    'rating' => '4.9',
                    'reviews' => 38,
                    'img' => asset('img/extensiones_clasicas.jpg'),
                    'popularidad' => 45,
                ],
                [
                    'id' => 2,
                    'cat' => 'Pestañas',
                    'cat_slug' => 'pestanas',
                    'name' => 'Volumen Ruso 3D - 6D',
                    'precio' => 140000,
                    'duracion' => '2h 00m',
                    'rating' => '5.0',
                    'reviews' => 54,
                    'img' => asset('img/volumen_ruso.jpg'),
                    'popularidad' => 50,
                ],
                [
                    'id' => 3,
                    'cat' => 'Pestañas',
                    'cat_slug' => 'pestanas',
                    'name' => 'Mega Volumen Glam',
                    'precio' => 170000,
                    'duracion' => '2h 15m',
                    'rating' => '4.8',
                    'reviews' => 29,
                    'img' => asset('img/volumen_ruso.jpg'),
                    'popularidad' => 35,
                ],
                [
                    'id' => 4,
                    'cat' => 'Cejas',
                    'cat_slug' => 'cejas',
                    'name' => 'Diseño y Depilación con Hilo',
                    'precio' => 45000,
                    'duracion' => '45m',
                    'rating' => '4.9',
                    'reviews' => 42,
                    'img' => asset('img/diseno_cejas.jpg'),
                    'popularidad' => 40,
                ],
                [
                    'id' => 5,
                    'cat' => 'Cejas',
                    'cat_slug' => 'cejas',
                    'name' => 'Microblading & Sombreado',
                    'precio' => 280000,
                    'duracion' => '2h 30m',
                    'rating' => '5.0',
                    'reviews' => 61,
                    'img' => asset('img/diseno_cejas.jpg'),
                    'popularidad' => 48,
                ],
                [
                    'id' => 6,
                    'cat' => 'Lifting',
                    'cat_slug' => 'lifting',
                    'name' => 'Lifting de Pestañas + Keratina',
                    'precio' => 85000,
                    'duracion' => '1h 00m',
                    'rating' => '4.8',
                    'reviews' => 31,
                    'img' => asset('img/volumen_ruso.jpg'),
                    'popularidad' => 32,
                ],
                [
                    'id' => 7,
                    'cat' => 'Lifting',
                    'cat_slug' => 'lifting',
                    'name' => 'Laminado de Cejas (Brow Lamination)',
                    'precio' => 90000,
                    'duracion' => '1h 15m',
                    'rating' => '4.9',
                    'reviews' => 27,
                    'img' => asset('img/diseno_cejas.jpg'),
                    'popularidad' => 30,
                ],
                [
                    'id' => 8,
                    'cat' => 'Uñas & Spa',
                    'cat_slug' => 'unas',
                    'name' => 'Manicure Semipermanente Spa',
                    'precio' => 65000,
                    'duracion' => '1h 00m',
                    'rating' => '4.7',
                    'reviews' => 46,
                    'img' => asset('img/manicure_semi.jpg'),
                    'popularidad' => 28,
                ],
            ];
        }
    }
@endphp

<!-- Contenido Principal -->
<div class="page-content">

    <!-- ══════════════════════════════════════════
         ENCABEZADO DE SECCIÓN CATÁLOGO
    ══════════════════════════════════════════ -->
    <div class="cat-header">
        <div class="cat-header-left">
            <div class="cat-header-icon">
                <i class="fa-solid fa-bag-shopping"></i>
            </div>
            <div>
                <h1>Catálogo de Servicios</h1>
                <p>Descubre nuestros tratamientos de belleza exclusivos y reserva tu momento especial</p>
            </div>
        </div>
        <div class="cat-header-right">
            <a href="{{ route('cliente.agendar') }}" class="icon-btn-circle" title="Agendar Cita Directa">
                <i class="fa-solid fa-calendar-plus"></i>
            </a>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         BARRA DE PESTAÑAS POR CATEGORÍAS
    ══════════════════════════════════════════ -->
    <div class="category-tabs-bar" id="categoryTabsBar">
        <button type="button" class="cat-tab-item active" data-categoria="todas" onclick="filtrarPorCategoria('todas', this)">
            <i class="fa-solid fa-sparkles"></i> Todos
        </button>
        @if (isset($categorias) && $categorias->count() > 0)
            @foreach ($categorias as $cat)
                @php
                    $catSlug = \Illuminate\Support\Str::slug($cat->nombre);
                    $icon = match($catSlug) {
                        'pestanas' => 'fa-eye',
                        'cejas' => 'fa-wand-magic-sparkles',
                        'lifting-laminado', 'lifting' => 'fa-arrow-trend-up',
                        'unas-spa', 'unas' => 'fa-hand-sparkles',
                        default => 'fa-sparkles',
                    };
                @endphp
                <button type="button" class="cat-tab-item" data-categoria="{{ $catSlug }}" onclick="filtrarPorCategoria('{{ $catSlug }}', this)">
                    <i class="fa-solid {{ $icon }}"></i> {{ $cat->nombre }}
                </button>
            @endforeach
        @else
            <button type="button" class="cat-tab-item" data-categoria="pestanas" onclick="filtrarPorCategoria('pestanas', this)">
                <i class="fa-solid fa-eye"></i> Pestañas
            </button>
            <button type="button" class="cat-tab-item" data-categoria="cejas" onclick="filtrarPorCategoria('cejas', this)">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Cejas
            </button>
            <button type="button" class="cat-tab-item" data-categoria="lifting-laminado" onclick="filtrarPorCategoria('lifting-laminado', this)">
                <i class="fa-solid fa-arrow-trend-up"></i> Lifting & Laminado
            </button>
            <button type="button" class="cat-tab-item" data-categoria="unas-spa" onclick="filtrarPorCategoria('unas-spa', this)">
                <i class="fa-solid fa-hand-sparkles"></i> Uñas & Spa
            </button>
        @endif
    </div>

    <!-- ══════════════════════════════════════════
         BARRA DE BÚSQUEDA Y CONTROLES DE FILTRO
    ══════════════════════════════════════════ -->
    <div class="filter-section-title">
        Explora nuestros servicios (<span id="totalServiciosCount">{{ count($itemsCatalogo) }}</span> disponibles)
    </div>

    <div class="controls-bar">
        <div class="search-input-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" placeholder="Buscar por servicio, categoría..." id="buscarServicioInput" onkeyup="filtrarServicios()">
        </div>

        <div class="select-control-box">
            <span>Ordenar por</span>
            <select id="ordenarSelect" onchange="ordenarServicios()">
                <option value="populares">Más populares</option>
                <option value="menor_precio">Menor precio</option>
                <option value="mayor_precio">Mayor precio</option>
                <option value="nombre_asc">Nombre (A - Z)</option>
            </select>
        </div>

        <div class="select-control-box">
            <span>Filtrar por precio</span>
            <select id="precioSelect" onchange="filtrarPorPrecio()">
                <option value="todos">Todos los precios</option>
                <option value="0-100">$0 - $100.000</option>
                <option value="100-200">$100.000 - $200.000</option>
                <option value="200+">Más de $200.000</option>
            </select>
        </div>

        <div class="view-toggle-btns">
            <button type="button" class="btn-toggle-view active" id="btnViewGrid" title="Vista en Grid" onclick="cambiarVista('grid')">
                <i class="fa-solid fa-border-all"></i> Grid
            </button>
            <button type="button" class="btn-toggle-view" id="btnViewList" title="Vista en Lista" onclick="cambiarVista('list')">
                <i class="fa-solid fa-list"></i> Lista
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         GRID / LISTA DE TARJETAS DE SERVICIOS
    ══════════════════════════════════════════ -->
    <div class="cat-cards-grid" id="catCardsGrid">
        @forelse ($itemsCatalogo as $item)
            <div class="cat-card-item"
                 data-id="{{ $item['id'] }}"
                 data-nombre="{{ strtolower($item['cat'] . ' ' . $item['name']) }}"
                 data-categoria="{{ strtolower($item['cat_slug'] ?? $item['cat']) }}"
                 data-precio="{{ $item['precio'] }}"
                 data-popularidad="{{ $item['popularidad'] ?? 10 }}">
                
                <div class="cat-card-img-wrap">
                    <img src="{{ $item['img'] }}" alt="{{ $item['name'] }}" class="cat-card-img" loading="lazy">
                    <button type="button" 
                            class="fav-heart-btn" 
                            title="Guardar en favoritos" 
                            onclick="toggleFavorito(this, '{{ addslashes($item['name']) }}')">
                        <i class="fa-regular fa-heart"></i>
                    </button>
                </div>

                <div class="cat-card-body">
                    <div>
                        <div class="cat-service-category">{{ $item['cat'] }}</div>
                        <div class="cat-service-subtitle">{{ $item['name'] }}</div>
                        <div class="cat-service-price">${{ number_format($item['precio'], 0, ',', '.') }}</div>
                        
                        <div class="cat-meta-row">
                            <div class="cat-meta-duration">
                                <i class="fa-regular fa-clock"></i> {{ $item['duracion'] }}
                            </div>
                            <div class="cat-meta-rating">
                                <i class="fa-solid fa-star"></i> {{ $item['rating'] }} ({{ $item['reviews'] }})
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('cliente.agendar', ['servicio_id' => $item['id']]) }}" class="btn-cat-detalles">
                        <i class="fa-solid fa-calendar-check"></i> Agendar Cita
                    </a>
                </div>
            </div>
        @empty
            <div class="no-results-box">
                <div class="no-results-icon">
                    <i class="fa-solid fa-bag-shopping"></i>
                </div>
                <h4>No hay servicios disponibles</h4>
                <p>Por favor revisa más tarde o ponte en contacto con nuestro equipo.</p>
            </div>
        @endforelse

        <!-- Mensaje cuando los filtros no arrojan resultados -->
        <div class="no-results-box" id="noResultsMsg" style="display: none;">
            <div class="no-results-icon">
                <i class="fa-solid fa-magnifying-glass"></i>
            </div>
            <h4>No encontramos servicios que coincidan</h4>
            <p>Intenta ajustar la búsqueda, el rango de precio o la categoría seleccionada.</p>
            <button type="button" onclick="limpiarFiltros()">
                <i class="fa-solid fa-rotate-left"></i> Restablecer filtros
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
         BANNER DE CARACTERÍSTICAS INFERIOR
    ══════════════════════════════════════════ -->
    <div class="features-banner-grid">
        <div class="feature-item">
            <div class="feature-icon">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div class="feature-info">
                <h5>Productos de alta calidad</h5>
                <p>Insumos certificados y seguros</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon">
                <i class="fa-solid fa-user-nurse"></i>
            </div>
            <div class="feature-info">
                <h5>Profesionales expertas</h5>
                <p>Especialistas certificadas en cada técnica</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon">
                <i class="fa-solid fa-shield-heart"></i>
            </div>
            <div class="feature-info">
                <h5>Ambiente seguro y relajante</h5>
                <p>Bioseguridad y confort garantizados</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon">
                <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div class="feature-info">
                <h5>Agenda fácil y rápida</h5>
                <p>Reserva tu cita en pocos segundos</p>
            </div>
        </div>
    </div>

</div>

<!-- Scripts interactivos extraídos al módulo Vite: resources/js/modules/clientes/catalogo.js -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper (abierto en headercliente.blade.php) -->
</div> <!-- Cierre de .app-layout (abierto en headercliente.blade.php) -->

</body>
</html>