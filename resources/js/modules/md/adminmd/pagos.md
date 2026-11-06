# Explicación de mi código de Gestión de Pagos (`pagos.js`)

En este archivo controlo los movimientos financieros y cobros de **LashApp**. Gracias a este código, la administradora puede filtrar los pagos por nombre de clienta o por método (Efectivo, Tarjeta, Transferencia, Nequi, etc.), autocompletar el monto a cobrar en cuanto escoge una cita, abrir las ventanas de registrar o editar un pago y eliminar registros con una confirmación segura.

---

## 1. Importación de alertas (`alerts.js`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `import { confirmAction } from '../../common/alerts.js';` | Importo la herramienta que muestra la ventana de confirmación antes de eliminar un comprobante de pago. |

---

## 2. Libreta de configuraciones (`DEFAULT_CONFIG`)

| Código | ¿Qué hace?
| :--- | :--- |
| `const DEFAULT_CONFIG = {` | Mi lista con los nombres de todos los elementos de la pantalla de pagos. |
| `searchInput: '#buscarPago',` | El buscador donde se escribe el nombre de la clienta o el número de referencia. |
| `methodFilter: '#filtroMetodo',` | El desplegable para ver solo pagos en Efectivo, Tarjeta, Transferencia, etc. |
| `tableRows: '#tablaPagos tbody tr',` | Las filas de la tabla de pagos. |
| `selectCita: '#id_cita_pago',` | La lista desplegable de citas al momento de registrar un pago. |
| `inputMonto: '#monto_pago',` | La casilla del formulario donde va el valor en dinero ($). |
| `modalCreate: '#modalCrearPago',` | La ventana flotante para crear un nuevo pago. |
| `modalEdit: '#modalEditarPago',` | La ventana flotante para corregir un pago existente. |
| `formEdit: '#formEditarPago',` | El formulario que enviará los datos modificados. |
| `btnEditTrigger: '.btn-editar-pago',` | El botón de editar en cada fila de la tabla. |
| `editFields: { ... }` | Los nombres de las casillas para rellenar (id, método, monto, referencia, estado). |
| `formDelete: '#formEliminarPago',` | El formulario oculto de eliminación segura. |
| `btnDeleteTrigger: '.btn-eliminar-pago',` | El botón de la caneca de basura para borrar el pago. |
| `baseUrl: '/admin/pagos',` | La ruta del servidor para guardar cambios o eliminar. |
| `messages: { ... }` | La pregunta amigable: *"¿Estás seguro de eliminar el pago de [Cliente]? Esta acción no se puede deshacer"*. |

---

## 3. Buscador y Filtro por Método de Pago (`setupPagosFilter`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupPagosFilter(config) {` | La función que busca pagos en tiempo real. |
| `const texto = (buscar ? buscar.value : '').toLowerCase().trim();` | Tomo lo que el usuario escribió para buscar (ej. "Valentina"). |
| `const metodo = (filtro ? filtro.value : '').toLowerCase().trim();` | Tomo el método seleccionado (ej. "transferencia"). |
| `filas.forEach((fila) => { ... });` | Reviso cada una de las filas de la tabla: |
| `const coincideTexto = !texto \|\| contenido.includes(texto);` | Reviso si el texto coincide con lo escrito. |
| `const coincideMetodo = !metodo \|\| metodoFila.includes(metodo);` | Reviso si el método de pago coincide con el seleccionado en el menú. |
| `fila.style.display = (coincideTexto && coincideMetodo) ? '' : 'none';` | Si coincide en ambos, la fila queda visible; si no coincide, la escondo. |
| `buscar.addEventListener('input', filtrarPagos);` | Escucho cada letra que se escribe en el buscador. |
| `filtro.addEventListener('change', filtrarPagos);` | Escucho cada cambio en el menú desplegable de métodos. |

---

## 4. Autocompletado del Precio de la Cita (`setupAmountAutocomplete`)

