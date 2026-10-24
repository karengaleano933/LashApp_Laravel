# Explicación Código por Código de `agendarcitas.blade.php` (En mis palabras)

Este documento detalla **cada bloque, línea y función de código** que compone el archivo [`agendarcitas.blade.php`](file:///c:/laragon/www/LashApp/resources/views/Clientes/agendarcitas.blade.php), el sistema guiado donde las clientas seleccionan servicio, especialista, fecha en un calendario interactivo, hora y método de pago (Tarjeta virtual 3D, PSE, Nequi/Daviplata o Efectivo) con confirmación AJAX.

---

## 1. Encabezado, Inclusiones y Preparación de Datos (Líneas 1 - 111)

```blade
<!-- Incluir Header superior y Barra lateral de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Agendar Cita'])
@include('layouts.sidebarcliente')

@vite(['resources/css/agendarcitas.css'])

@php
    use Carbon\Carbon;

    // Servicios para agendar (reales de BD o fallback demo de alta calidad)
    if (!isset($serviciosDisponibles)) {
        if (isset($servicios) && $servicios->count() > 0) {
            $serviciosDisponibles = $servicios->map(function ($s) {
                // Mapeo seguro de imagen, duración y precio...
            })->toArray();
        } else {
            // Catálogo demo de 6 servicios con fotos reales...
        }
    }

    // Preseleccionar servicio si viene por URL (ej. ?servicio_id=2)
    $servicioInicial = null;
    if (!empty($servicioIdSeleccionado)) {
        foreach ($serviciosDisponibles as $sd) {
            if ($sd['id'] == $servicioIdSeleccionado) {
                $servicioInicial = $sd;
                break;
            }
        }
    }
    if (!$servicioInicial) {
        $servicioInicial = $serviciosDisponibles[0] ?? [...];
    }

    $fechaHoy = Carbon::now();
    $clienteModel = $cliente ?? \App\Models\Cliente::firstOrCreate(['user_id' => auth()->id()]);
    $clienteId = $clienteModel?->id ?? 1;
    $primerTrabajador = (isset($trabajadores) && $trabajadores->count() > 0) ? $trabajadores->first() : null;
    $trabajadorIdInicial = $primerTrabajador?->id ?? 1;
@endphp
```

### ¿Qué hace cada parte?
* **`@vite(['resources/css/agendarcitas.css'])`:** Carga los estilos de la cuadrícula de 4 columnas, la tarjeta de crédito 3D interactiva y el calendario.
* **Preselección de Servicio:** Si la clienta vino desde el catálogo haciendo clic en *"Agendar Cita"* de un servicio específico, `$servicioIdSeleccionado` busca ese tratamiento y lo selecciona por defecto.
* **`$clienteModel = Cliente::firstOrCreate(...)`:** Garantiza que el usuario conectado tenga un registro asociado en la tabla `clientes` para evitar fallos de clave foránea.
* **`$primerTrabajador`:** Selecciona a la primera lashista disponible como valor inicial seguro.

---

## 2. Stepper Superior Visual de 4 Pasos (Líneas 119 - 174)

```blade
    <div class="agendar-header">
        <div class="agendar-header-left">
            <div class="agendar-header-icon"><i class="fa-regular fa-calendar-check"></i></div>
            <div>
                <h1>Agendar Cita</h1>
                <p>Reserva tu cita de manera fácil y rápida</p>
            </div>
        </div>
        <div class="agendar-header-right">
            <button type="button" class="btn-miscitas-toggle" onclick="abrirModalMisCitas()">
                <i class="fa-solid fa-calendar-check"></i> Mis Citas ({{ count($misCitas ?? []) }})
            </button>
        </div>
    </div>

    <div class="booking-stepper-card">
        <div class="step-item active">
            <div class="step-num">1</div>
            <div class="step-text">
                <div class="step-title">Seleccionar servicio</div>
                <div class="step-sub">Elige el procedimiento</div>
            </div>
        </div>
        <div class="step-arrow"><i class="fa-solid fa-chevron-right"></i></div>

        <div class="step-item active">
            <div class="step-num">2</div>
            <div class="step-text">
                <div class="step-title">Elegir fecha y hora</div>
                <div class="step-sub">Consulta disponibilidad</div>
            </div>
        </div>
        <div class="step-arrow"><i class="fa-solid fa-chevron-right"></i></div>

        <div class="step-item">
            <div class="step-num">3</div>
            <div class="step-text">
                <div class="step-title">Información adicional</div>
                <div class="step-sub">Cuéntanos más</div>
            </div>
        </div>
        <div class="step-arrow"><i class="fa-solid fa-chevron-right"></i></div>

        <div class="step-item">
            <div class="step-num">4</div>
            <div class="step-text">
                <div class="step-title">Revisión, pago y confirmación</div>
                <div class="step-sub">Revisa y confirma</div>
            </div>
        </div>
    </div>
```

### ¿Qué hace cada parte?
* **`btn-miscitas-toggle`:** Botón superior con un contador que abre una ventana modal con las citas que la clienta ya tiene agendadas.
* **`booking-stepper-card`:** Barra de progreso visual con 4 círculos numerados y títulos que indican el flujo lógico de reserva.

---

## 3. Formulario y Variables Ocultas (Líneas 176 - 186)

```blade
    <form action="{{ route('citas.store') }}" method="POST" id="formAgendarCita">
        @csrf
        <input type="hidden" name="id_cliente" id="inputClienteId" value="{{ $clienteId }}">
        <input type="hidden" name="id_servicio" id="inputServicioId" value="{{ $servicioInicial['id'] }}">
        <input type="hidden" name="id_trabajador" id="inputTrabajadorId" value="{{ $trabajadorIdInicial }}">
        <input type="hidden" name="fecha_cita" id="inputFechaCita" value="{{ $fechaHoy->format('Y-m-d') }}">
        <input type="hidden" name="hora_inicio" id="inputHoraInicio" value="08:00">
        <input type="hidden" name="metodo_pago" id="inputMetodoPago" value="tarjeta">
        <input type="hidden" name="estado" value="pendiente">
```

### ¿Qué hace cada campo?
* **`#inputClienteId`:** ID de la clienta logueada.
* **`#inputServicioId`:** ID del tratamiento seleccionado (se actualiza al hacer clic en las tarjetas).
* **`#inputTrabajadorId`:** ID de la lashista seleccionada.
* **`#inputFechaCita`:** Fecha en formato ISO `YYYY-MM-DD`.
* **`#inputHoraInicio`:** Hora en formato militar 24h `HH:MM` para la base de datos.
* **`#inputMetodoPago`:** Método elegido (`tarjeta`, `pse`, `digital` o `efectivo`).

---

## 4. Columna 1: Selección del Servicio (Líneas 192 - 225)

```blade
    <div class="agendar-col-card">
        <div class="col-header-title">1. Selecciona el servicio</div>

        <div class="col-search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="buscarServicioAgenda" placeholder="Buscar servicio..." onkeyup="filtrarServiciosAgenda()">
        </div>

        <div class="service-select-list" id="listaServiciosAgenda">
            @foreach ($serviciosDisponibles as $index => $s)
                <div class="service-card-option {{ $s['id'] == $servicioInicial['id'] ? 'selected' : '' }}"
                     data-id="{{ $s['id'] }}"
                     data-nombre="{{ $s['nombre'] }}"
                     data-precio="{{ $s['precio'] }}"
                     data-duracion="{{ $s['duracion'] }}"
                     data-img="{{ $s['img'] }}"
                     onclick="seleccionarServicio(this)">
                    
                    <img src="{{ $s['img'] }}" alt="{{ $s['nombre'] }}" class="service-option-thumb">
                    <div class="service-option-info">
                        <div class="service-option-name">{{ $s['nombre'] }}</div>
                        <div class="service-option-duration">{{ $s['duracion'] }}</div>
                        <div class="service-option-price">${{ number_format($s['precio'], 0, ',', '.') }}</div>
                    </div>
                    <div class="custom-radio-circle"></div>
                </div>
            @endforeach
        </div>
    </div>
```

* **Buscador en vivo (`filtrarServiciosAgenda()`):** Oculta o muestra opciones a medida que se teclea.
* **`onclick="seleccionarServicio(this)"`:** Ejecuta la función que marca el servicio activo, actualiza los campos ocultos y recalcula el resumen.

---

## 5. Columna 2: Profesional, Calendario y Pasarela Bancaria (Líneas 227 - 477)

### A. Selector de Especialista y Calendario Interactivo
```blade
    <div class="specialist-label">Selecciona la especialista:</div>
    <select class="specialist-select-box" id="selectEspecialista" onchange="cambiarEspecialista(this)">
        <option value="{{ $trabajadorIdInicial }}" data-nombre="Cualquier Profesional" selected>✦ Cualquier Profesional disponible</option>
        @foreach($trabajadores as $trab)
            <option value="{{ $trab->id }}" data-nombre="{{ $nombreTrab }}">{{ $nombreTrab }} ({{ $espTrab }})</option>
        @endforeach
    </select>

    <!-- Calendario interactivo -->
    <div class="cal-nav-bar">
        <button type="button" class="cal-nav-btn" onclick="cambiarMesCalendario(-1)"><i class="fa-solid fa-chevron-left"></i></button>
        <div class="cal-month-title" id="calMonthTitle">{{ ucfirst($fechaHoy->translatedFormat('F Y')) }}</div>
        <button type="button" class="cal-nav-btn" onclick="cambiarMesCalendario(1)"><i class="fa-solid fa-chevron-right"></i></button>
    </div>

    <div class="cal-matrix" id="diasCalendarioGrid"></div>
```
* **Navegación de mes:** Botones `-1` y `+1` que redibujan el mes actual.
* **`#diasCalendarioGrid`:** Contenedor donde JavaScript genera dinámicamente los días del mes y les agrega eventos de clic.

### B. Pasarela de Pago Bancario Simulada
```blade
    <div class="pasarela-pago-wrapper">
        <div class="pasarela-top-bar">
            <span><i class="fa-solid fa-circle-check"></i> Pago 100% Seguro</span>
            <span><i class="fa-solid fa-shield-halved"></i> SSL 256-bit</span>
        </div>

        <div class="pago-tabs-bar">
            <button type="button" class="pago-tab-btn active" data-metodo="tarjeta" onclick="cambiarMetodoPago('tarjeta', this)">Tarjeta</button>
            <button type="button" class="pago-tab-btn" data-metodo="pse" onclick="cambiarMetodoPago('pse', this)">PSE Bancos</button>
            <button type="button" class="pago-tab-btn" data-metodo="digital" onclick="cambiarMetodoPago('digital', this)">Digital</button>
            <button type="button" class="pago-tab-btn" data-metodo="efectivo" onclick="cambiarMetodoPago('efectivo', this)">Efectivo</button>
        </div>

        <!-- Tarjeta Virtual Interactiva -->
        <div class="virtual-card-wrap">
            <div class="virtual-card-body">
                <div class="vcard-brand" id="vcardBrandLogo">VISA</div>
                <div class="vcard-number" id="vcardNumberDisplay">•••• &nbsp; •••• &nbsp; •••• &nbsp; ••••</div>
                <div class="vcard-value" id="vcardHolderDisplay">NOMBRE EN LA TARJETA</div>
                <div class="vcard-value" id="vcardExpiryDisplay">MM/AA</div>
            </div>
        </div>
```
* **Tarjeta virtual 3D:** Simula una tarjeta bancaria en tiempo real: al escribir en los inputs del formulario, el plástico virtual se llena simultáneamente y detecta franquicias (Visa, Mastercard, Amex).
* **Paneles alternables:**
  * **Tarjeta:** Número, titular, vencimiento `MM/AA`, CVV y cuotas.
  * **PSE Bancos:** Selector con entidades colombianas (Bancolombia, Davivienda, etc.) y correo.
  * **Digital:** QR y número de cuenta para transferencias por Nequi o Daviplata.
  * **Efectivo:** Indicación de pago en recepción el día del servicio.

---

## 6. Columna 3: Franjas Horarias (Líneas 479 - 508)

```blade
    <div class="time-slots-list">
        @foreach ($horariosDisponibles as $index => $hora)
            <div class="time-slot-btn {{ $index === 0 ? 'selected' : '' }}"
                 data-hora="{{ $hora }}"
                 data-hora-24="{{ Carbon::parse($hora)->format('H:i') }}"
                 onclick="seleccionarHora(this)">
                <span>{{ $hora }}</span>
                <div class="slot-radio-dot"></div>
            </div>
        @endforeach
    </div>
```
* **Botones de turno:** Genera botones con las horas disponibles (de 8:00 am a 5:00 pm). Al hacer clic, `seleccionarHora(this)` guarda la hora en formato de 24h para la base de datos y la muestra en formato de 12 horas en el resumen.

---

## 7. Columna 4: Resumen de Cita y Confirmación (Líneas 510 - 582)

```blade
    <div class="resumen-details-list">
        <!-- Fecha, Hora, Profesional, Ubicación... -->
    </div>

    <div class="resumen-price-box">
        <div class="price-row-item">
            <span>Subtotal</span>
            <span id="resumenSubtotal">${{ number_format($servicioInicial['precio'], 0, ',', '.') }}</span>
        </div>
        <div class="price-row-item total">
            <span>Total a pagar</span>
            <span class="total-amount" id="resumenTotal">${{ number_format($servicioInicial['precio'], 0, ',', '.') }}</span>
        </div>
    </div>

    <button type="button" onclick="confirmarYAgendarCita(this)" class="btn-confirmar-reserva" id="btnConfirmarReserva">
        Confirmar y Continuar <i class="fa-solid fa-arrow-right"></i>
    </button>
```
* **Desglose en vivo:** Muestra foto, título, fecha elegida, hora, lashista y costo total.
* **`onclick="confirmarYAgendarCita(this)"`:** Dispara el proceso de envío mediante AJAX sin recargar la pantalla.

---

## 8. Modal de Mis Citas Agendadas (Líneas 584 - 643)

```blade
<div class="modal-miscitas-overlay" id="modalMisCitas" style="display: none;">
    <div class="modal-miscitas-container">
        <!-- Listado de citas de la clienta con botón de cancelación directa -->
    </div>
</div>
```
* Permite consultar reservas previas sin perder el progreso del agendamiento actual.

---

## 9. Scripts de JavaScript (Líneas 648 - 981)

```javascript
    // 1. Filtrar servicios en vivo
    function filtrarServiciosAgenda() { ... }

    // 2. Seleccionar servicio y sincronizar resumen
    function seleccionarServicio(element) { ... }

    // 3. Cambiar especialista
    function cambiarEspecialista(select) { ... }

    // 4. Renderizar Calendario y navegación de meses
    function renderizarCalendario() { ... }
    function cambiarMesCalendario(delta) { ... }

    // 5. Seleccionar hora y formatear 24h
    function seleccionarHora(btn) { ... }

    // 6. Cambiar método de pago
    function cambiarMetodoPago(metodo, btn) { ... }

    // 7. Simular tarjeta bancaria (número con espacios y detección de franquicia)
    function onCardNumberInput(input) { ... }
    function onCardHolderInput(input) { ... }
    function onCardExpiryInput(input) { ... }

    // 8. Envío por Fetch / AJAX con SweetAlert2
    function confirmarYAgendarCita(btn) {
        const formData = new FormData(document.getElementById('formAgendarCita'));
        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': '...' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            Swal.fire({ title: '¡Cita agendada exitosamente!', icon: 'success' });
        })
        .catch(err => {
            Swal.fire({ title: 'Atención', text: err.message, icon: 'warning' });
        });
    }
```

### Funciones destacadas:
* **`onCardNumberInput`:** Aplica máscara con espacios cada 4 dígitos, detecta si empieza por 4 (Visa), 5 o 2 (Mastercard) o 3 (Amex), y actualiza el logo y los números en la tarjeta virtual.
* **`confirmarYAgendarCita`:** Realiza una petición `fetch()` asíncrona con botón de cargando (`fa-spinner fa-spin`). Si Laravel devuelve éxito, dispara una alerta de SweetAlert2 sin perder la navegación.
