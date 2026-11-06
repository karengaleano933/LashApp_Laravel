# Explicación de mi Vista de Barra Lateral y Encabezado Admin (`layouts/sidebaradmin.blade.php`)

Esta vista es el marco de control superior y lateral de la administradora en LashApp. Permite abrir y colapsar el menú lateral, muestra el saludo personalizado con fecha actual en español, incluye el buscador rápido global ("Spotlight Search" con `Ctrl + K`), la campana de notificaciones en tiempo real con dropdown de marcar leídas, y el menú desplegable de perfil con accesos directos y cierre de sesión seguro.

---

### 1. Barra superior (`top-header`) y saludo

| Código | ¿Qué hace? |
| :--- | :--- |
| `<header class="top-header">` | Barra horizontal superior fija que acompaña a la administradora en todas las páginas. |
| `<button class="sidebar-toggle" id="sidebarToggle" title="Menú">` | Botón con tres rayitas (`fa-bars`) para abrir o esconder el menú lateral en dispositivos móviles y computadores. |
| `<h1> ¡Hola, {{ auth()->user()?->name ?? 'Usuario' }}! 👋 </h1>` | Saludo personalizado que extrae el primer nombre de la administradora conectada. |
| `<p> {{ auth()->user()?->role?->nombre }} — Oh La Lash Studio </p>` | Muestra el rol en el sistema y el nombre del estudio. |
| `<div class="date-badge"> {{ now()->translatedFormat('l, d \d\e F \d\e Y') }} </div>` | Muestra la fecha de hoy en formato largo y en español (ej. *Lunes, 26 de octubre de 2026*). |

---

### 2. Botón de búsqueda rápida (Spotlight Search)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="icon-btn-circle" id="btnQuickSearchToggle" onclick="abrirModalBusquedaRapida()">` | Botón circular con ícono de lupa que abre la ventana flotante de búsqueda global rápida. |
| `<div class="modal-quicksearch-overlay" id="modalQuickSearchOverlay">` | Fondo oscuro del buscador global. |
| `<input type="text" id="quickSearchInput" placeholder="Buscar citas, clientes, módulos... (ESC para salir)">` | Campo de búsqueda que filtra al instante los accesos directos a cualquier módulo de la aplicación. |
| `<a href="{{ route('citas.index') }}" class="quicksearch-item" data-text="citas agenda calendario turnos">` | Elemento de búsqueda rápida que me lleva directo a Citas si escribo cualquiera de esas palabras clave. |
| `<div class="quicksearch-footer"> ... </div>` | Pie informativo que enseña los atajos de teclado (flechas ↑ ↓, Enter para entrar y Escape para cerrar). |

---

### 3. Notificaciones con menú desplegable

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="notif-wrapper" id="notifWrapperMain" data-notif-url="..." data-mark-read-url="...">` | Contenedor de la campana de notificaciones con las rutas AJAX para consultar y marcar alertas como leídas. |
| `<span class="notif-badge-count" id="notifBadgeCount">0</span>` | Burbuja roja que muestra el número de notificaciones nuevas pendientes de revisar. |
| `<button type="button" class="btn-marcar-leidas" id="btnMarcarTodasLeidas">` | Botón para marcar todas las alertas como leídas con un solo clic. |
| `<div class="notif-dropdown-body" id="notifListContainer">` | Contenedor donde JavaScript inserta en vivo las alertas de nuevas citas, cancelaciones o pagos. |

---

### 4. Menú de perfil de usuario y cierre de sesión

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="icon-btn-circle" id="btnUserMenuToggle"> <i class="fa-solid fa-user"></i> </button>` | Botón circular con ícono de usuario que despliega el menú de cuenta. |
| `<div class="up-avatar-circle"> {{ strtoupper(substr(auth()->user()?->name, 0, 1)) }} </div>` | Círculo con la inicial del nombre de la administradora. |
| `<strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>` | Nombre y apellido completos del perfil. |
| `<a href="{{ route('profile.edit') }}" class="up-item"> Mi Perfil y Contraseña </a>` | Enlace directo para editar la información personal. |
| `<a href="{{ route('configuracion.index') }}" class="up-item"> Configuración del Sistema </a>` | Enlace para entrar a los ajustes del negocio. |
| `<button type="button" class="up-item up-logout-btn" onclick="..."> Cerrar Sesión </button>` | Botón rojo para salir del sistema enviando el formulario de logout de Laravel de forma segura. |
| `@vite(['resources/js/modules/layout/sidebaradmin.js'])` | Carga el módulo JavaScript que maneja el menú móvil, la búsqueda spotlight y las notificaciones. |
