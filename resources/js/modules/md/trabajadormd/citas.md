# Explicación de mi código de Citas del Trabajador (`citas.js`)

En este archivo controlo la agenda de citas personal para las especialistas de **LashApp**. Les permite revisar rápidamente sus clientas asignadas, buscar por nombre o servicio, filtrar por fecha o estado, consultar un mini-calendario mensual para ubicarse en el tiempo, marcar citas como realizadas cuando terminan el tratamiento y cancelar citas cuando sea necesario con mensajes de confirmación.

---

## 1. Libreta de configuraciones (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `containerSelector: '#citasTrabajadorContainer',` | El contenedor principal de la vista de citas de la trabajadora. |
| `modalNueva: '#modalNuevaCita',` | La ventana emergente para agendar una nueva cita. |
| `btnAbrirModal: '#abrirNuevaCita',` | El botón para abrir la ventana de registro de citas. |
| `buscarTabla: '#buscarTabla', buscarCita: '#buscarCita',` | Los campos de búsqueda para escribir el nombre de la clienta o servicio. |
| `filtroProf: '#filtroProfesional', filtroEst: '#filtroEstado', filtroFec: '#filtroFecha',` | Los filtros desplegables para clasificar por profesional, estado y fecha específica. |
| `selectRegistros: '#selectRegistros', infoPaginacion: '#infoPaginacion',` | El control para mostrar de 10 en 10 registros y el texto que dice cuántos registros hay en pantalla. |
| `calGrid: '#calGrid', calMesAnio: '#calMesAnio', calPrev: '#calPrev', calNext: '#calNext',` | Los elementos que componen el mini-calendario mensual (cuadrícula, título del mes y flechas). |
| `urlCompletar: '/citas/:id/completar', urlCancelar: '/citas/:id/cancelar',` | Las rutas para registrar en el servidor que la cita se realizó o se canceló. |

---

## 2. Detección de Mensajes de Éxito o Error (`handleSessionAlerts`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const successMsg = container.dataset.sessionSuccess;` | Reviso si el servidor envió un mensaje de éxito guardado en la etiqueta del contenedor. |
| `if (successMsg) { Swal.fire({ icon: 'success', title: '¡Operación Exitosa!', text: successMsg ... }); }` | Si hay mensaje de éxito, abro una ventana flotante morada celebrando que la operación salió bien. |
| `else if (errorMsg) { Swal.fire({ icon: 'error', title: 'Error', text: errorMsg ... }); }` | Si hubo algún problema o validación fallida, muestro la alerta de error correspondiente. |

---

## 3. Ventana emergente de Nueva Cita (`initModal`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function abrir() { modal.classList.add('activo'); modal.setAttribute('aria-hidden', 'false'); }` | Hace visible el modal y actualiza el atributo de accesibilidad para lectores de pantalla. |
| `function cerrar() { modal.classList.remove('activo'); modal.setAttribute('aria-hidden', 'true'); }` | Oculta la ventana flotante. |
| `btnAbrir.addEventListener('click', abrir);` | Abre el modal al tocar el botón de nueva cita. |
| `btnCerrar.addEventListener('click', cerrar);` | Cierra el modal con la "X". |
| `btnCancelar.addEventListener('click', cerrar);` | Cierra el modal con el botón Cancelar. |
| `modal.addEventListener('click', (e) => { if (e.target === modal) cerrar(); });` | Lo cierra al tocar el fondo oscuro por fuera de la ventana. |
| `document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrar(); });` | Lo cierra al oprimir la tecla Escape. |

---

## 4. Filtros en vivo y Paginación (`initFiltros`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const query = queryT \|\| queryC;` | Combina lo que se escriba en cualquiera de los dos buscadores disponibles. |
| `const matchQuery = !query \|\| cliente.includes(query) \|\| servicio.includes(query);` | Revisa si el nombre de la clienta o el tratamiento contiene la palabra buscada. |
| `const matchProf = !prof \|\| profesional.includes(prof);` | Comprueba si coincide con la profesional seleccionada. |
| `const matchEst = !est \|\| estado.includes(est);` | Comprueba si coincide con el estado elegido. |
| `const matchFec = !fec \|\| fecha === fec;` | Comprueba si coincide con el día específico del filtro de fecha. |
| `if (matchQuery && matchProf && matchEst && matchFec) { ... row.style.display = ''; }` | Si cumple con todos los filtros, la fila se muestra; si no, se oculta. |
| `infoPaginacion.textContent = 'Mostrando ' + mostrados + ' de ' + coincidentes + ' registros';` | Actualiza el letrero inferior para que la trabajadora sepa cuántas citas coinciden con su búsqueda. |
| `export function limpiarFiltros() { ... }` | Una función que borra todo lo escrito en los buscadores y regresa todos los selectores a su estado inicial. |

---

## 5. Mini Calendario Mensual (`initCalendario`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `calMesAnio.textContent = `${nombresMeses[month]} ${year}`;` | Escribe el nombre del mes y año actual en español. |
| `const firstDay = (new Date(year, month, 1).getDay() + 6) % 7;` | Calcula cuántos espacios en blanco dejar antes del día 1 del mes para que los lunes queden alineados. |
| `for (let d = 1; d <= totalDays; d++) { ... }` | Dibuja cada número de día y, si coincide con el día de hoy, le añade la clase `cal-today` para destacarlo. |
| `calPrev.addEventListener('click', ...); calNext.addEventListener('click', ...);` | Permite retroceder y avanzar de mes con las flechitas de navegación. |

---

## 6. Marcar Cita como Realizada o Cancelar (`confirmarCompletar` y `confirmarCancelar`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function confirmarCompletar(idCita) { ... }` | Abre una ventana verde con el mensaje: *"¿Marcar cita como realizada? La clienta podrá calificar el servicio"*. Si confirma, redirige a la URL para completar la cita. |
| `export function confirmarCancelar(idCita) { ... }` | Abre una advertencia roja: *"¿Cancelar cita? La cita cambiará a estado Cancelada"*. Si confirma, redirige a la URL de cancelación. |

---

## 7. Inicialización (`initCitasTrabajador`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initCitasTrabajador(customConfig = {}) {` | Enciende todas las funciones de este archivo en orden. |
| `window.limpiarFiltros = limpiarFiltros; ...` | Deja las funciones disponibles globalmente para que funcionen con los botones del HTML. |
| `document.addEventListener('DOMContentLoaded', () => initCitasTrabajador());` | Arranca todo de forma automática en cuanto la página esté lista. |
