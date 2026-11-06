# Explicación de mi código de Registro de Usuarios (`register.js`)

En este archivo me encargo de dos tareas clave en la pantalla de registro de **LashApp**: primero, generar un efecto visual de destellos dorados brillantes flotando en el panel decorativo lateral, y segundo, permitir ver u ocultar la contraseña al hacer clic en el ícono del ojo.

---

## 1. Configuración inicial (`initRegister`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initRegister(customConfig = {}) {` | La función que pone a andar las animaciones y funciones de la vista de registro. |
| `panelSelector: '#leftPanel',` | El panel decorativo izquierdo donde van a brillar las partículas. |
| `toggleSelector: '#togglePass',` | El botón del ojo para revelar la contraseña. |
| `passwordSelector: '#password',` | La casilla de texto donde se escribe la contraseña. |
| `sparklesCount: 55,` | La cantidad exacta de brillitos dorados que voy a crear (55 partículas). |

---

## 2. Generador de destellos dorados decorativos

| Código | ¿Qué hace? |
| :--- | :--- |
| `const panel = document.querySelector(config.panelSelector);` | Busco el panel lateral en la pantalla. |
| `for (let i = 0; i < config.sparklesCount; i++) {` | Hago un ciclo para fabricar una por una las 55 partículas. |
| `const sparkle = document.createElement('div'); sparkle.classList.add('sparkle');` | Creo un puntito nuevo con la clase de estilo `sparkle`. |
| `const size = Math.random() * 5 + 2;` | Calculo un tamaño aleatorio entre 2 y 7 píxeles para que no todos los brillos sean del mismo tamaño. |
| `sparkle.style.width = size + 'px'; sparkle.style.height = size + 'px';` | Asigno el ancho y alto calculado al brillito. |
| `sparkle.style.left = Math.random() * 100 + '%';` | Lo coloco en una posición horizontal al azar en cualquier parte del panel (de 0% a 100%). |
| `sparkle.style.top = Math.random() * 100 + '%';` | Lo coloco en una altura vertical al azar. |
| `sparkle.style.animationDelay = Math.random() * 3 + 's';` | Le doy un retraso aleatorio a la animación para que no parpadeen todos al mismo tiempo. |
| `sparkle.style.animationDuration = Math.random() * 2 + 2 + 's';` | Hago que cada brillo titile a su propia velocidad (entre 2 y 4 segundos). |
| `panel.appendChild(sparkle);` | Inserto el brillo terminado dentro del panel decorativo. |

---

## 3. Visibilidad de contraseña ("El Ojito")

| Código | ¿Qué hace? |
| :--- | :--- |
| `const toggle = document.querySelector(config.toggleSelector);` | Busco el botón del ojo. |
| `const password = document.querySelector(config.passwordSelector);` | Busco la casilla donde se escribe la clave. |
| `toggle.addEventListener('click', function () { ... });` | Escucho cuando la persona hace clic sobre el ojo: |
| `if (password.type === 'password') {` | Si la clave está oculta en forma de asteriscos o puntos... |
| `password.type = 'text';` | La muestro en texto legible. |
| `toggle.classList.remove('fa-eye'); toggle.classList.add('fa-eye-slash');` | Cambio el dibujo al ojo tachado. |
| `else { password.type = 'password'; ... }` | Si ya estaba visible, la vuelvo a esconder con puntos de seguridad y restauro el ojo abierto. |

---

## 4. Arranque automático

| Código | ¿Qué hace? |
| :--- | :--- |
| `document.addEventListener('DOMContentLoaded', () => { ... });` | Espero a que el navegador termine de cargar la estructura de la página. |
| `if (document.getElementById('togglePass') \|\| document.querySelector('.register-card...'))` | Compruebo si estamos en la página de registro. |
| `initRegister();` | Arranco el generador de brillos y el control de la contraseña de manera automática. |
