# Explicación de mi código de Inicio de Sesión (`login.js`)

En este archivo controlo la interactividad de la pantalla de inicio de sesión de **LashApp**. Principalmente me encargo de hacer que el botón del "ojito" funcione a la perfección, permitiendo que la persona que va a ingresar pueda alternar entre ver los caracteres de su contraseña o esconderlos bajo puntos de seguridad.

---

## 1. Configuración y búsqueda de elementos (`initLogin`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initLogin(customConfig = {}) {` | Es la función principal que inicializa el comportamiento del login. |
| `passwordSelector: '#password',` | El selector para encontrar la casilla de texto donde se ingresa la contraseña. |
| `toggleSelector: '.ojo',` | El selector del ícono del ojo que sirve como botón interactivo. |
| `const password = document.querySelector(config.passwordSelector);` | Busco en la página el campo de la clave. |
| `const ojo = document.querySelector(config.toggleSelector);` | Busco en la página el ícono del ojo. |

---

## 2. Mostrar u ocultar la contraseña (`mostrarPassword`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `window.mostrarPassword = function () {` | Defino la función de alternancia y la hago accesible globalmente para que funcione también si el HTML tiene un `onclick`. |
| `if (!password \|\| !ojo) return;` | Si por alguna razón no encuentro la casilla o el ojo, me detengo para evitar fallos. |
| `if (password.type === 'password') {` | Reviso si la clave está actualmente oculta como puntos. |
| `password.type = 'text';` | Cambio la casilla a tipo texto normal para que las letras sean legibles. |
| `ojo.classList.remove('fa-eye');` | Le quito al botón el ícono de ojo abierto. |
| `ojo.classList.add('fa-eye-slash');` | Le pongo el ícono del ojo tachado para indicar que al volver a tocar se esconderá. |
| `else { password.type = 'password'; ... }` | Si ya estaba visible, vuelvo a esconderla bajo puntos y regreso al ícono del ojo abierto (`fa-eye`). |
| `if (ojo) { ojo.addEventListener('click', window.mostrarPassword); }` | Le añado un detector de clics al ojito para que active la función cada vez que el usuario lo presione. |

---

## 3. Arranque automático

| Código | ¿Qué hace? |
| :--- | :--- |
| `if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', ...); }` | Espero a que el navegador termine de cargar la estructura HTML de la página. |
| `if (document.getElementById('password') && document.querySelector('.login-card...'))` | Reviso si estamos en la pantalla de login. |
| `initLogin();` | Ejecuto la inicialización de forma automática sin que el usuario tenga que hacer nada extra. |
