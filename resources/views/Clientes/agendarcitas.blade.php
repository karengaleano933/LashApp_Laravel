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
                $img = asset('img/volumen_ruso.jpg');
                if (!empty($s->imagen_url)) {
                    $img = filter_var($s->imagen_url, FILTER_VALIDATE_URL) ? $s->imagen_url : asset($s->imagen_url);
                }

                $duracionTexto = '45m';
                if (!empty($s->duracion_minuto)) {
                    if (is_string($s->duracion_minuto) && str_contains($s->duracion_minuto, ':')) {
                        $partes = explode(':', $s->duracion_minuto);
                        $duracionTexto = ((int)$partes[0] > 0 ? (int)$partes[0] . 'h ' : '') . (int)$partes[1] . 'm';
                    } else {
                        $duracionTexto = $s->duracion_minuto . 'm';
                    }
                }

                return [
                    'id' => $s->id,
                    'nombre' => $s->nombre,
                    'precio' => (float)$s->precio,
                    'duracion' => $duracionTexto,
                    'img' => $img,
                ];
            })->toArray();
        } else {
            $serviciosDisponibles = [
                [
                    'id' => 1,
                    'nombre' => 'Extensiones de Pestañas Volumen Ruso',
                    'precio' => 220000,
                    'duracion' => '2h 30m',
                    'img' => asset('img/volumen_ruso.jpg'),
                ],
                [
                    'id' => 2,
                    'nombre' => 'Extensiones de Pestañas Clásicas',
                    'precio' => 180000,
                    'duracion' => '2h 00m',
                    'img' => asset('img/extensiones_clasicas.jpg'),
                ],
                [
                    'id' => 3,
                    'nombre' => 'Diseño de Cejas con Henna',
                    'precio' => 80000,
                    'duracion' => '45m',
                    'img' => asset('img/diseno_cejas.jpg'),
                ],
                [
                    'id' => 4,
                    'nombre' => 'Lifting de Pestañas',
                    'precio' => 130000,
                    'duracion' => '1h 15m',
                    'img' => asset('img/volumen_ruso.jpg'),
                ],
                [
                    'id' => 5,
                    'nombre' => 'Manicure Semipermanente',
                    'precio' => 90000,
                    'duracion' => '1h 00m',
                    'img' => asset('img/manicure_semi.jpg'),
                ],
                [
                    'id' => 6,
                    'nombre' => 'Pedicure Spa Relajante',
                    'precio' => 95000,
                    'duracion' => '1h 00m',
                    'img' => asset('img/manicure_semi.jpg'),
                ],
            ];
        }
    }

    // Buscar si viene preseleccionado un servicio por ID (desde el catálogo u otra pantalla)
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
        $servicioInicial = $serviciosDisponibles[0] ?? [
            'id' => 1,
            'nombre' => 'Extensiones de Pestañas Volumen Ruso',
            'precio' => 220000,
            'duracion' => '2h 30m',
            'img' => asset('img/volumen_ruso.jpg'),
        ];
    }

    $fechaHoy = Carbon::now();
    $clienteModel = $cliente ?? \App\Models\Cliente::firstOrCreate(['user_id' => auth()->id()]);
    $clienteId = $clienteModel?->id ?? 1;
    $primerTrabajador = (isset($trabajadores) && $trabajadores->count() > 0) ? $trabajadores->first() : null;
    $trabajadorIdInicial = $primerTrabajador?->id ?? 1;
@endphp

