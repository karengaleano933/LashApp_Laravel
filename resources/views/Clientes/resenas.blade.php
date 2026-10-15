<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Mis Reseñas'])
@include('layouts.sidebarcliente')

@vite(['resources/css/resenascliente.css'])

@php
    use Carbon\Carbon;

    // Fallbacks para imágenes de servicio
    $getImageForService = function ($servicio) {
        if (!empty($servicio?->imagen_url)) {
            return filter_var($servicio->imagen_url, FILTER_VALIDATE_URL) 
                ? $servicio->imagen_url 
                : asset($servicio->imagen_url);
        }
        $sNom = strtolower($servicio?->nombre ?? '');
        if (str_contains($sNom, 'ceja') || str_contains($sNom, 'henna')) {
            return asset('img/diseno_cejas.jpg');
        } elseif (str_contains($sNom, 'clásica') || str_contains($sNom, 'clasica')) {
            return asset('img/extensiones_clasicas.jpg');
        } elseif (str_contains($sNom, 'mani') || str_contains($sNom, 'semi')) {
            return asset('img/manicure_semi.jpg');
        }
        return asset('img/volumen_ruso.jpg');
    };
@endphp

<div class="resenas-content-area" style="padding: 24px 30px;">

    <!-- TOP HEADER -->
    <div class="resenas-header">
        <div class="resenas-header-left">
            <div class="resenas-header-icon">
                <i class="fa-solid fa-star"></i>
            </div>
            <div>
                <h1>Mis Reseñas ⭐</h1>
                <p>Opiniones y calificaciones sobre tus experiencias en Oh La Lash Studio.</p>
            </div>
        </div>

        @if(isset($citasSinResena) && $citasSinResena->count() > 0)
            <button type="button" id="btnTopCalificarCita" class="btn-nueva-resena" onclick="abrirModalCalificar()">
                <i class="fa-solid fa-pen-to-square"></i> Calificar Cita
            </button>
        @endif
    </div>

    <!-- MENSAJES FLASH -->
    @if(session('success'))
        <div style="margin-bottom: 22px; padding: 14px 20px; border-radius: 14px; font-size: 13px; font-weight: 700; background: #d1fae5; color: #065f46; display: flex; align-items: center; gap: 10px; border: 1px solid #a7f3d0; box-shadow: 0 4px 12px rgba(6,95,70,0.06);">
            <i class="fa-solid fa-circle-check" style="font-size: 16px;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div style="margin-bottom: 22px; padding: 14px 20px; border-radius: 14px; font-size: 13px; font-weight: 700; background: #fee2e2; color: #991b1b; display: flex; align-items: center; gap: 10px; border: 1px solid #fecaca; box-shadow: 0 4px 12px rgba(153,27,27,0.06);">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 16px;"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- STATS GRID -->
    <div class="resenas-stats-grid">
        <div class="stat-resena-card">
            <div class="stat-resena-icon gold">
                <i class="fa-solid fa-star"></i>
            </div>
            <div class="stat-resena-info">
                <div class="stat-resena-val">{{ number_format($promedioStudio ?? 5.0, 1) }} / 5.0</div>
                <div class="stat-resena-label">Calificación de la comunidad ({{ $totalResenasStudio ?? 0 }} opiniones)</div>
            </div>
        </div>

        <div class="stat-resena-card">
            <div class="stat-resena-icon purple">
                <i class="fa-solid fa-comment-dots"></i>
            </div>
            <div class="stat-resena-info">
                <div class="stat-resena-val">{{ $totalResenasCliente ?? 0 }}</div>
                <div class="stat-resena-label">Reseñas compartidas por ti</div>
            </div>
        </div>

        <div class="stat-resena-card">
            <div class="stat-resena-icon green">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div class="stat-resena-info">
                <div class="stat-resena-val">{{ $totalPorCalificar ?? 0 }}</div>
                <div class="stat-resena-label">Citas disponibles para calificar</div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 1: CITAS PENDIENTES POR CALIFICAR -->
    @if(isset($citasSinResena) && $citasSinResena->count() > 0)
        <div class="resenas-section-box">
            <div class="resenas-section-title">
                <h2>
                    <i class="fa-regular fa-clock"></i> Citas pendientes de tu opinión
                </h2>
                <span class="badge-counter">{{ $citasSinResena->count() }} disponible(s)</span>
            </div>

            <div class="citas-pendientes-grid">
                @foreach($citasSinResena as $citaPendiente)
                    @php
                        $fechaFmt = $citaPendiente->fecha ? Carbon::parse($citaPendiente->fecha)->format('d/m/Y') : 'Fecha reciente';
                        $horaFmt = $citaPendiente->hora_inicio ? Carbon::parse($citaPendiente->hora_inicio)->format('h:i A') : '';
                        $profName = $citaPendiente->trabajador?->user 
                            ? trim($citaPendiente->trabajador->user->name . ' ' . ($citaPendiente->trabajador->user->apellido ?? '')) 
                            : 'Lorena Ayala';
                        $servName = $citaPendiente->servicio?->nombre ?? 'Servicio Realizado';
                        $imgServ = $getImageForService($citaPendiente->servicio);
                    @endphp
                    <div class="cita-calificar-card">
                        <div class="cita-card-top">
                            <img src="{{ $imgServ }}" alt="{{ $servName }}" class="cita-service-img">
                            <div class="cita-card-details">
                                <h4>{{ $servName }}</h4>
                                <div class="cita-card-meta">
                                    <span><i class="fa-regular fa-calendar"></i> {{ $fechaFmt }} {{ $horaFmt ? '• ' . $horaFmt : '' }}</span>
                                    <span><i class="fa-solid fa-user-nurse"></i> {{ $profName }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="btnCalificarCita{{ $citaPendiente->id }}" class="btn-abrir-calificar" onclick="abrirModalCalificar({{ $citaPendiente->id }}, '{{ addslashes($servName) }}', '{{ addslashes($profName) }}')">
                            <i class="fa-solid fa-star"></i> Calificar esta cita
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- SECCIÓN 2: MIS RESEÑAS PUBLICADAS -->
    <div class="resenas-section-box">
        <div class="resenas-section-title">
            <h2>
                <i class="fa-solid fa-quote-left"></i> Tus Reseñas Publicadas
            </h2>
            <span class="badge-counter">{{ $resenasCliente->count() }} reseña(s)</span>
        </div>

        @if($resenasCliente->count() > 0)
            <div class="resenas-list-grid">
                @foreach($resenasCliente as $r)
                    @php
                        $serv = $r->cita?->servicio;
                        $servNombre = $serv?->nombre ?? 'Servicio Realizado';
                        $thumb = $getImageForService($serv);
                        $especialista = $r->cita?->trabajador?->user 
                            ? trim($r->cita->trabajador->user->name . ' ' . ($r->cita->trabajador->user->apellido ?? '')) 
                            : 'Lorena Ayala';
                        $fechaResena = $r->fecha_resena ? Carbon::parse($r->fecha_resena)->format('d/m/Y') : Carbon::parse($r->created_at)->format('d/m/Y');
                    @endphp
                    <div class="resena-card-item">
                        <div>
                            <div class="resena-item-header">
                                <div class="resena-service-header">
                                    <img src="{{ $thumb }}" alt="{{ $servNombre }}" class="resena-thumb">
                                    <div>
                                        <h3 class="resena-service-title">{{ $servNombre }}</h3>
                                        <span class="resena-specialist">
                                            <i class="fa-solid fa-user-check"></i> Atendido por: {{ $especialista }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="resena-stars-wrap">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fa-{{ $i <= $r->calificacion ? 'solid' : 'regular' }} fa-star"></i>
                                @endfor
                            </div>

                            <div class="resena-comentario-box">
                                “{{ $r->comentario ?: 'Servicio excelente, quedé muy satisfecha con el resultado y la atención en el estudio.' }}”
                            </div>
                        </div>

                        <div class="resena-item-footer">
                            <span class="resena-badge-verificada">
                                <i class="fa-solid fa-certificate"></i> Experiencia verificada
                            </span>
                            <span>
                                <i class="fa-regular fa-calendar"></i> {{ $fechaResena }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="resenas-empty-state">
                <div class="empty-icon-circle">
                    <i class="fa-regular fa-star"></i>
                </div>
                <h3>Aún no has publicado reseñas</h3>
                <p>Una vez finalices tus citas en el estudio, podrás calificar la calidad del servicio, la atención de la especialista y compartir tu resultado.</p>
                @if(isset($citasSinResena) && $citasSinResena->count() > 0)
                    <button type="button" class="btn-nueva-resena" onclick="abrirModalCalificar()">
                        <i class="fa-solid fa-star"></i> Calificar mi primera cita
                    </button>
                @else
                    <a href="{{ route('cliente.agendar') }}" class="btn-nueva-resena">
                        <i class="fa-solid fa-calendar-plus"></i> Agendar una Cita
                    </a>
                @endif
            </div>
        @endif
    </div>

</div>

<!-- ══════════════════════════════════════════
     MODAL PARA CALIFICAR EXPERIENCIA
══════════════════════════════════════════ -->
<div class="modal-resena-overlay" id="modalResenaOverlay" style="display:none;">
    <div class="modal-resena-container">
        <div class="modal-resena-header">
            <h3><i class="fa-solid fa-star" style="color:#f59e0b;"></i> Calificar tu Experiencia</h3>
            <button type="button" class="modal-resena-close" onclick="cerrarModalCalificar()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" action="{{ route('cliente.resenas.store') }}" id="formCrearResena">
            @csrf
            <div class="modal-resena-body">
                <!-- Selector de Cita -->
                <div class="form-group-resena">
                    <label for="modalSelectCita">Cita a calificar</label>
                    <select name="id_cita" id="modalSelectCita" class="form-select-resena" required>
                        @if(isset($citasSinResena) && $citasSinResena->count() > 0)
                            @foreach($citasSinResena as $c)
                                @php
                                    $fechaStr = $c->fecha ? Carbon::parse($c->fecha)->format('d/m/Y') : 'Reciente';
                                    $servStr = $c->servicio?->nombre ?? 'Servicio';
                                    $profStr = $c->trabajador?->user?->name ?? 'Lorena';
                                @endphp
                                <option value="{{ $c->id }}" data-servicio="{{ $servStr }}" data-especialista="{{ $profStr }}">
                                    {{ $servStr }} — {{ $fechaStr }} ({{ $profStr }})
                                </option>
                            @endforeach
                        @else
                            <option value="" disabled selected>No tienes citas pendientes por calificar</option>
                        @endif
                    </select>
                </div>

                <!-- Selector de Estrellas -->
                <div class="form-group-resena">
                    <label>Tu calificación</label>
                    <input type="hidden" name="calificacion" id="inputCalificacionEstrellas" value="5">
                    <div class="star-rating-selector" id="starRatingSelector">
                        <i class="fa-solid fa-star active" data-val="1"></i>
                        <i class="fa-solid fa-star active" data-val="2"></i>
                        <i class="fa-solid fa-star active" data-val="3"></i>
                        <i class="fa-solid fa-star active" data-val="4"></i>
                        <i class="fa-solid fa-star active" data-val="5"></i>
                    </div>
                    <div class="rating-text-feedback" id="ratingTextFeedback">¡Excelente servicio! (5 de 5)</div>
                </div>

                <!-- Comentario -->
                <div class="form-group-resena">
                    <label for="modalComentario">Cuéntanos tu experiencia</label>
                    <textarea name="comentario" id="modalComentario" rows="4" class="form-textarea-resena" placeholder="¿Cómo te sentiste? ¿Qué te pareció el diseño, la atención y el resultado final?..." required minlength="5" maxlength="800"></textarea>
                </div>

                <div class="modal-resena-footer">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModalCalificar()">Cancelar</button>
                    <button type="submit" class="btn-modal-submit" id="btnSubmitResena">
                        <i class="fa-solid fa-paper-plane"></i> Publicar Reseña
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const ratingTexts = {
    1: 'Muy insatisfecha (1 de 5)',
    2: 'Podría mejorar (2 de 5)',
    3: 'Buena experiencia (3 de 5)',
    4: 'Muy buena atención (4 de 5)',
    5: '¡Excelente servicio! (5 de 5)'
};

function abrirModalCalificar(citaId = null, servicioNombre = '', especialistaNombre = '') {
    const modal = document.getElementById('modalResenaOverlay');
    const select = document.getElementById('modalSelectCita');
    if (citaId && select) {
        select.value = citaId;
    }
    if (modal) {
        modal.style.display = 'flex';
    }
}

function cerrarModalCalificar() {
    const modal = document.getElementById('modalResenaOverlay');
    if (modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('#starRatingSelector i');
    const inputVal = document.getElementById('inputCalificacionEstrellas');
    const feedbackText = document.getElementById('ratingTextFeedback');
    let currentScore = 5;

    function updateStars(score) {
        stars.forEach(star => {
            const val = parseInt(star.getAttribute('data-val'));
            if (val <= score) {
                star.className = 'fa-solid fa-star active';
            } else {
                star.className = 'fa-regular fa-star';
            }
        });
        if (feedbackText) {
            feedbackText.textContent = ratingTexts[score] || (score + ' de 5');
        }
    }

    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const val = parseInt(this.getAttribute('data-val'));
            stars.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-val'));
                if (sVal <= val) {
                    s.className = 'fa-solid fa-star hovered';
                } else {
                    s.className = 'fa-regular fa-star';
                }
            });
            if (feedbackText) {
                feedbackText.textContent = ratingTexts[val] || '';
            }
        });

        star.addEventListener('mouseleave', function() {
            updateStars(currentScore);
        });

        star.addEventListener('click', function() {
            currentScore = parseInt(this.getAttribute('data-val'));
            if (inputVal) inputVal.value = currentScore;
            updateStars(currentScore);
        });
    });

    // Cerrar modal al hacer clic en el overlay
    const overlay = document.getElementById('modalResenaOverlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalCalificar();
            }
        });
    }
});
</script>

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper (abierto en headercliente.blade.php) -->
</div> <!-- Cierre de .app-layout (abierto en headercliente.blade.php) -->

</body>
</html>
