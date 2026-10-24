/**
 * ============================================================================
 * Módulo de Navegación y Cabecera Administrativa (LashApp - Layout)
 * ============================================================================
 *
 * Segmentación de responsabilidades:
 * 1. Control del sidebar responsivo (colapso y apertura móvil/escritorio).
 * 2. Búsqueda rápida accesible (atajo Ctrl+K / Cmd+K y filtrado).
 * 3. Gestión asíncrona de notificaciones (carga remota, badge reactivo y marcado como leídas).
 * 4. Menú desplegable de perfil de usuario y control de clics fuera de pantalla.
 *
 * Parametrización:
 * - Endpoints configurables (por defecto o leídos desde atributos data-* en el DOM).
 * - Selectores y clases de estado desacoplados de la lógica central.
 */

import { http } from '../../common/http.js';
import { showToast } from '../../common/alerts.js';

const DEFAULT_CONFIG = {
    selectors: {
        // Sidebar
        sidebarToggle: '#sidebarToggle',
        sidebar: '#sidebar',
        // Búsqueda rápida
        searchOverlay: '#modalQuickSearchOverlay',
        searchInput: '#quickSearchInput',
        searchItem: '.quicksearch-item',
        searchTriggerBtn: '#btnQuickSearchToggle',
        // Notificaciones
        notifWrapper: '#notifWrapperMain',
        notifToggleBtn: '#btnNotifToggle',
        notifMenu: '#notifDropdownMenu',
        notifBadge: '#notifBadgeCount',
        notifList: '#notifListContainer',
        btnMarkRead: '#btnMarcarTodasLeidas',
        // Menú Usuario
        userWrapper: '#userMenuWrapperMain',
        userToggleBtn: '#btnUserMenuToggle',
        userMenu: '#userProfileDropdown',
    },
    classes: {
        active: 'active',
        collapsed: 'collapsed',
        show: 'show',
        unreadItem: 'noleida',
    },
    endpoints: {
        notificationsIndex: '/admin/notificaciones',
        notificationsMarkRead: '/admin/notificaciones/marcar-leidas',
    }
};

/**
 * 1. Segmento: Colapso y apertura del Sidebar
 */
function setupSidebar(config) {
    const toggleBtn = document.querySelector(config.selectors.sidebarToggle);
    const sidebar = document.querySelector(config.selectors.sidebar);

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            sidebar.classList.toggle(config.classes.active);
            sidebar.classList.toggle(config.classes.collapsed);
        });
    }
}

/**
 * 2. Segmento: Buscador Rápido (Ctrl+K)
 */
function setupQuickSearch(config) {
    const overlay = document.querySelector(config.selectors.searchOverlay);
    const input = document.querySelector(config.selectors.searchInput);
    const items = document.querySelectorAll(config.selectors.searchItem);
    const triggerBtn = document.querySelector(config.selectors.searchTriggerBtn);

    function openSearch() {
        if (!overlay) return;
        overlay.style.display = 'flex';
        if (input) {
            input.value = '';
            filterItems('');
            setTimeout(() => input.focus(), 80);
        }
    }

    function closeSearch() {
        if (overlay) overlay.style.display = 'none';
    }

    function filterItems(query) {
        const q = query.toLowerCase().trim();
        items.forEach((item) => {
            const dataText = (item.getAttribute('data-text') || '').toLowerCase();
            const textContent = item.textContent.toLowerCase();
            const matches = !q || dataText.includes(q) || textContent.includes(q);
            item.style.display = matches ? 'flex' : 'none';
        });
    }

    if (triggerBtn) triggerBtn.addEventListener('click', openSearch);
    if (overlay) {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) closeSearch();
        });
    }

    if (input) {
        input.addEventListener('input', () => filterItems(input.value));
    }

    // Exponer globalmente para retrocompatibilidad con onclick en Blade
    window.abrirModalBusquedaRapida = openSearch;
    window.cerrarModalBusquedaRapida = closeSearch;

    return { openSearch, closeSearch };
}

/**
 * 3. Segmento: Centro de Notificaciones Asíncrono
 */
