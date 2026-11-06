# Explicación de mi código de Gestión de Servicios (`servicios.js`)

En este archivo controlo todo el catálogo de tratamientos y servicios que ofrece **LashApp** (extensiones clásicas, volumen ruso, lifting de pestañas, diseño de cejas, depilación, etc.). Con este código la administradora puede buscar servicios en vivo, filtrarlos por categoría, abrir y cerrar las ventanas de nuevo servicio o editar uno existente, cargar los datos automáticamente en el formulario de edición y eliminar servicios con un aviso de confirmación.

---

## 1. Importación de alertas (`alerts.js`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `import { confirmAction } from '../../common/alerts.js';` | Traigo mi herramienta de confirmación visual para advertir antes de borrar cualquier servicio del catálogo. |

---

## 2. Libreta de opciones y selectores (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const DEFAULT_CONFIG = {` | Mi libreta donde anoto los identificadores de todos los elementos visuales del catálogo. |
| `searchInput: '#buscarServicio',` | La caja de texto para buscar servicios por nombre. |
| `categoryFilter: '#filtroCategoria',` | El menú desplegable para filtrar por categoría (Pestañas, Cejas, etc.). |
| `tableRows: '#tablaServicios tbody tr',` | Cada fila de la tabla del catálogo. |
| `modalCreate: '#modalCrearServicio',` | La ventana emergente para registrar un nuevo servicio. |
| `btnOpenCreate: '#abrirNuevoServicio',` | El botón morado superior `+ Nuevo Servicio`. |
| `btnCloseCreate: '#cerrarModalServicio',` | La "X" para cerrar la ventana de nuevo servicio. |
| `btnCancelCreate: '#cancelarModalServicio',` | El botón de cancelar en la ventana de nuevo servicio. |
| `modalEdit: '#modalEditarServicio',` | La ventana emergente para modificar un servicio. |
| `btnCloseEdit: '#cerrarModalEditarServicio',` | La "X" para cerrar la ventana de edición. |
| `btnCancelEdit: '#cancelarModalEditarServicio',` | El botón de cancelar en la ventana de edición. |
| `formEdit: '#formEditarServicio',` | El formulario HTML que enviará los cambios editados. |
| `btnEditTrigger: '.btn-editar-servicio',` | El botón de lápiz en cada fila de la tabla. |
| `editFields: { ... }` | La lista de casillas del modal de edición (id, nombre, categoría, precio, duración, descripción, estado). |
| `formDelete: '#formEliminarServicio',` | El formulario invisible para tramitar la eliminación segura. |
| `btnDeleteTrigger: '.btn-eliminar-servicio',` | El botón de la papelera roja para borrar el servicio. |
| `baseUrl: '/admin/servicios',` | La dirección web a donde viajan los datos para guardar o borrar. |
| `messages: { ... }` | La advertencia: *"¿Eliminar servicio? ¿Estás seguro de eliminar [Nombre]? Esta acción no se puede deshacer"*. |

---

## 3. Buscador y Filtro por Categoría en tiempo real (`setupServiceFiltering`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupServiceFiltering(config) {` | La función que busca servicios al instante mientras se teclea. |
| `const searchText = (searchInput ? searchInput.value : '').toLowerCase().trim();` | El texto que escribió la persona en el buscador, en minúsculas y limpio de espacios. |
| `const selectedCategory = (categorySelect ? categorySelect.value : '').toLowerCase().trim();` | La categoría elegida en el menú desplegable. |
| `rows.forEach((row) => { ... });` | Reviso cada servicio listado en la tabla: |
| `const matchesText = !searchText \|\| textContent.includes(searchText);` | Compruebo si el nombre o descripción del servicio contiene la palabra buscada. |
| `const matchesCategory = !selectedCategory \|\| rowCategory.includes(selectedCategory);` | Compruebo si la categoría del servicio coincide con la que se eligió en el filtro. |
| `row.style.display = (matchesText && matchesCategory) ? '' : 'none';` | Si cumple con ambos filtros, dejo el servicio a la vista; si no, lo oculto de inmediato. |
| `searchInput.addEventListener('input', applyFilter);` | Reacciono a cada letra que se escribe. |
| `categorySelect.addEventListener('change', applyFilter);` | Reacciono a cada cambio en el selector de categoría. |

---

