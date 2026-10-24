/**
 * ============================================================================
 * Módulo de Gestión de Reseñas (LashApp - Admin)
 * ============================================================================
 *
 * Segmentación de responsabilidades:
 * 1. Selector interactivo de estrellas para calificación en el formulario.
 * 2. Filtrado y búsqueda reactiva de reseñas por texto y cantidad de estrellas.
 * 3. Confirmación destructiva parametrizada para eliminar reseñas.
 */

import { confirmAction } from '../../common/alerts.js';

const DEFAULT_CONFIG = {
    containerSelector: '#resenasContainer',
    selectors: {
        starSelector: '#starSelector',
        inputCalificacion: '#input_calificacion',
        searchInput: '#buscarResena',
        starsFilter: '#filtroEstrellas',
        tableRows: '#tablaResenas tbody tr',
        formDelete: '#formEliminarResena',
        btnDeleteTrigger: '.btn-eliminar-resena',
    },
    endpoints: {
        baseUrl: '/admin/resenas',
    },
    messages: {
        deleteTitle: '¿Eliminar reseña?',
        deleteText: (cliente) => `¿Estás seguro de eliminar la reseña de "${cliente}"? Esta acción no se puede deshacer.`,
        deleteConfirmBtn: 'Sí, eliminar',
        deleteCancelBtn: 'Cancelar',
    }
};

/**
 * 1. Segmento: Selector de estrellas interactivo
 */
function setupStarRating(config) {
    const starSelector = document.querySelector(config.selectors.starSelector);
    const inputCalificacion = document.querySelector(config.selectors.inputCalificacion);

    if (starSelector && inputCalificacion) {
        const stars = starSelector.querySelectorAll('i');
        stars.forEach((star) => {
            star.addEventListener('click', function () {
                const val = parseInt(this.dataset.value);
                inputCalificacion.value = val;

                stars.forEach((s) => {
                    const sVal = parseInt(s.dataset.value);
                    if (sVal <= val) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    }
}

/**
 * 2. Segmento: Filtrado en tiempo real
 */
function setupResenasFilter(config) {
    const buscar = document.querySelector(config.selectors.searchInput);
    const filtro = document.querySelector(config.selectors.starsFilter);
    const filas = document.querySelectorAll(config.selectors.tableRows);

    if (!filas.length) return;

    function filtrarResenas() {
        const texto = (buscar ? buscar.value : '').toLowerCase().trim();
        const estrellas = (filtro ? filtro.value : '').trim();

        filas.forEach((fila) => {
            const contenido = fila.textContent.toLowerCase();
            const filaEstrellas = (fila.dataset.estrellas || '').trim();

            const coincideTexto = !texto || contenido.includes(texto);
            const coincideEstrellas = !estrellas || filaEstrellas === estrellas;

            fila.style.display = (coincideTexto && coincideEstrellas) ? '' : 'none';
        });
    }

    if (buscar) buscar.addEventListener('input', filtrarResenas);
    if (filtro) filtro.addEventListener('change', filtrarResenas);
}

/**
 * 3. Segmento: Eliminación parametrizada
 */
function setupDeleteResena(config) {
    const deleteBtns = document.querySelectorAll(config.selectors.btnDeleteTrigger);
    const formEliminar = document.querySelector(config.selectors.formDelete);

    if (!deleteBtns.length || !formEliminar) return;

    deleteBtns.forEach((btn) => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.id;
            const cliente = this.dataset.cliente || 'este cliente';

            const confirmed = await confirmAction({
                title: config.messages.deleteTitle,
                text: typeof config.messages.deleteText === 'function'
                    ? config.messages.deleteText(cliente)
                    : config.messages.deleteText,
                icon: 'warning',
                isDestructive: true,
                confirmButtonText: config.messages.deleteConfirmBtn,
                cancelButtonText: config.messages.deleteCancelBtn,
            });

            if (confirmed) {
                const baseUrl = config.endpoints.baseUrl.replace(/\/+$/, '');
                formEliminar.action = `${baseUrl}/${encodeURIComponent(id)}`;
                formEliminar.submit();
            }
        });
    });
}

/**
 * Inicializador principal
 */
export function initResenas(customConfig = {}) {
    const container = document.querySelector(customConfig.containerSelector || DEFAULT_CONFIG.containerSelector);
    const domBaseUrl = container?.dataset?.baseUrl;

    const config = {
        ...DEFAULT_CONFIG,
        ...customConfig,
        endpoints: {
            ...DEFAULT_CONFIG.endpoints,
            baseUrl: domBaseUrl || customConfig.endpoints?.baseUrl || DEFAULT_CONFIG.endpoints.baseUrl,
            ...customConfig.endpoints
        }
    };

    setupStarRating(config);
    setupResenasFilter(config);
    setupDeleteResena(config);
}

// Auto-inicialización
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('tablaResenas')) {
                initResenas();
            }
        });
    } else {
        if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('tablaResenas')) {
            initResenas();
        }
    }
}
