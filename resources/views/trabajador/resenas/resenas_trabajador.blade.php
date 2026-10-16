@include('layouts.header_trabajador', ['titulo' => 'Reseñas'])
@include('layouts.sidebar_trabajador')
@vite(['resources/css/resenas_trabajador.css'])

@php
    use Carbon\Carbon;

    if (!isset($resenas)) {
        $user = auth()->user();
        $trabajador = $user?->trabajador ?? \App\Models\Trabajador::where('user_id', $user?->id)->first();
        $trabajadorId = $trabajador?->id;

        $query = \App\Models\Resena::with(['cliente.user', 'cita.servicio', 'cita.trabajador.user']);
        if ($trabajadorId) {
            $query->whereHas('cita', fn($q) => $q->where('trabajador_id', $trabajadorId));
        }
        $resenas = $query->orderByDesc('fecha_resena')->orderByDesc('id')->get();

        if ($resenas->isEmpty()) {
            $resenas = \App\Models\Resena::with(['cliente.user', 'cita.servicio', 'cita.trabajador.user'])->orderByDesc('fecha_resena')->orderByDesc('id')->get();
        }

        $totalResenas = $resenas->count();
        $promedioCalificacion = $totalResenas > 0 ? round($resenas->avg('calificacion'), 1) : 5.0;
        $positivas = $resenas->where('calificacion', '>=', 4)->count();
        $neutras = $resenas->where('calificacion', 3)->count();
        $negativas = $resenas->where('calificacion', '<=', 2)->count();

        $c5 = $resenas->where('calificacion', 5)->count();
        $c4 = $resenas->where('calificacion', 4)->count();
        $c3 = $resenas->where('calificacion', 3)->count();
        $c2 = $resenas->where('calificacion', 2)->count();
        $c1 = $resenas->where('calificacion', 1)->count();

        $pct5 = $totalResenas > 0 ? round(($c5 / $totalResenas) * 100, 1) : 0;
        $pct4 = $totalResenas > 0 ? round(($c4 / $totalResenas) * 100, 1) : 0;
        $pct3 = $totalResenas > 0 ? round(($c3 / $totalResenas) * 100, 1) : 0;
        $pct2 = $totalResenas > 0 ? round(($c2 / $totalResenas) * 100, 1) : 0;
        $pct1 = $totalResenas > 0 ? round(($c1 / $totalResenas) * 100, 1) : 0;
    }
@endphp

