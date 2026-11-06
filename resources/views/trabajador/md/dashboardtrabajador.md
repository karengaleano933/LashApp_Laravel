# Explicación de mi Vista del Panel de Trabajador (`dashboardtrabajador.blade.php`)

Esta vista es la pantalla principal que ve la especialista o lashista al iniciar sesión en LashApp. Es su centro de trabajo diario: le muestra cuántas citas tiene programadas para el día de hoy, el total de clientas que ha atendido, los servicios completados, su calificación promedio en estrellas, una tabla con sus próximas citas asignadas, los servicios más pedidos del mes con barras de porcentaje y accesos rápidos a sus herramientas de trabajo.

---

### 1. Inclusión de componentes y preparación de datos

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header_trabajador', ['titulo' => 'Mi Panel'])` | Cargo la cabecera del portal de trabajadoras con el título "Mi Panel". |
| `@include('layouts.sidebar_trabajador')` | Cargo el menú lateral adaptado con las opciones de la empleada. |
| `if (!isset($totalCitas)) { ... }` | Bloque PHP de seguridad que consulta en la base de datos si el controlador no envió datos: averigua el ID de la trabajadora conectada, cuenta sus citas de hoy, pendientes y completadas, y calcula su promedio de estrellas. |
| `<div class="page-content"> ... <div class="welcome-header"> ... </div>` | Abro el contenedor principal y muestro el saludo de bienvenida a la especialista. |

---

### 2. Tarjetas de métricas de la especialista

| Código | ¿Qué hace? |
| :--- | :--- |
| `<a href="{{ route('citas.index') }}" class="card-design-sparkle"> ... {{ $citasHoy ?? 0 }} ... </a>` | Tarjeta con ícono de calendario que muestra cuántas citas tiene agendadas la especialista exclusivamente para hoy. |
| `<div class="card-sparkle-title">Clientes Atendidos</div> ... {{ $totalClientes ?? 0 }} ...` | Tarjeta con ícono de personas que cuenta cuántas clientas distintas ha atendido. |
| `<div class="card-sparkle-title">Servicios Realizados</div> ... {{ $citasCompletadas ?? 0 }} ...` | Tarjeta con ícono de tijeras que muestra el total de citas que ha finalizado con éxito. |
| `<div class="card-sparkle-title">Calificación</div> ... {{ number_format($calificacionPromedio ?? 4.9, 1) }} ...` | Tarjeta dorada con ícono de estrella que muestra su puntaje promedio de valoración (ej. `4.9`). |

---

### 3. Tabla de próximas citas asignadas

| Código | ¿Qué hace? |
| :--- | :--- |
| `@if (!empty($proximasCitas) && count($proximasCitas) > 0)` | Reviso si la especialista tiene citas próximas agendadas en su horario. |
| `@foreach ($proximasCitas as $cita) ... @endforeach` | Recorro cada una de sus citas programadas. |
| `<strong>{{ $cita['cliente'] ?? 'Cliente LashApp' }}</strong>` | Muestro el nombre de la clienta que asistirá. |
| `<span style="color:#7c1db8; font-weight:600;"> {{ $cita['servicio'] }} </span>` | Muestro el tratamiento en color morado (ej. Volumen Ruso o Lifting). |
| `{{ \Carbon\Carbon::parse($cita['hora_inicio'])->format('g:i a') }}` | Muestro la hora de inicio en formato am/pm de 12 horas. |
| `{{ \Carbon\Carbon::parse($cita['fecha_cita'])->format('d/m/Y') }}` | Muestro la fecha de la cita en formato día/mes/año. |
| `<span class="badge-pill-status {{ $badgeClass }}"> {{ ucfirst($cita['estado']) }} </span>` | Pastilla de color con el estado actual (amarillo para pendiente, verde para confirmada o completada, rojo para cancelada). |
| `@else ... <p>No hay próximas citas registradas.</p> ... @endif` | Si la agenda está libre, muestro un mensaje con ícono avisando que no hay citas próximas por el momento. |

---

### 4. Tarjeta lateral de servicios populares

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="popular-services-list"> ... </div>` | Lista con los tratamientos más solicitados por las clientas durante el mes. |
| `<div class="service-title-text">{{ $serv['nombre'] }}</div>` | Nombre del servicio destacado (ej. Pestañas Volumen Ruso, Lifting, Cejas). |
| `<div class="progress-bar-fill" style="width: {{ $serv['porcentaje'] }}%;"></div>` | Barra de progreso que se llena en color morado proporcionalmente a la demanda del servicio. |
| `<div class="service-percent-num">{{ $serv['porcentaje'] }} %</div>` | Porcentaje numérico exacto de popularidad del tratamiento. |

---

### 5. Accesos rápidos y pie de página

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="quick-section"> ... </div>` | Botones de acceso directo a las tareas frecuentes de la trabajadora. |
| `<a href="{{ route('citas.index') }}" class="quick-card"> ... <span>Mis citas</span></a>` | Acceso directo a su agenda y calendario. |
| `<a href="{{ route('servicios.index') }}" class="quick-card"> ... <span>Servicios</span></a>` | Acceso directo al catálogo de servicios que ofrece. |
| `<a href="{{ route('resenas.index') }}" class="quick-card"> ... <span>Reseñas</span></a>` | Acceso directo a ver las valoraciones y comentarios que le han dejado sus clientas. |
| `<a href="{{ route('profile.edit') }}" class="quick-card"> ... <span>Mi perfil</span></a>` | Acceso directo a editar su perfil y foto. |
| `@include('layouts.footer')` | Pie de página común del sistema. |
