# Explicación de mi código de Barra Lateral del Cliente (`sidebarcliente.js`)

En este archivo controlo el menú lateral y la cabecera cuando una clienta inicia sesión en **LashApp**. Como la clienta necesita exactamente las mismas comodidades de navegación que la administradora (abrir y colapsar el menú en teléfonos móviles, desplegar su perfil y cerrar menús al hacer clic afuera), este archivo reutiliza de manera eficiente la lógica centralizada de navegación.

---

## 1. Importación y Reutilización Modular

| Código | ¿Qué hace? |
| :--- | :--- |
| `import { initSidebarAdmin } from './sidebaradmin.js';` | Importo todas las funciones de control de navegación que ya construí para la barra lateral. |
| `export const initSidebarCliente = initSidebarAdmin;` | Le doy un nombre propio para el rol de clientas (`initSidebarCliente`), vinculándolo a la lógica probada de navegación. |

---

## 2. Arranque automático en vistas de Cliente

| Código | ¿Qué hace? |
| :--- | :--- |
| `if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', ...); }` | Espero a que el navegador termine de cargar el HTML. |
| `if (document.querySelector('.top-header') \|\| document.querySelector('#sidebar'))` | Reviso si en la pantalla actual existe la barra superior o el menú lateral de la clienta. |
| `initSidebarCliente();` | Activo los eventos de clic y apertura móvil automáticamente. |
