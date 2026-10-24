import Alpine from 'alpinejs';
import { getCsrfToken } from './common/csrf.js';
import { confirmAction, showToast, notify } from './common/alerts.js';
import { http, HttpClient } from './common/http.js';
import { initServicios } from './modules/admin/servicios.js';
import { initSidebarAdmin } from './modules/layout/sidebaradmin.js';
import { initCitas } from './modules/admin/citas.js';

// Alpine.js
window.Alpine = Alpine;
Alpine.start();

// Espacio de nombres global parametrizado LashApp
window.LashApp = {
    csrf: {
        getCsrfToken,
    },
    alerts: {
        confirmAction,
        showToast,
        notify,
    },
    http,
    HttpClient,
    modules: {
        initServicios,
        initSidebarAdmin,
        initCitas,
    }
};

export {
    getCsrfToken,
    confirmAction,
    showToast,
    notify,
    http,
    HttpClient,
    initServicios,
    initSidebarAdmin,
    initCitas,
};
