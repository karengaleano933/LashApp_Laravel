# Explicación de mi Vista de Barra Lateral y Encabezado de Trabajador (`layouts/sidebar_trabajador.blade.php`)

Esta vista es el marco de trabajo superior y de navegación para las especialistas en LashApp. Contiene el botón móvil para desplegar el menú lateral, el saludo con el nombre de la trabajadora y la fecha en español, el buscador rápido global ("Spotlight Search" con `Ctrl + K`), la campana de notificaciones con botón para marcar como leídas, y el menú de perfil para acceder a Mi Perfil, Mis Citas, Mi Horario, Servicios, Reseñas y Cerrar Sesión.

---

### 1. Barra superior (`top-header`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<header class="top-header">` | Barra superior común que acompaña a la trabajadora en todas las secciones. |
| `<button class="sidebar-toggle" id="sidebarToggle" title="Menú">` | Botón para abrir o cerrar el menú lateral en dispositivos móviles. |
| `<h1> ¡Hola, {{ auth()->user()?->name ?? 'Especialista' }}! 👋 </h1>` | Saludo amigable con el nombre de la lashista. |
| `<div class="date-badge"> {{ now()->translatedFormat('l, d \d\e F \d\e Y') }} </div>` | Muestra la fecha de hoy en formato largo (ej. *Lunes, 26 de octubre de 2026*). |

---

### 2. Buscador rápido para trabajadoras (Spotlight Search)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="icon-btn-circle" id="btnQuickSearchToggle" onclick="abrirModalBusquedaRapida()">` | Botón circular con ícono de lupa que abre la búsqueda instantánea con atajo `Ctrl + K`. |
| `<input type="text" id="quickSearchInput" placeholder="Buscar citas, clientes, servicios... (ESC para salir)">` | Campo de texto que filtra accesos directos al instante. |
| `<a href="{{ route('citas.index') }}" class="quicksearch-item"> ... <strong>Mis Citas</strong> ... </a>` | Acceso directo a la agenda de citas. |
| `<a href="{{ route('trabajador.horario') }}" class="quicksearch-item"> ... <strong>Mi Horario</strong> ... </a>` | Acceso directo a la configuración de turnos. |
| `<a href="{{ route('servicios.index') }}" class="quicksearch-item"> ... <strong>Servicios</strong> ... </a>` | Acceso directo al catálogo de servicios. |
| `<a href="{{ route('trabajador.resenas') }}" class="quicksearch-item"> ... <strong>Reseñas</strong> ... </a>` | Acceso directo a consultar las opiniones de las clientas. |

---

### 3. Notificaciones y menú de perfil

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="notif-wrapper" id="notifWrapperMain" data-notif-url="..." data-mark-read-url="...">` | Contenedor de la campana de notificaciones con rutas AJAX para leer y marcar alertas. |
| `<button type="button" class="btn-marcar-leidas" id="btnMarcarTodasLeidas">` | Botón para limpiar las notificaciones y marcarlas como leídas. |
| `<button type="button" class="icon-btn-circle" id="btnUserMenuToggle"> <i class="fa-solid fa-user"></i> </button>` | Botón circular de usuario que despliega el menú de cuenta de la empleada. |
| `<div class="up-avatar-circle"> {{ strtoupper(substr(auth()->user()?->name, 0, 1)) }} </div>` | Círculo con la inicial del nombre de la trabajadora. |
| `<a href="{{ route('trabajador.perfil') }}" class="up-item"> Mi Perfil </a>` | Enlace para gestionar sus datos y clave. |
| `<button type="button" class="up-item up-logout-btn" onclick="..."> Cerrar Sesión </button>` | Botón para salir de la cuenta de trabajadora de forma segura. |
| `@vite(['resources/js/modules/layout/sidebartrabajador.js'])` | Conecta el script modular que gobierna el menú móvil y los eventos del encabezado. |
