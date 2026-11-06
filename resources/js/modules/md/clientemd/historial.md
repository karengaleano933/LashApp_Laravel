# Explicación de `historial.js`

Este archivo administra el historial completo del cliente (citas pasadas, servicios tomados y pagos realizados). Permite filtrar por pestañas, sincronizar los selectores desplegables con las pestañas, buscar por fechas, filtrar por especialista y servicio, ordenar las tarjetas cronológicamente o por precio, resetear los filtros y exportar/imprimir el historial en PDF con un diseño optimizado.

---

### 1. Variables de control e inicialización (`initHistorial`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `let tabActiva = 'todos';` | Variable en memoria para recordar qué pestaña está activa (todos, citas, servicios o pagos). |
| `export function initHistorial() {` | Función que activa los escuchadores de eventos para todos los filtros del historial cuando carga la página. |
| `if (filterFecha) { filterFecha.addEventListener('input', aplicarFiltrosYOrden); }` | Cada vez que escribo una fecha en el buscador, actualizo el listado. |
| `if (filterServicio) { filterServicio.addEventListener('change', aplicarFiltrosYOrden); }` | Cuando elijo un servicio en el menú desplegable, aplico los filtros. |
| `if (filterProf) { filterProf.addEventListener('change', aplicarFiltrosYOrden); }` | Cuando elijo una profesional o especialista, aplico los filtros. |
| `if (filterTipo) { filterTipo.addEventListener('change', function() { sincronizarTipoConTab(this.value); }); }` | Si cambio el tipo de registro desde el selector desplegable, sincronizo la pestaña correspondiente arriba. |
| `if (orderSelect) { orderSelect.addEventListener('change', aplicarFiltrosYOrden); }` | Cuando elijo un orden (más recientes, más antiguos, mayor o menor precio), reordeno las tarjetas. |

---

### 2. Sincronización entre pestañas y selector desplegable

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function filtrarHistorialTab(tipo, btn) {` | Función que se ejecuta cuando toco una de las pestañas superiores (Todos, Citas, Servicios, Pagos). |
| `tabActiva = tipo;` | Guardo el tipo seleccionado en la variable activa. |
| `document.querySelectorAll('.hist-tab-btn').forEach(b => b.classList.remove('active')); if (btn) btn.classList.add('active');` | Desactivo todas las pestañas y marco la seleccionada con el color activo. |
| `if (tipoSelect) tipoSelect.value = tipo;` | Ajusto el selector desplegable de tipo para que coincida exactamente con la pestaña que presioné. |
| `aplicarFiltrosYOrden();` | Vuelvo a filtrar las tarjetas con la nueva selección. |
| `export function sincronizarTipoConTab(tipo) {` | Función inversa: si el usuario cambió el filtro desde el menú desplegable, busco y marco la pestaña correcta arriba. |

---

### 3. Filtro combinado y ordenamiento en vivo (`aplicarFiltrosYOrden`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function aplicarFiltrosYOrden() {` | Es el motor principal que evalúa pestañas, texto de fecha, servicios, especialistas y aplica el orden solicitado. |
| `const cards = Array.from(container.querySelectorAll('.hist-card-item'));` | Convierto todas las tarjetas del historial en un arreglo para poder filtrarlas y ordenarlas. |
| `let matchTab = true; if (tabActiva === 'citas') { ... }` | Compruebo si la tarjeta corresponde a la pestaña activa (si es cita, servicio o pago). |
| `const matchFecha = !fechaQuery \|\| cardFecha.includes(fechaQuery) \|\| cardText.includes(fechaQuery);` | Compruebo si la fecha o el texto de la tarjeta contienen la fecha que busqué. |
| `const matchServicio = (servicioFiltro === 'todos' \|\| cardServicio.includes(servicioFiltro) ...);` | Verifico si coincide con el servicio seleccionado. |
| `const matchProfesional = (profesionalFiltro === 'todas' \|\| cardProfesional.includes(profesionalFiltro) ...);` | Verifico si coincide con la especialista elegida. |
| `if (matchTab && matchFecha && matchServicio && matchProfesional) { card.style.display = 'flex'; visibles++; } else { card.style.display = 'none'; }` | Si cumple todas las condiciones al tiempo, muestro la tarjeta (`flex`); de lo contrario la oculto (`none`). |
| `cards.sort((a, b) => { ... });` | Reordeno las tarjetas en pantalla según la opción seleccionada. |
| `if (orden === 'recientes') return fechaB - fechaA;` | Coloco primero las fechas más recientes. |
| `if (orden === 'antiguos') return fechaA - fechaB;` | Coloco primero las fechas más antiguas. |
| `if (orden === 'mayor_precio') return precioB - precioA;` | Coloco primero las de mayor valor económico. |
| `if (orden === 'menor_precio') return precioA - precioB;` | Coloco primero las de menor valor económico. |
| `cards.forEach(card => container.appendChild(card));` | Reacomodo las tarjetas en el nuevo orden dentro del contenedor. |
| `if (emptyState) { emptyState.style.display = (visibles === 0) ? 'block' : 'none'; }` | Si ninguna tarjeta coincide con la búsqueda, muestro el mensaje de "no se encontraron registros". |

---

### 4. Reinicio de filtros (`resetFiltros`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function resetFiltros() {` | Función que devuelve todos los campos de búsqueda y filtros a su estado por defecto. |
| `tabActiva = 'todos';` | Vuelvo a la pestaña "Todos". |
| `document.querySelectorAll('.hist-tab-btn').forEach(b => { ... });` | Marco activa la pestaña "Todos" y desmarco las demás. |
| `if (fechaInput) fechaInput.value = '';` | Borro el campo de fecha. |
| `if (tipoSelect) tipoSelect.value = 'todos';` | Regreso el tipo a "todos". |
| `if (servSelect) servSelect.value = 'todos';` | Regreso el servicio a "todos". |
| `if (profSelect) profSelect.value = 'todas';` | Regreso las profesionales a "todas". |
| `if (ordSelect) ordSelect.value = 'recientes';` | Vuelvo el orden a "más recientes". |
| `aplicarFiltrosYOrden();` | Vuelvo a mostrar todos los elementos originales. |

---

### 5. Descargar o imprimir reporte en PDF (`descargarHistorialPDF`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function descargarHistorialPDF() {` | Función que prepara y abre la ventana para imprimir o guardar el historial en formato PDF. |
| `if (typeof Swal !== 'undefined') { Swal.fire({ ... }).then((result) => { if (result.isConfirmed) { window.print(); } }); }` | Muestro una ventana bonita informando que se abrirá la vista de impresión optimizada para guardar en PDF; si el usuario acepta, llamo a la función `window.print()` del navegador. |
| `else { window.print(); }` | Si no estuviera la librería de alertas, abro la ventana de impresión directamente. |

---

### 6. Disponibilidad global y ejecución inicial

| Código | ¿Qué hace? |
| :--- | :--- |
| `window.filtrarHistorialTab = filtrarHistorialTab; ...` | Expongo todas las funciones al entorno global de la página (`window`) para llamarlas desde eventos HTML. |
| `document.addEventListener('DOMContentLoaded', initHistorial);` | Pongo en marcha los filtros apenas termina de cargarse el HTML. |
