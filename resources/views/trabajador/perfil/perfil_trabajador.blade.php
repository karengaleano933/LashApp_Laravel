@include('layouts.header_trabajador', ['titulo' => 'Mi Perfil'])
@include('layouts.sidebar_trabajador')
@vite(['resources/css/perfil_trabajador.css'])

@php
    use Carbon\Carbon;

    if (!isset($user)) {
        $user = auth()->user();
    }
    if (!isset($trabajador)) {
        $trabajador = $user?->trabajador ?? \App\Models\Trabajador::where('user_id', $user?->id)->first();
    }
    $trabajadorId = $trabajador?->id;

    if (!isset($totalCitas)) {
        $totalCitas = \App\Models\Cita::where('trabajador_id', $trabajadorId)->count();
    }
    if (!isset($citasAtendidas)) {
        $citasAtendidas = \App\Models\Cita::where('trabajador_id', $trabajadorId)
            ->whereIn('estado', ['confirmado', 'finalizado', 'completado', 'completada', 'realizada'])
            ->count();
    }
    if (!isset($totalResenas)) {
        $resenas = \App\Models\Resena::whereHas('cita', fn($q) => $q->where('trabajador_id', $trabajadorId))->get();
        $totalResenas = $resenas->count();
        $promedioCalificacion = $totalResenas > 0 ? round($resenas->avg('calificacion'), 1) : 5.0;
    }
    if (!isset($serviciosTotal)) {
        $serviciosTotal = \App\Models\Servicio::where('estado', 'activo')->count();
    }
    if (!isset($proximaCita)) {
        $proximaCita = \App\Models\Cita::with(['servicio', 'cliente.user'])
            ->where('trabajador_id', $trabajadorId)
            ->whereIn('estado', ['confirmado', 'confirmada', 'pendiente'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->first();
    }

    $fotoSrc = null;
    if (!empty($user?->foto) && file_exists(public_path($user->foto))) {
        $fotoSrc = asset($user->foto);
    } elseif (file_exists(public_path('img/perfil_trabajador.jpg'))) {
        $fotoSrc = asset('img/perfil_trabajador.jpg');
    }

    $initials = strtoupper(substr($user?->name ?? 'T', 0, 1) . substr($user?->apellido ?? 'E', 0, 1));
    $nombreCompleto = trim(($user?->name ?? 'Especialista') . ' ' . ($user?->apellido ?? ''));
    $rolNombre = ucfirst($user?->role?->nombre ?? 'Especialista');
@endphp

<!-- ══════════════════ ALERTAS SWEETALERT ══════════════════ -->
@if (session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: '¡Operación Exitosa!',
        text: "{{ session('success') }}",
        confirmButtonColor: '#7c1db8',
        confirmButtonText: 'Aceptar'
    });
});
</script>
@endif

@if (session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'warning',
        title: 'Aviso',
        text: "{{ session('error') }}",
        confirmButtonColor: '#7c1db8',
        confirmButtonText: 'Entendido'
    });
});
</script>
@endif

<!-- Formulario Oculto para Subir Foto de Perfil -->
<form method="POST" action="{{ route('trabajador.perfil.foto') }}" enctype="multipart/form-data" id="formFotoTrabajador" style="display:none;">
    @csrf
    <input type="file" name="foto_perfil" id="inputFotoTrabajador" accept="image/*" onchange="document.getElementById('formFotoTrabajador').submit();">
</form>

