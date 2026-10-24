/**
 * Cliente HTTP parametrizado basado en Fetch API para Laravel.
 * 
 * Gestiona de forma centralizada:
 * - Inyección automática del Token CSRF en peticiones POST, PUT, PATCH, DELETE.
 * - Cabeceras estándar (Accept: application/json).
 * - Manejo uniforme de errores de red y respuestas HTTP de error.
 */

import { getCsrfToken } from './csrf.js';

class HttpClient {
    constructor(baseConfig = {}) {
        this.baseConfig = {
            baseUrl: '',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            ...baseConfig
        };
    }

    /**
     * Prepara las cabeceras requeridas para la solicitud.
     */
    _buildHeaders(customHeaders = {}, isJson = true) {
        const headers = {
            ...this.baseConfig.headers,
            ...customHeaders
        };

        const csrfToken = getCsrfToken();
        if (csrfToken) {
            headers['X-CSRF-TOKEN'] = csrfToken;
        }

        if (isJson && !headers['Content-Type']) {
            headers['Content-Type'] = 'application/json';
        }

        return headers;
    }

    /**
     * Construye la URL completa a partir de la ruta y baseUrl.
     */
    _buildUrl(url) {
        if (url.startsWith('http://') || url.startsWith('https://')) {
            return url;
        }
        const base = this.baseConfig.baseUrl.replace(/\/+$/, '');
        const path = url.replace(/^\/+/, '');
        return base ? `${base}/${path}` : `/${path}`;
    }

    /**
     * Ejecuta una petición HTTP genérica.
     */
    async request(url, options = {}) {
        const fullUrl = this._buildUrl(url);
        const method = (options.method || 'GET').toUpperCase();
        const isJson = options.isJson ?? !(options.body instanceof FormData);
        const headers = this._buildHeaders(options.headers, isJson);

        const fetchOptions = {
            method,
            headers,
            ...options
        };

        if (isJson && fetchOptions.body && typeof fetchOptions.body === 'object' && !(fetchOptions.body instanceof FormData)) {
            fetchOptions.body = JSON.stringify(fetchOptions.body);
        }

        try {
            const response = await fetch(fullUrl, fetchOptions);

            const contentType = response.headers.get('content-type') || '';
            const isResponseJson = contentType.includes('application/json');
            const data = isResponseJson ? await response.json() : await response.text();

            if (!response.ok) {
                const error = new Error(data?.message || `Error HTTP ${response.status}: ${response.statusText}`);
                error.status = response.status;
                error.data = data;
                throw error;
            }

            return data;
        } catch (error) {
            console.error(`[HttpClient] Falló la petición a "${fullUrl}":`, error);
            throw error;
        }
    }

    get(url, options = {}) {
        return this.request(url, { ...options, method: 'GET' });
    }

    post(url, body, options = {}) {
        return this.request(url, { ...options, method: 'POST', body });
    }

    put(url, body, options = {}) {
        return this.request(url, { ...options, method: 'PUT', body });
    }

    delete(url, options = {}) {
        return this.request(url, { ...options, method: 'DELETE' });
    }
}

export const http = new HttpClient();
export { HttpClient };
