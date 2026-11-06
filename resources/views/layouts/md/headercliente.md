uu# Explicación de mi Vista de Cabecera y Menú Lateral de Cliente (`layouts/headercliente.blade.php`)

Esta vista es la estructura base que abre todas las páginas del portal de la clienta en LashApp. Configura los metadatos y token CSRF, las fuentes tipográficas y estilos, y despliega el menú lateral izquierdo exclusivo para clientas con enlaces a Inicio, Catálogo, Agendar Cita, Mis Citas, Historial, Reseñas, Perfil, una tarjeta flotante de ayuda directa por WhatsApp y el botón de logout.

---

### 1. Metadatos, fuentes y estilos

| Código | ¿Qué hace? |
| :--- | :--- |
| `<meta name="csrf-token" content="{{ csrf_token() }}">` | Llave de seguridad CSRF para formularios y peticiones AJAX. |
| `<title>{{ $titulo ?? 'Mi Portal' }} \| LashApp Studio</title>` | Título dinámico en la pestaña del navegador. |
| `<link href="...DM+Sans...Playfair+Display..." rel="stylesheet">` | Tipografías elegantes para el portal de la clienta. |
| `<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>` | Librería de alertas y avisos emergentes. |
| `@vite(['resources/js/app.js', ...])` | Carga los archivos CSS y JS del cliente mediante Vite. |
| `<div class="app-layout">` | Contenedor principal de la interfaz cliente. |

---

### 2. Menú lateral de la clienta (`aside.sidebar`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="sidebar-logo"> <a href="{{ route('dashboard') }}"><img src="{{ asset('img/logo.png') }}"></a> </div>` | Logotipo oficial que lleva a la página principal. |
| `<a href="{{ route('dashboard') }}" class="menu-item {{ ... ? 'active' : '' }}">` | Enlace a Inicio que se resalta si estoy en el portal principal. |
| `<a href="{{ route('cliente.catalogo') }}" ...> <i class="fa-solid fa-bag-shopping"></i><span>Catálogo</span> </a>` | Enlace a la galería de servicios y tarifas. |
| `<a href="{{ route('cliente.agendar') }}" ...> <i class="fa-solid fa-calendar-plus"></i><span>Agendar Cita</span> </a>` | Enlace al flujo interactivo para reservar una cita. |
| `<a href="{{ route('cliente.citas') }}" ...> <i class="fa-solid fa-calendar-check"></i><span>Mis Citas</span> </a>` | Enlace para revisar próximas citas agendadas y estados. |
| `<a href="{{ route('cliente.historial') }}" ...> <i class="fa-solid fa-clock-rotate-left"></i><span>Historial</span> </a>` | Enlace a citas, servicios y pagos pasados. |
| `<a href="{{ route('cliente.resenas') }}" ...> <i class="fa-solid fa-star"></i><span>Reseñas</span> </a>` | Enlace para calificar y dejar opiniones de los servicios recibidos. |
| `<a href="{{ route('cliente.perfil') }}" ...> <i class="fa-solid fa-user"></i><span>Perfil</span> </a>` | Enlace a los datos personales y cambio de contraseña. |
| `<div class="sidebar-help-card"> ... <a href="https://wa.me/573147662316" ...> Contáctanos </a> </div>` | Tarjeta de soporte rápido que abre un chat directo de WhatsApp con el estudio ante dudas. |

---

### 3. Tarjeta de usuaria y cierre de sesión

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="user-avatar-initials"> ... </div>` | Muestra las iniciales del nombre y apellido de la clienta en un círculo morado. |
| `<strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>` | Nombre y apellido completo de la clienta conectada. |
| `<form method="POST" action="{{ route('logout') }}" id="sidebarLogoutForm"> @csrf` | Formulario protegido con método POST para salir del portal. |
| `<button type="submit" class="sidebar-logout-btn" title="Cerrar sesión">` | Botón con ícono de salida para cerrar sesión de forma segura. |
| `<div class="main-content-wrapper">` | Abre el contenedor derecho donde se mostrará la vista de cada página. |
