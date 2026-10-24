<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $titulo ?? 'Mi Portal' }} | LashApp Studio</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-url" content="{{ url('/') }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Estilos y Scripts de Cliente y Sistema -->
    @vite([
        'resources/js/app.js',
        'resources/css/sidebaradmin.css',
        'resources/css/dashboardadmin.css',
        'resources/css/dashboardcliente.css',
        'resources/css/footer.css'
    ])
    <link rel="stylesheet" href="{{ asset('css/sidebaradmin.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboardcliente.css') }}">
</head>
<body>

<div class="app-layout">

    <!-- ══════ SIDEBAR CLIENTE (OH LA LASH) ══════ -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-logo">
            <a href="{{ route('dashboard') }}">
                <img src="{{ asset('img/logo.png') }}" alt="Oh La Lash Studio">
            </a>
        </div>

        <div class="sidebar-divider"></div>

        <nav class="sidebar-menu">
            <p class="menu-title">MENÚ</p>

            <a href="{{ route('dashboard') }}" class="menu-item {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                <i class="fa-solid fa-house"></i><span>Inicio</span>
            </a>

            <a href="{{ route('cliente.catalogo') }}" class="menu-item {{ request()->routeIs('cliente.catalogo') ? 'active' : '' }}">
                <i class="fa-solid fa-bag-shopping"></i><span>Catálogo</span>
            </a>

            <a href="{{ route('cliente.agendar') }}" class="menu-item {{ request()->routeIs('cliente.agendar*') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-plus"></i><span>Agendar Cita</span>
            </a>

            <a href="{{ route('cliente.citas') }}" class="menu-item {{ request()->routeIs('cliente.citas') ? 'active' : '' }}">
                <i class="fa-solid fa-calendar-check"></i><span>Mis Citas</span>
            </a>

            <a href="{{ route('cliente.historial') }}" class="menu-item {{ request()->routeIs('cliente.historial*') ? 'active' : '' }}">
                <i class="fa-solid fa-clock-rotate-left"></i><span>Historial</span>
            </a>

            <a href="{{ route('cliente.resenas') }}" class="menu-item {{ request()->routeIs('cliente.resenas*') ? 'active' : '' }}">
                <i class="fa-solid fa-star"></i><span>Reseñas</span>
            </a>

            <a href="{{ route('cliente.perfil') }}" class="menu-item {{ request()->routeIs('cliente.perfil*') ? 'active' : '' }}">
                <i class="fa-solid fa-user"></i><span>Perfil</span>
            </a>

            <!-- Box ¿Tienes dudas? WhatsApp Soporte -->
            <div class="sidebar-help-card">
                <p class="help-title">¿Tienes dudas?</p>
                <p class="help-sub">Estamos para ayudarte</p>
                <a href="https://wa.me/573147662316" target="_blank" class="btn-help-contact">
                    <i class="fa-solid fa-comment-dots"></i> Contáctanos
                </a>
            </div>
        </nav>

        <!-- ══════ PERFIL Y CERRAR SESIÓN ══════ -->
        <div class="sidebar-user">
            <div class="user-avatar-initials">
                {{ strtoupper(substr(auth()->user()?->name ?? 'C', 0, 1)) }}{{ strtoupper(substr(auth()->user()?->apellido ?? 'F', 0, 1)) }}
            </div>
            <div class="user-info">
                <strong>{{ auth()->user()?->name ?? 'Claire' }} {{ auth()->user()?->apellido ?? 'Freiser' }}</strong>
                <span>{{ ucfirst(auth()->user()?->role?->nombre ?? 'Cliente') }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" id="sidebarLogoutForm" style="margin: 0;">
                @csrf
                <button type="submit" class="sidebar-logout-btn" title="Cerrar sesión" id="sidebarLogoutBtn">
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
            </form>
        </div>
    </aside>

    <!-- Contenido principal que se cerrará en dashboardcliente.blade.php / footer.blade.php -->
    <div class="main-content-wrapper">