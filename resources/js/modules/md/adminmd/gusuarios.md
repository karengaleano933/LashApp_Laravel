# Explicación de mi código de Gestión de Usuarios (`gusuarios.js`)

En este archivo controlo toda la administración del personal y usuarios de **LashApp** (lashistas, recepcionistas, administradoras y clientes). Aquí programo el buscador de usuarios, el filtro por rol, la función del "ojito" para ver u ocultar contraseñas, los modales para registrar y editar personas, y el aviso de confirmación antes de eliminar a alguien.



---

## 1. Importación de alertas (`alerts.js`)

| Código | ¿Qué hace? 
| :--- | :--- |
| `import { confirmAction } from '../../common/alerts.js';` | Importo mi diálogo de confirmación para preguntar antes de borrar a un usuario. |

---

## 2. Libreta de configuraciones (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const DEFAULT_CONFIG = {` | Mi objeto donde anoto todos los selectores y mensajes. |
| `searchInput: '#buscarUsuario',` | La caja de texto para buscar usuarios por nombre, apellido o correo. |
| `roleFilter: '#filtroRol',` | El desplegable para ver solo Administradores, Trabajadores o Clientes. |
| `tableRows: '#tablaUsuarios tbody tr',` | Cada una de las filas de la tabla donde están los usuarios listados. |
| `togglePassCreate: '#mostrarPassword',` | El ícono del ojito en el modal de crear para ver la contraseña. |
| `inputPassCreate: '#passwordUsuario',` | La casilla de contraseña en el formulario de creación. |
| `togglePassEdit: '#mostrarPasswordEdit',` | El ícono del ojito en el modal de edición. |
| `inputPassEdit: '#edit_password',` | La casilla de contraseña en el formulario de edición. |
| `modalCreate: '#modalCrearUsuario',` | La ventana emergente para registrar un nuevo usuario. |
| `modalEdit: '#modalEditarUsuario',` | La ventana emergente para modificar los datos de un usuario existente. |
| `formEdit: '#formEditarUsuario',` | El formulario HTML de edición que enviará los cambios al servidor. |
| `btnEditTrigger: '.btn-editar-usuario',` | El botón con el lápiz azul en cada fila para abrir la edición de ese usuario. |
| `editFields: { ... }` | La lista de identificadores de cada casilla del modal de edición (nombre, apellido, documento, etc.). |
| `formDelete: '#formEliminarUsuario',` | El formulario oculto que se usa para enviar la orden de borrado por seguridad. |
| `btnDeleteTrigger: '.btn-eliminar-usuario',` | El botón de la caneca de basura roja en la tabla. |
| `baseUrl: '/admin/usuarios',` | La dirección base a donde se envían las peticiones para actualizar o eliminar. |
| `messages: { ... }` | La pregunta que le hacemos al usuario: *"¿Estás seguro de eliminar a [Nombre]? Esta acción no se puede deshacer"*. |

---

## 3. Buscador y Filtro por Rol en tiempo real (`setupUserFilter`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupUserFilter(config) {` | La función que filtra la tabla mientras la persona escribe. |
| `const buscar = document.querySelector(...);` | Busco el campo del buscador en la pantalla. |
| `const filtro = document.querySelector(...);` | Busco el selector de roles. |
| `const filas = document.querySelectorAll(...);` | Tomo todas las filas de la tabla de usuarios. |
| `function filtrarUsuarios() { ... }` | La lógica que evalúa fila por fila: |
| `const texto = buscar.value.toLowerCase().trim();` | Convierto lo que el usuario escribió a minúsculas y sin espacios sobrantes. |
| `const rol = filtro.value.toLowerCase().trim();` | Tomo el rol seleccionado en el menú desplegable. |
| `const coincideTexto = contenido.includes(texto);` | Reviso si el texto escrito aparece en cualquier parte de la fila (nombre, correo, teléfono, etc.). |
| `const coincideRol = !rol \|\| rolFila.includes(rol);` | Reviso si el rol de la fila coincide con el rol filtrado (o si el filtro está en "Todos"). |
| `fila.style.display = (coincideTexto && coincideRol) ? '' : 'none';` | Si coincide en texto y en rol, dejo visible la fila; si no, la oculto de inmediato. |
| `buscar.addEventListener('input', filtrarUsuarios);` | Escucho cada tecla que se oprime en el buscador. |
| `filtro.addEventListener('change', filtrarUsuarios);` | Escucho cuando se cambia la opción del selector de rol. |

---

