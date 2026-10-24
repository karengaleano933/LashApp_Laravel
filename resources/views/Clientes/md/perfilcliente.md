# Explicación Código por Código de `perfilcliente.blade.php` (En mis palabras)

Este documento detalla **cada bloque, línea y función de código** que compone el archivo [`perfilcliente.blade.php`](file:///c:/laragon/www/LashApp/resources/views/Clientes/perfilcliente.blade.php), el espacio personal donde las clientas de **LashApp** actualizan sus datos personales, cambian su foto de perfil con 1 solo clic, modifican su contraseña, descargan sus datos en JSON o gestionan la privacidad de su cuenta.

---

## 1. Encabezado, Formularios Ocultos y Preparación (Líneas 1 - 49)

```blade
<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Mi Perfil'])
@include('layouts.sidebarcliente')

@vite(['resources/css/perfilcliente.css'])

@php
    use Carbon\Carbon;

    $fotoSrc = null;
    if (!empty($user->foto) && file_exists(public_path($user->foto))) {
        $fotoSrc = asset($user->foto);
    } elseif (file_exists(public_path('img/perfil_cliente.jpg'))) {
        $fotoSrc = asset('img/perfil_cliente.jpg');
    }

    $initials = strtoupper(substr($user->name ?? 'C', 0, 1) . substr($user->apellido ?? 'L', 0, 1));
    $nombreCompleto = trim(($user->name ?? '') . ' ' . ($user->apellido ?? ''));
    $rolNombre = ucfirst($user->role?->nombre ?? 'Cliente');
@endphp

<!-- Form oculto para subir foto de perfil al hacer clic en la cámara -->
<form method="POST" action="{{ route('cliente.perfil.foto') }}" enctype="multipart/form-data" id="fotoForm" style="display:none;">
    @csrf
    <input type="file" name="foto_perfil" id="fotoInput" accept="image/*" onchange="document.getElementById('fotoForm').submit();">
</form>

<!-- Form oculto para desactivar cuenta -->
<form method="POST" action="{{ route('cliente.perfil.eliminar') }}" id="formEliminarCuenta" style="display:none;">
    @csrf
</form>
```

### ¿Qué hace cada parte?
* **Lógica de Foto (`$fotoSrc`):** Comprueba si el usuario tiene una foto guardada en el servidor; si no existe, toma la imagen por defecto o genera sus iniciales (`$initials`, ej. `CG`).
* **Formulario `#fotoForm`:** Está oculto (`display:none`). Cuando la clienta toca el ícono de la camarita, se activa el `input type="file"`; apenas elige la foto en su galería, el evento `onchange` hace `submit()` automáticamente sin obligarla a buscar un botón de "Guardar".
* **Formulario `#formEliminarCuenta`:** Formulario `POST` protegido con `@csrf` para enviar la solicitud de baja tras la confirmación de seguridad.

---

## 2. Alertas de Sesión y Pestañas de Navegación (Líneas 68 - 110)

```blade
    @if(session('success'))
        <div class="alert alert-success">... {{ session('success') }} ...</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">... {{ session('error') }} ...</div>
    @endif

    <div class="perfil-tabs-bar">
        <button type="button" class="perfil-tab-btn active" data-tab="info">
            <i class="fa-regular fa-user"></i> Información personal
        </button>
        <button type="button" class="perfil-tab-btn" data-tab="preferencias">
            <i class="fa-regular fa-heart"></i> Preferencias
        </button>
        <button type="button" class="perfil-tab-btn" data-tab="seguridad">
            <i class="fa-solid fa-shield-halved"></i> Seguridad
        </button>
        <button type="button" class="perfil-tab-btn" data-tab="acciones">
            <i class="fa-solid fa-gears"></i> Acciones de cuenta
        </button>
    </div>
```

* Las 4 pestañas permiten hacer scroll automático suave (`scrollIntoView`) hasta cada tarjeta de la página mediante JavaScript.

---

## 3. Bloque 1: Información Personal y Avatar (Líneas 119 - 191)

```blade
    <div class="perfil-card" id="sec-info">
        <div class="perfil-card-title"><i class="fa-regular fa-user"></i> Información personal</div>

        <form method="POST" action="{{ route('cliente.perfil.update') }}">
            @csrf
            <div class="info-personal-wrap">
                <!-- Avatar a la izquierda -->
                <div class="avatar-section">
                    <div class="avatar-img-wrap">
                        @if($fotoSrc)
                            <img src="{{ $fotoSrc }}" alt="Foto Perfil" class="avatar-img" id="avatarImg">
                        @else
                            <div class="avatar-initials-fallback" id="avatarImg">{{ $initials }}</div>
                        @endif
                        <div class="avatar-edit-badge" title="Cambiar foto de perfil" onclick="document.getElementById('fotoInput').click();">
                            <i class="fa-solid fa-camera"></i>
                        </div>
                    </div>
                    <div class="avatar-name">{{ $nombreCompleto ?: 'Usuario LashApp' }}</div>
                    <div class="avatar-role">{{ $rolNombre }}</div>
                    <div class="avatar-badge-member">
                        <i class="fa-regular fa-calendar-check"></i> Miembro activo
                    </div>
                </div>

                <!-- Campos de formulario -->
                <div class="info-form-grid">
                    <input type="text" name="nombre" value="{{ old('nombre', $user->name) }}" required>
                    <input type="text" name="apellido" value="{{ old('apellido', $user->apellido) }}" required>
                    <input type="email" value="{{ $user->email }}" readonly style="background:#faf7fd; cursor:not-allowed;">
                    <input type="text" name="telefono" value="{{ old('telefono', $user->telefono) }}">
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $cliente?->fecha_nacimiento) }}">
                    <input type="text" name="documento" value="{{ old('documento', $user->documento) }}">
                    <input type="text" name="direccion" value="{{ old('direccion', $cliente?->direccion) }}">

                    <button type="submit" class="btn-edit-info">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
```

### ¿Qué hace cada campo?
* **Avatar con Insignia de Cámara (`.avatar-edit-badge`):** Dispara `document.getElementById('fotoInput').click()`.
* **Correo de Solo Lectura (`readonly`):** Evita que cambien el correo sin autorización administrativa.
* **Separación de Modelos:** `name`, `apellido`, `telefono` y `documento` se actualizan en el modelo `User`; `direccion` y `fecha_nacimiento` se actualizan en el modelo `Cliente`.

---

## 4. Bloque 2: Preferencias y Notificaciones (Líneas 196 - 239)

```blade
    <div class="perfil-card" id="sec-preferencias">
        <div class="perfil-card-title"><i class="fa-regular fa-heart"></i> Preferencias</div>
        <div class="pref-list">
            <!-- Servicios favoritos, Atención personalizada, Recordatorios de citas -->
        </div>
        <button type="button" class="btn-card-bottom" onclick="Swal.fire(...)">
            <i class="fa-solid fa-gear"></i> Gestionar preferencias
        </button>
    </div>
```

* Lista de ajustes de experiencia de la clienta (recordatorios activados y especialistas preferidas).

---

## 5. Bloque 3: Seguridad y Cambio de Contraseña (Líneas 242 - 306)

```blade
    <div class="perfil-card" id="sec-seguridad">
        <div class="perfil-card-title"><i class="fa-solid fa-shield-halved"></i> Seguridad</div>

        <!-- Botón para desplegar formulario -->
        <button type="button" class="btn-seg-action" onclick="togglePasswordForm()">Cambiar</button>

        <!-- Formulario desplegable -->
        <div id="formPasswordWrap" style="display:none; ...">
            <form method="POST" action="{{ route('cliente.perfil.password') }}">
                @csrf
                <input type="password" name="contrasena_actual" required placeholder="Tu contraseña actual">
                <input type="password" name="contrasena_nueva" minlength="6" required placeholder="Mínimo 6 caracteres">
                <input type="password" name="contrasena_confirmar" minlength="6" required placeholder="Repite la nueva contraseña">
                
                <button type="submit" class="btn-edit-info">Actualizar contraseña</button>
                <button type="button" onclick="togglePasswordForm()">Cancelar</button>
            </form>
        </div>
    </div>
```

* **Función `togglePasswordForm()`:** Muestra u oculta el formulario con animación suave.
* **Validación de longitud:** `minlength="6"` exige contraseñas seguras.

---

## 6. Columna Derecha: Widgets y Acciones de Cuenta (Líneas 310 - 425)

```blade
    <div class="perfil-sidebar">

        <!-- WIDGET 1: RESUMEN DE ACTIVIDAD -->
        <div class="act-stat-box">
            <div class="act-stat-num">{{ $totalCitas ?? 0 }}</div>
            <div class="act-stat-label">Citas agendadas</div>
        </div>
        <div class="act-stat-box">
            <div class="act-stat-num">{{ $citasRealizadas ?? 0 }}</div>
            <div class="act-stat-label">Servicios realizados</div>
        </div>

        <!-- WIDGET 2: PRÓXIMA CITA -->
        <!-- Tarjeta visual con servicio, fecha, hora y botón "Ver mis citas" -->

        <!-- WIDGET 3: ACCIONES DE CUENTA (EXPORTAR Y DESACTIVAR) -->
        <div class="perfil-card" id="sec-acciones">
            <a href="{{ route('cliente.perfil.exportar') }}" class="cuenta-action-item">
                <i class="fa-solid fa-download"></i> Descargar mis datos
            </a>
            <a href="javascript:void(0);" class="cuenta-action-item" onclick="document.getElementById('sidebarLogoutForm')?.submit()">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar sesión
            </a>
            <a href="javascript:void(0);" class="cuenta-action-item danger" onclick="confirmarEliminarCuenta();">
                <i class="fa-solid fa-trash-can"></i> Desactivar cuenta
            </a>
        </div>
    </div>
```

* **Exportar Datos:** Enlaza a `cliente.perfil.exportar` que descarga el JSON con la información personal y citas.
* **Desactivar Cuenta (`confirmarEliminarCuenta()`):** Pide confirmación con SweetAlert2 antes de enviar el formulario `#formEliminarCuenta`.

---

## 7. Scripts de JavaScript (Líneas 430 - 484)

```javascript
    // 1. Mostrar/Ocultar formulario de contraseña
    function togglePasswordForm() {
        const wrap = document.getElementById('formPasswordWrap');
        if (wrap) {
            wrap.style.display = (wrap.style.display === 'none' || wrap.style.display === '') ? 'block' : 'none';
            if (wrap.style.display === 'block') wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // 2. Alerta de confirmación de desactivación
    function confirmarEliminarCuenta() {
        Swal.fire({
            title: '¿Desactivar tu cuenta?',
            text: "Tu usuario quedará inactivo y tu sesión se cerrará inmediatamente.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Sí, desactivar cuenta'
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('formEliminarCuenta').submit();
        });
    }

    // 3. Scroll suave al hacer clic en pestañas
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.perfil-tab-btn').forEach(tab => {
            tab.addEventListener('click', function() {
                const target = this.getAttribute('data-tab');
                document.getElementById('sec-' + target)?.scrollIntoView({ behavior: 'smooth' });
            });
        });
    });
```
