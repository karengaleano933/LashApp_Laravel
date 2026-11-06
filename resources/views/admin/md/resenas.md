# Explicación de mi Vista de Reseñas y Valoraciones (`resenas/resenas.blade.php`)

Esta vista es el termómetro de satisfacción del cliente en LashApp. Me permite conocer las opiniones y testimonios de las clientas atendidas: veo la calificación promedio global con estrellas doradas, barras de distribución por nivel (1 a 5 estrellas), tabla con comentarios detallados, filtros por número de estrellas, buscador por palabras clave, formulario lateral para registrar valoraciones manuales y eliminación segura de reseñas.

---

### 1. Inclusión de componentes y alertas

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header', ['titulo' => 'Reseñas y Valoraciones'])` | Cargo los estilos y fuentes globales con el título de la página. |
| `@include('layouts.sidebaradmin')` | Cargo el menú lateral de administración. |
| `@vite(['resources/css/resenas.css', 'resources/js/modules/admin/resenas.js'])` | Conecto los estilos específicos y la lógica interactiva de reseñas mediante Vite. |
| `<div class="page-content" id="resenasContainer" data-base-url="{{ url('/admin/resenas') }}">` | Contenedor principal con el enlace base para procesar solicitudes desde JavaScript. |
| `@if (session('success')) ... @endif` | Cuadro verde que me avisa cuando una reseña se guardó o eliminó con éxito. |
| `@if (session('error')) ... @endif` | Cuadro rojo si ocurrió algún fallo al procesar la reseña. |
| `@if (isset($errors) && $errors->any()) ... @endif` | Cuadro con la lista de campos pendientes si falló alguna validación. |

---

### 2. Encabezado y tarjetas de satisfacción

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="btn-exportar" onclick="window.print()">` | Botón con ícono de descarga para imprimir o exportar el resumen de opiniones. |
| `<div class="resena-card"> ... {{ $promedioCalificacion ?? 5.0 }} / 5 ... </div>` | Tarjeta dorada que muestra el puntaje promedio general del negocio (ej. `4.9 / 5`). |
| `<div class="resena-card"> ... {{ $totalResenas ?? 0 }} ... </div>` | Tarjeta morada que muestra la cantidad total de testimonios y valoraciones recibidas. |
| `<div class="resena-card"> ... {{ $pct5 ?? 100 }}% ... </div>` | Tarjeta verde que calcula el porcentaje de reseñas excelentes con calificación máxima de 5 estrellas. |
| `<div class="resena-card"> ... <strong>94%</strong> ... </div>` | Tarjeta rosada con ícono de corazón que mide la fidelidad y recomendación de las clientas hacia el salón. |

---

### 3. Distribución visual de calificaciones (Desglose de estrellas)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="resenas-breakdown-card"> ... </div>` | Tarjeta principal donde muestro la nota en número gigante junto a estrellas pintadas completas, medias o vacías según el puntaje. |
| `<div class="linea-estrella"> ... <span>5 Estrellas</span> ... <div class="bar-fill" style="width: {{ $pct5 ?? 100 }}%;"></div> ... </div>` | Barra horizontal de progreso que se llena visualmente en morado según la proporción de clientas que calificaron con 5 estrellas. |
| `<span>4 Estrellas</span> ... <span>3 Estrellas</span> ... <span>2 Estrellas</span> ... <span>1 Estrella</span>` | Barras de progreso correspondientes a cada nivel de calificación con sus porcentajes exactos. |

---

### 4. Filtro por estrellas y buscador en tiempo real

| Código | ¿Qué hace? |
| :--- | :--- |
| `<select id="filtroEstrellas"> ... </select>` | Menú desplegable para ver todas las calificaciones o aislar solo las de 5, 4, 3, 2 o 1 estrella. |
| `<input type="text" id="buscarResena" placeholder="Buscar por cliente, servicio o comentario...">` | Campo buscador en vivo que filtra los testimonios mientras escribo cualquier palabra. |

---

### 5. Tabla de testimonios y opiniones de clientas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<table id="tablaResenas" class="resenas-table">` | Tabla principal con columnas para Cliente, Servicio, Calificación, Comentario, Fecha y Acción. |
| `@forelse ($resenas ?? [] as $r) ... @empty ... @endforelse` | Bucle que recorre cada reseña; si no hay ninguna registrada, muestra un mensaje amigable. |
| `<div class="cliente-avatar-circle">{{ $inicial }}</div> <strong>{{ $clienteNombre }}</strong>` | Muestra la inicial de la clienta en un avatar circular y su nombre completo. |
| `<span style="font-weight:600; color:#7c1db8;"><i class="fa-solid fa-spa"></i> {{ $servicioNombre }}</span>` | Muestra el tratamiento recibido en morado. |
| `@for ($i = 1; $i <= 5; $i++) <i class="fa-{{ $i <= $stars ? 'solid' : 'regular' }} fa-star"></i> @endfor` | Dibuja 5 estrellas, rellenando en amarillo dorado las estrellas obtenidas y dejando vacías las restantes. |
| `<small style="color:#5e4d66; font-style:italic;"> "{{ $comentario }}" </small>` | Muestra en letra cursiva el comentario textual dejado por la clienta. |
| `{{ $fechaFormat }}` | Muestra la fecha y hora en que se redactó la opinión. |
| `<button type="button" class="btn-accion-danger btn-eliminar-resena" data-id="..." data-cliente="...">` | Botón con ícono de papelera para borrar reseñas inapropiadas tras una confirmación con SweetAlert2. |

---

### 6. Formulario lateral para registrar valoración

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="dejar-resena-card"> ... </div>` | Tarjeta lateral con formulario rápido para registrar una reseña que una clienta haya dejado en persona o por WhatsApp. |
| `<form action="{{ route('resenas.store') }}" method="POST" id="formResena"> @csrf` | Formulario protegido con token CSRF con método POST para guardar en la base de datos. |
| `<select name="id_cita" required> ... </select>` | Lista desplegable con las citas atendidas que aún no tienen reseña (`$citasSinResena`). |
| `<div class="star-rating-selector" id="starSelector"> ... </div>` | Selector interactivo de estrellas donde puedo hacer clic sobre cualquiera de las 5 estrellas para elegir la puntuación. |
| `<input type="hidden" name="calificacion" id="input_calificacion" value="5">` | Campo oculto que guarda el número de estrellas seleccionado (1 al 5) para enviarlo al servidor. |
| `<textarea name="comentario" rows="4" placeholder="..." required></textarea>` | Cuadro de texto para escribir el testimonio de la clienta. |
| `<button type="submit" class="btn-guardar-resena"> Guardar Valoración </button>` | Botón final para publicar la reseña. |
| `<form id="formEliminarResena" method="POST" style="display: none;"> @csrf @method('DELETE') </form>` | Formulario invisible con método `DELETE` para eliminar la reseña de forma segura tras la confirmación de la administradora. |
| `@include('layouts.footer')` | Pie de página del sistema. |
