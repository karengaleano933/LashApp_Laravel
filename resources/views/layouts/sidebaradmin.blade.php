<!-- ══════ TOP HEADER ══════ -->
<header class="top-header">
    <div class="dash-header-left">
        <button class="sidebar-toggle" id="sidebarToggle" title="Menú">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <h1>¡Hola, {{ auth()->user()?->name ?? 'Usuario' }}! 👋</h1>
            <p>{{ auth()->user()?->role?->nombre ?? 'Panel de Control' }} — Oh La Lash Studio</p>
        </div>
    </div>

    <div class="dash-header-right">
        @if (auth()->user()?->role_id === 1)
            <div class="date-badge">
                <i class="fa-regular fa-calendar-days"></i>
                {{ now()->translatedFormat('l, d \d\e F \d\e Y') }}
            </div>

            <!-- 1. BOTÓN DE BÚSQUEDA RÁPIDA -->
            <button type="button" class="icon-btn-circle" title="Buscar en el sistema (Ctrl + K)" id="btnQuickSearchToggle" onclick="abrirModalBusquedaRapida()">
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>

            <!-- 2. BOTÓN DE NOTIFICACIONES CON DROPDOWN -->
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

            <!-- 3. BOTÓN DE PERFIL DE USUARIO CON DROPDOWN -->
            <div class="user-menu-wrapper" id="userMenuWrapperMain">
                <button type="button" class="icon-btn-circle" title="Mi Cuenta" id="btnUserMenuToggle">
                    <i class="fa-solid fa-user"></i>
                </button>
                <div class="user-profile-dropdown" id="userProfileDropdown">
                    <div class="user-profile-dropdown-header">
                        <div class="up-avatar-circle">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="up-user-info">
                            <strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>
                            <span>{{ auth()->user()?->email }}</span>
                            <small class="up-role-pill">{{ ucfirst(auth()->user()?->role?->nombre ?? 'Administrador') }}</small>
                        </div>
                    </div>
                    <div class="user-profile-dropdown-menu">
                        <a href="{{ route('profile.edit') }}" class="up-item">
                            <i class="fa-solid fa-id-badge"></i>
                            <span>Mi Perfil y Contraseña</span>
                        </a>
                        <a href="{{ route('configuracion.index') }}" class="up-item">
                            <i class="fa-solid fa-gear"></i>
                            <span>Configuración del Sistema</span>
                        </a>
                        <a href="{{ route('citas.index') }}" class="up-item">
                            <i class="fa-regular fa-calendar-check"></i>
                            <span>Agenda de Citas</span>
                        </a>
                        <a href="{{ route('pagos.index') }}" class="up-item">
                            <i class="fa-solid fa-wallet"></i>
                            <span>Gestión de Pagos</span>
                        </a>
                        <div class="up-divider"></div>
                        <button type="button" class="up-item up-logout-btn" onclick="document.getElementById('sidebarLogoutForm')?.submit()">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span>Cerrar Sesión</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</header>

