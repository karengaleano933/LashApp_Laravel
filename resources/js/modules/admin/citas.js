/**
 * ============================================================================
 * Módulo de Gestión de Citas (LashApp - Admin)
 * ============================================================================
 *
 * Segmentación de responsabilidades:
 * 1. Inicialización y parametrización de la tabla interactiva (DataTables).
 * 2. Filtros dinámicos reactivos (búsqueda en tiempo real, profesional, estado).
 * 3. Acciones de cita parametrizadas (confirmar completar, confirmar cancelar).
 * 4. Renderizado reactivo del mini-calendario mensual.
 * 5. Gestión de ciclo de vida del modal de Nueva Cita.
 */

import { confirmAction, notify } from '../../common/alerts.js';

const DEFAULT_CONFIG = {
    containerSelector: '#citasContainer',
    selectors: {
        table: '#citasTable',
        searchInput: '#buscarCita',
        professionalFilter: '#filtroProfesional',
        statusFilter: '#filtroEstado',
        btnComplete: '.btn-completar-cita',
        btnCancel: '.btn-cancelar-cita',
        // Calendario
        calGrid: '#calGrid',
        calLabel: '#calMesAnio',
        calPrevBtn: '#calPrev',
        calNextBtn: '#calNext',
        // Modal Nueva Cita
        modalNew: '#modalNuevaCita',
        btnOpenNew: '#abrirNuevaCita',
        btnCloseNew: '#cerrarNuevaCita',
        btnCancelNew: '#cancelarNuevaCita',
    },
    endpoints: {
        completeUrlTemplate: '/admin/citas/:id/completar',
        cancelUrlTemplate: '/admin/citas/:id/cancelar',
    },
    messages: {
        completeTitle: '¿Marcar cita como realizada?',
        completeText: 'La cita quedará como completada y la clienta podrá calificar su experiencia.',
        completeConfirmBtn: 'Sí, realizada',
        cancelTitle: '¿Cancelar esta cita?',
        cancelText: 'Esta acción actualizará el estado de la cita.',
        cancelConfirmBtn: 'Sí, cancelar',
    }
};

/**
 * 1. Segmento: Inicialización y filtros de DataTables
 */
function setupCitasTable(config) {
    if (typeof window.jQuery === 'undefined' || !window.jQuery.fn.DataTable) {
        return null;
    }

    const $ = window.jQuery;
    const tableEl = $(config.selectors.table);

    if (!tableEl.length) return null;

    if ($.fn.DataTable.isDataTable(config.selectors.table)) {
        tableEl.DataTable().destroy();
    }
    $.fn.dataTable.ext.errMode = 'none';

    const dt = tableEl.DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
            emptyTable: 'No hay citas próximas registradas',
            zeroRecords: 'No se encontraron citas coincidentes'
        },
        responsive: true,
        dom: 'rt',
        searching: true,
        lengthChange: false,
        info: false,
        paging: false
    });

    // Filtro por texto
    const searchInput = document.querySelector(config.selectors.searchInput);
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            dt.search(this.value).draw();
        });
    }

    // Filtro por profesional (Columna 4)
    $(config.selectors.professionalFilter).on('change', function () {
        dt.column(4).search(this.value).draw();
    });

    // Filtro por estado (Columna 5)
    $(config.selectors.statusFilter).on('change', function () {
        dt.column(5).search(this.value).draw();
    });

    return dt;
}

/**
 * 2. Segmento: Acciones de Cita (Completar / Cancelar)
 */
function setupCitasActions(config) {
    window.confirmarCompletar = async function (id) {
        const confirmed = await confirmAction({
            title: config.messages.completeTitle,
            text: config.messages.completeText,
            icon: 'question',
            confirmButtonColor: '#059669',
            confirmButtonText: config.messages.completeConfirmBtn,
            cancelButtonText: 'Volver',
        });

        if (confirmed) {
            const url = config.endpoints.completeUrlTemplate.replace(':id', id);
            window.location.href = url;
        }
    };

    window.confirmarCancelar = async function (id) {
        const confirmed = await confirmAction({
            title: config.messages.cancelTitle,
            text: config.messages.cancelText,
            icon: 'warning',
            isDestructive: true,
            confirmButtonText: config.messages.cancelConfirmBtn,
            cancelButtonText: 'No'
        });

        if (confirmed) {
            const url = config.endpoints.cancelUrlTemplate.replace(':id', id);
            window.location.href = url;
        }
    };

    window.cancelarSeleccionadas = function () {
        notify({
            icon: 'info',
            title: 'Selecciona las citas',
            text: 'Marca las citas que deseas cancelar en la tabla.'
        });
    };

    document.addEventListener('click', (e) => {
        const targetComplete = e.target.closest(config.selectors.btnComplete);
        if (targetComplete) {
            const id = targetComplete.dataset.id;
            if (id) window.confirmarCompletar(id);
        }

        const targetCancel = e.target.closest(config.selectors.btnCancel);
        if (targetCancel) {
            const id = targetCancel.dataset.id;
            if (id) window.confirmarCancelar(id);
        }
    });
}

