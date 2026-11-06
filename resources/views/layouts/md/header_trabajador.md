# Explicación de mi Vista de Cabecera y Menú Lateral de Trabajador (`layouts/header_trabajador.blade.php`)

Esta vista es el armazón que abre todas las vistas del portal de la especialista o trabajadora en LashApp. Configura las fuentes de Google, los íconos de FontAwesome, la librería SweetAlert2 y la barra de navegación lateral fija con los enlaces permitidos para la trabajadora (Inicio, Citas, Servicios, Mi Horario, Reseñas y Mi Perfil), junto con su tarjeta de usuario y botón de logout.

---

### 1. Cabecera HTML y recursos

| Código | ¿Qué hace? |
| :--- | :--- |
| `<title>{{ $titulo ?? 'Mi Panel' }} \| LashApp Studio</title>` | Título dinámico en la pestaña del navegador. |
| `<link href="...DM+Sans...Playfair+Display..." rel="stylesheet">` | Fuentes tipográficas elegantes para el portal de la empleada. |
| `<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>` | Librería de alertas emergentes para confirmaciones. |
| `@vite(['resources/css/sidebaradmin.css', ...])` | Carga las hojas de estilo del panel y de la trabajadora mediante Vite. |
| `<div class="app-layout">` | Contenedor principal que envuelve el menú lateral y la zona de contenido. |

---

### 2. Menú lateral de la trabajadora (`aside.sidebar`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="sidebar-logo"> <a href="{{ route('dashboard') }}"><img src="{{ asset('img/logo.png') }}"></a> </div>` | Logotipo oficial que actúa como enlace directo al inicio. |
| `<a href="{{ route('dashboard') }}" class="menu-item {{ ... ? 'active' : '' }}">` | Enlace a Inicio que se resalta en morado si estoy en el panel de trabajadora. |
| `<a href="{{ route('citas.index') }}" ...> <i class="fa-solid fa-calendar-check"></i><span>Citas</span> </a>` | Enlace a la agenda de citas asignadas. |
| `<a href="{{ route('servicios.index') }}" ...> <i class="fa-solid fa-spa"></i><span>Servicios</span> </a>` | Enlace al catálogo de servicios. |
| `<a href="{{ route('trabajador.horario') }}" ...> <i class="fa-solid fa-clock"></i><span>Mi horario</span> </a>` | Enlace exclusivo para gestionar turnos y horas disponibles. |
| `<a href="{{ route('trabajador.resenas') }}" ...> <i class="fa-solid fa-star"></i><span>Reseñas</span> </a>` | Enlace para revisar testimonios de clientas. |
| `<a href="{{ route('trabajador.perfil') }}" ...> <i class="fa-solid fa-user"></i><span>Mi perfil</span> </a>` | Enlace para modificar datos de la especialista y contraseña. |

---

### 3. Tarjeta de usuaria y cierre de sesión

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="user-avatar-initials"> {{ strtoupper(substr(auth()->user()?->name, 0, 1)) }} </div>` | Círculo con la inicial de la trabajadora. |
| `<strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>` | Nombre completo de la trabajadora. |
| `<form method="POST" action="{{ route('logout') }}" id="sidebarLogoutForm"> @csrf` | Formulario protegido con `@csrf` para enviar la petición de cierre de sesión al servidor. |
| `<button type="submit" class="sidebar-logout-btn" title="Cerrar sesión">` | Botón con ícono de puerta de salida para desconectarse. |
| `<div class="main-content-wrapper">` | Abre el contenedor derecho donde se renderiza la vista de cada página. |
