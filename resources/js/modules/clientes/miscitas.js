/**
 * Módulo: Mis Citas (Cliente)
 * LashApp - Oh La Lash Studio
 *
 * Funcionalidad:
 * - Filtrado de citas por pestañas de estado (Próximas, Pendientes, Completadas, Canceladas)
 * - Mini calendario interactivo con resaltado de días con citas agendadas
 * - Modal de detalles de cita con desglose
 * - Confirmación SweetAlert2 para cancelación de cita
 * - Manejo de mensajes de éxito de sesión
 */

let fechasCitasBD = [];
let mesCal = (new Date()).getMonth();
let anioCal = (new Date()).getFullYear();

const nombresMesesCal = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

export function initMisCitas() {
    const container = document.getElementById('misCitasContainer') || document.querySelector('.page-content');
    if (container) {
        if (container.dataset.fechasCitas) {
            try {
                fechasCitasBD = JSON.parse(container.dataset.fechasCitas);
            } catch (e) {
                fechasCitasBD = [];
            }
        }

        if (container.dataset.sessionSuccess) {
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
    }

    renderizarMiniCalendario();

    const modalDetEl = document.getElementById('modalDetallesCita');
    if (modalDetEl) {
        modalDetEl.addEventListener('click', (e) => {
            if (e.target === modalDetEl) cerrarModalDetalle();
        });
    }
}

export function cambiarPestanaCitas(tab, btn) {
    document.querySelectorAll('.miscitas-tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const titulos = {
        'proximas': 'Próximas citas',
        'pendientes': 'Citas pendientes',
        'completadas': 'Citas completadas',
        'canceladas': 'Citas canceladas'
    };
    const tituloEl = document.getElementById('tituloPestanaActiva');
    if (tituloEl && titulos[tab]) {
        tituloEl.textContent = titulos[tab];
    }

    const cards = document.querySelectorAll('#listaCitasContenedor .cita-card-box');
    let visibles = 0;

    cards.forEach(card => {
        const grupos = (card.getAttribute('data-tab-grupo') || '').split(' ');
        if (grupos.includes(tab)) {
            card.style.display = 'block';
            visibles++;
        } else {
            card.style.display = 'none';
        }
    });

    const msgVacio = document.getElementById('citasVaciasMsg');
    if (msgVacio) {
        msgVacio.style.display = (visibles === 0) ? 'block' : 'none';
    }
}

export function renderizarMiniCalendario() {
    const grid = document.getElementById('calRapidoGrid');
    const mesNombre = document.getElementById('calRapidoMesNombre');
    if (!grid || !mesNombre) return;

    mesNombre.textContent = nombresMesesCal[mesCal] + ' ' + anioCal;
    grid.innerHTML = '';

    const primerDiaSem = new Date(anioCal, mesCal, 1).getDay(); // 0 = Dom
    const offset = (primerDiaSem === 0) ? 6 : primerDiaSem - 1; // 0 = Lun
    const diasEnMes = new Date(anioCal, mesCal + 1, 0).getDate();

    // Celdas vacías previas
    for (let i = 0; i < offset; i++) {
        const empty = document.createElement('div');
        empty.className = 'cal-day-cell empty';
        grid.appendChild(empty);
    }

    // Días
    for (let dia = 1; dia <= diasEnMes; dia++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'cal-day-cell';
        dayCell.textContent = dia;

        const mStr = String(mesCal + 1).padStart(2, '0');
        const dStr = String(dia).padStart(2, '0');
        const fechaStr = `${anioCal}-${mStr}-${dStr}`;

        if (Array.isArray(fechasCitasBD) && fechasCitasBD.includes(fechaStr)) {
            dayCell.classList.add('active-day');
            dayCell.title = 'Tienes cita programada en la base de datos este día';
        }

        grid.appendChild(dayCell);
    }
}

export function cambiarMesRapido(delta) {
    mesCal += delta;
    if (mesCal < 0) {
        mesCal = 11;
        anioCal--;
    } else if (mesCal > 11) {
        mesCal = 0;
        anioCal++;
    }
    renderizarMiniCalendario();
}

export function abrirModalDetalle(id, serv, prof, fecha, hora, precio, estado, pago) {
    const elServ = document.getElementById('detServicio');
    if (elServ) elServ.textContent = serv || 'Volumen Ruso';

    const elProf = document.getElementById('detProfesional');
    if (elProf) elProf.textContent = prof || 'Isabella Sifuentes Perdomo';

    const elFecha = document.getElementById('detFecha');
    if (elFecha) elFecha.textContent = fecha || '30/09/2026';

    const elHora = document.getElementById('detHora');
    if (elHora) elHora.textContent = hora || '05:40 pm';

    const elPrecio = document.getElementById('detPrecio');
    if (elPrecio) elPrecio.textContent = '$' + precio;

    const elEstado = document.getElementById('detEstado');
    if (elEstado) elEstado.textContent = estado || 'Confirmada';

    const elPago = document.getElementById('detMetodoPago');
    if (elPago) elPago.textContent = pago || 'Tarjeta';

    const modal = document.getElementById('modalDetallesCita');
    if (modal) modal.style.display = 'flex';
}

export function cerrarModalDetalle() {
    const modal = document.getElementById('modalDetallesCita');
    if (modal) modal.style.display = 'none';
}

export function confirmarCancelarCita(event, url) {
    if (event) event.preventDefault();
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Deseas cancelar esta cita?',
            text: 'Esta acción liberará el espacio en la agenda de la especialista.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#7a5a90',
            confirmButtonText: 'Sí, cancelar cita',
            cancelButtonText: 'Volver'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    } else {
        if (confirm('¿Deseas cancelar esta cita?')) {
            window.location.href = url;
        }
    }
    return false;
}

// Retrocompatibilidad global
window.cambiarPestanaCitas = cambiarPestanaCitas;
window.renderizarMiniCalendario = renderizarMiniCalendario;
window.cambiarMesRapido = cambiarMesRapido;
window.abrirModalDetalle = abrirModalDetalle;
window.cerrarModalDetalle = cerrarModalDetalle;
window.confirmarCancelarCita = confirmarCancelarCita;

document.addEventListener('DOMContentLoaded', initMisCitas);
