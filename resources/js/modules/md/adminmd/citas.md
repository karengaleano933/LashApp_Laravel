# Explicación de mi código de Gestión de Citas (`citas.js`)

En este archivo me encargo de darle vida a toda la pantalla de citas de la administradora. Gracias a este código, la tabla se puede filtrar al instante, las citas se pueden marcar como hechas o canceladas con avisos bonitos, el mini-calendario mensual cambia de mes con un clic y la ventana para registrar una nueva cita se abre y se cierra con suavidad.


---

## 1. Importación de utilidades (`alerts.js`)

| Código | ¿Qué hace?  
| :--- | :--- |
| `import { confirmAction, notify } from '../../common/alerts.js';` | Traigo dos herramientas que ya preparé en otra carpeta: una para hacer preguntas de confirmación antes de borrar o completar algo (`confirmAction`) y otra para mostrar mensajitos informativos en la esquina (`notify`). |

---

## 2. El mapa o libreta de direcciones (`DEFAULT_CONFIG`)

Aquí guardo en un solo sitio los nombres de los botones, las cajas de texto y las rutas a donde se envían los datos, para no tener cosas regadas por todo el archivo:

| Código | ¿Qué hace? |
| :--- | :--- |
| `const DEFAULT_CONFIG = {` | Abro mi libreta de opciones por defecto. |
| `containerSelector: '#citasContainer',` | Le digo cuál es la caja principal que envuelve todo el contenido de citas. |
| `table: '#citasTable',` | La tabla HTML donde se dibujan las filas de citas. |
| `searchInput: '#buscarCita',` | La cajita donde la administradora escribe el nombre del cliente o servicio para buscar. |
| `professionalFilter: '#filtroProfesional',` | El menú desplegable para elegir ver solo las citas de una especialista en particular. |
| `statusFilter: '#filtroEstado',` | El menú desplegable para filtrar por estado: Confirmada, Pendiente, Cancelada, etc. |
| `btnComplete: '.btn-completar-cita',` | La clase de los botoncitos verdes de check para marcar una cita como lista. |
| `btnCancel: '.btn-cancelar-cita',` | La clase de los botones para cancelar una cita. |
| `calGrid: '#calGrid',` | La cuadrícula donde dibujo los cuadritos de los días del mes. |
| `calLabel: '#calMesAnio',` | El letrero que dice en qué mes y año estamos (ej. "Octubre 2026"). |
| `calPrevBtn: '#calPrev',` | El botón con flecha a la izquierda para ir al mes anterior. |
| `calNextBtn: '#calNext',` | El botón con flecha a la derecha para avanzar al mes siguiente. |
| `modalNew: '#modalNuevaCita',` | La ventana flotante que aparece para registrar una nueva cita. |
| `btnOpenNew: '#abrirNuevaCita',` | El botón morado superior que abre esa ventana flotante. |
| `btnCloseNew: '#cerrarNuevaCita',` | La "X" de arriba a la derecha para cerrar la ventana flotante. |
| `btnCancelNew: '#cancelarNuevaCita',` | El botón gris de abajo que dice "Cancelar". |
| `completeUrlTemplate: '/admin/citas/:id/completar',` | La dirección web a la que viaja la página para completar una cita (luego cambio `:id` por el número real de la cita). |
| `cancelUrlTemplate: '/admin/citas/:id/cancelar',` | La dirección web para cancelar la cita seleccionada. |
| `messages: { ... }` | Los textos amigables que saldrán en las ventanas de confirmación para que la administradora no se equivoque. |

---

## 3. Control de la tabla inteligente (`setupCitasTable`)

| Código | ¿Qué hace?  |
| :--- | :--- |
| `function setupCitasTable(config) {` | Inicio la función que se encarga de convertir la tabla sencilla en una tabla inteligente que busca rápido. |
| `if (typeof window.jQuery === 'undefined' \|\| !window.jQuery.fn.DataTable) return null;` | Me aseguro de que la librería DataTables esté cargada; si no está, me detengo para no generar errores. |
| `const $ = window.jQuery; const tableEl = $(config.selectors.table);` | Tomo el control de la tabla usando jQuery. |
| `if (!tableEl.length) return null;` | Si en esta página no hay ninguna tabla de citas, no hago nada. |
| `if ($.fn.DataTable.isDataTable(...)) { tableEl.DataTable().destroy(); }` | Si la tabla ya estaba configurada de antes, la reinicio para que no se dupliquen las funciones. |
| `$.fn.dataTable.ext.errMode = 'none';` | Silencio las alertas feas del navegador si falta algún dato pequeño. |
| `const dt = tableEl.DataTable({ ... });` | Activo DataTables en español, le digo que no pagine de golpe y que se adapte bien a celulares. |
| `searchInput.addEventListener('input', function () { dt.search(this.value).draw(); });` | Cada vez que escribo una letra en el buscador, la tabla filtra inmediatamente en vivo. |
| `$(config.selectors.professionalFilter).on('change', function () { dt.column(4).search(this.value).draw(); });` | Cuando elijo una especialista en el selector, reviso la columna 4 (Profesional) y solo muestro sus citas. |
| `$(config.selectors.statusFilter).on('change', function () { dt.column(5).search(this.value).draw(); });` | Cuando cambio el estado en el menú, reviso la columna 5 y muestro solo las citas con ese estado. |

---

## 4. Botones de Acción: Completar y Cancelar (`setupCitasActions`)

