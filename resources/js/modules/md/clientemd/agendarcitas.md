# Explicación de `agendarcitas.js`

Este archivo es el cerebro del proceso de reserva de citas para el cliente. Controla la selección del servicio y actualización de precios en el resumen, el filtrado de servicios en vivo, la elección de la especialista, el calendario interactivo con días del mes, la selección de horas disponibles, el cambio entre métodos de pago (con simulación visual interactiva de tarjeta de crédito en vivo), el envío asíncrono con seguridad CSRF y la ventana modal de "Mis Citas".

---

### 1. Variables de fecha y nombres de días y meses

| Código | ¿Qué hace? |
| :--- | :--- |
| `import { getCsrfToken } from '../../common/csrf.js';` | Traigo la función que obtiene la llave de seguridad CSRF de Laravel para poder enviar datos al servidor sin bloqueos. |
| `let mesActual = (new Date()).getMonth();` | Guardo el mes actual en memoria (del 0 al 11). |
| `let anioActual = (new Date()).getFullYear();` | Guardo el año en curso. |
| `let diaSeleccionado = (new Date()).getDate();` | Guardo el día del mes seleccionado inicialmente (el día de hoy). |
| `const nombresMeses = [ 'Enero', 'Febrero', ... ];` | Lista de nombres de los 12 meses en español. |
| `const nombresDias = [ 'Domingo', 'Lunes', ... ];` | Lista con los nombres de los días de la semana en español. |

---

### 2. Inicialización del proceso de reserva (`initAgendarCitas`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initAgendarCitas() {` | Función que pone en marcha todos los elementos del formulario de reserva al cargar la vista. |
| `const container = document.getElementById('agendarCitasContainer') \|\| ...;` | Busco el contenedor principal de la pantalla. |
| `if (container.dataset.mesActual !== undefined) { ... }` | Reviso si el servidor envió una fecha predefinida para abrir el calendario en ese mes, año y día. |
| `if (container.dataset.abrirMisCitas === '1') { abrirModalMisCitas(); }` | Si el enlace pedía abrir directamente el modal de mis citas, lo muestro de una vez. |
| `if (container.dataset.sessionSuccess) { Swal.fire({ ... }); }` | Si hubo una confirmación exitosa previa, muestro una alerta morada bonita. |
| `renderizarCalendario();` | Dibujo la cuadrícula de días del calendario del mes actual. |
| `if (modalMisCitasEl) { modalMisCitasEl.addEventListener('click', ...); }` | Si hago clic en el fondo oscuro exterior de la ventana de mis citas, la cierro. |
| `if (inputBuscar) { inputBuscar.addEventListener('input', filtrarServiciosAgenda); }` | Cada vez que escribo en el buscador de servicios, filtro las opciones en tiempo real. |

---

### 3. Filtro de servicios en vivo (`filtrarServiciosAgenda`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function filtrarServiciosAgenda() {` | Función para buscar entre los servicios disponibles en la lista de opciones. |
| `const query = (input ? input.value : '').toLowerCase().trim();` | Obtengo el texto ingresado en minúsculas y sin espacios adicionales. |
| `const items = document.querySelectorAll('.service-card-option');` | Selecciono todas las tarjetitas de servicios a elegir. |
| `items.forEach(item => { ... });` | Reviso tarjeta por tarjeta. |
| `item.style.display = (!query \|\| nombre.includes(query)) ? 'flex' : 'none';` | Si el nombre del servicio contiene lo que escribí, la muestro (`flex`); de lo contrario, la escondo (`none`). |

---

### 4. Selección del servicio y actualización del resumen (`seleccionarServicio`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function seleccionarServicio(element) {` | Función que se activa al hacer clic en una tarjeta de servicio. |
| `document.querySelectorAll('.service-card-option').forEach(el => el.classList.remove('selected'));` | Le quito la marca de seleccionado a cualquier otro servicio que estuviera marcado antes. |
| `element.classList.add('selected');` | Marco visualmente el servicio que acabo de tocar con el borde y fondo de selección. |
| `const id = element.getAttribute('data-id'); ...` | Extraigo del elemento su código identificador, nombre, precio, duración y foto. |
| `if (inputServId) inputServId.value = id;` | Pongo el ID del servicio en el campo oculto del formulario que se enviará a la base de datos. |
| `if (resTitulo) resTitulo.textContent = nombre;` | Actualizo el nombre del servicio en la tarjeta de resumen a la derecha de la pantalla. |
| `if (resDuracion) resDuracion.textContent = duracion;` | Actualizo la duración estimada (por ejemplo: "2 horas"). |
| `if (resImg && img) resImg.src = img;` | Cambio la foto pequeña del servicio en el resumen. |
| `const precioFmt = '$' + precio.toLocaleString('es-CO');` | Doy formato de moneda en pesos colombianos al precio (por ejemplo: `$120.000`). |
| `if (resSubtotal) resSubtotal.textContent = precioFmt;` | Muestro el valor en el subtotal del resumen. |
| `if (resTotal) resTotal.textContent = precioFmt;` | Muestro el valor en el total a pagar del resumen. |