Esta es una función muy cómoda para la administradora: cuando va a cobrar una cita, no tiene que adivinar ni calcular cuánto costaba el servicio:

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupAmountAutocomplete(config) {` | Función que detecta el valor del servicio agendado. |
| `selectCita.addEventListener('change', function () { ... });` | Escucho cuando la administradora elige una cita del listado desplegable. |
| `const opt = this.options[this.selectedIndex];` | Tomo la opción que acaba de seleccionar. |
| `const precio = opt ? opt.dataset.precio : null;` | Leo el precio del servicio que viene guardado dentro de esa opción (ej. "80000"). |
| `if (precio) { inputMonto.value = parseFloat(precio).toFixed(2); }` | Relleno automáticamente la casilla de monto con ese valor exacto. |

---

## 5. Control de Modales (`setupModalLifecycle`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function openModal(modal) { modal.classList.add('activo'); document.body.style.overflow = 'hidden'; }` | Abre la ventana flotante y bloquea el desplazamiento del fondo. |
| `function closeModal(modal) { modal.classList.remove('activo'); document.body.style.overflow = ''; }` | Cierra la ventana y reactiva el scroll normal. |
| `abrirBtn.addEventListener('click', () => openModal(modalCrear));` | Al hacer clic en `+ Registrar Pago`, abre el modal de creación. |
| `cerrarCrearBtn.addEventListener('click', () => closeModal(modalCrear));` | Al dar clic en la "X", lo cierra. |
| `cancelarCrearBtn.addEventListener('click', () => closeModal(modalCrear));` | Al dar clic en Cancelar, lo cierra. |
| `modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(modal); });` | Si se hace clic por fuera de la ventanita, se cierra. |
| `document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(...); });` | Si se presiona la tecla Escape en el teclado, se cierra. |

---

## 6. Llenar datos para Editar un Pago (`setupEditPago`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `editBtns.forEach((btn) => { btn.addEventListener('click', function () { ... }); });` | Le pongo un detector a cada botón de lápiz de la tabla de pagos. |
| `const id = this.dataset.id;` | Obtengo el número identificador del pago a modificar. |
| `setVal(fields.id, id \|\| '');` | Cargo el ID en el formulario. |
| `setVal(fields.metodo, this.dataset.metodo \|\| 'Efectivo');` | Selecciono en el menú si fue Efectivo, Tarjeta, etc. |
| `setVal(fields.monto, this.dataset.monto \|\| '');` | Coloco el valor cobrado en la casilla de monto. |
| `setVal(fields.referencia, this.dataset.referencia \|\| '');` | Coloco el número de aprobación o recibo en la casilla de referencia. |
| `setVal(fields.estado, ...);` | Selecciono el estado (Completado o Pendiente). |
| `formEditar.action = `${baseUrl}/${encodeURIComponent(id)}`;` | Le asigno la ruta correcta al formulario (`/admin/pagos/5`). |
| `modalControls.openModal(modalEditar);` | Abro la ventana de edición ya con todos los campos listos. |

---

## 7. Eliminar Pago con Advertencia (`setupDeletePago`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `deleteBtns.forEach((btn) => { btn.addEventListener('click', async function () { ... }); });` | Escucho el clic en cada botón de caneca de basura. |
| `const confirmed = await confirmAction({ ... });` | Lanzo la pregunta de seguridad: *"¿Eliminar registro de pago? ¿Estás seguro de eliminar el pago de [Cliente]? Esta acción no se puede deshacer"*. |
| `if (confirmed) { ... }` | Si la administradora confirma: |
| `formEliminar.action = `${baseUrl}/${encodeURIComponent(id)}`;` | Asigno la ruta del pago a borrar. |
| `formEliminar.submit();` | Envío la orden al servidor para borrar el pago. |

---

## 8. Inicialización (`initPagos`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initPagos(customConfig = {}) {` | Arranco todos los subsistemas de pagos en orden. |
| `setupPagosFilter(config);` | Enciendo el buscador y filtro por método. |
| `setupAmountAutocomplete(config);` | Enciendo el autocompletado inteligente de precios según la cita. |
| `setupModalLifecycle(config);` | Enciendo la apertura y cierre de ventanas flotantes. |
| `setupEditPago(config, modalControls);` | Enciendo el cargador de datos de edición. |
| `setupDeletePago(config);` | Enciendo la confirmación de eliminación. |
| `document.addEventListener('DOMContentLoaded', ...);` | Se ejecuta solo en cuanto la página esté lista en el navegador. |
