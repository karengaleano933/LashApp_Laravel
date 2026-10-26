/**
 * ============================================================================
 * Módulo de Servicios del Trabajador (LashApp - Trabajador)
 * ============================================================================
 * 
 * Segmentación y parametrización:
 * 1. Control del ciclo de vida de los modales (Creación y Edición de servicios).
 * 2. Poblamiento dinámico del formulario de edición y conversión de duración.
 * 3. Filtrado y búsqueda reactiva por texto y categoría en tiempo real.
 * 4. Confirmación de eliminación parametrizada con SweetAlert2.
 * 5. Procesamiento de mensajes de sesión (éxito y errores de validación).
 */

const DEFAULT_CONFIG = {
    containerSelector: '#serviciosTrabajadorContainer',
    selectors: {
        // Modal Crear
        modalNuevo: '#modalNuevoServicio',
        btnAbrirNuevo: '#abrirNuevoServicio',
        btnCerrarNuevo: '#cerrarNuevoServicio',
        btnCancelarNuevo: '#cancelarNuevoServicio',
        // Modal Editar
        modalEditar: '#modalEditarServicio',
        btnCerrarEditar: '#cerrarEditarServicio',
        btnCancelarEditar: '#cancelarEditarServicio',
        formEditar: '#formEditarServicio',
        // Campos de Edición
        fields: {
            id: '#edit_id_servicio',
            nombre: '#edit_nombre',
            categoriaId: '#edit_categoria_id',
            precio: '#edit_precio',
            duracionMinuto: '#edit_duracion_minuto',
            estado: '#edit_estado',
            descripcion: '#edit_descripcion',
        },
        // Filtros
        buscarInput: '#buscarServicio',
        filtroCat: '#filtroCategoria',
        tbody: '#serviciosTbody',
        tableRows: '#serviciosTbody tr[data-nombre]',
        infoConteo: '#infoConteoServicios',
        // Formulario Eliminar
        formEliminar: '#formEliminarServicio',
    },
    classes: {
        modalActivo: 'activo',
    },
    endpoints: {
        urlUpdate: '/servicios/:id',
        urlDestroy: '/servicios/:id',
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
            confirmButtonText: 'Aceptar',
            confirmButtonColor: currentConfig.colors.primary,
        });
    } else if (errorMsg) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMsg,
            confirmButtonText: 'Aceptar',
            confirmButtonColor: currentConfig.colors.primary,
        });
    }
}

/**
 * Control del ciclo de vida de los modales (Nuevo y Editar)
 */
function initModals(config) {
    const modalNuevo = document.querySelector(config.selectors.modalNuevo);
    const btnAbrirNuevo = document.querySelector(config.selectors.btnAbrirNuevo);
    const btnCerrarNuevo = document.querySelector(config.selectors.btnCerrarNuevo);
    const btnCancelarNuevo = document.querySelector(config.selectors.btnCancelarNuevo);

    const modalEditar = document.querySelector(config.selectors.modalEditar);
    const btnCerrarEditar = document.querySelector(config.selectors.btnCerrarEditar);
    const btnCancelarEditar = document.querySelector(config.selectors.btnCancelarEditar);

    function abrirNuevo() {
        if (modalNuevo) {
            modalNuevo.classList.add(config.classes.modalActivo);
            modalNuevo.setAttribute('aria-hidden', 'false');
        }
    }

    function cerrarNuevo() {
        if (modalNuevo) {
            modalNuevo.classList.remove(config.classes.modalActivo);
            modalNuevo.setAttribute('aria-hidden', 'true');
        }
    }

    function cerrarEditar() {
        if (modalEditar) {
            modalEditar.classList.remove(config.classes.modalActivo);
            modalEditar.setAttribute('aria-hidden', 'true');
        }
    }

    if (btnAbrirNuevo) btnAbrirNuevo.addEventListener('click', abrirNuevo);
    if (btnCerrarNuevo) btnCerrarNuevo.addEventListener('click', cerrarNuevo);
    if (btnCancelarNuevo) btnCancelarNuevo.addEventListener('click', cerrarNuevo);

    if (btnCerrarEditar) btnCerrarEditar.addEventListener('click', cerrarEditar);
    if (btnCancelarEditar) btnCancelarEditar.addEventListener('click', cerrarEditar);

    // Cierre al dar clic en el fondo oscuro
    [modalNuevo, modalEditar].forEach((modal) => {
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove(config.classes.modalActivo);
                    modal.setAttribute('aria-hidden', 'true');
                }
            });
        }
    });

    // Cierre con la tecla Escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            cerrarNuevo();
            cerrarEditar();
        }
    });
}

/**
 * Abre el modal de edición y puebla los campos con los datos del servicio
 */
