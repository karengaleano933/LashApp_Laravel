# Explicación de `resenas.js`

Este archivo maneja la ventana emergente para que el cliente califique y deje su reseña de una cita recibida. Controla el selector interactivo de estrellas (con animación al pasar el cursor y al hacer clic, y mensajes descriptivos) y el comportamiento del modal.

---

### 1. Mensajes de satisfacción y puntaje inicial

| Código | ¿Qué hace? |
| :--- | :--- |
| `const ratingTexts = { 1: '...', 2: '...', 3: '...', 4: '...', 5: '...' };` | Creo un listado con los textos descriptivos según la cantidad de estrellas seleccionadas: 1 estrella ("Muy insatisfecha"), 2 ("Podría mejorar"), 3 ("Buena experiencia"), 4 ("Muy buena atención") y 5 ("¡Excelente servicio!"). |
| `let currentScore = 5;` | Guardo en memoria el puntaje inicial seleccionado, que por defecto arranca en 5 estrellas. |

---

### 2. Inicialización y selector interactivo de estrellas (`initResenasCliente`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initResenasCliente() {` | Función que activa la interactividad de las estrellas y el modal cuando carga la pantalla. |
| `const stars = document.querySelectorAll('#starRatingSelector i');` | Localizo todos los íconos de estrellas dentro del selector de calificación. |
| `const inputVal = document.getElementById('inputCalificacionEstrellas');` | Busco el campo oculto del formulario donde se guardará el número final (1 al 5). |
| `const feedbackText = document.getElementById('ratingTextFeedback');` | Localizo el texto donde se muestra la descripción de satisfacción. |
| `function updateStars(score) { ... }` | Función interna que pinta de amarillo dorado las estrellas hasta el número indicado y deja vacías las demás. |
| `if (val <= score) { star.className = 'fa-solid fa-star active'; } else { star.className = 'fa-regular fa-star'; }` | Si el valor de la estrella es menor o igual al puntaje, la llena (`fa-solid active`); si es mayor, la deja vacía con borde (`fa-regular`). |
| `feedbackText.textContent = ratingTexts[score] \|\| (score + ' de 5');` | Muestro el texto que acompaña al puntaje seleccionado. |
| `star.addEventListener('mouseenter', function() { ... });` | Cuando paso el ratón por encima de una estrella, resalto temporalmente todas las estrellas hasta esa posición (`hovered`) y muestro el texto correspondiente para previsualizar. |
| `star.addEventListener('mouseleave', () => { updateStars(currentScore); });` | Al retirar el ratón, regreso las estrellas al puntaje real que el usuario había fijado. |
| `star.addEventListener('click', function() { ... });` | Al hacer clic en una estrella, guardo ese número como el puntaje definitivo (`currentScore`), lo coloco en el formulario (`inputVal.value`) y pinto las estrellas. |
| `const overlay = document.getElementById('modalResenaOverlay'); if (overlay) { overlay.addEventListener('click', ...); }` | Si el cliente hace clic en el fondo oscuro fuera de la tarjeta del modal, cierro la ventana automáticamente. |

---

### 3. Abrir y cerrar el modal para calificar

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function abrirModalCalificar(citaId = null, servicioNombre = '', especialistaNombre = '') {` | Función para abrir la ventana donde se escribe la reseña, permitiendo pasar de antemano el código de la cita. |
| `const modal = document.getElementById('modalResenaOverlay');` | Busco la ventana modal en el HTML. |
| `const select = document.getElementById('modalSelectCita'); if (citaId && select) { select.value = citaId; }` | Si me dieron el ID de la cita, preselecciono esa cita en la lista desplegable. |
| `if (modal) { modal.style.display = 'flex'; }` | Hago visible el modal en pantalla centrando su contenido (`flex`). |
| `export function cerrarModalCalificar() {` | Función para esconder la ventana emergente. |
| `if (modal) { modal.style.display = 'none'; }` | Oculto el modal (`none`). |

---

### 4. Disponibilidad global y ejecución automática

| Código | ¿Qué hace? |
| :--- | :--- |
| `window.abrirModalCalificar = abrirModalCalificar;` | Pongo la función de abrir el modal a disposición global para llamarla desde cualquier botón del listado de citas. |
| `window.cerrarModalCalificar = cerrarModalCalificar;` | Pongo a disposición global la función para cerrar la ventana. |
| `document.addEventListener('DOMContentLoaded', initResenasCliente);` | En cuanto la página termina de cargar, configuro las estrellas y los eventos del modal. |
