# Explicación de `perfilcliente.js`

Este archivo controla la vista del perfil del cliente: permite navegar suavemente entre las secciones del perfil con las pestañas, mostrar u ocultar el formulario para cambiar la contraseña y pedir una confirmación de seguridad antes de desactivar la cuenta.

---

### 1. Navegación por pestañas con desplazamiento suave (`initPerfilCliente`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initPerfilCliente() {` | Función que inicializa los botones de las pestañas en el perfil del cliente. |
| `const tabs = document.querySelectorAll('.perfil-tab-btn');` | Busco todos los botones de las pestañas en la pantalla. |
| `tabs.forEach(tab => { tab.addEventListener('click', function() { ... }); });` | Le agrego a cada botón la acción de responder al hacer clic. |
| `tabs.forEach(t => t.classList.remove('active')); this.classList.add('active');` | Desactivo visualmente todas las pestañas y marco como activa solo a la que le di clic. |
| `const tabTarget = this.getAttribute('data-tab');` | Obtengo el nombre de la sección a la que apunta la pestaña (info, preferencias, seguridad o acciones). |
| `if (tabTarget === 'info') { document.getElementById('sec-info')?.scrollIntoView({ behavior: 'smooth' }); }` | Si elegí "info", la pantalla rueda suavemente hacia la sección de datos personales. |
| `else if (tabTarget === 'preferencias') { document.getElementById('sec-preferencias')?.scrollIntoView({ behavior: 'smooth' }); }` | Si elegí "preferencias", la pantalla rueda hacia la sección de preferencias. |
| `else if (tabTarget === 'seguridad') { document.getElementById('sec-seguridad')?.scrollIntoView({ behavior: 'smooth' }); togglePasswordForm(); }` | Si elegí "seguridad", rueda hacia la seguridad y abre el formulario de cambio de clave. |
| `else if (tabTarget === 'acciones') { document.getElementById('sec-acciones')?.scrollIntoView({ behavior: 'smooth' }); }` | Si elegí "acciones", rueda hacia la parte de opciones de cuenta (como desactivar). |

---

### 2. Mostrar u ocultar el cambio de contraseña (`togglePasswordForm`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function togglePasswordForm() {` | Función para abrir o cerrar el bloque donde se cambia la contraseña. |
| `const wrap = document.getElementById('formPasswordWrap'); if (wrap) {` | Busco el contenedor del formulario de contraseña. |
| `wrap.style.display = (wrap.style.display === 'none' \|\| wrap.style.display === '') ? 'block' : 'none';` | Si está escondido lo muestro (`block`), y si ya estaba abierto lo vuelvo a ocultar (`none`). |
| `if (wrap.style.display === 'block') { wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); }` | Cuando se abre, deslizo la pantalla suavemente para que los campos queden a la vista del usuario. |

---

### 3. Confirmación segura para desactivar la cuenta (`confirmarEliminarCuenta`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function confirmarEliminarCuenta() {` | Función que pide confirmación antes de desactivar la cuenta del cliente. |
| `if (typeof Swal !== 'undefined') { Swal.fire({ ... }).then((result) => { ... }); }` | Si tengo la librería SweetAlert2, muestro una alerta roja de advertencia advirtiendo que la sesión se cerrará y la cuenta quedará inactiva. |
| `if (result.isConfirmed) { document.getElementById('formEliminarCuenta')?.submit(); }` | Si el cliente presiona el botón rojo "Sí, desactivar cuenta", envío el formulario para procesar la baja. |
| `else { if (confirm('¿Estás seguro de que deseas desactivar tu cuenta?')) { ... } }` | Si no estuviera la librería disponible, uso la alerta básica del navegador como respaldo seguro. |

---

### 4. Disponibilidad global y ejecución automática

| Código | ¿Qué hace? |
| :--- | :--- |
| `window.togglePasswordForm = togglePasswordForm;` | Expongo la función del formulario de contraseña al navegador para llamarla desde botones del HTML. |
| `window.confirmarEliminarCuenta = confirmarEliminarCuenta;` | Expongo la confirmación de desactivación a todo el sistema. |
| `document.addEventListener('DOMContentLoaded', initPerfilCliente);` | En cuanto la página termina de cargar, activo los eventos y pestañas del perfil. |
