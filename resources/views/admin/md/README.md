# Documentación de las Vistas del Administrador (`resources/views/admin/`)

Bienvenida a la carpeta de documentación de las vistas del panel de administración de **LashApp**. En esta carpeta encontrarás explicaciones detalladas y claras de cada una de las pantallas que componen el módulo de administración, redactadas en un lenguaje cercano, estructurado y sin tecnicismos innecesarios.

---

## Índice de Documentación de Vistas

| Archivo Markdown | Vista Blade Correspondiente | Propósito Principal |
| :--- | :--- | :--- |
| **[`dashboard.md`](./dashboard.md)** | `dashboard.blade.php` | Torre de control, métricas generales en tiempo real, resumen de citas y accesos rápidos. |
| **[`citas.md`](./citas.md)** | `citas/citas.blade.php` | Agenda general de citas, calendario interactivo en JS, filtros por especialista y modal de nueva cita. |
| **[`gusuarios.md`](./gusuarios.md)** | `gusuarios/gusuarios.blade.php` | Gestión de cuentas de usuario, asignación de roles (Admin, Trabajador, Cliente), modales de creación y edición. |
| **[`servicios.md`](./servicios.md)** | `servicios/servicios.blade.php` | Catálogo de tratamientos de pestañas y cejas, precios, fotos, duraciones y categorías. |
| **[`pagos.md`](./pagos.md)** | `pagos/pagos.blade.php` | Control financiero, historial de transacciones, visor de comprobantes bancarios y aprobación de pagos. |
| **[`resenas.md`](./resenas.md)** | `resenas/resenas.blade.php` | Monitoreo de reputación, desglose de calificaciones por estrellas, tabla de testimonios y registro manual. |
| **[`reportes.md`](./reportes.md)** | `reportes/reportes.blade.php` | Analítica de negocio con Chart.js, gráficos de ingresos, comparativa por períodos y exportación a PDF. |
| **[`configuracion.md`](./configuracion.md)** | `configuracion/configuracion.blade.php` | Ajustes de marca, logo del estudio, políticas de reserva/cancelación, zona horaria y moneda. |

---

## Estructura de Diseño Compartida

Todas las vistas de administración siguen una arquitectura visual homogénea y coherente:

1. **Inclusión de Layouts:**
   * `@include('layouts.header')`: Metadatos, fuentes Google y estilos CSS.
   * `@include('layouts.sidebaradmin')`: Menú lateral izquierdo con navegación por roles y barra superior.
   * `@include('layouts.footer')`: Cierre de contenedores y scripts compartidos.

2. **Alertas del Sistema:**
   * Alertas verdes con `session('success')` para confirmar acciones guardadas o eliminadas.
   * Alertas rojas con `$errors->any()` para avisar si faltan campos requeridos en los formularios.

3. **Métricas en Tarjetas (Cards Grid):**
   * Tarjetas con acentos de color (`purple`, `pink`, `gold`, `green`, `blue`) para lectura rápida de números clave.

4. **Interactividad Dinámica:**
   * DataTables y buscadores en tiempo real.
   * Modales emergentes para no obligar a la administradora a cambiar de página.
   * Confirmaciones seguras con **SweetAlert2** para acciones críticas (cancelar o eliminar).