## 4. El "Ojito" para ver contraseñas (`setupPasswordToggles`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const bindToggle = (btnSelector, inputSelector) => { ... };` | Una mini-función reusable para no repetir código entre el modal de crear y el de editar. |
| `btn.addEventListener('click', () => { ... });` | Cuando la administradora hace clic en el ojito: |
| `if (input.type === 'password') { input.type = 'text'; ... }` | Si la contraseña estaba oculta con puntitos (`password`), la paso a texto plano (`text`) para que se lea y cambio el ícono por un ojo tachado (`fa-eye-slash`). |
| `else { input.type = 'password'; ... }` | Si ya estaba visible, la vuelvo a esconder con puntitos y regreso al ícono del ojo normal (`fa-eye`). |
| `bindToggle(config.selectors.togglePassCreate, ...);` | Aplico la función al formulario de crear nuevo usuario. |
| `bindToggle(config.selectors.togglePassEdit, ...);` | Aplico la función al formulario de editar usuario. |

---

## 5. Control de los Modales (`setupModalLifecycle`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function openModal(modal) { modal.classList.add('activo'); document.body.style.overflow = 'hidden'; }` | Muestra la ventana y congela el scroll de fondo para que no se desplace la página. |
| `function closeModal(modal) { modal.classList.remove('activo'); document.body.style.overflow = ''; }` | Oculta la ventana y devuelve el scroll. |
| `abrirBtn.addEventListener('click', () => { openModal(modalCrear); firstInput.focus(); });` | Al dar clic en `+ Nuevo Usuario`, abro el modal y coloco el cursor automáticamente en la primera casilla (nombre) para que empiece a escribir sin tener que hacer clic. |
| `cerrarCrearBtn.addEventListener('click', ...);` | Cierra el modal de creación con la "X". |
| `cancelarCrearBtn.addEventListener('click', ...);` | Cierra el modal de creación con el botón Cancelar. |
| `modalCrear.addEventListener('click', (e) => { if (e.target === modalCrear) closeModal(...); });` | Si se hace clic en la zona oscura por fuera del modal, se cierra. |
| `document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(...); });` | Si se oprime la tecla Escape del teclado, se cierran los modales abiertos. |

---

## 6. Llenar los datos para Editar (`setupEditUser`)

| Código | ¿Qué hace?   |
| :--- | :--- |
| `editBtns.forEach((btn) => { btn.addEventListener('click', function () { ... }); });` | Le pongo un detector de clic a cada botón de lápiz de la tabla. |
| `const id = this.dataset.id;` | Obtengo el número identificador del usuario de esa fila. |
| `setVal(fields.nombre, this.dataset.nombre \|\| '');` | Tomo el nombre guardado en los atributos de la fila y lo escribo en la casilla de nombre del modal. |
| `setVal(fields.apellido, this.dataset.apellido \|\| '');` | Hago lo mismo con el apellido. |
| `setVal(fields.documento, this.dataset.documento \|\| '');` | Relleno el número de documento / cédula. |
| `setVal(fields.telefono, this.dataset.telefono \|\| '');` | Relleno el número de teléfono. |
| `setVal(fields.correo, this.dataset.correo \|\| '');` | Relleno el correo electrónico. |
| `setVal(fields.rol, this.dataset.rol \|\| '1');` | Selecciono el rol que ya tiene ese usuario en el menú desplegable. |
| `setVal(fields.estado, ...);` | Selecciono si está activo o inactivo. |
| `setVal(fields.password, '');` | Dejo la casilla de contraseña vacía, porque solo debe escribirse si se desea cambiar. |
| `formEditar.action = `${baseUrl}/${encodeURIComponent(id)}`;` | Cambio la dirección a donde apunta el formulario para que Laravel sepa exactamente a qué usuario actualizar (`/admin/usuarios/12`). |
| `modalControls.openModal(modalEditar);` | Abro el modal ya con todos los campos llenos. |

---

## 7. Eliminar Usuario con Advertencia (`setupDeleteUser`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `deleteBtns.forEach((btn) => { btn.addEventListener('click', async function () { ... }); });` | Escucho el clic en cada botón de la caneca de basura. |
| `const confirmed = await confirmAction({ ... });` | Despliego una ventana flotante de advertencia: *"¿Eliminar usuario? ¿Estás seguro de eliminar a [Nombre]? Esta acción no se puede deshacer"*. |
| `if (confirmed) { ... }` | Solo si la administradora confirma: |
| `formEliminar.action = `${baseUrl}/${encodeURIComponent(id)}`;` | Le asigno al formulario oculto la dirección del usuario que se va a borrar. |
| `formEliminar.submit();` | Envío la orden de eliminación hacia el servidor Laravel de forma segura. |

---

## 8. Inicialización (`initUsuarios`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initUsuarios(customConfig = {}) {` | Enciendo todos los bloques de código de este módulo en orden. |
| `setupUserFilter(config);` | Activo el buscador y filtro por rol. |
| `setupPasswordToggles(config);` | Activo los ojitos de contraseña. |
| `setupModalLifecycle(config);` | Activo los modales de crear y editar. |
| `setupEditUser(config, modalControls);` | Vinculo el llenado automático de datos de edición. |
| `setupDeleteUser(config);` | Vinculo la confirmación de eliminación. |
| `document.addEventListener('DOMContentLoaded', ...);` | Arranco todo de manera automática en cuanto el navegador cargue la página. |
