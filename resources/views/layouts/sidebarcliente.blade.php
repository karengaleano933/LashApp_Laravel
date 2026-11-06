<!-- ══════ TOP HEADER CLIENTE ══════ -->
<header class="top-header">
    <div class="dash-header-left">
        <button class="sidebar-toggle" id="sidebarToggle" title="Menú">
            <i class="fa-solid fa-bars"></i>
        </button>
        <div>
            <h1>¡Hola, {{ auth()->user()?->name ?? 'Claire' }}! 👋</h1>
            <p>Bienvenida a Oh La Lash by Lorena Ayala Studio</p>
        </div>
    </div>

    <div class="dash-header-right">

        <!-- 1. BOTÓN BÚSQUEDA RÁPIDA / HORARIOS -->
        <button type="button" class="icon-btn-circle" title="Buscar servicios o citas (Ctrl + K)" id="btnQuickSearchToggle" onclick="abrirModalBusquedaRapida()">
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>

        <!-- 2. BOTÓN DE NOTIFICACIONES -->
        <div class="notif-wrapper" id="notifWrapperMain" data-notif-url="{{ route('notificaciones.index') }}" data-mark-read-url="{{ route('notificaciones.marcarLeidas') }}">
            <button type="button" class="icon-btn-circle" title="Mis Notificaciones" id="btnNotifToggle" onclick="toggleMenuNotificaciones(event)">
                <i class="fa-solid fa-bell"></i>
                <span class="notif-badge-count" id="notifBadgeCount" style="display: none;">0</span>
            </button>
            <div class="notif-dropdown" id="notifDropdownMenu">
                <div class="notif-dropdown-header">
                    <div class="notif-header-title">
                        <i class="fa-solid fa-bell"></i> Notificaciones
                    </div>
                    <button type="button" class="btn-marcar-leidas" id="btnMarcarTodasLeidas" onclick="marcarTodasNotificacionesLeidas(event)">
                        <i class="fa-solid fa-check-double"></i> Leídas
                    </button>
                </div>
                <div class="notif-dropdown-body" id="notifListContainer">
                    <div class="notif-loading"><i class="fa-solid fa-spinner fa-spin"></i> Cargando...</div>
                </div>
            </div>
        </div>

        <!-- 3. BOTÓN MI CUENTA -->
        <div class="user-menu-wrapper" id="userMenuWrapperMain">
            <button type="button" class="icon-btn-circle" title="Mi Cuenta" id="btnUserMenuToggle" onclick="toggleMenuUsuario(event)">
                <i class="fa-solid fa-user"></i>
            </button>
            <div class="user-profile-dropdown" id="userProfileDropdown">
                <div class="user-profile-dropdown-header">
                    <div class="up-avatar-circle">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'C', 0, 1)) }}
                    </div>
                    <div class="up-user-info">
                        <strong>{{ auth()->user()?->name }} {{ auth()->user()?->apellido }}</strong>
                        <span>{{ auth()->user()?->email }}</span>
                        <small class="up-role-pill">CLIENTE</small>
                    </div>
                </div>
                <div class="user-profile-dropdown-menu">
                    <a href="{{ route('cliente.perfil') }}" class="up-item">
                        <i class="fa-solid fa-id-badge"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="{{ route('cliente.citas') }}" class="up-item">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span>Mis Citas</span>
                    </a>
                    <a href="{{ route('cliente.historial') }}" class="up-item">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <span>Historial</span>
                    </a>
                    <a href="{{ route('cliente.catalogo') }}" class="up-item">
                        <i class="fa-solid fa-bag-shopping"></i>
                        <span>Catálogo de Servicios</span>
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

