# Explicación de mi Vista de Gestión de Usuarios (`gusuarios/gusuarios.blade.php`)

Esta vista es el centro donde controlo todos los usuarios registrados en LashApp (administradoras, trabajadoras/lashistas y clientas). Me permite monitorear cuántos usuarios tengo activos o inactivos, buscar personas en tiempo real, filtrar por su rol en el sistema, registrar nuevos usuarios con contraseña segura mediante una ventana modal, editar sus datos o estado, y eliminarlos tras una confirmación de seguridad.

---

### 1. Inclusión de estructura y alertas

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header')` | Cargo los estilos, tipografías e íconos generales del sistema. |
| `@include('layouts.sidebaradmin')` | Cargo el menú lateral de administración. |
| `<div class="page-content" id="usuariosContainer" data-base-url="{{ url('/admin/usuarios') }}">` | Contenedor principal donde guardo en `data-base-url` la ruta base de usuarios para que el script JavaScript sepa a dónde enviar las peticiones de edición o eliminación. |
| `@if (session('success')) ... @endif` | Muestro un recuadro verde con un chulito cuando un usuario se crea, edita o elimina con éxito. |
| `@if (session('error')) ... @endif` | Muestro un recuadro rojo si ocurre algún fallo en el servidor. |
| `@if (isset($errors) && $errors->any()) ... @endif` | Muestro la lista de campos faltantes o errores de validación si algo no cumple con los requisitos del formulario. |

---

### 2. Tarjetas de estadísticas de usuarios

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="usuario-card"> ... {{ $usuariosTotales ?? 0 }} ... </div>` | Tarjeta morada que muestra la suma total de usuarios registrados en el sistema. |
| `<div class="usuario-card"> ... {{ $usuariosActivos ?? 0 }} ... </div>` | Tarjeta verde que muestra cuántos usuarios tienen su cuenta habilitada y activa. |
| `<div class="usuario-card"> ... {{ $usuariosInactivos ?? 0 }} ... </div>` | Tarjeta roja que muestra los usuarios que han sido dados de baja o desactivados. |
| `<div class="usuario-card"> ... {{ $administradores ?? 0 }} ... </div>` | Tarjeta azul con ícono de escudo que muestra cuántos administradores existen en la plataforma. |
| `<div class="usuario-card"> ... <strong class="ultimo-acceso">Hoy</strong> ... </div>` | Tarjeta morada que indica el monitoreo del sistema en tiempo real. |

---

### 3. Filtro por rol, botón nuevo y buscador

| Código | ¿Qué hace? |
| :--- | :--- |
| `<select id="filtroRol"> ... </select>` | Menú desplegable que lista dinámicamente los roles existentes (Administrador, Trabajador, Cliente) para filtrar la tabla al instante. |
| `<button type="button" class="btn-nuevo-usuario" id="abrirNuevoUsuario">` | Botón morado con ícono `+` para desplegar la ventana modal de crear usuario. |
| `<input type="text" id="buscarUsuario" placeholder="Buscar por nombre, correo o rol...">` | Campo de búsqueda en vivo que va filtrando la tabla a medida que escribo cualquier palabra. |

---

### 4. Tabla de usuarios y acciones

| Código | ¿Qué hace? |
| :--- | :--- |
| `<table id="tablaUsuarios" class="usuarios-table">` | Tabla principal con columnas para Usuario, Documento, Teléfono, Correo, Rol, Estado, Fecha Registro y Acciones. |
| `@forelse ($usuarios ?? [] as $usuario) ... @empty ... @endforelse` | Recorro cada usuario de la base de datos; si la tabla está vacía, muestro un mensaje avisando que no hay registros. |
| `<div class="usuario-avatar">{{ $inicial }}</div> <span>{{ $nombreCompleto }}</span>` | Muestro la inicial de su nombre en un círculo morado y su nombre completo con apellido. |
| `<td>{{ $usuario->documento ?? 'N/A' }}</td>` | Muestro el número de identificación o cédula del usuario. |
| `<td>{{ $usuario->telefono ?? 'N/A' }}</td>` | Muestro el teléfono de contacto. |
| `<td>{{ $usuario->email ?? '' }}</td>` | Muestro su correo electrónico. |
| `<span class="rol-badge rol-{{ $rolClase }}">{{ ucfirst($rolNombre) }}</span>` | Pastilla de color para identificar visualmente si es Administrador, Trabajador o Cliente. |
| `<span class="estado-badge estado-{{ $estadoClase }}">{{ ucfirst($estado) }}</span>` | Etiqueta en verde si está "Activo" o gris/rojo si está "Inactivo". |
| `{{ $usuario->created_at ? $usuario->created_at->format('d/m/Y, h:i a') : 'Sin fecha' }}` | Fecha y hora en que se creó la cuenta en formato legible. |
| `<button class="btn-accion btn-editar-usuario" data-id="..." data-nombre="..." ...>` | Botón con ícono de lápiz que almacena todos los datos del usuario en atributos `data-*` para rellenar automáticamente la ventana de edición. |
| `<button class="btn-accion btn-eliminar-usuario" data-id="..." data-nombre="...">` | Botón con ícono de basurero para solicitar la eliminación segura del usuario mediante una confirmación con SweetAlert2. |

---

### 5. Panel lateral de desglose de roles

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="roles-permisos-card"> ... </div>` | Tarjeta lateral que resume cuántos usuarios pertenecen a cada uno de los 3 perfiles principales. |
| `<div class="rol-permiso"> ... <strong>Administrador</strong> ... <b>{{ $administradores ?? 0 }}</b> </div>` | Muestra el total de usuarios con acceso completo a todo el sistema. |
| `<div class="rol-permiso"> ... <strong>Trabajador</strong> ... <b>{{ $trabajadores ?? 0 }}</b> </div>` | Muestra la cantidad de empleadas que atienden citas y prestan servicios. |
| `<div class="rol-permiso"> ... <strong>Cliente</strong> ... <b>{{ $clientes ?? 0 }}</b> </div>` | Muestra la cantidad de clientas que reservan citas en la aplicación. |

