/**
 * ============================================================================
 * Módulo de Reportes y Estadísticas (LashApp - Admin)
 * ============================================================================
 *
 * Segmentación de responsabilidades:
 * 1. Gráfico de Ingresos por Día (Línea con gradiente).
 * 2. Gráfico de Ingresos por Servicio (Dona).
 * 3. Gráfico de Distribución de Citas por Estado (Dona).
 *
 * Parametrización:
 * - Los datos, etiquetas y colores se leen desde atributos data-* de cada canvas
 *   o mediante opciones configurables.
 */

const DEFAULT_CONFIG = {
    selectors: {
        chartIngresosDia: '#chartIngresosDia',
        chartIngresosServicio: '#chartIngresosServicio',
        chartCitasEstado: '#chartCitasEstado',
    },
    colors: {
        primary: '#7c1db8',
        pink: '#ec4899',
        warning: '#f59e0b',
        success: '#10b981',
        indigo: '#6366f1',
        danger: '#ef4444',
    }
};

/**
 * Parsea atributos JSON de elementos de forma segura
 */
function safeJsonParse(attr, fallback) {
    if (!attr) return fallback;
    try {
        return JSON.parse(attr);
    } catch {
        return fallback;
    }
}

/**
 * 1. Segmento: Gráfico Ingresos por Día
 */
function setupChartIngresosDia(config) {
    if (typeof window.Chart === 'undefined') return;
    const canvas = document.querySelector(config.selectors.chartIngresosDia);
    if (!canvas) return;

    const labels = safeJsonParse(canvas.dataset.labels, ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']);
    const data = safeJsonParse(canvas.dataset.values, [350000, 420000, 390000, 510000, 680000, 920000, 280000]);

    new window.Chart(canvas, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos ($)',
                data: data,
                borderColor: config.colors.primary,
                backgroundColor: 'rgba(124, 29, 184, 0.08)',
                borderWidth: 3,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: config.colors.primary,
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function (value) {
                            return '$' + (value / 1000) + 'k';
                        }
                    },
                    grid: { color: '#f0e6f8' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}

/**
 * 2. Segmento: Gráfico Ingresos por Servicio
 */
function setupChartIngresosServicio(config) {
    if (typeof window.Chart === 'undefined') return;
    const canvas = document.querySelector(config.selectors.chartIngresosServicio);
    if (!canvas) return;

    const labels = safeJsonParse(canvas.dataset.labels, ['Ext. Pestañas', 'Lifting Pestañas', 'Cejas', 'Otros']);
    const data = safeJsonParse(canvas.dataset.values, [55, 25, 15, 5]);

    new window.Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    config.colors.primary,
                    config.colors.pink,
                    config.colors.warning,
                    config.colors.success,
                    config.colors.indigo
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '70%'
        }
    });
}

/**
 * 3. Segmento: Gráfico Citas por Estado
 */
function setupChartCitasEstado(config) {
    if (typeof window.Chart === 'undefined') return;
    const canvas = document.querySelector(config.selectors.chartCitasEstado);
    if (!canvas) return;

    const labels = safeJsonParse(canvas.dataset.labels, ['Completadas', 'Pendientes', 'Canceladas']);
    const data = safeJsonParse(canvas.dataset.values, [198, 18, 14]);

    new window.Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [config.colors.primary, config.colors.warning, config.colors.danger],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            cutout: '70%'
        }
    });
}

/**
 * Inicializador principal
 */
export function initReportes(customConfig = {}) {
    const config = {
        ...DEFAULT_CONFIG,
        ...customConfig,
        selectors: {
            ...DEFAULT_CONFIG.selectors,
            ...customConfig.selectors
        },
        colors: {
            ...DEFAULT_CONFIG.colors,
            ...customConfig.colors
        }
    };

    setupChartIngresosDia(config);
    setupChartIngresosServicio(config);
    setupChartCitasEstado(config);
}

// Auto-inicialización
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector(DEFAULT_CONFIG.selectors.chartIngresosDia)) {
                initReportes();
            }
        });
    } else {
        if (document.querySelector(DEFAULT_CONFIG.selectors.chartIngresosDia)) {
            initReportes();
        }
    }
}
