# Guía de Segmentación y Parametrización de Código Fuente en LashApp

Esta guía documenta la arquitectura implementada para la **Segmentación** y **Parametrización** del código JavaScript y su interacción con el Backend en **LashApp (Laravel + Vite)**.

---

## 1. Conceptos Fundamentales

### ¿Qué es la Segmentación?
La **segmentación** consiste en dividir el código fuente en módulos independientes con una única responsabilidad (Principio de Responsabilidad Única - SRP), eliminando scripts en línea embebidos dentro de las plantillas Blade y organizándolos en capas reutilizables.

### ¿Qué es la Parametrización?
La **parametrización** consiste en desacoplar el comportamiento del código de valores estáticos o "quemados" (hardcoded). En lugar de escribir URLs absolutas, selectores DOM fijos o textos de confirmación directamente dentro de funciones JS, se configuran a través de:
1. **Atributos `data-*`** en elementos contenedores del DOM (suministrados dinámicamente por Laravel Blade).
2. **Objetos de configuración** con valores por defecto que pueden sobreescribirse.
3. **Etiquetas `<meta>`** estándar para configuraciones globales (ej. `csrf-token`, `app-url`).

---

## 2. Estructura de la Arquitectura Implementada

resources/js/
├── app.js                          # Punto de entrada unificado y namespace global (window.LashApp)
├── common/                         # Capa de utilidades transversales parametrizadas
│   ├── csrf.js                     # Extracción segura y desacoplada del token CSRF
│   ├── alerts.js                   # Wrappers configurables de SweetAlert2 (confirmaciones y toasts)
│   └── http.js                     # Cliente HTTP (fetch) con inyección automática de CSRF y headers
└── modules/                        # Módulos de lógica de negocio por dominio
    ├── layout/
    │   ├── sidebaradmin.js         # Sidebar Admin, buscador Ctrl+K, notificaciones y perfil
    │   ├── sidebartrabajador.js    # Sidebar Trabajador
    │   └── sidebarcliente.js       # Sidebar Cliente
    ├── auth/
    │   ├── login.js                # Login y alternancia de contraseña
    │   └── register.js             # Registro, efectos visuales y visibilidad de contraseña
    └── admin/
        ├── servicios.js            # Filtros en tiempo real, modales CRUD, confirmación de eliminación
        ├── citas.js                # DataTables, filtros, calendario reactivo y cambios de estado
        ├── gusuarios.js            # Gestión de usuarios, toggle password, modales y confirmación
        ├── pagos.js                # Filtros, autocompletar monto por cita, modales y eliminar
        ├── resenas.js              # Selector de estrellas, filtros por valoración y eliminar
        ├── reportes.js             # Gráficos estadísticos con Chart.js parametrizados por atributos
        └── configuracion.js        # Preview de logo, modales de tarifas/políticas y acciones del sistema

---

## 3. Capa Transversal (`resources/js/common/`)

### 3.1 Manejo de CSRF (`csrf.js`)
Permite obtener el token sin interpolar Blade `{{ csrf_token() }}` dentro de JavaScript:
```javascript
import { getCsrfToken } from './common/csrf.js';
const token = getCsrfToken(); // Lee automáticamente de <meta name="csrf-token">
```

### 3.2 Alertas y Confirmaciones (`alerts.js`)
Centraliza los estilos, colores corporativos y botones:
```javascript
import { confirmAction, showToast } from './common/alerts.js';

const confirmado = await confirmAction({
    title: '¿Eliminar registro?',
    text: 'Esta acción no se puede deshacer.',
    isDestructive: true,
    confirmButtonText: 'Sí, eliminar'
});

if (confirmado) {
    showToast({ title: 'Operación exitosa', icon: 'success' });
}
```

### 3.3 Cliente HTTP Asíncrono (`http.js`)
Cliente parametrizado sobre Fetch API que inyecta automáticamente el token CSRF y cabeceras JSON:
```javascript
import { http } from './common/http.js';

// GET
const notificaciones = await http.get('/admin/notificaciones');

// POST
await http.post('/admin/notificaciones/marcar-leidas', {});
```

---

## 4. Ejemplo Práctico: Módulo de Servicios (`servicios.js`)

### Antes (Código Acoplado y No Segmentado)
En `servicios.blade.php` existían más de 140 líneas dentro de `<script>` con:
- URLs quemadas: `"{{ url('/admin/servicios') }}/" + id`
- Dependencia directa con el renderizado Blade.
- Sin modularización ni reutilización.

### Después (Segmentado y Parametrizado)

#### 1. En la vista Blade (`servicios.blade.php`):
Solo se importan los estilos y el script vía Vite, pasando parámetros por atributos HTML:
```html
@vite([
    'resources/css/servicios.css',
    'resources/js/modules/admin/servicios.js'
])

<!-- Contenedor con endpoint parametrizado -->
<div class="page-content" id="serviciosContainer" data-base-url="{{ url('/admin/servicios') }}">
    ...
</div>
```

#### 2. En el archivo JavaScript (`resources/js/modules/admin/servicios.js`):
El módulo lee la URL del contenedor o acepta un objeto de opciones:
```javascript
export function initServicios(customConfig = {}) {
    const container = document.querySelector('#serviciosContainer');
    const domBaseUrl = container?.dataset?.baseUrl;

    const config = {
        baseUrl: domBaseUrl || '/admin/servicios',
        ...customConfig
    };

    // Sub-segmentos aislados
    setupServiceFiltering(config);
    setupModalLifecycle(config);
    setupEditService(config);
    setupDeleteService(config);
}
```

---

## 5. Cómo Crear un Nuevo Módulo Segmentado y Parametrizado

Cuando vayas a crear un nuevo módulo (por ejemplo `usuarios.js` o `pagos.js`):

1. **Crear el archivo en `resources/js/modules/admin/[modulo].js`**:
   - Definir un objeto `DEFAULT_CONFIG` con selectores, endpoints y textos.
   - Dividir la lógica en funciones pequeñas (`setupFilters`, `setupModals`, `setupActions`).
   - Exportar `initModulo(customConfig)`.
   - Permitir auto-inicialización en `DOMContentLoaded` si el contenedor existe.
2. **Registrarlo en `vite.config.js`**:
   - Añadir `'resources/js/modules/admin/[modulo].js'` en el array `input`.
3. **Incluirlo en la vista Blade**:
   - Agregar `@vite(['resources/js/modules/admin/[modulo].js'])`.
   - Pasar los endpoints necesarios mediante atributos `data-*` en el elemento contenedor padre.
4. **Compilar y Verificar**:
   - Ejecutar `npm run build` para asegurar la compilación de Vite.

---

## 6. Parametrización en el Backend (Laravel)

Para completar la parametrización en el Backend:
1. **Form Requests**: Extraer reglas de validación de los controladores a clases `app/Http/Requests/*`.
2. **Services**: Mover la lógica de subida de imágenes y cálculo de horarios a `app/Services/*`.
3. **Archivos de Configuración**: Definir rutas fijas de imágenes y estados permitidos en `config/lashapp.php` o variables `.env`.
