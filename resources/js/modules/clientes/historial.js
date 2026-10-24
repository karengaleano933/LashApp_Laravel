/**
 * Módulo: Historial de Cliente
 * LashApp - Oh La Lash Studio
 *
 * Funcionalidad:
 * - Filtrado interactivo por pestañas principales (Todos, Citas, Servicios, Pagos)
 * - Sincronización bidireccional entre pestañas y selectores de filtro
 * - Filtrado combinado en vivo por fecha, servicio y especialista
 * - Ordenación dinámica en el DOM (recientes, antiguos, mayor/menor precio)
 * - Reseteo de filtros
 * - Diálogo de confirmación SweetAlert2 y generación de reporte / impresión en PDF
 */

let tabActiva = 'todos';

export function initHistorial() {
    const filterFecha = document.getElementById('filterFechaInput');
    if (filterFecha) {
        filterFecha.addEventListener('input', aplicarFiltrosYOrden);
    }

    const filterServicio = document.getElementById('filterServicioSelect');
    if (filterServicio) {
        filterServicio.addEventListener('change', aplicarFiltrosYOrden);
    }

    const filterProf = document.getElementById('filterProfesionalSelect');
    if (filterProf) {
        filterProf.addEventListener('change', aplicarFiltrosYOrden);
    }

    const filterTipo = document.getElementById('filterTipoSelect');
    if (filterTipo) {
        filterTipo.addEventListener('change', function() {
            sincronizarTipoConTab(this.value);
        });
    }

    const orderSelect = document.getElementById('ordenarHistorialSelect');
    if (orderSelect) {
        orderSelect.addEventListener('change', aplicarFiltrosYOrden);
    }
}

export function filtrarHistorialTab(tipo, btn) {
    tabActiva = tipo;
    document.querySelectorAll('.hist-tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const tipoSelect = document.getElementById('filterTipoSelect');
    if (tipoSelect) tipoSelect.value = tipo;

    aplicarFiltrosYOrden();
}

export function sincronizarTipoConTab(tipo) {
    tabActiva = tipo;
    document.querySelectorAll('.hist-tab-btn').forEach(b => {
        if (b.getAttribute('data-tab') === tipo) {
            b.classList.add('active');
        } else {
            b.classList.remove('active');
        }
    });
    aplicarFiltrosYOrden();
}

export function aplicarFiltrosYOrden() {
    const container = document.getElementById('histListContainer');
    const emptyState = document.getElementById('histEmptyState');
    if (!container) return;

    const cards = Array.from(container.querySelectorAll('.hist-card-item'));
    const fechaQuery = (document.getElementById('filterFechaInput')?.value || '').toLowerCase().trim();
    const servicioFiltro = document.getElementById('filterServicioSelect')?.value || 'todos';
    const profesionalFiltro = document.getElementById('filterProfesionalSelect')?.value || 'todas';
    const orden = document.getElementById('ordenarHistorialSelect')?.value || 'recientes';

    let visibles = 0;

    cards.forEach(card => {
        const cardTipo = card.getAttribute('data-tipo') || '';
        const cardServicio = card.getAttribute('data-servicio') || '';
        const cardProfesional = card.getAttribute('data-profesional') || '';
        const cardFecha = card.getAttribute('data-fecha') || '';
        const cardText = card.textContent.toLowerCase();

        // 1. Condición de Pestaña / Tipo
        let matchTab = true;
        if (tabActiva === 'citas') {
            matchTab = card.getAttribute('data-tiene-cita') === '1' || cardTipo === 'citas';
        } else if (tabActiva === 'servicios') {
            matchTab = card.getAttribute('data-tiene-servicio') === '1' || cardTipo === 'servicios';
        } else if (tabActiva === 'pagos') {
            matchTab = card.getAttribute('data-tiene-pago') === '1' || cardTipo === 'pagos';
        }

        // 2. Condición de Fecha
        const matchFecha = !fechaQuery || cardFecha.includes(fechaQuery) || cardText.includes(fechaQuery);

        // 3. Condición de Servicio
        const matchServicio = (servicioFiltro === 'todos' || cardServicio.includes(servicioFiltro) || servicioFiltro.includes(cardServicio));

        // 4. Condición de Profesional
        const matchProfesional = (profesionalFiltro === 'todas' || cardProfesional.includes(profesionalFiltro) || profesionalFiltro.includes(cardProfesional));

        if (matchTab && matchFecha && matchServicio && matchProfesional) {
            card.style.display = 'flex';
            visibles++;
        } else {
            card.style.display = 'none';
        }
    });

    cards.sort((a, b) => {
        const fechaA = new Date(a.getAttribute('data-fecha') || '1970-01-01').getTime();
        const fechaB = new Date(b.getAttribute('data-fecha') || '1970-01-01').getTime();
        const precioA = parseFloat(a.getAttribute('data-precio') || 0);
        const precioB = parseFloat(b.getAttribute('data-precio') || 0);

        if (orden === 'recientes') return fechaB - fechaA;
        if (orden === 'antiguos') return fechaA - fechaB;
        if (orden === 'mayor_precio') return precioB - precioA;
        if (orden === 'menor_precio') return precioA - precioB;
        return 0;
    });

    cards.forEach(card => container.appendChild(card));

    if (emptyState) {
        emptyState.style.display = (visibles === 0) ? 'block' : 'none';
    }
}

export function resetFiltros() {
    tabActiva = 'todos';
    document.querySelectorAll('.hist-tab-btn').forEach(b => {
        if (b.getAttribute('data-tab') === 'todos') b.classList.add('active');
        else b.classList.remove('active');
    });

    const fechaInput = document.getElementById('filterFechaInput');
    if (fechaInput) fechaInput.value = '';

    const tipoSelect = document.getElementById('filterTipoSelect');
    if (tipoSelect) tipoSelect.value = 'todos';

    const servSelect = document.getElementById('filterServicioSelect');
    if (servSelect) servSelect.value = 'todos';

    const profSelect = document.getElementById('filterProfesionalSelect');
    if (profSelect) profSelect.value = 'todas';

    const ordSelect = document.getElementById('ordenarHistorialSelect');
    if (ordSelect) ordSelect.value = 'recientes';

    aplicarFiltrosYOrden();
}

export function descargarHistorialPDF() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Generando Reporte PDF',
            text: 'Se abrirá la vista de impresión optimizada para guardar tu historial en PDF.',
            icon: 'info',
            confirmButtonColor: '#7c1db8',
            confirmButtonText: 'Imprimir / Guardar PDF',
            showCancelButton: true,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.print();
            }
        });
    } else {
        window.print();
    }
}

// Retrocompatibilidad global
window.filtrarHistorialTab = filtrarHistorialTab;
window.sincronizarTipoConTab = sincronizarTipoConTab;
window.aplicarFiltrosYOrden = aplicarFiltrosYOrden;
window.resetFiltros = resetFiltros;
window.descargarHistorialPDF = descargarHistorialPDF;

document.addEventListener('DOMContentLoaded', initHistorial);
