/**
 * ============================================================================
 * Módulo de Layout Cliente (LashApp)
 * ============================================================================
 */

import { initSidebarAdmin } from './sidebaradmin.js';

export const initSidebarCliente = initSidebarAdmin;

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector('.top-header') || document.querySelector('#sidebar')) {
                initSidebarCliente();
            }
        });
    } else {
        if (document.querySelector('.top-header') || document.querySelector('#sidebar')) {
            initSidebarCliente();
        }
    }
}
