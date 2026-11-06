# Explicación de mi código de Barra Lateral del Trabajador (`sidebartrabajador.js`)

En este archivo controlo el menú lateral y la cabecera cuando una especialista o trabajadora del salón ingresa a su panel en **LashApp**. Le permite colapsar y expandir el menú en su celular mientras atiende a sus clientas, ver su perfil profesional y cerrar sesión de manera cómoda y segura.

---

## 1. Importación y Reutilización Modular

| Código | ¿Qué hace? |
| :--- | :--- |
| `import { initSidebarAdmin } from './sidebaradmin.js';` | Importo el motor de control de barras laterales ya construido. |
| `export const initSidebarTrabajador = initSidebarAdmin;` | Asigno el inicializador específico para el personal de trabajo (`initSidebarTrabajador`). |

---

## 2. Arranque automático en vistas de Trabajador

| Código | ¿Qué hace? |
| :--- | :--- |
| `if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', ...); }` | Espero a que el navegador termine de cargar la vista del trabajador. |
| `if (document.querySelector('.top-header') \|\| document.querySelector('#sidebar'))` | Compruebo que existan los elementos de menú en la página. |
| `initSidebarTrabajador();` | Conecto la interactividad del menú de la trabajadora de forma automática. |
