<!-- ══════ TOP HEADER TRABAJADOR ══════ -->
<header class="top-header">
    <div class="dash-header-left">
        <button class="sidebar-toggle" id="sidebarToggle" title="Menú">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <h1>¡Hola, {{ auth()->user()?->name ?? 'Especialista' }}! 👋</h1>
            <p>{{ ucfirst(auth()->user()?->role?->nombre ?? 'Trabajador') }} — Oh La Lash Studio</p>
        </div>
    </div>

    <div class="dash-header-right">
        <!-- 1. BADGE DE FECHA -->
        <div class="date-badge">
            <i class="fa-regular fa-calendar-days"></i>
            {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
        </div>

        <!-- 2. BOTÓN DE BÚSQUEDA RÁPIDA -->
        <button type="button" class="icon-btn-circle" title="Buscar citas o servicios (Ctrl + K)" id="btnQuickSearchToggle" onclick="abrirModalBusquedaRapida()">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        <!-- 3. BOTÓN DE NOTIFICACIONES -->
        <div class="notif-wrapper" id="notifWrapperMain" data-notif-url="{{ route('notificaciones.index') }}" data-mark-read-url="{{ route('notificaciones.marcarLeidas') }}">
            <button type="button" class="icon-btn-circle" title="Notificaciones" id="btnNotifToggle">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-badge-count" id="notifBadgeCount" style="display: none;">0</span>
            </button>
            <div class="notif-dropdown" id="notifDropdownMenu">
                <div class="notif-dropdown-header">
                    <div class="notif-header-title">
                        <i class="fa-solid fa-bell"></i> Notificaciones
                    </div>
                    <button type="button" class="btn-marcar-leidas" id="btnMarcarTodasLeidas">
                        <i class="fa-solid fa-check-double"></i> Leídas
                    </button>
                </div>
                <div class="notif-dropdown-body" id="notifListContainer">
                    <div class="notif-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>
                </div>
            </div>
        </div>

        <!-- 4. BOTÓN MI CUENTA / PERFIL -->
        <div class="user-menu-wrapper" id="userMenuWrapperMain">
            <button type="button" class="icon-btn-circle" title="Mi Cuenta" id="btnUserMenuToggle">
                <i class="fa-solid fa-user"></i>
            </button>
            <div class="user-profile-dropdown" id="userProfileDropdown">
                <div class="user-profile-dropdown-header">
                    <div class="up-avatar-circle">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'T', 0, 1)) }}
                    </div>
                    <div class="up-user-info">
                        <strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>
                        <span>{{ auth()->user()?->email }}</span>
                        <small class="up-role-pill">{{ strtoupper(auth()->user()?->role?->nombre ?? 'TRABAJADOR') }}</small>
                    </div>
                </div>
                <div class="user-profile-dropdown-menu">
                    <a href="{{ route('trabajador.perfil') }}" class="up-item">
                        <i class="fa-solid fa-id-badge"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="{{ route('citas.index') }}" class="up-item">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Mis Citas</span>
                    </a>
                    <a href="{{ route('trabajador.horario') }}" class="up-item">
                        <i class="fa-solid fa-clock"></i>
                        <span>Mi Horario</span>
                    </a>
                    <a href="{{ route('servicios.index') }}" class="up-item">
                        <i class="fa-solid fa-spa"></i>
                        <span>Servicios</span>
                    </a>
                    <a href="{{ route('trabajador.resenas') }}" class="up-item">
                        <i class="fa-solid fa-star"></i>
                        <span>Reseñas</span>
                    </a>
                    <div class="up-divider"></div>
                    <button type="button" class="up-item up-logout-btn" onclick="document.getElementById('sidebarLogoutForm')?.submit()">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- ══════ MODAL DE BÚSQUEDA RÁPIDA ══════ -->
<div class="modal-quicksearch-overlay" id="modalQuickSearchOverlay" style="display:none;">
    <div class="modal-quicksearch-container">
        <div class="quicksearch-input-header">
            <i class="fa-solid fa-magnifying-glass quicksearch-icon"></i>
            <input type="text" id="quickSearchInput" placeholder="Buscar citas, clientes, servicios... (ESC para salir)" autocomplete="off">
            <button type="button" class="quicksearch-close-btn" onclick="cerrarModalBusquedaRapida()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="quicksearch-results-list" id="quickSearchResultsList">
            <div class="quicksearch-section-title">ACCESOS RÁPIDOS</div>
            <a href="{{ route('citas.index') }}" class="quicksearch-item" data-text="citas agendadas calendario clientes">
                <i class="fa-solid fa-calendar-check"></i>
                <div class="qs-item-info"><strong>Mis Citas</strong><span>Gestionar agenda y citas programadas</span></div>
                <span class="qs-badge">Citas</span>
            </a>
            <a href="{{ route('trabajador.horario') }}" class="quicksearch-item" data-text="horario disponibilidad turnos horas">
                <i class="fa-solid fa-clock"></i>
                <div class="qs-item-info"><strong>Mi Horario</strong><span>Configurar turnos y disponibilidad</span></div>
                <span class="qs-badge">Horario</span>
            </a>
            <a href="{{ route('servicios.index') }}" class="quicksearch-item" data-text="servicios catalogo tratamientos pestanas">
                <i class="fa-solid fa-spa"></i>
                <div class="qs-item-info"><strong>Servicios</strong><span>Catálogo de servicios disponibles</span></div>
                <span class="qs-badge">Servicios</span>
            </a>
            <a href="{{ route('trabajador.resenas') }}" class="quicksearch-item" data-text="resenas opiniones comentarios calificaciones">
                <i class="fa-solid fa-star"></i>
                <div class="qs-item-info"><strong>Reseñas</strong><span>Opiniones y calificaciones recibidas</span></div>
                <span class="qs-badge">Reseñas</span>
            </a>
            <a href="{{ route('trabajador.perfil') }}" class="quicksearch-item" data-text="perfil configuracion cuenta seguridad">
                <i class="fa-solid fa-user-gear"></i>
                <div class="qs-item-info"><strong>Mi Perfil</strong><span>Editar datos personales y contraseña</span></div>
                <span class="qs-badge">Perfil</span>
            </a>
        </div>
    </div>
</div>
<!-- ══════ JAVASCRIPT GLOBAL TRABAJADOR (VITE) ══════ -->
@vite(['resources/js/modules/layout/sidebartrabajador.js'])