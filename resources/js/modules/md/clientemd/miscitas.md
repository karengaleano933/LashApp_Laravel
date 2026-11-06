# Explicación de `miscitas.js`

Este archivo gestiona la pantalla "Mis Citas" del cliente. Permite filtrar las citas según su estado mediante pestañas, visualizar un mini calendario interactivo donde se resaltan los días que tienen citas agendadas, abrir los detalles completos de una cita en una ventana modal y solicitar confirmación segura antes de cancelar una cita.

---

### 1. Variables de fecha y meses del calendario

| Código | ¿Qué hace? |
| :--- | :--- |
| `let fechasCitasBD = [];` | Creo una lista en memoria para guardar las fechas en que el cliente tiene citas según la base de datos. |
| `let mesCal = (new Date()).getMonth();` | Guardo el mes actual para saber qué mes mostrar primero en el calendario. |
| `let anioCal = (new Date()).getFullYear();` | Guardo el año actual. |
| `const nombresMesesCal = [ 'Enero', 'Febrero', ... ];` | Lista de nombres de los 12 meses del año en español. |

---

### 2. Inicialización del módulo (`initMisCitas`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initMisCitas() {` | Función que inicializa la vista de citas cuando carga la página. |
| `const container = document.getElementById('misCitasContainer') \|\| ...;` | Busco el contenedor principal de la pantalla. |
| `if (container.dataset.fechasCitas) { fechasCitasBD = JSON.parse(container.dataset.fechasCitas); }` | Leo las fechas de citas que envió el servidor en formato JSON y las guardo en memoria. |
| `if (container.dataset.sessionSuccess) { Swal.fire({ ... }); }` | Si hay un mensaje de éxito guardado en la sesión (por ejemplo tras cancelar o agendar), muestro una alerta morada informándolo. |
| `renderizarMiniCalendario();` | Dibujo los días y meses del mini calendario. |
| `if (modalDetEl) { modalDetEl.addEventListener('click', (e) => { if (e.target === modalDetEl) cerrarModalDetalle(); }); }` | Si el cliente hace clic fuera del modal de detalles (en el fondo oscuro), cierro la ventana. |

---

### 3. Filtrar citas por pestañas (`cambiarPestanaCitas`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function cambiarPestanaCitas(tab, btn) {` | Función que se activa al hacer clic en las pestañas (próximas, pendientes, completadas, canceladas). |
| `document.querySelectorAll('.miscitas-tab-btn').forEach(b => b.classList.remove('active')); if (btn) btn.classList.add('active');` | Desactivo todas las pestañas y marco como activa únicamente a la que acabo de hacerle clic. |
| `const titulos = { 'proximas': 'Próximas citas', ... };` | Diccionario con los títulos que debe mostrar el encabezado según la pestaña. |
| `if (tituloEl && titulos[tab]) { tituloEl.textContent = titulos[tab]; }` | Cambio el texto del título principal de la lista. |
| `cards.forEach(card => { ... });` | Reviso cada tarjeta de cita en la lista. |
| `const grupos = (card.getAttribute('data-tab-grupo') \|\| '').split(' ');` | Obtengo a qué grupos de estado pertenece la tarjeta. |
| `if (grupos.includes(tab)) { card.style.display = 'block'; visibles++; } else { card.style.display = 'none'; }` | Si la cita pertenece a la pestaña seleccionada, la muestro (`block`); de lo contrario, la escondo (`none`). |
| `if (msgVacio) { msgVacio.style.display = (visibles === 0) ? 'block' : 'none'; }` | Si no hay ninguna cita en este grupo, muestro un aviso diciendo que no hay citas en este estado. |

---

