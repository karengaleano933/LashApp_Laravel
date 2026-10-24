<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Mi Perfil'])
@include('layouts.sidebarcliente')

@vite([
    'resources/css/perfilcliente.css',
    'resources/js/modules/clientes/perfilcliente.js'
])

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

    // Servicio para próxima cita
    $proximaImg = asset('img/volumen_ruso.jpg');
    if ($proximaCita && $proximaCita->servicio) {
        if (!empty($proximaCita->servicio->imagen_url)) {
            $proximaImg = filter_var($proximaCita->servicio->imagen_url, FILTER_VALIDATE_URL) 
                ? $proximaCita->servicio->imagen_url 
                : asset($proximaCita->servicio->imagen_url);
        } else {
            $sNom = strtolower($proximaCita->servicio->nombre ?? '');
            if (str_contains($sNom, 'ceja') || str_contains($sNom, 'henna')) {
                $proximaImg = asset('img/diseno_cejas.jpg');
            } elseif (str_contains($sNom, 'clásica') || str_contains($sNom, 'clasica')) {
                $proximaImg = asset('img/extensiones_clasicas.jpg');
            }
        }
    }
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

<div class="perfil-content-area" style="padding: 24px 30px;">

    <!-- ══════════════════════════════════════════
         TOP HEADER DE PERFIL
    ══════════════════════════════════════════ -->
    <div class="perfil-header">
        <div class="perfil-header-left">
            <div class="perfil-header-icon">
                <i class="fa-regular fa-user"></i>
            </div>
            <div>
                <h1>Mi Perfil</h1>
                <p>Gestiona tu información personal, contraseña y preferencias</p>
            </div>
        </div>
    </div>

    <!-- MENSAJES DE ESTADO Y VALIDACIÓN -->
    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 14px; font-size: 13px; font-weight: 700; background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 14px; font-size: 13px; font-weight: 700; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 16px;"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div style="margin-bottom: 20px; padding: 14px 18px; border-radius: 14px; font-size: 13px; font-weight: 700; background: #fee2e2; color: #991b1b; border: 1px solid #fecaca;">
            <div style="margin-bottom: 6px;"><i class="fa-solid fa-triangle-exclamation"></i> Por favor revisa los siguientes errores:</div>
            <ul style="margin: 0; padding-left: 20px; font-weight: normal; font-size: 12px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ══════════════════════════════════════════
         BARRA DE PESTAÑAS DE NAVEGACIÓN
    ══════════════════════════════════════════ -->
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

    <!-- ══════════════════════════════════════════
         GRID DE CONTENIDO (2 COLUMNAS)
    ══════════════════════════════════════════ -->
    <div class="perfil-main-grid">

        <!-- COLUMNA IZQUIERDA: INFORMACIÓN PERSONAL, PREFERENCIAS, SEGURIDAD -->
        <div>
            <!-- CARD 1: INFORMACIÓN PERSONAL -->
            <div class="perfil-card" id="sec-info">
                <div class="perfil-card-title">
                    <i class="fa-regular fa-user"></i> Información personal
                </div>

                <form method="POST" action="{{ route('cliente.perfil.update') }}">
                    @csrf
                    <div class="info-personal-wrap">
                        <!-- Foto de Perfil Izquierda -->
                        <div class="avatar-section">
                            <div class="avatar-img-wrap">
                                @if($fotoSrc)
                                    <img src="{{ $fotoSrc }}" alt="Foto Perfil" class="avatar-img" id="avatarImg">
                                @else
                                    <div class="avatar-initials-fallback" id="avatarImg">
                                        {{ $initials }}
                                    </div>
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

                        <!-- Campos del Formulario -->
                        <div class="info-form-grid">
                            <div class="form-group-item">
                                <label for="inputNombre">Nombres</label>
                                <input type="text" id="inputNombre" name="nombre" class="form-control-input" value="{{ old('nombre', $user->name) }}" required>
                            </div>

                            <div class="form-group-item">
                                <label for="inputApellido">Apellidos</label>
                                <input type="text" id="inputApellido" name="apellido" class="form-control-input" value="{{ old('apellido', $user->apellido) }}" required>
                            </div>

                            <div class="form-group-item">
                                <label>Correo electrónico</label>
                                <input type="email" class="form-control-input" value="{{ $user->email }}" readonly style="background:#faf7fd; cursor:not-allowed;" title="El correo electrónico está vinculado a tu cuenta">
                            </div>

                            <div class="form-group-item">
                                <label for="inputTelefono">Teléfono</label>
                                <input type="text" id="inputTelefono" name="telefono" class="form-control-input" value="{{ old('telefono', $user->telefono) }}" placeholder="Ej: 314 123 4567">
                            </div>

                            <div class="form-group-item">
                                <label for="inputFechaNac">Fecha de nacimiento</label>
                                <input type="date" id="inputFechaNac" name="fecha_nacimiento" class="form-control-input" value="{{ old('fecha_nacimiento', $cliente?->fecha_nacimiento) }}">
                            </div>

                            <div class="form-group-item">
                                <label for="inputDocumento">Documento de identidad</label>
                                <input type="text" id="inputDocumento" name="documento" class="form-control-input" value="{{ old('documento', $user->documento) }}" placeholder="Ej: 1085234567">
                            </div>

                            <div class="form-group-item full-width">
                                <label for="inputDireccion">Dirección</label>
                                <input type="text" id="inputDireccion" name="direccion" class="form-control-input" value="{{ old('direccion', $cliente?->direccion) }}" placeholder="Ej: Cra 15 # 24-50, Neiva">
                            </div>

                            <button type="submit" class="btn-edit-info">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- SUB GRID: PREFERENCIAS & SEGURIDAD -->
            <div class="sub-sections-grid">
                <!-- PREFERENCIAS -->
                <div class="perfil-card" id="sec-preferencias">
                    <div class="perfil-card-title">
                        <i class="fa-regular fa-heart"></i> Preferencias
                    </div>

                    <div class="pref-list">
                        <div class="pref-item">
                            <div class="pref-item-left">
                                <div class="pref-icon"><i class="fa-solid fa-eye"></i></div>
                                <div>
                                    <div class="pref-title">Servicios favoritos</div>
                                    <div class="pref-sub">Extensiones de pestañas, Diseño de cejas</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right" style="color:#dcc8ee; font-size:12px;"></i>
                        </div>

                        <div class="pref-item">
                            <div class="pref-item-left">
                                <div class="pref-icon"><i class="fa-solid fa-user-nurse"></i></div>
                                <div>
                                    <div class="pref-title">Atención personalizada</div>
                                    <div class="pref-sub">Especialistas certificadas en estudio</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right" style="color:#dcc8ee; font-size:12px;"></i>
                        </div>

                        <div class="pref-item">
                            <div class="pref-item-left">
                                <div class="pref-icon"><i class="fa-regular fa-bell"></i></div>
                                <div>
                                    <div class="pref-title">Recordatorios de citas</div>
                                    <div class="pref-sub">Notificaciones activadas en plataforma</div>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right" style="color:#dcc8ee; font-size:12px;"></i>
                        </div>
                    </div>

                    <button type="button" class="btn-card-bottom" onclick="Swal.fire({title:'Preferencias guardadas', text:'Tus preferencias de atención y recordatorios están activas.', icon:'success', confirmButtonColor:'#7c1db8'})">
                        <i class="fa-solid fa-gear"></i> Gestionar preferencias
                    </button>
                </div>

                <!-- SEGURIDAD -->
                <div class="perfil-card" id="sec-seguridad">
                    <div class="perfil-card-title">
                        <i class="fa-solid fa-shield-halved"></i> Seguridad
                    </div>

                    <div class="seg-list">
                        <div class="seg-item">
                            <div class="seg-item-left">
                                <div class="seg-icon"><i class="fa-solid fa-lock"></i></div>
                                <div>
                                    <div class="seg-title">Contraseña</div>
                                    <div class="seg-sub">••••••••</div>
                                </div>
                            </div>
                            <button type="button" class="btn-seg-action" onclick="togglePasswordForm()">Cambiar</button>
                        </div>

                        <div class="seg-item">
                            <div class="seg-item-left">
                                <div class="seg-icon"><i class="fa-solid fa-shield-check"></i></div>
                                <div>
                                    <div class="seg-title">Estado de la cuenta</div>
                                    <div class="seg-sub"><span class="badge-activada">Activa y Segura</span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario desplegable para cambiar contraseña -->
                    <div id="formPasswordWrap" style="display:none; margin-top:16px; padding:18px; background:#fbf8fd; border-radius:16px; border:1px solid #dcc8ee;">
                        <h4 style="font-family:'Playfair Display',Georgia,serif; color:#2a0d3e; font-size:15px; margin-bottom:12px;">
                            <i class="fa-solid fa-key" style="color:#7c1db8;"></i> Cambiar Contraseña
                        </h4>
                        <form method="POST" action="{{ route('cliente.perfil.password') }}">
                            @csrf
                            <div style="display:grid; gap:12px;">
                                <div>
                                    <label style="font-size:12px; font-weight:700; color:#2a0d3e; display:block; margin-bottom:4px;">Contraseña actual</label>
                                    <input type="password" name="contrasena_actual" class="form-control-input" required placeholder="Tu contraseña actual">
                                </div>
                                <div>
                                    <label style="font-size:12px; font-weight:700; color:#2a0d3e; display:block; margin-bottom:4px;">Nueva contraseña</label>
                                    <input type="password" name="contrasena_nueva" class="form-control-input" minlength="6" required placeholder="Mínimo 6 caracteres">
                                </div>
                                <div>
                                    <label style="font-size:12px; font-weight:700; color:#2a0d3e; display:block; margin-bottom:4px;">Confirmar nueva contraseña</label>
                                    <input type="password" name="contrasena_confirmar" class="form-control-input" minlength="6" required placeholder="Repite la nueva contraseña">
                                </div>
                                <div style="display:flex; gap:10px; margin-top:6px;">
                                    <button type="submit" class="btn-edit-info" style="flex:1; justify-content:center;">
                                        <i class="fa-solid fa-check"></i> Actualizar contraseña
                                    </button>
                                    <button type="button" onclick="togglePasswordForm()" style="padding:10px 16px; border-radius:14px; border:1px solid #dcc8ee; background:#fff; color:#7a5a90; font-weight:700; cursor:pointer;">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <button type="button" class="btn-card-bottom" onclick="togglePasswordForm()">
                        <i class="fa-solid fa-lock"></i> Actualizar contraseña
                    </button>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: SIDEBAR DE ACTIVIDAD Y ACCIONES DE CUENTA -->
        <div class="perfil-sidebar">

            <!-- WIDGET 1: RESUMEN DE TU ACTIVIDAD -->
            <div class="perfil-card">
                <div class="perfil-card-title">
                    <i class="fa-solid fa-chart-column"></i> Resumen de tu actividad
                </div>

                <div class="actividad-stats-grid">
                    <div class="act-stat-box">
                        <div class="act-stat-icon purple"><i class="fa-regular fa-calendar-check"></i></div>
                        <div>
                            <div class="act-stat-num">{{ $totalCitas ?? 0 }}</div>
                            <div class="act-stat-label">Citas agendadas</div>
                        </div>
                    </div>

                    <div class="act-stat-box">
                        <div class="act-stat-icon green"><i class="fa-regular fa-circle-check"></i></div>
                        <div>
                            <div class="act-stat-num">{{ $citasRealizadas ?? 0 }}</div>
                            <div class="act-stat-label">Servicios realizados</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WIDGET 2: PRÓXIMA CITA -->
            <div class="perfil-card">
                <div class="perfil-card-title">
                    <i class="fa-regular fa-calendar-days"></i> Próxima cita
                </div>

                @if($proximaCita)
                    @php
                        $fechaProxCita = $proximaCita->fecha ? Carbon::parse($proximaCita->fecha)->format('d/m/Y') : 'Próximamente';
                        $horaProxCita = $proximaCita->hora_inicio ? Carbon::parse($proximaCita->hora_inicio)->format('h:i A') : '--:--';
                    @endphp
                    <div class="proxima-widget-box">
                        <img src="{{ $proximaImg }}" class="proxima-widget-img" alt="Servicio">
                        <div>
                            <div class="proxima-widget-title">{{ $proximaCita->servicio?->nombre ?? 'Servicio Agendado' }}</div>
                            <div class="proxima-widget-meta">
                                <span><i class="fa-regular fa-calendar"></i> {{ $fechaProxCita }}</span>
                                <span><i class="fa-regular fa-clock"></i> {{ $horaProxCita }}</span>
                                <span><i class="fa-solid fa-location-dot"></i> Oh La Lash Studio</span>
                            </div>
                        </div>
                    </div>
                @else
                    <div style="text-align:center; padding:18px 12px; color:#7a5a90; font-size:13px;">
                        <i class="fa-regular fa-calendar-xmark" style="font-size:24px; color:#dcc8ee; display:block; margin-bottom:8px;"></i>
                        No tienes citas próximas agendadas.
                    </div>
                @endif

                <a href="{{ route('cliente.citas') }}" class="btn-card-bottom" style="text-decoration:none;">
                    <i class="fa-solid fa-calendar-check"></i> Ver mis citas
                </a>
            </div>

            <!-- WIDGET 3: ACCIONES DE CUENTA -->
            <div class="perfil-card" id="sec-acciones">
                <div class="perfil-card-title">
                    <i class="fa-solid fa-gears"></i> Acciones de cuenta
                </div>

                <div class="acciones-cuenta-list">
                    <a href="{{ route('cliente.perfil.exportar') }}" class="cuenta-action-item" title="Descargar copia de seguridad en JSON">
                        <div class="cuenta-action-left">
                            <div class="cuenta-action-icon"><i class="fa-solid fa-download"></i></div>
                            <div>
                                <div class="cuenta-action-title">Descargar mis datos</div>
                                <div class="cuenta-action-sub">Obtén una copia completa de tu información</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:#dcc8ee; font-size:12px;"></i>
                    </a>

                    <a href="javascript:void(0);" class="cuenta-action-item" onclick="document.getElementById('sidebarLogoutForm')?.submit()">
                        <div class="cuenta-action-left">
                            <div class="cuenta-action-icon"><i class="fa-solid fa-arrow-right-from-bracket"></i></div>
                            <div>
                                <div class="cuenta-action-title">Cerrar sesión</div>
                                <div class="cuenta-action-sub">Salir de tu cuenta de forma segura</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:#dcc8ee; font-size:12px;"></i>
                    </a>

                    <a href="javascript:void(0);" class="cuenta-action-item danger" onclick="confirmarEliminarCuenta();">
                        <div class="cuenta-action-left">
                            <div class="cuenta-action-icon"><i class="fa-solid fa-trash-can"></i></div>
                            <div>
                                <div class="cuenta-action-title">Desactivar cuenta</div>
                                <div class="cuenta-action-sub">Desactivar mi cuenta en el sistema</div>
                            </div>
                        </div>
                        <i class="fa-solid fa-chevron-right" style="color:#fca5a5; font-size:12px;"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- BANNER INFERIOR DE AYUDA -->
    <div class="bottom-help-bar">
        <div>
            <i class="fa-solid fa-circle-info"></i> ¿Necesitas ayuda o soporte con tu perfil? Estamos aquí para ayudarte.
        </div>
        <a href="https://wa.me/573147662316" target="_blank" class="btn-footer-contact">
            <i class="fa-solid fa-comment-dots"></i> Contáctanos
        </a>
    </div>

</div>

<!-- Scripts interactivos extraídos al módulo Vite: resources/js/modules/clientes/perfilcliente.js -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper (abierto en headercliente.blade.php) -->
</div> <!-- Cierre de .app-layout (abierto en headercliente.blade.php) -->

</body>
</html>
