# Explicación de mi código de Reportes y Estadísticas (`reportes.js`)

En este archivo me encargo de dibujar los gráficos visuales de **LashApp** usando la librería `Chart.js`. En lugar de mostrar solo números fríos o tablas aburridas, este código convierte las ventas y citas en gráficos profesionales: una gráfica de línea suave para los ingresos del día a día y gráficos circulares tipo dona para ver qué servicios generan más dinero y cuántas citas se completaron o cancelaron.


---

## 1. Libreta de opciones y paleta de colores (`DEFAULT_CONFIG`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `const DEFAULT_CONFIG = {` | Mi libreta donde defino dónde dibujar los gráficos y qué colores usar. |
| `chartIngresosDia: '#chartIngresosDia',` | El lienzo (`<canvas>`) donde se pintará la gráfica de líneas de ingresos por día. |
| `chartIngresosServicio: '#chartIngresosServicio',` | El lienzo donde se pintará la dona de ingresos por cada servicio. |
| `chartCitasEstado: '#chartCitasEstado',` | El lienzo donde se pintará la dona de estados de citas (completadas, pendientes, canceladas). |
| `colors: { primary: '#7c1db8', pink: '#ec4899', ... }` | Mi paleta de colores estilizada (morado corporativo, fucsia para pestañas, naranja de advertencia, verde de éxito y rojo de cancelación). |

---

## 2. Lector seguro de datos (`safeJsonParse`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function safeJsonParse(attr, fallback) {` | Una mini-herramienta para leer datos que vienen desde la base de datos a través de atributos HTML. |
| `if (!attr) return fallback;` | Si el lienzo no trajo datos, devuelvo unos valores de respaldo por defecto para que la gráfica no quede vacía. |
| `try { return JSON.parse(attr); }` | Intento convertir el texto en una lista de números o nombres reales. |
| `catch { return fallback; }` | Si el formato venía dañado, no rompo la pantalla: simplemente uso los valores de respaldo de forma segura. |

---

## 3. Gráfico de Línea: Ingresos por Día (`setupChartIngresosDia`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupChartIngresosDia(config) {` | La función que dibuja la curva de ingresos a lo largo de la semana. |
| `if (typeof window.Chart === 'undefined') return;` | Verifico que la librería Chart.js esté cargada en el navegador. |
| `const canvas = document.querySelector(...); if (!canvas) return;` | Busco el lienzo en la pantalla; si no existe, no hago nada. |
| `const labels = safeJsonParse(canvas.dataset.labels, ['Lun', ...]);` | Leo los días de la semana (Lunes, Martes, Miércoles...). |
| `const data = safeJsonParse(canvas.dataset.values, [350000, ...]);` | Leo la cantidad de dinero ganada en cada uno de esos días. |
| `new window.Chart(canvas, { type: 'line', ... });` | Creo una gráfica de tipo línea. |
| `borderColor: config.colors.primary,` | La línea principal es de color morado LashApp. |
| `backgroundColor: 'rgba(124, 29, 184, 0.08)', fill: true,` | Relleno la parte inferior de la línea con un sombreado morado muy suave y elegante. |
| `tension: 0.35,` | Hago que la curva sea redondeada y fluida, en lugar de picos rectos y toscos. |
| `pointBackgroundColor: config.colors.primary, pointRadius: 4,` | Coloco puntitos morados de 4 píxeles en cada día para que sea fácil ver el valor. |
| `callback: function (value) { return '$' + (value / 1000) + 'k'; }` | En el eje vertical formateo el dinero para que se lea fácil: en vez de mostrar `$500000`, muestro `$500k` para no saturar la pantalla con ceros. |

---

## 4. Gráfico de Dona: Ingresos por Servicio (`setupChartIngresosServicio`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupChartIngresosServicio(config) {` | La función que muestra qué tratamientos son los más rentables. |
| `const labels = safeJsonParse(..., ['Ext. Pestañas', 'Lifting', ...]);` | Los nombres de los servicios del catálogo. |
| `const data = safeJsonParse(..., [55, 25, 15, 5]);` | El porcentaje o dinero que aporta cada uno al total del negocio. |
| `new window.Chart(canvas, { type: 'doughnut', ... });` | Creo un gráfico circular de tipo dona. |
| `backgroundColor: [ config.colors.primary, config.colors.pink, ... ],` | Cada porción de la dona tiene un color distinto y armonioso. |
| `borderWidth: 0,` | Quito bordes gruesos para que el diseño se vea moderno y limpio. |
| `cutout: '70%'` | Hago el agujero del centro de la dona bastante amplio (al 70%) para darle un toque minimalista. |

---

## 5. Gráfico de Dona: Estado de las Citas (`setupChartCitasEstado`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `function setupChartCitasEstado(config) {` | La función que muestra la efectividad de las citas del salón. |
| `const labels = safeJsonParse(..., ['Completadas', 'Pendientes', 'Canceladas']);` | Los 3 grupos principales de citas. |
| `const data = safeJsonParse(..., [198, 18, 14]);` | La cantidad exacta de citas en cada grupo. |
| `backgroundColor: [config.colors.primary, config.colors.warning, config.colors.danger],` | Verde/Morado para citas completadas con éxito, amarillo para pendientes y rojo para canceladas. |
| `cutout: '70%'` | Mantengo el mismo estilo de dona elegante con agujero central amplio. |

---

## 6. Inicialización del Módulo (`initReportes`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `export function initReportes(customConfig = {}) {` | Pone a andar los tres gráficos juntos. |
| `setupChartIngresosDia(config);` | Dibuja la curva semanal de ingresos. |
| `setupChartIngresosServicio(config);` | Dibuja la dona de servicios populares. |
| `setupChartCitasEstado(config);` | Dibuja la dona de distribución de citas. |
| `document.addEventListener('DOMContentLoaded', ...);` | Apenas se cargue la página en pantalla, revisa si existe el lienzo de reportes y los dibuja automáticamente. |
