# Explicación de `catalogo.js`

Este archivo da vida al catálogo de servicios para los clientes. Permite buscar servicios en tiempo real por nombre, filtrarlos por categoría (pestañas) y por rangos de precio, ordenarlos según popularidad o costo, cambiar la forma de visualización (cuadrícula o lista), marcar favoritos con alertas flotantes y resetear todos los filtros con un clic.

---

### 1. Variables globales e inicialización (`initCatalogo`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `let categoriaActiva = 'todas';` | Guardo cuál es la pestaña de categoría seleccionada actualmente, empezando en "todas". |
| `export function initCatalogo() {` | Función que conecta los eventos de la barra de búsqueda y los selectores del catálogo al cargar la página. |
| `const inputBuscar = document.getElementById('buscarServicioInput'); if (inputBuscar) { inputBuscar.addEventListener('input', filtrarServicios); }` | Escucho cada letra que el cliente escribe en el buscador para filtrar en vivo. |
| `const selectPrecio = document.getElementById('precioSelect'); if (selectPrecio) { selectPrecio.addEventListener('change', filtrarPorPrecio); }` | Cuando el cliente cambia el rango de precios en el selector, ejecuto el filtro. |
| `const selectOrdenar = document.getElementById('ordenarSelect'); if (selectOrdenar) { selectOrdenar.addEventListener('change', ordenarServicios); }` | Cuando el cliente escoge un criterio de orden (menor precio, popularidad, etc.), reorganizo las tarjetas. |

---

### 2. Filtrado por categorías (`filtrarPorCategoria`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function filtrarPorCategoria(categoria, boton) {` | Función que se ejecuta al presionar una pestaña de categoría (pestañas pelo a pelo, cejas, etc.). |
| `categoriaActiva = categoria;` | Guardo la categoría elegida como la categoría activa en memoria. |
| `document.querySelectorAll('.cat-tab-item').forEach(btn => btn.classList.remove('active'));` | Les quito el estilo de seleccionado a todos los botones de categorías. |
| `if (boton) { boton.classList.add('active'); }` | Le pongo el estilo resaltado al botón que acabo de presionar. |
| `filtrarServicios();` | Vuelvo a evaluar los servicios para mostrar solo los que pertenecen a esta categoría. |

---

### 3. Filtro combinado en vivo (`filtrarServicios`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function filtrarServicios() {` | Es el motor principal que evalúa texto, categoría y precio al mismo tiempo. |
| `const query = inputBuscar ? inputBuscar.value.toLowerCase().trim() : '';` | Obtengo el texto de búsqueda en minúsculas y sin espacios a los lados. |
| `const rangoPrecio = selectPrecio ? selectPrecio.value : 'todos';` | Obtengo el filtro de precio seleccionado (`todos`, `0-100`, `100-200`, `200+`). |
| `const cards = document.querySelectorAll('#catCardsGrid .cat-card-item');` | Obtengo todas las tarjetas de servicios del catálogo. |
| `let visibles = 0;` | Llevo la cuenta de cuántos servicios cumplen con los criterios. |
| `cards.forEach(card => { ... });` | Reviso cada tarjeta de servicio una por una. |
| `const coincideTexto = !query \|\| nombre.includes(query) \|\| categoria.includes(query);` | Reviso si el nombre o la categoría del servicio contienen la palabra buscada. |
| `const coincideCategoria = (categoriaActiva === 'todas') \|\| categoria.includes(categoriaActiva);` | Reviso si el servicio corresponde a la pestaña de categoría seleccionada. |
| `let coincidePrecio = true; if (rangoPrecio === '0-100') { coincidePrecio = precio <= 100000; } ...` | Evalúo si el precio del servicio cae dentro del rango seleccionado (hasta $100.000, entre $100.000 y $200.000, o más de $200.000). |
| `if (coincideTexto && coincideCategoria && coincidePrecio) { card.style.display = ''; visibles++; } else { card.style.display = 'none'; }` | Si cumple con las tres condiciones a la vez, dejo la tarjeta visible y sumo 1 a los visibles; de lo contrario, la escondo. |
| `if (counter) counter.textContent = visibles;` | Actualizo el contador de pantalla para que diga cuántos servicios coinciden. |
| `if (noResults) { noResults.style.display = (visibles === 0) ? 'block' : 'none'; }` | Si no hay ningún servicio que coincida, muestro un aviso avisándole al cliente que no se encontraron resultados. |
| `export function filtrarPorPrecio() { filtrarServicios(); }` | Llamo a la función principal cada vez que cambia el precio. |

