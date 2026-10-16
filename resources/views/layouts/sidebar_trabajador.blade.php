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
        <div class="notif-wrapper" id="notifWrapperMain">
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
                    <a href="{{ route('profile.edit') }}" class="up-item">
                        <i class="fa-solid fa-id-badge"></i>
                        <span>Mi Perfil</span>
                    </a>
                    <a href="{{ route('citas.index') }}" class="up-item">
                        <i class="fa-solid fa-calendar-check"></i>
                        <span>Mis Citas</span>
                    </a>
                    <a href="{{ route('servicios.index') }}" class="up-item">
                        <i class="fa-solid fa-spa"></i>
                        <span>Servicios</span>
                    </a>
                    <a href="{{ route('resenas.index') }}" class="up-item">
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
            <a href="{{ route('servicios.index') }}" class="quicksearch-item" data-text="servicios catalogo tratamientos pestanas">
                <i class="fa-solid fa-spa"></i>
                <div class="qs-item-info"><strong>Servicios</strong><span>Catálogo de servicios disponibles</span></div>
                <span class="qs-badge">Servicios</span>
            </a>
            <a href="{{ route('resenas.index') }}" class="quicksearch-item" data-text="resenas opiniones comentarios calificaciones">
                <i class="fa-solid fa-star"></i>
                <div class="qs-item-info"><strong>Reseñas</strong><span>Opiniones y calificaciones recibidas</span></div>
                <span class="qs-badge">Reseñas</span>
            </a>
            <a href="{{ route('profile.edit') }}" class="quicksearch-item" data-text="perfil configuracion cuenta seguridad">
                <i class="fa-solid fa-user-gear"></i>
                <div class="qs-item-info"><strong>Mi Perfil</strong><span>Editar datos personales y contraseña</span></div>
                <span class="qs-badge">Perfil</span>
            </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ══════════════════════════════════════════════════════
    // 1. TOGGLE SIDEBAR RESPONSIVE
    // ══════════════════════════════════════════════════════
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            sidebar.classList.toggle('active');
        });

        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
                    sidebar.classList.remove('active');
                }
            }
        });
    }

    // ══════════════════════════════════════════════════════
    // 2. MODAL DE BÚSQUEDA RÁPIDA (Ctrl + K)
    // ══════════════════════════════════════════════════════
    const searchModal = document.getElementById('modalQuickSearchOverlay');
    const searchInput = document.getElementById('quickSearchInput');

    window.abrirModalBusquedaRapida = function() {
        if (!searchModal) return;
        searchModal.style.display = 'flex';
        setTimeout(() => {
            if (searchInput) {
                searchInput.value = '';
                searchInput.focus();
                filtrarItemsBusqueda('');
            }
        }, 50);
    };

    window.cerrarModalBusquedaRapida = function() {
        if (searchModal) searchModal.style.display = 'none';
    };

    if (searchModal) {
        searchModal.addEventListener('click', function(e) {
            if (e.target === searchModal) cerrarModalBusquedaRapida();
        });
    }

    function filtrarItemsBusqueda(query) {
        const q = query.trim().toLowerCase();
        const items = document.querySelectorAll('#quickSearchResultsList .quicksearch-item');
        items.forEach(item => {
            const txt = (item.getAttribute('data-text') || '').toLowerCase();
            const strong = (item.querySelector('strong')?.innerText || '').toLowerCase();
            item.style.display = (!q || txt.includes(q) || strong.includes(q)) ? 'flex' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filtrarItemsBusqueda(this.value);
        });
    }

    // Atajo de teclado: Ctrl + K
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            if (searchModal && searchModal.style.display === 'flex') {
                cerrarModalBusquedaRapida();
            } else {
                abrirModalBusquedaRapida();
            }
        } else if (e.key === 'Escape') {
            cerrarModalBusquedaRapida();
            cerrarNotifDropdown();
            cerrarUserDropdown();
        }
    });

    // ══════════════════════════════════════════════════════
    // 3. DROPDOWN DE NOTIFICACIONES
    // ══════════════════════════════════════════════════════
    const btnNotif = document.getElementById('btnNotifToggle');
    const notifMenu = document.getElementById('notifDropdownMenu');
    const notifCountBadge = document.getElementById('notifBadgeCount');
    const notifContainer = document.getElementById('notifListContainer');
    const btnMarcarLeidas = document.getElementById('btnMarcarTodasLeidas');

    function toggleNotifDropdown() {
        if (!notifMenu) return;
        const abierto = notifMenu.classList.contains('show');
        cerrarUserDropdown();
        if (abierto) {
            cerrarNotifDropdown();
        } else {
            notifMenu.classList.add('show');
            cargarNotificaciones();
        }
    }

    function cerrarNotifDropdown() {
        if (notifMenu) notifMenu.classList.remove('show');
    }

    if (btnNotif) {
        btnNotif.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleNotifDropdown();
        });
    }

    function cargarNotificaciones() {
        fetch("{{ route('notificaciones.index') }}")
            .then(res => res.json())
            .then(data => {
                actualizarBadge(data.count || 0);
                if (!notifContainer) return;

                if (!data.notificaciones || data.notificaciones.length === 0) {
                    notifContainer.innerHTML = `
                        <div class="notif-empty" style="text-align:center; padding: 25px 15px; color:#888;">
                            <i class="fa-regular fa-bell-slash" style="font-size:24px; color:#dcc8ee; margin-bottom:8px; display:block;"></i>
                            <p style="margin:0; font-size:12px;">No tienes notificaciones pendientes.</p>
                        </div>
                    `;
                    return;
                }

                let html = '';
                data.notificaciones.forEach(n => {
                    const leidaClase = n.leida ? 'leida' : 'no-leida';
                    html += `
                        <div class="notif-item ${leidaClase}">
                            <div class="notif-item-icon">
                                <i class="${n.icono || 'fa-solid fa-bell'}"></i>
                            </div>
                            <div class="notif-item-body">
                                <strong>${n.titulo || 'Notificación'}</strong>
                                <p>${n.mensaje || ''}</p>
                                <small>${n.tiempo || ''}</small>
                            </div>
                        </div>
                    `;
                });
                notifContainer.innerHTML = html;
            })
            .catch(() => {
                if (notifContainer) {
                    notifContainer.innerHTML = `
                        <div class="notif-empty" style="text-align:center; padding: 20px; color:#888; font-size:12px;">
                            Sin notificaciones recientes
                        </div>
                    `;
                }
            });
    }

    function actualizarBadge(count) {
        if (!notifCountBadge) return;
        if (count > 0) {
            notifCountBadge.textContent = count > 99 ? '99+' : count;
            notifCountBadge.style.display = 'inline-flex';
        } else {
            notifCountBadge.style.display = 'none';
        }
    }

    if (btnMarcarLeidas) {
        btnMarcarLeidas.addEventListener('click', function() {
            fetch("{{ route('notificaciones.marcarLeidas') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            })
            .then(() => {
                actualizarBadge(0);
                cargarNotificaciones();
            })
            .catch(console.error);
        });
    }

    // ══════════════════════════════════════════════════════
    // 4. DROPDOWN PERFIL DE USUARIO
    // ══════════════════════════════════════════════════════
    const btnUserMenu = document.getElementById('btnUserMenuToggle');
    const userDropdown = document.getElementById('userProfileDropdown');

    function toggleUserDropdown() {
        if (!userDropdown) return;
        const abierto = userDropdown.classList.contains('show');
        cerrarNotifDropdown();
        if (abierto) {
            cerrarUserDropdown();
        } else {
            userDropdown.classList.add('show');
        }
    }

    function cerrarUserDropdown() {
        if (userDropdown) userDropdown.classList.remove('show');
    }

    if (btnUserMenu) {
        btnUserMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleUserDropdown();
        });
    }

    // Cerrar dropdowns si se hace clic afuera
    document.addEventListener('click', function (e) {
        const notifWrap = document.getElementById('notifWrapperMain');
        const userWrap = document.getElementById('userMenuWrapperMain');
        if (notifWrap && !notifWrap.contains(e.target)) {
            cerrarNotifDropdown();
        }
        if (userWrap && !userWrap.contains(e.target)) {
            cerrarUserDropdown();
        }
    });

    // Cargar badge silenciosamente al inicio
    cargarNotificaciones();
});
</script>   