<!-- ══════ MODAL DE BÚSQUEDA RÁPIDA (SPOTLIGHT SEARCH) ══════ -->
<div class="modal-quicksearch-overlay" id="modalQuickSearchOverlay" style="display:none;">
    <div class="modal-quicksearch-container">
        <div class="quicksearch-input-header">
            <i class="fa-solid fa-magnifying-glass quicksearch-icon"></i>
            <input type="text" id="quickSearchInput" placeholder="Buscar citas, clientes, módulos, servicios... (ESC para salir)" autocomplete="off">
            <button type="button" class="quicksearch-close-btn" onclick="cerrarModalBusquedaRapida()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="quicksearch-results-list" id="quickSearchResultsList">
            <div class="quicksearch-section-title">MÓDULOS DEL SISTEMA</div>
            <a href="{{ route('dashboard') }}" class="quicksearch-item" data-text="inicio dashboard métricas">
                <i class="fa-solid fa-house"></i>
                <div class="qs-item-info"><strong>Inicio / Dashboard</strong><span>Vista principal y métricas globales</span></div>
                <span class="qs-badge">Módulo</span>
            </a>
            <a href="{{ route('citas.index') }}" class="quicksearch-item" data-text="citas agenda calendario turnos">
                <i class="fa-solid fa-calendar-check"></i>
                <div class="qs-item-info"><strong>Gestión de Citas</strong><span>Agenda, clientes y programación</span></div>
                <span class="qs-badge">Módulo</span>
            </a>
            <a href="{{ route('usuarios.index') }}" class="quicksearch-item" data-text="usuarios clientes trabajadores">
                <i class="fa-solid fa-users"></i>
                <div class="qs-item-info"><strong>Usuarios</strong><span>Lista de clientes y personal</span></div>
                <span class="qs-badge">Módulo</span>
            </a>
            <a href="{{ route('servicios.index') }}" class="quicksearch-item" data-text="servicios pestañas cejas precios lifting">
                <i class="fa-solid fa-spa"></i>
                <div class="qs-item-info"><strong>Catálogo de Servicios</strong><span>Tarifas, tiempos y categorías</span></div>
                <span class="qs-badge">Módulo</span>
            </a>
            <a href="{{ route('pagos.index') }}" class="quicksearch-item" data-text="pagos dinero comprobantes nequi efectivo">
                <i class="fa-solid fa-money-bill-wave"></i>
                <div class="qs-item-info"><strong>Gestión de Pagos</strong><span>Transacciones y comprobantes</span></div>
                <span class="qs-badge">Módulo</span>
            </a>
            <a href="{{ route('resenas.index') }}" class="quicksearch-item" data-text="reseñas testimonios estrellas valoraciones">
                <i class="fa-solid fa-star"></i>
                <div class="qs-item-info"><strong>Reseñas y Opiniones</strong><span>Satisfacción de clientes</span></div>
                <span class="qs-badge">Módulo</span>
            </a>
            <a href="{{ route('reportes.index') }}" class="quicksearch-item" data-text="reportes gráficos rendimiento balance">
                <i class="fa-solid fa-chart-column"></i>
                <div class="qs-item-info"><strong>Reportes y Rendimiento</strong><span>Estadísticas financieras y gráficos</span></div>
                <span class="qs-badge">Módulo</span>
            </a>
            <a href="{{ route('configuracion.index') }}" class="quicksearch-item" data-text="configuracion estudio logo moneda horario">
                <i class="fa-solid fa-gear"></i>
                <div class="qs-item-info"><strong>Configuración del Sistema</strong><span>Datos del estudio, logo y políticas</span></div>
                <span class="qs-badge">Módulo</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="quicksearch-item" data-text="perfil contraseña correo usuario">
                <i class="fa-solid fa-id-badge"></i>
                <div class="qs-item-info"><strong>Mi Perfil</strong><span>Modificar clave o datos personales</span></div>
                <span class="qs-badge">Cuenta</span>
            </a>
        </div>
        <div class="quicksearch-footer">
            <span>Usa <strong>↑</strong> <strong>↓</strong> para navegar, <strong>ENTER</strong> para seleccionar, <strong>ESC</strong> para cerrar</span>
        </div>
    </div>
</div>

<!-- ══════ ESTILOS ESPECÍFICOS DE LOS BOTONES Y MODALES SUPERIORES ══════ -->
<style>
/* ── Dropdown de Perfil ── */
.user-menu-wrapper {
    position: relative;
    display: inline-block;
}

.user-profile-dropdown {
    position: absolute;
    top: 48px;
    right: 0;
    width: 290px;
    background: #ffffff;
    border-radius: 18px;
    box-shadow: 0 14px 40px rgba(53, 20, 78, 0.2);
    border: 1px solid #ebdcf5;
    z-index: 1050;
    display: none;
    overflow: hidden;
    animation: fadeInDropdown 0.2s ease-out forwards;
}

.user-profile-dropdown.show {
    display: block;
}

@keyframes fadeInDropdown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}

.user-profile-dropdown-header {
    background: linear-gradient(135deg, #6c129e 0%, #4a0c6d 100%);
    color: #ffffff;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.up-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #ffffff;
    color: #7c1db8;
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    flex-shrink: 0;
}

.up-user-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
}

