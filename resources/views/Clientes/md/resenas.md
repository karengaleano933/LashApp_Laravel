# Explicación Código por Código de `resenas.blade.php` (En mis palabras)

Este documento detalla **cada bloque, línea y función de código** que compone el archivo [`resenas.blade.php`](file:///c:/laragon/www/LashApp/resources/views/Clientes/resenas.blade.php), el espacio donde las clientas de **LashApp** califican los servicios recibidos con estrellas interactivas (1 a 5), revisan citas completadas pendientes de opinión y consultan sus testimonios publicados.

---

## 1. Encabezado, Inclusiones y Helper de Imágenes (Líneas 1 - 27)

```blade
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
```

### ¿Qué hace cada parte?
* **`@vite(['resources/css/resenascliente.css'])`:** Carga los estilos CSS para las tarjetas de reseñas, animación hover en las estrellas y el modal.
* **Función `$getImageForService`:** Función anónima de PHP que analiza el modelo `Servicio` y retorna la foto adecuada (o un fallback temático según si es cejas, pestañas clásicas o manicure).

---

## 2. Encabezado y Estadísticas de Calificación (Líneas 29 - 96)

```blade
    <div class="resenas-header">
        <div class="resenas-header-left">
            <div class="resenas-header-icon"><i class="fa-solid fa-star"></i></div>
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

    <!-- TARJETAS DE ESTADÍSTICAS -->
    <div class="resenas-stats-grid">
        <div class="stat-resena-card">
            <div class="stat-resena-icon gold"><i class="fa-solid fa-star"></i></div>
            <div class="stat-resena-info">
                <div class="stat-resena-val">{{ number_format($promedioStudio ?? 5.0, 1) }} / 5.0</div>
                <div class="stat-resena-label">Calificación de la comunidad ({{ $totalResenasStudio ?? 0 }} opiniones)</div>
            </div>
        </div>

        <div class="stat-resena-card">
            <div class="stat-resena-icon purple"><i class="fa-solid fa-comment-dots"></i></div>
            <div class="stat-resena-info">
                <div class="stat-resena-val">{{ $totalResenasCliente ?? 0 }}</div>
                <div class="stat-resena-label">Reseñas compartidas por ti</div>
            </div>
        </div>

        <div class="stat-resena-card">
            <div class="stat-resena-icon green"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <div class="stat-resena-info">
                <div class="stat-resena-val">{{ $totalPorCalificar ?? 0 }}</div>
                <div class="stat-resena-label">Citas disponibles para calificar</div>
            </div>
        </div>
    </div>
```

### ¿Qué hace cada parte?
* **Botón `btnTopCalificarCita`:** Solo aparece si `$citasSinResena->count() > 0` para invitar a la clienta a calificar.
* **Tarjeta 1 (Dorada):** Calificación promedio global del estudio (`$promedioStudio`) sobre 5.0.
* **Tarjeta 2 (Morada):** Cuántas reseñas ha escrito esta clienta (`$totalResenasCliente`).
* **Tarjeta 3 (Verde):** Cuántas citas completadas tiene pendientes por calificar (`$totalPorCalificar`).

---

## 3. Sección 1: Citas Pendientes de tu Opinión (Líneas 98 - 138)

```blade
    @if(isset($citasSinResena) && $citasSinResena->count() > 0)
        <div class="resenas-section-box">
            <div class="resenas-section-title">
                <h2><i class="fa-regular fa-clock"></i> Citas pendientes de tu opinión</h2>
                <span class="badge-counter">{{ $citasSinResena->count() }} disponible(s)</span>
            </div>

            <div class="citas-pendientes-grid">
                @foreach($citasSinResena as $citaPendiente)
                    <div class="cita-calificar-card">
                        <div class="cita-card-top">
                            <img src="{{ $imgServ }}" alt="{{ $servName }}" class="cita-service-img">
                            <div class="cita-card-details">
                                <h4>{{ $servName }}</h4>
                                <div class="cita-card-meta">
                                    <span><i class="fa-regular fa-calendar"></i> {{ $fechaFmt }}</span>
                                    <span><i class="fa-solid fa-user-nurse"></i> {{ $profName }}</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn-abrir-calificar" onclick="abrirModalCalificar({{ $citaPendiente->id }}, '{{ addslashes($servName) }}', '{{ addslashes($profName) }}')">
                            <i class="fa-solid fa-star"></i> Calificar esta cita
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
```

* **Bloque condicional:** Muestra las citas atendidas que aún no tienen feedback.
* **`onclick="abrirModalCalificar(id, serv, prof)"`:** Pasa el ID de la cita al modal para preseleccionarla automáticamente.

---

## 4. Sección 2: Tus Reseñas Publicadas (Líneas 140 - 215)

```blade
    <div class="resenas-section-box">
        <div class="resenas-section-title">
            <h2><i class="fa-solid fa-quote-left"></i> Tus Reseñas Publicadas</h2>
            <span class="badge-counter">{{ $resenasCliente->count() }} reseña(s)</span>
        </div>

        @if($resenasCliente->count() > 0)
            <div class="resenas-list-grid">
                @foreach($resenasCliente as $r)
                    <div class="resena-card-item">
                        <div class="resena-service-header">
                            <img src="{{ $thumb }}" class="resena-thumb">
                            <div>
                                <h3 class="resena-service-title">{{ $servNombre }}</h3>
                                <span class="resena-specialist"><i class="fa-solid fa-user-check"></i> Atendido por: {{ $especialista }}</span>
                            </div>
                        </div>

                        <!-- Estrellas de 1 a 5 -->
                        <div class="resena-stars-wrap">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-{{ $i <= $r->calificacion ? 'solid' : 'regular' }} fa-star"></i>
                            @endfor
                        </div>

                        <!-- Comentario entre comillas -->
                        <div class="resena-comentario-box">
                            “{{ $r->comentario ?: 'Servicio excelente, quedé muy satisfecha...' }}”
                        </div>

                        <div class="resena-item-footer">
                            <span class="resena-badge-verificada"><i class="fa-solid fa-certificate"></i> Experiencia verificada</span>
                            <span><i class="fa-regular fa-calendar"></i> {{ $fechaResena }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Estado vacío con invitación a calificar o agendar -->
        @endif
    </div>
```

### ¿Qué hace cada parte?
* **Bucle `@for($i = 1; $i <= 5; $i++)`:** Dibuja las estrellas amarillas sólidas (`fa-solid`) hasta la calificación otorgada y vacías (`fa-regular`) para el resto.
* **`resena-badge-verificada`:** Distintivo de autenticidad que certifica que la clienta asistió a su cita.

---

## 5. Modal para Calificar Experiencia (Líneas 222 - 282)

```blade
<div class="modal-resena-overlay" id="modalResenaOverlay" style="display:none;">
    <div class="modal-resena-container">
        <div class="modal-resena-header">
            <h3><i class="fa-solid fa-star" style="color:#f59e0b;"></i> Calificar tu Experiencia</h3>
            <button type="button" class="modal-resena-close" onclick="cerrarModalCalificar()"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <form method="POST" action="{{ route('cliente.resenas.store') }}" id="formCrearResena">
            @csrf
            <div class="modal-resena-body">
                <!-- Select de Cita -->
                <select name="id_cita" id="modalSelectCita" class="form-select-resena" required>
                    @foreach($citasSinResena as $c)
                        <option value="{{ $c->id }}">{{ $servStr }} — {{ $fechaStr }} ({{ $profStr }})</option>
                    @endforeach
                </select>

                <!-- Selector de Estrellas Interactivas -->
                <input type="hidden" name="calificacion" id="inputCalificacionEstrellas" value="5">
                <div class="star-rating-selector" id="starRatingSelector">
                    <i class="fa-solid fa-star active" data-val="1"></i>
                    <i class="fa-solid fa-star active" data-val="2"></i>
                    <i class="fa-solid fa-star active" data-val="3"></i>
                    <i class="fa-solid fa-star active" data-val="4"></i>
                    <i class="fa-solid fa-star active" data-val="5"></i>
                </div>
                <div class="rating-text-feedback" id="ratingTextFeedback">¡Excelente servicio! (5 de 5)</div>

                <!-- Textarea de Comentario -->
                <textarea name="comentario" id="modalComentario" rows="4" placeholder="¿Cómo te sentiste?..." required minlength="5" maxlength="800"></textarea>

                <div class="modal-resena-footer">
                    <button type="button" class="btn-modal-cancel" onclick="cerrarModalCalificar()">Cancelar</button>
                    <button type="submit" class="btn-modal-submit"><i class="fa-solid fa-paper-plane"></i> Publicar Reseña</button>
                </div>
            </div>
        </form>
    </div>
</div>
```

* **`#inputCalificacionEstrellas`:** Guarda el valor numérico (1 a 5) que se enviará al controlador.
* **`#ratingTextFeedback`:** Muestra un mensaje en texto según las estrellas elegidas (ej. *"¡Excelente servicio! (5 de 5)"* o *"Podría mejorar (2 de 5)"*).

---

## 6. Scripts de JavaScript (Líneas 284 - 368)

```javascript
    const ratingTexts = {
        1: 'Muy insatisfecha (1 de 5)',
        2: 'Podría mejorar (2 de 5)',
        3: 'Buena experiencia (3 de 5)',
        4: 'Muy buena atención (4 de 5)',
        5: '¡Excelente servicio! (5 de 5)'
    };

    // 1. Abrir y cerrar modal
    function abrirModalCalificar(citaId = null) {
        if (citaId) document.getElementById('modalSelectCita').value = citaId;
        document.getElementById('modalResenaOverlay').style.display = 'flex';
    }

    function cerrarModalCalificar() {
        document.getElementById('modalResenaOverlay').style.display = 'none';
    }

    // 2. Control de eventos en las Estrellas
    document.addEventListener('DOMContentLoaded', function() {
        const stars = document.querySelectorAll('#starRatingSelector i');
        const inputVal = document.getElementById('inputCalificacionEstrellas');
        const feedbackText = document.getElementById('ratingTextFeedback');
        let currentScore = 5;

        stars.forEach(star => {
            // Hover (iluminar estrellas hasta el cursor)
            star.addEventListener('mouseenter', function() {
                const val = parseInt(this.getAttribute('data-val'));
                stars.forEach(s => {
                    s.className = (parseInt(s.getAttribute('data-val')) <= val) 
                        ? 'fa-solid fa-star hovered' 
                        : 'fa-regular fa-star';
                });
                feedbackText.textContent = ratingTexts[val] || '';
            });

            // Salir del hover (restaurar la nota seleccionada)
            star.addEventListener('mouseleave', function() {
                updateStars(currentScore);
            });

            // Clic (fijar nueva calificación)
            star.addEventListener('click', function() {
                currentScore = parseInt(this.getAttribute('data-val'));
                inputVal.value = currentScore;
                updateStars(currentScore);
            });
        });
    });
```
