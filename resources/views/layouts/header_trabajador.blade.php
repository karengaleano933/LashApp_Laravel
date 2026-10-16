<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Mi Panel' }} | LashApp Studio</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Estilos de Trabajador y Sistema -->
    @vite([
        'resources/css/sidebaradmin.css',
        'resources/css/dashboardadmin.css',
        'resources/css/dashboardtrabajador.css',
        'resources/css/footer.css'
    ])
</head>
<body>

<div class="app-layout">

    <!-- ══════ SIDEBAR TRABAJADOR ══════ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('img/logo.png') }}" alt="LashApp Studio">
            </a>
        </div>

        <div class="sidebar-divider"></div>

        <nav class="sidebar-menu">
            <!-- ── MENÚ TRABAJADOR ── -->
            <p class="menu-title">PRINCIPAL</p>
            <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard*') || request()->routeIs('trabajador.dashboard*') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i><span>Inicio</span>
            </a>
            <a href="{{ route('citas.index') }}" class="menu-item {{ request()->routeIs('citas.*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i><span>Citas</span>
            </a>
            <a href="{{ route('servicios.index') }}" class="menu-item {{ request()->routeIs('servicios.*') ? 'active' : '' }}">
                <i class="fa-solid fa-spa"></i><span>Servicios</span>
            </a>

            <p class="menu-title">GESTIÓN</p>
            <a href="{{ route('trabajador.horario') }}" class="menu-item {{ request()->routeIs('trabajador.horario*') ? 'active' : '' }}">
                <i class="fa-solid fa-clock"></i><span>Mi horario</span>
            </a>
            <a href="{{ route('trabajador.resenas') }}" class="menu-item {{ request()->routeIs('*resenas*') ? 'active' : '' }}">
                <i class="fa-solid fa-star"></i><span>Reseñas</span>
            </a>

            <p class="menu-title">CUENTA</p>
            <a href="{{ route('trabajador.perfil') }}" class="menu-item {{ request()->routeIs('trabajador.perfil*') || request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i><span>Mi perfil</span>
            </a>
        </nav>

        <!-- ══════ PERFIL Y CERRAR SESIÓN ══════ -->
        <div class="sidebar-user">
            <div class="user-avatar-initials">
                {{ strtoupper(substr(auth()->user()?->name ?? 'T', 0, 1)) }}
            </div>
            <div class="user-info">
                <strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>
                <span>{{ ucfirst(auth()->user()?->role?->nombre ?? 'Trabajador') }}</span>
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