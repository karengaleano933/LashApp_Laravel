/**
 * ============================================================================
 * Módulo de Autenticación - Registro de Usuarios (LashApp)
 * ============================================================================
 */

export function initRegister(customConfig = {}) {
    const config = {
        panelSelector: '#leftPanel',
        toggleSelector: '#togglePass',
        passwordSelector: '#password',
        sparklesCount: 55,
        ...customConfig
    };

    // 1. Destellos dorados decorativos
    const panel = document.querySelector(config.panelSelector);
    if (panel) {
        for (let i = 0; i < config.sparklesCount; i++) {
            const sparkle = document.createElement('div');
            sparkle.classList.add('sparkle');
            const size = Math.random() * 5 + 2;
            sparkle.style.width = size + 'px';
            sparkle.style.height = size + 'px';
            sparkle.style.left = Math.random() * 100 + '%';
            sparkle.style.top = Math.random() * 100 + '%';
            sparkle.style.animationDelay = Math.random() * 3 + 's';
            sparkle.style.animationDuration = Math.random() * 2 + 2 + 's';
            panel.appendChild(sparkle);
        }
    }

    // 2. Toggle visibilidad de contraseña
    const toggle = document.querySelector(config.toggleSelector);
    const password = document.querySelector(config.passwordSelector);
    if (toggle && password) {
        toggle.addEventListener('click', function () {
            if (password.type === 'password') {
                password.type = 'text';
                toggle.classList.remove('fa-eye');
                toggle.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                toggle.classList.remove('fa-eye-slash');
                toggle.classList.add('fa-eye');
            }
        });
    }
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            if (document.getElementById('togglePass') || document.querySelector('.register-card, .login-page-body')) {
                initRegister();
            }
        });
    } else {
        if (document.getElementById('togglePass') || document.querySelector('.register-card, .login-page-body')) {
            initRegister();
        }
    }
}
