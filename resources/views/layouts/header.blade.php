<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    <title>{{ $titulo ?? 'Panel' }} | LashApp</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Estilos y Scripts Generales (Vite) -->
    @vite([
        'resources/js/app.js',
        'resources/css/sidebaradmin.css',
        'resources/css/dashboardadmin.css',
        'resources/css/footer.css',
        'resources/css/citas.css',
        'resources/css/usuarios.css'
    ])
</head>
<body>

<div class="app-layout">

    <!-- ══════ SIDEBAR ══════ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <img src="{{ asset('img/logo.png') }}" alt="LashApp">
        </div>

        <div class="sidebar-divider"></div>

        <nav class="sidebar-menu">
            @if (auth()->user()?->role_id === 1)
                <!-- ── MENÚ ADMIN ── -->
                <p class="menu-title">PRINCIPAL</p>
                <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i><span>Inicio</span>
                </a>
                <a href="{{ route('citas.index') }}" class="menu-item {{ request()->routeIs('citas.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i><span>Citas</span>
                </a>
                <a href="{{ route('usuarios.index') }}" class="menu-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i><span>Usuarios</span>
                </a>

                <p class="menu-title">GESTIÓN</p>
                <a href="{{ route('servicios.index') }}" class="menu-item {{ request()->routeIs('servicios.*') ? 'active' : '' }}"><i class="fa-solid fa-spa"></i><span>Servicios</span></a>
                <a href="{{ route('pagos.index') }}" class="menu-item {{ request()->routeIs('pagos.*') ? 'active' : '' }}"><i class="fa-solid fa-money-bill-wave"></i><span>Pagos</span></a>
                <a href="{{ route('resenas.index') }}" class="menu-item {{ request()->routeIs('resenas.*') ? 'active' : '' }}"><i class="fa-solid fa-star"></i><span>Reseñas</span></a>
                <a href="{{ route('reportes.index') }}" class="menu-item {{ request()->routeIs('reportes.*') ? 'active' : '' }}"><i class="fa-solid fa-chart-column"></i><span>Reportes</span></a>

                <p class="menu-title">CONFIGURACIÓN</p>
                <a href="{{ route('configuracion.index') }}" class="menu-item {{ request()->routeIs('configuracion.*') ? 'active' : '' }}"><i class="fa-solid fa-gear"></i><span>Configuración</span></a>
            @elseif (auth()->user()?->role_id === 2)
                <!-- ── MENÚ TRABAJADOR ── -->
                <p class="menu-title">PRINCIPAL</p>
                <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house"></i><span>Inicio</span>
                </a>
                <a href="{{ route('citas.index') }}" class="menu-item {{ request()->routeIs('citas.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i><span>Citas</span>
                </a>

                <p class="menu-title">GESTIÓN</p>
                <a href="{{ route('servicios.index') }}" class="menu-item {{ request()->routeIs('servicios.*') ? 'active' : '' }}"><i class="fa-solid fa-spa"></i><span>Servicios</span></a>
                <a href="{{ route('resenas.index') }}" class="menu-item {{ request()->routeIs('resenas.*') ? 'active' : '' }}"><i class="fa-solid fa-star"></i><span>Reseñas</span></a>
            @endif
        </nav>

        <!-- ══════ PERFIL Y CERRAR SESIÓN ══════ -->
        <div class="sidebar-user">
            <div class="user-avatar-initials">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div class="user-info">
                <strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>
                <span>{{ ucfirst(auth()->user()?->role?->nombre ?? 'Usuario') }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" id="sidebarLogoutForm" style="margin: 0;">
                @csrf
                <button type="submit" class="sidebar-logout-btn" title="Cerrar sesión" id="sidebarLogoutBtn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Contenido principal que se cerrará en el footer -->
    <div class="main-content-wrapper">