---

### 5. Selección de especialista (`cambiarEspecialista`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function cambiarEspecialista(select) {` | Función que se ejecuta cuando el cliente elige a una especialista en la lista desplegable. |
| `const nombre = selectedOption?.getAttribute('data-nombre') \|\| 'Cualquier Profesional';` | Obtengo el nombre de la profesional seleccionada. |
| `if (inputTrabajador) inputTrabajador.value = select.value;` | Guardo el ID de la especialista en el campo del formulario. |
| `if (resProf) resProf.textContent = nombre;` | Muestro el nombre de la especialista en la tarjeta de resumen lateral. |

---

### 6. Dibujar y navegar el calendario (`renderizarCalendario` y `cambiarMesCalendario`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function renderizarCalendario() {` | Función que genera los días del mes para poder escoger la fecha de la cita. |
| `title.textContent = nombresMeses[mesActual] + ' ' + anioActual;` | Coloco el mes y año en el título superior del calendario. |
| `grid.innerHTML = '';` | Limpio los días anteriores para dibujar los nuevos. |
| `const primerDiaSemana = new Date(anioActual, mesActual, 1).getDay();` | Averiguo qué día cae el 1 del mes para ubicarlo correctamente en la semana. |
| `const offset = (primerDiaSemana === 0) ? 6 : primerDiaSemana - 1;` | Calculo los espacios vacíos previos para que la semana empiece en lunes. |
| `for (let i = 0; i < offset; i++) { ... }` | Inserto los espacios vacíos de inicio de mes. |
| `for (let dia = 1; dia <= diasEnMes; dia++) { ... }` | Creo una casilla por cada día del mes (1 al 31). |
| `if (dia === diaSeleccionado) { dayCell.classList.add('selected'); }` | Si coincide con el día elegido actualmente, lo resalto en morado. |
| `dayCell.addEventListener('click', () => { ... });` | Al hacer clic en un día, lo marco como seleccionado, actualizo el campo de fecha (`AAAA-MM-DD`) y actualizo el texto del resumen (por ejemplo: "Lunes, 15 de octubre de 2026"). |
| `export function cambiarMesCalendario(delta) { ... }` | Función para avanzar o retroceder de mes con las flechas (+1 o -1), ajustando los límites de enero a diciembre. |

---

### 7. Selección de horario (`seleccionarHora`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function seleccionarHora(btn) {` | Función que se dispara cuando toco una pastilla de horario (ej. 10:00 AM). |
| `document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));` | Desmarco los demás horarios. |
| `btn.classList.add('selected');` | Marco como seleccionado el botón de hora que toqué. |
| `const hora = btn.getAttribute('data-hora'); const hora24 = btn.getAttribute('data-hora-24');` | Obtengo la hora en formato normal (ej. "10:00 AM") y formato de 24 horas (ej. "10:00"). |
| `if (inputHora) inputHora.value = hora24;` | Guardo la hora en formato 24 horas en el campo que se enviará al sistema. |
| `if (resHora) resHora.textContent = hora;` | Muestro la hora elegida en la tarjeta de resumen lateral. |

---

### 8. Alternancia de métodos de pago (`cambiarMetodoPago`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function cambiarMetodoPago(metodo, btn) {` | Función que se ejecuta al seleccionar una pestaña de pago (Tarjeta, PSE, Billetera Digital o Efectivo). |
| `document.querySelectorAll('.pago-tab-btn').forEach(b => b.classList.remove('active'));` | Desmarco todas las pestañas de métodos de pago. |
| `if (btn) btn.classList.add('active');` | Marco como activa la pestaña que presioné. |
| `panels.forEach(id => { ... p.style.display = 'none'; });` | Oculto todos los formularios de los métodos de pago. |
| `if (inputMetodo) inputMetodo.value = metodo;` | Registro en el formulario cuál método de pago se va a utilizar. |
| `if (targetPanel) targetPanel.style.display = 'block';` | Muestro únicamente el panel correspondiente al método elegido (por ejemplo, el simulador de tarjeta o los bancos PSE). |

