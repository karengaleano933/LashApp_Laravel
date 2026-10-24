<!-- Incluir Header superior de Cliente -->
@include('layouts.headercliente', ['titulo' => 'Mi Portal'])
@include('layouts.sidebarcliente')

@vite(['resources/js/modules/clientes/dashboardcliente.js'])

<!-- Contenido Principal -->
<div class="page-content" id="dashboardClienteContainer" data-session-success="{{ session('success') ?? '' }}">

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

<!-- Scripts interactivos extraídos al módulo Vite: resources/js/modules/clientes/dashboardcliente.js -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>