<!-- Contenido Principal -->
<div class="page-content">

    <!-- ══════════════════════════════════════════
         ENCABEZADO DE SECCIÓN
    ══════════════════════════════════════════ -->
    <div class="agendar-header">
        <div class="agendar-header-left">
            <div class="agendar-header-icon">
                <i class="fa-regular fa-calendar-check"></i>
            </div>
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

    <!-- ══════════════════════════════════════════
         STEPPER SUPERIOR (4 PASOS)
    ══════════════════════════════════════════ -->
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

    <!-- Formulario que envuelve el proceso de reserva -->
    <form action="{{ route('citas.store') }}" method="POST" id="formAgendarCita">
        @csrf
        <input type="hidden" name="id_cliente" id="inputClienteId" value="{{ $clienteId }}">
        <input type="hidden" name="id_servicio" id="inputServicioId" value="{{ $servicioInicial['id'] }}">
        <input type="hidden" name="id_trabajador" id="inputTrabajadorId" value="{{ $trabajadorIdInicial }}">
        <input type="hidden" name="fecha_cita" id="inputFechaCita" value="{{ $fechaHoy->format('Y-m-d') }}">
        <input type="hidden" name="hora_inicio" id="inputHoraInicio" value="08:00">
        <input type="hidden" name="metodo_pago" id="inputMetodoPago" value="tarjeta">
        <input type="hidden" name="estado" value="pendiente">

        <!-- ══════════════════════════════════════════
             GRID PRINCIPAL DE 4 COLUMNAS
        ══════════════════════════════════════════ -->
        <div class="agendar-grid-layout">

            <!-- ── COLUMNA 1: 1. SELECCIONA EL SERVICIO ── -->
            <div class="agendar-col-card">
                <div class="col-header-title">
                    1. Selecciona el servicio
                </div>

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

            <!-- ── COLUMNA 2: 2. PROFESIONAL Y FECHA ── -->
            <div class="agendar-col-card">
                <div class="col-header-title">
                    <i class="fa-solid fa-user-nurse"></i> 2. Profesional y Fecha
                </div>

                <div class="specialist-label">Selecciona la especialista:</div>
                <select class="specialist-select-box" id="selectEspecialista" onchange="cambiarEspecialista(this)">
                    <option value="{{ $trabajadorIdInicial }}" data-nombre="Cualquier Profesional" selected>✦ Cualquier Profesional disponible</option>
                    @if(isset($trabajadores) && $trabajadores->count() > 0)
                        @foreach($trabajadores as $trab)
                            @php
                                $nombreTrab = $trab->user ? trim($trab->user->name . ' ' . $trab->user->apellido) : 'Especialista #' . $trab->id;
                                $espTrab = $trab->especialidad ?: 'Lashista Profesional';
                            @endphp
                            <option value="{{ $trab->id }}" data-nombre="{{ $nombreTrab }}">{{ $nombreTrab }} ({{ $espTrab }})</option>
                        @endforeach
                    @endif
                </select>

                <!-- Calendario interactivo -->
                <div class="cal-nav-bar">
                    <button type="button" class="cal-nav-btn" onclick="cambiarMesCalendario(-1)">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <div class="cal-month-title" id="calMonthTitle">
                        {{ ucfirst($fechaHoy->translatedFormat('F Y')) }}
                    </div>
                    <button type="button" class="cal-nav-btn" onclick="cambiarMesCalendario(1)">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                <div class="cal-weekdays">
                    <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
                </div>

                <div class="cal-matrix" id="diasCalendarioGrid">
                    <!-- Días generados dinámicamente con JavaScript -->
                </div>

                <div class="cal-legend">
                    <div class="legend-pill"><span class="legend-dot green"></span> Disponible</div>
                    <div class="legend-pill"><span class="legend-dot orange"></span> Pocos cupos</div>
                    <div class="legend-pill"><span class="legend-dot gray"></span> No disponible</div>
                </div>

                <div class="cal-notice-banner">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Puedes cancelar o reprogramar tu cita hasta 12 horas antes de la misma.</span>
                </div>

                <!-- ══════════════════════════════════════════
                     4. PASARELA DE PAGO BANCARIO
                ══════════════════════════════════════════ -->
                <div class="pasarela-pago-wrapper">
                    <div class="pasarela-section-header">
                        <i class="fa-solid fa-building-columns"></i> 4. Pasarela de Pago Bancario
                    </div>

                    <div class="pasarela-card-box">
                        <!-- Top Bar: Pago 100% Seguro + SSL 256-bit -->
                        <div class="pasarela-top-bar">
                            <span class="pago-seguro-text">
                                <i class="fa-solid fa-circle-check"></i> Pago 100% Seguro
                            </span>
                            <span class="ssl-badge">
                                <i class="fa-solid fa-shield-halved"></i> SSL 256-bit
                            </span>
                        </div>

                        <!-- Tabs de Método de Pago: Tarjeta, PSE Bancos, Digital, Efectivo -->
                        <div class="pago-tabs-bar">
                            <button type="button" class="pago-tab-btn active" data-metodo="tarjeta" onclick="cambiarMetodoPago('tarjeta', this)">
                                <i class="fa-regular fa-credit-card"></i>
                                <span>Tarjeta</span>
                            </button>
                            <button type="button" class="pago-tab-btn" data-metodo="pse" onclick="cambiarMetodoPago('pse', this)">
                                <i class="fa-solid fa-building-columns"></i>
                                <span>PSE Bancos</span>
                            </button>
                            <button type="button" class="pago-tab-btn" data-metodo="digital" onclick="cambiarMetodoPago('digital', this)">
                                <i class="fa-solid fa-mobile-screen"></i>
                                <span>Digital</span>
                            </button>
                            <button type="button" class="pago-tab-btn" data-metodo="efectivo" onclick="cambiarMetodoPago('efectivo', this)">
                                <i class="fa-solid fa-money-bill-1-wave"></i>
                                <span>Efectivo</span>
                            </button>
                        </div>

                        <!-- PANEL 1: TARJETA DE CRÉDITO / DÉBITO -->
                        <div class="panel-pago-content" id="panelMetodoTarjeta">
                            <!-- Tarjeta Visual Interactiva -->
                            <div class="virtual-card-wrap">
                                <div class="virtual-card-body">
                                    <div class="vcard-top-row">
                                        <div class="vcard-chip"></div>
                                        <i class="fa-solid fa-wifi vcard-wifi"></i>
                                        <div class="vcard-brand" id="vcardBrandLogo">VISA</div>
                                    </div>

                                    <div class="vcard-number" id="vcardNumberDisplay">
                                        •••• &nbsp; •••• &nbsp; •••• &nbsp; ••••
                                    </div>

                                    <div class="vcard-bottom-row">
                                        <div>
                                            <div class="vcard-label">TITULAR</div>
                                            <div class="vcard-value" id="vcardHolderDisplay">NOMBRE EN LA TARJETA</div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div class="vcard-label">VENCE</div>
                                            <div class="vcard-value" id="vcardExpiryDisplay">MM/AA</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Campos del Formulario de Tarjeta -->
                            <div class="pago-fields-wrap">
                                <div class="pago-input-group">
                                    <label>Número de Tarjeta</label>
                                    <div class="pago-input-icon-box">
                                        <i class="fa-regular fa-credit-card"></i>
                                        <input type="text" 
                                               id="inputCardNumber" 
                                               name="numero_tarjeta"
                                               placeholder="4000 1234 5678 9010" 
                                               maxlength="19" 
                                               oninput="onCardNumberInput(this)">
                                    </div>
                                </div>

                                <div class="pago-input-group">
                                    <label>Nombre del Titular</label>
                                    <div class="pago-input-icon-box">
                                        <i class="fa-regular fa-user"></i>
                                        <input type="text" 
                                               id="inputCardHolder" 
                                               name="titular_tarjeta"
                                               placeholder="EJ. MARIA GOMEZ" 
                                               oninput="onCardHolderInput(this)">
                                    </div>
                                </div>

                                <div class="pago-row-2col">
                                    <div class="pago-input-group">
                                        <label>Vencimiento</label>
                                        <input type="text" 
                                               id="inputCardExpiry" 
                                               name="vencimiento_tarjeta"
                                               placeholder="MM/AA" 
                                               maxlength="5" 
                                               oninput="onCardExpiryInput(this)">
                                    </div>
                                    <div class="pago-input-group">
                                        <label>CVC / CVV</label>
                                        <input type="password" 
                                               id="inputCardCvc" 
                                               name="cvc_tarjeta"
                                               placeholder="••••" 
                                               maxlength="4">
                                    </div>
                                </div>

                                <div class="pago-input-group">
                                    <label>Cuotas de pago</label>
                                    <select class="pago-select" name="cuotas_pago">
                                        <option value="1">1 Cuota (Sin Intereses)</option>
                                        <option value="2">2 Cuotas</option>
                                        <option value="3">3 Cuotas</option>
                                        <option value="6">6 Cuotas</option>
                                        <option value="12">12 Cuotas</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 2: PSE BANCOS -->
                        <div class="panel-pago-content" id="panelMetodoPse" style="display: none;">
                            <div class="pago-fields-wrap">
                                <div class="pago-input-group">
                                    <label>Selecciona tu Banco</label>
                                    <select class="pago-select" name="banco_pse">
                                        <option value="">Selecciona una entidad bancaria...</option>
                                        <option value="bancolombia">Bancolombia</option>
                                        <option value="davivienda">Davivienda</option>
                                        <option value="nequi">Nequi</option>
                                        <option value="bbva">BBVA Colombia</option>
                                        <option value="bogota">Banco de Bogotá</option>
                                        <option value="falabella">Banco Falabella</option>
                                        <option value="daviplata">Daviplata</option>
                                    </select>
                                </div>
                                <div class="pago-input-group">
                                    <label>Tipo de Persona</label>
                                    <select class="pago-select" name="tipo_persona_pse">
                                        <option value="natural">Persona Natural</option>
                                        <option value="juridica">Persona Jurídica</option>
                                    </select>
                                </div>
                                <div class="pago-input-group">
                                    <label>Correo Electrónico registrado en PSE</label>
                                    <input type="email" class="pago-input-simple" placeholder="tucorreo@ejemplo.com" value="{{ auth()->user()?->email ?? '' }}">
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 3: DIGITAL (NEQUI / DAVIPLATA) -->
                        <div class="panel-pago-content" id="panelMetodoDigital" style="display: none;">
                            <div class="pago-fields-wrap">
                                <div style="text-align: center; padding: 10px 0;">
                                    <img src="{{ asset('img/logo.png') }}" alt="QR" style="width: 70px; height: 70px; object-fit: contain; margin: 0 auto 8px; border-radius: 12px; background: #faf5ff; padding: 6px;">
                                    <p style="font-size: 12px; color: #2a0d3e; font-weight: 700; margin-bottom: 2px;">Transferencia inmediata por Nequi o Daviplata</p>
                                    <p style="font-size: 11px; color: #7a5a90; margin: 0;">Número de cuenta: <strong>314 766 2316</strong></p>
                                </div>
                                <div class="pago-input-group">
                                    <label>Número de celular desde el que transfieres</label>
                                    <input type="tel" class="pago-input-simple" placeholder="300 000 0000">
                                </div>
                            </div>
                        </div>

                        <!-- PANEL 4: EFECTIVO EN RECEPCIÓN -->
                        <div class="panel-pago-content" id="panelMetodoEfectivo" style="display: none;">
                            <div style="background: #faf5ff; border: 1px dashed #dcc8ee; border-radius: 14px; padding: 14px; text-align: center; margin: 6px 0;">
                                <div style="font-size: 22px; color: #7c1db8; margin-bottom: 6px;">
                                    <i class="fa-solid fa-shop"></i>
                                </div>
                                <h5 style="font-size: 12.5px; font-weight: 700; color: #2a0d3e; margin-bottom: 4px;">Paga en el Estudio el día de tu cita</h5>
                                <p style="font-size: 11px; color: #7a5a90; margin: 0;">Recibimos efectivo, datafono y transferencias en la recepción de Oh La Lash.</p>
                            </div>
                        </div>

                        <!-- Fila de Garantía de Seguridad -->
                        <div class="pago-garantia-row">
                            <span class="garantia-lbl">Garantizado por:</span>
                            <div class="garantia-logos-list">
                                <span class="badge-brand visa">VISA</span>
                                <span class="badge-brand mastercard">
                                    <span class="mc-c1"></span><span class="mc-c2"></span>
                                </span>
                                <span class="badge-brand amex">AMEX</span>
                                <span class="badge-brand pse">PSE</span>
                                <span class="badge-brand nequi">NEQUI</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── COLUMNA 3: 3. ELIGE HORA ── -->
            <div class="agendar-col-card">
                <div class="col-header-title">
                    <i class="fa-regular fa-clock"></i> 3. Elige hora
                </div>

                <div class="col-sub-notice">
                    <i class="fa-solid fa-circle-info"></i> Horas disponibles para la fecha seleccionada
                </div>

                @php
                    $horariosDisponibles = [
                        '08:00 am', '09:00 am', '10:00 am', '11:00 am', 
                        '12:00 pm', '01:00 pm', '02:00 pm', '03:00 pm', 
                        '04:00 pm', '05:00 pm'
                    ];
                @endphp

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
            </div>

            <!-- ── COLUMNA 4: RESUMEN DE TU CITA ── -->
            <div class="agendar-col-card">
                <div class="col-header-title">
                    <i class="fa-regular fa-calendar-check"></i> Resumen de tu cita
                </div>

                <div class="resumen-service-preview">
                    <img src="{{ $servicioInicial['img'] }}" id="resumenImg" alt="Servicio" class="resumen-thumb">
                    <div>
                        <div class="resumen-serv-title" id="resumenTitulo">
                            {{ $servicioInicial['nombre'] }}
                        </div>
                        <div class="resumen-serv-duration" id="resumenDuracion">
                            {{ $servicioInicial['duracion'] }}
                        </div>
                    </div>
                </div>

                <div class="resumen-details-list">
                    <div class="resumen-detail-item">
                        <i class="fa-regular fa-calendar"></i>
                        <div class="resumen-detail-content">
                            <strong>Fecha</strong>
                            <span id="resumenFechaTexto">{{ ucfirst($fechaHoy->translatedFormat('l, d \d\e F \d\e Y')) }}</span>
                        </div>
                    </div>

                    <div class="resumen-detail-item">
                        <i class="fa-regular fa-clock"></i>
                        <div class="resumen-detail-content">
                            <strong>Hora</strong>
                            <span id="resumenHoraTexto">08:00 am</span>
                        </div>
                    </div>

                    <div class="resumen-detail-item">
                        <i class="fa-regular fa-user"></i>
                        <div class="resumen-detail-content">
                            <strong>Profesional</strong>
                            <span id="resumenProfesionalTexto">Cualquier Profesional</span>
                        </div>
                    </div>

                    <div class="resumen-detail-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <div class="resumen-detail-content">
                            <strong>Ubicación</strong>
                            <span>Oh La Lash Studio</span>
                            <small style="color: #7a5a90; display: block; font-size: 11px;">Calle 123 #45-67, Local 2<br>Medellín, Colombia</small>
                        </div>
                    </div>
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
                    Confirmar y Continuar <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>
                </button>
            </div>

        </div>
    </form>

