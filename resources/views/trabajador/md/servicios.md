# Explicación de mi Vista de Servicios de Trabajador (`servicios/servicios_trabajador.blade.php`)

Esta vista es el catálogo de tratamientos administrado por la especialista o trabajadora en LashApp. Desde aquí la empleada puede ver qué servicios están activos o pausados, filtrar por categoría, buscar servicios en tiempo real por nombre o precio, ver un gráfico de dona con la distribución de servicios por especialidad, registrar un nuevo servicio con foto y duración estimada mediante una ventana modal, editar servicios existentes y eliminar tratamientos obsoletos con confirmación segura.

---

### 1. Inclusión de componentes y preparación de datos

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header_trabajador', ['titulo' => 'Servicios'])` | Cargo la cabecera para trabajadoras con el título "Servicios". |
| `@include('layouts.sidebar_trabajador')` | Cargo el menú lateral de la especialista. |
| `@vite(['resources/css/servicios.css', 'resources/css/servicio_trabajador.css', 'resources/js/modules/trabajador/servicios.js'])` | Conecto las hojas de estilo y el script interactivo mediante Vite. |
| `<div class="page-content" id="serviciosTrabajadorContainer" data-url-update="..." data-url-destroy="...">` | Contenedor principal que almacena en atributos `data-*` las rutas de Laravel para actualizar o eliminar servicios vía JavaScript. |

---

### 2. Tarjetas de métricas del catálogo

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="card-sparkle-title">Servicios activos</div> ... {{ $serviciosActivos ?? 0 }} ...` | Tarjeta con ícono de tijeras que cuenta los servicios listos y disponibles para agendar. |
| `<div class="card-sparkle-title">Categorías registradas</div> ... {{ $totalCategorias ?? 0 }} ...` | Tarjeta azul que muestra el número de categorías o especialidades en el sistema. |
| `<div class="card-sparkle-title">Total en Catálogo</div> ... {{ $totalServicios ?? 0 }} ...` | Tarjeta morada con el total de tratamientos dados de alta en la plataforma. |
| `<div class="card-sparkle-title">Inactivos / Pausados</div> ... {{ $serviciosInact ?? 0 }} ...` | Tarjeta roja que muestra cuántos servicios están temporalmente suspendidos. |

---

### 3. Filtros y tabla de servicios

| Código | ¿Qué hace? |
| :--- | :--- |
| `<select id="filtroCategoria" class="filtro-categoria-select"> ... </select>` | Selector desplegable para filtrar la tabla y ver solo los servicios de una categoría específica. |
| `<button type="button" id="abrirNuevoServicio" class="btn-nuevo-servicio-trigger">` | Botón con ícono `+` para desplegar la ventana emergente de registrar servicio. |
| `<input type="text" id="buscarServicio" placeholder="Buscar por nombre, categoría o precio...">` | Buscador en vivo que filtra las filas al instante mientras escribo. |
| `<table class="servicios-table">` | Tabla principal con columnas para Servicio, Categoría, Duración, Precio, Estado y Acciones. |
| `@forelse ($servicios as $s) ... @empty ... @endforelse` | Bucle que recorre cada servicio; si no hay servicios, muestra un aviso avisando que el catálogo está vacío. |
| `<img src="{{ $imgSrc }}" alt="{{ $s->nombre }}" class="servicio-thumb-img">` | Muestra la foto en miniatura del servicio. |
| `<strong>{{ $s->nombre }}</strong> <span>{{ Str::limit($s->descripcion, 40) }}</span>` | Muestra el nombre del tratamiento y un extracto de su descripción. |
| `<span class="servicio-cat-pill"> {{ $catNombre }} </span>` | Pastilla con el nombre de la categoría asignada. |
| `{{ $duracionTxt }}` | Duración estimada formateada en horas y minutos (ej. `1h 30m`). |
| `${{ number_format($s->precio, 0, ',', '.') }}` | Precio en pesos colombianos con separador de miles. |
| `<span class="servicio-estado-badge {{ $estadoLower === 'activo' ? 'activo' : 'inactivo' }}">` | Etiqueta verde si está "Activo" o gris si está "Inactivo". |
| `<button class="btn-accion btn-editar" onclick='abrirModalEditar(@json($s))'>` | Botón con ícono de lápiz que abre el modal de edición cargando todos los datos del servicio en formato JSON. |
| `<button class="btn-accion btn-eliminar" onclick="confirmarEliminarServicio({{ $s->id }})">` | Botón con ícono de basurero para eliminar el servicio tras confirmar con SweetAlert2. |

---

### 4. Panel lateral: Gráfico por categoría y servicios más solicitados

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="chart-donut-wrap"> <svg viewBox="0 0 36 36"> ... </svg> </div>` | Dibuja un gráfico circular de dona usando SVG que ilustra qué porcentaje del catálogo corresponde a cada categoría. |
| `<div class="cat-legend-list"> ... </div>` | Leyenda con puntos de color que indica la cantidad y porcentaje de servicios por categoría. |
| `<div class="destacados-list"> ... </div>` | Lista con los servicios más populares según el número de citas que han recibido. |

---

### 5. Modal para crear nuevo servicio

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div id="modalNuevoServicio" class="modal-lash-overlay" ...>` | Ventana emergente con fondo oscuro para dar de alta un nuevo tratamiento. |
| `<form action="{{ route('servicios.store') }}" method="POST" enctype="multipart/form-data"> @csrf` | Formulario protegido con token CSRF y soporte para subir fotos. |
| `<input type="text" name="nombre" id="nombre" required placeholder="...">` | Campo para el nombre del servicio. |
| `<select name="categoria_id" id="categoria_id" required> ... </select>` | Selector para asignar la categoría. |
| `<input type="number" step="1000" min="0" name="precio" id="precio" required>` | Campo para definir el precio en pesos. |
| `<input type="number" min="1" name="duracion_minuto" id="duracion_minuto" required>` | Campo para la duración en minutos. |
| `<select name="estado" id="estado" required> ... </select>` | Selector para fijar el estado inicial (Activo o Inactivo). |
| `<input type="file" name="imagen" id="imagen" accept="image/*">` | Selector para adjuntar una imagen ilustrativa del servicio. |
| `<textarea name="descripcion" id="descripcion" rows="3"> ... </textarea>` | Cuadro de texto para notas o descripción técnica del procedimiento. |
| `<button type="submit" class="btn-primario"> Guardar Servicio </button>` | Botón final para guardar el servicio en la base de datos. |

---

### 6. Modal de edición y formulario de eliminación

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div id="modalEditarServicio" class="modal-lash-overlay" ...>` | Ventana emergente para modificar los datos de un servicio existente. |
| `<form id="formEditarServicio" action="" method="POST" enctype="multipart/form-data"> @csrf @method('PUT')` | Formulario con `@method('PUT')` para enviar la actualización al servidor. |
| `<form id="formEliminarServicio" action="" method="POST" style="display: none;"> @csrf @method('DELETE') </form>` | Formulario invisible con método `DELETE` que se envía automáticamente tras la confirmación en la alerta. |
| `@include('layouts.footer')` | Pie de página común del sistema. |
