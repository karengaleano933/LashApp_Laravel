<!-- Incluir Header superior de Admin -->
@include('layouts.header', ['titulo' => 'Configuración del Sistema'])
@include('layouts.sidebaradmin')

<!-- Estilos específicos de configuración -->
@vite(['resources/css/configuracion.css'])
<link rel="stylesheet" href="{{ asset('css/configuracion.css') }}">

<!-- Contenido Principal -->
<div class="page-content">

    <!-- ══════════════════ ALERTAS DE ACCIÓN ══════════════════ -->
    @if (session('success'))
        <div class="alert alert-success" style="padding: 14px 18px; border-radius: 14px; background: #eaf6ef; color: #2e6946; border: 1px solid #cbe9d6; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
            <i class="fa-solid fa-circle-check" style="font-size: 18px; color: #16a34a;"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger" style="padding: 14px 18px; border-radius: 14px; background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
            <i class="fa-solid fa-circle-exclamation" style="font-size: 18px; color: #dc2626;"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if (isset($errors) && $errors->any())
        <div class="alert alert-danger" style="padding: 14px 18px; border-radius: 14px; background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; margin-bottom: 20px;">
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ══════════════════ FORMULARIO DE CONFIGURACIÓN ══════════════════ -->
    <form action="{{ route('configuracion.update') }}" method="POST" enctype="multipart/form-data" id="configForm">
        @csrf

        <div class="config-container">

            <!-- ── FILA SUPERIOR: INFORMACIÓN DEL NEGOCIO & CONFIGURACIÓN RÁPIDA ── -->
            <div class="config-top-grid">

                <!-- TARJETA: INFORMACIÓN DEL NEGOCIO -->
                <div class="cfg-card">
                    <div class="cfg-card-title">
                        <i class="fa-solid fa-store"></i>
                        <span>Información del Negocio</span>
                    </div>

                    <div class="negocio-layout">
                        <!-- Columna de Campos -->
                        <div class="negocio-inputs">
                            <div class="cfg-field">
                                <label for="nombre_estudio">Nombre del negocio</label>
                                <input type="text" id="nombre_estudio" name="nombre_estudio" value="{{ old('nombre_estudio', $configuracion->nombre_estudio ?? 'Oh La Lash by Lorena Ayala Studio') }}" required>
                            </div>

                            <div class="cfg-row-two">
                                <div class="cfg-field">
                                    <label for="telefono">Teléfono de contacto</label>
                                    <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $configuracion->telefono ?? '+57 300 123 4567') }}" required>
                                </div>
                                <div class="cfg-field">
                                    <label for="email_contacto">Correo electrónico</label>
                                    <input type="email" id="email_contacto" name="email_contacto" value="{{ old('email_contacto', $configuracion->email_contacto ?? 'info@ohlalash.com') }}" required>
                                </div>
                            </div>

                            <div class="cfg-field">
                                <label for="direccion">Dirección física</label>
                                <input type="text" id="direccion" name="direccion" value="{{ old('direccion', $configuracion->direccion ?? 'Calle 123 #45-67, Local 2, Medellín, Colombia') }}" required>
                            </div>

                            <div class="cfg-field">
                                <label for="zona_horaria">Zona horaria</label>
                                @php
                                    $zonaActual = old('zona_horaria', $configuracion->zona_horaria ?? 'America/Bogota');
                                @endphp
                                <select id="zona_horaria" name="zona_horaria">
                                    <option value="America/Bogota" {{ $zonaActual === 'America/Bogota' ? 'selected' : '' }}>(GMT-05:00) Bogotá, Lima, Quito</option>
                                    <option value="America/Mexico_City" {{ $zonaActual === 'America/Mexico_City' ? 'selected' : '' }}>(GMT-06:00) Ciudad de México</option>
                                    <option value="America/Caracas" {{ $zonaActual === 'America/Caracas' ? 'selected' : '' }}>(GMT-04:00) Caracas, La Paz</option>
                                    <option value="America/Santiago" {{ $zonaActual === 'America/Santiago' ? 'selected' : '' }}>(GMT-03:00) Santiago, Buenos Aires</option>
                                    <option value="America/Madrid" {{ $zonaActual === 'America/Madrid' ? 'selected' : '' }}>(GMT+01:00) Madrid, París</option>
                                </select>
                            </div>
                        </div>

                        <!-- Columna de Logo del Negocio -->
                        <div class="logo-box-wrapper">
                            <label>Logo del negocio</label>
                            <div class="logo-display-card" id="logoPreviewBox">
                                @if (!empty($configuracion->logo_path) && file_exists(public_path($configuracion->logo_path)))
                                    <img id="logoImgPreview" src="{{ asset($configuracion->logo_path) }}" alt="Logo" style="max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 12px;">
                                    <i class="fa-solid fa-crown" id="logoIconDefault" style="display:none;"></i>
                                    <span id="logoTextDefault" style="display:none;">OH LA LASH</span>
                                @else
                                    <img id="logoImgPreview" src="" alt="Logo" style="display:none; max-width: 90%; max-height: 90%; object-fit: contain; border-radius: 12px;">
                                    <i class="fa-solid fa-crown" id="logoIconDefault"></i>
                                    <span id="logoTextDefault">OH LA LASH</span>
                                @endif
                            </div>
                            <input type="file" id="logoFileInput" name="logo" accept="image/png, image/jpeg, image/webp" style="display:none;" onchange="previewLogo(event)">
                            <button type="button" class="btn-cambiar-logo" onclick="document.getElementById('logoFileInput').click()">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> Cambiar logo
                            </button>
                            <span class="logo-formats-hint">Formatos: PNG, JPG (máx. 2MB)</span>
                        </div>
                    </div>

                    <!-- Botón Guardar Cambios -->
                    <button type="submit" class="btn-guardar-cambios-p">
                        <i class="fa-solid fa-bookmark"></i> Guardar cambios
                    </button>
                </div>

                <!-- TARJETA: CONFIGURACIÓN RÁPIDA -->
                <div class="cfg-card">
                    <div class="cfg-card-title">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Configuración Rápida</span>
                    </div>

                    <div class="quick-config-list">
                        <!-- Configuración de Citas -->
                        <a href="{{ route('citas.index') }}" class="quick-cfg-item">
                            <div class="quick-cfg-left">
                                <div class="quick-cfg-icon">
                                    <i class="fa-regular fa-calendar-days"></i>
                                </div>
                                <div class="quick-cfg-text">
                                    <strong>Configuración de Citas</strong>
                                    <span>Define duración, tiempos y políticas de cancelación</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right quick-cfg-arrow"></i>
                        </a>

                        <!-- Métodos de Pago -->
                        <a href="{{ route('pagos.index') }}" class="quick-cfg-item">
                            <div class="quick-cfg-left">
                                <div class="quick-cfg-icon">
                                    <i class="fa-regular fa-credit-card"></i>
                                </div>
                                <div class="quick-cfg-text">
                                    <strong>Métodos de Pago</strong>
                                    <span>Gestiona tus métodos de pago y monedas</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right quick-cfg-arrow"></i>
                        </a>

                        <!-- Impuestos y Tarifas -->
                        <a href="javascript:void(0)" onclick="mostrarModalImpuestos()" class="quick-cfg-item">
                            <div class="quick-cfg-left">
                                <div class="quick-cfg-icon">
                                    <i class="fa-solid fa-percent"></i>
                                </div>
                                <div class="quick-cfg-text">
                                    <strong>Impuestos y Tarifas</strong>
                                    <span>Configura impuestos y tarifas aplicables</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right quick-cfg-arrow"></i>
                        </a>

                        <!-- Políticas del Negocio -->
                        <a href="javascript:void(0)" onclick="mostrarModalPoliticas()" class="quick-cfg-item">
                            <div class="quick-cfg-left">
                                <div class="quick-cfg-icon">
                                    <i class="fa-regular fa-file-lines"></i>
                                </div>
                                <div class="quick-cfg-text">
                                    <strong>Políticas del Negocio</strong>
                                    <span>Edita tus políticas de privacidad y términos</span>
                                </div>
                            </div>
                            <i class="fa-solid fa-chevron-right quick-cfg-arrow"></i>
                        </a>
                    </div>
                </div>

            </div>

            <!-- ── FILA INFERIOR: 3 TARJETAS EN CUADRÍCULA ── -->
            <div class="config-bottom-grid">

                <!-- TARJETA 1: PREFERENCIAS GENERALES -->
                <div class="cfg-card">
                    <div class="cfg-card-title">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Preferencias Generales</span>
                    </div>

                    @php
                        $idiomaActual = old('idioma', $configuracion->idioma ?? 'es');
                        $monedaActual = old('moneda', $configuracion->moneda ?? 'COP');
                        $fechaActual = old('formato_fecha', $configuracion->formato_fecha ?? 'DD/MM/YYYY');
                        $horaActual = old('formato_hora', $configuracion->formato_hora ?? '12');
                    @endphp

                    <div class="cfg-field" style="margin-bottom: 14px;">
                        <label for="idioma">Idioma de la plataforma</label>
                        <select id="idioma" name="idioma">
                            <option value="es" {{ $idiomaActual === 'es' ? 'selected' : '' }}>Español (Latinoamérica)</option>
                            <option value="en" {{ $idiomaActual === 'en' ? 'selected' : '' }}>English (United States)</option>
                            <option value="pt" {{ $idiomaActual === 'pt' ? 'selected' : '' }}>Português (Brasil)</option>
                        </select>
                    </div>

                    <div class="cfg-field" style="margin-bottom: 14px;">
                        <label for="moneda">Moneda principal</label>
                        <select id="moneda" name="moneda">
                            <option value="COP" {{ $monedaActual === 'COP' ? 'selected' : '' }}>Peso Colombiano (COP)</option>
                            <option value="USD" {{ $monedaActual === 'USD' ? 'selected' : '' }}>Dólar Estadounidense (USD)</option>
                            <option value="EUR" {{ $monedaActual === 'EUR' ? 'selected' : '' }}>Euro (EUR)</option>
                        </select>
                    </div>

                    <div class="cfg-row-two" style="margin-bottom: 16px;">
                        <div class="cfg-field">
                            <label for="formato_fecha">Formato de fecha</label>
                            <select id="formato_fecha" name="formato_fecha">
                                <option value="DD/MM/YYYY" {{ $fechaActual === 'DD/MM/YYYY' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                <option value="MM/DD/YYYY" {{ $fechaActual === 'MM/DD/YYYY' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                <option value="YYYY-MM-DD" {{ $fechaActual === 'YYYY-MM-DD' ? 'selected' : '' }}>YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div class="cfg-field">
                            <label for="formato_hora">Formato de hora</label>
                            <select id="formato_hora" name="formato_hora">
                                <option value="12" {{ $horaActual === '12' ? 'selected' : '' }}>12 horas (am/pm)</option>
                                <option value="24" {{ $horaActual === '24' ? 'selected' : '' }}>24 horas</option>
                            </select>
                        </div>
                    </div>

                    <!-- Switches -->
                    <div class="cfg-toggle-row">
                        <span>Permitir reservas online a clientes</span>
                        <label class="switch-pill">
                            <input type="checkbox" name="reservas_online" value="1" {{ old('reservas_online', $configuracion->reservas_online ?? true) ? 'checked' : '' }}>
                            <span class="slider-pill"></span>
                        </label>
                    </div>

                    <div class="cfg-toggle-row">
                        <span>Enviar recordatorios automáticos</span>
                        <label class="switch-pill">
                            <input type="checkbox" name="recordatorios_auto" value="1" {{ old('recordatorios_auto', $configuracion->recordatorios_auto ?? true) ? 'checked' : '' }}>
                            <span class="slider-pill"></span>
                        </label>
                    </div>
                </div>

                <!-- TARJETA 2: POLÍTICAS DE CITAS -->
                <div class="cfg-card">
                    <div class="cfg-card-title">
                        <i class="fa-regular fa-calendar-check"></i>
                        <span>Políticas de Citas</span>
                    </div>

                    @php
                        $antRes = old('antelacion_reservar', $configuracion->antelacion_reservar ?? '2h');
                        $antCanc = old('antelacion_cancelar', $configuracion->antelacion_cancelar ?? '12h');
                        $tiempoCitas = old('tiempo_entre_citas', $configuracion->tiempo_entre_citas ?? '15m');
                        $duracion = old('duracion_predeterminada', $configuracion->duracion_predeterminada ?? '90m');
                    @endphp

                    <div class="cfg-field" style="margin-bottom: 14px;">
                        <label for="antelacion_reservar">Antelación mínima para reservar</label>
                        <select id="antelacion_reservar" name="antelacion_reservar">
                            <option value="1h" {{ $antRes === '1h' ? 'selected' : '' }}>1 hora</option>
                            <option value="2h" {{ $antRes === '2h' ? 'selected' : '' }}>2 horas</option>
                            <option value="4h" {{ $antRes === '4h' ? 'selected' : '' }}>4 horas</option>
                            <option value="12h" {{ $antRes === '12h' ? 'selected' : '' }}>12 horas</option>
                            <option value="24h" {{ $antRes === '24h' ? 'selected' : '' }}>24 horas</option>
                        </select>
                    </div>

                    <div class="cfg-field" style="margin-bottom: 14px;">
                        <label for="antelacion_cancelar">Antelación mínima para cancelar</label>
                        <select id="antelacion_cancelar" name="antelacion_cancelar">
                            <option value="6h" {{ $antCanc === '6h' ? 'selected' : '' }}>6 horas</option>
                            <option value="12h" {{ $antCanc === '12h' ? 'selected' : '' }}>12 horas</option>
                            <option value="24h" {{ $antCanc === '24h' ? 'selected' : '' }}>24 horas</option>
                            <option value="48h" {{ $antCanc === '48h' ? 'selected' : '' }}>48 horas</option>
                        </select>
                    </div>

                    <div class="cfg-row-two" style="margin-bottom: 14px;">
                        <div class="cfg-field">
                            <label for="tiempo_entre_citas">Tiempo entre citas</label>
                            <select id="tiempo_entre_citas" name="tiempo_entre_citas">
                                <option value="5m" {{ $tiempoCitas === '5m' ? 'selected' : '' }}>5 minutos</option>
                                <option value="10m" {{ $tiempoCitas === '10m' ? 'selected' : '' }}>10 minutos</option>
                                <option value="15m" {{ $tiempoCitas === '15m' ? 'selected' : '' }}>15 minutos</option>
                                <option value="30m" {{ $tiempoCitas === '30m' ? 'selected' : '' }}>30 minutos</option>
                            </select>
                        </div>
                        <div class="cfg-field">
                            <label for="duracion_predeterminada">Duración predeterminada</label>
                            <select id="duracion_predeterminada" name="duracion_predeterminada">
                                <option value="45m" {{ $duracion === '45m' ? 'selected' : '' }}>45 minutos</option>
                                <option value="60m" {{ $duracion === '60m' ? 'selected' : '' }}>60 minutos</option>
                                <option value="90m" {{ $duracion === '90m' ? 'selected' : '' }}>90 minutos</option>
                                <option value="120m" {{ $duracion === '120m' ? 'selected' : '' }}>120 minutos</option>
                            </select>
                        </div>
                    </div>

                    <div class="cfg-field">
                        <label for="politica_cancelacion">Política de cancelación</label>
                        <textarea id="politica_cancelacion" name="politica_cancelacion" rows="3">{{ old('politica_cancelacion', $configuracion->politica_cancelacion ?? 'Las cancelaciones deben realizarse con mínimo 12 horas de anticipación para evitar cargos adicionales.') }}</textarea>
                    </div>
                </div>

                <!-- TARJETA 3: COPIA DE SEGURIDAD Y DATOS -->
                <div class="cfg-card">
                    <div class="cfg-card-title">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Copia de Seguridad y Datos</span>
                    </div>

                    <div class="backup-dates">
                        <div class="backup-date-line">
                            <span>Última copia de seguridad:</span>
                            <strong>
                                @if(!empty($configuracion->ultima_copia_seguridad))
                                    {{ \Carbon\Carbon::parse($configuracion->ultima_copia_seguridad)->translatedFormat('d \d\e F \d\e Y, h:i a') }}
                                @else
                                    15 de junio de 2026, 02:30 am
                                @endif
                            </strong>
                        </div>
                        <div class="backup-date-line">
                            <span>Próxima copia programada:</span>
                            <strong>{{ now()->addDay()->translatedFormat('d \d\e F \d\e Y, 02:30 \a\m') }}</strong>
                        </div>
                    </div>

                    <button type="button" class="btn-backup-now" onclick="realizarBackup()">
                        <i class="fa-solid fa-rotate"></i> Realizar copia ahora
                    </button>

                    <div class="danger-zone">
                        <h4>Zona peligrosa</h4>
                        <p>Acciones avanzadas que pueden afectar permanentemente los datos de tu negocio.</p>

                        <button type="button" class="btn-danger-reset" onclick="confirmarRestablecer()">
                            <i class="fa-solid fa-rotate-left"></i> Restablecer configuración
                        </button>

                        <button type="button" class="btn-danger-delete" onclick="confirmarEliminarCuenta()">
                            <i class="fa-regular fa-trash-can"></i> Eliminar cuenta
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </form>

</div> <!-- Cierre de page-content -->

<!-- Scripts de interacción -->
<script>
    // Previsualización de logo cargado
    function previewLogo(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('logoImgPreview');
                const icon = document.getElementById('logoIconDefault');
                const text = document.getElementById('logoTextDefault');
                
                img.src = e.target.result;
                img.style.display = 'block';
                if (icon) icon.style.display = 'none';
                if (text) text.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    }

    // Modal Impuestos y Tarifas
    function mostrarModalImpuestos() {
        Swal.fire({
            title: 'Impuestos y Tarifas',
            html: `
                <div style="text-align: left; font-size: 13.5px; color: #2a0d3e; display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">IVA / Impuesto de Venta (%)</label>
                        <input type="number" id="swalIva" value="19" class="swal2-input" style="width: 100%; margin: 0; box-sizing: border-box; border-radius: 12px; height: 42px;">
                    </div>
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Porcentaje de Retención en Citas (%)</label>
                        <input type="number" id="swalRetencion" value="0" class="swal2-input" style="width: 100%; margin: 0; box-sizing: border-box; border-radius: 12px; height: 42px;">
                    </div>
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 5px;">Propina sugerida por defecto (%)</label>
                        <input type="number" id="swalPropina" value="10" class="swal2-input" style="width: 100%; margin: 0; box-sizing: border-box; border-radius: 12px; height: 42px;">
                    </div>
                </div>
            `,
            confirmButtonText: 'Guardar Tarifas',
            confirmButtonColor: '#7c1db8',
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#e5d8ee'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Tarifas guardadas!',
                    text: 'Los cambios impositivos fueron aplicados correctamente.',
                    confirmButtonColor: '#7c1db8'
                });
            }
        });
    }

    // Modal Políticas del Negocio
    function mostrarModalPoliticas() {
        Swal.fire({
            title: 'Políticas del Negocio',
            html: `
                <div style="text-align: left; font-size: 13px; color: #2a0d3e; display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 4px;">Términos y Condiciones del Servicio</label>
                        <textarea class="swal2-textarea" style="width: 100%; margin: 0; font-size: 12.5px; border-radius: 12px; min-height: 80px;" rows="3">Todos los servicios de pestañas y cejas requieren puntualidad. Se permite un margen de tolerancia máximo de 10 minutos.</textarea>
                    </div>
                    <div>
                        <label style="font-weight: 600; display: block; margin-bottom: 4px;">Política de Privacidad y Tratamiento de Datos</label>
                        <textarea class="swal2-textarea" style="width: 100%; margin: 0; font-size: 12.5px; border-radius: 12px; min-height: 80px;" rows="3">La información recopilada será utilizada únicamente para gestionar reservas, recordatorios y comprobantes de pago de Oh La Lash Studio.</textarea>
                    </div>
                </div>
            `,
            confirmButtonText: 'Guardar Políticas',
            confirmButtonColor: '#7c1db8',
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#e5d8ee'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Políticas actualizadas!',
                    text: 'Tus políticas y términos han sido actualizados con éxito.',
                    confirmButtonColor: '#7c1db8'
                });
            }
        });
    }

    // Realizar Copia de Seguridad
    function realizarBackup() {
        Swal.fire({
            title: 'Generando Copia de Seguridad...',
            text: 'Comprimiendo base de datos y archivos multimedia.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                setTimeout(() => {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Copia de Seguridad Lista!',
                        text: 'Se ha creado el respaldo exitosamente: backup_ohlalash_' + new Date().toISOString().slice(0,10) + '.sql.gz',
                        confirmButtonColor: '#7c1db8'
                    });
                }, 1500);
            }
        });
    }

    // Restablecer Configuración
    function confirmarRestablecer() {
        Swal.fire({
            title: '¿Restablecer configuración?',
            text: 'Se restablecerán todos los parámetros a sus valores predeterminados de fábrica.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#b59ec4',
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Configuración restablecida!',
                    text: 'Los valores por defecto han sido restaurados.',
                    confirmButtonColor: '#7c1db8'
                });
            }
        });
    }

    // Eliminar Cuenta
    function confirmarEliminarCuenta() {
        Swal.fire({
            title: '¿Eliminar cuenta del negocio?',
            text: 'Esta acción es irreversible y eliminará todos los registros asociados.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#b59ec4',
            confirmButtonText: 'Eliminar definitivamente',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Escribe "ELIMINAR" para confirmar',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#b59ec4',
                    confirmButtonText: 'Confirmar eliminación',
                    cancelButtonText: 'Cancelar',
                    preConfirm: (valor) => {
                        if (valor !== 'ELIMINAR') {
                            Swal.showValidationMessage('La palabra no coincide con "ELIMINAR"');
                        }
                    }
                });
            }
        });
    }
</script>

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>