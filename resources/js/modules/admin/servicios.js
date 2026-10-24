/**
 * ============================================================================
 * Módulo de Gestión de Servicios (LashApp - Admin)
 * ============================================================================
 * 
 * Segmentación de responsabilidades:
 * 1. Filtrado y búsqueda en tiempo real de la tabla de servicios.
 * 2. Gestión de ciclo de vida de modales (Creación y Edición).
 * 3. Poblamiento y vinculación dinámica del formulario de edición.
 * 4. Confirmación parametrizada de eliminación con diálogo destructivo.
 *
 * Parametrización:
 * - Todos los selectores DOM, URLs base y mensajes de confirmación son
 *   configurables a través de un objeto de opciones o atributos data-* del DOM.
 */

import { confirmAction } from '../../common/alerts.js';

// Configuración por defecto parametrizada
const DEFAULT_CONFIG = {
    containerSelector: '#serviciosContainer',
    selectors: {
        searchInput: '#buscarServicio',
        categoryFilter: '#filtroCategoria',
        tableRows: '#tablaServicios tbody tr',
        emptyRowClass: '.tabla-vacia',
        // Modal Crear
        modalCreate: '#modalCrearServicio',
        btnOpenCreate: '#abrirNuevoServicio',
        btnCloseCreate: '#cerrarModalServicio',
        btnCancelCreate: '#cancelarModalServicio',
        // Modal Editar
        modalEdit: '#modalEditarServicio',
        btnCloseEdit: '#cerrarModalEditarServicio',
        btnCancelEdit: '#cancelarModalEditarServicio',
        formEdit: '#formEditarServicio',
        btnEditTrigger: '.btn-editar-servicio',
        // Campos Formulario Editar
        editFields: {
            id: '#edit_id_servicio',
            nombre: '#edit_nombre_servicio',
            categoria: '#edit_id_categoria',
            precio: '#edit_precio_servicio',
            duracion: '#edit_duracion_servicio',
            descripcion: '#edit_descripcion_servicio',
            estado: '#edit_estado_servicio',
        },
        // Eliminar
        formDelete: '#formEliminarServicio',
        btnDeleteTrigger: '.btn-eliminar-servicio',
    },
    classes: {
        modalActive: 'activo',
    },
    endpoints: {
        baseUrl: '/admin/servicios',
    },
    messages: {
        deleteTitle: '¿Eliminar servicio?',
        deleteText: (nombre) => `¿Estás seguro de eliminar "${nombre}"? Esta acción no se puede deshacer.`,
        deleteConfirmBtn: 'Sí, eliminar',
        deleteCancelBtn: 'Cancelar',
    }
};

/**
 * 1. Segmento: Filtrado en tiempo real (Búsqueda por texto y categoría)
 */
function setupServiceFiltering(config) {
    const searchInput = document.querySelector(config.selectors.searchInput);
    const categorySelect = document.querySelector(config.selectors.categoryFilter);
    const rows = document.querySelectorAll(config.selectors.tableRows);

    if (!rows.length) return;

    function applyFilter() {
        const searchText = (searchInput ? searchInput.value : '').toLowerCase().trim();
        const selectedCategory = (categorySelect ? categorySelect.value : '').toLowerCase().trim();

        rows.forEach((row) => {
            if (row.querySelector(config.selectors.emptyRowClass)) return;

            const textContent = row.textContent.toLowerCase();
            const rowCategory = (row.dataset.categoria || '').toLowerCase();

            const matchesText = !searchText || textContent.includes(searchText);
            const matchesCategory = !selectedCategory || rowCategory.includes(selectedCategory);

            row.style.display = (matchesText && matchesCategory) ? '' : 'none';
        });
    }

    if (searchInput) searchInput.addEventListener('input', applyFilter);
    if (categorySelect) categorySelect.addEventListener('change', applyFilter);
}

/**
 * 2. Segmento: Ciclo de vida de los Modales (Abrir, Cerrar, Escape)
 */
function setupModalLifecycle(config) {
    const modalCreate = document.querySelector(config.selectors.modalCreate);
    const btnOpenCreate = document.querySelector(config.selectors.btnOpenCreate);
    const btnCloseCreate = document.querySelector(config.selectors.btnCloseCreate);
    const btnCancelCreate = document.querySelector(config.selectors.btnCancelCreate);

    const modalEdit = document.querySelector(config.selectors.modalEdit);
    const btnCloseEdit = document.querySelector(config.selectors.btnCloseEdit);
    const btnCancelEdit = document.querySelector(config.selectors.btnCancelEdit);

    const activeClass = config.classes.modalActive;

    function openModal(modal) {
        if (!modal) return;
        modal.classList.add(activeClass);
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove(activeClass);
        document.body.style.overflow = '';
    }

    // Eventos Modal Crear
    if (btnOpenCreate) btnOpenCreate.addEventListener('click', () => openModal(modalCreate));
    if (btnCloseCreate) btnCloseCreate.addEventListener('click', () => closeModal(modalCreate));
    if (btnCancelCreate) btnCancelCreate.addEventListener('click', () => closeModal(modalCreate));

    if (modalCreate) {
        modalCreate.addEventListener('click', (e) => {
            if (e.target === modalCreate) closeModal(modalCreate);
        });
    }

    // Eventos Modal Editar
    if (btnCloseEdit) btnCloseEdit.addEventListener('click', () => closeModal(modalEdit));
    if (btnCancelEdit) btnCancelEdit.addEventListener('click', () => closeModal(modalEdit));

    if (modalEdit) {
        modalEdit.addEventListener('click', (e) => {
            if (e.target === modalEdit) closeModal(modalEdit);
        });
    }

    // Tecla Escape para cerrar cualquier modal abierto
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(modalCreate);
            closeModal(modalEdit);
        }
    });

    return { openModal, closeModal };
}

