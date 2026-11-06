# Explicación de mi código de Servicios del Trabajador (`servicios.js`)

En este archivo controlo la gestión del catálogo de servicios desde la perspectiva del personal en **LashApp**. Permite que la trabajadora busque tratamientos en vivo, filtre por categoría, abra los modales para crear o editar servicios con autocompletado y conversión automática de horas a minutos, y elimine servicios mediante avisos de confirmación.

---

## 1. Libreta de configuraciones (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `containerSelector: '#serviciosTrabajadorContainer',` | El contenedor principal de la vista de servicios del trabajador. |
| `modalNuevo: '#modalNuevoServicio', btnAbrirNuevo: '#abrirNuevoServicio', ...` | Los selectores del modal para agregar un servicio nuevo al catálogo. |
| `modalEditar: '#modalEditarServicio', formEditar: '#formEditarServicio', ...` | Los selectores del modal y formulario para actualizar datos de un servicio. |
| `fields: { id, nombre, categoriaId, precio, duracionMinuto, estado, descripcion }` | La lista con cada una de las casillas del formulario de edición. |
| `buscarInput: '#buscarServicio', filtroCat: '#filtroCategoria',` | La caja de búsqueda y el menú desplegable de categorías. |
| `infoConteo: '#infoConteoServicios',` | El letrero que dice cuántos servicios se están mostrando del total. |
| `formEliminar: '#formEliminarServicio',` | El formulario oculto de eliminación segura. |
| `urlUpdate: '/servicios/:id', urlDestroy: '/servicios/:id',` | Las rutas para actualizar y eliminar servicios en el servidor. |

---

## 2. Alertas de Sesión (`handleSessionAlerts`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const successMsg = container.dataset.sessionSuccess;` | Revisa si el servidor devolvió un mensaje de éxito. |
| `if (successMsg) { Swal.fire({ icon: 'success', title: '¡Operación Exitosa!' ... }); }` | Muestra la ventana de éxito morada. |
| `else if (errorMsg) { Swal.fire({ icon: 'error', title: 'Error' ... }); }` | Muestra la alerta de error si faltó algún campo obligatorio. |

---

## 3. Control de Modales de Creación y Edición (`initModals`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function abrirNuevo() { modalNuevo.classList.add('activo'); ... }` | Abre la ventana de nuevo servicio. |
| `function cerrarNuevo() { modalNuevo.classList.remove('activo'); ... }` | Cierra la ventana de nuevo servicio. |
| `function cerrarEditar() { modalEditar.classList.remove('activo'); ... }` | Cierra la ventana de edición de servicio. |
| `btnAbrirNuevo.addEventListener('click', abrirNuevo);` | Abre el modal al tocar `+ Nuevo Servicio`. |
| `[modalNuevo, modalEditar].forEach(modal => { ... });` | Cierra cualquiera de los dos modales si se hace clic afuera en el fondo oscuro. |
| `document.addEventListener('keydown', (e) => { if (e.key === 'Escape') ... });` | Cierra los modales al presionar la tecla Escape en el teclado. |

---

## 4. Llenado Dinámico al Editar (`abrirModalEditar`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function abrirModalEditar(servicio) { ... }` | Función llamada al hacer clic en el lápiz de editar de cualquier servicio. |
| `formEditar.action = templateUpdate.replace(':id', servicio.id);` | Ajusta la ruta del formulario para que apunte exactamente al ID de ese servicio. |
| `if (elId) elId.value = servicio.id;` | Asigna el ID del servicio. |
| `if (elNombre) elNombre.value = servicio.nombre;` | Rellena la casilla del nombre del servicio. |
| `if (elCat) elCat.value = servicio.categoria_id;` | Selecciona la categoría correspondiente en el desplegable. |
| `if (elPrecio) elPrecio.value = Math.round(servicio.precio);` | Rellena el precio redondeado sin decimales extraños. |
| `if (String(servicio.duracion_minuto).includes(':')) { minutos = (parseInt(partes[0]) * 60) + parseInt(partes[1]); }` | Convierte inteligentemente la duración si viene en formato `HH:MM:SS` (ej. `01:30:00`) a minutos numéricos (`90`) para que la casilla numérica lo acepte sin errores. |
| `if (elEstado) elEstado.value = servicio.estado;` | Selecciona si está activo o inactivo. |
| `if (elDesc) elDesc.value = servicio.descripcion;` | Rellena el campo de descripción. |
| `modalEditar.classList.add('activo');` | Muestra el modal con todos los datos listos para modificar. |

---

## 5. Buscador y Filtro por Categoría en vivo (`initFiltros`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const matchQuery = !query \|\| nombre.includes(query) \|\| categoria.includes(query) \|\| precio.includes(query);` | Revisa si el texto coincide con el nombre, la categoría o el precio del servicio. |
| `const matchCat = !cat \|\| categoria === cat;` | Revisa si la categoría coincide con la del menú desplegable. |
| `row.style.display = (matchQuery && matchCat) ? '' : 'none';` | Si coincide en ambos, la fila queda visible; si no, se oculta. |
| `if (infoConteo) { infoConteo.textContent = 'Mostrando ' + visibles + ' de ' + totalServicios + ' servicios'; }` | Actualiza el contador inferior para indicar cuántos servicios coinciden. |
| `buscarInput.addEventListener('input', filtrar);` | Filtra al instante mientras se teclea en el buscador. |
| `filtroCat.addEventListener('change', filtrar);` | Filtra al instante al cambiar de categoría. |

---

## 6. Eliminar Servicio con Advertencia (`confirmarEliminarServicio`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function confirmarEliminarServicio(idServicio) { ... }` | Abre una advertencia en rojo: *"¿Eliminar servicio? Esta acción no se puede deshacer"*. |
| `if (result.isConfirmed) { ejecutarEliminacion(idServicio); }` | Si la trabajadora confirma, le asigna la ruta al formulario oculto y lo envía para borrar el servicio. |

---

## 7. Inicialización (`initServiciosTrabajador`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initServiciosTrabajador(customConfig = {}) {` | Enciende los modales, filtros, alertas y acciones del catálogo. |
| `window.abrirModalEditar = abrirModalEditar; ...` | Expone las funciones globalmente para retrocompatibilidad con los botones del HTML. |
| `document.addEventListener('DOMContentLoaded', () => initServiciosTrabajador());` | Arranca todo automáticamente al cargar la página. |
