# Explicación de mi código de Horario del Trabajador (`horario.js`)

En este archivo controlo la gestión de turnos y bloques de disponibilidad de cada especialista en **LashApp**. Permite que la trabajadora agregue nuevos bloques de atención para que las clientas puedan agendarla, filtre sus horarios entre *Todos*, *Disponibles* y *Ocupados*, busque por fecha o texto y elimine bloques vacíos con confirmación de seguridad.

---

## 1. Libreta de configuraciones (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `containerSelector: '#horarioTrabajadorContainer',` | El contenedor principal de la vista de horarios. |
| `modal: '#modalNuevoHorario',` | La ventana emergente para registrar un nuevo turno o bloque. |
| `btnAbrir: '#btnAbrirModalHorario',` | El botón para abrir la ventana de nuevo bloque. |
| `tabBtns: '.horario-tab-btn',` | Los botones de pestañas (*Todos*, *Disponibles*, *Ocupados*). |
| `searchInput: '#horarioSearchInput',` | La caja de texto para buscar bloques por fecha o descripción. |
| `tableRows: '#tablaHorarios tbody tr',` | Cada fila de la tabla donde están anotados los turnos. |

---

## 2. Alertas de Sesión (`handleSessionAlerts`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const successMsg = container.dataset.sessionSuccess;` | Lee si el servidor notificó que el bloque se guardó o eliminó con éxito. |
| `if (successMsg) { Swal.fire({ icon: 'success', title: '¡Operación Exitosa!' ... }); }` | Muestra la ventana verde de confirmación. |
| `else if (errorMsg) { Swal.fire({ icon: 'warning', title: 'Aviso' ... }); }` | Si hubo algún inconveniente, muestra un aviso de advertencia. |

---

## 3. Control del Modal de Nuevo Bloque (`initModal`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function abrirModal() { if (modal) modal.classList.add(config.classes.modalActivo); }` | Muestra la ventana emergente para ingresar fecha y horas. |
| `function cerrarModal() { if (modal) modal.classList.remove(config.classes.modalActivo); }` | Oculta la ventana emergente. |
| `btnAbrir.addEventListener('click', abrirModal);` | Abre el modal al tocar el botón de agregar bloque. |
| `btnCerrar.addEventListener('click', cerrarModal);` | Lo cierra al tocar la "X". |
| `btnCancelar.addEventListener('click', cerrarModal);` | Lo cierra al tocar Cancelar. |
| `modal.addEventListener('click', (e) => { if (e.target === modal) cerrarModal(); });` | Lo cierra al hacer clic por fuera. |
| `document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrarModal(); });` | Lo cierra con la tecla Escape del teclado. |

---

## 4. Filtros por Pestañas y Buscador (`initFiltros`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const matchesFilter = (currentFilter === 'all') \|\| (filterType === currentFilter);` | Comprueba si el bloque coincide con la pestaña activa (si es disponible u ocupado). |
| `const matchesSearch = !query \|\| searchText.includes(query);` | Comprueba si coincide con el texto escrito en el buscador. |
| `row.style.display = (matchesFilter && matchesSearch) ? '' : 'none';` | Si pasa ambos filtros, muestra el bloque; si no, lo esconde. |
| `tabBtns.forEach(btn => { ... });` | Cambia la pestaña activa y vuelve a filtrar de inmediato cuando la especialista hace clic en otra pestaña. |
| `searchInput.addEventListener('input', aplicarFiltros);` | Filtra al instante mientras se escribe. |

---

## 5. Eliminar Bloque Horario (`confirmarEliminarHorario`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function confirmarEliminarHorario(btn) {` | La función que previene borrar turnos por equivocación. |
| `Swal.fire({ title: '¿Eliminar este bloque horario?', text: 'Esta acción no se puede deshacer si no tiene citas asignadas.', icon: 'warning' ... });` | Despliega una advertencia en rojo pidiendo confirmación. |
| `if (result.isConfirmed) { btn.closest('form')?.submit(); }` | Si la trabajadora confirma, envía el formulario correspondiente para borrar el turno en el servidor. |

---

## 6. Inicialización (`initHorarioTrabajador`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initHorarioTrabajador(customConfig = {}) {` | Pone a andar todas las funciones del horario. |
| `window.confirmarEliminarHorario = confirmarEliminarHorario;` | Permite que los botones con `onclick` en la tabla puedan llamar a la confirmación. |
| `document.addEventListener('DOMContentLoaded', () => initHorarioTrabajador());` | Arranca todo de forma automática en cuanto la página esté lista. |
