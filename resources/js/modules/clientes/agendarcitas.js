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
    cargarHorasDisponibles();

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

    // Recargar disponibilidad según la duración del nuevo servicio
    cargarHorasDisponibles();
}

export function cambiarEspecialista(select) {
    if (!select) return;
    const selectedOption = select.options[select.selectedIndex];
    const nombre = selectedOption?.getAttribute('data-nombre') || 'Cualquier Profesional';
    const inputTrabajador = document.getElementById('inputTrabajadorId');
    if (inputTrabajador) inputTrabajador.value = select.value;

    const resProf = document.getElementById('resumenProfesionalTexto');
    if (resProf) resProf.textContent = nombre;

    // Recargar disponibilidad para la especialista seleccionada
    cargarHorasDisponibles();
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

    const hoy = new Date();
    const hoyAnio = hoy.getFullYear();
    const hoyMes = hoy.getMonth();
    const hoyDia = hoy.getDate();

    // Días del mes
    for (let dia = 1; dia <= diasEnMes; dia++) {
        const dayCell = document.createElement('div');
        dayCell.className = 'cal-day-item';
        dayCell.textContent = dia;

        const dot = document.createElement('span');
        dot.className = 'cal-day-dot';
        dayCell.appendChild(dot);

        // Validar si el día ya pasó
        const esPasado = (anioActual < hoyAnio) ||
            (anioActual === hoyAnio && mesActual < hoyMes) ||
            (anioActual === hoyAnio && mesActual === hoyMes && dia < hoyDia);

        if (esPasado) {
            dayCell.classList.add('disabled', 'past-day');
            dayCell.style.opacity = '0.35';
            dayCell.style.pointerEvents = 'none';
            dayCell.style.cursor = 'not-allowed';
            dot.className = 'cal-day-dot gray';
        } else {
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

                // Cargar dinámicamente los horarios disponibles para esta nueva fecha
                cargarHorasDisponibles();
            });
        }

        grid.appendChild(dayCell);
    }
}

export function cambiarMesCalendario(delta) {
    const hoy = new Date();
    const hoyAnio = hoy.getFullYear();
    const hoyMes = hoy.getMonth();

    // Evitar navegar a meses anteriores al actual
    if (delta < 0 && (anioActual < hoyAnio || (anioActual === hoyAnio && mesActual <= hoyMes))) {
        return;
    }

    mesActual += delta;
    if (mesActual < 0) {
        mesActual = 11;
        anioActual--;
    } else if (mesActual > 11) {
        mesActual = 0;
        anioActual++;
    }

    // Si es el mes en curso, seleccionar el día de hoy o primer día válido
    if (anioActual === hoyAnio && mesActual === hoyMes) {
        diaSeleccionado = hoy.getDate();
    } else {
        diaSeleccionado = 1;
    }

    renderizarCalendario();

    // Actualizar fecha en input y resumen
    const fechaObj = new Date(anioActual, mesActual, diaSeleccionado);
    const anioStr = anioActual;
    const mesStr = String(mesActual + 1).padStart(2, '0');
    const diaStr = String(diaSeleccionado).padStart(2, '0');

    const inputFecha = document.getElementById('inputFechaCita');
    if (inputFecha) inputFecha.value = `${anioStr}-${mesStr}-${diaStr}`;

    const nombreDia = nombresDias[fechaObj.getDay()];
    const nombreMes = nombresMeses[mesActual].toLowerCase();
    const resFecha = document.getElementById('resumenFechaTexto');
    if (resFecha) resFecha.textContent = `${nombreDia}, ${diaSeleccionado} de ${nombreMes} de ${anioStr}`;

    // Cargar horarios para la nueva fecha
    cargarHorasDisponibles();
}

/**
 * Consulta vía AJAX los horarios disponibles en el servidor para la fecha,
 * especialista y servicio seleccionados, actualizando la lista en vivo.
 */
