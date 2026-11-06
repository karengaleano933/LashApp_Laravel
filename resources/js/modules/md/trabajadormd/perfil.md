# Explicación de mi código de Perfil del Trabajador (`perfil.js`)

En este archivo controlo la pantalla de cuenta personal de la especialista en **LashApp**. Le permite navegar fácilmente entre sus pestañas de información (*Datos personales*, *Cambiar contraseña* y *Próximas citas*) y actualizar su foto de perfil de inmediato al hacer clic en el ícono de la camarita.

---

## 1. Libreta de configuraciones (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `containerSelector: '#perfilTrabajadorContainer',` | El contenedor principal de la vista de perfil. |
| `tabs: '.perfil-tab-btn',` | Los botones de las pestañas para cambiar de sección. |
| `panels: '.perfil-tab-panel',` | Las secciones de contenido que se muestran u ocultan. |
| `inputFoto: '#inputFotoTrabajador',` | El campo oculto de tipo archivo donde se carga la nueva imagen. |
| `formFoto: '#formFotoTrabajador',` | El formulario que envía la foto al servidor. |
| `btnCambiarFoto: '.btn-cambiar-foto',` | El botoncito redondo con ícono de cámara sobre la foto de perfil. |

---

## 2. Alertas de Sesión (`handleSessionAlerts`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const successMsg = container.dataset.sessionSuccess;` | Lee si el servidor confirmó que los datos o la foto se guardaron con éxito. |
| `if (successMsg) { Swal.fire({ icon: 'success', title: '¡Operación Exitosa!' ... }); }` | Muestra la alerta de éxito con el mensaje recibido. |
| `else if (errorMsg) { Swal.fire({ icon: 'warning', title: 'Aviso' ... }); }` | Muestra un aviso de advertencia si la contraseña actual no coincidía o faltó un dato. |

---

## 3. Navegación por Pestañas (`initTabs`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `tabs.forEach((tab) => { tab.addEventListener('click', function () { ... }); });` | Le pongo un detector de clics a cada pestaña del perfil. |
| `tabs.forEach((t) => t.classList.remove(config.classes.active));` | Apago la pestaña anterior. |
| `panels.forEach((p) => p.classList.remove(config.classes.active));` | Escondo la sección de contenido anterior. |
| `this.classList.add(config.classes.active);` | Enciendo la pestaña que la especialista acaba de tocar. |
| `const targetId = this.getAttribute('data-tab');` | Averiguo qué sección le corresponde (ej. `tab-datos`, `tab-password` o `tab-citas`). |
| `targetPanel.classList.add(config.classes.active);` | Muestro en pantalla el panel correspondiente. |

---

## 4. Subida de Foto de Perfil con un Clic (`initFotoUpload`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `btnCambiar.addEventListener('click', () => { inputFoto.click(); });` | Cuando la trabajadora toca el botón de la camarita, abro en automático la ventana de su computador o celular para que elija una foto. |
| `inputFoto.addEventListener('change', () => { ... });` | Escucho cuando termina de seleccionar la foto. |
| `if (inputFoto.files && inputFoto.files.length > 0) { formFoto.submit(); }` | Si eligió una imagen válida, envío el formulario de inmediato sin obligarla a buscar ningún botón de "Guardar foto". |

---

## 5. Inicialización (`initPerfilTrabajador`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initPerfilTrabajador(customConfig = {}) {` | Enciende las pestañas, la carga de fotos y las alertas de sesión. |
| `document.addEventListener('DOMContentLoaded', () => initPerfilTrabajador());` | Arranca todo de forma automática en cuanto la página esté lista. |
