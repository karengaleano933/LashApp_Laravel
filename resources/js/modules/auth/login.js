/**
 * ============================================================================
 * Módulo de Autenticación - Inicio de Sesión (LashApp)
 * ============================================================================
 */

export function initLogin(customConfig = {}) {
    const config = {
        passwordSelector: '#password',
        toggleSelector: '.ojo',
        ...customConfig
    };

    const password = document.querySelector(config.passwordSelector);
    const ojo = document.querySelector(config.toggleSelector);

    window.mostrarPassword = function () {
        if (!password || !ojo) return;
        if (password.type === 'password') {
            password.type = 'text';
            ojo.classList.remove('fa-eye');
            ojo.classList.add('fa-eye-slash');
        } else {
            password.type = 'password';
            ojo.classList.remove('fa-eye-slash');
            ojo.classList.add('fa-eye');
        }
    };

    if (ojo) {
        ojo.addEventListener('click', window.mostrarPassword);
    }
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('password') && document.querySelector('.login-card, .login-container, .ojo')) {
                initLogin();
            }
        });
    } else {
        if (document.getElementById('password') && document.querySelector('.login-card, .login-container, .ojo')) {
            initLogin();
        }
    }
}
