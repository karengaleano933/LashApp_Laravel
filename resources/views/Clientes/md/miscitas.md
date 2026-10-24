# Explicación Código por Código de `miscitas.blade.php` (En mis palabras)

Este documento detalla **cada bloque, línea y función de código** que compone el archivo [`miscitas.blade.php`](file:///c:/laragon/www/LashApp/resources/views/Clientes/miscitas.blade.php), el centro de control donde las clientas revisan sus citas agendadas, filtran por estado (Próximas, Pendientes, Completadas, Canceladas), visualizan fechas en un mini-calendario conectado a la base de datos y gestionan cancelaciones o detalles.

---

## 1. Encabezado, Layouts y Preparación de Citas (Líneas 1 - 83)

```blade
<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Mis Citas'])
@include('layouts.sidebarcliente')

@vite(['resources/css/miscitas.css'])

@php
    use Carbon\Carbon;

    $citasListado = [];

    // Mapear citas reales de la Base de Datos
    if (isset($citas) && $citas->count() > 0) {
        $citasListado = $citas->map(function ($c) {
            $fechaObj = $c->fecha ? Carbon::parse($c->fecha) : Carbon::today();
            
            // Lógica de asignación de foto según el nombre del servicio
            $img = asset('img/volumen_ruso.jpg');
            if (!empty($c->servicio?->imagen_url)) {
                $img = filter_var($c->servicio->imagen_url, FILTER_VALIDATE_URL) 
                    ? $c->servicio->imagen_url 
                    : asset($c->servicio->imagen_url);
            } else {
                $sNom = strtolower($c->servicio?->nombre ?? '');
                if (str_contains($sNom, 'ceja') || str_contains($sNom, 'henna')) {
                    $img = asset('img/diseno_cejas.jpg');
                } elseif (str_contains($sNom, 'clásica') || str_contains($sNom, 'clasica')) {
                    $img = asset('img/extensiones_clasicas.jpg');
                } elseif (str_contains($sNom, 'mani') || str_contains($sNom, 'semi')) {
                    $img = asset('img/manicure_semi.jpg');
                }
            }

            // Datos de la lashista y normalización de estados
            $profesional = $c->trabajador?->user 
                ? trim($c->trabajador->user->name . ' ' . ($c->trabajador->user->apellido ?? '')) 
                : 'Lorena Ayala';

            $estadoLower = strtolower($c->estado ?? 'pendiente');
            $estadoLabel = match ($estadoLower) {
                'confirmada', 'confirmado' => 'Confirmada',
                'completada', 'completado', 'finalizado' => 'Completada',
                'cancelada', 'cancelado' => 'Cancelada',
                default => 'Pendiente',
            };

            // Agrupación en pestañas (tabs)
            $hoy = Carbon::today();
            $grupos = [];
            if ($estadoLower === 'cancelado' || $estadoLower === 'cancelada') {
                $grupos[] = 'canceladas';
            } elseif (in_array($estadoLower, ['finalizado', 'completado', 'completada'])) {
                $grupos[] = 'completadas';
            } else {
                if ($fechaObj->greaterThanOrEqualTo($hoy) || in_array($estadoLower, ['confirmado', 'confirmada'])) {
                    $grupos[] = 'proximas';
                }
                if ($estadoLower === 'pendiente') {
                    $grupos[] = 'pendientes';
                }
            }
            if (empty($grupos)) $grupos[] = 'proximas';

            return [
                'id' => $c->id,
                'servicio' => $c->servicio?->nombre ?? 'Servicio de Belleza',
                'servicio_id' => $c->servicio_id ?? 1,
                'precio' => (float)($c->pago?->monto ?? $c->servicio?->precio ?? 130000),
                'profesional' => $profesional,
                'ubicacion' => 'Oh La Lash Studio',
                'fecha_fmt' => $fechaObj->format('d/m/Y'),
                'fecha_iso' => $fechaObj->format('Y-m-d'),
                'hora_fmt' => $c->hora_inicio ? Carbon::parse($c->hora_inicio)->format('h:i a') : '10:00 am',
                'estado_slug' => $estadoLower,
                'estado_label' => $estadoLabel,
                'grupo' => implode(' ', $grupos),
                'img' => $img,
                'metodo_pago' => $c->pago?->metodo_pago ?? 'Tarjeta',
            ];
        })->toArray();
    }

    $fechasCitasJson = json_encode(array_values(array_unique(array_column($citasListado, 'fecha_iso'))));
@endphp
```

### ¿Qué hace cada parte?
* **Mapeo de Citas:** Transforma los registros de Eloquent en un array optimizado para la vista.
* **Asignación inteligente de imagen:** Si el servicio no tiene foto en la base de datos, analiza palabras clave en el título (`ceja`, `clásica`, `mani`) para mostrar la foto correcta de pestañas o cejas.
* **Agrupación en Pestañas (`$grupos`):** Clasifica la cita en `proximas`, `pendientes`, `completadas` o `canceladas`. Un servicio puede pertenecer a más de un grupo si aplica (ej. próxima y pendiente a la vez).
* **`$fechasCitasJson`:** Extrae las fechas en formato `YYYY-MM-DD` en un JSON que alimentará el mini-calendario de JavaScript para marcar los días con cita.

---

## 2. Pestañas de Navegación por Estado (Líneas 104 - 117)

```blade
    <div class="miscitas-tabs-bar">
        <button type="button" class="miscitas-tab-btn active" data-tab="proximas" onclick="cambiarPestanaCitas('proximas', this)">
            <i class="fa-regular fa-calendar"></i> Próximas
        </button>
        <button type="button" class="miscitas-tab-btn" data-tab="pendientes" onclick="cambiarPestanaCitas('pendientes', this)">
            <i class="fa-regular fa-clock"></i> Pendientes
        </button>
        <button type="button" class="miscitas-tab-btn" data-tab="completadas" onclick="cambiarPestanaCitas('completadas', this)">
            <i class="fa-regular fa-circle-check"></i> Completadas
        </button>
        <button type="button" class="miscitas-tab-btn" data-tab="canceladas" onclick="cambiarPestanaCitas('canceladas', this)">
            <i class="fa-regular fa-circle-xmark"></i> Canceladas
        </button>
    </div>
```

* Cuatro botones que activan la función JavaScript `cambiarPestanaCitas(tab, this)` para filtrar las tarjetas instantáneamente sin recargar la página.

---

## 3. Columna Izquierda: Tarjetas de Cita (Líneas 131 - 204)

```blade
    <div class="citas-cards-list" id="listaCitasContenedor">
        @foreach ($citasListado as $item)
            <div class="cita-card-box" 
                 data-tab-grupo="{{ $item['grupo'] }}"
                 data-id="{{ $item['id'] }}"
                 style="{{ str_contains($item['grupo'], 'proximas') ? 'display: block;' : 'display: none;' }}">

                <div class="cita-card-main">
                    <div class="cita-card-img-wrap">
                        <img src="{{ $item['img'] }}" alt="{{ $item['servicio'] }}" class="cita-card-img" loading="lazy">
                    </div>

                    <div class="cita-card-content">
                        <div class="cita-card-top-row">
                            <span class="badge-cita-status {{ $item['estado_slug'] }}">
                                {{ $item['estado_label'] }}
                            </span>
                            <div class="cita-card-price">
                                ${{ number_format($item['precio'], 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="cita-card-title">{{ $item['servicio'] }}</div>

                        <div class="cita-card-meta">
                            <span><i class="fa-regular fa-user"></i> {{ $item['profesional'] }}</span>
                            <span><i class="fa-solid fa-location-dot"></i> {{ $item['ubicacion'] }}</span>
                            <span><i class="fa-regular fa-calendar"></i> {{ $item['fecha_fmt'] }}</span>
                            <span><i class="fa-regular fa-clock"></i> {{ $item['hora_fmt'] }}</span>
                        </div>

                        <div class="cita-card-notice">
                            <i class="fa-solid fa-circle-info"></i> Te recomendamos llegar 10 minutos antes de tu cita.
                        </div>
                    </div>
                </div>

                <div class="cita-card-actions">
                    <button type="button" class="btn-card-action" onclick="abrirModalDetalle(...)">
                        Ver detalles
                    </button>
                    <a href="{{ route('cliente.agendar', ['servicio_id' => $item['servicio_id']]) }}" class="btn-card-action">
                        Reprogramar
                    </a>
                    @if (in_array($item['estado_slug'], ['pendiente', 'confirmada', 'confirmado']))
                        <a href="{{ route('citas.cancelar', $item['id']) }}" class="btn-card-action btn-danger" onclick="return confirmarCancelarCita(event, this.href)">
                            <i class="fa-regular fa-trash-can"></i> Cancelar cita
                        </a>
                    @endif
                </div>
            </div>
        @endforeach

        <!-- Mensaje cuando la pestaña está vacía -->
        <div id="citasVaciasMsg" style="...">
            <!-- Icono y mensaje amigable con botón para agendar -->
        </div>
    </div>
```

### ¿Qué hace cada parte?
* **Atributo `data-tab-grupo`:** Almacena los grupos a los que pertenece la tarjeta (ej. `proximas pendientes`).
* **`badge-cita-status`:** Badge de color según el estado (`confirmada`, `pendiente`, `completada`, `cancelada`).
* **Botón "Ver detalles":** Pasa todos los datos por parámetros a la función `abrirModalDetalle()`.
* **Botón "Reprogramar":** Enlaza a `cliente.agendar` con el servicio listo para reservar una nueva fecha.
* **Botón "Cancelar cita":** Se muestra solo en citas pendientes o confirmadas y dispara una confirmación segura con SweetAlert2.

---

## 4. Columna Derecha: Mini Calendario y Resumen Estadístico (Líneas 207 - 291)

```blade
    <div class="miscitas-sidebar">

        <!-- WIDGET 1: MINI CALENDARIO CON MARCADORES -->
        <div class="widget-box">
            <div class="widget-box-header"><i class="fa-regular fa-calendar"></i> Calendario rápido</div>
            <div class="cal-nav-row">
                <button type="button" onclick="cambiarMesRapido(-1)"><i class="fa-solid fa-chevron-left"></i></button>
                <div class="cal-month-name" id="calRapidoMesNombre">Junio 2026</div>
                <button type="button" onclick="cambiarMesRapido(1)"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
            <div class="cal-grid-days" id="calRapidoGrid"></div>
        </div>

        <!-- WIDGET 2: RESUMEN DE CITAS -->
        <div class="widget-box">
            <div class="widget-box-header"><i class="fa-solid fa-chart-pie"></i> Resumen de citas</div>
            <div class="resumen-citas-list">
                <div class="resumen-citas-row"><span>Próximas</span><span>{{ $countProximas ?? 0 }}</span></div>
                <div class="resumen-citas-row"><span>Pendientes</span><span>{{ $countPendientes ?? 0 }}</span></div>
                <div class="resumen-citas-row"><span>Completadas</span><span>{{ $countCompletadas ?? 0 }}</span></div>
                <div class="resumen-citas-row"><span>Canceladas</span><span>{{ $countCanceladas ?? 0 }}</span></div>
            </div>
            <a href="{{ route('cliente.agendar') }}" class="btn-agendar-nueva">Agendar nueva cita</a>
        </div>
    </div>
```

* **Mini Calendario:** Se genera con JavaScript y compara cada día contra el arreglo de fechas de la base de datos (`fechasCitasBD`). Los días con cita reciben la clase `.active-day` con un punto morado brillante.
* **Resumen de citas:** Contadores de citas por estado y botón directo para agendar una nueva.

---

## 5. Modal de Detalles de Cita (Líneas 298 - 347)

```blade
<div class="modal-detalles-overlay" id="modalDetallesCita" style="display: none;">
    <div class="modal-detalles-container">
        <!-- Servicio, Profesional, Fecha, Hora, Ubicación, Estado, Precio y Método de Pago -->
    </div>
</div>
```
* Ventana modal que muestra la información completa del servicio, la dirección del estudio y el método de pago utilizado.

---

## 6. Scripts de JavaScript (Líneas 352 - 508)

```javascript
    // 1. Alternar pestañas y ocultar/mostrar tarjetas
    function cambiarPestanaCitas(tab, btn) {
        // Actualiza el botón activo y el título de la vista
        // Lee data-tab-grupo y pone display block o none
        // Si visibles === 0 muestra citasVaciasMsg
    }

    // 2. Mini Calendario con marcadores
    const fechasCitasBD = {!! $fechasCitasJson ?? '[]' !!};
    function renderizarMiniCalendario() {
        // Genera celdas del mes
        // Si fechasCitasBD incluye la fecha del día, agrega la clase .active-day
    }

    // 3. Abrir y cerrar modal de detalles
    function abrirModalDetalle(id, serv, prof, fecha, hora, precio, estado, pago) {
        // Inyecta los textos en los span del modal y lo muestra con flex
    }

    // 4. Cancelación con SweetAlert2
    function confirmarCancelarCita(event, url) {
        event.preventDefault();
        Swal.fire({
            title: '¿Deseas cancelar esta cita?',
            text: 'Esta acción liberará el espacio en la agenda de la especialista.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            confirmButtonText: 'Sí, cancelar cita'
        }).then((res) => {
            if (res.isConfirmed) window.location.href = url;
        });
    }
```
