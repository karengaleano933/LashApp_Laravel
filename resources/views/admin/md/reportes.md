# Explicación de mi Vista de Reportes y Rendimiento (`reportes/reportes.blade.php`)

Esta vista es el centro de analítica y estadísticas de LashApp. Me permite evaluar cómo le está yendo al negocio en dinero y clientas: muestra gráficos interactivos de ingresos diarios, participación por tipo de servicio, tarjetas de métricas clave, comparaciones porcentuales respecto al mes o año anterior, distribución de citas por estado y un botón para exportar todo el informe a PDF o imprimirlo.

---

### 1. Inclusiones, estilos y motor de gráficos

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header', ['titulo' => 'Reportes y Rendimiento'])` | Cargo la cabecera general con el título de la pestaña. |
| `@include('layouts.sidebaradmin')` | Cargo el menú lateral de administración. |
| `@vite(['resources/css/reportes.css', 'resources/js/modules/admin/reportes.js'])` | Conecto los estilos específicos de reportes y el script interactivo mediante Vite. |
| `<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>` | Cargo la librería externa Chart.js encargada de dibujar los gráficos de líneas y de dona en pantalla. |

---

### 2. Barra de filtros temporales y exportación

| Código | ¿Qué hace? |
| :--- | :--- |
| `<input type="text" value="{{ now()->subMonth()->format('d/m/Y') }} - {{ now()->format('d/m/Y') }}" readonly>` | Muestra el rango de fechas analizado (los últimos 30 días hasta hoy). |
| `<select> <option value="anterior">Período anterior</option> ... </select>` | Permite elegir contra qué período quiero comparar los resultados (contra el mes inmediatamente anterior o contra el año pasado). |
| `<button type="button" class="btn-exportar-reporte" onclick="window.print()">` | Botón con ícono de PDF que abre la ventana de impresión para guardar o imprimir el reporte. |

---

### 3. Tarjetas de métricas superiores (Indicadores clave)

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="rep-metric-card"> ... ${{ number_format((float)($totalIngresos ?? 0), 0, ',', '.') }} ... </div>` | Tarjeta morada que muestra el total de ingresos recaudados en el período con su porcentaje de crecimiento (+12%). |
| `<div class="rep-metric-card"> ... {{ $totalCitas ?? 0 }} ... </div>` | Tarjeta dorada que muestra el total de citas gestionadas en el período. |
| `<div class="rep-metric-card"> ... {{ $totalClientes ?? 0 }} ... </div>` | Tarjeta verde con el número de clientes nuevos que llegaron al estudio. |
| `<div class="rep-metric-card"> ... {{ $promedioEstrellas ?? 4.8 }} / 5 ... </div>` | Tarjeta rosada con ícono de estrella que muestra la calificación promedio de satisfacción del negocio. |

---

### 4. Gráficos interactivos de ingresos

| Código | ¿Qué hace? |
| :--- | :--- |
| `<canvas id="chartIngresosDia" data-labels="..." data-values="..."></canvas>` | Lienzo donde Chart.js dibuja una curva de líneas moradas mostrando la evolución de las ventas día a día durante la última semana. |
| `<canvas id="chartIngresosServicio" data-labels="..." data-values="..."></canvas>` | Lienzo donde Chart.js dibuja un gráfico circular tipo dona mostrando qué porcentaje de los ingresos proviene de cada servicio (Extensiones, Lifting, Cejas, etc.). |
| `@forelse ($labelsServicios ?? [] as $idx => $labelServ) ... @endforelse` | Bucle que calcula el porcentaje de participación de cada tratamiento y dibuja la lista de etiquetas con sus puntos de color y porcentajes exactos (ej. 55%). |

---

### 5. Tabla comparativa de períodos

| Código | ¿Qué hace? |
| :--- | :--- |
| `<table class="resumen-tabla"> ... </table>` | Tabla que compara lado a lado las cifras del período actual contra el período anterior. |
| `<td>Ingresos Totales</td> <td>${{ number_format(...) }}</td> <td>$3.160.000</td> <td class="trend-up">+12.3%</td>` | Fila comparativa de dinero facturado con flecha verde de incremento. |
| `<td>Total de Citas</td> <td>{{ $totalCitas }}</td> <td>213</td> <td class="trend-up">+8.0%</td>` | Comparativa del volumen de citas atendidas. |
| `<td>Clientes Nuevos</td> <td>{{ $totalClientes }}</td> <td>23</td> <td class="trend-up">+21.7%</td>` | Comparativa de adquisición de clientas. |
| `<td>Citas Canceladas</td> <td>{{ $citasCanceladas }}</td> <td>37</td> <td class="trend-down">-13.5%</td>` | Comparativa de cancelaciones con flecha roja de disminución favorable. |
| `<td>Calificación Promedio</td> <td>{{ $promedioEstrellas }} / 5</td> <td>4.2 / 5</td> <td>+0.3</td>` | Comparativa de la reputación en estrellas. |

---

### 6. Gráfico de citas por estado y datos destacados

| Código | ¿Qué hace? |
| :--- | :--- |
| `<canvas id="chartCitasEstado" data-labels="..." data-values="..."></canvas>` | Gráfico circular pequeño que ilustra visualmente la proporción entre citas completadas (morado), pendientes (amarillo) y canceladas (rojo). |
| `<div class="highlight-box"> ... <label>Mejor Día de la Semana</label> <strong>Sábado</strong> ... </div>` | Cuadro destacado que resalta cuál es el día con más clientas e ingresos de la semana. |
| `<div class="highlight-box"> ... <label>Mejor Franja Horaria</label> <strong>11:00 am - 1:00 pm</strong> ... </div>` | Cuadro destacado que identifica las horas de mayor concurrencia en el salón. |
| `<div class="reportes-info-banner"> ... </div>` | Mensaje informativo al pie de página recordando que estos datos sirven para la toma estratégica de decisiones. |
| `@include('layouts.footer')` | Pie de página del sistema. |
