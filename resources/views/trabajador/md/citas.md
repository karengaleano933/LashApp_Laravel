# Explicación de mi Vista de Mis Citas Asignadas (`citas/citas_trabajador.blade.php`)

Esta vista es la agenda de citas personal de la trabajadora o especialista en LashApp. Desde aquí la lashista puede consultar sus citas asignadas del día y de la semana, buscar clientas o tratamientos en tiempo real, filtrar por estado o fecha, interactuar con el mini calendario mensual, marcar citas como realizadas (chulito verde) o cancelarlas, y agendar una nueva cita rápida con una ventana emergente.

---

### 1. Inclusión de encabezados, estilos y datos de sesión

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header_trabajador', ['titulo' => 'Mis Citas'])` | Cargo la cabecera para trabajadoras con el título "Mis Citas". |
| `@include('layouts.sidebar_trabajador')` | Cargo el menú lateral de navegación de la empleada. |
| `@vite(['resources/css/citas.css', 'resources/css/citas_trabajador.css', 'resources/js/modules/trabajador/citas.js'])` | Conecto las hojas de estilo y el módulo JavaScript de citas mediante Vite. |
| `<div class="page-content" id="citasTrabajadorContainer" data-url-completar="..." data-url-cancelar="...">` | Contenedor principal que almacena en atributos `data-*` las rutas para completar o cancelar citas y los mensajes de sesión. |

---

### 2. Tarjetas de métricas de la trabajadora

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="cita-card"> ... {{ $citasHoy ?? 0 }} ... </div>` | Tarjeta morada que muestra cuántas citas tiene programadas la trabajadora exclusivamente para el día de hoy. |
| `<div class="cita-card"> ... {{ $citasSemana ?? 0 }} ... </div>` | Tarjeta azul que contabiliza las citas de toda la semana de trabajo. |
| `<div class="cita-card"> ... {{ $citasComplet ?? 0 }} ... </div>` | Tarjeta verde que cuenta el total de citas que la trabajadora ha finalizado con éxito. |
| `<div class="cita-card"> ... {{ $citasCancelad ?? 0 }} ... </div>` | Tarjeta roja que muestra las citas que fueron canceladas en su agenda. |

---

### 3. Barra de herramientas y tabla de citas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<select id="selectRegistros"> <option value="10">10</option> ... </select>` | Selector para elegir cuántas citas ver por página (10, 25 o 50 registros). |
| `<input type="text" id="buscarTabla" placeholder="Buscar por cliente, servicio...">` | Buscador en vivo que filtra instantáneamente las filas de la tabla mientras la lashista teclea. |
| `<table id="citasTable" class="citas-table">` | Tabla principal con columnas para Cliente, Servicio, Hora, Fecha, Profesional, Estado y Acciones. |
| `@forelse ($proximasCitas ?? [] as $cita) ... @empty ... @endforelse` | Bucle que recorre las citas asignadas; si la agenda está vacía, muestra un ícono de calendario con un aviso amigable. |
| `<div class="cliente-avatar-badge">{{ $inicial }}</div> <strong>{{ $clienteNombre }}</strong>` | Muestra la inicial de la clienta en un círculo y su nombre completo. |
| `<span class="servicio-pill"><i class="fa-solid fa-spa"></i> {{ $servicioNombre }}</span>` | Pastilla morada con el tratamiento a realizar. |
| `{{ date('g:i a', strtotime($horaInicio)) }}` | Hora de la cita en formato de 12 horas con am/pm (ej. `09:30 am`). |
| `{{ date('d/m/Y', strtotime($fechaCita)) }}` | Fecha de la cita en formato día/mes/año. |
| `<span class="estado-badge estado-{{ $estadoClase }}">{{ $estado }}</span>` | Badge con color correspondiente al estado de la cita. |

---

### 4. Acciones de la cita (completar, cancelar o estado final)

