/**
 * ============================================================================
 * Módulo de Perfil del Trabajador (LashApp - Trabajador)
 * ============================================================================
 * 
 * Segmentación y parametrización:
 * 1. Navegación por pestañas (Datos personales, Cambiar contraseña, Próximas citas).
 * 2. Carga y previsualización/envío de la fotografía de perfil.
 * 3. Procesamiento de mensajes de sesión (éxito y advertencia/error).
 */

const DEFAULT_CONFIG = {
    containerSelector: '#perfilTrabajadorContainer',
    selectors: {
        tabs: '.perfil-tab-btn',
        panels: '.perfil-tab-panel',
        inputFoto: '#inputFotoTrabajador',
        formFoto: '#formFotoTrabajador',
        btnCambiarFoto: '.btn-cambiar-foto',
    },
    classes: {
        active: 'active',
    },
    colors: {
        primary: '#7c1db8',
    }
};

let currentConfig = { ...DEFAULT_CONFIG };

/**
 * Procesa notificaciones de sesión pasadas vía data-attributes en el contenedor
 */
function handleSessionAlerts(container) {
    if (!container || typeof Swal === 'undefined') return;

    const successMsg = container.dataset.sessionSuccess;
    const errorMsg = container.dataset.sessionError;

    if (successMsg) {
        Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: successMsg,
            confirmButtonColor: currentConfig.colors.primary,
            confirmButtonText: 'Aceptar',
        });
    } else if (errorMsg) {
        Swal.fire({
            icon: 'warning',
            title: 'Aviso',
            text: errorMsg,
            confirmButtonColor: currentConfig.colors.primary,
            confirmButtonText: 'Entendido',
        });
    }
}

/**
 * Inicializa la navegación por pestañas del perfil
 */
function initTabs(config) {
    const tabs = document.querySelectorAll(config.selectors.tabs);
    const panels = document.querySelectorAll(config.selectors.panels);

    tabs.forEach((tab) => {
        tab.addEventListener('click', function () {
            tabs.forEach((t) => t.classList.remove(config.classes.active));
            panels.forEach((p) => p.classList.remove(config.classes.active));

            this.classList.add(config.classes.active);
            const targetId = this.getAttribute('data-tab');
            const targetPanel = document.getElementById(targetId);
            if (targetPanel) {
                targetPanel.classList.add(config.classes.active);
            }
        });
    });
}

/**
 * Configura la subida rápida de foto de perfil
 */
function initFotoUpload(config) {
    const inputFoto = document.querySelector(config.selectors.inputFoto);
    const formFoto = document.querySelector(config.selectors.formFoto);
    const btnCambiar = document.querySelector(config.selectors.btnCambiarFoto);

    if (btnCambiar && inputFoto) {
        btnCambiar.addEventListener('click', () => {
            inputFoto.click();
        });
    }

    if (inputFoto && formFoto) {
        inputFoto.addEventListener('change', () => {
            if (inputFoto.files && inputFoto.files.length > 0) {
                formFoto.submit();
            }
        });
    }
}

/**
 * Inicializador principal del módulo
 */
export function initPerfilTrabajador(customConfig = {}) {
    const container = document.querySelector(customConfig.containerSelector || DEFAULT_CONFIG.containerSelector)
                   || document.querySelector('.perfil-page-container');
    if (!container) return;

    currentConfig = {
        ...DEFAULT_CONFIG,
        ...customConfig,
    };

    handleSessionAlerts(container);
    initTabs(currentConfig);
    initFotoUpload(currentConfig);
}

// Retrocompatibilidad global
window.initPerfilTrabajador = initPerfilTrabajador;

// Inicialización automática al cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    initPerfilTrabajador();
});