## 4. Control de los Modales (`setupModalLifecycle`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function openModal(modal) { modal.classList.add(activeClass); document.body.style.overflow = 'hidden'; }` | Muestro la ventana emergente y congelo el scroll de la página de fondo. |
| `function closeModal(modal) { modal.classList.remove(activeClass); document.body.style.overflow = ''; }` | Oculto la ventana emergente y devuelvo el scroll normal. |
| `btnOpenCreate.addEventListener('click', () => openModal(modalCreate));` | Al hacer clic en `+ Nuevo Servicio`, abro la ventana de creación. |
| `btnCloseCreate.addEventListener('click', () => closeModal(modalCreate));` | Al hacer clic en la "X", cierro la ventana de creación. |
| `btnCancelCreate.addEventListener('click', () => closeModal(modalCreate));` | Al hacer clic en "Cancelar", cierro la ventana de creación. |
| `modalCreate.addEventListener('click', (e) => { if (e.target === modalCreate) closeModal(...); });` | Si se hace clic en el fondo oscuro por fuera del cuadro blanco, se cierra. |
| `document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { ... } });` | Si se presiona la tecla Escape en el teclado, se cierran las ventanas abiertas. |

---

## 5. Llenar los datos para Editar un Servicio (`setupEditService`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `editButtons.forEach((button) => { button.addEventListener('click', function () { ... }); });` | Escucho el clic en cada botón de lápiz de la tabla. |
| `const id = this.dataset.id;` | Obtengo el código o ID del servicio. |
| `const nombre = this.dataset.nombre;` | Obtengo el nombre del servicio (ej. "Volumen Ruso 3D"). |
| `const categoria = this.dataset.categoria;` | Obtengo la categoría a la que pertenece. |
| `const precio = this.dataset.precio;` | Obtengo el precio actual del servicio. |
| `const duracion = this.dataset.duracion;` | Obtengo el tiempo en minutos que toma realizarlo (ej. 90 min). |
| `const descripcion = this.dataset.descripcion;` | Obtengo la descripción del procedimiento. |
| `const estado = this.dataset.estado;` | Obtengo si está activo o inactivo. |
| `setVal(fields.nombre, nombre);` | Escribo el nombre en la casilla correspondiente del formulario. |
| `setVal(fields.precio, precio);` | Escribo el precio en la casilla del precio. |
| `setVal(fields.duracion, duracion);` | Escribo los minutos en la casilla de duración. |
| `setVal(fields.descripcion, descripcion);` | Escribo la descripción en el área de texto. |
| `editForm.action = `${baseUrl}/${encodeURIComponent(id)}`;` | Le digo al formulario que cuando la administradora presione "Guardar Cambios", envíe los datos a la ruta de ese servicio específico (`/admin/servicios/4`). |
| `modalControls.openModal(modalEdit);` | Abro la ventana de edición ya con todos los campos llenos para que solo edite lo que necesite. |

---

## 6. Eliminar Servicio con Advertencia (`setupDeleteService`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `deleteButtons.forEach((button) => { button.addEventListener('click', async function () { ... }); });` | Escucho el clic en cada botón de papelera de la tabla de servicios. |
| `const confirmed = await confirmAction({ ... });` | Abro una ventana roja con SweetAlert2 preguntando: *"¿Eliminar servicio? ¿Estás seguro de eliminar [Nombre del servicio]? Esta acción no se puede deshacer"*. |
| `if (confirmed) { ... }` | Si la administradora confirma que de verdad desea borrarlo: |
| `deleteForm.action = `${baseUrl}/${encodeURIComponent(id)}`;` | Le asigno la ruta del servicio a eliminar al formulario oculto. |
| `deleteForm.submit();` | Envío la orden hacia el servidor Laravel para eliminarlo de la base de datos. |

---

## 7. Inicialización del Módulo (`initServicios`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initServicios(customConfig = {}) {` | La función que conecta y activa todos los subsistemas del catálogo en orden. |
| `setupServiceFiltering(mergedConfig);` | Enciendo el buscador y filtro por categoría. |
| `const modalControls = setupModalLifecycle(mergedConfig);` | Enciendo la apertura y cierre de ventanas flotantes. |
| `setupEditService(mergedConfig, modalControls);` | Enciendo el rellenado automático de datos para edición. |
| `setupDeleteService(mergedConfig);` | Enciendo la confirmación de eliminación segura. |
| `document.addEventListener('DOMContentLoaded', ...);` | Si el navegador terminó de leer la página y encuentra la tabla de servicios, enciende todo automáticamente. |
