# Explicación Código por Código de `catalogo.blade.php` (En mis palabras)

Este documento detalla **cada bloque, línea y función de código** que compone el archivo [`catalogo.blade.php`](file:///c:/laragon/www/LashApp/resources/views/Clientes/catalogo.blade.php), la galería virtual donde las clientas de **LashApp** exploran todos los tratamientos de pestañas, cejas y uñas, filtran por precios y agendan directamente.

---

## 1. Encabezado, Inclusiones y Estilos (Líneas 1 - 6)

```blade
<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Catálogo de Servicios'])
@include('layouts.sidebarcliente')

@vite(['resources/css/catalogoclient.css'])
```

### ¿Qué hace cada línea?
* **`@include('layouts.headercliente', ['titulo' => 'Catálogo de Servicios'])`:** Carga el encabezado del portal cliente e inyecta el título dinámico para la pestaña del navegador.
* **`@include('layouts.sidebarcliente')`:** Carga el menú lateral con navegación por las opciones de la clienta.
* **`@vite(['resources/css/catalogoclient.css'])`:** Compila y carga la hoja de estilos CSS específica del catálogo (tarjetas, grid responsive, vista lista, botones de favoritos y animaciones).

---

## 2. Preparación de Datos con Fallback Inteligente (Líneas 7 - 147)

```blade
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
                // 8 servicios de demostración con fotos reales...
            ];
        }
    }
@endphp
```

### ¿Qué hace cada parte?
* **`$servicios->map(...)`:** Si la base de datos contiene registros, recorre cada modelo `Servicio` y lo transforma en un arreglo asociativo limpio.
* **Manejo seguro de imagen (`$img`):** Verifica si la ruta es una URL externa (`FILTER_VALIDATE_URL`) o una imagen interna del proyecto (`asset(...)`).
* **Formateo de duración (`$duracionTexto`):** Si la base de datos guarda horas en formato `01:30:00`, lo convierte a texto amigable tipo `1h 30m`.
* **Slug de categoría (`$catSlug`):** Convierte el nombre de la categoría a minúsculas sin acentos (ej. *"Lifting & Laminado"* -> `lifting-laminado`) para usarlo en los filtros de JavaScript.
* **`else` (Fallback de alta calidad):** Si la base de datos está vacía, genera 8 servicios predeterminados (Extensiones Clásicas, Volumen Ruso, Mega Volumen, Cejas con Henna, Microblading, Lifting, Brow Lamination y Manicure) con fotos reales ya presentes en `public/img/`.

---

## 3. Encabezado del Catálogo (Líneas 149 - 170)

```blade
<div class="page-content">

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
```

### ¿Qué hace cada parte?
* **`cat-header-icon`:** Icono morado con forma de bolsa de compras `bag-shopping`.
* **Título y Subtítulo:** Mensaje de bienvenida al catálogo.
* **`cat-header-right`:** Botón circular con ícono `calendar-plus` que conduce directamente a `route('cliente.agendar')` si la clienta ya sabe qué cita quiere sin necesidad de navegar el catálogo.

---

## 4. Barra de Pestañas por Categorías (Líneas 172 - 209)

```blade
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
            <!-- Pestañas por defecto si no hay categorías en BD -->
        @endif
    </div>
```

### ¿Qué hace cada parte?
* **Pestaña "Todos":** Viene marcada con la clase `.active` por defecto.
* **`match($catSlug)`:** Expresión de PHP 8 que asocia automáticamente el ícono visual perfecto según la categoría:
  * `pestanas` -> ojo (`fa-eye`).
  * `cejas` -> varita mágica (`fa-wand-magic-sparkles`).
  * `lifting` -> flecha ascendente (`fa-arrow-trend-up`).
  * `unas` -> mano brillante (`fa-hand-sparkles`).
* **Evento `onclick="filtrarPorCategoria('slug', this)"`:** Filtra en el cliente sin recargar la página.

---

## 5. Barra de Búsqueda, Filtros y Vistas (Líneas 211 - 252)

```blade
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
```

### ¿Qué hace cada control?
* **Contador dinámico (`#totalServiciosCount`):** Muestra cuántos servicios coinciden en tiempo real tras aplicar filtros.
* **`#buscarServicioInput`:** Campo de texto con evento `onkeyup="filtrarServicios()"` para búsqueda instantánea.
* **`#ordenarSelect`:** Reordena las tarjetas en el DOM por precio ascendente/descendente, popularidad o alfabeto.
* **`#precioSelect`:** Filtra por rangos de presupuesto.
* **`#btnViewGrid` y `#btnViewList`:** Botones que alternan entre vista de cuadrícula o vista en lista vertical.

---

## 6. Cuadrícula de Tarjetas de Servicio (Líneas 254 - 319)

```blade
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
            <!-- Estado si no hay ningún servicio -->
        @endforelse

        <!-- Mensaje cuando los filtros no arrojan resultados -->
        <div class="no-results-box" id="noResultsMsg" style="display: none;">
            <div class="no-results-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
            <h4>No encontramos servicios que coincidan</h4>
            <p>Intenta ajustar la búsqueda, el rango de precio o la categoría seleccionada.</p>
            <button type="button" onclick="limpiarFiltros()">
                <i class="fa-solid fa-rotate-left"></i> Restablecer filtros
            </button>
        </div>
    </div>
```

### ¿Qué hace cada parte?
* **Atributos `data-*`:** Cada tarjeta almacena `data-nombre`, `data-categoria`, `data-precio` y `data-popularidad` para que los scripts filtren y ordenen a máxima velocidad en el navegador sin peticiones HTTP.
* **`loading="lazy"`:** Carga diferida de imágenes para no saturar la conexión del móvil.
* **`fav-heart-btn`:** Botón con forma de corazón que ejecuta `toggleFavorito(this, 'nombre')`.
* **`cat-service-price`:** Precio formateado en pesos colombianos con separador de miles.
* **`btn-cat-detalles`:** Enlace directo a agendar con el parámetro `servicio_id`.
* **`#noResultsMsg`:** Caja de error amigable que aparece cuando ningún servicio coincide con el filtro, junto con el botón *"Restablecer filtros"*.

---

## 7. Banner de Características y Garantías (Líneas 321 - 364)

```blade
    <div class="features-banner-grid">
        <div class="feature-item">
            <div class="feature-icon"><i class="fa-solid fa-shield-halved"></i></div>
            <div class="feature-info">
                <h5>Productos de alta calidad</h5>
                <p>Insumos certificados y seguros</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon"><i class="fa-solid fa-user-nurse"></i></div>
            <div class="feature-info">
                <h5>Profesionales expertas</h5>
                <p>Especialistas certificadas en cada técnica</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon"><i class="fa-solid fa-shield-heart"></i></div>
            <div class="feature-info">
                <h5>Ambiente seguro y relajante</h5>
                <p>Bioseguridad y confort garantizados</p>
            </div>
        </div>

        <div class="feature-item">
            <div class="feature-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="feature-info">
                <h5>Agenda fácil y rápida</h5>
                <p>Reserva tu cita en pocos segundos</p>
            </div>
        </div>
    </div>
</div>
```

### ¿Qué hace cada ítem?
* Cuatro tarjetas informativas con íconos vectoriales que refuerzan la confianza del cliente en la marca (calidad de insumos, certificación de lashistas, bioseguridad y facilidad de reserva).

---

## 8. Scripts de Interactividad en JavaScript (Líneas 367 - 533)

```javascript
    let categoriaActiva = 'todas';

    // 1. Filtrar por pestaña de categoría
    function filtrarPorCategoria(categoria, boton) {
        categoriaActiva = categoria;
        document.querySelectorAll('.cat-tab-item').forEach(btn => btn.classList.remove('active'));
        if (boton) boton.classList.add('active');
        filtrarServicios();
    }

    // 2. Filtrado combinado (Texto + Categoría + Precio)
    function filtrarServicios() {
        const query = document.getElementById('buscarServicioInput').value.toLowerCase().trim();
        const rangoPrecio = document.getElementById('precioSelect').value;
        const cards = document.querySelectorAll('#catCardsGrid .cat-card-item');
        let visibles = 0;

        cards.forEach(card => {
            const nombre = (card.getAttribute('data-nombre') || '').toLowerCase();
            const categoria = (card.getAttribute('data-categoria') || '').toLowerCase();
            const precio = parseFloat(card.getAttribute('data-precio') || '0');

            const coincideTexto = !query || nombre.includes(query) || categoria.includes(query);
            const coincideCategoria = (categoriaActiva === 'todas') || categoria.includes(categoriaActiva);

            let coincidePrecio = true;
            if (rangoPrecio === '0-100') coincidePrecio = precio <= 100000;
            else if (rangoPrecio === '100-200') coincidePrecio = precio > 100000 && precio <= 200000;
            else if (rangoPrecio === '200+') coincidePrecio = precio > 200000;

            if (coincideTexto && coincideCategoria && coincidePrecio) {
                card.style.display = '';
                visibles++;
            } else {
                card.style.display = 'none';
            }
        });

        document.getElementById('totalServiciosCount').textContent = visibles;
        document.getElementById('noResultsMsg').style.display = (visibles === 0) ? 'block' : 'none';
    }

    // 3. Ordenamiento en el DOM
    function ordenarServicios() {
        const criterio = document.getElementById('ordenarSelect').value;
        const grid = document.getElementById('catCardsGrid');
        const cards = Array.from(grid.querySelectorAll('.cat-card-item'));

        cards.sort((a, b) => {
            const precioA = parseFloat(a.getAttribute('data-precio') || '0');
            const precioB = parseFloat(b.getAttribute('data-precio') || '0');
            const popA = parseFloat(a.getAttribute('data-popularidad') || '0');
            const popB = parseFloat(b.getAttribute('data-popularidad') || '0');
            const nomA = (a.getAttribute('data-nombre') || '').toLowerCase();
            const nomB = (b.getAttribute('data-nombre') || '').toLowerCase();

            if (criterio === 'menor_precio') return precioA - precioB;
            if (criterio === 'mayor_precio') return precioB - precioA;
            if (criterio === 'nombre_asc') return nomA.localeCompare(nomB);
            return popB - popA;
        });

        const noResults = document.getElementById('noResultsMsg');
        cards.forEach(card => grid.appendChild(card));
        if (noResults) grid.appendChild(noResults);
    }

    // 4. Alternar Grid vs Lista
    function cambiarVista(tipo) {
        const grid = document.getElementById('catCardsGrid');
        if (tipo === 'list') {
            grid.classList.add('list-view');
            document.getElementById('btnViewList').classList.add('active');
            document.getElementById('btnViewGrid').classList.remove('active');
        } else {
            grid.classList.remove('list-view');
            document.getElementById('btnViewGrid').classList.add('active');
            document.getElementById('btnViewList').classList.remove('active');
        }
    }

    // 5. Toggle de favoritos con SweetAlert2 Toast
    function toggleFavorito(button, nombreServicio) {
        button.classList.toggle('active');
        const icon = button.querySelector('i');
        const esFavorito = button.classList.contains('active');

        if (esFavorito) {
            icon.classList.replace('fa-regular', 'fa-solid');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `"${nombreServicio}" añadido a favoritos`,
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } else {
            icon.classList.replace('fa-solid', 'fa-regular');
        }
    }

    // 6. Restablecer filtros
    function limpiarFiltros() {
        document.getElementById('buscarServicioInput').value = '';
        document.getElementById('precioSelect').value = 'todos';
        document.getElementById('ordenarSelect').value = 'populares';
        filtrarPorCategoria('todas', document.querySelector('.cat-tab-item[data-categoria="todas"]'));
    }
```

### ¿Qué hace cada función?
1. **`filtrarPorCategoria`:** Modifica la variable global `categoriaActiva`, resalta la pestaña elegida y llama a `filtrarServicios()`.
2. **`filtrarServicios`:** Aplica **3 filtros simultáneos** (texto, categoría y rango de precio) sobre cada tarjeta leyendo sus atributos `data-*`. Actualiza el contador numérico y activa el mensaje de sin resultados si `visibles === 0`.
3. **`ordenarServicios`:** Extrae las tarjetas en un array de JavaScript, las ordena con `Array.prototype.sort()` y las vuelve a insertar en `#catCardsGrid`.
4. **`cambiarVista`:** Añade o remueve la clase CSS `.list-view`, reestructurando las tarjetas en filas horizontales o cuadrícula sin perder datos.
5. **`toggleFavorito`:** Rellena o vacía el ícono del corazón (`fa-solid` vs `fa-regular`) y dispara una notificación flotante (toast) con SweetAlert2.
6. **`limpiarFiltros`:** Borra los campos de búsqueda, reinicia los selects y devuelve la vista a la categoría *"Todas"*.

---

## 9. Footer y Cierre de Etiquetas (Líneas 535 - 541)

```blade
@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>
```

* Cierra los envoltorios principales abiertos en el header y asegura que los scripts del footer se carguen correctamente al final.