/**
 * 3. Segmento: Mini Calendario Mensual
 */
function setupCalendar(config) {
    const grid = document.querySelector(config.selectors.calGrid);
    const label = document.querySelector(config.selectors.calLabel);
    if (!grid || !label) return;

    const meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    let calYear = new Date().getFullYear();
    let calMonth = new Date().getMonth();

    function renderCal() {
        label.textContent = `${meses[calMonth]} ${calYear}`;
        grid.innerHTML = '';

        const firstDay = (new Date(calYear, calMonth, 1).getDay() + 6) % 7; // Lunes = 0
        const days = new Date(calYear, calMonth + 1, 0).getDate();
        const today = new Date();

        for (let i = 0; i < firstDay; i++) {
            const el = document.createElement('div');
            el.className = 'cal-cell cal-empty';
            grid.appendChild(el);
        }

        for (let d = 1; d <= days; d++) {
            const el = document.createElement('div');
            const isToday = today.getFullYear() === calYear && today.getMonth() === calMonth && today.getDate() === d;
            el.className = 'cal-cell' + (isToday ? ' cal-today' : '');
            el.textContent = d;
            grid.appendChild(el);
        }
    }

    const prevBtn = document.querySelector(config.selectors.calPrevBtn);
    const nextBtn = document.querySelector(config.selectors.calNextBtn);

    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            calMonth--;
            if (calMonth < 0) { calMonth = 11; calYear--; }
            renderCal();
        });
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            calMonth++;
            if (calMonth > 11) { calMonth = 0; calYear++; }
            renderCal();
        });
    }

    renderCal();
}

/**
 * 4. Segmento: Modal Nueva Cita
 */
function setupCreateCitaModal(config) {
    const modal = document.querySelector(config.selectors.modalNew);
    const abrir = document.querySelector(config.selectors.btnOpenNew);
    const cerrar = document.querySelector(config.selectors.btnCloseNew);
    const cancelar = document.querySelector(config.selectors.btnCancelNew);

    if (!modal) return;

    function abrirModal() {
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }

    function cerrarModal() {
        modal.classList.remove('activo');
        document.body.style.overflow = '';
    }

    if (abrir) abrir.addEventListener('click', abrirModal);
    if (cerrar) cerrar.addEventListener('click', cerrarModal);
    if (cancelar) cancelar.addEventListener('click', cerrarModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) cerrarModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('activo')) {
            cerrarModal();
        }
    });
}

/**
 * Inicializador principal del módulo de citas
 */
export function initCitas(customConfig = {}) {
    const container = document.querySelector(customConfig.containerSelector || DEFAULT_CONFIG.containerSelector);
    const domCompleteUrl = container?.dataset?.completeUrl;
    const domCancelUrl = container?.dataset?.cancelUrl;

    const config = {
        ...DEFAULT_CONFIG,
        ...customConfig,
        endpoints: {
            ...DEFAULT_CONFIG.endpoints,
            completeUrlTemplate: domCompleteUrl || customConfig.endpoints?.completeUrlTemplate || DEFAULT_CONFIG.endpoints.completeUrlTemplate,
            cancelUrlTemplate: domCancelUrl || customConfig.endpoints?.cancelUrlTemplate || DEFAULT_CONFIG.endpoints.cancelUrlTemplate,
            ...customConfig.endpoints,
        }
    };

    setupCitasTable(config);
    setupCitasActions(config);
    setupCalendar(config);
    setupCreateCitaModal(config);
}

// Auto-inicialización
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('citasTable')) {
                initCitas();
            }
        });
    } else {
        if (document.querySelector(DEFAULT_CONFIG.containerSelector) || document.getElementById('citasTable')) {
            initCitas();
        }
    }
}
