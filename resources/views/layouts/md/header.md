# Explicación de mi Vista de Cabecera y Menú Lateral (`layouts/header.blade.php`)

Esta vista es el esqueleto estructural que abre todas las páginas de administración en LashApp. Contiene las etiquetas `<head>` con el token de seguridad CSRF, la librería de alertas SweetAlert2, las hojas de estilo compiladas por Vite y el menú lateral (`sidebar`) completo con el logo del salón, enlaces organizados por secciones (Principal, Gestión, Configuración) que se marcan como activos según la página abierta, y la tarjeta inferior del perfil con botón de cierre de sesión.

---

### 1. Metadatos, seguridad y estilos globales

| Código | ¿Qué hace? |
| :--- | :--- |
| `<meta name="csrf-token" content="{{ csrf_token() }}">` | Llave de seguridad CSRF para autorizar peticiones AJAX al servidor Laravel. |
| `<meta name="app-url" content="{{ url('/') }}">` | Dirección base del sitio web para rutas dinámicas en JavaScript. |
| `<title>{{ $titulo ?? 'Panel' }} \| LashApp</title>` | Nombre dinámico de la página mostrado en la pestaña del navegador. |
| `<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>` | Librería de alertas visuales bonitas y confirmaciones modales. |
| `@vite(['resources/js/app.js', ...])` | Carga los archivos CSS principales y scripts globales mediante Vite. |
| `<div class="app-layout">` | Contenedor principal de pantalla completa que organiza el sidebar a la izquierda y el contenido a la derecha. |

---

### 2. Logotipo y navegación lateral (`aside.sidebar`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<aside class="sidebar" id="sidebar">` | Barra lateral izquierda que permanece fija mientras la usuaria navega. |
| `<img src="{{ asset('img/logo.png') }}" alt="LashApp">` | Imagen del logotipo oficial de LashApp en la parte superior del menú. |
| `@if (auth()->user()?->role_id === 1)` | Si la persona conectada es la Administradora, muestra el menú completo con todos los privilegios. |
| `<a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">` | Enlace a Inicio que se ilumina con fondo morado si estoy actualmente en el Dashboard. |
| `<a href="{{ route('citas.index') }}" ...> <i class="fa-solid fa-calendar-check"></i><span>Citas</span> </a>` | Enlace al módulo de citas y agenda. |
| `<a href="{{ route('usuarios.index') }}" ...> <i class="fa-solid fa-users"></i><span>Usuarios</span> </a>` | Enlace al módulo de gestión de usuarios y clientes. |
| `<a href="{{ route('servicios.index') }}" ...> <i class="fa-solid fa-spa"></i><span>Servicios</span> </a>` | Enlace al catálogo de tratamientos. |
| `<a href="{{ route('pagos.index') }}" ...> <i class="fa-solid fa-money-bill-wave"></i><span>Pagos</span> </a>` | Enlace al módulo de cobros y transacciones. |
| `<a href="{{ route('resenas.index') }}" ...> <i class="fa-solid fa-star"></i><span>Reseñas</span> </a>` | Enlace al módulo de calificaciones. |
| `<a href="{{ route('reportes.index') }}" ...> <i class="fa-solid fa-chart-column"></i><span>Reportes</span> </a>` | Enlace a las estadísticas financieras y gráficos. |
| `<a href="{{ route('configuracion.index') }}" ...> <i class="fa-solid fa-gear"></i><span>Configuración</span> </a>` | Enlace a los ajustes del salón y del sistema. |

---

### 3. Tarjeta de usuario inferior y cierre de sesión

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="sidebar-user"> ... </div>` | Tarjeta en el fondo del sidebar con la foto/inicial de la usuaria. |
| `<div class="user-avatar-initials"> {{ strtoupper(substr(auth()->user()?->name, 0, 1)) }} </div>` | Inicial del nombre en un círculo morado. |
| `<strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>` | Nombre y apellido del usuario conectado. |
| `<form method="POST" action="{{ route('logout') }}" id="sidebarLogoutForm"> @csrf` | Formulario protegido con método POST para salir del sistema al presionar el botón de logout. |
| `<button type="submit" class="sidebar-logout-btn" title="Cerrar sesión">` | Botón con ícono de salida (`fa-right-from-bracket`) para cerrar sesión. |
| `<div class="main-content-wrapper">` | Abre el contenedor derecho donde se inyectará la vista específica de cada página. |
