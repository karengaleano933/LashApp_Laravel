# Explicación de mi Vista del Dashboard Admin (`dashboard.blade.php`)

Esta vista es la pantalla principal que veo al iniciar sesión como administradora en LashApp. Es mi centro de control general: me muestra en un vistazo los números clave del negocio, las próximas citas agendadas, un resumen operativo y accesos rápidos a los módulos más usados.

---

### 1. Inclusión de plantillas comunes y bienvenida

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header')` | Cargo el encabezado general del sistema (estilos CSS, fuentes de Google e íconos). |
| `@include('layouts.sidebaradmin')` | Cargo el menú lateral izquierdo y la barra superior con el saludo y notificaciones. |
| `<div class="page-content">` | Abro el contenedor principal que envuelve todo el contenido visual de la página. |
| `<div class="welcome-header"> <h1>Resumen general</h1> <p>Administra las citas, clientes y servicios de LashApp.</p> </div>` | Muestro el encabezado de bienvenida indicando que este es el resumen general del sistema. |

---

### 2. Tarjetas de métricas superiores (Indicadores clave)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="cards-grid">` | Creo la cuadrícula donde organizo las 4 tarjetas de colores superiores. |
| `<div class="metric-card purple"> ... {{ $totalUsuarios ?? 0 }} ... </div>` | Tarjeta morada con ícono de personas; muestra la cantidad total de usuarios registrados en el sistema. Si viene vacío, muestra 0 por seguridad. |
| `<div class="metric-card pink"> ... {{ $totalClientes ?? 0 }} ... </div>` | Tarjeta rosada con ícono de grupo; muestra el total de clientas registradas en la plataforma. |
| `<div class="metric-card gold"> ... {{ $totalTrabajadores ?? 0 }} ... </div>` | Tarjeta dorada con ícono de corbata; muestra cuántas trabajadoras o especialistas están disponibles. |
| `<div class="metric-card green"> ... {{ $totalCitas ?? 0 }} ... </div>` | Tarjeta verde con ícono de calendario; muestra la cantidad total de citas históricas guardadas en el sistema. |

---

### 3. Listado de próximas citas agendadas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="dashboard-grid">` | Abro la cuadrícula de dos columnas para mostrar las citas al lado del resumen del negocio. |
| `<a href="{{ route('citas.index') }}">Ver todas ...</a>` | Pongo un enlace directo para saltar a la pantalla completa de gestión de citas. |
| `@if (!empty($proximasCitas))` | Reviso si en el sistema hay citas agendadas próximas para mostrar. |
| `@foreach ($proximasCitas as $cita)` | Recorro una a una cada cita programada en la lista. |
| `$estadoClase = strtolower($cita['estado'] ?? 'pendiente');` | Convierto el estado de la cita a minúsculas para asignarle su color CSS correspondiente. |
| `$inicial = strtoupper(substr($cita['cliente'] ?? 'C', 0, 1));` | Extraigo la primera letra del nombre de la clienta en mayúscula para crear su avatar circular. |
| `<div class="cita-avatar">{{ $inicial }}</div>` | Dibujo el círculo con la inicial de la clienta. |
| `<strong>{{ $cita['cliente'] ?? 'Cliente' }}</strong>` | Muestro el nombre completo de la clienta. |
| `{{ $cita['servicio'] ?? 'Servicio' }} &bull; {{ $cita['profesional'] ?? 'Profesional' }}` | Muestro qué servicio reservó y el nombre de la lashista que la va a atender. |
| `{{ \Carbon\Carbon::parse($cita['fecha_cita'] ?? now())->format('d/m/Y') }}` | Convierto la fecha de la cita al formato legible día/mes/año (ej. `26/10/2026`). |
| `<span class="cita-estado-tag estado-{{ $estadoClase }}">{{ $cita['estado'] ?? 'Pendiente' }}</span>` | Muestro una pastilla de color con el estado actual (amarillo para pendiente, verde para confirmada, etc.). |
| `@else` | Si no hay citas agendadas próximas en el sistema. |
| `<div class="empty-state"> ... <h4>No hay próximas citas registradas</h4> ... </div>` | Muestro un dibujo con ícono de calendario avisando que no hay citas por ahora. |

---

### 4. Tarjeta lateral de resumen del negocio

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="summary-item"> ... <span>Citas pendientes</span><strong>{{ $citasPendientes ?? 0 }}</strong> </div>` | Fila con ícono morado que muestra cuántas citas están en espera de ser atendidas o confirmadas. |
| `<div class="summary-item"> ... <span>Servicios activos</span><strong>{{ $totalServicios ?? 0 }}</strong> </div>` | Fila con ícono rosado que muestra cuántos servicios están activos en el catálogo. |
| `<div class="summary-item"> ... <span>Reseñas registradas</span><strong>{{ $totalResenas ?? 0 }}</strong> </div>` | Fila con ícono dorado que muestra la cantidad de opiniones dejadas por los clientes. |

---

### 5. Accesos directos y cierre de estructura

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="quick-section"> ... </div>` | Bloque que contiene los botones grandes para entrar directo a los módulos más usados. |
| `<a href="{{ route('citas.index') }}" class="quick-card"> ... <span>Gestionar citas</span></a>` | Botón que me lleva directo a la agenda de citas. |
| `<a href="{{ route('usuarios.index') }}" class="quick-card"> ... <span>Gestionar Usuarios</span></a>` | Botón que me lleva al listado de usuarios y clientes. |
| `<a href="#" class="quick-card"> ... <span>Gestionar servicios</span></a>` | Botón de acceso directo a la administración de servicios. |
| `<a href="#" class="quick-card"> ... <span>Gestionar pagos</span></a>` | Botón de acceso directo al control de pagos. |
| `@include('layouts.footer')` | Cargo el pie de página con los scripts y el cierre de las etiquetas HTML del documento. |
