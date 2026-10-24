/**
 * Módulo: Reseñas de Cliente
 * LashApp - Oh La Lash Studio
 *
 * Funcionalidad:
 * - Modal para redactar y publicar reseña
 * - Selector interactivo de calificación con estrellas (hover, click, feedback visual de texto)
 * - Cierre de modal con overlay
 */

const ratingTexts = {
    1: 'Muy insatisfecha (1 de 5)',
    2: 'Podría mejorar (2 de 5)',
    3: 'Buena experiencia (3 de 5)',
    4: 'Muy buena atención (4 de 5)',
    5: '¡Excelente servicio! (5 de 5)'
};

let currentScore = 5;

export function initResenasCliente() {
    const stars = document.querySelectorAll('#starRatingSelector i');
    const inputVal = document.getElementById('inputCalificacionEstrellas');
    const feedbackText = document.getElementById('ratingTextFeedback');

    function updateStars(score) {
        stars.forEach(star => {
            const val = parseInt(star.getAttribute('data-val'), 10);
            if (val <= score) {
                star.className = 'fa-solid fa-star active';
            } else {
                star.className = 'fa-regular fa-star';
            }
        });
        if (feedbackText) {
            feedbackText.textContent = ratingTexts[score] || (score + ' de 5');
        }
    }

    stars.forEach(star => {
        star.addEventListener('mouseenter', function() {
            const val = parseInt(this.getAttribute('data-val'), 10);
            stars.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-val'), 10);
                if (sVal <= val) {
                    s.className = 'fa-solid fa-star hovered';
                } else {
                    s.className = 'fa-regular fa-star';
                }
            });
            if (feedbackText) {
                feedbackText.textContent = ratingTexts[val] || '';
            }
        });

        star.addEventListener('mouseleave', () => {
            updateStars(currentScore);
        });

        star.addEventListener('click', function() {
            currentScore = parseInt(this.getAttribute('data-val'), 10);
            if (inputVal) inputVal.value = currentScore;
            updateStars(currentScore);
        });
    });

    const overlay = document.getElementById('modalResenaOverlay');
    if (overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalCalificar();
            }
        });
    }
}

export function abrirModalCalificar(citaId = null, servicioNombre = '', especialistaNombre = '') {
    const modal = document.getElementById('modalResenaOverlay');
    const select = document.getElementById('modalSelectCita');
    if (citaId && select) {
        select.value = citaId;
    }
    if (modal) {
        modal.style.display = 'flex';
    }
}

export function cerrarModalCalificar() {
    const modal = document.getElementById('modalResenaOverlay');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Retrocompatibilidad global
window.abrirModalCalificar = abrirModalCalificar;
window.cerrarModalCalificar = cerrarModalCalificar;

document.addEventListener('DOMContentLoaded', initResenasCliente);
