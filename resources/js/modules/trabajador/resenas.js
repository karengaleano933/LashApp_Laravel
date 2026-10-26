/**
 * ============================================================================
 * Módulo de Reseñas del Trabajador (LashApp - Trabajador)
 * ============================================================================
 * 
 * Segmentación y parametrización:
 * 1. Filtrado interactivo de opiniones de clientas por nivel de calificación
 *    (Todas, 5 estrellas, 4 estrellas, etc.).
 * 2. Gestión visual del estado activo de las pestañas tipo píldora.
 */

const DEFAULT_CONFIG = {
    containerSelector: '#resenasTrabajadorContainer',
    selectors: {
        tabBtns: '.tab-pill-item',
        items: '#resenasList .resena-row-item',
    },
    classes: {
        active: 'active',
    }
};

let currentConfig = { ...DEFAULT_CONFIG };

/**
 * Inicializa el filtrado de reseñas por calificación
 */
function initFiltros(config) {
    const tabBtns = document.querySelectorAll(config.selectors.tabBtns);
    const items = document.querySelectorAll(config.selectors.items);

    tabBtns.forEach((btn) => {
        btn.addEventListener('click', function () {
            tabBtns.forEach((b) => b.classList.remove(config.classes.active));
            this.classList.add(config.classes.active);

            const filter = this.getAttribute('data-filter') || 'all';

            items.forEach((item) => {
                const itemRating = item.getAttribute('data-rating-type');
                if (filter === 'all' || itemRating === filter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
}

/**
 * Inicializador principal del módulo
 */
export function initResenasTrabajador(customConfig = {}) {
    const container = document.querySelector(customConfig.containerSelector || DEFAULT_CONFIG.containerSelector)
                   || document.querySelector('.resenas-page-container');
    if (!container) return;

    currentConfig = {
        ...DEFAULT_CONFIG,
        ...customConfig,
    };

    initFiltros(currentConfig);
}

// Retrocompatibilidad global
window.initResenasTrabajador = initResenasTrabajador;

// Inicialización automática al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    initResenasTrabajador();
});
