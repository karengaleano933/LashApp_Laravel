# Explicación de mi Vista de Catálogo de Servicios (`servicios/servicios.blade.php`)

Esta vista es el menú oficial de tratamientos y servicios ofrecidos en LashApp. Desde aquí la administradora controla todo el catálogo: consulto el total de servicios activos e inactivos, busco tratamientos por nombre o precio, filtro por categoría (Pestañas, Cejas, etc.), registro nuevos servicios con foto y duración estimada mediante una ventana modal, edito tarifas o descripciones y elimino servicios con confirmación segura.

---

### 1. Inclusión de componentes y alertas

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header', ['titulo' => 'Gestión de Servicios'])` | Cargo los estilos, tipografías e íconos generales del sistema. |
| `@include('layouts.sidebaradmin')` | Cargo el menú lateral de administración. |
| `@vite(['resources/css/servicios.css', 'resources/js/modules/admin/servicios.js'])` | Conecto los estilos específicos del catálogo y la interactividad JavaScript mediante Vite. |
| `<div class="page-content" id="serviciosContainer" data-base-url="{{ url('/admin/servicios') }}">` | Contenedor principal con la ruta base guardada para procesar llamadas AJAX desde JavaScript. |
| `@if (session('success')) ... @endif` | Cuadro verde que me confirma cuando un servicio fue creado, modificado o eliminado con éxito. |
| `@if (session('error')) ... @endif` | Cuadro rojo si ocurrió algún problema al guardar. |
| `@if (isset($errors) && $errors->any()) ... @endif` | Cuadro con la lista de campos faltantes si falló la validación del formulario. |

---

### 2. Encabezado y tarjetas de estadísticas del catálogo

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="btn-nuevo-servicio" id="abrirNuevoServicio">` | Botón con ícono `+` para desplegar la ventana modal de crear un nuevo servicio. |
| `<div class="servicio-card"> ... {{ $totalServicios }} ... </div>` | Tarjeta morada con ícono de spa que indica el número total de tratamientos registrados en el catálogo. |
| `<div class="servicio-card"> ... {{ $activosCount }} ... </div>` | Tarjeta verde que cuenta cuántos servicios están activos y disponibles para que las clientas los agenden. |
| `<div class="servicio-card"> ... {{ $totalCategorias }} ... </div>` | Tarjeta rosada que muestra cuántas categorías de especialidades existen en el estudio. |
| `<div class="servicio-card"> ... {{ $inactivosCount }} ... </div>` | Tarjeta azul que muestra los servicios que están temporalmente pausados o inactivos. |

---

### 3. Filtro por categoría y buscador en tiempo real

| Código | ¿Qué hace? |
| :--- | :--- |
| `<select id="filtroCategoria"> ... </select>` | Menú desplegable que carga dinámicamente las categorías (`$categorias`) para filtrar la tabla al instante. |
| `<input type="text" id="buscarServicio" placeholder="Buscar servicio por nombre, categoría o precio...">` | Campo buscador en vivo que filtra las filas de la tabla mientras escribo cualquier palabra. |

---

### 4. Tabla de servicios registrados

| Código | ¿Qué hace? |
| :--- | :--- |
| `<table id="tablaServicios" class="servicios-table">` | Tabla principal con columnas para Imagen, Servicio, Categoría, Precio, Duración, Estado y Acciones. |
| `@forelse ($servicios ?? [] as $srv) ... @empty ... @endforelse` | Bucle que recorre cada servicio; si no hay ninguno en el catálogo, muestra un aviso de tabla vacía. |
| `<img src="{{ $imgSrc }}" alt="{{ $srvNombre }}" ...>` | Muestra la foto pequeña en miniatura del servicio con esquinas redondeadas; si no tiene foto subida, muestra un ícono de flor de loto. |
| `<strong>{{ $srvNombre }}</strong> <small>{{ $srvDesc }}</small>` | Muestra el nombre principal del servicio y una pequeña descripción debajo. |
| `<span class="categoria-badge"><i class="fa-solid fa-tag"></i> {{ $srvCatNombre }}</span>` | Pastilla visual con el nombre de la categoría asignada. |
| `<strong>${{ number_format((float)$srvPrecio, 0, ',', '.') }}</strong>` | Precio del servicio formateado con puntos de miles en pesos colombianos. |
| `<span><i class="fa-solid fa-clock"></i> {{ $srvDuracion }} min</span>` | Duración estimada del procedimiento en minutos (ej. `60 min`). |
| `<span class="estado-badge estado-{{ $srvEstado }}">{{ ucfirst($srvEstado) }}</span>` | Etiqueta verde si está "Activo" o gris si está "Inactivo". |
| `<button class="btn-accion btn-editar-servicio" data-id="..." data-nombre="..." ...>` | Botón con ícono de lápiz que precarga todos los datos del servicio en la ventana modal de edición. |
| `<button class="btn-accion btn-eliminar-servicio" data-id="..." data-nombre="...">` | Botón con ícono de basurero para eliminar el servicio tras una confirmación con SweetAlert2. |

---

### 5. Panel lateral de resumen de categorías

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="categorias-resumen-card"> ... </div>` | Tarjeta lateral que lista todas las categorías existentes con su ícono y una breve descripción de cada especialidad. |

---

### 6. Modal para crear un nuevo servicio

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="modal-servicio-overlay" id="modalCrearServicio">` | Ventana emergente con fondo oscuro para dar de alta un nuevo tratamiento en el salón. |
| `<form action="{{ route('servicios.store') }}" method="POST" enctype="multipart/form-data" id="formCrearServicio"> @csrf` | Formulario protegido con token CSRF y soporte para subir fotos del tratamiento. |
| `<input type="text" name="nombre" placeholder="Ej. Extensiones Pestañas..." required>` | Campo obligatorio para el nombre comercial del servicio. |
| `<select name="categoria_id" required> ... </select>` | Menú desplegable para asignarle su categoría técnica (Pestañas, Cejas, etc.). |
| `<input type="number" step="0.01" name="precio" placeholder="Ej. 65000" required>` | Campo numérico para fijar el costo del servicio en pesos. |
| `<input type="number" name="duracion_minuto" placeholder="Ej. 60" min="5" max="480" required>` | Campo para definir el tiempo estimado en minutos (mínimo 5 y máximo 480 minutos). |
| `<input type="file" name="imagen" accept="image/*">` | Selector de archivos para adjuntar una fotografía representativa del servicio. |
| `<textarea name="descripcion" rows="3" placeholder="..."></textarea>` | Campo opcional para describir el procedimiento, cuidados o recomendaciones. |
| `<select name="estado" required> <option value="activo">Activo</option> <option value="inactivo">Inactivo</option> </select>` | Permite fijar si el nuevo servicio sale inmediatamente al público o queda guardado como borrador. |
| `<button type="submit" class="btn-guardar-servicio"> Guardar Servicio </button>` | Botón final para guardar el servicio en la base de datos. |

---

### 7. Modal para editar servicio y formulario de eliminación

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="modal-servicio-overlay" id="modalEditarServicio">` | Ventana emergente para modificar el nombre, precio, duración, foto o categoría de un servicio existente. |
| `<form action="" method="POST" enctype="multipart/form-data" id="formEditarServicio"> @csrf @method('PUT')` | Formulario configurado con `@method('PUT')` para enviar los cambios actualizados a Laravel. |
| `<form id="formEliminarServicio" method="POST" style="display: none;"> @csrf @method('DELETE') </form>` | Formulario invisible con método `DELETE` para borrar el servicio tras la confirmación de la administradora. |
| `@include('layouts.footer')` | Pie de página común del sistema. |