---

### 4. Ordenamiento dinámico de tarjetas (`ordenarServicios`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function ordenarServicios() {` | Función que cambia el orden de las tarjetas en la pantalla sin recargar la página. |
| `const cards = Array.from(grid.querySelectorAll('.cat-card-item'));` | Convierto la lista de tarjetas en un arreglo para poder ordenarlas fácilmente. |
| `cards.sort((a, b) => { ... });` | Comparo las tarjetas según la opción que eligió el cliente. |
| `if (criterio === 'menor_precio') return precioA - precioB;` | Ordeno del más económico al más costoso. |
| `else if (criterio === 'mayor_precio') return precioB - precioA;` | Ordeno del más costoso al más económico. |
| `else if (criterio === 'nombre_asc') return nomA.localeCompare(nomB);` | Ordeno en orden alfabético de la A a la Z. |
| `else return popB - popA;` | Por defecto, coloco primero los servicios más populares (mayor puntaje o cantidad de reservas). |
| `cards.forEach(card => grid.appendChild(card));` | Reinserto las tarjetas en el nuevo orden en la cuadrícula de la página. |

---

### 5. Alternar vista de cuadrícula o lista (`cambiarVista`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function cambiarVista(tipo) {` | Función para cambiar la apariencia entre cuadrícula (bloques) o lista horizontal. |
| `if (tipo === 'list') { grid.classList.add('list-view'); ... }` | Si elegí lista, le agrego la clase `list-view` al contenedor para acomodar los servicios en filas horizontales y resalto el botón de lista. |
| `else { grid.classList.remove('list-view'); ... }` | Si elegí cuadrícula, le quito esa clase para que vuelvan a verse en columnas. |

---

### 6. Guardar en favoritos (`toggleFavorito`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function toggleFavorito(button, nombreServicio) {` | Función que se ejecuta cuando toco el botón con forma de corazón en un servicio. |
| `button.classList.toggle('active');` | Enciendo o apago la clase `active` en el botón. |
| `if (esFavorito) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); ... }` | Si quedó marcado como favorito, pinto el corazón relleno (`fa-solid`) y muestro una notificación emergente ("toast") en la esquina superior diciendo que se añadió a favoritos. |
| `else { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); }` | Si se desmarcó, regreso el corazón a su versión de solo borde (`fa-regular`). |

---

### 7. Limpiar y reiniciar filtros (`limpiarFiltros`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function limpiarFiltros() {` | Función del botón "Limpiar filtros" para volver todo a su estado original. |
| `if (inputBuscar) inputBuscar.value = '';` | Borro el texto del buscador. |
| `if (selectPrecio) selectPrecio.value = 'todos';` | Vuelvo el selector de precios a "todos". |
| `if (selectOrdenar) selectOrdenar.value = 'populares';` | Pongo el orden en los más populares. |
| `filtrarPorCategoria('todas', tabTodas);` | Selecciono nuevamente la pestaña de todas las categorías y refresco la vista. |

---

### 8. Exposición global y ejecución inicial

| Código | ¿Qué hace? |
| :--- | :--- |
| `window.filtrarPorCategoria = filtrarPorCategoria; ...` | Registro todas las funciones en el objeto `window` para que funcionen desde los atributos `onclick` o `onchange` en el HTML. |
| `document.addEventListener('DOMContentLoaded', initCatalogo);` | Arranco la preparación del catálogo en cuanto carga la página. |
