# Explicación de mi código de Gestión de Reseñas (`resenas.js`)

En este archivo controlo todo lo relacionado con las opiniones, valoraciones y calificaciones que dejan las clientas en **LashApp**. Gracias a este código, la administradora puede filtrar las opiniones en tiempo real (por ejemplo, buscar todas las clientas que dejaron 5 estrellas o ver qué comentarios contienen cierta palabra), usar un selector interactivo de estrellas doradas y borrar reseñas inapropiadas o falsas con una confirmación de seguridad.

---

## 1. Importación de alertas (`alerts.js`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `import { confirmAction } from '../../common/alerts.js';` | Cargo mi ventana de advertencia para preguntar con seguridad antes de eliminar una reseña de la base de datos. |

---

## 2. Libreta de configuraciones (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const DEFAULT_CONFIG = {` | Inicio mi lista de selectores y rutas del módulo. |
| `starSelector: '#starSelector',` | El contenedor donde están dibujadas las 5 estrellitas interactivas. |
| `inputCalificacion: '#input_calificacion',` | El campo oculto del formulario donde se guarda el número de estrellas (del 1 al 5). |
| `searchInput: '#buscarResena',` | La caja de texto para buscar por nombre de clienta o por palabras dentro del comentario. |
| `starsFilter: '#filtroEstrellas',` | El menú desplegable para filtrar por cantidad de estrellas (5 estrellas, 4 estrellas, etc.). |
| `tableRows: '#tablaResenas tbody tr',` | Cada fila de la tabla donde se muestran los comentarios de las clientas. |
| `formDelete: '#formEliminarResena',` | El formulario oculto para procesar la eliminación segura. |
| `btnDeleteTrigger: '.btn-eliminar-resena',` | El botón de la papelera roja en cada fila de la tabla. |
| `baseUrl: '/admin/resenas',` | La dirección web base para enviar las órdenes de eliminación. |
| `messages: { ... }` | El mensaje de advertencia: *"¿Estás seguro de eliminar la reseña de [Cliente]? Esta acción no se puede deshacer"*. |

---

## 3. Selector de Estrellas Interactivo (`setupStarRating`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupStarRating(config) {` | La función que hace que las estrellitas brillen al hacerles clic. |
| `const starSelector = document.querySelector(...);` | Busco el contenedor de las estrellas en el formulario. |
| `const inputCalificacion = document.querySelector(...);` | Busco la casilla donde se anota el valor final. |
| `const stars = starSelector.querySelectorAll('i');` | Selecciono los 5 íconos de estrellas. |
| `stars.forEach((star) => { star.addEventListener('click', function () { ... }); });` | Le pongo un detector de clic a cada estrellita. |
| `const val = parseInt(this.dataset.value);` | Leo qué número representa la estrella que tocaron (ej. si tocó la cuarta estrella, vale 4). |
| `inputCalificacion.value = val;` | Guardo ese número `4` en el formulario para que viaje al servidor al guardar. |
| `stars.forEach((s) => { ... });` | Recorro las 5 estrellas para iluminarlas: |
| `if (sVal <= val) { s.classList.add('active'); }` | Todas las estrellas con un número menor o igual al que toqué se encienden con la clase `active` (doradas). |
| `else { s.classList.remove('active'); }` | Las estrellas que queden por encima se apagan y quedan grises. |

---

## 4. Buscador y Filtro por Cantidad de Estrellas (`setupResenasFilter`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupResenasFilter(config) {` | La función que busca comentarios en tiempo real. |
| `const texto = (buscar ? buscar.value : '').toLowerCase().trim();` | Lo que escribió la administradora en el buscador (ej. "excelente"). |
| `const estrellas = (filtro ? filtro.value : '').trim();` | La cantidad de estrellas que eligió en el desplegable (ej. "5"). |
| `filas.forEach((fila) => { ... });` | Reviso cada una de las opiniones de la lista: |
| `const coincideTexto = !texto \|\| contenido.includes(texto);` | Reviso si la opinión contiene la palabra buscada o el nombre de la clienta. |
| `const coincideEstrellas = !estrellas \|\| filaEstrellas === estrellas;` | Reviso si la reseña tiene la misma cantidad de estrellas seleccionadas. |
| `fila.style.display = (coincideTexto && coincideEstrellas) ? '' : 'none';` | Si cumple con ambas condiciones, muestro la fila; de lo contrario, la escondo. |
| `buscar.addEventListener('input', filtrarResenas);` | Filtro cada vez que se teclea en el buscador. |
| `filtro.addEventListener('change', filtrarResenas);` | Filtro cada vez que se cambia el filtro de estrellas. |

---

## 5. Eliminar Reseña con Advertencia (`setupDeleteResena`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `deleteBtns.forEach((btn) => { btn.addEventListener('click', async function () { ... }); });` | Escucho el clic en cada botón de caneca de basura de las reseñas. |
| `const confirmed = await confirmAction({ ... });` | Pregunto con una ventana roja: *"¿Eliminar reseña? ¿Estás seguro de eliminar la reseña de [Cliente]? Esta acción no se puede deshacer"*. |
| `if (confirmed) { ... }` | Si la administradora presiona "Sí, eliminar": |
| `formEliminar.action = `${baseUrl}/${encodeURIComponent(id)}`;` | Le pongo la dirección exacta de la reseña al formulario (`/admin/resenas/8`). |
| `formEliminar.submit();` | Envío la orden al servidor para borrarla definitivamente. |

---

## 6. Inicialización del Módulo (`initResenas`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initResenas(customConfig = {}) {` | Arranco todas las funciones de reseñas en el orden correcto. |
| `setupStarRating(config);` | Activo las estrellitas interactivas para calificar. |
| `setupResenasFilter(config);` | Activo el buscador y el filtro de estrellas. |
| `setupDeleteResena(config);` | Activo la confirmación de eliminación segura. |
| `document.addEventListener('DOMContentLoaded', ...);` | Ejecuto todo de forma automática apenas el navegador cargue la página. |
