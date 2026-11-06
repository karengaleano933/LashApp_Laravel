# Explicación de mi Vista de Perfil de Trabajador (`perfil/perfil_trabajador.blade.php`)

Esta vista es la ficha profesional de la lashista o especialista en LashApp. Desde aquí la trabajadora puede ver y actualizar sus datos personales (nombre, teléfono, documento, especialidad técnica y biografía profesional), cambiar su foto de perfil con un botón de cámara flotante, actualizar su contraseña de forma segura y revisar los detalles de su próxima cita agendada.

---

### 1. Inclusión de componentes y preparación de datos

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header_trabajador', ['titulo' => 'Mi Perfil'])` | Cargo la cabecera de la trabajadora con el título "Mi Perfil". |
| `@include('layouts.sidebar_trabajador')` | Cargo el menú lateral de navegación de la empleada. |
| `@vite(['resources/css/perfil_trabajador.css', 'resources/js/modules/trabajador/perfil.js'])` | Conecto la hoja de estilos y el script modular de perfil mediante Vite. |
| `<form method="POST" action="{{ route('trabajador.perfil.foto') }}" enctype="multipart/form-data" id="formFotoTrabajador" style="display:none;">` | Formulario invisible que se activa cuando selecciono una nueva foto desde el computador o celular para subirla al servidor. |

---

### 2. Tarjeta principal ("Hero Banner") del perfil

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="perfil-avatar-container"> ... </div>` | Muestra la foto de perfil circular de la especialista o sus iniciales si aún no ha subido foto. |
| `<button type="button" class="btn-cambiar-foto" onclick="document.getElementById('inputFotoTrabajador').click();">` | Botón flotante con ícono de cámara sobre la foto para abrir la ventana de selección de archivos. |
| `<h2> {{ $nombreCompleto }} <i class="fa-solid fa-circle-check" ...></i> </h2>` | Muestra el nombre completo de la especialista junto a una insignia morada de profesional verificada. |
| `<p> ... {{ $user?->email }} &bull; {{ $user?->telefono }} </p>` | Muestra su correo de contacto y número telefónico o WhatsApp. |
| `<span class="badge-especialidad"> ... {{ $trabajador?->especialidad }} </span>` | Pastilla con su especialidad técnica (ej. *Lash Artist & Brow Designer*). |
| `<div class="metric-pill-item"> ... {{ $citasAtendidas }} ... </div>` | Píldora de métrica con el número total de citas completadas con éxito. |
| `<div class="metric-pill-item"> ... {{ number_format($promedioCalificacion, 1) }} ★ ... </div>` | Píldora con su promedio de satisfacción en estrellas (ej. `4.9 ★`). |
| `<div class="metric-pill-item"> ... {{ $totalResenas }} ... </div>` | Píldora con la cantidad de testimonios recibidos de clientas. |

---

### 3. Pestañas de navegación del perfil

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="perfil-tab-btn active" data-tab="tab-informacion">` | Botón para abrir el panel de datos personales y biografía profesional. |
| `<button type="button" class="perfil-tab-btn" data-tab="tab-seguridad">` | Botón para abrir el formulario de cambio de clave. |
| `<button type="button" class="perfil-tab-btn" data-tab="tab-agenda">` | Botón para ver el detalle de la próxima clienta que atenderá. |

---

### 4. Panel 1: Datos personales y especialidad

| Código | ¿Qué hace? |
| :--- | :--- |
| `<form action="{{ route('trabajador.perfil.update') }}" method="POST"> @csrf` | Formulario protegido con token CSRF hacia la ruta de actualización del perfil. |
| `<input type="text" name="name" id="name" value="{{ old('name', $user?->name) }}" required>` | Campo para editar el nombre de pila. |
| `<input type="text" name="apellido" id="apellido" value="{{ old('apellido', $user?->apellido) }}" required>` | Campo para editar el apellido. |
| `<input type="text" name="documento" id="documento" ...>` | Campo para actualizar el número de cédula o documento. |
| `<input type="text" name="telefono" id="telefono" ...>` | Campo para actualizar el número celular de contacto. |
| `<input type="email" id="email" value="{{ $user?->email }}" readonly>` | Campo de correo electrónico en modo solo lectura (para evitar cambios accidentales sin administración). |
| `<input type="text" name="especialidad" id="especialidad" ... required>` | Campo para escribir su título profesional (ej. *Master en Pestañas Volumen Ruso*). |
| `<textarea name="descripcion" id="descripcion" rows="4"> ... </textarea>` | Cuadro de texto para redactar su biografía y experiencia en belleza de la mirada. |
| `<button type="submit" class="btn-guardar-perfil"> Guardar Cambios del Perfil </button>` | Botón morado para guardar las modificaciones en la base de datos. |

---

### 5. Panel 2: Seguridad y contraseña

| Código | ¿Qué hace? |
| :--- | :--- |
| `<form action="{{ route('trabajador.perfil.password') }}" method="POST"> @csrf` | Formulario para el cambio seguro de la clave de acceso. |
| `<input type="password" name="contrasena_actual" id="contrasena_actual" required>` | Campo obligatorio donde la lashista debe escribir su clave actual para verificar su identidad. |
| `<input type="password" name="contrasena_nueva" id="contrasena_nueva" required>` | Campo para escribir la nueva clave de mínimo 6 letras o números. |
| `<input type="password" name="contrasena_confirmar" id="contrasena_confirmar" required>` | Campo para repetir la nueva clave y asegurar que no hubo errores de tipeo. |
| `<button type="submit" class="btn-guardar-perfil"> Actualizar Contraseña </button>` | Botón para procesar el cambio de contraseña. |

---

### 6. Panel 3: Próxima cita agendada

| Código | ¿Qué hace? |
| :--- | :--- |
| `@if ($proximaCita) ... @else ... @endif` | Revisa si la especialista tiene una cita agendada en camino; si no tiene, muestra un aviso invitándola a abrir turnos en su horario. |
| `<div class="proxima-cita-box"> ... </div>` | Tarjeta con foto del servicio, nombre de la clienta, fecha completa y hora programada. |
| `<a href="{{ route('citas.index') }}" class="btn-guardar-perfil"> Ver en Mis Citas </a>` | Botón para ir directamente a la vista de citas. |
| `@include('layouts.footer')` | Pie de página común del sistema. |