### 4. Dibujar el mini calendario interactivo (`renderizarMiniCalendario`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function renderizarMiniCalendario() {` | Función que dibuja la cuadrícula de días del mes en el mini calendario. |
| `mesNombre.textContent = nombresMesesCal[mesCal] + ' ' + anioCal;` | Pongo el nombre del mes y el año en el encabezado del calendario (ej. "Septiembre 2026"). |
| `grid.innerHTML = '';` | Limpio cualquier celda anterior antes de dibujar las nuevas. |
| `const primerDiaSem = new Date(anioCal, mesCal, 1).getDay();` | Averiguo qué día de la semana cae el primer día del mes. |
| `const offset = (primerDiaSem === 0) ? 6 : primerDiaSem - 1;` | Calculo cuántos espacios vacíos necesito antes del día 1 (para que empiece en lunes). |
| `const diasEnMes = new Date(anioCal, mesCal + 1, 0).getDate();` | Calculo cuántos días tiene en total el mes actual (28, 30 o 31 días). |
| `for (let i = 0; i < offset; i++) { ... grid.appendChild(empty); }` | Dibujo las celdas vacías previas para alinear el primer día de la semana. |
| `for (let dia = 1; dia <= diasEnMes; dia++) { ... }` | Recorro cada día del 1 al último día del mes. |
| `const fechaStr = `${anioCal}-${mStr}-${dStr}`;` | Armo la fecha en formato año-mes-día (`AAAA-MM-DD`). |
| `if (Array.isArray(fechasCitasBD) && fechasCitasBD.includes(fechaStr)) { dayCell.classList.add('active-day'); ... }` | Si esta fecha coincide con alguna cita agendada en la base de datos, le pongo la clase `active-day` (un puntito o fondo morado) para que resalte visualmente. |
| `grid.appendChild(dayCell);` | Agrego el número del día al calendario. |

---

### 5. Navegación entre meses del calendario (`cambiarMesRapido`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function cambiarMesRapido(delta) {` | Función para avanzar o retroceder de mes con las flechas (+1 o -1). |
| `mesCal += delta;` | Le sumo o resto un mes al valor actual. |
| `if (mesCal < 0) { mesCal = 11; anioCal--; }` | Si retrocedo de enero, paso a diciembre del año anterior. |
| `else if (mesCal > 11) { mesCal = 0; anioCal++; }` | Si avanzo de diciembre, paso a enero del año siguiente. |
| `renderizarMiniCalendario();` | Vuelvo a dibujar los días del nuevo mes. |

---

### 6. Modal de detalles de cita (`abrirModalDetalle` y `cerrarModalDetalle`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function abrirModalDetalle(id, serv, prof, fecha, hora, precio, estado, pago) {` | Función que recibe toda la información de una cita y la muestra en la ventana emergente. |
| `if (elServ) elServ.textContent = serv ...;` | Relleno en el modal el nombre del servicio recibido. |
| `if (elProf) elProf.textContent = prof ...;` | Relleno el nombre de la especialista que atenderá la cita. |
| `if (elFecha) elFecha.textContent = fecha ...;` | Relleno la fecha programada. |
| `if (elHora) elHora.textContent = hora ...;` | Relleno la hora fijada. |
| `if (elPrecio) elPrecio.textContent = '$' + precio;` | Relleno el costo total. |
| `if (elEstado) elEstado.textContent = estado ...;` | Relleno el estado actual (confirmada, pendiente, etc.). |
| `if (elPago) elPago.textContent = pago ...;` | Relleno el método de pago utilizado. |
| `if (modal) modal.style.display = 'flex';` | Abro la ventana emergente mostrándola centrada en pantalla. |
| `export function cerrarModalDetalle() { if (modal) modal.style.display = 'none'; }` | Oculto la ventana emergente al hacer clic en cerrar. |

---

### 7. Confirmación para cancelar cita (`confirmarCancelarCita`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function confirmarCancelarCita(event, url) {` | Función para interceptar el botón de cancelar y pedir una confirmación de seguridad. |
| `if (event) event.preventDefault();` | Detengo la navegación inmediata para no cancelar por error sin confirmación. |
| `if (typeof Swal !== 'undefined') { Swal.fire({ ... }).then((result) => { if (result.isConfirmed) { window.location.href = url; } }); }` | Si tengo SweetAlert2, muestro una alerta de peligro advirtiendo que se liberará el espacio en la agenda de la especialista; si el cliente confirma, lo redirijo a la dirección web (`url`) que procesa la cancelación. |
| `else { if (confirm('...')) window.location.href = url; }` | Si no está la librería, muestro la ventana de confirmación clásica del navegador. |

---

### 8. Exposición global y ejecución inicial

| Código | ¿Qué hace? |
| :--- | :--- |
| `window.cambiarPestanaCitas = cambiarPestanaCitas; ...` | Expongo todas las funciones al objeto global `window` para que puedan llamarse desde los botones del HTML. |
| `document.addEventListener('DOMContentLoaded', initMisCitas);` | Inicio todas las operaciones y el calendario al terminar de cargar el contenido del sitio web. |