/**
 * 3. Segmento: Edición de Servicios (Mapeo de datos y configuración del endpoint)
 */
function setupEditService(config, modalControls) {
    const editButtons = document.querySelectorAll(config.selectors.btnEditTrigger);
    const editForm = document.querySelector(config.selectors.formEdit);
    const modalEdit = document.querySelector(config.selectors.modalEdit);
    const fields = config.selectors.editFields;

    if (!editButtons.length || !editForm) return;

    editButtons.forEach((button) => {
        button.addEventListener('click', function () {
            const id = this.dataset.id || '';
            const nombre = this.dataset.nombre || '';
            const categoria = this.dataset.categoria || '';
            const precio = this.dataset.precio || '';
            const duracion = this.dataset.duracion || '';
            const descripcion = this.dataset.descripcion || '';
            const estado = (this.dataset.estado || 'activo').toLowerCase();

            // Mapeo seguro a los campos del formulario
            const setVal = (selector, val) => {
                const el = document.querySelector(selector);
                if (el) el.value = val;
            };

            setVal(fields.id, id);
            setVal(fields.nombre, nombre);
            setVal(fields.categoria, categoria);
            setVal(fields.precio, precio);
            setVal(fields.duracion, duracion);
            setVal(fields.descripcion, descripcion);
            setVal(fields.estado, estado);

            // Parametrización dinámica del endpoint PUT
            const baseUrl = config.endpoints.baseUrl.replace(/\/+$/, '');
            editForm.action = `${baseUrl}/${encodeURIComponent(id)}`;

            modalControls.openModal(modalEdit);
        });
    });
}

/**
 * 4. Segmento: Confirmación y eliminación parametrizada
 */
function setupDeleteService(config) {
    const deleteButtons = document.querySelectorAll(config.selectors.btnDeleteTrigger);
    const deleteForm = document.querySelector(config.selectors.formDelete);

    if (!deleteButtons.length || !deleteForm) return;

    deleteButtons.forEach((button) => {
        button.addEventListener('click', async function () {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre || 'este servicio';

            const confirmed = await confirmAction({
                title: config.messages.deleteTitle,
                text: typeof config.messages.deleteText === 'function'
                    ? config.messages.deleteText(nombre)
                    : config.messages.deleteText,
                icon: 'warning',
                isDestructive: true,
                confirmButtonText: config.messages.deleteConfirmBtn,
                cancelButtonText: config.messages.deleteCancelBtn,
            });

            if (confirmed) {
                const baseUrl = config.endpoints.baseUrl.replace(/\/+$/, '');
                deleteForm.action = `${baseUrl}/${encodeURIComponent(id)}`;
                deleteForm.submit();
            }
        });
    });
}

/**
 * Inicializador principal del módulo de servicios
 *
 * @param {Object} customConfig - Opciones de configuración para sobreescribir defaults
 */
export function initServicios(customConfig = {}) {
    // Lectura de parámetros desde data-attributes en el contenedor principal si existen
    const container = document.querySelector(customConfig.containerSelector || DEFAULT_CONFIG.containerSelector);
    const domBaseUrl = container?.dataset?.baseUrl;

    const mergedConfig = {
        ...DEFAULT_CONFIG,
        ...customConfig,
        selectors: {
            ...DEFAULT_CONFIG.selectors,
            ...customConfig.selectors,
            editFields: {
                ...DEFAULT_CONFIG.selectors.editFields,
                ...(customConfig.selectors?.editFields || {})
            }
        },
        endpoints: {
            ...DEFAULT_CONFIG.endpoints,
            baseUrl: domBaseUrl || customConfig.endpoints?.baseUrl || DEFAULT_CONFIG.endpoints.baseUrl,
            ...customConfig.endpoints
        },
        messages: {
            ...DEFAULT_CONFIG.messages,
            ...customConfig.messages
        }
    };

    // Inicializar cada subsistema segmentado
    setupServiceFiltering(mergedConfig);
    const modalControls = setupModalLifecycle(mergedConfig);
    setupEditService(mergedConfig, modalControls);
    setupDeleteService(mergedConfig);
}

// Auto-inicialización si el elemento contenedor está presente en la página
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('tablaServicios')) {
                initServicios();
            }
        });
    } else {
        if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('tablaServicios')) {
            initServicios();
        }
    }
}
