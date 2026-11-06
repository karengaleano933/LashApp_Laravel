# Explicación de mi Vista de Gestión de Citas (`citas/citas.blade.php`)

Esta vista es el centro principal para administrar toda la agenda de citas en LashApp. Me permite ver qué clientas vienen hoy, revisar las citas de la semana, filtrar citas por profesional o fecha, interactuar con un calendario mensual, marcar citas como realizadas o cancelarlas con confirmación segura, y agendar nuevas citas en una ventana modal interactiva sin salir de la pantalla.

---

### 1. Inclusión de layouts y contenedor principal

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header')` | Cargo los encabezados globales de la aplicación (estilos, tipografías e íconos). |
| `@include('layouts.sidebaradmin')` | Cargo el menú lateral de administración y la barra superior de usuario. |
| `<div class="page-content" id="citasContainer" data-complete-url="..." data-cancel-url="...">` | Contenedor principal donde guardo en atributos `data-*` las rutas de Laravel para completar o cancelar citas desde JavaScript. |

---

### 2. Encabezado de la página y alertas de notificación

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="btn-nueva-cita" id="abrirNuevaCita">` | Botón llamativo con ícono `+` para desplegar la ventana emergente de agendar nueva cita. |
| `@if (session('success')) ... @endif` | Si el controlador guardó un mensaje de éxito en la sesión, muestro una caja verde con ícono de check confirmando la acción (por ejemplo tras crear o cancelar una cita). |
| `@if (isset($errors) && $errors->any()) ... @endif` | Si hubo errores de validación en el formulario, muestro un cuadro rojo con la lista de datos que faltaron o quedaron mal diligenciados. |

---

### 3. Tarjetas de métricas operativas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="cita-card"> ... {{ $citasHoy ?? 0 }} ... </div>` | Tarjeta morada con ícono de calendario que muestra la cantidad de citas reservadas exclusivamente para hoy. |
| `<div class="cita-card"> ... {{ $citasSemana ?? 0 }} ... </div>` | Tarjeta azul con ícono de reloj que me muestra las citas de toda la semana en curso. |
| `<div class="cita-card"> ... {{ $citasComplet ?? 0 }} ... </div>` | Tarjeta verde con ícono de verificación que cuenta cuántas citas han sido completadas con éxito en el mes. |
| `<div class="cita-card"> ... {{ $citasCancelad ?? 0 }} ... </div>` | Tarjeta roja que muestra cuántas citas fueron canceladas en el mes. |

---

### 4. Tabla de citas y filas dinámicas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<table id="citasTable" class="citas-table">` | Tabla estilizada conectada a DataTables con columnas para Cliente, Servicio, Hora, Fecha, Profesional, Estado y Acciones. |
| `@forelse ($proximasCitas ?? [] as $cita) ... @empty ... @endforelse` | Recorro cada cita recibida desde el controlador; si la lista está vacía, muestro un mensaje avisando que no hay citas próximas registradas. |
| `$inicial = strtoupper(substr($clienteNombre, 0, 1));` | Extraigo la inicial del nombre de la clienta para su avatar redondo. |
| `<div class="cliente-avatar-badge">{{ $inicial }}</div> <strong>{{ $clienteNombre }}</strong>` | Muestro el avatar con la inicial y el nombre completo de la clienta. |
| `<span class="servicio-pill"><i class="fa-solid fa-spa"></i> {{ $servicioNombre }}</span>` | Pastilla con ícono de spa con el nombre del servicio (ej. Lifting de pestañas). |
| `{{ date('g:i a', strtotime($horaInicio)) }}` | Convierto la hora militar a formato estándar de 12 horas con am/pm (ej. `10:30 am`). |
| `{{ date('d/m/Y', strtotime($fechaCita)) }}` | Convierto la fecha al formato día/mes/año (ej. `26/10/2026`). |
| `<span class="profesional-pill"><i class="fa-solid fa-user-tie"></i> {{ $profesional }}</span>` | Muestro el nombre de la lashista o especialista asignada a atender la cita. |
| `<span class="estado-badge estado-{{ $estadoClase }}">{{ $estado }}</span>` | Etiqueta con color personalizado según el estado (verde para confirmada, amarillo para pendiente, etc.). |

---

### 5. Botones de acción por cada cita

