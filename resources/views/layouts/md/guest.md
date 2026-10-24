# Documentación Técnica: Layout de Invitados y Autenticación (`guest.blade.php`)

> **Autor:** Karen Galeano  
> **Ubicación:** `resources/views/layouts/guest.blade.php`  
> **Propósito:** Plantilla maestra para las pantallas de acceso público y flujo de autenticación (Login, Registro, Recuperación de Contraseña y Verificación de Correo).

---

## 1. ¿Qué es este archivo y por qué lo diseñé así? (En mis palabras)

Diseñé este layout para albergar todas las vistas donde el usuario **aún no ha iniciado sesión** o se encuentra en un flujo de autenticación crítico. 

A diferencia del layout privado donde tengo sidebars o barras de navegación completas, aquí busco una experiencia **limpia, minimalista y centrada**, sin distracciones visuales. Coloco el logotipo de mi aplicación en la parte superior y debajo presento una tarjeta blanca elegante con sombras suaves donde se renderiza el formulario correspondiente (por ejemplo el login, registro o restablecimiento de clave).

---

## 2. Desglose y Explicación Detallada de Todo el Código

### A. Encabezado HTML y Configuración de Assets
Al igual que en mis otras plantillas base, defino las directivas principales para optimización y seguridad:

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
```

**¿Cómo funciona y por qué lo programé así?:**
- Mantengo la coherencia tipográfica de `Figtree` y la compilación ágil mediante `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- El token CSRF `<meta name="csrf-token">` es indispensable para proteger todos los formularios de autenticación contra vulnerabilidades de falsificación de peticiones en sitios cruzados.

---

### B. Contenedor Centrado, Logotipo y Tarjeta de Autenticación
Aquí configuro la disposición visual tipo caja centrada con Flexbox:

```blade
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
```

**¿Cómo funciona y por qué lo programé así?:**
- `min-h-screen flex flex-col sm:justify-center items-center`: Con Tailwind CSS hago que el contenedor ocupe el alto completo de la pantalla y centre verticalmente la tarjeta en pantallas de tablet o escritorio (`sm:justify-center`), manteniendo una separación superior agradable en móviles (`pt-6`).
- `<a href="/"><x-application-logo ... /></a>`: Enlaza directamente a la página de bienvenida o landing de mi estudio mediante el componente de logotipo de la app, dándole identidad visual inmediata.
- `w-full sm:max-w-md bg-white shadow-md sm:rounded-lg`: Es la tarjeta de autenticación. En móviles aprovecha el 100% del ancho (`w-full`), y en pantallas más grandes se limita a un ancho máximo estético de 448px (`max-w-md`) con esquinas redondeadas (`sm:rounded-lg`) y una sombra suave (`shadow-md`).
- `{{ $slot }}`: Es el punto donde se insertan los formularios de Blade (`login.blade.php`, `register.blade.php`, `forgot-password.blade.php`, etc.).

---

## 3. Flujo Visual de la Vista

```mermaid
graph TD
    A[Usuario entra a /login o /register] --> B[guest.blade.php]
    B --> C[Logotipo centrado superior x-application-logo]
    B --> D[Tarjeta blanca sm:max-w-md con sombra]
    D --> E[Inyección de {{ $slot }} con inputs de autenticación]
```