---

### 9. Simulación interactiva de tarjeta de crédito en vivo

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function onCardNumberInput(input) {` | Función que formatea el número de tarjeta mientras el usuario teclea. |
| `let val = input.value.replace(/\D/g, '');` | Elimino letras y símbolos, dejando únicamente números. |
| `if (val.length > 16) val = val.substring(0, 16);` | Limito el largo a un máximo de 16 dígitos. |
| `const formatted = val.match(/.{1,4}/g)?.join(' ') \|\| '';` | Separo los números en grupitos de 4 dígitos con un espacio (ej: `4557 1234 5678 9012`). |
| `input.value = formatted;` | Coloco el número con formato en el campo de texto. |
| `if (val.startsWith('4')) { brandLogo.textContent = 'VISA'; ... }` | Si empieza con 4 reconozco que es VISA y le pongo color azul marino. |
| `else if (val.startsWith('5') \|\| val.startsWith('2')) { brandLogo.textContent = 'MC'; ... }` | Si empieza con 5 o 2 reconozco que es Mastercard y le pongo color naranja. |
| `else if (val.startsWith('3')) { brandLogo.textContent = 'AMEX'; ... }` | Si empieza con 3 reconozco que es American Express y le pongo color celeste. |
| `if (display) { ... }` | Reflejo en tiempo real los números sobre el plástico virtual de la tarjeta gráfica en la pantalla; si está vacío, muestro los puntos de seguridad. |
| `export function onCardHolderInput(input) {` | Convierto el nombre del titular a mayúsculas y lo escribo en vivo sobre la tarjeta plástica interactiva. |
| `export function onCardExpiryInput(input) {` | Formateo la fecha de vencimiento a 4 números insertando automáticamente la barra (`MM/AA`) y la dibujo sobre la tarjeta plástica. |

---

### 10. Envío asíncrono y confirmación de la cita (`confirmarYAgendarCita`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function confirmarYAgendarCita(btn) {` | Función que procesa la reserva final enviando los datos por internet en segundo plano (AJAX). |
| `const form = document.getElementById('formAgendarCita'); if (!form) return;` | Localizo el formulario de agendamiento. |
| `btn.disabled = true; btn.innerHTML = '... Agendando cita...';` | Desactivo el botón y muestro un ícono giratorio para evitar que el cliente presione dos veces por error. |
| `const formData = new FormData(form);` | Recojo todos los datos seleccionados (servicio, especialista, fecha, hora y pago). |
| `fetch(form.action, { method: 'POST', headers: { ... 'X-CSRF-TOKEN': getCsrfToken() }, body: formData })` | Envío la información al servidor mediante una petición POST segura con el token CSRF. |
| `.then(response => { if (!response.ok) { ... throw new Error(errMsg); } return response.json(); })` | Si el servidor detecta algún problema (ej. fecha ya ocupada o campo faltante), leo el mensaje de error y lo lanzo para atraparlo. |
| `.then(data => { ... Swal.fire({ title: '¡Cita agendada exitosamente!', ... }); })` | Si todo salió perfecto, reactivo el botón y muestro una alerta con SweetAlert2 informando con éxito que la cita ya está reservada. |
| `.catch(error => { ... Swal.fire({ title: 'Atención', text: error.message, icon: 'warning' }); })` | Si hubo algún error, vuelvo a habilitar el botón y muestro un cuadro de advertencia con la explicación clara de lo ocurrido. |

---

### 11. Ventana modal de Mis Citas (`abrirModalMisCitas` y `cerrarModalMisCitas`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function abrirModalMisCitas() {` | Función para abrir la ventana donde se pueden consultar rápidamente las citas agendadas. |
| `if (modal) modal.style.display = 'flex';` | Pongo la ventana visible y centrada en pantalla. |
| `export function cerrarModalMisCitas() {` | Función para esconder la ventana emergente. |
| `if (modal) modal.style.display = 'none';` | Oculto la ventana emergente. |

---

### 12. Disponibilidad global y ejecución automática

| Código | ¿Qué hace? |
| :--- | :--- |
| `window.seleccionarServicio = seleccionarServicio; ...` | Conecto cada función al entorno global del navegador (`window`) para que respondan directamente a los clics del cliente en el HTML. |
| `document.addEventListener('DOMContentLoaded', initAgendarCitas);` | Inicio toda la lógica y preparo el calendario apenas se termina de cargar la página en el navegador. |
