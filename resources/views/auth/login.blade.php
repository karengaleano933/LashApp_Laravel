<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>LashApp | Iniciar Sesión</title>

    <!-- CSS DEL LOGIN -->
    

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Fuentes -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600;1,700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
   @vite(['resources/css/login.css'])
</head>


<body class="login-page">

    <!-- Decoraciones -->
    <div class="decoracion-fondo estrella1">✦</div>
    <div class="decoracion-fondo estrella2">✧</div>
    <div class="decoracion-fondo estrella3">✦</div>


    <!-- CONTENEDOR PRINCIPAL -->

    <main class="login-container">


        <!-- =========================
             PANEL IZQUIERDO
        ========================== -->

        <section class="panel-izquierdo">

            <div class="contenido-izquierdo">

                <div class="logo">

                    <img src="{{ asset('img/logo.png') }}" alt="Logo LashApp">

                </div>


                <div class="linea-dorada"></div>


                <h2>
                    Tu belleza,
                    <span>nuestra pasión.</span>
                </h2>


                <p>
                    Gestiona tu salón de belleza
                    de manera fácil, rápida y elegante.
                </p>


                <div class="icono-pestanas">

                    <i class="fa-solid fa-eye"></i>

                </div>

            </div>

        </section>



        <!-- =========================
             PANEL DERECHO
        ========================== -->

        <section class="panel-derecho">

            <div class="login-box">


                <!-- ENCABEZADO -->

                <div class="encabezado-login">

                    <span class="etiqueta-login">
                        ✦ Bienvenido
                    </span>

                    <h1>
                        Inicia sesión
                    </h1>

                    <p>
                        Ingresa a tu cuenta para continuar
                    </p>

                </div>


                <!-- DECORACIÓN -->

                <div class="decoracion-login">

                    <span></span>

                    <i class="fa-solid fa-crown"></i>

                    <span></span>

                </div>



                <!-- =========================
                     FORMULARIO
                ========================== -->
                @if (session('success'))
                    <div style="background-color: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 15px; border: 1px solid #c3e6cb;">
                        {{ session('success') }}
                    </div>
                @endif

                 @if ($errors->any())
                    <div style="background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                 @endif

                <form action="{{ route('login') }}" method="POST" id="formLogin">
                    @csrf

                    <!-- CORREO -->

                    <div class="campo">

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

                    </div>



                    <!-- CONTRASEÑA -->

                    <div class="campo">

                        <label for="password">
                            Contraseña
                        </label>

                        <div class="input-group">

                            <i class="fa-solid fa-lock"></i>

                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="••••••••"
                                required>

                            <i
                                class="fa-solid fa-eye ojo"
                                onclick="mostrarPassword()">
                            </i>

                        </div>

                    </div>



                    <!-- OPCIONES -->

                    <div class="opciones">

                        <label class="recordar">

                            <input type="checkbox" name="remember" id="remember_me">

                            <span>
                                Recordarme
                            </span>

                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>



                    <!-- BOTÓN -->

                    <button
                        type="submit"
                        class="btn-principal">

                        <i class="fa-solid fa-right-to-bracket"></i>

                        Iniciar Sesión

                    </button>



                    <!-- REGISTRO -->
                   @if (Route::has('register'))
                    <button
                        type="button"
                        class="btn-secundario"
                        onclick="window.location.href='{{ route('register') }}'">

                        <i class="fa-solid fa-user-plus"></i>

                        Crear cuenta nueva

                    </button>
                @endif
                </form>



                <!-- SEPARADOR -->

                <div class="separador">

                    <span></span>

                    <p>
                        O continúa con
                    </p>

                    <span></span>

                </div>



                <!-- REDES -->

                <div class="social">

                    <a href="#" title="Google">

                        <i class="fa-brands fa-google"></i>

                    </a>

                    <a href="#" title="Facebook">

                        <i class="fa-brands fa-facebook-f"></i>

                    </a>

                    <a href="#" title="Apple">

                        <i class="fa-brands fa-apple"></i>

                    </a>

                </div>



                <!-- VOLVER -->
                @if (Route::has('welcome'))
                <div class="volver">

                    <a href="{{ route('welcome') }}">

                        <i class="fa-solid fa-arrow-left"></i>

                        Volver al inicio

                    </a>

                </div>
                @endif


            </div>

        </section>

    </main>



    <!-- =========================
         JAVASCRIPT
    ========================== -->

    <script>

        function mostrarPassword() {

            const password =
                document.getElementById("password");

            const ojo =
                document.querySelector(".ojo");


            if (password.type === "password") {

                password.type = "text";

                ojo.classList.remove("fa-eye");

                ojo.classList.add("fa-eye-slash");

            } else {

                password.type = "password";

                ojo.classList.remove("fa-eye-slash");

                ojo.classList.add("fa-eye");

            }

        }

    </script>

</body>

</html>