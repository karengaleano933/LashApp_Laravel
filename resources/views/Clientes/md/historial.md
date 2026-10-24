# Explicación Código por Código de `historial.blade.php` (En mis palabras)

Este documento detalla **cada bloque, línea y función de código** que compone el archivo [`historial.blade.php`](file:///c:/laragon/www/LashApp/resources/views/Clientes/historial.blade.php), el registro histórico y financiero donde las clientas de **LashApp** consultan todos los servicios completados, el total acumulado de dinero invertido, filtran por fechas o lashistas y descargan su historial en PDF.

---

## 1. Encabezado, Inclusiones y Preparación de Datos (Líneas 1 - 93)

```blade
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

            // Lógica de asignación de foto según el nombre del servicio
            $img = asset('img/volumen_ruso.jpg');
            // ... detección de imágenes ...

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

    // Estadísticas conectadas a BD
    $statTotalCitas = $totalCitas ?? count($historialItems);
    $statServiciosRealizados = $serviciosRealizados ?? 0;
    $statTotalInvertido = isset($totalInvertido) && $totalInvertido > 0 
        ? '$' . number_format($totalInvertido, 0, ',', '.') 
        : '$0';
    $statCalificacion = isset($calificacionPromedio) ? number_format($calificacionPromedio, 1) : '5.0';
@endphp
```

### ¿Qué hace cada parte?
* **Arreglo de Meses abreviados (`$meses`):** Mapea números de mes (1 al 12) a siglas en español (`ENE`, `FEB`, `MAR`...) para pintarlos en el bloque de fecha tipo calendario.
* **Separación de Día, Mes y Año:** Divide `$fechaObj` en día (`d`), mes (`ENE`) y año (`Y`) para el diseño gráfico.
* **Slugs de servicio y profesional:** Para usarlos como atributos de filtrado rápido en JavaScript.
* **Cálculo de Estadísticas:** Define `$statTotalCitas`, `$statServiciosRealizados`, `$statTotalInvertido` (dinero acumulado gastado) y `$statCalificacion`.

---

## 2. Encabezado y Botones de Acción (Líneas 98 - 120)

```blade
    <div class="historial-header">
        <div class="historial-header-left">
            <div class="historial-header-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <div>
                <h1>Historial</h1>
                <p>Consulta el historial de todas tus citas y servicios realizados</p>
            </div>
        </div>
        <div class="historial-header-right">
            <a href="{{ route('cliente.agendar') }}" class="btn-agendar-hist-empty">
                <i class="fa-solid fa-calendar-plus"></i> Agendar Cita
            </a>
            <button type="button" class="icon-btn-circle" title="Descargar o Imprimir Historial" onclick="descargarHistorialPDF()">
                <i class="fa-solid fa-print"></i>
            </button>
        </div>
    </div>
```

* **Botón Agendar Cita:** Acceso directo a nueva reserva.
* **Botón Imprimir (`descargarHistorialPDF()`):** Abre una ventana de impresión optimizada para guardar el historial en PDF con un diseño limpio.

---

## 3. Pestañas de Filtrado Rápido (Líneas 122 - 137)

```blade
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
```

* Cuatro botones que activan `filtrarHistorialTab(tipo, this)` y sincronizan automáticamente el selector de tipo de la barra lateral.

---

## 4. Columna Izquierda: Tarjetas del Historial (Líneas 143 - 233)

```blade
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
                
                <!-- Bloque de Fecha tipo Calendario -->
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

    <!-- Empty state y banner inferior informativo -->
```

### ¿Qué hace cada parte?
* **`hist-date-box`:** Presenta un recuadro morado con el día en grande y el mes/año debajo.
* **`hist-item-meta`:** Agrupa hora, lashista y método de pago con separadores de puntos.
* **`hist-arrow-btn`:** Botón con flecha circular que permite volver a agendar el mismo servicio con un clic.
* **`#histEmptyState`:** Bloque ilustrado que aparece cuando la clienta no tiene citas o cuando los filtros no arrojan resultados, con botón para restablecer filtros (`resetFiltros()`).

---

## 5. Columna Derecha: Widgets de Filtros y Resumen (Líneas 236 - 346)

### Widget 1: Filtros Avanzados
* **Rango de fechas (`#filterFechaInput`):** Búsqueda por año o fecha exacta.
* **Select de Tipo (`#filterTipoSelect`):** Sincronizado con las pestañas superiores.
* **Select de Servicio (`#filterServicioSelect`):** Carga los tratamientos existentes.
* **Select de Profesional (`#filterProfesionalSelect`):** Lista a las lashistas del estudio.
* **Botón "Limpiar filtros":** Reinicia todos los inputs.

### Widget 2: Resumen del Historial
* **Total de Citas:** `$statTotalCitas`.
* **Servicios Realizados:** `$statServiciosRealizados`.
* **Total Invertido:** `$statTotalInvertido` (suma del dinero acumulado de la clienta).
* **Calificación Promedio:** `$statCalificacion` con estrella dorada.

### Widget 3: Descarga en PDF
* Explica que el reporte es apto para imprimir o guardar y contiene el botón *"Descargar PDF"*.

---

## 6. Scripts de JavaScript (Líneas 354 - 497)

```javascript
    let tabActiva = 'todos';

    // 1. Filtrar por tabs y sincronizar select lateral
    function filtrarHistorialTab(tipo, btn) { ... }
    function sincronizarTipoConTab(tipo) { ... }

    // 2. Aplicar filtros combinados (tipo, fecha, servicio, profesional) y ordenación en el DOM
    function aplicarFiltrosYOrden() {
        const cards = Array.from(document.querySelectorAll('.hist-card-item'));
        // Evalúa:
        // - matchTab: tiene-cita, tiene-servicio, tiene-pago
        // - matchFecha: fechaQuery
        // - matchServicio: servicio_slug
        // - matchProfesional: profesional_slug
        // Muestra u oculta la tarjeta
        // Ordena por fechaB - fechaA (recientes) o precioB - precioA (mayor valor)
    }

    // 3. Resetear filtros
    function resetFiltros() { ... }

    // 4. Descargar o imprimir PDF con SweetAlert2
    function descargarHistorialPDF() {
        Swal.fire({
            title: 'Generando Reporte PDF',
            text: 'Se abrirá la vista de impresión optimizada para guardar tu historial en PDF.',
            icon: 'info',
            confirmButtonText: 'Imprimir / Guardar PDF'
        }).then(res => { if (res.isConfirmed) window.print(); });
    }
```