<style>
.icon-btn-circle * { pointer-events: none !important; }
.notif-wrapper { position: relative !important; display: inline-block !important; }
.notif-dropdown { position: absolute !important; top: 48px !important; right: 0 !important; width: 340px !important; max-width: 90vw !important; background: #fff !important; border: 1px solid #e9d5ff !important; border-radius: 16px !important; box-shadow: 0 12px 35px rgba(53, 20, 78, 0.22) !important; z-index: 999999 !important; }
.notif-dropdown.show { display: block !important; z-index: 999999 !important; }
.user-menu-wrapper { position: relative !important; display: inline-block !important; }
.user-profile-dropdown { position: absolute !important; top: calc(100% + 10px) !important; right: 0 !important; width: 290px !important; background: #fff !important; border: 1px solid #ebdcf5 !important; border-radius: 18px !important; box-shadow: 0 14px 40px rgba(53, 20, 78, 0.2) !important; z-index: 999999 !important; }
.user-profile-dropdown.show { display: block !important; z-index: 999999 !important; }
</style>

<script>
(function() {
    window.toggleMenuNotificaciones = function(e) {
        if (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
            e.preventDefault();
        }
        var notifMenu = document.getElementById('notifDropdownMenu');
        var userMenu = document.getElementById('userProfileDropdown');
        if (userMenu) userMenu.classList.remove('show');
        
        if (!notifMenu) return;
        var isOpen = notifMenu.classList.contains('show');
        if (isOpen) {
            notifMenu.classList.remove('show');
        } else {
            notifMenu.classList.add('show');
            if (typeof window.cargarNotificacionesHeader === 'function') {
                window.cargarNotificacionesHeader();
            }
        }
    };

    window.toggleMenuUsuario = function(e) {
        if (e) {
            e.stopPropagation();
            e.stopImmediatePropagation();
            e.preventDefault();
        }
        var notifMenu = document.getElementById('notifDropdownMenu');
        if (notifMenu) notifMenu.classList.remove('show');
        
        var userMenu = document.getElementById('userProfileDropdown');
        if (!userMenu) return;
        userMenu.classList.toggle('show');
    };

    window.marcarTodasNotificacionesLeidas = async function(e) {
        if (e) {
            e.stopPropagation();
            e.preventDefault();
        }
        try {
            var wrapper = document.getElementById('notifWrapperMain');
            var url = wrapper ? wrapper.getAttribute('data-mark-read-url') : '{{ route("notificaciones.marcarLeidas") }}';
            var metaCsrf = document.querySelector('meta[name="csrf-token"]');
            var token = metaCsrf ? metaCsrf.getAttribute('content') : '';

            var resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                }
            });

            var badge = document.getElementById('notifBadgeCount');
            if (badge) badge.style.display = 'none';

            document.querySelectorAll('.notif-item.noleida').forEach(function(el) {
                el.classList.remove('noleida');
            });
            document.querySelectorAll('.notif-unread-dot').forEach(function(el) {
                el.remove();
            });

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Notificaciones leídas',
                    text: 'Todas las notificaciones han sido marcadas como leídas.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }
        } catch(err) {
            console.error('Error al marcar leídas:', err);
        }
    };

    window.cargarNotificacionesHeader = async function() {
        try {
            var wrapper = document.getElementById('notifWrapperMain');
            var url = wrapper ? wrapper.getAttribute('data-notif-url') : '{{ route("notificaciones.index") }}';
            var list = document.getElementById('notifListContainer');
            var badge = document.getElementById('notifBadgeCount');

            var resp = await fetch(url, {
                headers: { 'Accept': 'application/json' }
            });
            if (!resp.ok) return;
            var data = await resp.json();

            var count = data ? (data.count || 0) : 0;
            if (badge) {
                if (count > 0) {
                    badge.textContent = count > 9 ? '9+' : count;
                    badge.style.display = 'flex';
                } else {
                    badge.style.display = 'none';
                }
            }

            if (!list) return;
            if (!data || !data.notificaciones || data.notificaciones.length === 0) {
                list.innerHTML = '<div class="notif-empty"><i class="fa-regular fa-bell-slash"></i><p>No tienes notificaciones pendientes.</p></div>';
                return;
            }

            var html = '';
            data.notificaciones.forEach(function(n) {
                var iconClass = n.tipo === 'cancelada' 
                    ? 'fa-circle-xmark' 
                    : (n.tipo === 'info' ? 'fa-circle-info' : 'fa-calendar-check');
                var noLeidaClass = !n.leida ? 'noleida' : '';
                var dotHtml = !n.leida ? '<span class="notif-unread-dot"></span>' : '';

                html += '<div class="notif-item ' + noLeidaClass + '" onclick="window.location.href=\'' + (n.enlace || '#') + '\'" style="cursor: pointer;">' +
                    '<div class="notif-icon ' + (n.tipo || 'agendada') + '">' +
                        '<i class="fa-solid ' + iconClass + '"></i>' +
                    '</div>' +
                    '<div class="notif-content">' +
                        '<div class="notif-item-title">' +
                            '<span>' + n.titulo + '</span>' +
                            dotHtml +
                        '</div>' +
                        '<div class="notif-item-text">' + n.mensaje + '</div>' +
                        '<div class="notif-item-time"><i class="fa-regular fa-clock"></i> ' + n.tiempo + '</div>' +
                    '</div>' +
                '</div>';
            });
            list.innerHTML = html;
        } catch(err) {
            console.error('Error al cargar notificaciones:', err);
        }
    };

    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(e) {
        var notifWrap = document.getElementById('notifWrapperMain');
        var userWrap = document.getElementById('userMenuWrapperMain');
        if (notifWrap && !notifWrap.contains(e.target)) {
            var nm = document.getElementById('notifDropdownMenu');
            if (nm) nm.classList.remove('show');
        }
        if (userWrap && !userWrap.contains(e.target)) {
            var um = document.getElementById('userProfileDropdown');
            if (um) um.classList.remove('show');
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            var nm = document.getElementById('notifDropdownMenu');
            if (nm) nm.classList.remove('show');
            var um = document.getElementById('userProfileDropdown');
            if (um) um.classList.remove('show');
        }
    });

    // Carga inicial
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.cargarNotificacionesHeader);
    } else {
        window.cargarNotificacionesHeader();
    }
})();
</script>

