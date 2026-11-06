<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket POS - {{ $numeroTicket }} | {{ $configuracion->nombre_estudio ?? 'Oh La Lash Studio' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --ticket-width: 320px;
            --paper-color: #ffffff;
            --ink-color: #111111;
            --ink-muted: #555555;
            --brand-purple: #7c1db8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Space Mono', 'Courier New', Courier, monospace;
            background: linear-gradient(135deg, #1f1228 0%, #301740 50%, #15091d 100%);
            color: var(--ink-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 15px;
            -webkit-font-smoothing: antialiased;
        }

        @if(request('embed') == '1')
        body {
            background: #faf5fc !important;
            min-height: auto !important;
            padding: 12px 6px !important;
        }
        .actions-toolbar {
            display: none !important;
        }
        .ticket-wrapper {
            box-shadow: 0 4px 18px rgba(124, 29, 184, 0.1) !important;
        }
        @endif

        /* ── BARRA SUPERIOR DE ACCIONES (NO IMPRIMIBLE) ── */
        .actions-toolbar {
            width: 100%;
            max-width: var(--ticket-width);
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-toolbar {
            flex: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }

        .btn-download {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
        }

        .btn-download:hover {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            transform: translateY(-2px);
        }

        .btn-pdf {
            background: linear-gradient(135deg, #7c1db8 0%, #5a1285 100%);
            color: #ffffff;
            box-shadow: 0 4px 15px rgba(124, 29, 184, 0.4);
        }

        .btn-pdf:hover {
            background: linear-gradient(135deg, #9324d9 0%, #6c129e 100%);
            transform: translateY(-2px);
        }

        .btn-back {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            backdrop-filter: blur(8px);
        }

        .btn-back:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* ── ESTRUCTURA DEL TICKET TÉRMICO ── */
        .ticket-wrapper {
            width: 100%;
            max-width: var(--ticket-width);
            background: var(--paper-color);
            padding: 24px 20px 30px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.05);
            position: relative;
            border-radius: 4px;
        }

        /* Efecto de borde dentado de papel térmico cortado */
        .ticket-wrapper::before,
        .ticket-wrapper::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            height: 8px;
            background-size: 16px 8px;
            background-repeat: repeat-x;
        }

        .ticket-wrapper::before {
            top: -8px;
            background-image: radial-gradient(circle at 8px 8px, transparent 8px, var(--paper-color) 8px);
        }

        .ticket-wrapper::after {
            bottom: -8px;
            background-image: radial-gradient(circle at 8px 0, transparent 8px, var(--paper-color) 8px);
        }

        /* ── SECCIONES DEL TICKET ── */
        .ticket-header {
            text-align: center;
            margin-bottom: 14px;
        }

        .brand-logo-symbol {
            font-size: 26px;
            color: #7c1db8;
            margin-bottom: 4px;
        }

        .brand-name {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #111111;
            margin-bottom: 2px;
        }

        .brand-slogan {
            font-size: 11px;
            color: var(--ink-muted);
            margin-bottom: 8px;
        }

        .brand-meta {
            font-size: 11px;
            line-height: 1.4;
            color: var(--ink-muted);
        }

        /* Línea punteada divisoria */
        .ticket-divider {
            border: none;
            border-top: 1px dashed #777777;
            margin: 12px 0;
        }

        .ticket-divider-double {
            border: none;
            border-top: 2px dashed #333333;
            margin: 12px 0;
        }

        /* Datos del comprobante */
        .ticket-badge-title {
            text-align: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin: 4px 0 8px;
            text-transform: uppercase;
        }

        .ticket-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 11px;
            line-height: 1.5;
            margin-bottom: 3px;
        }

        .ticket-row .lbl {
            color: var(--ink-muted);
            flex-shrink: 0;
        }

        .ticket-row .val {
            font-weight: 700;
            text-align: right;
            word-break: break-word;
        }

        /* Tabla de ítems */
        .items-table {
            width: 100%;
            margin: 8px 0;
            font-size: 11px;
        }

        .items-table-header {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
            padding-bottom: 4px;
            border-bottom: 1px dashed #777777;
            margin-bottom: 6px;
        }

        .item-entry {
            margin-bottom: 6px;
        }

        .item-entry-main {
            display: flex;
            justify-content: space-between;
            font-weight: 700;
        }

        .item-entry-sub {
            font-size: 10px;
            color: var(--ink-muted);
            padding-left: 10px;
        }

        /* Totales */
        .totals-section {
            margin-top: 6px;
        }

        .total-highlight {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 16px;
            font-weight: 800;
            padding: 6px 0;
            margin: 4px 0;
            border-top: 1px dashed #111;
            border-bottom: 1px dashed #111;
        }

        .badge-pago-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-pago-completado {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-pago-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        /* Código de Barras / QR */
        .ticket-barcode-wrap {
            text-align: center;
            margin: 16px 0 10px;
        }

        .barcode-svg {
            width: 180px;
            height: 44px;
            margin: 0 auto 4px;
            display: block;
        }

        .ticket-footer-text {
            text-align: center;
            font-size: 10px;
            line-height: 1.4;
            color: var(--ink-muted);
            margin-top: 8px;
        }

        .ticket-footer-text strong {
            color: #111111;
        }

        /* ── ESTILOS DE IMPRESIÓN (TICKET 80MM) ── */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            html, body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 80mm;
            }

            .actions-toolbar {
                display: none !important;
            }

            .ticket-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 10px 12px !important;
            }

            .ticket-wrapper::before,
            .ticket-wrapper::after {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    @php
        $userActual = auth()->user();
        $urlVolver = route('cliente.citas');
        if ($userActual && $userActual->role_id === 1) {
            $urlVolver = route('pagos.index');
        } elseif ($userActual && $userActual->role_id === 2) {
            $urlVolver = route('trabajador.citas');
        }
    @endphp

    @if(request('embed') != '1')
    <!-- BARRA DE ACCIONES SUPERIOR (Visible solo en pantalla completa) -->
    <div class="actions-toolbar" style="max-width: 440px; flex-wrap: wrap;">
        <button type="button" class="btn-toolbar btn-download" id="btnDescargarTicket" onclick="descargarTicketPOS()" title="Descargar como imagen PNG">
            <i class="fa-solid fa-download"></i> Descargar Ticket (PNG)
        </button>
        <button type="button" class="btn-toolbar btn-pdf" id="btnDescargarPdf" onclick="descargarTicketPDF()" title="Descargar como documento PDF">
            <i class="fa-solid fa-file-pdf"></i> Descargar PDF
        </button>
        <a href="{{ $urlVolver }}" class="btn-toolbar btn-back" onclick="cerrarOVolver(event, '{{ $urlVolver }}')">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>
    @endif

    <!-- TICKET TIPO POS (80 MM) -->
    <div class="ticket-wrapper" id="ticketPos">

        <!-- CABECERA DEL ESTUDIO -->
        <div class="ticket-header">
            <div class="brand-logo-symbol">
                <i class="fa-solid fa-gem"></i>
            </div>
            <div class="brand-name">
                {{ $configuracion->nombre_estudio ?? 'OH LA LASH STUDIO' }}
            </div>
            <div class="brand-slogan">Estudio de Pestañas & Cejas</div>
            <div class="brand-meta">
                NIT / RUT: 901.458.789-2<br>
                {{ $configuracion->direccion ?? 'Calle 123 #45-67, Local 2, Medellín' }}<br>
                Tel: {{ $configuracion->telefono ?? '+57 300 123 4567' }}<br>
                Email: {{ $configuracion->email_contacto ?? 'info@ohlalash.com' }}
            </div>
        </div>

        <hr class="ticket-divider">

        <!-- DATOS DEL COMPROBANTE -->
        <div class="ticket-badge-title">
            FACTURA / COMPROBANTE POS
        </div>

        <div class="ticket-row">
            <span class="lbl">TICKET N°:</span>
            <span class="val">{{ $numeroTicket }}</span>
        </div>
        <div class="ticket-row">
            <span class="lbl">EMISIÓN:</span>
            <span class="val">{{ $pago?->fecha_pago ? $pago->fecha_pago->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A') }}</span>
        </div>
        <div class="ticket-row">
            <span class="lbl">ESTADO:</span>
            <span class="val">
                <span class="badge-pago-status badge-pago-{{ strtolower($pago?->estado ?? 'completado') }}">
                    {{ strtoupper($pago?->estado ?? 'COMPLETADO') }}
                </span>
            </span>
        </div>

        <hr class="ticket-divider">

        <!-- DATOS DEL CLIENTE -->
        @php
            $clienteUser = $cita->cliente?->user;
            $nombreCliente = $clienteUser ? trim($clienteUser->name . ' ' . ($clienteUser->apellido ?? '')) : 'Cliente General';
            $docCliente = $clienteUser?->documento ?? 'Consumidor Final';
            $telCliente = $clienteUser?->telefono ?? 'N/A';
            $especialista = $cita->trabajador?->user ? trim($cita->trabajador->user->name . ' ' . ($cita->trabajador->user->apellido ?? '')) : 'Profesional Asignada';
            $servicio = $cita->servicio;
        @endphp

        <div class="ticket-row">
            <span class="lbl">CLIENTE:</span>
            <span class="val">{{ $nombreCliente }}</span>
        </div>
        <div class="ticket-row">
            <span class="lbl">DOC / C.C:</span>
            <span class="val">{{ $docCliente }}</span>
        </div>
        <div class="ticket-row">
            <span class="lbl">TELÉFONO:</span>
            <span class="val">{{ $telCliente }}</span>
        </div>
        <div class="ticket-row">
            <span class="lbl">ESPECIALISTA:</span>
            <span class="val">{{ $especialista }}</span>
        </div>

        <hr class="ticket-divider">

        <!-- DATOS DE LA CITA -->
        <div class="ticket-row">
            <span class="lbl">FECHA CITA:</span>
            <span class="val">{{ $cita->fecha ? $cita->fecha->format('d/m/Y') : date('d/m/Y') }}</span>
        </div>
        <div class="ticket-row">
            <span class="lbl">HORARIO:</span>
            <span class="val">{{ $cita->hora_inicio ? \Carbon\Carbon::parse($cita->hora_inicio)->format('g:i A') : '08:00 AM' }}</span>
        </div>

        <hr class="ticket-divider">

        <!-- TABLA DE SERVICIOS ADQUIRIDOS -->
        <div class="items-table">
            <div class="items-table-header">
                <span>CANT / DESCRIPCIÓN</span>
                <span>TOTAL</span>
            </div>

            <div class="item-entry">
                <div class="item-entry-main">
                    <span>1x {{ $servicio?->nombre ?? 'Servicio de Belleza' }}</span>
                    <span>${{ number_format($total, 0, ',', '.') }}</span>
                </div>
                <div class="item-entry-sub">
                    Cat: {{ $servicio?->categoria?->nombre ?? 'Pestañas' }} | Dur: {{ $servicio?->duracion_minuto ?? 45 }} min
                </div>
            </div>
        </div>

        <hr class="ticket-divider-double">

        <!-- SECCIÓN DE TOTALES -->
        <div class="totals-section">
            <div class="ticket-row">
                <span class="lbl">SUBTOTAL:</span>
                <span class="val">${{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>

            @if($ivaPorcentaje > 0)
                <div class="ticket-row">
                    <span class="lbl">IVA ({{ number_format($ivaPorcentaje, 0) }}%):</span>
                    <span class="val">${{ number_format($ivaMonto, 0, ',', '.') }}</span>
                </div>
            @else
                <div class="ticket-row">
                    <span class="lbl">IMPUESTOS:</span>
                    <span class="val">$0 (Régimen Simpl.)</span>
                </div>
            @endif

            <div class="total-highlight">
                <span>TOTAL A PAGAR:</span>
                <span>${{ number_format($total, 0, ',', '.') }} COP</span>
            </div>

            <div class="ticket-row" style="margin-top: 6px;">
                <span class="lbl">MÉTODO PAGO:</span>
                <span class="val">{{ $pago?->metodo_pago ?? 'Tarjeta' }}</span>
            </div>

            @if(!empty($pago?->observaciones))
                <div class="ticket-row">
                    <span class="lbl">REFERENCIA:</span>
                    <span class="val" style="font-size: 10px;">{{ $pago->observaciones }}</span>
                </div>
            @endif
        </div>

        <hr class="ticket-divider">

        <!-- CÓDIGO DE BARRAS VECTORIAL -->
        <div class="ticket-barcode-wrap">
            <svg class="barcode-svg" viewBox="0 0 200 40" preserveAspectRatio="none">
                <rect x="0" y="0" width="3" height="40" fill="#111" />
                <rect x="5" y="0" width="2" height="40" fill="#111" />
                <rect x="9" y="0" width="4" height="40" fill="#111" />
                <rect x="15" y="0" width="2" height="40" fill="#111" />
                <rect x="19" y="0" width="5" height="40" fill="#111" />
                <rect x="26" y="0" width="2" height="40" fill="#111" />
                <rect x="30" y="0" width="3" height="40" fill="#111" />
                <rect x="35" y="0" width="4" height="40" fill="#111" />
                <rect x="42" y="0" width="2" height="40" fill="#111" />
                <rect x="46" y="0" width="6" height="40" fill="#111" />
                <rect x="54" y="0" width="3" height="40" fill="#111" />
                <rect x="59" y="0" width="2" height="40" fill="#111" />
                <rect x="63" y="0" width="4" height="40" fill="#111" />
                <rect x="69" y="0" width="5" height="40" fill="#111" />
                <rect x="76" y="0" width="2" height="40" fill="#111" />
                <rect x="80" y="0" width="4" height="40" fill="#111" />
                <rect x="86" y="0" width="3" height="40" fill="#111" />
                <rect x="91" y="0" width="5" height="40" fill="#111" />
                <rect x="98" y="0" width="2" height="40" fill="#111" />
                <rect x="102" y="0" width="4" height="40" fill="#111" />
                <rect x="108" y="0" width="3" height="40" fill="#111" />
                <rect x="113" y="0" width="5" height="40" fill="#111" />
                <rect x="120" y="0" width="2" height="40" fill="#111" />
                <rect x="124" y="0" width="6" height="40" fill="#111" />
                <rect x="132" y="0" width="2" height="40" fill="#111" />
                <rect x="136" y="0" width="4" height="40" fill="#111" />
                <rect x="142" y="0" width="3" height="40" fill="#111" />
                <rect x="147" y="0" width="5" height="40" fill="#111" />
                <rect x="154" y="0" width="2" height="40" fill="#111" />
                <rect x="158" y="0" width="4" height="40" fill="#111" />
                <rect x="164" y="0" width="6" height="40" fill="#111" />
                <rect x="172" y="0" width="2" height="40" fill="#111" />
                <rect x="176" y="0" width="4" height="40" fill="#111" />
                <rect x="182" y="0" width="3" height="40" fill="#111" />
                <rect x="187" y="0" width="5" height="40" fill="#111" />
                <rect x="194" y="0" width="2" height="40" fill="#111" />
                <rect x="198" y="0" width="2" height="40" fill="#111" />
            </svg>
            <div style="font-size: 10px; letter-spacing: 2px;">* {{ $numeroTicket }} *</div>
        </div>

        <!-- PIE DE PÁGINA DEL TICKET -->
        <div class="ticket-footer-text">
            <strong>¡Gracias por tu visita y preferencia!</strong><br>
            Por favor preséntate 10 minutos antes de tu cita.<br>
            {{ $configuracion->politica_cancelacion ?? 'Cancelaciones con mínimo 12 horas de antelación.' }}<br>
            <small style="margin-top: 4px; display: block; font-size: 9px; color: #888;">
                Software LashApp &bull; Sistema POS v2.6
            </small>
        </div>

    </div>

    <script>
        function descargarTicketPOS() {
            const el = document.getElementById('ticketPos');
            if (!el) return;

            const btnDescargar = document.getElementById('btnDescargarTicket');
            const oldHtml = btnDescargar ? btnDescargar.innerHTML : '';
            if (btnDescargar) {
                btnDescargar.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Descargando...';
                btnDescargar.disabled = true;
            }

            if (typeof html2canvas !== 'undefined') {
                html2canvas(el, {
                    scale: 2,
                    backgroundColor: '#ffffff',
                    useCORS: true,
                    logging: false
                }).then(function(canvas) {
                    var link = document.createElement('a');
                    var num = '{{ $numeroTicket }}'.replace(/[^a-zA-Z0-9_-]/g, '_');
                    link.download = 'Ticket_' + num + '.png';
                    link.href = canvas.toDataURL('image/png');
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    if (btnDescargar) {
                        btnDescargar.innerHTML = oldHtml;
                        btnDescargar.disabled = false;
                    }
                }).catch(function(err) {
                    console.error('Error generando descarga PNG del ticket:', err);
                    if (btnDescargar) {
                        btnDescargar.innerHTML = oldHtml;
                        btnDescargar.disabled = false;
                    }
                });
            }
        }
        window.descargarTicketPOS = descargarTicketPOS;

        function descargarTicketPDF() {
            const el = document.getElementById('ticketPos');
            if (!el) return;

            const btnPdf = document.getElementById('btnDescargarPdf');
            const oldHtml = btnPdf ? btnPdf.innerHTML : '';
            if (btnPdf) {
                btnPdf.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Descargando PDF...';
                btnPdf.disabled = true;
            }

            var num = '{{ $numeroTicket }}'.replace(/[^a-zA-Z0-9_-]/g, '_');
            if (typeof html2pdf !== 'undefined') {
                var opt = {
                    margin:       [4, 4, 4, 4],
                    filename:     'Ticket_' + num + '.pdf',
                    image:        { type: 'jpeg', quality: 0.98 },
                    html2canvas:  { scale: 2, useCORS: true, backgroundColor: '#ffffff' },
                    jsPDF:        { unit: 'mm', format: [85, 230], orientation: 'portrait' }
                };
                html2pdf().set(opt).from(el).save().then(function() {
                    if (btnPdf) {
                        btnPdf.innerHTML = oldHtml;
                        btnPdf.disabled = false;
                    }
                }).catch(function(err) {
                    console.error('Error generando PDF del ticket:', err);
                    if (btnPdf) {
                        btnPdf.innerHTML = oldHtml;
                        btnPdf.disabled = false;
                    }
                    descargarTicketPOS();
                });
            } else {
                descargarTicketPOS();
                if (btnPdf) {
                    btnPdf.innerHTML = oldHtml;
                    btnPdf.disabled = false;
                }
            }
        }
        window.descargarTicketPDF = descargarTicketPDF;

        function cerrarOVolver(event, fallbackUrl) {
            if (window.history.length > 1) {
                event.preventDefault();
                window.history.back();
                return;
            }

            if (window.opener) {
                window.close();
            }

            setTimeout(function() {
                window.location.href = fallbackUrl;
            }, 80);
        }
    </script>

    @if(!empty($autoPrint))
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                setTimeout(descargarTicketPOS, 500);
            });
        </script>
    @endif

</body>
</html>