| Código | ¿Qué hace?  |
| :--- | :--- |
| `window.confirmarCompletar = async function (id) {` | Creo la función global para marcar una cita como terminada. |
| `const confirmed = await confirmAction({ ... });` | Le muestro una ventana elegante preguntando: *"¿Marcar cita como realizada?"* con un botón verde. |
| `if (confirmed) { const url = config.endpoints.completeUrlTemplate.replace(':id', id); window.location.href = url; }` | Si la persona responde que sí, cambio la dirección del navegador a la ruta de completar esa cita específica. |
| `window.confirmarCancelar = async function (id) {` | Creo la función para cancelar una cita. |
| `const confirmed = await confirmAction({ isDestructive: true ... });` | Muestro una advertencia roja: *"¿Cancelar esta cita? Esta acción actualizará el estado"*. |
| `if (confirmed) { const url = config.endpoints.cancelUrlTemplate.replace(':id', id); window.location.href = url; }` | Si dice que sí, redirijo a la dirección de cancelación para que el servidor la cancele. |
| `window.cancelarSeleccionadas = function () { notify(...); }` | Una pequeña ayuda por si alguien hace clic en cancelar varias, avisándole que primero las marque. |
| `document.addEventListener('click', (e) => { ... });` | Detecto si se hizo clic en los botones de completar o cancelar directamente dentro de la tabla para activar la confirmación correcta con el ID de la fila. |

---

## 5. Mini Calendario Mensual (`setupCalendar`)

| Código | ¿Qué hace?  |
| :--- | :--- |
| `const grid = document.querySelector(config.selectors.calGrid);` | Busco la caja donde van los números de los días. |
| `const label = document.querySelector(config.selectors.calLabel);` | Busco el texto del título donde dice el mes. |
| `let calYear = new Date().getFullYear(); let calMonth = new Date().getMonth();` | Guardo el año actual y el mes actual según el reloj del computador. |
| `function renderCal() { ... }` | La función que calcula cuántos días tiene el mes y los dibuja en pantalla. |
| `label.textContent = `${meses[calMonth]} ${calYear}`;` | Escribo el nombre del mes en español (ej. "Noviembre 2026"). |
| `const firstDay = (new Date(calYear, calMonth, 1).getDay() + 6) % 7;` | Calculo qué día de la semana cae el primer día del mes (para saber cuántos espacios vacíos dejar al inicio del lunes). |
| `const days = new Date(calYear, calMonth + 1, 0).getDate();` | Averiguo cuántos días exactos tiene este mes (28, 30, 31). |
| `for (let i = 0; i < firstDay; i++) { ... }` | Dibujo los espacios en blanco antes del primer día del mes. |
| `for (let d = 1; d <= days; d++) { ... }` | Dibujo un cuadrito por cada día del mes. Si el día coincide con la fecha de hoy, le agrego la clase `cal-today` para resaltarlo de color. |
| `prevBtn.addEventListener('click', () => { calMonth--; ... renderCal(); });` | Cuando hago clic en la flecha izquierda, resto un mes y vuelvo a dibujar el calendario. Si estaba en Enero, paso a Diciembre del año anterior. |
| `nextBtn.addEventListener('click', () => { calMonth++; ... renderCal(); });` | Cuando hago clic en la flecha derecha, avanzo un mes y vuelvo a dibujar. Si paso de Diciembre, arranco en Enero del próximo año. |

---

## 6. Ventana emergente de Nueva Cita (`setupCreateCitaModal`)

| Código | ¿Qué hace?  |
| :--- | :--- |
| `const modal = document.querySelector(config.selectors.modalNew);` | Busco la ventana emergente en el documento. |
| `function abrirModal() { modal.classList.add('activo'); document.body.style.overflow = 'hidden'; }` | Le pongo la clase `activo` para que aparezca en pantalla y congelo el scroll de la página para que no se mueva el fondo. |
| `function cerrarModal() { modal.classList.remove('activo'); document.body.style.overflow = ''; }` | Le quito la clase para ocultarla y devuelvo el scroll normal a la página. |
| `abrir.addEventListener('click', abrirModal);` | Al hacer clic en el botón `+ Nueva cita`, la abro. |
| `cerrar.addEventListener('click', cerrarModal);` | Al hacer clic en la "X", la cierro. |
| `cancelar.addEventListener('click', cerrarModal);` | Al hacer clic en "Cancelar", la cierro. |
| `modal.addEventListener('click', (e) => { if (e.target === modal) cerrarModal(); });` | Si la administradora hace clic afuera de la ventanita (en la parte oscura), se cierra automáticamente. |
| `document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrarModal(); });` | Si presiona la tecla `Escape` en el teclado, también se cierra. |

---

## 7. Función principal y arranque automático (`initCitas`)

| Código | ¿Qué hace?  |
| :--- | :--- |
| `export function initCitas(customConfig = {}) {` | Es la función que enciende todo el módulo de citas. |
| `const container = document.querySelector(...);` | Leo si en el HTML se configuraron rutas especiales mediante atributos `data-*`. |
| `setupCitasTable(config);` | Enciendo la tabla con sus filtros. |
| `setupCitasActions(config);` | Enciendo las confirmaciones de completar y cancelar. |
| `setupCalendar(config);` | Enciendo el calendario interactivo. |
| `setupCreateCitaModal(config);` | Enciendo la ventana flotante de registrar cita. |
| `document.addEventListener('DOMContentLoaded', () => { ... });` | Apenas el navegador termina de leer todo el HTML de la página, reviso si existe la tabla de citas y, si está, arranco todo de forma automática. |