<!-- ══════ MODAL DE BÚSQUEDA RÁPIDA PARA CLIENTE ══════ -->
<div class="modal-quicksearch-overlay" id="modalQuickSearchOverlay" style="display:none;">
    <div class="modal-quicksearch-container">
        <div class="quicksearch-input-header">
            <i class="fa-solid fa-magnifying-glass quicksearch-icon"></i>
            <input type="text" id="quickSearchInput" placeholder="Buscar servicios, fechas, especialistas... (ESC para salir)" autocomplete="off">
            <button type="button" class="quicksearch-close-btn" onclick="cerrarModalBusquedaRapida()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="quicksearch-results-list" id="quickSearchResultsList">
            <div class="quicksearch-section-title">SERVICIOS & SECCIONES</div>
            <a href="{{ route('cliente.catalogo') }}" class="quicksearch-item" data-text="extensiones volumen ruso pestañas">
                <i class="fa-solid fa-spa"></i>
                <div class="qs-item-info"><strong>Extensiones Volumen Ruso</strong><span>$220.000 — 90 min</span></div>
                <span class="qs-badge">Catálogo</span>
            </a>
            <a href="{{ route('cliente.catalogo') }}" class="quicksearch-item" data-text="extensiones clasicas pestañas">
                <i class="fa-solid fa-spa"></i>
                <div class="qs-item-info"><strong>Extensiones Clásicas</strong><span>$180.000 — 90 min</span></div>
                <span class="qs-badge">Catálogo</span>
            </a>
            <a href="{{ route('cliente.catalogo') }}" class="quicksearch-item" data-text="diseño cejas henna">
                <i class="fa-solid fa-spa"></i>
                <div class="qs-item-info"><strong>Diseño de Cejas con Henna</strong><span>$80.000 — 45 min</span></div>
                <span class="qs-badge">Catálogo</span>
            </a>
            <a href="{{ route('cliente.agendar') }}" class="quicksearch-item" data-text="agendar cita reserva horario">
                <i class="fa-solid fa-calendar-plus"></i>
                <div class="qs-item-info"><strong>Agendar Nueva Cita</strong><span>Elige fecha y horario disponible</span></div>
                <span class="qs-badge">Agenda</span>
            </a>
            <a href="{{ route('cliente.citas') }}" class="quicksearch-item" data-text="mis citas reservas">
                <i class="fa-solid fa-calendar-check"></i>
                <div class="qs-item-info"><strong>Mis Citas</strong><span>Revisa tus citas programadas</span></div>
                <span class="qs-badge">Citas</span>
            </a>
            <a href="{{ route('cliente.historial') }}" class="quicksearch-item" data-text="historial completo citas servicios pagos">
                <i class="fa-solid fa-clock-rotate-left"></i>
                <div class="qs-item-info"><strong>Historial</strong><span>Consulta tus servicios y pagos pasados</span></div>
                <span class="qs-badge">Historial</span>
            </a>
            <a href="{{ route('resenas.index') }}" class="quicksearch-item" data-text="reseñas opiniones testimonios">
                <i class="fa-solid fa-star"></i>
                <div class="qs-item-info"><strong>Reseñas y Opiniones</strong><span>Valora nuestro servicio</span></div>
                <span class="qs-badge">Opinión</span>
            </a>
        </div>
        <div class="quicksearch-footer">
            <span>Usa <strong>↑</strong> <strong>↓</strong> para navegar, <strong>ENTER</strong> para seleccionar, <strong>ESC</strong> para cerrar</span>
        </div>
    </div>
</div>

<!-- ══════ ESTILOS ESPECÍFICOS DE LOS BOTONES Y MODALES SUPERIORES (CLIENTE) ══════ -->
<style>
/* ── Dropdown de Perfil ── */
.user-menu-wrapper {
    position: relative;
    display: inline-block;
}

.user-profile-dropdown {
    position: absolute;
    top: calc(100% + 10px);
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
    display: block !important;
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

<!-- ══════ JAVASCRIPT GLOBAL CLIENTE (VITE) ══════ -->
@vite(['resources/js/modules/layout/sidebarcliente.js'])