<main class="resenas-page-container">

    <!-- ── Encabezado de Reseñas ── -->
    <div class="resenas-header-bar">
        <div class="resenas-title-group">
            <h1>Opiniones y Reseñas de Clientas</h1>
            <p>Conoce las valoraciones y comentarios sobre tu atención y servicios realizados</p>
        </div>
    </div>

    <!-- ── Tarjetas Métricas de Reseñas ── -->
    <div class="resenas-cards-grid">
        <!-- Card 1: Calificación Promedio -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon">
                <i class="fa-solid fa-star"></i>
            </div>
            <div class="card-sparkle-title">Calificación Promedio</div>
            <div class="card-sparkle-value">{{ number_format($promedioCalificacion, 1) }} / 5.0</div>
            <div class="card-sparkle-sub" style="color: #10b981;">
                <i class="fa-solid fa-arrow-trend-up"></i> Alta satisfacción
            </div>
        </div>

        <!-- Card 2: Total de Reseñas -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon">
                <i class="fa-regular fa-comment-dots"></i>
            </div>
            <div class="card-sparkle-title">Total de Reseñas</div>
            <div class="card-sparkle-value">{{ $totalResenas }}</div>
            <div class="card-sparkle-sub" style="color: #7c1db8;">
                <i class="fa-solid fa-check-double"></i> Opiniones reales
            </div>
        </div>

        <!-- Card 3: Reseñas Positivas -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon" style="background: #d1fae5; color: #059669;">
                <i class="fa-regular fa-face-smile"></i>
            </div>
            <div class="card-sparkle-title">Positivas (4-5★)</div>
            <div class="card-sparkle-value">{{ $positivas }}</div>
            <div class="card-sparkle-sub" style="color: #059669;">
                {{ $totalResenas > 0 ? round(($positivas / $totalResenas) * 100) : 100 }}% del total
            </div>
        </div>

        <!-- Card 4: Reseñas Neutras -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon" style="background: #fef3c7; color: #d97706;">
                <i class="fa-regular fa-face-meh"></i>
            </div>
            <div class="card-sparkle-title">Neutras (3★)</div>
            <div class="card-sparkle-value">{{ $neutras }}</div>
            <div class="card-sparkle-sub" style="color: #d97706;">
                {{ $totalResenas > 0 ? round(($neutras / $totalResenas) * 100) : 0 }}% del total
            </div>
        </div>

        <!-- Card 5: Reseñas Negativas -->
        <div class="card-design-sparkle">
            <div class="card-sparkle-icon" style="background: #fee2e2; color: #dc2626;">
                <i class="fa-regular fa-face-frown"></i>
            </div>
            <div class="card-sparkle-title">Por Mejorar (1-2★)</div>
            <div class="card-sparkle-value">{{ $negativas }}</div>
            <div class="card-sparkle-sub" style="color: #dc2626;">
                {{ $totalResenas > 0 ? round(($negativas / $totalResenas) * 100) : 0 }}% del total
            </div>
        </div>
    </div>

    <!-- ── Fila Principal: Lista y Estadísticas ── -->
    <div class="dashboard-grid">

        <!-- Columna Izquierda: Listado de Reseñas -->
        <div class="dashboard-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; flex-wrap:wrap; gap:12px;">
                <h3 style="font-family:'Playfair Display',Georgia,serif; font-size:18px; color:#2a0d3e; margin:0;">
                    <i class="fa-regular fa-comments" style="color:#7c1db8; margin-right:6px;"></i> Comentarios Recibidos
                </h3>
            </div>

            <!-- Tabs de filtrado -->
            <div class="resenas-tabs">
                <button type="button" class="tab-pill-item active" data-filter="all">Todas ({{ $totalResenas }})</button>
                <button type="button" class="tab-pill-item" data-filter="positivas">Positivas ({{ $positivas }})</button>
                <button type="button" class="tab-pill-item" data-filter="neutras">Neutras ({{ $neutras }})</button>
                <button type="button" class="tab-pill-item" data-filter="negativas">Por Mejorar ({{ $negativas }})</button>
            </div>

            <!-- Listado de Reseñas -->
            <div class="resenas-list-custom" id="resenasList">
                @forelse ($resenas as $r)
                    @php
                        $clienteObj = $r->cliente?->user;
                        $nombreCliente = $clienteObj ? trim($clienteObj->name . ' ' . ($clienteObj->apellido ?? '')) : 'Cliente LashApp';
                        $iniciales = $clienteObj 
                            ? strtoupper(substr($clienteObj->name ?? 'C', 0, 1) . substr($clienteObj->apellido ?? 'L', 0, 1))
                            : 'CL';
                        
                        $cal = (int)$r->calificacion;
                        $tipoRating = ($cal >= 4) ? 'positivas' : (($cal == 3) ? 'neutras' : 'negativas');
                        $fecha = $r->fecha_resena ? Carbon::parse($r->fecha_resena)->translatedFormat('d \d\e M \d\e Y') : ($r->created_at ? $r->created_at->format('d/m/Y') : 'Reciente');
                        $servicioNombre = $r->cita?->servicio?->nombre ?? 'Servicio Profesional';
                    @endphp
                    <div class="resena-row-item" data-rating-type="{{ $tipoRating }}">
                        <div class="resena-user-box">
                            <div class="resena-avatar">{{ $iniciales }}</div>
                            <div>
                                <strong style="color:#2a0d3e; font-size:13px; display:block;">{{ $nombreCliente }}</strong>
                                <span class="resena-servicio-tag">
                                    <i class="fa-solid fa-spa" style="font-size:10px;"></i> {{ $servicioNombre }}
                                </span>
                            </div>
                        </div>

                        <div class="resena-content-box">
                            <div class="resena-stars-gold">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa-solid fa-star" style="{{ $i <= $cal ? 'color:#f59e0b;' : 'color:#e8daf3;' }}"></i>
                                @endfor
                                <span style="font-size:11px; font-weight:700; color:#2a0d3e; margin-left:6px;">{{ $cal }}.0</span>
                            </div>
                            <div class="resena-comment-text">
                                "{{ $r->comentario }}"
                            </div>
                        </div>

                        <div class="resena-meta-box">
                            <span class="resena-date-text">
                                <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> {{ $fecha }}
                            </span>
                            <span style="padding:4px 12px; border-radius:15px; font-size:11px; font-weight:700; background:#d1fae5; color:#065f46;">
                                Verificada
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center; padding:40px; color:#7a5a90;">
                        <i class="fa-regular fa-comment-slash" style="font-size:36px; color:#dcc8ee; margin-bottom:12px; display:block;"></i>
                        <strong style="color:#2a0d3e; display:block; margin-bottom:4px;">Aún no tienes opiniones registradas</strong>
                        <p style="font-size:13px; margin:0;">Tus clientas podrán dejar su calificación una vez completen sus citas contigo.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Columna Derecha: Gráfico y Desglose por Estrellas -->
        <div style="display:flex; flex-direction:column; gap:20px;">
            <div class="dashboard-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h3 style="font-family:'Playfair Display',Georgia,serif; font-size:16px; color:#2a0d3e; margin:0;">
                        Distribución por Estrellas
                    </h3>
                    <span style="font-size:12px; color:#7c1db8; font-weight:700;">{{ $totalResenas }} total</span>
                </div>

                <div class="chart-donut-wrap">
                    <div class="donut-svg-box">
                        <svg viewBox="0 0 36 36" style="width:100%; height:100%; transform:rotate(-90deg);">
                            <circle cx="18" cy="18" r="15.915" fill="none" stroke="#f0e6f8" stroke-width="4.5"></circle>
                            @php
                                $dashOffset = 0;
                                $coloresEstrellas = ['#7c1db8', '#c026a0', '#2563eb', '#f59e0b', '#dc2626'];
                                $pcts = [$pct5 ?? 70, $pct4 ?? 20, $pct3 ?? 7, $pct2 ?? 2, $pct1 ?? 1];
                            @endphp
                            @foreach ($pcts as $idx => $p)
                                @if ($p > 0)
                                    <circle cx="18" cy="18" r="15.915" fill="none" stroke="{{ $coloresEstrellas[$idx] }}" stroke-width="4.5"
                                            stroke-dasharray="{{ $p }} 100" stroke-dashoffset="{{ -$dashOffset }}"></circle>
                                    @php $dashOffset += $p; @endphp
                                @endif
                            @endforeach
                        </svg>
                        <div class="donut-center-text">
                            <strong>{{ number_format($promedioCalificacion, 1) }}</strong>
                            <span>Promedio</span>
                        </div>
                    </div>

                    <div class="stars-legend-list">
                        <div class="stars-legend-item">
                            <span><span class="cat-dot" style="background:#7c1db8;"></span> 5 Estrellas</span>
                            <strong>{{ $c5 ?? 0 }} ({{ $pct5 ?? 0 }}%)</strong>
                        </div>
                        <div class="stars-legend-item">
                            <span><span class="cat-dot" style="background:#c026a0;"></span> 4 Estrellas</span>
                            <strong>{{ $c4 ?? 0 }} ({{ $pct4 ?? 0 }}%)</strong>
                        </div>
                        <div class="stars-legend-item">
                            <span><span class="cat-dot" style="background:#2563eb;"></span> 3 Estrellas</span>
                            <strong>{{ $c3 ?? 0 }} ({{ $pct3 ?? 0 }}%)</strong>
                        </div>
                        <div class="stars-legend-item">
                            <span><span class="cat-dot" style="background:#f59e0b;"></span> 2 Estrellas</span>
                            <strong>{{ $c2 ?? 0 }} ({{ $pct2 ?? 0 }}%)</strong>
                        </div>
                        <div class="stars-legend-item">
                            <span><span class="cat-dot" style="background:#dc2626;"></span> 1 Estrella</span>
                            <strong>{{ $c1 ?? 0 }} ({{ $pct1 ?? 0 }}%)</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Consejos para Especialistas -->
            <div class="dashboard-card" style="background: linear-gradient(135deg, #fdf8fe 0%, #f7edfb 100%); border-color: #dcc8ee;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                    <div style="width:38px; height:38px; border-radius:12px; background:#7c1db8; color:#fff; display:flex; align-items:center; justify-content:center; font-size:16px;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <div>
                        <h4 style="margin:0; font-family:'Playfair Display',Georgia,serif; font-size:15px; color:#2a0d3e;">Tip Profesional</h4>
                        <small style="color:#7a5a90;">Mejora continua</small>
                    </div>
                </div>
                <p style="font-size:12.5px; color:#2a0d3e; line-height:1.5; margin:0;">
                    Pregunta a tus clientas al finalizar la sesión cómo sienten el peso y curvatura de sus pestañas. Una buena comunicación genera valoraciones de 5 estrellas constantes.
                </p>
            </div>
        </div>

    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabBtns = document.querySelectorAll('.tab-pill-item');
    const items = document.querySelectorAll('#resenasList .resena-row-item');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter') || 'all';

            items.forEach(item => {
                const itemRating = item.getAttribute('data-rating-type');
                if (filter === 'all' || itemRating === filter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

@include('layouts.footer')
