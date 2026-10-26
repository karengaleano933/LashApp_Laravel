/**
 * ============================================================================
 * Módulo de Citas del Trabajador (LashApp - Trabajador)
 * ============================================================================
 * 
 * Segmentación y parametrización:
 * 1. Control del ciclo de vida del modal de nueva cita.
 * 2. Filtrado reactivo en tiempo real y paginación client-side de la tabla de citas.
 * 3. Mini-calendario interactivo mensual.
 * 4. Diálogos SweetAlert2 parametrizados para completar y cancelar citas.
 * 5. Procesamiento de mensajes de sesión (éxito y error).
 */

const DEFAULT_CONFIG = {
    containerSelector: '#citasTrabajadorContainer',
    selectors: {
        modalNueva: '#modalNuevaCita',
        btnAbrirModal: '#abrirNuevaCita',
        btnCerrarModal: '#cerrarNuevaCita',
        btnCancelarModal: '#cancelarNuevaCita',
        buscarTabla: '#buscarTabla',
        buscarCita: '#buscarCita',
        filtroProf: '#filtroProfesional',
        filtroEst: '#filtroEstado',
        filtroFec: '#filtroFecha',
        selectRegistros: '#selectRegistros',
        infoPaginacion: '#infoPaginacion',
        tbody: '#citasTbody',
        tableRows: '#citasTbody tr[data-cliente]',
        // Calendario
        calGrid: '#calGrid',
        calMesAnio: '#calMesAnio',
        calPrev: '#calPrev',
        calNext: '#calNext',
    },
    classes: {
        modalActivo: 'activo',
    },
    endpoints: {
        urlCompletar: '/citas/:id/completar',
        urlCancelar: '/citas/:id/cancelar',
    },
    colors: {
        primary: '#7c1db8',
        success: '#059669',
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
 * Inicializa el modal de nueva cita
 */
function initModal(config) {
    const modal = document.querySelector(config.selectors.modalNueva);
    const btnAbrir = document.querySelector(config.selectors.btnAbrirModal);
    const btnCerrar = document.querySelector(config.selectors.btnCerrarModal);
    const btnCancelar = document.querySelector(config.selectors.btnCancelarModal);

    function abrir() {
        if (modal) {
            modal.classList.add(config.classes.modalActivo);
            modal.setAttribute('aria-hidden', 'false');
        }
    }

    function cerrar() {
        if (modal) {
            modal.classList.remove(config.classes.modalActivo);
            modal.setAttribute('aria-hidden', 'true');
        }
    }

    if (btnAbrir) btnAbrir.addEventListener('click', abrir);
    if (btnCerrar) btnCerrar.addEventListener('click', cerrar);
    if (btnCancelar) btnCancelar.addEventListener('click', cerrar);

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) cerrar();
        });
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal && modal.classList.contains(config.classes.modalActivo)) {
            cerrar();
        }
    });
}

/**
 * Inicializa el filtrado reactivo y la paginación client-side de la tabla
 */
function initFiltros(config) {
    const buscarTabla = document.querySelector(config.selectors.buscarTabla);
    const buscarCita = document.querySelector(config.selectors.buscarCita);
    const filtroProf = document.querySelector(config.selectors.filtroProf);
    const filtroEst = document.querySelector(config.selectors.filtroEst);
    const filtroFec = document.querySelector(config.selectors.filtroFec);
    const selectRegistros = document.querySelector(config.selectors.selectRegistros);
    const infoPaginacion = document.querySelector(config.selectors.infoPaginacion);
    const tbody = document.querySelector(config.selectors.tbody);

    function getFilas() {
        return tbody ? Array.from(tbody.querySelectorAll('tr[data-cliente]')) : [];
    }

    function filtrar() {
        const queryT = (buscarTabla ? buscarTabla.value : '').toLowerCase().trim();
        const queryC = (buscarCita ? buscarCita.value : '').toLowerCase().trim();
        const query = queryT || queryC;

        const prof = (filtroProf ? filtroProf.value : '').toLowerCase().trim();
        const est = (filtroEst ? filtroEst.value : '').toLowerCase().trim();
        const fec = (filtroFec ? filtroFec.value : '').trim();
        const limite = selectRegistros ? parseInt(selectRegistros.value, 10) : 10;

        let coincidentes = 0;
        const filas = getFilas();

        filas.forEach((row) => {
            const cliente = (row.getAttribute('data-cliente') || '').toLowerCase();
            const servicio = (row.getAttribute('data-servicio') || '').toLowerCase();
            const profesional = (row.getAttribute('data-profesional') || '').toLowerCase();
            const estado = (row.getAttribute('data-estado') || '').toLowerCase();
            const fecha = (row.getAttribute('data-fecha') || '').trim();

            const matchQuery = !query || cliente.includes(query) || servicio.includes(query);
            const matchProf = !prof || profesional.includes(prof);
            const matchEst = !est || estado.includes(est);
            const matchFec = !fec || fecha === fec;

            if (matchQuery && matchProf && matchEst && matchFec) {
                coincidentes++;
                row.style.display = coincidentes <= limite ? '' : 'none';
            } else {
                row.style.display = 'none';
            }
        });

        if (infoPaginacion) {
            const mostrados = Math.min(coincidentes, limite);
            infoPaginacion.textContent = `Mostrando ${mostrados} de ${coincidentes} registros`;
        }
    }

    if (buscarTabla) buscarTabla.addEventListener('input', filtrar);
    if (buscarCita) buscarCita.addEventListener('input', filtrar);
    if (filtroProf) filtroProf.addEventListener('change', filtrar);
    if (filtroEst) filtroEst.addEventListener('change', filtrar);
    if (filtroFec) filtroFec.addEventListener('change', filtrar);
    if (selectRegistros) selectRegistros.addEventListener('change', filtrar);

    // Ejecutar filtro inicial
    filtrar();

    return { filtrar };
}

