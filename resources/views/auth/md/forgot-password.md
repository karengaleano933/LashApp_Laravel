# Explicación de mi Vista de Recuperación de Contraseña (`auth/forgot-password.blade.php`)

Esta vista permite a cualquier usuario de LashApp solicitar un enlace seguro a su correo electrónico para restablecer su contraseña en caso de olvido. Utiliza los componentes de Laravel Breeze para validar el correo y enviar el enlace de recuperación.

---

### 1. Estructura, mensaje y formulario de recuperación

| Código | ¿Qué hace? |
| :--- | :--- |
| `<x-guest-layout>` | Envuelve el contenido en el diseño limpio para usuarios invitados (sin barra lateral ni menús internos). |
| `<div class="mb-4 text-sm text-gray-600"> ... </div>` | Mensaje orientativo que explica que, tras ingresar el correo, el sistema enviará un enlace para elegir una nueva contraseña. |
| `<x-auth-session-status class="mb-4" :status="session('status')" />` | Muestra un aviso verde cuando el correo con el enlace ya fue enviado exitosamente. |
| `<form method="POST" action="{{ route('password.email') }}"> @csrf` | Formulario protegido con `@csrf` que envía la solicitud al controlador de contraseñas de Laravel. |
| `<x-input-label for="email" :value="__('Email')" />` | Etiqueta del campo de correo electrónico. |
| `<x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />` | Campo donde el usuario digita su correo registrado. |
| `<x-input-error :messages="$errors->get('email')" class="mt-2" />` | Muestra en rojo si el correo no existe en la base de datos o está mal escrito. |
| `<x-primary-button> ... </x-primary-button>` | Botón para enviar el enlace de restablecimiento al correo. |