export function abrirModalEditar(servicio) {
    const modalEditar = document.querySelector(currentConfig.selectors.modalEditar);
    const formEditar = document.querySelector(currentConfig.selectors.formEditar);

    if (!modalEditar || !formEditar || !servicio) return;

    const container = document.querySelector(currentConfig.containerSelector);
    const templateUpdate = container?.dataset?.urlUpdate || currentConfig.endpoints.urlUpdate;
    formEditar.action = templateUpdate.replace(':id', servicio.id);

    const f = currentConfig.selectors.fields;
    const elId = document.querySelector(f.id);
    const elNombre = document.querySelector(f.nombre);
    const elCat = document.querySelector(f.categoriaId);
    const elPrecio = document.querySelector(f.precio);
    const elDuracion = document.querySelector(f.duracionMinuto);
    const elEstado = document.querySelector(f.estado);
    const elDesc = document.querySelector(f.descripcion);

    if (elId) elId.value = servicio.id;
    if (elNombre) elNombre.value = servicio.nombre || '';
    if (elCat) elCat.value = servicio.categoria_id || '';
    if (elPrecio) elPrecio.value = Math.round(servicio.precio || 0);

    // Convertir formato HH:MM:SS a minutos para el input numérico
    let minutos = 60;
    if (servicio.duracion_minuto) {
        if (String(servicio.duracion_minuto).includes(':')) {
            const partes = String(servicio.duracion_minuto).split(':');
            minutos = (parseInt(partes[0], 10) * 60) + parseInt(partes[1], 10);
        } else {
            minutos = parseInt(servicio.duracion_minuto, 10) || 60;
        }
    }
    if (elDuracion) elDuracion.value = minutos;
    if (elEstado) elEstado.value = (servicio.estado || 'activo').toLowerCase();
    if (elDesc) elDesc.value = servicio.descripcion || '';

    modalEditar.classList.add(currentConfig.classes.modalActivo);
    modalEditar.setAttribute('aria-hidden', 'false');
}

/**
 * Filtrado en tiempo real y buscador
 */
function initFiltros(config) {
    const buscarInput = document.querySelector(config.selectors.buscarInput);
    const filtroCat = document.querySelector(config.selectors.filtroCat);
    const tbody = document.querySelector(config.selectors.tbody);
    const infoConteo = document.querySelector(config.selectors.infoConteo);

    const container = document.querySelector(config.containerSelector);
    const totalServicios = container?.dataset?.totalServicios || '0';

    function filtrar() {
        const query = (buscarInput?.value || '').toLowerCase().trim();
        const cat = (filtroCat?.value || '').toLowerCase().trim();
        const filas = tbody ? tbody.querySelectorAll('tr[data-nombre]') : [];

        let visibles = 0;

        filas.forEach((row) => {
            const nombre = (row.getAttribute('data-nombre') || '').toLowerCase();
            const categoria = (row.getAttribute('data-categoria') || '').toLowerCase();
            const precio = (row.getAttribute('data-precio') || '').toLowerCase();

            const matchQuery = !query || nombre.includes(query) || categoria.includes(query) || precio.includes(query);
            const matchCat = !cat || categoria === cat;

            if (matchQuery && matchCat) {
                row.style.display = '';
                visibles++;
            } else {
                row.style.display = 'none';
            }
        });

        if (infoConteo) {
            infoConteo.textContent = `Mostrando ${visibles} de ${totalServicios} servicios`;
        }
    }

    if (buscarInput) buscarInput.addEventListener('input', filtrar);
    if (filtroCat) filtroCat.addEventListener('change', filtrar);
}

/**
 * Confirmación con SweetAlert2 para eliminar un servicio
 */
export function confirmarEliminarServicio(idServicio) {
    if (typeof Swal === 'undefined') {
        if (confirm('¿Eliminar servicio?\nEsta acción no se puede deshacer.')) {
            ejecutarEliminacion(idServicio);
        }
        return;
    }

    Swal.fire({
        title: '¿Eliminar servicio?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: currentConfig.colors.danger,
        cancelButtonColor: currentConfig.colors.neutral,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            ejecutarEliminacion(idServicio);
        }
    });
}

function ejecutarEliminacion(idServicio) {
    const form = document.querySelector(currentConfig.selectors.formEliminar);
    const container = document.querySelector(currentConfig.containerSelector);
    if (form) {
        const templateDestroy = container?.dataset?.urlDestroy || currentConfig.endpoints.urlDestroy;
        form.action = templateDestroy.replace(':id', idServicio);
        form.submit();
    }
}

/**
 * Inicializador principal del módulo
 */
export function initServiciosTrabajador(customConfig = {}) {
    const container = document.querySelector(customConfig.containerSelector || DEFAULT_CONFIG.containerSelector)
                   || document.querySelector('.page-content');
    if (!container) return;

    currentConfig = {
        ...DEFAULT_CONFIG,
        ...customConfig,
        endpoints: {
            urlUpdate: container.dataset.urlUpdate || DEFAULT_CONFIG.endpoints.urlUpdate,
            urlDestroy: container.dataset.urlDestroy || DEFAULT_CONFIG.endpoints.urlDestroy,
            ...(customConfig.endpoints || {}),
        }
    };

    handleSessionAlerts(container);
    initModals(currentConfig);
    initFiltros(currentConfig);
}

// Retrocompatibilidad global para llamadas inline en Blade (onclick)
window.abrirModalEditar = abrirModalEditar;
window.confirmarEliminarServicio = confirmarEliminarServicio;
window.initServiciosTrabajador = initServiciosTrabajador;

// Inicialización automática al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    initServiciosTrabajador();
});