| Código | ¿Qué hace? |
| :--- | :--- |
| `@if (!in_array(strtolower($estado), ['finalizado', 'completado', 'completada', 'realizada', 'cancelado', 'cancelada']))` | Reviso si la cita todavía está activa y pendiente de atención. |
| `<button class="btn-accion btn-completar" onclick="confirmarCompletar({{ $idCita }})">` | Botón verde con ícono de chulito (`fa-check`) para marcar la cita como realizada tras pedir confirmación. |
| `<button class="btn-accion btn-cancelar" onclick="confirmarCancelar({{ $idCita }})">` | Botón rojo con ícono de prohibido (`fa-ban`) para cancelar la cita de forma segura. |
| `@elseif (in_array(strtolower($estado), ['finalizado', 'completado', 'completada', 'realizada']))` | Si la cita ya fue atendida con éxito. |
| `<span class="badge-realizada-pill"><i class="fa-solid fa-circle-check"></i> Realizada</span>` | Muestro una pastilla verde estática indicando que ya fue realizada. |
| `@else` | Si la cita fue anulada. |
| `<span class="badge-cancelada-pill"><i class="fa-solid fa-circle-xmark"></i> Cancelada</span>` | Muestro una pastilla roja estática indicando que está cancelada. |

---

### 6. Panel lateral: Calendario interactivo y filtros de búsqueda

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="calendario-card"> ... </div>` | Tarjeta del calendario mensual interactivo. |
| `<button type="button" id="calPrev"> ... <span id="calMesAnio"></span> ... <button type="button" id="calNext">` | Controles de navegación para retroceder o avanzar entre los meses del año y ver el nombre del mes. |
| `<div class="cal-grid" id="calGrid"></div>` | Cuadrícula donde JavaScript dibuja dinámicamente los números de los días. |
| `<div class="cal-leyenda"> ... </div>` | Leyenda explicativa con bolitas de colores para saber cuáles días tienen citas confirmadas, canceladas o pendientes. |
| `<input type="text" id="buscarCita" placeholder="Busque por cliente o servicio">` | Caja de búsqueda que filtra en tiempo real la tabla mientras escribo. |
| `<select id="filtroProfesional" class="filtro-select">` | Menú desplegable cargado dinámicamente con las especialistas del negocio para filtrar solo las citas de una en particular. |
| `<select id="filtroEstado" class="filtro-select">` | Filtro para aislar citas por estado (Confirmada, Pendiente, Cancelada, Completada). |
| `<input type="date" id="filtroFecha" class="filtro-select" value="{{ $hoy ?? date('Y-m-d') }}">` | Campo de fecha para revisar la agenda de cualquier día en específico. |

---

### 7. Ventana modal para agendar nueva cita

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div id="modalNuevaCita" class="modal-lash-overlay" ...>` | Fondo oscuro y contenedor de la ventana modal emergente. |
| `<button type="button" class="modal-lash-cerrar" id="cerrarNuevaCita">` | Botón con una X para cerrar la ventana. |
| `<form action="{{ route('citas.store') }}" method="POST" id="formNuevaCita"> @csrf` | Formulario protegido con token CSRF que envía los datos a la ruta de guardar cita en Laravel. |
| `<select name="id_cliente" id="id_cliente" required>` | Lista desplegable con todos los clientes registrados (`$listaClientes`), mostrando nombre y correo para identificarlos sin confusiones. |
| `<select name="id_servicio" id="id_servicio" required>` | Lista desplegable con todos los servicios activos (`$listaServicios`), indicando su precio formateado en pesos colombianos y su duración en minutos. |
| `<select name="id_trabajador" id="id_trabajador" required>` | Lista desplegable con las especialistas (`$listaTrabajadores`) y su especialidad técnica. |
| `<select name="estado" id="estado">` | Menú para fijar el estado inicial (por defecto Pendiente o Confirmada). |
| `<input type="date" name="fecha_cita" id="fecha_cita" min="{{ $hoy }}" required>` | Campo para elegir la fecha, con validación `min` para no permitir seleccionar fechas pasadas por error. |
| `<input type="time" name="hora_inicio" id="hora_inicio" required>` | Campo para especificar la hora de inicio de la cita. |
| `<textarea name="observaciones" id="observaciones" ...></textarea>` | Cuadro de texto para notas especiales (alergias de la clienta, diseño preferido, etc.). |
| `<button type="submit" class="modal-lash-btn-agendar"> Agendar cita </button>` | Botón final para guardar la cita en la base de datos. |

---

### 8. Librerías y carga de JavaScript modular

| Código | ¿Qué hace? |
| :--- | :--- |
| `<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>` | Cargo jQuery necesario para la librería de tablas DataTables. |
| `<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>` | Cargo el motor de DataTables para ordenar, paginar y buscar al instante en la tabla. |
| `@vite(['resources/js/modules/admin/citas.js'])` | Cargo mediante Vite el módulo JavaScript interactivo que controla el calendario, el modal y los filtros de esta vista. |