| Código | ¿Qué hace? |
| :--- | :--- |
| `@if (!in_array(strtolower($estado), ['finalizado', 'completado', 'completada', 'realizada', 'cancelado', 'cancelada']))` | Si la cita aún está pendiente o confirmada y lista para atenderse. |
| `<button class="btn-accion btn-completar" onclick="confirmarCompletar({{ $idCita }})">` | Botón verde con chulito (`fa-check`) para marcar la cita como realizada tras confirmación. |
| `<button class="btn-accion btn-cancelar" onclick="confirmarCancelar({{ $idCita }})">` | Botón rojo con ícono de cancelar (`fa-ban`) para anular la cita de forma segura. |
| `@elseif (in_array(strtolower($estado), ['finalizado', 'completado', ...]))` | Si ya fue atendida. |
| `<span class="badge-realizada-pill"><i class="fa-solid fa-circle-check"></i> Realizada</span>` | Pastilla verde fija indicando que la cita se cumplió. |
| `@else` | Si fue anulada. |
| `<span class="badge-cancelada-pill"><i class="fa-solid fa-circle-xmark"></i> Cancelada</span>` | Pastilla roja fija indicando que está cancelada. |
| `<button class="btn-ver-todas" onclick="limpiarFiltros()"> ... </button>` | Botón inferior para restablecer la tabla y ver todas las citas sin filtros. |

---

### 5. Panel derecho: Calendario interactivo y filtros avanzados

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="calendario-card"> ... </div>` | Mini calendario mensual donde JavaScript dibuja los días y resalta citas con bolitas de colores. |
| `<button id="calPrev"> ... <span id="calMesAnio"></span> ... <button id="calNext">` | Controles de flechas para navegar entre meses. |
| `<div class="cal-leyenda"> ... </div>` | Leyenda de colores (verde para confirmada, rojo para cancelada, amarillo para pendiente). |
| `<select id="filtroProfesional" class="filtro-select"> ... </select>` | Menú desplegable para filtrar por especialista. |
| `<select id="filtroEstado" class="filtro-select"> ... </select>` | Menú desplegable para filtrar por estado (Confirmada, Pendiente, Cancelada, Completada). |
| `<input type="date" id="filtroFecha" class="filtro-select">` | Selector de fecha para consultar la agenda de un día en específico. |

---

### 6. Modal emergente para agendar nueva cita

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div id="modalNuevaCita" class="modal-lash-overlay" ...>` | Fondo oscuro y contenedor de la ventana emergente para que la trabajadora agende una cita directamente. |
| `<form action="{{ route('citas.store') }}" method="POST" id="formNuevaCita"> @csrf` | Formulario protegido con token CSRF hacia la ruta de almacenamiento de citas. |
| `<select name="id_cliente" id="id_cliente" required> ... </select>` | Lista desplegable con las clientas registradas (`$listaClientes`). |
| `<select name="id_servicio" id="id_servicio" required> ... </select>` | Lista desplegable con los servicios activos y sus tarifas en pesos colombianos. |
| `<select name="id_trabajador" id="id_trabajador" required> ... </select>` | Lista de especialistas, con la trabajadora actual seleccionada por defecto. |
| `<input type="date" name="fecha_cita" id="fecha_cita" value="{{ $hoy }}" required>` | Campo para definir la fecha de la cita. |
| `<input type="time" name="hora_inicio" id="hora_inicio" value="09:00" required>` | Campo para definir la hora de inicio del procedimiento. |
| `<select name="estado" id="estado"> ... </select>` | Estado inicial de la cita (Pendiente, Confirmada o Completada). |
| `<select name="metodo_pago" id="metodo_pago"> ... </select>` | Menú para fijar la forma de pago (Efectivo, Transferencia o Tarjeta). |
| `<textarea name="observaciones" id="observaciones" ...></textarea>` | Cuadro de texto para notas sobre preferencias de diseño o alergias de la clienta. |
| `<button type="submit" class="modal-lash-btn-agendar"> Guardar Cita </button>` | Botón final para confirmar y registrar la cita en la base de datos. |
| `@include('layouts.footer')` | Pie de página común del sistema. |
