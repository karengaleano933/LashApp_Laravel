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
            <button type="button" class="icon-btn-circle" title="Notificaciones" id="btnNotifToggle" onclick="toggleMenuNotificaciones(event)">
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

        <!-- 4. BOTÓN MI CUENTA / PERFIL -->
        <div class="user-menu-wrapper" id="userMenuWrapperMain">
            <button type="button" class="icon-btn-circle" title="Mi Cuenta" id="btnUserMenuToggle" onclick="toggleMenuUsuario(event)">
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