/**
 * Inicializa el mini calendario
 */
function initCalendario(config) {
    const calGrid = document.querySelector(config.selectors.calGrid);
    const calMesAnio = document.querySelector(config.selectors.calMesAnio);
    const calPrev = document.querySelector(config.selectors.calPrev);
    const calNext = document.querySelector(config.selectors.calNext);

    if (!calGrid || !calMesAnio) return;

    let currDate = new Date();
    const nombresMeses = [
        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
    ];

    function renderCalendar() {
        const year = currDate.getFullYear();
        const month = currDate.getMonth();

        calMesAnio.textContent = `${nombresMeses[month]} ${year}`;

        const firstDay = (new Date(year, month, 1).getDay() + 6) % 7;
        const totalDays = new Date(year, month + 1, 0).getDate();

        calGrid.innerHTML = '';

        for (let i = 0; i < firstDay; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'cal-cell cal-empty';
            calGrid.appendChild(emptyCell);
        }

        const today = new Date();

        for (let d = 1; d <= totalDays; d++) {
            const dayCell = document.createElement('div');
            const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === d;
            dayCell.className = 'cal-cell' + (isToday ? ' cal-today' : '');
            dayCell.textContent = d;
            calGrid.appendChild(dayCell);
        }
    }

    renderCalendar();

    if (calPrev) calPrev.addEventListener('click', () => {
        currDate.setMonth(currDate.getMonth() - 1);
        renderCalendar();
    });

    if (calNext) calNext.addEventListener('click', () => {
        currDate.setMonth(currDate.getMonth() + 1);
        renderCalendar();
    });
}

/**
 * Restablece los filtros de búsqueda
 */
export function limpiarFiltros() {
    const buscarTabla = document.querySelector(currentConfig.selectors.buscarTabla);
    const buscarCita = document.querySelector(currentConfig.selectors.buscarCita);
    const filtroProf = document.querySelector(currentConfig.selectors.filtroProf);
    const filtroEst = document.querySelector(currentConfig.selectors.filtroEst);
    const filtroFec = document.querySelector(currentConfig.selectors.filtroFec);
    const selectRegistros = document.querySelector(currentConfig.selectors.selectRegistros);

    if (buscarTabla) buscarTabla.value = '';
    if (buscarCita) buscarCita.value = '';
    if (filtroProf) filtroProf.value = '';
    if (filtroEst) filtroEst.value = '';
    if (filtroFec) filtroFec.value = '';
    if (selectRegistros) selectRegistros.value = '10';

    const buscarInput = buscarTabla || buscarCita;
    if (buscarInput) {
        buscarInput.dispatchEvent(new Event('input'));
    }
}

/**
 * Confirmación para marcar cita como realizada
 */
export function confirmarCompletar(idCita) {
    if (typeof Swal === 'undefined') {
        if (confirm('¿Marcar cita como realizada?')) {
            navegarAccion('urlCompletar', idCita);
        }
        return;
    }

    Swal.fire({
        title: '¿Marcar cita como realizada?',
        text: 'La cita cambiará a estado Completada y la clienta podrá calificar el servicio.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: currentConfig.colors.success,
        cancelButtonColor: currentConfig.colors.neutral,
        confirmButtonText: 'Sí, realizada',
        cancelButtonText: 'Volver',
    }).then((result) => {
        if (result.isConfirmed) {
            navegarAccion('urlCompletar', idCita);
        }
    });
}

/**
 * Confirmación para cancelar una cita
 */
export function confirmarCancelar(idCita) {
    if (typeof Swal === 'undefined') {
        if (confirm('¿Cancelar cita?')) {
            navegarAccion('urlCancelar', idCita);
        }
        return;
    }

    Swal.fire({
        title: '¿Cancelar cita?',
        text: 'La cita cambiará a estado Cancelada.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: currentConfig.colors.danger,
        cancelButtonColor: currentConfig.colors.neutral,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Volver',
    }).then((result) => {
        if (result.isConfirmed) {
            navegarAccion('urlCancelar', idCita);
        }
    });
}

/**
 * Redirige a la URL de acción parametrizada
 */
function navegarAccion(endpointKey, idCita) {
    const container = document.querySelector(currentConfig.containerSelector);
    let template = container?.dataset?.[endpointKey] || currentConfig.endpoints[endpointKey];
    if (template) {
        window.location.href = template.replace(':id', idCita);
    }
}

/**
 * Inicializador principal del módulo
 */
export function initCitasTrabajador(customConfig = {}) {
    const container = document.querySelector(customConfig.containerSelector || DEFAULT_CONFIG.containerSelector);
    if (!container) return;

    currentConfig = {
        ...DEFAULT_CONFIG,
        ...customConfig,
        endpoints: {
            urlCompletar: container.dataset.urlCompletar || DEFAULT_CONFIG.endpoints.urlCompletar,
            urlCancelar: container.dataset.urlCancelar || DEFAULT_CONFIG.endpoints.urlCancelar,
            ...(customConfig.endpoints || {}),
        }
    };

    handleSessionAlerts(container);
    initModal(currentConfig);
    initFiltros(currentConfig);
    initCalendario(currentConfig);
}

// Retrocompatibilidad global para llamadas inline en Blade (onclick)
window.limpiarFiltros = limpiarFiltros;
window.confirmarCompletar = confirmarCompletar;
window.confirmarCancelar = confirmarCancelar;
window.initCitasTrabajador = initCitasTrabajador;

// Inicialización automática al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    initCitasTrabajador();
});
