/**
 * Módulo: Dashboard de Cliente
 * LashApp - Oh La Lash Studio
 *
 * Funcionalidad:
 * - Apertura y cierre de modal de horarios / agenda
 * - Filtrado en tiempo real de tarjetas de agenda
 * - Manejo de backdrop y scroll lock
 * - Notificación de bienvenida / confirmación de sesión exitosa
 */

export function initDashboardCliente() {
    const container = document.getElementById('dashboardClienteContainer') || document.querySelector('.page-content');
    if (container && container.dataset.sessionSuccess) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Operación Exitosa!',
                text: container.dataset.sessionSuccess,
                icon: 'success',
                confirmButtonColor: '#7c1db8',
                confirmButtonText: 'Entendido'
            });
        }
    }

    const modalAgenda = document.getElementById('modalAgenda');
    if (modalAgenda) {
        modalAgenda.addEventListener('click', (e) => {
            if (e.target === modalAgenda) cerrarModalAgenda();
        });
    }

    const inputFiltro = document.getElementById('filtroAgenda');
    if (inputFiltro) {
        inputFiltro.addEventListener('input', filtrarAgendas);
    }
}

export function abrirModalAgenda() {
    const modal = document.getElementById('modalAgenda');
    if (modal) {
        modal.classList.add('activo');
        document.body.style.overflow = 'hidden';
    }
}

export function cerrarModalAgenda() {
    const modal = document.getElementById('modalAgenda');
    if (modal) {
        modal.classList.remove('activo');
        document.body.style.overflow = '';
    }
}

export function filtrarAgendas() {
    const input = document.getElementById('filtroAgenda');
    if (!input) return;
    const query = input.value.toLowerCase().trim();
    const cards = document.querySelectorAll('#contenedorAgendas .agenda-card');

    cards.forEach(card => {
        const searchData = (card.getAttribute('data-search') || '').toLowerCase();
        card.style.display = (!query || searchData.includes(query)) ? 'flex' : 'none';
    });
}

// Retrocompatibilidad global
window.abrirModalAgenda = abrirModalAgenda;
window.cerrarModalAgenda = cerrarModalAgenda;
window.filtrarAgendas = filtrarAgendas;

document.addEventListener('DOMContentLoaded', initDashboardCliente);