export async function cargarHorasDisponibles() {
    const inputFecha = document.getElementById('inputFechaCita');
    const inputTrabajador = document.getElementById('inputTrabajadorId');
    const inputServicio = document.getElementById('inputServicioId');
    const timeSlotsList = document.querySelector('.time-slots-list');

    if (!inputFecha || !timeSlotsList) return;

    const fecha = inputFecha.value;
    const idTrabajador = inputTrabajador ? inputTrabajador.value : '';
    const idServicio = inputServicio ? inputServicio.value : '';

    timeSlotsList.style.opacity = '0.5';

    try {
        const url = `/citas/horas-disponibles?fecha=${encodeURIComponent(fecha)}&id_trabajador=${encodeURIComponent(idTrabajador)}&id_servicio=${encodeURIComponent(idServicio)}`;
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Error al consultar horarios disponibles');
        }

        const data = await response.json();
        const horarios = data.horarios || [];

        renderizarTimeSlots(horarios);
    } catch (err) {
        console.error('Error cargando horas disponibles:', err);
    } finally {
        timeSlotsList.style.opacity = '1';
    }
}

/**
 * Renderiza los botones de franjas horarias con base en su disponibilidad real.
 * Si una hora ya fue reservada por alguien más, la muestra deshabilitada con etiqueta 'Ocupado'.
 */
