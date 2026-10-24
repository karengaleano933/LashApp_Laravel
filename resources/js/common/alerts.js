/**
 * Módulo parametrizado para la gestión de diálogos y alertas con SweetAlert2.
 * 
 * Centraliza configuraciones de diseño, colores institucionales y opciones
 * por defecto de la aplicación (LashApp), permitiendo sobreescribir cualquier
 * parámetro mediante un objeto de opciones.
 */

const DEFAULT_ALERT_COLORS = {
    confirm: '#7c1db8',
    cancel: '#6c757d',
    danger: '#dc3545',
    success: '#059669',
};

/**
 * Diálogo de confirmación interactivo parametrizado.
 *
 * @param {Object} options
 * @param {string} [options.title='¿Estás seguro?']
 * @param {string} [options.text='']
 * @param {string} [options.icon='warning']
 * @param {string} [options.confirmButtonText='Sí, continuar']
 * @param {string} [options.cancelButtonText='Cancelar']
 * @param {string} [options.confirmButtonColor]
 * @param {string} [options.cancelButtonColor]
 * @param {boolean} [options.isDestructive=false]
 * @returns {Promise<boolean>} Resuelve true si el usuario confirmó, false si canceló.
 */
export async function confirmAction(options = {}) {
    const isDestructive = options.isDestructive ?? false;
    const confirmColor = options.confirmButtonColor || (isDestructive ? DEFAULT_ALERT_COLORS.danger : DEFAULT_ALERT_COLORS.confirm);
    const cancelColor = options.cancelButtonColor || DEFAULT_ALERT_COLORS.cancel;

    if (typeof window.Swal !== 'undefined') {
        const result = await window.Swal.fire({
            title: options.title || '¿Estás seguro?',
            text: options.text || '',
            icon: options.icon || (isDestructive ? 'warning' : 'question'),
            showCancelButton: true,
            confirmButtonColor: confirmColor,
            cancelButtonColor: cancelColor,
            confirmButtonText: options.confirmButtonText || (isDestructive ? 'Sí, eliminar' : 'Sí, continuar'),
            cancelButtonText: options.cancelButtonText || 'Cancelar',
            reverseButtons: options.reverseButtons ?? true,
            focusCancel: options.focusCancel ?? isDestructive,
            ...options.customSwalOptions
        });
        return result.isConfirmed;
    }

    // Fallback nativo si SweetAlert no está disponible
    return window.confirm(`${options.title || 'Confirmación'}\n${options.text || ''}`);
}

/**
 * Muestra una notificación tipo Toast emergente en una esquina.
 *
 * @param {Object} options
 * @param {string} options.title
 * @param {'success'|'error'|'warning'|'info'} [options.icon='success']
 * @param {number} [options.timer=2500]
 * @param {string} [options.position='top-end']
 */
export function showToast(options = {}) {
    const title = typeof options === 'string' ? options : options.title;
    const icon = options.icon || 'success';
    const timer = options.timer || 2500;
    const position = options.position || 'top-end';

    if (typeof window.Swal !== 'undefined') {
        window.Swal.fire({
            toast: true,
            position: position,
            icon: icon,
            title: title,
            showConfirmButton: false,
            timer: timer,
            timerProgressBar: true
        });
    } else {
        console.log(`[Toast ${icon}]: ${title}`);
    }
}

/**
 * Muestra un mensaje simple de éxito o información.
 */
export function notify(options = {}) {
    const title = options.title || 'Operación completada';
    const text = options.text || '';
    const icon = options.icon || 'success';

    if (typeof window.Swal !== 'undefined') {
        return window.Swal.fire({
            title,
            text,
            icon,
            confirmButtonColor: DEFAULT_ALERT_COLORS.confirm
        });
    } else {
        alert(`${title}: ${text}`);
    }
}
