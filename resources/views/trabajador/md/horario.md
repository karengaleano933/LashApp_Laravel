# Explicación de mi Vista de Horario y Disponibilidad (`mi horario/horario_trabajador.blade.php`)

Esta vista permite a la trabajadora o especialista en LashApp gestionar sus turnos y franjas horarias de atención. Desde aquí define qué días y a qué horas puede atender clientas, consulta cuántos bloques tiene disponibles u ocupados con citas, filtra por pestañas, busca por fecha u hora, elimina turnos viejos y agrega nuevos bloques de trabajo con una ventana modal.

---

### 1. Inclusiones, estilos y preparación de datos

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header_trabajador', ['titulo' => 'Mi Horario'])` | Cargo la cabecera de la trabajadora con el título "Mi Horario". |
| `@include('layouts.sidebar_trabajador')` | Cargo el menú lateral de navegación de la empleada. |
| `@vite(['resources/css/horario_trabajador.css', 'resources/js/modules/trabajador/horario.js'])` | Conecto los estilos propios y el script de interactividad de horarios mediante Vite. |
| `<main class="horario-page-container" id="horarioTrabajadorContainer" ...>` | Contenedor principal que almacena mensajes de éxito o error enviados desde el servidor. |

---

### 2. Encabezado y tarjetas de métricas horarias

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="btn-crear-horario" id="btnAbrirModalHorario">` | Botón morado con ícono `+` para desplegar la ventana emergente de agregar nuevo bloque de tiempo. |
| `<div class="stat-card-horario"> ... <h4>Total Bloques</h4> <div class="stat-val">{{ $totalHorarios }}</div> ... </div>` | Tarjeta morada que muestra la cantidad total de turnos de trabajo registrados. |
| `<div class="stat-card-horario"> ... <h4>Disponibles</h4> <div class="stat-val">{{ $disponibles }}</div> ... </div>` | Tarjeta verde con chulito que cuenta los turnos libres listos para que una clienta reserve. |
| `<div class="stat-card-horario"> ... <h4>Ocupados / Con Cita</h4> <div class="stat-val">{{ $ocupados }}</div> ... </div>` | Tarjeta naranja que muestra las franjas horarias que ya tienen una cita asignada. |
| `<div class="stat-card-horario"> ... <h4>Próxima Fecha</h4> ... </div>` | Tarjeta azul con la fecha más próxima en que la trabajadora tiene programado laborar. |

---

### 3. Pestañas de filtro y buscador

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="horario-tab-btn active" data-filter="all">Todos</button>` | Pestaña para ver la lista completa de todos los bloques de horario. |
| `<button type="button" class="horario-tab-btn" data-filter="disponible">Disponibles</button>` | Pestaña para aislar y ver únicamente los turnos libres. |
| `<button type="button" class="horario-tab-btn" data-filter="ocupado">Ocupados</button>` | Pestaña para revisar solo las franjas que ya tienen citas agendadas. |
| `<input type="text" id="horarioSearchInput" placeholder="Filtrar por fecha u hora...">` | Buscador en vivo que filtra las filas de la tabla según la fecha o la hora escrita. |

---

### 4. Tabla de franjas horarias

| Código | ¿Qué hace? |
| :--- | :--- |
| `<table class="table-horarios-custom" id="tablaHorarios">` | Tabla principal con columnas para Fecha, Horario, Duración, Estado y Acciones. |
| `@forelse ($horarios as $h) ... @empty ... @endforelse` | Bucle que recorre cada turno de la especialista; si no hay horarios configurados, muestra una advertencia invitándola a crear su primer horario. |
| `<div style="font-weight: 700;">{{ ucfirst($fechaFormateada) }}</div>` | Muestra el día de la semana y la fecha en formato extendido en español (ej. *Lunes, 15 de octubre de 2026*). |
| `<span class="hora-badge"> {{ $horaIni }} &mdash; {{ $horaFin }} </span>` | Muestra el rango de atención en formato legible de 12 horas (ej. `9:00 am — 10:30 am`). |
| `<span style="font-weight: 600;"> {{ $duracionTxt }} </span>` | Calcula e indica cuánto dura el turno (ej. `1h 30m`). |
| `@if ($esOcupado) <span class="badge-ocupado"> ... Cita Asignada ... </span> @else <span class="badge-disponible"> ... Disponible </span> @endif` | Etiqueta naranja si el espacio ya fue tomado por una clienta o etiqueta verde si sigue disponible. |
| `<button type="button" class="btn-eliminar-horario" onclick="confirmarEliminarHorario(this)">` | Botón con basurero para eliminar el turno de disponibilidad tras pedir confirmación segura. |

---

### 5. Modal para agregar nuevo bloque de disponibilidad

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div id="modalNuevoHorario" class="modal-lash-overlay">` | Ventana emergente con fondo oscuro para crear un nuevo turno. |
| `<form action="{{ route('trabajador.horario.store') }}" method="POST"> @csrf` | Formulario protegido con token CSRF hacia la ruta de almacenamiento de horarios de trabajadoras. |
| `<input type="date" name="fecha" id="fecha" value="{{ $hoy }}" min="{{ $hoy }}" required>` | Campo para definir la fecha del turno, no permitiendo fechas del pasado. |
| `<input type="time" name="hora_inicio" id="hora_inicio" value="09:00" required>` | Campo para fijar la hora en que empieza la disponibilidad de la lashista. |
| `<input type="time" name="hora_fin" id="hora_fin" value="10:30" required>` | Campo para fijar la hora en que finaliza la disponibilidad. |
| `<select name="estado" id="estado"> ... </select>` | Permite fijar si el turno entra disponible para que las clientas agenden o reservado. |
| `<button type="submit" class="btn-primario"> Guardar Bloque </button>` | Botón final para confirmar y guardar el bloque de horario en la base de datos. |
| `@include('layouts.footer')` | Pie de página del sistema. |
