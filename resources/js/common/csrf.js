/**
 * Helper parametrizado para la obtención del token CSRF de Laravel.
 * 
 * Permite obtener el token desde la etiqueta <meta name="csrf-token">
 * o desde un selector configurable, garantizando que el código JavaScript
 * no dependa de interpolaciones de Blade en línea.
 *
 * @param {string} selector - Selector CSS para encontrar la meta etiqueta o input (default: 'meta[name="csrf-token"]')
 * @returns {string} El token CSRF o cadena vacía si no existe.
 */
export function getCsrfToken(selector = 'meta[name="csrf-token"]') {
    const metaTag = document.querySelector(selector);
    if (metaTag && metaTag.getAttribute('content')) {
        return metaTag.getAttribute('content');
    }

    const inputTag = document.querySelector('input[name="_token"]');
    if (inputTag && inputTag.value) {
        return inputTag.value;
    }

    console.warn(`[CSRF] No se encontró el token CSRF con el selector: "${selector}"`);
    return '';
}
