<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LashApp | Sistema de Gestión</title>

    <!-- Iconos -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Poppins:wght@400;500;600&display=swap"
          rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/footer.css'])
</head>

<body>

<header class="navbar">

    <div class="logo">
        <div class="logo-icon">
         <img src="{{ asset('img/logo.png') }}" alt="Logo OH LA LASH">
        </div>

        <div>
            <h2>LashApp</h2>
            <span> Gestión de citas para Salones de Belleza</span>
        </div>
    </div>

    <nav>
        <a href="#" class="activo">Inicio</a>
        <a href="#modulos">Módulos</a>
        <a href="#contacto">Contacto</a>
        <a href="#caracteristicas">Características</a>
       
    </nav>

    <div class="acciones">

        <div class="conexion">
            <span></span>
            Conectado a MySQL 8.0
        </div>

       

    </div>

</header>


<main>

<!-- HERO -->

<section class="hero">

    <div class="decoracion decoracion1">✦</div>
    <div class="decoracion decoracion2">✧</div>

    <div class="hero-text">

        <div class="etiqueta">
            ✦ Sistema Inteligente
        </div>

        <h1>
            Gestiona tu salón<br>
            de belleza con
            <strong>LashApp</strong>
        </h1>

        <p>
            Organiza tus citas, clientes, servicios y más.
            <br>
            Todo en un solo lugar, fácil, rápido y seguro.
        </p>

        <div class="botones">
            @if (Route::has('login'))
            <a href="{{ route('login') }}" class="btn-principal">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
                Iniciar Sesión
            </a>
            @endif
            @if (Route::has('register'))
            <a href="{{ route('register') }}" class="btn-secundario">
                <i class="fa-solid fa-user-plus"></i>
                Quiero registrarme
            </a>
            @endif
        </div>

    </div>


    <!-- TARJETA -->

    <div class="tarjeta">

        <h2>Todo lo que necesitas en un solo sistema</h2>

        <div class="linea"></div>

        <div class="funciones">

            <div class="funcion">
                <div class="icono">
                    <i class="fa-solid fa-calendar-days"></i>
                </div>

                <div>
                    <h3>Gestión de Citas</h3>
                    <p>
                        Agenda, reprograma y administra
                        tus citas fácilmente.
                    </p>
                </div>
            </div>


            <div class="funcion">
                <div class="icono">
                    <i class="fa-solid fa-chart-column"></i>
                </div>

                <div>
                    <h3>Reportes</h3>
                    <p>
                        Visualiza tus ventas e informes
                        en tiempo real.
                    </p>
                </div>
            </div>


            <div class="funcion">
                <div class="icono">
                    <i class="fa-solid fa-users"></i>
                </div>

                <div>
                    <h3>Clientes</h3>
                    <p>
                        Lleva el control de tus clientes
                        y su historial.
                    </p>
                </div>
            </div>


            <div class="funcion">
                <div class="icono">
                    <i class="fa-regular fa-star"></i>
                </div>

                <div>
                    <h3>Reseñas</h3>
                    <p>
                        Gestiona las reseñas y opiniones
                        de tus clientes.
                    </p>
                </div>
            </div>


            <div class="funcion">
                <div class="icono">
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                </div>

                <div>
                    <h3>Servicios</h3>
                    <p>
                        Administra tus servicios,
                        precios y promociones.
                    </p>
                </div>
            </div>


            <div class="funcion">
                <div class="icono">
                    <i class="fa-solid fa-gear"></i>
                </div>

                <div>
                    <h3>Configuración</h3>
                    <p>
                        Personaliza tu sistema de
                        acuerdo a tu negocio.
                    </p>
                </div>
            </div>

        </div>

    </div>

</section>


<!-- INDICADORES -->

<section class="indicadores" id="contacto">

    <div class="indicador">

        <div class="indicador-icono">
            <i class="fa-brands fa-facebook"></i>
        </div>

        <div>
            <strong>Facebook</strong>
            <span>Oh La Lash</span>
        </div>

    </div>


    <div class="indicador">

        <div class="indicador-icono">
            <i class="fa-brands fa-instagram"></i>
        </div>

        <div>
            <strong>Instagram</strong>
            <span>OhLaLash Neiva</span>
        </div>

    </div>


    <div class="indicador">

        <div class="indicador-icono">
            <i class="fa-brands fa-tiktok"></i>
        </div>

        <div>
            <strong>Tiktok</strong>
            <span>ohlalashneiva0</span>
        </div>

    </div>


    <div class="indicador">

        <div class="indicador-icono">
            <i class="fa-brands fa-whatsapp"></i>
        </div>

        <div>
            <strong>WhatsApp</strong>
            <span>Tel: 3147662316</span>
        </div>

    </div>

</section>


<!-- MÓDULOS -->

<section class="seccion-modulos" id="modulos">

    <h2>Módulos del sistema</h2>

    <div class="subrayado"></div>

    <div class="modulos">

        <div class="modulo">
            <div class="modulo-icono">
                <i class="fa-solid fa-calendar-days"></i>
            </div>
            <span>Citas</span>
        </div>


        <div class="modulo">
            <div class="modulo-icono">
                <i class="fa-solid fa-users"></i>
            </div>
            <span>Clientes</span>
        </div>


        <div class="modulo">
            <div class="modulo-icono">
                <i class="fa-solid fa-eye"></i>
            </div>
            <span>Servicios</span>
        </div>


        <div class="modulo">
            <div class="modulo-icono">
                <i class="fa-solid fa-chart-column"></i>
            </div>
            <span>Reportes</span>
        </div>


        <div class="modulo">
            <div class="modulo-icono">
                <i class="fa-regular fa-star"></i>
            </div>
            <span>Reseñas</span>
        </div>


        <div class="modulo">
            <div class="modulo-icono">
                <i class="fa-solid fa-user-group"></i>
            </div>
            <span>Usuarios</span>
        </div>


        <div class="modulo">
            <div class="modulo-icono">
                <i class="fa-solid fa-gear"></i>
            </div>
            <span>Configuración</span>
        </div>

    </div>

</section>

</main>

@include('layouts.footer')

</body>
</html>