<main class="perfil-page-container">

    <!-- ── Hero Banner del Especialista ── -->
    <div class="perfil-hero-card">
        <div class="perfil-hero-left">
            <div class="perfil-avatar-container">
                @if ($fotoSrc)
                    <img src="{{ $fotoSrc }}" alt="{{ $nombreCompleto }}" class="perfil-avatar-img">
                @else
                    <div class="perfil-avatar-placeholder">{{ $initials }}</div>
                @endif
                <button type="button" class="btn-cambiar-foto" title="Cambiar foto de perfil" onclick="document.getElementById('inputFotoTrabajador').click();">
                    <i class="fa-solid fa-camera"></i>
                </button>
            </div>

            <div class="perfil-hero-info">
                <h2>
                    {{ $nombreCompleto }}
                    <i class="fa-solid fa-circle-check" style="color: #7c1db8; font-size: 18px;" title="Especialista Verificada"></i>
                </h2>
                <p>
                    <i class="fa-regular fa-envelope" style="margin-right: 4px;"></i> {{ $user?->email }} &nbsp;&bull;&nbsp;
                    <i class="fa-solid fa-phone" style="margin-right: 4px;"></i> {{ $user?->telefono ?? 'Sin teléfono registrado' }}
                </p>
                <div class="perfil-badges-row">
                    <span class="badge-especialidad">
                        <i class="fa-solid fa-wand-magic-sparkles"></i> {{ $trabajador?->especialidad ?? 'Lash Artist & Brow Designer' }}
                    </span>
                    <span class="badge-activo">
                        <i class="fa-solid fa-circle-dot" style="font-size: 8px;"></i> Especialista Activa
                    </span>
                </div>
            </div>
        </div>

        <!-- Métricas Rápidas -->
        <div class="perfil-hero-metrics">
            <div class="metric-pill-item">
                <div class="metric-pill-val">{{ $citasAtendidas }}</div>
                <div class="metric-pill-lbl">Citas Atendidas</div>
            </div>
            <div class="metric-pill-item">
                <div class="metric-pill-val">{{ number_format($promedioCalificacion, 1) }} ★</div>
                <div class="metric-pill-lbl">Calificación</div>
            </div>
            <div class="metric-pill-item">
                <div class="metric-pill-val">{{ $totalResenas }}</div>
                <div class="metric-pill-lbl">Reseñas</div>
            </div>
            <div class="metric-pill-item">
                <div class="metric-pill-val">{{ $serviciosTotal }}</div>
                <div class="metric-pill-lbl">Servicios</div>
            </div>
        </div>
    </div>

    <!-- ── Pestañas de Navegación del Perfil ── -->
    <div class="perfil-nav-tabs">
        <button type="button" class="perfil-tab-btn active" data-tab="tab-informacion">
            <i class="fa-regular fa-id-card"></i> Datos Personales y Especialidad
        </button>
        <button type="button" class="perfil-tab-btn" data-tab="tab-seguridad">
            <i class="fa-solid fa-shield-halved"></i> Seguridad y Contraseña
        </button>
        <button type="button" class="perfil-tab-btn" data-tab="tab-agenda">
            <i class="fa-regular fa-calendar-check"></i> Próxima Cita & Turnos
        </button>
    </div>

    <!-- ══════ PANEL 1: DATOS PERSONALES ══════ -->
    <div class="perfil-tab-panel active" id="tab-informacion">
        <div class="perfil-card">
            <div class="perfil-card-header">
                <h3><i class="fa-solid fa-user-pen" style="color: #7c1db8;"></i> Información del Perfil</h3>
                <p>Mantén tu información personal y descripción profesional actualizada</p>
            </div>

            <form action="{{ route('trabajador.perfil.update') }}" method="POST">
                @csrf
                <div class="form-grid-perfil">
                    <div class="form-group-perfil">
                        <label for="name">Nombre <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name', $user?->name) }}" required>
                    </div>

                    <div class="form-group-perfil">
                        <label for="apellido">Apellido <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="apellido" id="apellido" value="{{ old('apellido', $user?->apellido) }}" required>
                    </div>

                    <div class="form-group-perfil">
                        <label for="documento">Documento de Identidad</label>
                        <input type="text" name="documento" id="documento" value="{{ old('documento', $user?->documento) }}" placeholder="Ej: 1075208040">
                    </div>

                    <div class="form-group-perfil">
                        <label for="telefono">Teléfono / WhatsApp</label>
                        <input type="text" name="telefono" id="telefono" value="{{ old('telefono', $user?->telefono) }}" placeholder="Ej: 3147662316">
                    </div>

                    <div class="form-group-perfil full-width">
                        <label for="email">Correo Electrónico (No modificable)</label>
                        <input type="email" id="email" value="{{ $user?->email }}" readonly title="El correo electrónico se gestiona por administración">
                    </div>

                    <div class="form-group-perfil full-width">
                        <label for="especialidad">Especialidad Principal <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="especialidad" id="especialidad" value="{{ old('especialidad', $trabajador?->especialidad ?? 'Lash Artist & Brow Designer') }}" placeholder="Ej: Master en Extensiones Pelo a Pelo y Diseño de Cejas" required>
                    </div>

                    <div class="form-group-perfil full-width">
                        <label for="descripcion">Biografía Profesional / Resumen de Experiencia</label>
                        <textarea name="descripcion" id="descripcion" rows="4" placeholder="Describe tu experiencia, certificaciones y dedicación en el cuidado de la mirada...">{{ old('descripcion', $trabajador?->descripcion ?? 'Especialista en pestañas pelo a pelo, volumen ruso y diseño de cejas.') }}</textarea>
                    </div>
                </div>

                <div class="perfil-form-actions">
                    <button type="submit" class="btn-guardar-perfil">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios del Perfil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════ PANEL 2: SEGURIDAD Y CONTRASEÑA ══════ -->
    <div class="perfil-tab-panel" id="tab-seguridad">
        <div class="perfil-card">
            <div class="perfil-card-header">
                <h3><i class="fa-solid fa-lock" style="color: #7c1db8;"></i> Actualizar Contraseña</h3>
                <p>Te recomendamos utilizar una contraseña segura con letras, números y símbolos</p>
            </div>

            <form action="{{ route('trabajador.perfil.password') }}" method="POST">
                @csrf
                <div class="form-grid-perfil">
                    <div class="form-group-perfil full-width">
                        <label for="contrasena_actual">Contraseña Actual <span style="color:#dc2626;">*</span></label>
                        <input type="password" name="contrasena_actual" id="contrasena_actual" placeholder="Ingresa tu contraseña actual" required>
                    </div>

                    <div class="form-group-perfil">
                        <label for="contrasena_nueva">Nueva Contraseña <span style="color:#dc2626;">*</span></label>
                        <input type="password" name="contrasena_nueva" id="contrasena_nueva" placeholder="Mínimo 6 caracteres" required>
                    </div>

                    <div class="form-group-perfil">
                        <label for="contrasena_confirmar">Confirmar Nueva Contraseña <span style="color:#dc2626;">*</span></label>
                        <input type="password" name="contrasena_confirmar" id="contrasena_confirmar" placeholder="Repite tu nueva contraseña" required>
                    </div>
                </div>

                <div class="perfil-form-actions">
                    <button type="submit" class="btn-guardar-perfil">
                        <i class="fa-solid fa-key"></i> Actualizar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ══════ PANEL 3: PRÓXIMA CITA & AGENDA ══════ -->
    <div class="perfil-tab-panel" id="tab-agenda">
        <div class="perfil-card">
            <div class="perfil-card-header">
                <h3><i class="fa-solid fa-calendar-star" style="color: #7c1db8;"></i> Próxima Cita Asignada</h3>
                <p>Detalles del próximo servicio agendado en tu agenda de atención</p>
            </div>

            @if ($proximaCita)
                @php
                    $cliNombre = $proximaCita->cliente?->user ? trim($proximaCita->cliente->user->name . ' ' . ($proximaCita->cliente->user->apellido ?? '')) : 'Clienta LashApp';
                    $srvNombre = $proximaCita->servicio?->nombre ?? 'Servicio Profesional';
                    $fechaCita = $proximaCita->fecha ? Carbon::parse($proximaCita->fecha)->translatedFormat('l, d \d\e F \d\e Y') : 'Fecha pendiente';
                    $horaCita = $proximaCita->hora_inicio ? date('g:i a', strtotime($proximaCita->hora_inicio)) : '--:--';
                @endphp
                <div class="proxima-cita-box">
                    <img src="{{ asset('img/volumen_ruso.jpg') }}" alt="{{ $srvNombre }}" class="proxima-cita-img">
                    <div class="proxima-cita-info" style="flex:1;">
                        <h4>{{ $srvNombre }}</h4>
                        <p>
                            <strong>Clienta:</strong> {{ $cliNombre }} &nbsp;&bull;&nbsp;
                            <strong>Fecha:</strong> {{ ucfirst($fechaCita) }} &nbsp;&bull;&nbsp;
                            <strong>Hora:</strong> {{ $horaCita }}
                        </p>
                    </div>
                    <a href="{{ route('citas.index') }}" class="btn-guardar-perfil" style="text-decoration:none; padding:10px 18px; font-size:12px;">
                        Ver en Mis Citas
                    </a>
                </div>
            @else
                <div style="text-align:center; padding:32px; color:#7a5a90;">
                    <i class="fa-regular fa-calendar-check" style="font-size:36px; color:#dcc8ee; margin-bottom:12px; display:block;"></i>
                    <strong style="color:#2a0d3e; display:block; margin-bottom:4px;">No tienes citas próximas agendadas</strong>
                    <p style="font-size:13px; margin:0 0 16px;">Revisa tus horarios disponibles para que tus clientas puedan agendar.</p>
                    <a href="{{ route('trabajador.horario') }}" class="btn-guardar-perfil" style="text-decoration:none; display:inline-flex;">
                        <i class="fa-solid fa-clock"></i> Administrar Mi Horario
                    </a>
                </div>
            @endif
        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.perfil-tab-btn');
    const panels = document.querySelectorAll('.perfil-tab-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            const targetId = this.getAttribute('data-tab');
            const targetPanel = document.getElementById(targetId);
            if (targetPanel) targetPanel.classList.add('active');
        });
    });
});
</script>

@include('layouts.footer')
