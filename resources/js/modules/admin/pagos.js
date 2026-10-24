/**
 * ============================================================================
 * Módulo de Gestión de Pagos (LashApp - Admin)
 * ============================================================================
 *
 * Segmentación de responsabilidades:
 * 1. Búsqueda y filtrado reactivo de pagos por cliente/método.
 * 2. Autocompletado del monto según la cita seleccionada.
 * 3. Ciclo de vida y accesibilidad de modales (Crear y Editar Pago).
 * 4. Mapeo dinámico de datos al formulario de edición.
 * 5. Confirmación y eliminación parametrizada de pagos.
 */

import { confirmAction } from '../../common/alerts.js';

const DEFAULT_CONFIG = {
    containerSelector: '#pagosContainer',
    selectors: {
        searchInput: '#buscarPago',
        methodFilter: '#filtroMetodo',
        tableRows: '#tablaPagos tbody tr',
        emptyRowClass: '.tabla-vacia',
        // Autocompletado de monto
        selectCita: '#id_cita_pago',
        inputMonto: '#monto_pago',
        // Modal Crear
        modalCreate: '#modalCrearPago',
        btnOpenCreate: '#abrirNuevoPago',
        btnCloseCreate: '#cerrarModalPago',
        btnCancelCreate: '#cancelarModalPago',
        // Modal Editar
        modalEdit: '#modalEditarPago',
        btnCloseEdit: '#cerrarModalEditarPago',
        btnCancelEdit: '#cancelarModalEditarPago',
        formEdit: '#formEditarPago',
        btnEditTrigger: '.btn-editar-pago',
        // Campos Editar
        editFields: {
            id: '#edit_id_pago',
            metodo: '#edit_metodo_pago',
            monto: '#edit_monto_pago',
            referencia: '#edit_referencia_pago',
            estado: '#edit_estado_pago',
        },
        // Eliminar
        formDelete: '#formEliminarPago',
        btnDeleteTrigger: '.btn-eliminar-pago',
    },
    classes: {
        modalActive: 'activo',
    },
    endpoints: {
        baseUrl: '/admin/pagos',
    },
    messages: {
        deleteTitle: '¿Eliminar registro de pago?',
        deleteText: (cliente) => `¿Estás seguro de eliminar el pago de "${cliente}"? Esta acción no se puede deshacer.`,
        deleteConfirmBtn: 'Sí, eliminar',
        deleteCancelBtn: 'Cancelar',
    }
};

/**
 * 1. Segmento: Filtrado en tiempo real
 */
function setupPagosFilter(config) {
    const buscar = document.querySelector(config.selectors.searchInput);
    const filtro = document.querySelector(config.selectors.methodFilter);
    const filas = document.querySelectorAll(config.selectors.tableRows);

    if (!filas.length) return;

    function filtrarPagos() {
        const texto = (buscar ? buscar.value : '').toLowerCase().trim();
        const metodo = (filtro ? filtro.value : '').toLowerCase().trim();

        filas.forEach((fila) => {
            if (fila.querySelector(config.selectors.emptyRowClass)) return;

            const contenido = fila.textContent.toLowerCase();
            const metodoFila = (fila.dataset.metodo || '').toLowerCase();

            const coincideTexto = !texto || contenido.includes(texto);
            const coincideMetodo = !metodo || metodoFila.includes(metodo);

            fila.style.display = (coincideTexto && coincideMetodo) ? '' : 'none';
        });
    }

    if (buscar) buscar.addEventListener('input', filtrarPagos);
    if (filtro) filtro.addEventListener('change', filtrarPagos);
}

/**
 * 2. Segmento: Autocompletar monto por cita seleccionada
 */
function setupAmountAutocomplete(config) {
    const selectCita = document.querySelector(config.selectors.selectCita);
    const inputMonto = document.querySelector(config.selectors.inputMonto);

    if (selectCita && inputMonto) {
        selectCita.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const precio = opt ? (opt.dataset.precio || opt.getAttribute('data-precio')) : null;
            if (precio) {
                inputMonto.value = parseFloat(precio).toFixed(2);
            }
        });
    }
}

