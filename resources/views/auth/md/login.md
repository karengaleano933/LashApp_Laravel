# Explicación de mi Vista de Inicio de Sesión (`auth/login.blade.php`)

Esta vista es la puerta de entrada a LashApp. Es una pantalla moderna dividida en dos paneles: a la izquierda muestra la marca, el logo y el eslogan del salón de belleza con decoraciones brillantes, y a la derecha ofrece el formulario para que administradoras, empleadas y clientas inicien sesión con su correo y contraseña, con opción de recordar credenciales, alternar visibilidad de contraseña y botones de registro o redes sociales.

---

### 1. Estructura del documento y recursos externos

| Código | ¿Qué hace? |
| :--- | :--- |
| `<title>LashApp \| Iniciar Sesión</title>` | Asigna el nombre que aparece en la pestaña del navegador. |
| `<link rel="stylesheet" href="...font-awesome...">` | Carga la librería de íconos FontAwesome (candado, correo, ojo, corona, etc.). |
| `<link href="...Playfair+Display...Poppins..." rel="stylesheet">` | Carga las tipografías elegantes de Google Fonts para los títulos y textos. |
| `@vite(['resources/css/login.css', 'resources/js/modules/auth/login.js'])` | Compila y carga la hoja de estilos del login y el archivo JavaScript modular mediante Vite. |
| `<div class="decoracion-fondo estrella1">✦</div>` | Elementos decorativos de estrellas que flotan en el fondo de la pantalla. |

---

### 2. Panel izquierdo de bienvenida e identidad de marca

| Código | ¿Qué hace? |
| :--- | :--- |
| `<img src="{{ asset('img/logo.png') }}" alt="Logo LashApp">` | Muestra el logotipo oficial del estudio de belleza. |
| `<div class="linea-dorada"></div>` | Dibuja una línea dorada divisoria decorativa. |
| `<h2> Tu belleza, <span>nuestra pasión.</span> </h2>` | Eslogan principal del negocio. |
| `<p> Gestiona tu salón de belleza de manera fácil, rápida y elegante. </p>` | Mensaje explicativo sobre el propósito de la plataforma. |
| `<i class="fa-solid fa-eye"></i>` | Ícono de ojo alusivo a la belleza de pestañas y miradas. |

---

### 3. Encabezado del formulario de ingreso y alertas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<span class="etiqueta-login"> ✦ Bienvenido </span>` | Insignia superior que da la bienvenida a la usuaria. |
| `<h1> Inicia sesión </h1>` | Título principal de la tarjeta de acceso. |
| `@if (session('success')) ... @endif` | Cuadro verde que aparece si la usuaria acaba de registrarse o recuperar su clave exitosamente. |
| `@if ($errors->any()) ... @endif` | Cuadro rojo que muestra los errores si el correo o la contraseña no coinciden con la base de datos. |

---

### 4. Formulario de inicio de sesión

| Código | ¿Qué hace? |
| :--- | :--- |
| `<form action="{{ route('login') }}" method="POST" id="formLogin"> @csrf` | Formulario protegido con token `@csrf` que envía las credenciales por POST a Laravel. |
| `<input type="email" id="correo" name="email" value="{{ old('email') }}" placeholder="Ejemplo@gmail.com" required>` | Campo para el correo electrónico que recuerda lo escrito (`old('email')`) en caso de error. |
| `<input type="password" id="password" name="password" placeholder="••••••••" required>` | Campo seguro para escribir la contraseña. |
| `<i class="fa-solid fa-eye ojo" onclick="mostrarPassword()"></i>` | Ícono interactivo de ojito que permite ver o tapar la contraseña al hacer clic. |
| `<input type="checkbox" name="remember" id="remember_me"> <span>Recordarme</span>` | Casilla de verificación para que el navegador mantenga la sesión abierta por varios días. |
| `<a href="{{ route('password.request') }}"> ¿Olvidaste tu contraseña? </a>` | Enlace directo para restablecer la contraseña en caso de olvido. |
| `<button type="submit" class="btn-principal"> <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión </button>` | Botón morado principal para acceder a la aplicación. |
| `<button type="button" class="btn-secundario" onclick="window.location.href='{{ route('register') }}'">` | Botón secundario para ir a la página de registro si la usuaria aún no tiene cuenta. |
| `<div class="social"> ... </div>` | Botones estilizados para iniciar sesión con redes sociales (Google, Facebook, Apple). |
| `<a href="{{ route('welcome') }}"> <i class="fa-solid fa-arrow-left"></i> Volver al inicio </a>` | Enlace en el pie de página para regresar a la página principal de LashApp. |
