/**
 * ============================================================================
 * Módulo de Layout Trabajador (LashApp)
 * ============================================================================
 */

import { initSidebarAdmin } from './sidebaradmin.js';

export const initSidebarTrabajador = initSidebarAdmin;

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.querySelector('.top-header') || document.querySelector('#sidebar')) {
                initSidebarTrabajador();
            }
        });
    } else {
        if (document.querySelector('.top-header') || document.querySelector('#sidebar')) {
            initSidebarTrabajador();
        }
    }
}
