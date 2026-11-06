# Explicación de mi Vista de Registro de Cuenta (`auth/register.blade.php`)

Esta vista es el formulario donde nuevas clientas pueden registrarse para comenzar a agendar citas en LashApp. Al igual que el inicio de sesión, cuenta con un diseño de doble panel: el lado izquierdo muestra la identidad visual y eslogan de la marca, mientras que el panel derecho presenta los campos organizados para capturar nombre, apellido, cédula, teléfono, correo electrónico, contraseña con botón de ojo para ver/ocultar y la casilla de aceptación de términos y condiciones.

---

### 1. Estructura y recursos externos

| Código | ¿Qué hace? |
| :--- | :--- |
| `<title>Crear Cuenta \| LashApp</title>` | Título de la página mostrado en la pestaña del navegador. |
| `<link rel="stylesheet" href="...font-awesome...">` | Carga los íconos de usuario, candado, sobre, ojo y corona. |
| `@vite(['resources/css/register.css', 'resources/js/modules/auth/register.js'])` | Compila y carga los estilos del registro y el script interactivo mediante Vite. |
| `<div class="bg-shape bg-shape-1"></div>` | Figuras decorativas flotantes que ambientan el fondo de la pantalla. |

---

### 2. Panel izquierdo de bienvenida

| Código | ¿Qué hace? |
| :--- | :--- |
| `<img src="{{ asset('img/logo.png') }}" alt="Logo LashApp">` | Imagen del logotipo oficial de LashApp. |
| `<div class="linea"></div>` | Línea divisoria decorativa con estilo sutil. |
| `<h2> Tu belleza, <span>nuestra pasión.</span> </h2>` | Eslogan comercial del salón de belleza. |
| `<i class="fa-solid fa-eye"></i>` | Ícono decorativo de pestañas y mirada. |

---

### 3. Encabezado del formulario de registro y errores

| Código | ¿Qué hace? |
| :--- | :--- |
| `<span> ✦ Crear cuenta </span>` | Insignia superior que indica que estamos en el registro de nueva cuenta. |
| `<h1> Crear cuenta </h1>` | Título principal de la tarjeta. |
| `<p class="subtitulo"> Completa tus datos para registrarte </p>` | Subtítulo explicativo para orientar a la usuaria. |
| `@if ($errors->any()) ... @endif` | Cuadro rojo que lista los errores si el correo ya está registrado, la cédula ya existe o la contraseña es muy corta. |
| `<div class="decoracion"> ... <i class="fa-solid fa-crown"></i> ... </div>` | Separador visual con una corona central. |

---

### 4. Campos del formulario de registro

| Código | ¿Qué hace? |
| :--- | :--- |
| `<form action="{{ route('register') }}" method="POST"> @csrf` | Formulario protegido con token `@csrf` que envía los datos del nuevo usuario a Laravel. |
| `<input type="text" id="nombre" name="name" value="{{ old('nombre') }}" placeholder="Nombre" required>` | Campo obligatorio para el primer nombre de la usuaria. |
| `<input type="text" id="apellido" name="apellido" value="{{ old('apellido') }}" placeholder="Apellido" required>` | Campo obligatorio para los apellidos. |
| `<input type="number" id="documento" name="documento" ... required>` | Campo numérico para la cédula o documento de identidad. |
| `<input type="number" id="telefono" name="telefono" ... required>` | Campo numérico para el celular o WhatsApp de contacto. |
| `<input type="email" id="correo" name="email" value="{{ old('email') }}" placeholder="Ejemplo@gmail.com" required>` | Campo para el correo electrónico personal de la usuaria. |
| `<input type="password" id="password" name="password" placeholder="••••••••" required>` | Campo protegido para la contraseña de la nueva cuenta. |
| `<i class="fa-solid fa-eye eye" id="togglePass"></i>` | Ícono interactivo de ojo para ver o esconder la clave mientras se teclea. |
| `<input type="checkbox" name="terminos" id="termsCheck" required>` | Casilla de verificación obligatoria para aceptar los términos y condiciones y políticas de privacidad antes de registrarse. |
| `<button type="submit" class="btn"> <i class="fa-solid fa-user-plus"></i> Crear cuenta </button>` | Botón morado principal para guardar la nueva usuaria en el sistema. |
| `<div class="login-link"> ¿Ya tienes una cuenta? <a href="{{ route('login') }}"> Inicia sesión </a> </div>` | Enlace inferior para que clientas que ya tienen cuenta puedan ir directo al login. |