export function renderizarTimeSlots(horarios) {
    const timeSlotsList = document.querySelector('.time-slots-list');
    const inputHora = document.getElementById('inputHoraInicio');
    const resHora = document.getElementById('resumenHoraTexto');
    const btnConfirmar = document.getElementById('btnConfirmarReserva');

    if (!timeSlotsList) return;

    timeSlotsList.innerHTML = '';

    const currentSelected24 = inputHora ? inputHora.value : null;
    let slotToSelect = null;

    if (!horarios || horarios.length === 0) {
        timeSlotsList.innerHTML = `
            <div class="slots-no-disponibles">
                <i class="fa-solid fa-circle-exclamation"></i>
                <p>No hay turnos disponibles para esta fecha.</p>
                <span>Por favor selecciona otro día en el calendario.</span>
            </div>
        `;
        if (inputHora) inputHora.value = '';
        if (resHora) resHora.textContent = 'Sin horario seleccionado';
        if (btnConfirmar) btnConfirmar.disabled = true;
        return;
    }

    const disponibles = horarios.filter(h => h.disponible);

    if (disponibles.length === 0) {
        timeSlotsList.innerHTML = `
            <div class="slots-no-disponibles">
                <i class="fa-solid fa-calendar-xmark"></i>
                <p>Todos los horarios están reservados para esta fecha.</p>
                <span>Por favor selecciona otro día en el calendario.</span>
            </div>
        `;
        if (inputHora) inputHora.value = '';
        if (resHora) resHora.textContent = 'Sin horario disponible';
        if (btnConfirmar) btnConfirmar.disabled = true;
        return;
    }

    if (btnConfirmar) btnConfirmar.disabled = false;

    // Mantener la hora seleccionada si sigue libre, de lo contrario elegir la primera disponible
    if (currentSelected24 && disponibles.some(h => h.hora_24 === currentSelected24)) {
        slotToSelect = currentSelected24;
    } else {
        slotToSelect = disponibles[0].hora_24;
    }

    horarios.forEach(slot => {
        const btn = document.createElement('div');
        const isSelected = slot.disponible && (slot.hora_24 === slotToSelect);

        if (!slot.disponible) {
            btn.className = 'time-slot-btn ocupado disabled';
            const motivoLabel = slot.motivo === 'pasado' ? 'Pasado' : 'Ocupado';
            btn.title = slot.motivo === 'pasado' ? 'Horario ya pasado' : 'Horario ya reservado / no disponible';
            btn.innerHTML = `
                <span class="slot-time-text" style="text-decoration: line-through; opacity: 0.7;">${slot.hora_12}</span>
                <span class="slot-ocupado-tag">${motivoLabel}</span>
            `;
        } else {
            btn.className = `time-slot-btn ${isSelected ? 'selected' : ''}`;
            btn.setAttribute('data-hora', slot.hora_12);
            btn.setAttribute('data-hora-24', slot.hora_24);
            btn.innerHTML = `
                <span class="slot-time-text">${slot.hora_12}</span>
                <div class="slot-radio-dot"></div>
            `;
            btn.addEventListener('click', () => seleccionarHora(btn));
        }

        timeSlotsList.appendChild(btn);
    });

    // Actualizar input oculto y resumen con el horario seleccionado
    const selectedSlotObj = disponibles.find(h => h.hora_24 === slotToSelect);
    if (selectedSlotObj) {
        if (inputHora) inputHora.value = selectedSlotObj.hora_24;
        if (resHora) resHora.textContent = selectedSlotObj.hora_12;
    }
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

    const inputHora = document.getElementById('inputHoraInicio');
    if (!inputHora || !inputHora.value) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Horario Requerido',
                text: 'Por favor selecciona un horario disponible para tu cita.',
                icon: 'warning',
                confirmButtonColor: '#7c1db8',
                confirmButtonText: 'Entendido'
            });
        } else {
            alert('Por favor selecciona un horario disponible para tu cita.');
        }
        return;
    }

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
            const ticketNumero = data.ticket_numero || 'Generado';
            const ticketUrl = data.ticket_url || '#';

            Swal.fire({
                title: '¡Cita Confirmada y Factura POS Generada!',
                html: `
                    <div style="text-align: center; padding: 6px 0;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #f6effb; color: #7c1db8; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 12px;">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div style="background: #faf5fc; border: 1px dashed #d8b4e2; border-radius: 10px; padding: 10px; margin-bottom: 14px;">
                            <span style="font-size: 11px; text-transform: uppercase; color: #7a5a90; font-weight: 700; display: block;">Número de Comprobante</span>
                            <strong style="font-family: monospace; font-size: 16px; color: #7c1db8;">${ticketNumero}</strong>
                        </div>
                        <p style="color: #4a2d59; font-size: 13px; line-height: 1.5; margin-bottom: 16px;">
                            ${data.message || 'Tu cita y pago fueron procesados correctamente.'}
                        </p>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <button type="button" onclick="Swal.close(); if(window.abrirModalTicketPOS){ window.abrirModalTicketPOS('${ticketUrl}?embed=1'); }" style="background: #7c1db8; color: #ffffff; border: none; padding: 10px 16px; border-radius: 10px; font-weight: 700; font-size: 13px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(124, 29, 184, 0.3);">
                                <i class="fa-solid fa-receipt"></i> Ver Ticket POS en Pantalla
                            </button>
                        </div>
                    </div>
                `,
                icon: 'success',
                confirmButtonColor: '#4a2d59',
                confirmButtonText: 'Ir a Mis Citas',
                showCloseButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '/cliente/mis-citas';
                }
            });
        } else {
            alert('¡Cita agendada exitosamente! Factura POS: ' + (data.ticket_numero || ''));
            window.location.href = '/cliente/mis-citas';
        }
    })
    .catch(error => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Horario no disponible',
                text: error.message || 'Hubo un inconveniente al registrar la cita.',
                icon: 'warning',
                confirmButtonColor: '#7c1db8',
                confirmButtonText: 'Aceptar'
            });
        } else {
            alert(error.message || 'Error al procesar la cita.');
        }

        // Recargar las horas disponibles de inmediato para reflejar las horas ocupadas
        cargarHorasDisponibles();
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
window.cargarHorasDisponibles = cargarHorasDisponibles;
window.renderizarTimeSlots = renderizarTimeSlots;
window.cambiarMetodoPago = cambiarMetodoPago;
window.onCardNumberInput = onCardNumberInput;
window.onCardHolderInput = onCardHolderInput;
window.onCardExpiryInput = onCardExpiryInput;
window.confirmarYAgendarCita = confirmarYAgendarCita;
window.abrirModalMisCitas = abrirModalMisCitas;
window.cerrarModalMisCitas = cerrarModalMisCitas;

document.addEventListener('DOMContentLoaded', initAgendarCitas);
