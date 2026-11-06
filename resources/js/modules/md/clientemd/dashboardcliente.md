# Explicación de `dashboardcliente.js`

Este archivo se encarga de darle interactividad al panel principal (dashboard) del cliente: muestra alertas si una acción salió bien, abre y cierra la ventana emergente (modal) con la agenda de horarios y filtra las tarjetas de agenda en tiempo real mientras escribo.

---

### 1. Inicialización del panel (`initDashboardCliente`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initDashboardCliente() {` | Creo la función principal que prepara todas las acciones del panel apenas carga la página. |
| `const container = document.getElementById('dashboardClienteContainer') \|\| document.querySelector('.page-content');` | Busco el contenedor principal de la vista del cliente. |
| `if (container && container.dataset.sessionSuccess) {` | Reviso si el servidor envió un mensaje guardado de que una operación fue exitosa. |
| `if (typeof Swal !== 'undefined') { Swal.fire({ ... }); }` | Si la librería SweetAlert2 está disponible, muestro una ventana bonita con el mensaje de éxito en morado (`#7c1db8`). |
| `const modalAgenda = document.getElementById('modalAgenda');` | Busco en la página la ventana modal donde se ven los horarios y la agenda. |
| `if (modalAgenda) { modalAgenda.addEventListener('click', (e) => { if (e.target === modalAgenda) cerrarModalAgenda(); }); }` | Si hago clic en la parte oscura que rodea al modal (el fondo), cierro la ventana automáticamente. |
| `const inputFiltro = document.getElementById('filtroAgenda');` | Localizo la cajita de texto donde el cliente puede escribir para buscar. |
| `if (inputFiltro) { inputFiltro.addEventListener('input', filtrarAgendas); }` | Cada vez que escribo o borro una letra, ejecuto el filtro en vivo. |

---

### 2. Abrir y cerrar el modal de agenda

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function abrirModalAgenda() {` | Función para abrir la ventana de agenda de citas. |
| `const modal = document.getElementById('modalAgenda');` | Busco el modal en la pantalla. |
| `if (modal) { modal.classList.add('activo'); document.body.style.overflow = 'hidden'; }` | Le pongo la clase `activo` para que se haga visible y congelo la barra de desplazamiento de la página para que el fondo no se mueva. |
| `export function cerrarModalAgenda() {` | Función para cerrar la ventana emergente. |
| `if (modal) { modal.classList.remove('activo'); document.body.style.overflow = ''; }` | Le quito la clase `activo` para esconderlo y devuelvo el movimiento normal a la página. |

---

### 3. Filtrar las tarjetas de agenda en tiempo real

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function filtrarAgendas() {` | Función que busca y muestra solo las tarjetas que coincidan con lo que escribí. |
| `const input = document.getElementById('filtroAgenda'); if (!input) return;` | Obtengo el campo de texto; si no existe, no hago nada. |
| `const query = input.value.toLowerCase().trim();` | Convierto el texto que escribí a minúsculas y le quito espacios sobrantes al inicio y final. |
| `const cards = document.querySelectorAll('#contenedorAgendas .agenda-card');` | Selecciono todas las tarjetitas de agenda que hay en la lista. |
| `cards.forEach(card => {` | Reviso una por una cada tarjeta. |
| `const searchData = (card.getAttribute('data-search') \|\| '').toLowerCase();` | Extraigo el texto de búsqueda que tiene guardado la tarjeta en minúsculas. |
| `card.style.display = (!query \|\| searchData.includes(query)) ? 'flex' : 'none';` | Si el buscador está vacío o la tarjeta contiene lo que escribí, la muestro (`flex`); si no coincide, la oculto (`none`). |

---

### 4. Disponibilidad global y arranque automático

| Código | ¿Qué hace? |
| :--- | :--- |
| `window.abrirModalAgenda = abrirModalAgenda;` | Dejo la función disponible globalmente para poder llamarla desde botones en el HTML. |
| `window.cerrarModalAgenda = cerrarModalAgenda;` | Dejo disponible el cierre del modal en todo el navegador. |
| `window.filtrarAgendas = filtrarAgendas;` | Dejo disponible el buscador globalmente. |
| `document.addEventListener('DOMContentLoaded', initDashboardCliente);` | En cuanto el navegador termina de leer todo el documento HTML, ejecuto la inicialización. |
