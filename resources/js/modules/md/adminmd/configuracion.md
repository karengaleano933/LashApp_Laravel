# Explicación de mi código de Configuración del Negocio (`configuracion.js`)

En este archivo controlo los aspectos técnicos y administrativos de **LashApp** desde el panel de ajustes: la previsualización del logo del salón antes de subirlo, las ventanas flotantes para modificar impuestos o políticas del estudio, la descarga de copias de seguridad de la base de datos y los diálogos de seguridad para restablecer o eliminar datos delicados.

## 1. Importación de alertas (`alerts.js`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `import { confirmAction, notify } from '../../common/alerts.js';` | Cargo las ventanas de diálogo bonitas para pedir confirmación antes de acciones peligrosas (`confirmAction`) y para dar avisos rápidos de éxito (`notify`). |

---

## 2. Libreta de opciones y selectores (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const DEFAULT_CONFIG = {` | Inicio la lista de elementos y colores predeterminados. |
| `logoInput: '#logoFileInput',` | El botón donde la administradora selecciona la imagen del logo desde su computador. |
| `logoPreview: '#logoImgPreview',` | La etiqueta de imagen donde se verá la foto seleccionada antes de guardarla. |
| `logoDefaultIcon: '#logoIconDefault',` | El ícono de camarita o imagen que se muestra si todavía no hay logo cargado. |
| `logoDefaultText: '#logoTextDefault',` | El texto explicativo de "Subir logo nuevo". |
| `btnImpuestos: '#btnModalImpuestos',` | El botón para configurar el IVA y porcentajes. |
| `btnPoliticas: '#btnModalPoliticas',` | El botón para editar los términos y condiciones del salón. |
| `btnBackup: '#btnRealizarBackup',` | El botón para generar una copia de seguridad de la información. |
| `btnRestablecer: '#btnConfirmarRestablecer',` | El botón para reiniciar los ajustes a valores de fábrica. |
| `btnEliminarCuenta: '#btnConfirmarEliminarCuenta',` | El botón de zona de peligro para borrar la cuenta. |
| `colors: { primary: '#7c1db8', danger: '#dc2626', cancel: '#b59ec4' }` | Los colores corporativos de LashApp (morado principal, rojo de peligro y lila suave de cancelar). |

---

## 3. Previsualización del Logo en vivo (`setupLogoPreview`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const fileInput = document.querySelector(...)` | Busco el campo donde se elige el archivo de imagen. |
| `const imgPreview = document.querySelector(...)` | Busco el recuadro donde se mostrará la foto. |
| `fileInput.addEventListener('change', function (e) {` | Escucho cuando la persona escoge una imagen de su computadora. |
| `const file = e.target.files[0];` | Tomo el primer archivo que seleccionó. |
| `const reader = new FileReader();` | Creo un lector de archivos en memoria del navegador para no tener que subirlo al servidor todavía. |
| `reader.onload = function (event) { ... };` | Cuando el navegador termina de leer la imagen... |
| `imgPreview.src = event.target.result;` | Pongo la foto en la etiqueta `<img>` para que se vea de inmediato. |
| `imgPreview.style.display = 'block';` | Hago visible la imagen. |
| `if (icon) icon.style.display = 'none';` | Escondo el ícono viejo para que no estorbe. |
| `if (text) text.style.display = 'none';` | Escondo el texto viejo de "Subir logo". |
| `reader.readAsDataURL(file);` | Le doy la orden al lector de convertir la imagen en datos visibles. |
| `window.previewLogo = function (event) { ... };` | Dejo la misma función accesible de manera global por si algún formulario en el HTML tiene un `onchange="previewLogo(event)"`. |

---

## 4. Modales de Configuración (`setupConfigModals`)

### A. Modal de Impuestos y Tarifas
| Código | ¿Qué hace? |
| :--- | :--- |
| `window.mostrarModalImpuestos = function () {` | Creo la función para abrir la ventana de impuestos. |
| `window.Swal.fire({ title: 'Impuestos y Tarifas', html: ... });` | Abro una ventana flotante con tres casillas numéricas: una para el IVA (19%), otra para retenciones (0%) y otra para propina sugerida (10%). |
| `if (result.isConfirmed) { notify({ ... }); }` | Si la administradora le da a "Guardar Tarifas", muestro un mensaje verde confirmando que los cambios impositivos fueron guardados. |

### B. Modal de Políticas y Términos
| Código | ¿Qué hace? |
| :--- | :--- |
| `window.mostrarModalPoliticas = function () {` | Abro la ventana para escribir las reglas del estudio. |
| `html: '<textarea>Todos los servicios... tolerancia 10 minutos...</textarea>'` | Muestro cajas de texto grandes para redactar los términos de puntualidad y la política de privacidad de los datos de las clientas. |
| `if (result.isConfirmed) { notify({ ... }); }` | Si presiona "Guardar Políticas", sale una confirmación de éxito. |

### C. Copia de Seguridad (`realizarBackup`)
| Código | ¿Qué hace? |
| :--- | :--- |
| `window.realizarBackup = function () {` | Función que simula la creación del respaldo del sistema. |
| `window.Swal.showLoading();` | Muestro un círculo de carga girando con el mensaje *"Comprimiendo base de datos y archivos multimedia..."*. |
| `setTimeout(() => { window.Swal.fire({ icon: 'success', title: '¡Copia de Seguridad Lista!' ... }); }, 1500);` | Al cabo de un segundo y medio, detengo la animación y muestro que el archivo `backup_ohlalash_YYYY-MM-DD.sql.gz` fue generado con éxito. |

### D. Restablecer Parámetros (`confirmarRestablecer`)
| Código | ¿Qué hace? |
| :--- | :--- |
| `window.confirmarRestablecer = async function () {` | Función para volver a la configuración inicial. |
| `const confirmed = await confirmAction({ title: '¿Restablecer configuración?', ... icon: 'warning' });` | Muestro una advertencia amarilla preguntando si de verdad quiere volver a los valores de fábrica. |
| `if (confirmed) { notify({ icon: 'success', text: 'Los valores por defecto han sido restaurados.' }); }` | Si confirma, le aviso que los ajustes volvieron a su estado original. |

### E. Eliminar Cuenta de Negocio (`confirmarEliminarCuenta`)
| Código | ¿Qué hace? |
| :--- | :--- |
| `window.confirmarEliminarCuenta = function () {` | Función de máxima seguridad para evitar accidentes graves. |
| `window.Swal.fire({ title: '¿Eliminar cuenta del negocio?', icon: 'error' ... });` | Primera pregunta en color rojo avisando que la acción es irreversible. |
| `if (result.isConfirmed) { window.Swal.fire({ title: 'Escribe "ELIMINAR" para confirmar', input: 'text' ... }); }` | Si dice que sí, no borro todavía: le pido que escriba con su propio teclado la palabra "ELIMINAR". |
| `preConfirm: (valor) => { if (valor !== 'ELIMINAR') window.Swal.showValidationMessage(...); }` | Si escribe cualquier otra cosa o se equivoca en una letra, la rechazo y no permito continuar. |

---

## 5. Arranque del Módulo (`initConfiguracion`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initConfiguracion(customConfig = {}) {` | La función que pone a andar las previsualizaciones y los botones. |
| `setupLogoPreview(config);` | Activo la vista previa del logo. |
| `setupConfigModals(config);` | Registro todos los modales de impuestos, términos, copias de seguridad y eliminación. |
| `document.addEventListener('DOMContentLoaded', () => { ... });` | Cuando la página termine de cargar en el navegador, revisa si estamos en la vista de configuración y arranca todo automáticamente. |
