# Explicación de mi código de Reseñas del Trabajador (`resenas.js`)

En este archivo controlo el filtro de calificaciones en el panel de opiniones de la especialista en **LashApp**. Permite que la trabajadora revise lo que opinan sus clientas sobre su atención y filtre los comentarios mediante píldoras interactivas (*Todas*, *5 estrellas*, *4 estrellas*, etc.).

---

## 1. Libreta de configuraciones (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `containerSelector: '#resenasTrabajadorContainer',` | El contenedor principal de la vista de reseñas de la trabajadora. |
| `tabBtns: '.tab-pill-item',` | Los botones redondeados tipo píldora para seleccionar la calificación. |
| `items: '#resenasList .resena-row-item',` | Cada una de las tarjetas de opinión dejadas por las clientas. |

---

## 2. Filtrado de Opiniones por Estrellas (`initFiltros`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `tabBtns.forEach((btn) => { btn.addEventListener('click', function () { ... }); });` | Escucho el clic en cada píldora de filtro. |
| `tabBtns.forEach((b) => b.classList.remove(config.classes.active));` | Desactivo la píldora que estaba encendida anteriormente. |
| `this.classList.add(config.classes.active);` | Enciendo la píldora que la especialista acaba de tocar. |
| `const filter = this.getAttribute('data-filter') \|\| 'all';` | Leo cuántas estrellas quiere ver (ej. "5", "4", "3" o "all" para ver todas). |
| `items.forEach((item) => { ... });` | Reviso cada tarjeta de reseña de la lista: |
| `const itemRating = item.getAttribute('data-rating-type');` | Leo la calificación que tiene esa tarjeta de opinión. |
| `if (filter === 'all' \|\| itemRating === filter) { item.style.display = 'flex'; }` | Si coincide con el filtro elegido (o si se eligió ver todas), la muestro en pantalla. |
| `else { item.style.display = 'none'; }` | Si no coincide con las estrellas seleccionadas, la escondo. |

---

## 3. Inicialización (`initResenasTrabajador`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initResenasTrabajador(customConfig = {}) {` | Enciende el filtrado de opiniones por estrellas. |
| `document.addEventListener('DOMContentLoaded', () => initResenasTrabajador());` | Arranca todo de forma automática en cuanto la página esté lista. |
