<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Crear Cuenta | LashApp</title>


    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/register.css'])
</head>

<body class="login-page-body">

    <!-- =====================================
         FIGURAS DECORATIVAS DEL FONDO
    ====================================== -->

    <div class="bg-shape bg-shape-1"></div>
    <div class="bg-shape bg-shape-2"></div>


    <!-- =====================================
         CONTENEDOR PRINCIPAL
    ====================================== -->

    <div class="container">


        <!-- =================================
             PANEL IZQUIERDO
        ================================== -->

        <div class="left-panel" id="leftPanel">

            <div class="logo">
                <img src="{{ asset('img/logo.png') }}" alt="Logo LashApp">
            </div>

            <div class="linea"></div>

            <div class="texto-presentacion">

                <h2>
                    Tu belleza,
                    <span>nuestra pasión.</span>
                </h2>

                <p>
                    Gestiona tu salón de belleza de manera fácil,
                    rápida y elegante.
                </p>

            </div>

            <div class="corona-deco">
                <i class="fa-solid fa-eye"></i>
            </div>

        </div>


        <!-- =================================
             PANEL DERECHO
        ================================== -->

        <div class="right-panel">

            <div class="login-box registro-box">

                <!-- Encabezado -->

                <div class="bienvenida">
                    <span>
                        ✦ Crear cuenta
                    </span>
                </div>

                <h1>Crear cuenta</h1>

                <p class="subtitulo">
                    Completa tus datos para registrarte
                </p>

           @if ($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

                <!-- Decoración -->

                <div class="decoracion">

                    <span></span>

                    <i class="fa-solid fa-crown"></i>

                    <span></span>

                </div>


                <!-- =================================
                     FORMULARIO
                ================================== -->

                <form action="{{ route('register') }}" method="POST">
                  @csrf

                    <!-- NOMBRES Y APELLIDOS -->

                    <div class="row">

                        <div class="col">

                            <label for="nombre">
                                Nombre
                            </label>

                            <div class="input-group">

                                <i class="fa-solid fa-user"></i>

                                <input type="text" id="nombre" name="name" value="{{ old('nombre') }}" placeholder="Nombre" required>

                            </div>

                        </div>


                        <div class="col">

                            <label for="apellido">
                                Apellido
                            </label>

                            <div class="input-group">

                                <i class="fa-solid fa-user"></i>

                                <input type="text"  id="apellido" name="apellido" value="{{ old('apellido') }}" placeholder="Apellido" required>

                            </div>

                        </div>

                    </div>


                    <!-- DOCUMENTO -->

                    <label>
                        Documento de identidad
                    </label>

                            <div class="input-group">

                                <i class="fa-solid fa-user"></i>

                                <input type="number"  id="documento" name="documento" value="{{ old('documento') }}" placeholder="Número de documento" required>

                            </div>
                        <!-- TELEFONO -->
                    <label>
                        Telefono
                    </label>

                            <div class="input-group">

                                <i class="fa-solid fa-user"></i>

                                <input type="number"  id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Número de teléfono" required>

                            </div>

            
                    <!-- CORREO -->

                    <label for="correo">
                        Correo electrónico
                    </label>

                    <div class="input-group">

                        <i class="fa-regular fa-envelope"></i>

                        <input
                            type="email"
                            id="correo"
                            name="email"
                            value="{{ old('email') }}"
                             placeholder="Ejemplo@gmail.com"
                            required>

                    </div>


                    <!-- CONTRASEÑA -->

                    <label for="password">
                        Contraseña
                    </label>

                    <div class="input-group">

                        <i class="fa-solid fa-lock"></i>

                        <input type="password" id="password" name="password"  value="{{ old('password') }}" placeholder="••••••••" required>
                        <i
                            class="fa-solid fa-eye eye"
                            id="togglePass">
                        </i>

                    </div>
                <input type="checkbox" name="terminos" value="{{ old('terminos') }}" class="form-check-input ms-1 mt-1" id="termsCheck" required>
                    <label class="form-check-label small text-muted ms-2" for="termsCheck">
                        Acepto los <a href="#" class="text-success text-decoration-none fw-semibold">Términos y Condiciones</a> y la Política de Privacidad.
                    </label>

                    <!-- BOTÓN -->

                    <button
                        type="submit"
                        class="btn">

                        <i class="fa-solid fa-user-plus"></i>

                        Crear cuenta

                    </button>


                    <!-- LINK LOGIN -->

                    <div class="login-link">

                        ¿Ya tienes una cuenta?

                        <a href="{{ route('login') }}">
                            Inicia sesión
                        </a>

                    </div>

                </form>

            </div>

        </div>

    </div>


    <!-- =====================================
         JAVASCRIPT
    ====================================== -->

    <script>

        /* =================================
           DESTELLOS DORADOS
        ================================== */

        const panel =
            document.getElementById('leftPanel');

        for (let i = 0; i < 55; i++) {

            const sparkle =
                document.createElement('div');

            sparkle.classList.add('sparkle');

            const size =
                Math.random() * 5 + 2;

            sparkle.style.width =
                size + 'px';

            sparkle.style.height =
                size + 'px';

            sparkle.style.left =
                Math.random() * 100 + '%';

            sparkle.style.top =
                Math.random() * 100 + '%';

            sparkle.style.animationDelay =
                Math.random() * 3 + 's';

            sparkle.style.animationDuration =
                Math.random() * 2 + 2 + 's';

            panel.appendChild(sparkle);

        }


        /* =================================
           MOSTRAR / OCULTAR CONTRASEÑA
        ================================== */

        const toggle =
            document.getElementById('togglePass');

        const password =
            document.getElementById('password');

        toggle.addEventListener('click', function () {

            if (password.type === 'password') {

                password.type = 'text';

                toggle.classList.remove('fa-eye');

                toggle.classList.add('fa-eye-slash');

            } else {

                password.type = 'password';

                toggle.classList.remove('fa-eye-slash');

                toggle.classList.add('fa-eye');

            }

        });

    </script>

</body>

</html>