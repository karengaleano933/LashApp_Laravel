/**
 * Módulo: Perfil de Cliente
 * LashApp - Oh La Lash Studio
 *
 * Funcionalidad:
 * - Alternancia y scroll suave hacia formulario de cambio de contraseña
 * - Confirmación segura de desactivación / eliminación de cuenta mediante SweetAlert2
 * - Navegación reactiva por pestañas (Información, Preferencias, Seguridad, Acciones) con scroll suave
 */

export function initPerfilCliente() {
    const tabs = document.querySelectorAll('.perfil-tab-btn');
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            tabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const tabTarget = this.getAttribute('data-tab');
            if (tabTarget === 'info') {
                document.getElementById('sec-info')?.scrollIntoView({ behavior: 'smooth' });
            } else if (tabTarget === 'preferencias') {
                document.getElementById('sec-preferencias')?.scrollIntoView({ behavior: 'smooth' });
            } else if (tabTarget === 'seguridad') {
                document.getElementById('sec-seguridad')?.scrollIntoView({ behavior: 'smooth' });
                togglePasswordForm();
            } else if (tabTarget === 'acciones') {
                document.getElementById('sec-acciones')?.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
}

export function togglePasswordForm() {
    const wrap = document.getElementById('formPasswordWrap');
    if (wrap) {
        wrap.style.display = (wrap.style.display === 'none' || wrap.style.display === '') ? 'block' : 'none';
        if (wrap.style.display === 'block') {
            wrap.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}

export function confirmarEliminarCuenta() {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Desactivar tu cuenta?',
            text: 'Tu usuario quedará inactivo y tu sesión se cerrará inmediatamente. ¿Deseas continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, desactivar cuenta',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('formEliminarCuenta')?.submit();
            }
        });
    } else {
        if (confirm('¿Estás seguro de que deseas desactivar tu cuenta?')) {
            document.getElementById('formEliminarCuenta')?.submit();
        }
    }
}

// Retrocompatibilidad global
window.togglePasswordForm = togglePasswordForm;
window.confirmarEliminarCuenta = confirmarEliminarCuenta;

document.addEventListener('DOMContentLoaded', initPerfilCliente);