function setupNotifications(config, userMenuControls) {
    const btnToggle = document.querySelector(config.selectors.notifToggleBtn);
    const menu = document.querySelector(config.selectors.notifMenu);
    const badge = document.querySelector(config.selectors.notifBadge);
    const list = document.querySelector(config.selectors.notifList);
    const btnMarkAll = document.querySelector(config.selectors.btnMarkRead);

    function updateBadge(count) {
        if (!badge) return;
        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    async function loadNotifications() {
        try {
            const data = await http.get(config.endpoints.notificationsIndex);
            updateBadge(data?.count || 0);

            if (!list) return;

            if (!data?.notificaciones || data.notificaciones.length === 0) {
                list.innerHTML = `
                    <div class="notif-empty">
                        <i class="fa-regular fa-bell-slash"></i>
                        <p>No tienes notificaciones pendientes.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            data.notificaciones.forEach((n) => {
                const iconClass = n.tipo === 'cancelada' 
                    ? 'cancelada fa-circle-xmark' 
                    : (n.tipo === 'info' ? 'info fa-circle-info' : 'agendada fa-calendar-check');
                const noLeidaClass = !n.leida ? 'noleida' : '';
                const dotHtml = !n.leida ? '<span class="notif-unread-dot"></span>' : '';

                html += `
                    <div class="notif-item ${noLeidaClass}" onclick="window.location.href='${n.enlace || '#'}'">
                        <div class="notif-icon ${n.tipo || 'agendada'}">
                            <i class="fa-solid ${iconClass}"></i>
                        </div>
                        <div class="notif-content">
                            <div class="notif-item-title">
                                <span>${n.titulo}</span>
                                ${dotHtml}
                            </div>
                            <div class="notif-item-text">${n.mensaje}</div>
                            <div class="notif-item-time"><i class="fa-regular fa-clock"></i> ${n.tiempo}</div>
                        </div>
                    </div>
                `;
            });
            list.innerHTML = html;
        } catch (error) {
            console.error('[Notificaciones] Error al cargar:', error);
            if (list) {
                list.innerHTML = `
                    <div class="notif-empty">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <p>No fue posible conectar con las notificaciones.</p>
                    </div>
                `;
            }
        }
    }

    function toggleDropdown() {
        if (!menu) return;
        const isOpen = menu.classList.contains(config.classes.show);
        if (userMenuControls?.closeMenu) userMenuControls.closeMenu();

        if (isOpen) {
            closeDropdown();
        } else {
            menu.classList.add(config.classes.show);
            loadNotifications();
        }
    }

    function closeDropdown() {
        if (menu) menu.classList.remove(config.classes.show);
    }

    if (btnToggle) {
        btnToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleDropdown();
        });
    }

    if (btnMarkAll) {
        btnMarkAll.addEventListener('click', async (e) => {
            e.stopPropagation();
            try {
                await http.post(config.endpoints.notificationsMarkRead, {});
                updateBadge(0);
                document.querySelectorAll('.notif-item.noleida').forEach((el) => el.classList.remove('noleida'));
                document.querySelectorAll('.notif-unread-dot').forEach((el) => el.remove());
                showToast({ title: 'Notificaciones marcadas como leídas', icon: 'success' });
            } catch (err) {
                console.error('[Notificaciones] Error al marcar como leídas:', err);
            }
        });
    }

    // Carga inicial pasiva del badge
    loadNotifications();

    return { toggleDropdown, closeDropdown, loadNotifications };
}

/**
 * 4. Segmento: Menú de Perfil de Usuario
 */
function setupUserMenu(config) {
    const btnToggle = document.querySelector(config.selectors.userToggleBtn);
    const menu = document.querySelector(config.selectors.userMenu);

    function toggleMenu() {
        if (!menu) return;
        menu.classList.toggle(config.classes.show);
    }

    function closeMenu() {
        if (menu) menu.classList.remove(config.classes.show);
    }

    if (btnToggle) {
        btnToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleMenu();
        });
    }

    return { toggleMenu, closeMenu };
}

/**
 * Inicializador principal del layout administrativo
 */
export function initSidebarAdmin(customConfig = {}) {
    // Parámetros leídos desde atributos data-* del wrapper
    const notifWrapper = document.querySelector(DEFAULT_CONFIG.selectors.notifWrapper);
    const domNotifUrl = notifWrapper?.dataset?.notifUrl;
    const domMarkReadUrl = notifWrapper?.dataset?.markReadUrl;

    const config = {
        ...DEFAULT_CONFIG,
        ...customConfig,
        selectors: {
            ...DEFAULT_CONFIG.selectors,
            ...customConfig.selectors,
        },
        endpoints: {
            ...DEFAULT_CONFIG.endpoints,
            notificationsIndex: domNotifUrl || customConfig.endpoints?.notificationsIndex || DEFAULT_CONFIG.endpoints.notificationsIndex,
            notificationsMarkRead: domMarkReadUrl || customConfig.endpoints?.notificationsMarkRead || DEFAULT_CONFIG.endpoints.notificationsMarkRead,
        }
    };

    setupSidebar(config);
    const searchControls = setupQuickSearch(config);
    const userMenuControls = setupUserMenu(config);
    const notifControls = setupNotifications(config, userMenuControls);

    // Cerrar dropdowns al hacer clic fuera o pulsar Escape
    document.addEventListener('click', (e) => {
        const notifWrap = document.querySelector(config.selectors.notifWrapper);
        const userWrap = document.querySelector(config.selectors.userWrapper);

        if (notifWrap && !notifWrap.contains(e.target)) {
            notifControls.closeDropdown();
        }
        if (userWrap && !userWrap.contains(e.target)) {
            userMenuControls.closeMenu();
        }
    });

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            searchControls.openSearch();
        } else if (e.key === 'Escape') {
            searchControls.closeSearch();
            notifControls.closeDropdown();
            userMenuControls.closeMenu();
        }
    });
}

// Auto-inicialización
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector('.top-header') || document.querySelector('#sidebar')) {
                initSidebarAdmin();
            }
        });
    } else {
        if (document.querySelector('.top-header') || document.querySelector('#sidebar')) {
            initSidebarAdmin();
        }
    }
}
