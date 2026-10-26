/**
 * ============================================================================
 * Módulo de Horario del Trabajador (LashApp - Trabajador)
 * ============================================================================
 * 
 * Segmentación y parametrización:
 * 1. Control del ciclo de vida del modal de agregar nuevo bloque de horario.
 * 2. Filtrado interactivo por pestañas de estado (Todos / Disponibles / Ocupados)
 *    y búsqueda en tiempo real.
 * 3. Diálogo SweetAlert2 destructivo parametrizado para eliminación de bloques.
 * 4. Procesamiento de mensajes de sesión (éxito y advertencia/error).
 */

const DEFAULT_CONFIG = {
    containerSelector: '#horarioTrabajadorContainer',
    selectors: {
        modal: '#modalNuevoHorario',
        btnAbrir: '#btnAbrirModalHorario',
        btnCerrar: '#btnCerrarModalHorario',
        btnCancelar: '#btnCancelarModalHorario',
        tabBtns: '.horario-tab-btn',
        searchInput: '#horarioSearchInput',
        tableRows: '#tablaHorarios tbody tr',
    },
    classes: {
        modalActivo: 'activo',
        tabActiva: 'active',
    },
    colors: {
        primary: '#7c1db8',
        danger: '#dc2626',
        neutral: '#7a5a90',
    }
};

let currentConfig = { ...DEFAULT_CONFIG };

/**
 * Procesa notificaciones de sesión pasadas vía data-attributes en el contenedor
 */
function handleSessionAlerts(container) {
    if (!container || typeof Swal === 'undefined') return;

    const successMsg = container.dataset.sessionSuccess;
    const errorMsg = container.dataset.sessionError;

    if (successMsg) {
        Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: successMsg,
            confirmButtonColor: currentConfig.colors.primary,
            confirmButtonText: 'Aceptar',
        });
    } else if (errorMsg) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: errorMsg,
            confirmButtonColor: currentConfig.colors.primary,
            confirmButtonText: 'Entendido',
        });
    }
}

/**
 * Control del modal de nuevo horario
 */
function initModal(config) {
    const modal = document.querySelector(config.selectors.modal);
    const btnAbrir = document.querySelector(config.selectors.btnAbrir);
    const btnCerrar = document.querySelector(config.selectors.btnCerrar);
    const btnCancelar = document.querySelector(config.selectors.btnCancelar);

    function abrirModal() {
        if (modal) modal.classList.add(config.classes.modalActivo);
    }

    function cerrarModal() {
        if (modal) modal.classList.remove(config.classes.modalActivo);
    }

    if (btnAbrir) btnAbrir.addEventListener('click', abrirModal);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrarModal);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrarModal);

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) cerrarModal();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains(config.classes.modalActivo)) {
            cerrarModal();
        }
    });
}

/**
 * Filtrado por pestañas y búsqueda en tiempo real
 */
function initFiltros(config) {
    const tabBtns = document.querySelectorAll(config.selectors.tabBtns);
    const searchInput = document.querySelector(config.selectors.searchInput);
    const tableRows = document.querySelectorAll(config.selectors.tableRows);

    let currentFilter = 'all';

    function aplicarFiltros() {
        const query = (searchInput?.value || '').trim().toLowerCase();

        tableRows.forEach((row) => {
            const filterType = row.getAttribute('data-filter-type');
            const searchText = (row.getAttribute('data-search-text') || '').toLowerCase();

            if (!filterType) return; // Fila de estado vacío

            const matchesFilter = (currentFilter === 'all') || (filterType === currentFilter);
            const matchesSearch = !query || searchText.includes(query);

            row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';
        });
    }

    tabBtns.forEach((btn) => {
        btn.addEventListener('click', function () {
            tabBtns.forEach((b) => b.classList.remove(config.classes.tabActiva));
            this.classList.add(config.classes.tabActiva);
            currentFilter = this.getAttribute('data-filter') || 'all';
            aplicarFiltros();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', aplicarFiltros);
    }
}

/**
 * Confirmación SweetAlert para eliminar un bloque de horario
 */
export function confirmarEliminarHorario(btn) {
    if (typeof Swal === 'undefined') {
        if (confirm('¿Eliminar este bloque horario?\nEsta acción no se puede deshacer si no tiene citas asignadas.')) {
            btn.closest('form')?.submit();
        }
        return;
    }

    Swal.fire({
        title: '¿Eliminar este bloque horario?',
        text: 'Esta acción no se puede deshacer si no tiene citas asignadas.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: currentConfig.colors.danger,
        cancelButtonColor: currentConfig.colors.neutral,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            btn.closest('form')?.submit();
        }
    });
}

/**
 * Inicializador principal del módulo
 */
export function initHorarioTrabajador(customConfig = {}) {
    const container = document.querySelector(customConfig.containerSelector || DEFAULT_CONFIG.containerSelector) 
                   || document.querySelector('.horario-page-container');
    if (!container) return;

    currentConfig = {
        ...DEFAULT_CONFIG,
        ...customConfig,
    };

    handleSessionAlerts(container);
    initModal(currentConfig);
    initFiltros(currentConfig);
}

// Retrocompatibilidad global para llamadas inline en Blade (onclick)
window.confirmarEliminarHorario = confirmarEliminarHorario;
window.initHorarioTrabajador = initHorarioTrabajador;

// Inicialización automática al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    initHorarioTrabajador();
});
