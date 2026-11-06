# Explicación de mi código de Barra Lateral y Cabecera (`sidebaradmin.js`)

En este archivo controlo todo el menú lateral y la barra superior de **LashApp**: encoger y expandir el menú para celulares o computadores, abrir el buscador rápido con el atajo de teclado `Ctrl + K`, mostrar el menú de perfil de usuario y gestionar las notificaciones en vivo (cargarlas sin recargar la página, actualizar la campanita roja con el número de alertas no leídas y marcarlas todas como leídas con un clic).

---

## 1. Importaciones y Libreta de Selectores (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `import { http } from '../../common/http.js';` | Traigo mi cliente de peticiones web para hablar con el servidor sin recargar la pantalla. |
| `import { showToast } from '../../common/alerts.js';` | Traigo la notificación tipo burbuja flotante para avisar que las alertas se marcaron como leídas. |
| `sidebarToggle: '#sidebarToggle',` | El botón de tres rayas (hamburguesa) para ocultar o mostrar el menú lateral. |
| `sidebar: '#sidebar',` | La barra lateral completa donde están los enlaces de navegación. |
| `searchOverlay: '#modalQuickSearchOverlay',` | El fondo oscuro de la ventana de búsqueda rápida. |
| `searchInput: '#quickSearchInput',` | La cajita donde se escribe para buscar páginas o secciones. |
| `notifToggleBtn: '#btnNotifToggle',` | El botón de la campanita en la barra superior. |
| `notifMenu: '#notifDropdownMenu',` | La ventanita desplegable donde caen las notificaciones. |
| `notifBadge: '#notifBadgeCount',` | El círculo rojo con el número de notificaciones pendientes (ej. "3" o "9+"). |
| `btnMarkRead: '#btnMarcarTodasLeidas',` | El botón que dice "Marcar todas como leídas". |
| `userToggleBtn: '#btnUserMenuToggle',` | El botón con la foto o inicial del usuario arriba a la derecha. |
| `userMenu: '#userProfileDropdown',` | El menú desplegable para ir a "Mi Perfil" o "Cerrar sesión". |

---

## 2. Abrir y Cerrar el Menú Lateral (`setupSidebar`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const toggleBtn = document.querySelector(config.selectors.sidebarToggle);` | Busco el botón de hamburguesa. |
| `const sidebar = document.querySelector(config.selectors.sidebar);` | Busco la barra lateral. |
| `toggleBtn.addEventListener('click', (e) => { ... });` | Cuando hago clic en el botón de hamburguesa: |
| `sidebar.classList.toggle(config.classes.active);` | Si está escondido en el celular, lo muestro en pantalla. |
| `sidebar.classList.toggle(config.classes.collapsed);` | Si está en el computador, lo encojo para dejar más espacio al contenido principal. |

---

## 3. Buscador Rápido con `Ctrl + K` (`setupQuickSearch`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function openSearch() { ... }` | Abre la ventana de búsqueda, limpia el texto anterior y pone el cursor automáticamente en la cajita de escribir. |
| `function closeSearch() { ... }` | Oculta la ventana de búsqueda rápida. |
| `function filterItems(query) { ... }` | Compara lo que escribe el usuario con el nombre de cada sección de la aplicación; si coincide, la muestra; si no, la oculta. |
| `triggerBtn.addEventListener('click', openSearch);` | Permite abrir el buscador al tocar la lupa en la cabecera. |
| `overlay.addEventListener('click', (e) => { ... });` | Cierra el buscador al hacer clic por fuera en el fondo oscuro. |
| `input.addEventListener('input', () => filterItems(input.value));` | Filtra las opciones en vivo a medida que se teclea. |

---

## 4. Centro de Notificaciones en vivo (`setupNotifications`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function updateBadge(count) { ... }` | Dibuja la bolita roja sobre la campanita. Si hay más de 9 avisos muestra "9+", si no hay ninguno la esconde. |
| `async function loadNotifications() { ... }` | Pide al servidor las notificaciones recientes sin recargar la página. |
| `if (!data?.notificaciones \|\| data.notificaciones.length === 0)` | Si la bandeja está limpia, dibuja un mensaje amigable con una campanita dormida diciendo que no hay pendientes. |
| `data.notificaciones.forEach((n) => { ... });` | Si hay notificaciones, dibuja cada una con su ícono de color (verde si es cita agendada, rojo si fue cancelada y azul si es informativa). |
| `function toggleDropdown() { ... }` | Abre o cierra la ventanita de la campana y carga las alertas más frescas. |
| `btnMarkAll.addEventListener('click', async (e) => { ... });` | Al tocar "Marcar todas como leídas", envía la orden al servidor, apaga los puntitos de no leído, pone el contador en 0 y muestra una notificación flotante de éxito. |

---

## 5. Menú de Perfil de Usuario (`setupUserMenu`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function toggleMenu() { menu.classList.toggle(config.classes.show); }` | Muestra u oculta la ventanita con el enlace a mi perfil y el botón de cerrar sesión. |
| `btnToggle.addEventListener('click', (e) => { e.stopPropagation(); toggleMenu(); });` | Abre el menú al tocar el avatar del usuario. |

---

## 6. Atajos de Teclado y Cierre Inteligente

| Código | ¿Qué hace? |
| :--- | :--- |
| `document.addEventListener('click', (e) => { ... });` | Si la campanita o el menú de usuario están abiertos y hago clic en cualquier otra parte de la pantalla, se cierran automáticamente. |
| `if ((e.ctrlKey \|\| e.metaKey) && (e.key === 'k' \|\| e.key === 'K'))` | Si presiono `Ctrl + K` (o `Cmd + K` en Mac), abro el buscador rápido de inmediato. |
| `else if (e.key === 'Escape')` | Si presiono la tecla `Escape`, cierro el buscador, las notificaciones y el menú de usuario de golpe. |

---

## 7. Inicialización (`initSidebarAdmin`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initSidebarAdmin(customConfig = {}) {` | La función que conecta y activa todos los componentes de la barra de navegación. |
| `document.addEventListener('DOMContentLoaded', ...);` | Arranca todo automáticamente en cuanto la página esté lista en el navegador. |
