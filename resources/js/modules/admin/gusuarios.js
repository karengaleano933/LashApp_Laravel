/**
 * ============================================================================
 * Módulo de Gestión de Usuarios (LashApp - Admin)
 * ============================================================================
 *
 * Segmentación de responsabilidades:
 * 1. Búsqueda y filtrado reactivo de usuarios por nombre/rol.
 * 2. Alternancia de visibilidad de contraseñas (Crear y Editar).
 * 3. Ciclo de vida y accesibilidad de modales (Crear y Editar).
 * 4. Carga y mapeo dinámico de datos al formulario de edición.
 * 5. Confirmación y eliminación destructiva parametrizada con SweetAlert2.
 */

import { confirmAction } from '../../common/alerts.js';

const DEFAULT_CONFIG = {
    containerSelector: '#usuariosContainer',
    selectors: {
        searchInput: '#buscarUsuario',
        roleFilter: '#filtroRol',
        tableRows: '#tablaUsuarios tbody tr',
        // Passwords
        togglePassCreate: '#mostrarPassword',
        inputPassCreate: '#passwordUsuario',
        togglePassEdit: '#mostrarPasswordEdit',
        inputPassEdit: '#edit_password',
        // Modal Crear
        modalCreate: '#modalCrearUsuario',
        btnOpenCreate: '#abrirNuevoUsuario',
        btnCloseCreate: '#cerrarModalUsuario',
        btnCancelCreate: '#cancelarModalUsuario',
        // Modal Editar
        modalEdit: '#modalEditarUsuario',
        btnCloseEdit: '#cerrarModalEditar',
        btnCancelEdit: '#cancelarModalEditar',
        formEdit: '#formEditarUsuario',
        btnEditTrigger: '.btn-editar-usuario',
        // Campos Editar
        editFields: {
            nombre: '#edit_nombre',
            apellido: '#edit_apellido',
            documento: '#edit_documento',
            telefono: '#edit_telefono',
            correo: '#edit_correo',
            rol: '#edit_id_rol',
            estado: '#edit_estado',
            password: '#edit_password',
        },
        // Eliminar
        formDelete: '#formEliminarUsuario',
        btnDeleteTrigger: '.btn-eliminar-usuario',
    },
    classes: {
        modalActive: 'activo',
    },
    endpoints: {
        baseUrl: '/admin/usuarios',
    },
    messages: {
        deleteTitle: '¿Eliminar usuario?',
        deleteText: (nombre) => `¿Estás seguro de eliminar a "${nombre}"? Esta acción no se puede deshacer.`,
        deleteConfirmBtn: 'Sí, eliminar',
        deleteCancelBtn: 'Cancelar',
    }
};

/**
 * 1. Segmento: Filtrado en tiempo real
 */
function setupUserFilter(config) {
    const buscar = document.querySelector(config.selectors.searchInput);
    const filtro = document.querySelector(config.selectors.roleFilter);
    const filas = document.querySelectorAll(config.selectors.tableRows);

    if (!filas.length) return;

    function filtrarUsuarios() {
        const texto = (buscar ? buscar.value : '').toLowerCase().trim();
        const rol = (filtro ? filtro.value : '').toLowerCase().trim();

        filas.forEach((fila) => {
            const contenido = fila.textContent.toLowerCase();
            const rolFila = (fila.dataset.rol || '').toLowerCase();

            const coincideTexto = contenido.includes(texto);
            const coincideRol = !rol || rolFila.includes(rol);

            fila.style.display = (coincideTexto && coincideRol) ? '' : 'none';
        });
    }

    if (buscar) buscar.addEventListener('input', filtrarUsuarios);
    if (filtro) filtro.addEventListener('change', filtrarUsuarios);
}

/**
 * 2. Segmento: Visibilidad de contraseñas
 */
function setupPasswordToggles(config) {
    const bindToggle = (btnSelector, inputSelector) => {
        const btn = document.querySelector(btnSelector);
        const input = document.querySelector(inputSelector);
        if (btn && input) {
            btn.addEventListener('click', () => {
                if (input.type === 'password') {
                    input.type = 'text';
                    btn.classList.remove('fa-eye');
                    btn.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    btn.classList.remove('fa-eye-slash');
                    btn.classList.add('fa-eye');
                }
            });
        }
    };

    bindToggle(config.selectors.togglePassCreate, config.selectors.inputPassCreate);
    bindToggle(config.selectors.togglePassEdit, config.selectors.inputPassEdit);
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

    if (abrirBtn) {
        abrirBtn.addEventListener('click', () => {
            openModal(modalCrear);
            const firstInput = modalCrear?.querySelector('input[name="name"]');
            if (firstInput) firstInput.focus();
        });
    }
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
 * 4. Segmento: Edición de Usuario
 */
function setupEditUser(config, modalControls) {
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

            setVal(fields.nombre, this.dataset.nombre || '');
            setVal(fields.apellido, this.dataset.apellido || '');
            setVal(fields.documento, this.dataset.documento || '');
            setVal(fields.telefono, this.dataset.telefono || '');
            setVal(fields.correo, this.dataset.correo || '');
            setVal(fields.rol, this.dataset.rol || '1');
            setVal(fields.estado, (this.dataset.estado || 'activo').toLowerCase());
            setVal(fields.password, '');

            const baseUrl = config.endpoints.baseUrl.replace(/\/+$/, '');
            formEditar.action = `${baseUrl}/${encodeURIComponent(id)}`;

            modalControls.openModal(modalEditar);
        });
    });
}

/**
 * 5. Segmento: Eliminación parametrizada
 */
function setupDeleteUser(config) {
    const deleteBtns = document.querySelectorAll(config.selectors.btnDeleteTrigger);
    const formEliminar = document.querySelector(config.selectors.formDelete);

    if (!deleteBtns.length || !formEliminar) return;

    deleteBtns.forEach((btn) => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre || 'este usuario';

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
                formEliminar.action = `${baseUrl}/${encodeURIComponent(id)}`;
                formEliminar.submit();
            }
        });
    });
}

/**
 * Inicializador principal
 */
export function initUsuarios(customConfig = {}) {
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

    setupUserFilter(config);
    setupPasswordToggles(config);
    const modalControls = setupModalLifecycle(config);
    setupEditUser(config, modalControls);
    setupDeleteUser(config);
}

// Auto-inicialización
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('tablaUsuarios')) {
                initUsuarios();
            }
        });
    } else {
        if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('tablaUsuarios')) {
            initUsuarios();
        }
    }
}