/**
 * 3. Segmento: Modales Crear y Editar
 */
function setupModalLifecycle(config) {
    const modalCrear = document.querySelector(config.selectors.modalCreate);
    const abrirBtn = document.querySelector(config.selectors.btnOpenCreate);
    const cerrarCrearBtn = document.querySelector(config.selectors.btnCloseCreate);
    const cancelarCrearBtn = document.querySelector(config.selectors.btnCancelCreate);

    const modalEditar = document.querySelector(config.selectors.modalEdit);
    const cerrarEditarBtn = document.querySelector(config.selectors.btnCloseEdit);
    const cancelarEditarBtn = document.querySelector(config.selectors.btnCancelEdit);

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

    if (abrirBtn) abrirBtn.addEventListener('click', () => openModal(modalCrear));
    if (cerrarCrearBtn) cerrarCrearBtn.addEventListener('click', () => closeModal(modalCrear));
    if (cancelarCrearBtn) cancelarCrearBtn.addEventListener('click', () => closeModal(modalCrear));

    if (modalCrear) {
        modalCrear.addEventListener('click', (e) => {
            if (e.target === modalCrear) closeModal(modalCrear);
        });
    }

    if (cerrarEditarBtn) cerrarEditarBtn.addEventListener('click', () => closeModal(modalEditar));
    if (cancelarEditarBtn) cancelarEditarBtn.addEventListener('click', () => closeModal(modalEditar));

    if (modalEditar) {
        modalEditar.addEventListener('click', (e) => {
            if (e.target === modalEditar) closeModal(modalEditar);
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeModal(modalCrear);
            closeModal(modalEditar);
        }
    });

    return { openModal, closeModal };
}

/**
 * 4. Segmento: Edición de Pago
 */
function setupEditPago(config, modalControls) {
    const editBtns = document.querySelectorAll(config.selectors.btnEditTrigger);
    const formEditar = document.querySelector(config.selectors.formEdit);
    const modalEditar = document.querySelector(config.selectors.modalEdit);
    const fields = config.selectors.editFields;

    if (!editBtns.length || !formEditar) return;

    editBtns.forEach((btn) => {
        btn.addEventListener('click', function () {
            const id = this.dataset.id;
            const setVal = (sel, val) => {
                const el = document.querySelector(sel);
                if (el) el.value = val;
            };

            setVal(fields.id, id || '');
            setVal(fields.metodo, this.dataset.metodo || 'Efectivo');
            setVal(fields.monto, this.dataset.monto || '');
            setVal(fields.referencia, this.dataset.referencia || '');
            setVal(fields.estado, (this.dataset.estado || 'completado').toLowerCase());

            const baseUrl = config.endpoints.baseUrl.replace(/\/+$/, '');
            formEditar.action = `${baseUrl}/${encodeURIComponent(id)}`;

            modalControls.openModal(modalEditar);
        });
    });
}

/**
 * 5. Segmento: Eliminación parametrizada
 */
function setupDeletePago(config) {
    const deleteBtns = document.querySelectorAll(config.selectors.btnDeleteTrigger);
    const formEliminar = document.querySelector(config.selectors.formDelete);

    if (!deleteBtns.length || !formEliminar) return;

    deleteBtns.forEach((btn) => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.id;
            const cliente = this.dataset.cliente || 'este pago';

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
export function initPagos(customConfig = {}) {
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

    setupPagosFilter(config);
    setupAmountAutocomplete(config);
    const modalControls = setupModalLifecycle(config);
    setupEditPago(config, modalControls);
    setupDeletePago(config);
}

// Auto-inicialización
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('tablaPagos')) {
                initPagos();
            }
        });
    } else {
        if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('tablaPagos')) {
            initPagos();
        }
    }
}