</div> <!-- Cierre de .page-content -->

<!-- ══════════════════════════════════════════
     MODAL MIS CITAS AGENDADAS
══════════════════════════════════════════ -->
<div class="modal-miscitas-overlay" id="modalMisCitas" style="display: none;">
    <div class="modal-miscitas-container">
        <div class="modal-miscitas-header">
            <h3><i class="fa-solid fa-calendar-check"></i> Mis Citas Agendadas</h3>
            <button type="button" class="modal-miscitas-close" onclick="cerrarModalMisCitas()">&times;</button>
        </div>
        <div class="modal-miscitas-body">
            @if(isset($misCitas) && $misCitas->count() > 0)
                <div class="miscitas-list-grid">
                    @foreach($misCitas as $mc)
                        @php
                            $imgCita = asset('img/volumen_ruso.jpg');
                            if (!empty($mc->servicio?->imagen_url)) {
                                $imgCita = filter_var($mc->servicio->imagen_url, FILTER_VALIDATE_URL) ? $mc->servicio->imagen_url : asset($mc->servicio->imagen_url);
                            }
                            $nombreProf = $mc->trabajador?->user ? trim($mc->trabajador->user->name . ' ' . $mc->trabajador->user->apellido) : 'Lorena Ayala';
                            $pago = $mc->pago;
                            $metodoPagoTexto = $pago ? $pago->metodo_pago . ' ($' . number_format($pago->monto, 0, ',', '.') . ')' : 'Pendiente';
                        @endphp
                        <div class="miscitas-item-card">
                            <div class="miscitas-item-left">
                                <img src="{{ $imgCita }}" alt="{{ $mc->servicio?->nombre ?? 'Servicio' }}" class="miscitas-item-thumb">
                                <div>
                                    <div class="miscitas-serv-name">{{ $mc->servicio?->nombre ?? 'Tratamiento' }}</div>
                                    <div class="miscitas-meta-row">
                                        <span><i class="fa-regular fa-calendar"></i> {{ $mc->fecha ? $mc->fecha->format('d/m/Y') : '' }}</span>
                                        <span><i class="fa-regular fa-clock"></i> {{ $mc->hora_inicio ? Carbon::parse($mc->hora_inicio)->format('g:i A') : '' }}</span>
                                        <span><i class="fa-solid fa-user-nurse"></i> {{ $nombreProf }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="miscitas-item-right">
                                <span class="badge-pago-info"><i class="fa-regular fa-credit-card"></i> {{ $metodoPagoTexto }}</span>
                                <span class="badge-status-cita {{ $mc->estado }}">{{ ucfirst($mc->estado) }}</span>
                                @if(in_array($mc->estado, ['pendiente', 'confirmado']))
                                    <a href="{{ route('citas.cancelar', $mc->id) }}" 
                                       class="btn-cancelar-cita-sm"
                                       onclick="return confirm('¿Seguro que deseas cancelar esta cita?')">
                                        <i class="fa-solid fa-xmark"></i> Cancelar
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div style="text-align: center; padding: 40px 20px;">
                    <div style="width: 56px; height: 56px; border-radius: 50%; background: #f6effb; color: #7c1db8; display: flex; align-items: center; justify-content: center; font-size: 24px; margin: 0 auto 12px;">
                        <i class="fa-regular fa-calendar-plus"></i>
                    </div>
                    <h4 style="font-size: 16px; color: #2a0d3e; font-weight: 700; margin-bottom: 6px;">No tienes citas agendadas</h4>
                    <p style="font-size: 13px; color: #7a5a90; margin-bottom: 18px;">Usa el formulario para reservar tu próximo tratamiento de belleza.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     SCRIPTS DE CONTROL INTERACTIVO
══════════════════════════════════════════ -->
<script>
    let mesActual = {{ $fechaHoy->format('m') - 1 }};
    let anioActual = {{ $fechaHoy->format('Y') }};
    let diaSeleccionado = {{ $fechaHoy->format('d') }};

    const nombresMeses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];

    const nombresDias = [
        "Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"
    ];

    // Inicializar calendario al cargar
    document.addEventListener('DOMContentLoaded', () => {
        renderizarCalendario();
    });

    // 1. Filtrar lista de servicios
    function filtrarServiciosAgenda() {
        const input = document.getElementById('buscarServicioAgenda');
        const query = (input ? input.value : '').toLowerCase().trim();
        const items = document.querySelectorAll('.service-card-option');

        items.forEach(item => {
            const nombre = (item.getAttribute('data-nombre') || '').toLowerCase();
            item.style.display = (!query || nombre.includes(query)) ? 'flex' : 'none';
        });
    }

    // 2. Seleccionar servicio
    function seleccionarServicio(element) {
        document.querySelectorAll('.service-card-option').forEach(el => el.classList.remove('selected'));
        element.classList.add('selected');

        const id = element.getAttribute('data-id');
        const nombre = element.getAttribute('data-nombre');
        const precio = parseFloat(element.getAttribute('data-precio') || '0');
        const duracion = element.getAttribute('data-duracion');
        const img = element.getAttribute('data-img');

        // Actualizar inputs ocultos
        document.getElementById('inputServicioId').value = id;

        // Actualizar resumen
        document.getElementById('resumenTitulo').textContent = nombre;
        document.getElementById('resumenDuracion').textContent = duracion;
        document.getElementById('resumenImg').src = img;

        const precioFmt = '$' + precio.toLocaleString('es-CO');
        document.getElementById('resumenSubtotal').textContent = precioFmt;
        document.getElementById('resumenTotal').textContent = precioFmt;
    }

    // 3. Cambiar especialista
    function cambiarEspecialista(select) {
        const selectedOption = select.options[select.selectedIndex];
        const nombre = selectedOption.getAttribute('data-nombre') || 'Cualquier Profesional';
        document.getElementById('inputTrabajadorId').value = select.value;
        document.getElementById('resumenProfesionalTexto').textContent = nombre;
    }

    // 4. Renderizar Calendario
    function renderizarCalendario() {
        const grid = document.getElementById('diasCalendarioGrid');
        const title = document.getElementById('calMonthTitle');
        if (!grid || !title) return;

        title.textContent = nombresMeses[mesActual] + " " + anioActual;
        grid.innerHTML = "";

        const primerDiaSemana = new Date(anioActual, mesActual, 1).getDay(); // 0 = Dom
        const diasEnMes = new Date(anioActual, mesActual + 1, 0).getDate();

        // Convertir a Lunes = 0, Domingo = 6
        const offset = (primerDiaSemana === 0) ? 6 : primerDiaSemana - 1;

        // Celdas vacías previas
        for (let i = 0; i < offset; i++) {
            const emptyCell = document.createElement('div');
            emptyCell.className = 'cal-day-item empty';
            grid.appendChild(emptyCell);
        }

        // Días del mes
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const dayCell = document.createElement('div');
            dayCell.className = 'cal-day-item';
            dayCell.textContent = dia;

            const dot = document.createElement('span');
            dot.className = 'cal-day-dot';
            dayCell.appendChild(dot);

            if (dia === diaSeleccionado) {
                dayCell.classList.add('selected');
            }

            dayCell.addEventListener('click', () => {
                diaSeleccionado = dia;
                document.querySelectorAll('.cal-day-item').forEach(c => c.classList.remove('selected'));
                dayCell.classList.add('selected');

                const fechaObj = new Date(anioActual, mesActual, dia);
                const anioStr = anioActual;
                const mesStr = String(mesActual + 1).padStart(2, '0');
                const diaStr = String(dia).padStart(2, '0');

                document.getElementById('inputFechaCita').value = `${anioStr}-${mesStr}-${diaStr}`;

                const nombreDia = nombresDias[fechaObj.getDay()];
                const nombreMes = nombresMeses[mesActual].toLowerCase();
                document.getElementById('resumenFechaTexto').textContent = `${nombreDia}, ${dia} de ${nombreMes} de ${anioStr}`;
            });

            grid.appendChild(dayCell);
        }
    }

    function cambiarMesCalendario(delta) {
        mesActual += delta;
        if (mesActual < 0) {
            mesActual = 11;
            anioActual--;
        } else if (mesActual > 11) {
            mesActual = 0;
            anioActual++;
        }
        diaSeleccionado = 1;
        renderizarCalendario();
    }

    // 5. Seleccionar hora
    function seleccionarHora(btn) {
        document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');

        const hora = btn.getAttribute('data-hora');
        const hora24 = btn.getAttribute('data-hora-24');

        document.getElementById('inputHoraInicio').value = hora24;
        document.getElementById('resumenHoraTexto').textContent = hora;
    }

    // 6. Cambiar método de pago (Tarjeta, PSE, Digital, Efectivo)
    function cambiarMetodoPago(metodo, btn) {
        document.querySelectorAll('.pago-tab-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        // Ocultar todos los paneles
        document.getElementById('panelMetodoTarjeta').style.display = 'none';
        document.getElementById('panelMetodoPse').style.display = 'none';
        document.getElementById('panelMetodoDigital').style.display = 'none';
        document.getElementById('panelMetodoEfectivo').style.display = 'none';

        // Cambiar método de pago en input oculto
        const inputMetodo = document.getElementById('inputMetodoPago');
        if (inputMetodo) inputMetodo.value = metodo;

        // Mostrar el panel correspondiente
        if (metodo === 'tarjeta') {
            document.getElementById('panelMetodoTarjeta').style.display = 'block';
        } else if (metodo === 'pse') {
            document.getElementById('panelMetodoPse').style.display = 'block';
        } else if (metodo === 'digital') {
            document.getElementById('panelMetodoDigital').style.display = 'block';
        } else if (metodo === 'efectivo') {
            document.getElementById('panelMetodoEfectivo').style.display = 'block';
        }
    }

    // 7. Formatear y sincronizar número de tarjeta en tiempo real
    function onCardNumberInput(input) {
        let val = input.value.replace(/\D/g, ''); // Solo dígitos
        if (val.length > 16) val = val.substring(0, 16);

        // Formato con espacios cada 4 dígitos
        let formatted = val.match(/.{1,4}/g)?.join(' ') || '';
        input.value = formatted;

        // Detectar franquicia
        const brandLogo = document.getElementById('vcardBrandLogo');
        if (val.startsWith('4')) {
            brandLogo.textContent = 'VISA';
            brandLogo.style.color = '#1e3a8a';
        } else if (val.startsWith('5') || val.startsWith('2')) {
            brandLogo.textContent = 'MC';
            brandLogo.style.color = '#ea580c';
        } else if (val.startsWith('3')) {
            brandLogo.textContent = 'AMEX';
            brandLogo.style.color = '#0284c7';
        } else {
            brandLogo.textContent = 'VISA';
            brandLogo.style.color = '#1e3a8a';
        }

        // Reflejar en la tarjeta visual
        const display = document.getElementById('vcardNumberDisplay');
        if (formatted.length > 0) {
            display.textContent = formatted;
        } else {
            display.innerHTML = '&bull;&bull;&bull;&bull; &nbsp; &bull;&bull;&bull;&bull; &nbsp; &bull;&bull;&bull;&bull; &nbsp; &bull;&bull;&bull;&bull;';
        }
    }

    // 8. Sincronizar titular de la tarjeta
    function onCardHolderInput(input) {
        input.value = input.value.toUpperCase();
        const display = document.getElementById('vcardHolderDisplay');
        display.textContent = input.value.trim() || 'NOMBRE EN LA TARJETA';
    }

    // 9. Formatear y sincronizar fecha de expiración (MM/AA)
    function onCardExpiryInput(input) {
        let val = input.value.replace(/\D/g, '');
        if (val.length > 4) val = val.substring(0, 4);

        if (val.length >= 2) {
            val = val.substring(0, 2) + '/' + val.substring(2);
        }
        input.value = val;

        const display = document.getElementById('vcardExpiryDisplay');
        display.textContent = val || 'MM/AA';
    }

    // Confirmar y Agendar Cita vía AJAX sin redireccionar
    function confirmarYAgendarCita(btn) {
        const form = document.getElementById('formAgendarCita');
        if (!form) return;

        const originalHtml = btn ? btn.innerHTML : 'Confirmar y Continuar <i class="fa-solid fa-arrow-right" style="margin-left: 6px;"></i>';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" style="margin-right: 6px;"></i> Agendando cita...';
        }

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errData => {
                    let errMsg = errData.message || 'Error al procesar la reserva.';
                    if (errData.errors) {
                        const primerError = Object.values(errData.errors)[0];
                        if (primerError && primerError.length > 0) errMsg = primerError[0];
                    }
                    throw new Error(errMsg);
                });
            }
            return response.json();
        })
        .then(data => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Cita agendada exitosamente!',
                    html: '<p style="color:#7a5a90; font-size:14px; margin-top:8px;">' + (data.message || 'Tu cita ha sido reservada con éxito en el sistema.') + '<br><small style="color:#9b70b5; display:block; margin-top:6px;">Puedes consultarla en cualquier momento desde Mis Citas o tu Historial.</small></p>',
                    icon: 'success',
                    confirmButtonColor: '#7c1db8',
                    confirmButtonText: 'Entendido'
                });
            } else {
                alert('¡Cita agendada exitosamente!');
            }
        })
        .catch(error => {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Atención',
                    text: error.message || 'Hubo un inconveniente al registrar la cita.',
                    icon: 'warning',
                    confirmButtonColor: '#7c1db8',
                    confirmButtonText: 'Aceptar'
                });
            } else {
                alert(error.message || 'Error al procesar la cita.');
            }
        });
    }

    // Modal Mis Citas
    function abrirModalMisCitas() {
        const modal = document.getElementById('modalMisCitas');
        if (modal) modal.style.display = 'flex';
    }

    function cerrarModalMisCitas() {
        const modal = document.getElementById('modalMisCitas');
        if (modal) modal.style.display = 'none';
    }

    const modalMisCitasEl = document.getElementById('modalMisCitas');
    if (modalMisCitasEl) {
        modalMisCitasEl.addEventListener('click', function(e) {
            if (e.target === modalMisCitasEl) cerrarModalMisCitas();
        });
    }

    @if(!empty($abrirMisCitas))
        document.addEventListener('DOMContentLoaded', () => {
            abrirModalMisCitas();
        });
    @endif

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

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper (abierto en headercliente.blade.php) -->
</div> <!-- Cierre de .app-layout (abierto en headercliente.blade.php) -->

</body>
</html>
