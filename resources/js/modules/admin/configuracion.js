/**
 * ============================================================================
 * Módulo de Configuración del Negocio (LashApp - Admin)
 * ============================================================================
 *
 * Segmentación de responsabilidades:
 * 1. Previsualización de logo cargado en el formulario.
 * 2. Modales parametrizados de configuración (Impuestos, Políticas).
 * 3. Acciones de mantenimiento (Backup, Restablecer parámetros, Eliminación).
 */

import { confirmAction, notify } from '../../common/alerts.js';

const DEFAULT_CONFIG = {
    selectors: {
        logoInput: '#logoFileInput',
        logoPreview: '#logoImgPreview',
        logoDefaultIcon: '#logoIconDefault',
        logoDefaultText: '#logoTextDefault',
        btnImpuestos: '#btnModalImpuestos',
        btnPoliticas: '#btnModalPoliticas',
        btnBackup: '#btnRealizarBackup',
        btnRestablecer: '#btnConfirmarRestablecer',
        btnEliminarCuenta: '#btnConfirmarEliminarCuenta',
    },
    colors: {
        primary: '#7c1db8',
        danger: '#dc2626',
        cancel: '#b59ec4',
    }
};

/**
 * 1. Segmento: Previsualización de logo
 */
function setupLogoPreview(config) {
    const fileInput = document.querySelector(config.selectors.logoInput) || document.querySelector('input[name="logo"]');
    const imgPreview = document.querySelector(config.selectors.logoPreview);
    const icon = document.querySelector(config.selectors.logoDefaultIcon);
    const text = document.querySelector(config.selectors.logoDefaultText);

    if (fileInput && imgPreview) {
        fileInput.addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (event) {
                    imgPreview.src = event.target.result;
                    imgPreview.style.display = 'block';
                    if (icon) icon.style.display = 'none';
                    if (text) text.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Exponer globalmente para retrocompatibilidad con onchange en HTML
    window.previewLogo = function (event) {
        const file = event.target.files[0];
        if (file && imgPreview) {
            const reader = new FileReader();
            reader.onload = function (e) {
                imgPreview.src = e.target.result;
                imgPreview.style.display = 'block';
                if (icon) icon.style.display = 'none';
                if (text) text.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    };
}

/**
 * 2. Segmento: Modales de configuración interactiva
 */
function setupConfigModals(config) {
    window.mostrarModalImpuestos = function () {
        if (typeof window.Swal === 'undefined') return;

        window.Swal.fire({
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
            confirmButtonColor: config.colors.primary,
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#e5d8ee'
        }).then((result) => {
            if (result.isConfirmed) {
                notify({
                    icon: 'success',
                    title: '¡Tarifas guardadas!',
                    text: 'Los cambios impositivos fueron aplicados correctamente.'
                });
            }
        });
    };

    window.mostrarModalPoliticas = function () {
        if (typeof window.Swal === 'undefined') return;

        window.Swal.fire({
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
            confirmButtonColor: config.colors.primary,
            showCancelButton: true,
            cancelButtonText: 'Cerrar',
            cancelButtonColor: '#e5d8ee'
        }).then((result) => {
            if (result.isConfirmed) {
                notify({
                    icon: 'success',
                    title: '¡Políticas actualizadas!',
                    text: 'Tus políticas y términos han sido actualizados con éxito.'
                });
            }
        });
    };

    window.realizarBackup = function () {
        if (typeof window.Swal === 'undefined') return;

        window.Swal.fire({
            title: 'Generando Copia de Seguridad...',
            text: 'Comprimiendo base de datos y archivos multimedia.',
            allowOutsideClick: false,
            didOpen: () => {
                window.Swal.showLoading();
                setTimeout(() => {
                    window.Swal.fire({
                        icon: 'success',
                        title: '¡Copia de Seguridad Lista!',
                        text: 'Se ha creado el respaldo exitosamente: backup_ohlalash_' + new Date().toISOString().slice(0, 10) + '.sql.gz',
                        confirmButtonColor: config.colors.primary
                    });
                }, 1500);
            }
        });
    };

    window.confirmarRestablecer = async function () {
        const confirmed = await confirmAction({
            title: '¿Restablecer configuración?',
            text: 'Se restablecerán todos los parámetros a sus valores predeterminados de fábrica.',
            icon: 'warning',
            confirmButtonColor: config.colors.danger,
            cancelButtonColor: config.colors.cancel,
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar'
        });

        if (confirmed) {
            notify({
                icon: 'success',
                title: '¡Configuración restablecida!',
                text: 'Los valores por defecto han sido restaurados.'
            });
        }
    };

    window.confirmarEliminarCuenta = function () {
        if (typeof window.Swal === 'undefined') return;

        window.Swal.fire({
            title: '¿Eliminar cuenta del negocio?',
            text: 'Esta acción es irreversible y eliminará todos los registros asociados.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: config.colors.danger,
            cancelButtonColor: config.colors.cancel,
            confirmButtonText: 'Eliminar definitivamente',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.Swal.fire({
                    title: 'Escribe "ELIMINAR" para confirmar',
                    input: 'text',
                    showCancelButton: true,
                    confirmButtonColor: config.colors.danger,
                    cancelButtonColor: config.colors.cancel,
                    confirmButtonText: 'Confirmar eliminación',
                    cancelButtonText: 'Cancelar',
                    preConfirm: (valor) => {
                        if (valor !== 'ELIMINAR') {
                            window.Swal.showValidationMessage('La palabra no coincide con "ELIMINAR"');
                        }
                    }
                });
            }
        });
    };
}

/**
 * Inicializador principal
 */
export function initConfiguracion(customConfig = {}) {
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

    setupLogoPreview(config);
    setupConfigModals(config);
}

// Auto-inicialización
if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('logoImgPreview') || document.querySelector('.config-form')) {
                initConfiguracion();
            }
        });
    } else {
        if (document.getElementById('logoImgPreview') || document.querySelector('.config-form')) {
            initConfiguracion();
        }
    }
}
