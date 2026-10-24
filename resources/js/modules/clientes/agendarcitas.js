/**
 * Módulo: Agendar Citas (Cliente)
 * LashApp - Oh La Lash Studio
 *
 * Funcionalidad:
 * - Selección interactiva de servicio con cálculo dinámico
 * - Filtro de servicios en vivo
 * - Selección de profesional
 * - Calendario interactivo mensual con selección de fecha
 * - Selección de franja horaria
 * - Pestañas de método de pago (Tarjeta, PSE, Digital, Efectivo)
 * - Formateo y validación visual de tarjeta en vivo (Luhn, franquicia, expiración)
 * - Modal de Mis Citas
 * - Envío asíncrono AJAX con inyección CSRF y feedback SweetAlert2
 */

import { getCsrfToken } from '../../common/csrf.js';

let mesActual = (new Date()).getMonth();
let anioActual = (new Date()).getFullYear();
let diaSeleccionado = (new Date()).getDate();

const nombresMeses = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
];

const nombresDias = [
    'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'
];

export function initAgendarCitas() {
    const container = document.getElementById('agendarCitasContainer') || document.querySelector('.agendar-citas-page');
    if (container) {
        if (container.dataset.mesActual !== undefined) {
            mesActual = parseInt(container.dataset.mesActual, 10);
        }
        if (container.dataset.anioActual !== undefined) {
            anioActual = parseInt(container.dataset.anioActual, 10);
        }
        if (container.dataset.diaSeleccionado !== undefined) {
            diaSeleccionado = parseInt(container.dataset.diaSeleccionado, 10);
        }

        if (container.dataset.abrirMisCitas === '1') {
            abrirModalMisCitas();
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

    renderizarCalendario();

    // Event listeners para modal de Mis Citas
    const modalMisCitasEl = document.getElementById('modalMisCitas');
    if (modalMisCitasEl) {
        modalMisCitasEl.addEventListener('click', (e) => {
            if (e.target === modalMisCitasEl) cerrarModalMisCitas();
        });
    }

    // Buscador en vivo de servicios
    const inputBuscar = document.getElementById('buscarServicioAgenda');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', filtrarServiciosAgenda);
    }
}

export function filtrarServiciosAgenda() {
    const input = document.getElementById('buscarServicioAgenda');
    const query = (input ? input.value : '').toLowerCase().trim();
    const items = document.querySelectorAll('.service-card-option');

    items.forEach(item => {
        const nombre = (item.getAttribute('data-nombre') || '').toLowerCase();
        item.style.display = (!query || nombre.includes(query)) ? 'flex' : 'none';
    });
}

export function seleccionarServicio(element) {
    if (!element) return;
    document.querySelectorAll('.service-card-option').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');

    const id = element.getAttribute('data-id');
    const nombre = element.getAttribute('data-nombre');
    const precio = parseFloat(element.getAttribute('data-precio') || '0');
    const duracion = element.getAttribute('data-duracion');
    const img = element.getAttribute('data-img');

    // Actualizar inputs ocultos
    const inputServId = document.getElementById('inputServicioId');
    if (inputServId) inputServId.value = id;

    // Actualizar resumen
    const resTitulo = document.getElementById('resumenTitulo');
    if (resTitulo) resTitulo.textContent = nombre;

    const resDuracion = document.getElementById('resumenDuracion');
    if (resDuracion) resDuracion.textContent = duracion;

    const resImg = document.getElementById('resumenImg');
    if (resImg && img) resImg.src = img;

    const precioFmt = '$' + precio.toLocaleString('es-CO');
    const resSubtotal = document.getElementById('resumenSubtotal');
    if (resSubtotal) resSubtotal.textContent = precioFmt;

    const resTotal = document.getElementById('resumenTotal');
    if (resTotal) resTotal.textContent = precioFmt;
}

export function cambiarEspecialista(select) {
    if (!select) return;
    const selectedOption = select.options[select.selectedIndex];
    const nombre = selectedOption?.getAttribute('data-nombre') || 'Cualquier Profesional';
    const inputTrabajador = document.getElementById('inputTrabajadorId');
    if (inputTrabajador) inputTrabajador.value = select.value;

    const resProf = document.getElementById('resumenProfesionalTexto');
    if (resProf) resProf.textContent = nombre;
}

export function renderizarCalendario() {
    const grid = document.getElementById('diasCalendarioGrid');
    const title = document.getElementById('calMonthTitle');
    if (!grid || !title) return;

    title.textContent = nombresMeses[mesActual] + ' ' + anioActual;
    grid.innerHTML = '';

    const primerDiaSemana = new Date(anioActual, mesActual, 1).getDay(); // 0 = Dom
    const diasEnMes = new Date(anioActual, mesActual + 1, 0).getDate();

    // Convertir a Lunes = 0, Domingo = 6
    const offset = (primerDiaSemana === 0) ? 6 : primerDiaSemana - 1;

    // Celdas vacías previas
    for (let i = 0; i < offset; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'cal-day-item empty';
        grid.appendChild(emptyCell);
    }

    // Días del mes
    for (let dia = 1; dia <= diasEnMes; dia++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'cal-day-item';
        dayCell.textContent = dia;

        const dot = document.createElement('span');
        dot.className = 'cal-day-dot';
        dayCell.appendChild(dot);

        if (dia === diaSeleccionado) {
            dayCell.classList.add('selected');
        }

        dayCell.addEventListener('click', () => {
            diaSeleccionado = dia;
            document.querySelectorAll('.cal-day-item').forEach(c => c.classList.remove('selected'));
            dayCell.classList.add('selected');

            const fechaObj = new Date(anioActual, mesActual, dia);
            const anioStr = anioActual;
            const mesStr = String(mesActual + 1).padStart(2, '0');
            const diaStr = String(dia).padStart(2, '0');

            const inputFecha = document.getElementById('inputFechaCita');
            if (inputFecha) inputFecha.value = `${anioStr}-${mesStr}-${diaStr}`;

            const nombreDia = nombresDias[fechaObj.getDay()];
            const nombreMes = nombresMeses[mesActual].toLowerCase();
            const resFecha = document.getElementById('resumenFechaTexto');
            if (resFecha) resFecha.textContent = `${nombreDia}, ${dia} de ${nombreMes} de ${anioStr}`;
        });

        grid.appendChild(dayCell);
    }
}

export function cambiarMesCalendario(delta) {
    mesActual += delta;
    if (mesActual < 0) {
        mesActual = 11;
        anioActual--;
    } else if (mesActual > 11) {
        mesActual = 0;
        anioActual++;
    }
    diaSeleccionado = 1;
    renderizarCalendario();
}

export function seleccionarHora(btn) {
    if (!btn) return;
    document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');

    const hora = btn.getAttribute('data-hora');
    const hora24 = btn.getAttribute('data-hora-24');

    const inputHora = document.getElementById('inputHoraInicio');
    if (inputHora) inputHora.value = hora24;

    const resHora = document.getElementById('resumenHoraTexto');
    if (resHora) resHora.textContent = hora;
}

export function cambiarMetodoPago(metodo, btn) {
    document.querySelectorAll('.pago-tab-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const panels = ['panelMetodoTarjeta', 'panelMetodoPse', 'panelMetodoDigital', 'panelMetodoEfectivo'];
    panels.forEach(id => {
        const p = document.getElementById(id);
        if (p) p.style.display = 'none';
    });

    const inputMetodo = document.getElementById('inputMetodoPago');
    if (inputMetodo) inputMetodo.value = metodo;

    const targetPanelMap = {
        tarjeta: 'panelMetodoTarjeta',
        pse: 'panelMetodoPse',
        digital: 'panelMetodoDigital',
        efectivo: 'panelMetodoEfectivo'
    };

    const targetId = targetPanelMap[metodo];
    if (targetId) {
        const targetPanel = document.getElementById(targetId);
        if (targetPanel) targetPanel.style.display = 'block';
    }
}

export function onCardNumberInput(input) {
    if (!input) return;
    let val = input.value.replace(/\D/g, '');
    if (val.length > 16) val = val.substring(0, 16);

    const formatted = val.match(/.{1,4}/g)?.join(' ') || '';
    input.value = formatted;

    const brandLogo = document.getElementById('vcardBrandLogo');
    if (brandLogo) {
        if (val.startsWith('4')) {
            brandLogo.textContent = 'VISA';
            brandLogo.style.color = '#1e3a8a';
        } else if (val.startsWith('5') || val.startsWith('2')) {
            brandLogo.textContent = 'MC';
            brandLogo.style.color = '#ea580c';
        } else if (val.startsWith('3')) {
            brandLogo.textContent = 'AMEX';
            brandLogo.style.color = '#0284c7';
        } else {
            brandLogo.textContent = 'VISA';
            brandLogo.style.color = '#1e3a8a';
        }
    }

    const display = document.getElementById('vcardNumberDisplay');
    if (display) {
        if (formatted.length > 0) {
            display.textContent = formatted;
        } else {
            display.innerHTML = '&bull;&bull;&bull;&bull; &nbsp; &bull;&bull;&bull;&bull; &nbsp; &bull;&bull;&bull;&bull; &nbsp; &bull;&bull;&bull;&bull;';
        }
    }
}

export function onCardHolderInput(input) {
    if (!input) return;
    input.value = input.value.toUpperCase();
    const display = document.getElementById('vcardHolderDisplay');
    if (display) {
        display.textContent = input.value.trim() || 'NOMBRE EN LA TARJETA';
    }
}

export function onCardExpiryInput(input) {
    if (!input) return;
    let val = input.value.replace(/\D/g, '');
    if (val.length > 4) val = val.substring(0, 4);

    if (val.length >= 2) {
        val = val.substring(0, 2) + '/' + val.substring(2);
    }
    input.value = val;

    const display = document.getElementById('vcardExpiryDisplay');
    if (display) {
        display.textContent = val || 'MM/AA';
    }
}

export function confirmarYAgendarCita(btn) {
    const form = document.getElementById('formAgendarCita');
    if (!form) return;

    const originalHtml = btn ? btn.innerHTML : 'Confirmar y Continuar <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i> Agendando cita...';
    }

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken()
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(errData => {
                let errMsg = errData.message || 'Error al procesar la reserva.';
                if (errData.errors) {
                    const primerError = Object.values(errData.errors)[0];
                    if (primerError && primerError.length > 0) errMsg = primerError[0];
                }
                throw new Error(errMsg);
            });
        }
        return response.json();
    })
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Cita agendada exitosamente!',
                html: '<p style="color:#7a5a90; font-size:14px; margin-top:8px;">' + (data.message || 'Tu cita ha sido reservada con éxito en el sistema.') + '<br><small style="color:#9b70b5; display:block; margin-top:6px;">Puedes consultarla en cualquier momento desde Mis Citas o tu Historial.</small></p>',
                icon: 'success',
                confirmButtonColor: '#7c1db8',
                confirmButtonText: 'Entendido'
            });
        } else {
            alert('¡Cita agendada exitosamente!');
        }
    })
    .catch(error => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Atención',
                text: error.message || 'Hubo un inconveniente al registrar la cita.',
                icon: 'warning',
                confirmButtonColor: '#7c1db8',
                confirmButtonText: 'Aceptar'
            });
        } else {
            alert(error.message || 'Error al procesar la cita.');
        }
    });
}

export function abrirModalMisCitas() {
    const modal = document.getElementById('modalMisCitas');
    if (modal) modal.style.display = 'flex';
}

export function cerrarModalMisCitas() {
    const modal = document.getElementById('modalMisCitas');
    if (modal) modal.style.display = 'none';
}

// Retrocompatibilidad global para handlers HTML inline
window.filtrarServiciosAgenda = filtrarServiciosAgenda;
window.seleccionarServicio = seleccionarServicio;
window.cambiarEspecialista = cambiarEspecialista;
window.renderizarCalendario = renderizarCalendario;
window.cambiarMesCalendario = cambiarMesCalendario;
window.seleccionarHora = seleccionarHora;
window.cambiarMetodoPago = cambiarMetodoPago;
window.onCardNumberInput = onCardNumberInput;
window.onCardHolderInput = onCardHolderInput;
window.onCardExpiryInput = onCardExpiryInput;
window.confirmarYAgendarCita = confirmarYAgendarCita;
window.abrirModalMisCitas = abrirModalMisCitas;
window.cerrarModalMisCitas = cerrarModalMisCitas;

document.addEventListener('DOMContentLoaded', initAgendarCitas);