---

### 6. Modal para crear un nuevo usuario

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="modal-usuario-overlay" id="modalCrearUsuario" ...>` | Ventana emergente con fondo oscuro para el registro manual de usuarios por parte de la administradora. |
| `<form action="{{ route('usuarios.store') }}" method="POST" id="formCrearUsuario"> @csrf` | Formulario protegido con token CSRF que envía los datos por el método POST a la ruta de almacenamiento de usuarios. |
| `<input type="text" name="name" placeholder="Ej. Ana" required>` | Campo obligatorio para el nombre del nuevo usuario. |
| `<input type="text" name="apellido" ... required>` | Campo obligatorio para el apellido. |
| `<input type="text" name="documento" ... required>` | Campo obligatorio para la cédula o documento de identidad. |
| `<input type="text" name="telefono" ... required>` | Campo para el número telefónico de contacto. |
| `<input type="email" name="email" ... required>` | Campo obligatorio para su correo electrónico. |
| `<select name="role_id" required> ... </select>` | Menú desplegable para asignarle su rol (Administrador, Trabajador o Cliente). |
| `<input type="password" name="password" id="passwordUsuario" minlength="6" required>` | Campo para asignarle una contraseña inicial de mínimo 6 letras o números. |
| `<i class="fa-solid fa-eye" id="mostrarPassword"></i>` | Botón con forma de ojito para ver o esconder la contraseña mientras se escribe. |
| `<button type="submit" class="btn-crear-usuario"> Crear Usuario </button>` | Botón final para guardar el nuevo usuario en el sistema. |

---

### 7. Modal para editar usuario existente y formulario de eliminación

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="modal-usuario-overlay" id="modalEditarUsuario" ...>` | Ventana emergente para modificar los datos de un usuario ya existente. |
| `<form id="formEditarUsuario" method="POST" action=""> @csrf @method('PUT')` | Formulario configurado con `@method('PUT')` para actualizar la información en Laravel. |
| `<input type="text" name="name" id="edit_nombre" required> ...` | Campos donde JavaScript inyecta automáticamente el nombre, apellido, documento, teléfono y correo actuales. |
| `<select name="estado" id="edit_estado" required> <option value="activo">Activo</option> <option value="inactivo">Inactivo</option> </select>` | Menú para activar o suspender el acceso de este usuario a la plataforma. |
| `<input type="password" name="password" id="edit_password" placeholder="Nueva contraseña (opcional)">` | Campo opcional: si se deja en blanco se conserva la contraseña anterior; si se escribe una nueva, se actualiza. |
| `<form id="formEliminarUsuario" method="POST" style="display: none;"> @csrf @method('DELETE') </form>` | Formulario invisible con método `DELETE` que se dispara automáticamente cuando la administradora confirma la eliminación en la alerta. |
| `@vite(['resources/js/modules/admin/gusuarios.js'])` | Cargo mediante Vite el módulo JavaScript que da vida a los modales, filtros y confirmaciones de esta vista. |
