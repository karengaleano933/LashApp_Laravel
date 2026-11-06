# Explicación de mi Vista de Barra Lateral y Encabezado de Cliente (`layouts/sidebarcliente.blade.php`)

Esta vista es el marco de navegación superior y lateral para las clientas en LashApp. Permite alternar la barra de navegación en celulares y computadores, muestra el saludo personalizado de bienvenida, incluye un buscador rápido ("Spotlight Search" con `Ctrl + K`) adaptado para encontrar servicios y tarifas, una campana de notificaciones con contador de citas pendientes, y el menú de cuenta de la clienta para acceder a su perfil, historial, citas o cerrar sesión.

---

### 1. Barra superior de cliente (`top-header`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<header class="top-header">` | Barra superior que acompaña a la clienta en todas las vistas de su portal. |
| `<button class="sidebar-toggle" id="sidebarToggle" title="Menú">` | Botón para abrir o cerrar el menú lateral en dispositivos móviles. |
| `<h1> ¡Hola, {{ auth()->user()?->name ?? 'Claire' }}! 👋 </h1>` | Saludo amigable con el nombre de la clienta. |
| `<p> Bienvenida a Oh La Lash by Lorena Ayala Studio </p>` | Mensaje de bienvenida al estudio de belleza. |

---

### 2. Buscador rápido para clientas (Spotlight Search)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="icon-btn-circle" id="btnQuickSearchToggle" onclick="abrirModalBusquedaRapida()">` | Botón circular con ícono de lupa para abrir la búsqueda instantánea con atajo `Ctrl + K`. |
| `<input type="text" id="quickSearchInput" placeholder="Buscar servicios, fechas, especialistas... (ESC para salir)">` | Campo de texto que filtra tratamientos en tiempo real. |
| `<a href="{{ route('cliente.catalogo') }}" class="quicksearch-item" data-text="...">` | Acceso directo a servicios como Extensiones Volumen Ruso o Cejas Henna mostrando su precio y duración. |
| `<a href="{{ route('cliente.agendar') }}" class="quicksearch-item"> ... <strong>Agendar Nueva Cita</strong> ... </a>` | Acceso rápido para saltar al calendario de reserva. |
| `<a href="{{ route('cliente.citas') }}" class="quicksearch-item"> ... <strong>Mis Citas</strong> ... </a>` | Acceso rápido para revisar citas agendadas. |

---

### 3. Notificaciones y menú de cuenta

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="icon-btn-circle" id="btnNotifToggle"> <i class="fa-solid fa-bell"></i> ... </button>` | Botón de campana con contador para ver avisos sobre citas confirmadas o recordatorios. |
| `<button type="button" class="icon-btn-circle" id="btnUserMenuToggle"> <i class="fa-solid fa-user"></i> </button>` | Botón circular de usuario que despliega el menú de perfil de la clienta. |
| `<div class="up-avatar-circle"> {{ strtoupper(substr(auth()->user()?->name, 0, 1)) }} </div>` | Círculo con la inicial del nombre de la clienta. |
| `<a href="{{ route('cliente.perfil') }}" class="up-item"> Mi Perfil </a>` | Enlace para gestionar sus datos y contraseña. |
| `<a href="{{ route('cliente.historial') }}" class="up-item"> Historial </a>` | Enlace para revisar sus pagos y servicios pasados. |
| `<button type="button" class="up-item up-logout-btn" onclick="..."> Cerrar Sesión </button>` | Botón para salir del portal de clienta de forma segura. |
| `@vite(['resources/js/modules/layout/sidebarcliente.js'])` | Conecta el script modular que gobierna el menú móvil y las notificaciones de la clienta. |
