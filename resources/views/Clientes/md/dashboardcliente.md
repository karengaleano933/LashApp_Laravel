# Explicación Código por Código de `dashboardcliente.blade.php` (En mis palabras)

Este documento explica **cada bloque, línea y función de código** que compone el archivo [`dashboardcliente.blade.php`](file:///c:/laragon/www/LashApp/resources/views/Clientes/dashboardcliente.blade.php), el portal principal donde las clientas de **LashApp** gestionan sus citas, consultan el catálogo y descubren promociones.

---

## 1. Encabezado, Layouts y Apertura del Contenedor (Líneas 1 - 7)

```blade
<!-- Incluir Header superior de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Mi Portal'])
@include('layouts.sidebarcliente')

<!-- Contenido Principal -->
<div class="page-content">
```

### ¿Qué hace cada línea?
* **`@include('layouts.headercliente', ['titulo' => 'Mi Portal'])`:** Carga el encabezado HTML común para las clientas (etiquetas `<head>`, fuentes de Google, FontAwesome, metadatos y la barra superior con el saludo). Le paso el parámetro `'titulo' => 'Mi Portal'` para que la pestaña del navegador muestre ese nombre.
* **`@include('layouts.sidebarcliente')`:** Incluye la barra lateral de navegación exclusiva para clientas (con enlaces a Inicio, Catálogo, Agendar Cita, Mis Citas, Historial, Reseñas y Perfil).
* **`<div class="page-content">`:** Es el contenedor principal con margen y espaciado donde se ubican todas las secciones del dashboard.

---

## 2. Sección 1: Acciones Rápidas (Líneas 8 - 51)

```blade
    <!-- ══════════════════════════════════════════
         SECCIÓN 1: ACCIONES RÁPIDAS
    ══════════════════════════════════════════ -->
    <div class="section-label">
        Acciones rápidas
    </div>

    <div class="quick-actions-grid">
        <a href="{{ route('cliente.agendar') }}" class="quick-action-card">
            <div class="quick-action-icon"><i class="fa-solid fa-calendar-plus"></i></div>
            <div class="quick-action-info">
                <div class="quick-action-title">Agendar Cita</div>
                <div class="quick-action-desc">Reserva tu próxima cita fácilmente</div>
            </div>
            <div class="quick-action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
        </a>

        <a href="{{ route('cliente.catalogo') }}" class="quick-action-card">
            <div class="quick-action-icon"><i class="fa-solid fa-bag-shopping"></i></div>
            <div class="quick-action-info">
                <div class="quick-action-title">Catálogo</div>
                <div class="quick-action-desc">Descubre nuestros servicios y precios</div>
            </div>
            <div class="quick-action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
        </a>

        <div onclick="abrirModalAgenda()" class="quick-action-card">
            <div class="quick-action-icon"><i class="fa-solid fa-calendar-days"></i></div>
            <div class="quick-action-info">
                <div class="quick-action-title">Ver Horarios</div>
                <div class="quick-action-desc">Consulta fechas y horarios disponibles</div>
            </div>
            <div class="quick-action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
        </div>

        <a href="{{ route('cliente.citas') }}" class="quick-action-card">
            <div class="quick-action-icon"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="quick-action-info">
                <div class="quick-action-title">Mis Citas</div>
                <div class="quick-action-desc">Revisa tu historial de reservas</div>
            </div>
            <div class="quick-action-arrow"><i class="fa-solid fa-chevron-right"></i></div>
        </a>
    </div>
```

### ¿Qué hace cada parte?
* **`<div class="section-label">`:** Texto guía pequeño y en mayúsculas que separa visualmente cada bloque de la página.
* **`<div class="quick-actions-grid">`:** Cuadrícula CSS que organiza las 4 tarjetas horizontales en columnas responsive.
* **Tarjeta 1 (`route('cliente.agendar')`):** Enlace directo con ícono `calendar-plus` que abre la pantalla de agendamiento guiado.
* **Tarjeta 2 (`route('cliente.catalogo')`):** Enlace con ícono de bolsa de compras `bag-shopping` que lleva al catálogo completo con precios.
* **Tarjeta 3 (`onclick="abrirModalAgenda()"`):** No es un enlace ordinario; es un `div` con un evento JavaScript que abre una ventana modal emergente para consultar qué días y horas están libres sin abandonar la página actual.
* **Tarjeta 4 (`route('cliente.citas')`):** Enlace con ícono `calendar-check` que lleva al panel de seguimiento de citas de la clienta.
* **`<div class="quick-action-arrow">`:** Una flechita hacia la derecha (`fa-chevron-right`) que le indica intuitivamente a la usuaria que el elemento es interactivo.

---

## 3. Sección 2: Próxima Cita de la Clienta (Líneas 53 - 109)

```blade
    <!-- ══════════════════════════════════════════
         SECCIÓN 2: MID GRID (TU PRÓXIMA CITA + CATÁLOGO DESTACADO)
    ══════════════════════════════════════════ -->
    <div class="mid-grid">

        <!-- Tu Próxima Cita -->
        <div class="proxima-card">
            <div>
                <div class="proxima-header">
                    <h3><i class="fa-regular fa-calendar-check"></i> Tu próxima cita</h3>
                    <i class="fa-solid fa-ellipsis-vertical" style="color:#aaa; cursor:pointer;"></i>
                </div>
                
                @if (!empty($proximaCita))
                    <img src="{{ $proximaCita['imagen_url'] ?? asset('img/volumen_ruso.jpg') }}" alt="Pestañas" class="proxima-img">

                    <div class="proxima-body-title">
                        {{ $proximaCita['servicio'] ?? 'Extensiones de Pestañas' }}
                    </div>
                    
                    <div class="proxima-sub-row">
                        <span class="proxima-sub-tag">{{ $proximaCita['subservicio'] ?? 'Volumen Ruso' }}</span>
                        <span class="proxima-badge-prof">{{ $proximaCita['profesional'] ?? 'Andrea' }}</span>
                    </div>

                    <div class="proxima-details-list">
                        <div class="proxima-detail-item">
                            <i class="fa-regular fa-calendar"></i> 
                            {{ !empty($proximaCita['fecha_cita']) ? \Carbon\Carbon::parse($proximaCita['fecha_cita'])->translatedFormat('l, d \d\e F \d\e Y') : 'Viernes, 20 de junio de 2026' }}
                        </div>
                        <div class="proxima-detail-item">
                            <i class="fa-regular fa-clock"></i> 
                            {{ !empty($proximaCita['hora_inicio']) ? \Carbon\Carbon::parse($proximaCita['hora_inicio'])->format('g:i a') : '10:00 am' }}
                        </div>
                        <div class="proxima-detail-item">
                            <i class="fa-solid fa-location-dot"></i> Oh La Lash Studio
                        </div>
                    </div>

                    <div class="proxima-actions">
                        <a href="{{ route('cliente.citas') }}" class="btn-proxima-detalles">Ver detalles</a>
                        <a href="{{ route('cliente.citas') }}" class="btn-proxima-cancelar">Reprogramar / Cancelar</a>
                    </div>
                @else
                    <div style="text-align: center; padding: 16px 12px;">
                        <div style="width: 44px; height: 44px; margin: 0 auto 10px; border-radius: 50%; background: #f4ecfa; color: #7c1db8; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                            <i class="fa-regular fa-calendar-plus"></i>
                        </div>
                        <h4 style="font-size: 13.5px; color: #2a0d3e; margin-bottom: 4px; font-weight: 700;">No tienes citas próximas agendadas</h4>
                        <p style="font-size: 11px; color: #7a5a90; margin-bottom: 14px;">¡Reserva tu momento de belleza y luce una mirada deslumbrante!</p>
                        <a href="{{ route('cliente.agendar') }}" class="btn-proxima-detalles" style="display: inline-flex; width: auto; padding: 8px 18px; font-size: 12px; margin: 0 auto;">
                            <i class="fa-solid fa-calendar-check" style="margin-right: 6px;"></i> Agendar mi cita ahora
                        </a>
                    </div>
                @endif
            </div>
        </div>
```

### ¿Qué hace cada parte?
* **`<div class="mid-grid">`:** Cuadrícula de 2 columnas que coloca la próxima cita a la izquierda y el catálogo destacado a la derecha.
* **`@if (!empty($proximaCita))`:** Condicional que verifica si el controlador encontró una cita pendiente o confirmada para hoy o días futuros.
  * **Imagen del servicio:** Si la cita tiene foto personalizada (`$proximaCita['imagen_url']`) la muestra; si no, recurre a `asset('img/volumen_ruso.jpg')` como respaldo.
  * **Título y Subtítulo:** Nombre del servicio (`$proximaCita['servicio']`) y técnica (`$proximaCita['subservicio']`).
  * **Insignia de la Lashista (`proxima-badge-prof`):** El nombre de la profesional asignada.
  * **Formateo de Fecha con Carbon:** `Carbon::parse(...)->translatedFormat('l, d \d\e F \d\e Y')` traduce la fecha al español (ej. *"Viernes, 20 de junio de 2026"*).
  * **Formateo de Hora:** `Carbon::parse(...)->format('g:i a')` muestra la hora en formato legible de 12 horas (ej. `10:00 am`).
  * **Botones de acción:** *"Ver detalles"* y *"Reprogramar / Cancelar"* que conducen a la pantalla de gestión de citas.
* **`@else` (Estado Vacío):** Si la clienta no tiene citas agendadas, no muestra un espacio en blanco; muestra un círculo morado con un ícono, el mensaje amigable *"No tienes citas próximas agendadas"* y un botón directo *"Agendar mi cita ahora"*.

---

## 4. Sección 2: Catálogo Destacado (Líneas 111 - 174)

```blade
        <!-- Catálogo Destacado (4 en fila como en la referencia) -->
        <div class="destacado-card">
            <div class="destacado-header">
                <h3>Catálogo destacado</h3>
                <a href="{{ route('cliente.catalogo') }}">Ver catálogo completo <i class="fa-solid fa-chevron-right" style="font-size:10px;"></i></a>
            </div>

            <div class="destacado-grid">
                @if (isset($serviciosDestacados) && count($serviciosDestacados) > 0)
                    @foreach ($serviciosDestacados as $serv)
                        @php
                            $imgServ = asset('img/volumen_ruso.jpg');
                            if (!empty($serv->imagen_url)) {
                                $imgServ = filter_var($serv->imagen_url, FILTER_VALIDATE_URL) ? $serv->imagen_url : asset($serv->imagen_url);
                            }
                        @endphp
                        <a href="{{ route('cliente.agendar', ['servicio_id' => $serv->id]) }}" class="serv-dest-item">
                            <img src="{{ $imgServ }}" alt="{{ $serv->nombre }}" class="serv-dest-img">
                            <div class="serv-dest-body">
                                <div class="serv-dest-name">{{ $serv->nombre }}</div>
                                <div class="serv-dest-price">${{ number_format($serv->precio, 0, ',', '.') }}</div>
                            </div>
                        </a>
                    @endforeach
                @else
                    <a href="{{ route('cliente.agendar') }}" class="serv-dest-item">
                        <img src="{{ asset('img/volumen_ruso.jpg') }}" alt="Extensiones Volumen Ruso" class="serv-dest-img">
                        <div class="serv-dest-body">
                            <div class="serv-dest-name">Extensiones Volumen Ruso</div>
                            <div class="serv-dest-price">$220.000</div>
                        </div>
                    </a>
                    <a href="{{ route('cliente.agendar') }}" class="serv-dest-item">
                        <img src="{{ asset('img/extensiones_clasicas.jpg') }}" alt="Extensiones Clásicas" class="serv-dest-img">
                        <div class="serv-dest-body">
                            <div class="serv-dest-name">Extensiones Clásicas</div>
                            <div class="serv-dest-price">$180.000</div>
                        </div>
                    </a>
                    <a href="{{ route('cliente.agendar') }}" class="serv-dest-item">
                        <img src="{{ asset('img/diseno_cejas.jpg') }}" alt="Diseño de Cejas con Henna" class="serv-dest-img">
                        <div class="serv-dest-body">
                            <div class="serv-dest-name">Diseño de Cejas con Henna</div>
                            <div class="serv-dest-price">$80.000</div>
                        </div>
                    </a>
                    <a href="{{ route('cliente.agendar') }}" class="serv-dest-item">
                        <img src="{{ asset('img/manicure_semi.jpg') }}" alt="Manicure Semipermanente" class="serv-dest-img">
                        <div class="serv-dest-body">
                            <div class="serv-dest-name">Manicure Semipermanente</div>
                            <div class="serv-dest-price">$90.000</div>
                        </div>
                    </a>
                @endif
            </div>

            <div class="carousel-dots">
                <span class="dot active"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>

    </div>
```

### ¿Qué hace cada parte?
* **`destacado-header`:** Cabecera con título y enlace *"Ver catálogo completo"* a `route('cliente.catalogo')`.
* **`@if (isset($serviciosDestacados) && count($serviciosDestacados) > 0)`:**
  * Si el controlador envió servicios reales de la base de datos, ejecuta un `@foreach`.
  * **Lógica de imagen segura (`@php`):** Comprueba si la imagen es una URL externa válida (`filter_var`) o una ruta local (`asset(...)`).
  * **Enlace con ID preseleccionado:** `route('cliente.agendar', ['servicio_id' => $serv->id])`, permitiendo que la clienta agende ese servicio específico con 1 clic.
  * **Formato de moneda:** `number_format($serv->precio, 0, ',', '.')` para separar miles con puntos (ej. `$180.000`).
* **`@else`:** Si aún no hay servicios cargados en el sistema, muestra 4 servicios predeterminados (Volumen Ruso, Clásicas, Cejas con Henna, Manicure) para que la pantalla nunca pierda su atractivo visual.
* **`<div class="carousel-dots">`:** Indicadores circulares de paginación visual.

---

## 5. Sección 3: Resumen de Citas y Promociones (Líneas 176 - 224)

```blade
    <!-- ══════════════════════════════════════════
         SECCIÓN 3: BOTTOM GRID (RESUMEN + PROMOS + ACTIVIDAD)
    ══════════════════════════════════════════ -->
    <div class="bottom-grid">

        <!-- Resumen de Citas + Promociones Especiales -->
        <div>
            <div class="section-label"><i class="fa-regular fa-calendar"></i> Resumen de citas</div>
            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-icon purple"><i class="fa-solid fa-calendar-check"></i></div>
                    <div>
                        <div class="stat-num">{{ $totalCitas }}</div>
                        <div class="stat-title">TOTAL CITAS</div>
                        <div class="stat-sub green">{{ !empty($proximaCita) ? 'Próxima confirmada' : 'Al día' }}</div>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon orange"><i class="fa-regular fa-clock"></i></div>
                    <div>
                        <div class="stat-num">{{ $citasPendientes }}</div>
                        <div class="stat-title">PENDIENTES</div>
                        <div class="stat-sub orange">Por atender</div>
                    </div>
                </div>

                <div class="stat-box">
                    <div class="stat-icon green"><i class="fa-regular fa-circle-check"></i></div>
                    <div>
                        <div class="stat-num">{{ $citasCompletadas }}</div>
                        <div class="stat-title">COMPLETADAS</div>
                        <div class="stat-sub purple">En tu historial</div>
                    </div>
                </div>
            </div>

            <!-- Promociones Especiales Banner -->
            <div class="promo-card">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div class="promo-badge">15%<br>OFF</div>
                    <div class="promo-info">
                        <h4>En extensiones de pestañas</h4>
                        <p>Aplica para Volumen Ruso y Clásicas<br><small>Válido hasta fin de mes</small></p>
                    </div>
                </div>
                <a href="{{ route('cliente.catalogo') }}" class="btn-promo">Ver promociones</a>
            </div>
        </div>
```

### ¿Qué hace cada parte?
* **`<div class="stats-row">`:** Fila horizontal con 3 tarjetas estadísticas:
  1. **TOTAL CITAS (`{{ $totalCitas }}`):** Número total de citas agendadas históricamente por la clienta.
  2. **PENDIENTES (`{{ $citasPendientes }}`):** Citas reservadas esperando fecha o confirmación.
  3. **COMPLETADAS (`{{ $citasCompletadas }}`):** Procedimientos que ya se realizó.
* **`<div class="promo-card">`:** Banner promocional con:
  * **Badge animado (`15% OFF`):** Destaca el descuento.
  * **Texto explicativo:** Condiciones de la oferta (Volumen Ruso y Clásicas hasta fin de mes).
  * **Botón `btn-promo`:** Enlace directo al catálogo para aprovechar la promoción.

---

## 6. Sección 3: Actividad Reciente de la Clienta (Líneas 226 - 264)

```blade
        <!-- Actividad Reciente -->
        <div class="actividad-card">
            <div>
                <div class="actividad-header">
                    <h3><i class="fa-solid fa-rotate-left"></i> Actividad reciente</h3>
                    <a href="{{ route('cliente.citas') }}">Ver todo</a>
                </div>

                <div class="actividad-list">
                    @if (isset($actividadReciente) && count($actividadReciente) > 0)
                        @foreach ($actividadReciente as $act)
                            <div class="actividad-item">
                                <div class="act-icon {{ $act['color'] }}"><i class="{{ $act['icono'] }}"></i></div>
                                <div class="act-info">
                                    <div class="act-title">{{ $act['titulo'] }}</div>
                                    <div class="act-desc">{{ $act['desc'] }}</div>
                                </div>
                                <div class="act-time">{{ $act['tiempo'] }}</div>
                            </div>
                        @endforeach
                    @else
                        <div class="actividad-item">
                            <div class="act-icon purple"><i class="fa-regular fa-calendar"></i></div>
                            <div class="act-info">
                                <div class="act-title">Bienvenida a Oh La Lash</div>
                                <div class="act-desc">Explora nuestros servicios y agenda tu primera cita</div>
                            </div>
                            <div class="act-time">Hoy</div>
                        </div>
                    @endif
                </div>
            </div>

            <a href="{{ route('cliente.citas') }}" class="btn-historial-btn">Ver historial completo</a>
        </div>

    </div>

</div> <!-- Cierre de .page-content -->
```

### ¿Qué hace cada parte?
* **`actividad-header`:** Título con ícono de historial y enlace rápido *"Ver todo"*.
* **`@foreach ($actividadReciente as $act)`:** Recorre la lista de eventos enviada por el controlador:
  * **`$act['color']` & `$act['icono']`:** Define el color del círculo y el ícono de FontAwesome (ej. `purple`, `green`, `orange`).
  * **`$act['titulo']` & `$act['desc']`:** Qué ocurrió (ej. *"Cita agendada"*, *"Reseña publicada"*).
  * **`$act['tiempo']`:** Tiempo relativo (ej. *"hace 2 horas"* o *"ayer"*).
* **`@else`:** Mensaje de bienvenida para clientas nuevas que aún no tienen actividad registrada.
* **`btn-historial-btn`:** Botón al pie de la tarjeta para consultar el historial completo.
* **`</div> <!-- Cierre de .page-content -->`:** Cierra el contenedor principal de la vista.

---

## 7. Modal Emergente de Agendas Disponibles (Líneas 267 - 325)

```blade
<!-- ══════════════════════════════════════════
     MODAL AGENDAS DISPONIBLES
══════════════════════════════════════════ -->
<div class="modal-agenda-overlay" id="modalAgenda">
    <div class="modal-agenda-content">
        <div class="modal-agenda-header">
            <h3><i class="fa-solid fa-calendar-days"></i> Agendas y Horarios Disponibles</h3>
            <button type="button" class="modal-agenda-close" onclick="cerrarModalAgenda()" title="Cerrar">&times;</button>
        </div>
        <div class="modal-agenda-body">
            @if (!empty($agendasDisponibles) && count($agendasDisponibles) > 0)
                <div class="agenda-filter-bar">
                    <input type="text" id="filtroAgenda" onkeyup="filtrarAgendas()" placeholder="🔍 Buscar por especialista, fecha o turno...">
                </div>
                <div class="agenda-grid" id="contenedorAgendas">
                    @foreach ($agendasDisponibles as $agenda)
                        @php
                            $fechaFmt = !empty($agenda['fecha']) ? \Carbon\Carbon::parse($agenda['fecha'])->format('d/m/Y') : now()->format('d/m/Y');
                            $horaInicio = !empty($agenda['hora_inicio']) ? \Carbon\Carbon::parse($agenda['hora_inicio'])->format('g:i A') : '09:00 AM';
                            $horaFin = !empty($agenda['hora_fin']) ? \Carbon\Carbon::parse($agenda['hora_fin'])->format('g:i A') : '10:00 AM';
                            $trabNombre = $agenda['trabajador_nombre'] ?? 'Lorena Ayala';
                            $especialidad = $agenda['especialidad'] ?? 'Lash Master';
                        @endphp
                        <div class="agenda-card" data-search="{{ strtolower($trabNombre . ' ' . $fechaFmt . ' ' . $especialidad) }}">
                            <div>
                                <div class="agenda-card-header">
                                    <div class="agenda-fecha">
                                        <i class="fa-regular fa-calendar"></i> {{ $fechaFmt }}
                                    </div>
                                    <span class="badge-disponible">Disponible</span>
                                </div>
                                <div class="agenda-details">
                                    <div class="agenda-detail-item">
                                        <i class="fa-regular fa-clock"></i> {{ $horaInicio }} - {{ $horaFin }}
                                    </div>
                                    <div class="agenda-detail-item">
                                        <i class="fa-solid fa-user-nurse"></i> {{ $trabNombre }} ({{ $especialidad }})
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('cliente.agendar') }}" class="btn-agendar-slot">
                                <i class="fa-solid fa-calendar-plus"></i> Agendar este horario
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><i class="fa-solid fa-calendar-check"></i></div>
                    <h4>¡Horarios de atención disponibles!</h4>
                    <p>Atendemos de Lunes a Sábado de 8:00 AM a 6:00 PM con previa cita.</p>
                    <br>
                    <a href="{{ route('cliente.agendar') }}" class="btn-subir-submit" style="margin: 0 auto; width: auto; padding: 10px 26px; text-decoration: none;">
                        <i class="fa-solid fa-calendar-plus"></i> Agendar Cita en Línea
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
```

### ¿Qué hace cada parte?
* **`<div class="modal-agenda-overlay" id="modalAgenda">`:** Capa oscura semitransparente que cubre la pantalla al abrirse.
* **`<button class="modal-agenda-close" onclick="cerrarModalAgenda()">`:** Botón "X" para cerrar el modal.
* **`<input id="filtroAgenda" onkeyup="filtrarAgendas()">`:** Buscador en tiempo real que filtra los turnos a medida que la usuaria teclea.
* **Atributo `data-search`:** Guarda en minúsculas el nombre de la lashista, la fecha y la especialidad para que el filtro de JavaScript compare al instante.
* **Tarjeta de Turno (`.agenda-card`):** Muestra la fecha formateada (`$fechaFmt`), el rango de horas (`$horaInicio - $horaFin`), la lashista y un botón directo *"Agendar este horario"*.
* **Estado Alternativo (`@else`):** Si no hay turnos específicos cargados, muestra el horario general del salón (Lunes a Sábado 8am a 6pm) y un botón de agendamiento.

---

## 8. Scripts de Interacción en JavaScript (Líneas 327 - 374)

```blade
<!-- Scripts de Interacción para el Dashboard del Cliente -->
<script>
    function abrirModalAgenda() {
        const modal = document.getElementById('modalAgenda');
        if (modal) {
            modal.classList.add('activo');
            document.body.style.overflow = 'hidden';
        }
    }

    function cerrarModalAgenda() {
        const modal = document.getElementById('modalAgenda');
        if (modal) {
            modal.classList.remove('activo');
            document.body.style.overflow = '';
        }
    }

    function filtrarAgendas() {
        const input = document.getElementById('filtroAgenda');
        if (!input) return;
        const query = input.value.toLowerCase().trim();
        const cards = document.querySelectorAll('#contenedorAgendas .agenda-card');

        cards.forEach(card => {
            const searchData = (card.getAttribute('data-search') || '').toLowerCase();
            card.style.display = (!query || searchData.includes(query)) ? 'flex' : 'none';
        });
    }

    // Cerrar modal al hacer clic en fondo
    const modalAgenda = document.getElementById('modalAgenda');
    if (modalAgenda) {
        modalAgenda.addEventListener('click', function(e) {
            if (e.target === modalAgenda) cerrarModalAgenda();
        });
    }

    @if(session('success'))
        Swal.fire({
            title: '¡Operación Exitosa!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#7c1db8',
            confirmButtonText: 'Entendido'
        });
    @endif
</script>
```

### ¿Qué hace cada función JavaScript?
1. **`abrirModalAgenda()`:**
   * Busca el elemento con id `#modalAgenda`.
   * Le agrega la clase CSS `.activo` (que cambia la opacidad de 0 a 1 y hace visible el modal con animación suave).
   * Pone `document.body.style.overflow = 'hidden'` para que el fondo de la página no se mueva mientras la clienta navega en el modal.
2. **`cerrarModalAgenda()`:**
   * Quita la clase `.activo`.
   * Restablece el scroll de la página (`overflow = ''`).
3. **`filtrarAgendas()`:**
   * Obtiene el texto escrito en `#filtroAgenda` y lo pasa a minúsculas (`toLowerCase().trim()`).
   * Recorre todas las tarjetas `.agenda-card`.
   * Lee el atributo `data-search` de cada una. Si contiene el texto buscado, la muestra (`display = 'flex'`); si no, la oculta (`display = 'none'`).
4. **Cierre al hacer clic en el fondo oscuro:**
   * Si la usuaria hace clic fuera de la tarjeta blanca del modal (`e.target === modalAgenda`), el modal se cierra solo sin obligarla a buscar la X.
5. **Alerta SweetAlert2 (`@if(session('success'))`):**
   * Si una acción anterior (como agendar una cita o calificar) dejó un mensaje de éxito en la sesión de Laravel, se dispara automáticamente una ventana emergente moderna de SweetAlert2 con color morado de la marca (`#7c1db8`).

---

## 9. Footer y Cierre de Estructura (Líneas 376 - 383)

```blade
@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>
```

### ¿Qué hace cada línea?
* **`@include('layouts.footer')`:** Incluye scripts comunes y pie de página.
* **Cierre de contenedores:** Cierra los `<div>` maestros abiertos en el header y sidebar para que el diseño no se descuadre en ningún navegador.

---

## 10. Tabla Resumen de Variables y Rutas

| Elemento | Tipo | Descripción |
| :--- | :--- | :--- |
| **`$proximaCita`** | Array / Objeto | Almacena servicio, especialista, fecha, hora y foto de la cita más cercana. |
| **`$serviciosDestacados`** | Colección | Lista de los 4 servicios más solicitados para el carrusel de catálogo. |
| **`$totalCitas`** | Entero | Número total de citas agendadas por la clienta. |
| **`$citasPendientes`** | Entero | Citas reservadas esperando asistencia o confirmación. |
| **`$citasCompletadas`** | Entero | Cantidad de citas ya atendidas en el historial. |
| **`$actividadReciente`** | Array | Eventos cronológicos con título, descripción, ícono, color y tiempo relativo. |
| **`$agendasDisponibles`** | Array | Horarios y turnos libres de las lashistas para el modal. |
| **`route('cliente.agendar')`** | Ruta GET | Lleva al formulario de reserva guiada en 4 pasos. |
| **`route('cliente.catalogo')`** | Ruta GET | Lleva a la galería de servicios y precios. |
| **`route('cliente.citas')`** | Ruta GET | Lleva al panel de seguimiento de citas. |
