# Documentación de las Vistas del Portal de Clientes (`resources/views/Clientes/`)

Bienvenida a la carpeta de documentación de las vistas del portal privado de **Clientas** de **LashApp**. En esta carpeta encontrarás explicaciones detalladas y claras de cada una de las pantallas que componen la experiencia del cliente, redactadas en un lenguaje amigable, estructurado y sin tecnicismos innecesarios.

---

## Índice de Documentación de Vistas de Clientas

| Archivo Markdown | Vista Blade Correspondiente | Propósito Principal |
| :--- | :--- | :--- |
| **[`dashboardcliente.md`](./dashboardcliente.md)** | `dashboardcliente.blade.php` | Inicio de la clienta, recordatorio de próxima cita, catálogo destacado, promociones activas y modal de agendas disponibles. |
| **[`catalogo.md`](./catalogo.md)** | `catalogo.blade.php` | Galería y vitrina comercial con filtros por categoría, buscador en vivo, ordenamiento por precio y favoritos. |
| **[`agendarcitas.md`](./agendarcitas.md)** | `agendarcitas.blade.php` | Sistema guiado en 4 pasos para reservar citas, calendario dinámico, selección de turnos y pasarela de pagos (Tarjeta, PSE, Nequi, Efectivo). |
| **[`miscitas.md`](./miscitas.md)** | `miscitas.blade.php` | Gestión y seguimiento de citas activas divididas en pestañas (Próximas, Pendientes, Completadas, Canceladas) con mini-calendario. |
| **[`historial.md`](./historial.md)** | `historial.blade.php` | Historial acumulado de tratamientos, total invertido en belleza, tarjetas tipo calendario y opción de imprimir o descargar en PDF. |
| **[`perfilcliente.md`](./perfilcliente.md)** | `perfilcliente.blade.php` | Administración de datos personales, cambio de foto de perfil con 1 clic, cambio de contraseña y descarga de datos en JSON. |
| **[`resenas.md`](./resenas.md)** | `resenas.blade.php` | Panel de satisfacción, recordatorio de citas pendientes por calificar, estrellas interactivas y testimonios de la clienta. |

---

## Estructura de Diseño Compartida en el Portal Cliente

Todas las vistas de clientas siguen una arquitectura visual y funcional coherente:

1. **Inclusión de Layouts Especializados:**
   * `@include('layouts.headercliente')`: Metadatos, hojas de estilo específicas para la clienta y barra superior.
   * `@include('layouts.sidebarcliente')`: Menú lateral estilizado con navegación por los módulos del portal.
   * `@include('layouts.footer')`: Cierre de contenedores y carga de scripts globales.

2. **Interactividad Dinámica:**
   * Modales flotantes para agendar, calificar, ver horarios y consultar detalles sin abandonar la pantalla actual.
   * Alertas y confirmaciones visuales usando **SweetAlert2**.

3. **Optimización Responsive:**
   * Cuadrículas adaptables para celulares, tablets y computadores de escritorio, garantizando que agendar una cita desde el celular sea una experiencia cómoda y fluida.