.up-user-info strong {
    font-size: 14px;
    font-weight: 700;
    color: #ffffff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.up-user-info span {
    font-size: 11.5px;
    color: rgba(255,255,255,0.8);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.up-role-pill {
    display: inline-block;
    align-self: flex-start;
    margin-top: 4px;
    padding: 2px 8px;
    border-radius: 10px;
    background: rgba(255,255,255,0.2);
    font-size: 9.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.user-profile-dropdown-menu {
    padding: 8px;
    display: flex;
    flex-direction: column;
}

.up-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 12px;
    color: #35144e;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    font-family: inherit;
    transition: background-color .15s, color .15s;
}

.up-item i {
    width: 20px;
    text-align: center;
    color: #7c1db8;
    font-size: 14px;
}

.up-item:hover {
    background: #f7effd;
    color: #7c1db8;
}

.up-divider {
    height: 1px;
    background: #f1e4f8;
    margin: 6px 4px;
}

.up-logout-btn {
    color: #dc2626 !important;
}

.up-logout-btn i {
    color: #dc2626 !important;
}

.up-logout-btn:hover {
    background: #fef2f2 !important;
}

/* ── Modal Búsqueda Rápida ── */
.modal-quicksearch-overlay {
    position: fixed;
    inset: 0;
    background: rgba(30, 8, 48, 0.6);
    backdrop-filter: blur(4px);
    z-index: 2000;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 12vh;
    animation: fadeInOverlay 0.2s ease-out;
}

@keyframes fadeInOverlay {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-quicksearch-container {
    width: 580px;
    max-width: 92vw;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 50px rgba(50, 10, 75, 0.35);
    border: 1px solid #e5d2f6;
    overflow: hidden;
    animation: slideDownModal 0.22s ease-out;
}

@keyframes slideDownModal {
    from { transform: translateY(-16px) scale(0.98); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

.quicksearch-input-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-bottom: 1.5px solid #f0e2f9;
}

.quicksearch-icon {
    font-size: 18px;
    color: #7c1db8;
}

.quicksearch-input-header input {
    flex: 1;
    border: none;
    outline: none;
    font-size: 15px;
    color: #2a0d3e;
    font-family: inherit;
    background: transparent;
}

.quicksearch-close-btn {
    background: transparent;
    border: none;
    font-size: 16px;
    color: #9f8bb0;
    cursor: pointer;
    padding: 4px;
}

.quicksearch-close-btn:hover {
    color: #7c1db8;
}

.quicksearch-results-list {
    max-height: 380px;
    overflow-y: auto;
    padding: 10px 12px;
}

.quicksearch-section-title {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: 1px;
    color: #9d82b0;
    padding: 8px 12px 4px;
}

.quicksearch-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 11px 14px;
    border-radius: 12px;
    color: #2a0d3e;
    text-decoration: none;
    transition: background-color .15s, transform .15s;
    margin-bottom: 2px;
}

.quicksearch-item i {
    width: 24px;
    font-size: 16px;
    color: #7c1db8;
    text-align: center;
}

.qs-item-info {
    flex: 1;
    display: flex;
    flex-direction: column;
}

.qs-item-info strong {
    font-size: 13.5px;
}

.qs-item-info span {
    font-size: 11.5px;
    color: #7a5a90;
}

.qs-badge {
    padding: 3px 8px;
    border-radius: 8px;
    background: #f4e8fd;
    color: #7c1db8;
    font-size: 10.5px;
    font-weight: 700;
}

.quicksearch-item:hover, .quicksearch-item.selected {
    background: #f7effd;
    transform: translateX(3px);
}

.quicksearch-footer {
    padding: 10px 20px;
    background: #faf6fd;
    border-top: 1px solid #f1e4f8;
    font-size: 11px;
    color: #7a5a90;
    display: flex;
    justify-content: flex-end;
}
</style>

<!-- ══════ JAVASCRIPT GLOBAL MODULAR (VITE) ══════ -->
@vite(['resources/js/modules/layout/sidebaradmin.js'])

