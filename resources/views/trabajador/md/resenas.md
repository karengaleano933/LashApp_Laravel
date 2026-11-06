# Explicación de mi Vista de Reseñas de Trabajador (`resenas/resenas_trabajador.blade.php`)

Esta vista permite a la trabajadora o especialista en LashApp consultar y analizar las opiniones que las clientas han dejado sobre su trabajo. Muestra su promedio de estrellas, desglose de calificaciones (positivas, neutras o por mejorar), lista con los comentarios y nombres de clientas con estrellas doradas, filtros por tipo de valoración y un gráfico circular en forma de dona con la distribución de notas del 1 al 5.

---

### 1. Inclusión de encabezados, estilos y cálculo de estadísticas

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header_trabajador', ['titulo' => 'Reseñas'])` | Cargo la cabecera para trabajadoras con el título "Reseñas". |
| `@include('layouts.sidebar_trabajador')` | Cargo el menú lateral de la especialista. |
| `@vite(['resources/css/resenas_trabajador.css', 'resources/js/modules/trabajador/resenas.js'])` | Conecto los estilos y el script modular de interactividad mediante Vite. |
| `$query = \App\Models\Resena::...;` | Bloque PHP que consulta en la base de datos solo las opiniones asociadas a las citas atendidas por esta trabajadora, calculando el total y el promedio de estrellas. |

---

### 2. Tarjetas de métricas de reputación

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="card-sparkle-title">Calificación Promedio</div> ... {{ number_format($promedioCalificacion, 1) }} / 5.0 ...` | Tarjeta con ícono de estrella que muestra su puntaje de reputación global. |
| `<div class="card-sparkle-title">Total de Reseñas</div> ... {{ $totalResenas }} ...` | Tarjeta morada con la cantidad total de comentarios recibidos. |
| `<div class="card-sparkle-title">Positivas (4-5★)</div> ... {{ $positivas }} ...` | Tarjeta verde que cuenta cuántas clientas le dieron 4 o 5 estrellas y el porcentaje correspondiente. |
| `<div class="card-sparkle-title">Neutras (3★)</div> ... {{ $neutras }} ...` | Tarjeta amarilla que cuenta las valoraciones intermedias (3 estrellas). |
| `<div class="card-sparkle-title">Por Mejorar (1-2★)</div> ... {{ $negativas }} ...` | Tarjeta roja que muestra cuántas valoraciones bajas recibió (1 o 2 estrellas). |

---

### 3. Pestañas de filtrado de comentarios

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button class="tab-pill-item active" data-filter="all">Todas ({{ $totalResenas }})</button>` | Pestaña para ver todas las opiniones sin importar el puntaje. |
| `<button class="tab-pill-item" data-filter="positivas">Positivas ({{ $positivas }})</button>` | Pestaña para filtrar solo las reseñas de 4 y 5 estrellas. |
| `<button class="tab-pill-item" data-filter="neutras">Neutras ({{ $neutras }})</button>` | Pestaña para aislar los comentarios de 3 estrellas. |
| `<button class="tab-pill-item" data-filter="negativas">Por Mejorar ({{ $negativas }})</button>` | Pestaña para revisar las opiniones críticas de 1 o 2 estrellas. |

---

### 4. Listado de reseñas y testimonios de clientas

| Código | ¿Qué hace? |
| :--- | :--- |
| `@forelse ($resenas as $r) ... @empty ... @endforelse` | Bucle que recorre cada valoración; si no tiene opiniones, muestra un aviso diciendo que aún no hay comentarios. |
| `<div class="resena-avatar">{{ $iniciales }}</div> <strong>{{ $nombreCliente }}</strong>` | Muestra un círculo con las iniciales de la clienta y su nombre completo. |
| `<span class="resena-servicio-tag"><i class="fa-solid fa-spa"></i> {{ $servicioNombre }}</span>` | Muestra el tratamiento que recibió (ej. Lifting de pestañas o cejas). |
| `@for ($i = 1; $i <= 5; $i++) <i class="fa-solid fa-star" ...></i> @endfor` | Pinta de color ámbar las estrellas obtenidas según la nota (1 al 5). |
| `<div class="resena-comment-text"> "{{ $r->comentario }}" </div>` | Muestra el texto del testimonio que escribió la clienta sobre la atención. |
| `<span class="resena-date-text"> {{ $fecha }} </span>` | Muestra la fecha en que se publicó la reseña. |
| `<span ...> Verificada </span>` | Etiqueta verde indicando que es una cita real completada en el salón. |

---

### 5. Gráfico circular (Dona) y leyenda de estrellas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<svg viewBox="0 0 36 36"> ... </svg>` | Dibuja un gráfico circular de dona usando SVG puro donde cada segmento de color representa la proporción de estrellas del 1 al 5. |
| `<div class="donut-center-text"> <strong>{{ number_format($promedioCalificacion, 1) }}</strong> <span>Promedio</span> </div>` | Muestra en el centro de la dona el promedio en número grande. |
| `<div class="stars-legend-list"> ... </div>` | Lista con puntos de colores que indica cuántas reseñas hay en cada nivel de estrellas y su porcentaje. |
| `<div class="dashboard-card" style="background: linear-gradient(...)"> ... <h4>Tip Profesional</h4> ... </div>` | Tarjeta decorativa con un consejo profesional para brindar una mejor atención y obtener mejores reseñas. |
| `@include('layouts.footer')` | Pie de página común del sistema. |
