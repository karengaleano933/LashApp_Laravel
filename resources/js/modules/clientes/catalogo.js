/**
 * Módulo: Catálogo de Servicios (Cliente)
 * LashApp - Oh La Lash Studio
 *
 * Funcionalidad:
 * - Filtrado reactivo en tiempo real por categoría (Tabs)
 * - Filtrado combinado por texto (búsqueda en vivo), categoría y rango de precios
 * - Ordenación dinámica en el DOM (precio, popularidad, nombre)
 * - Alternancia de vista Grid y Lista
 * - Gestión visual e interacción de favoritos con toasts SweetAlert2
 * - Limpieza y reseteo integral de filtros
 */

let categoriaActiva = 'todas';

export function initCatalogo() {
    const inputBuscar = document.getElementById('buscarServicioInput');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', filtrarServicios);
    }

    const selectPrecio = document.getElementById('precioSelect');
    if (selectPrecio) {
        selectPrecio.addEventListener('change', filtrarPorPrecio);
    }

    const selectOrdenar = document.getElementById('ordenarSelect');
    if (selectOrdenar) {
        selectOrdenar.addEventListener('change', ordenarServicios);
    }
}

export function filtrarPorCategoria(categoria, boton) {
    categoriaActiva = categoria;

    document.querySelectorAll('.cat-tab-item').forEach(btn => btn.classList.remove('active'));
    if (boton) {
        boton.classList.add('active');
    }

    filtrarServicios();
}

export function filtrarServicios() {
    const inputBuscar = document.getElementById('buscarServicioInput');
    const query = inputBuscar ? inputBuscar.value.toLowerCase().trim() : '';

    const selectPrecio = document.getElementById('precioSelect');
    const rangoPrecio = selectPrecio ? selectPrecio.value : 'todos';

    const cards = document.querySelectorAll('#catCardsGrid .cat-card-item');
    let visibles = 0;

    cards.forEach(card => {
        const nombre = (card.getAttribute('data-nombre') || '').toLowerCase();
        const categoria = (card.getAttribute('data-categoria') || '').toLowerCase();
        const precio = parseFloat(card.getAttribute('data-precio') || '0');

        // 1. Coincidencia de texto
        const coincideTexto = !query || nombre.includes(query) || categoria.includes(query);

        // 2. Coincidencia de categoría
        const coincideCategoria = (categoriaActiva === 'todas') || categoria.includes(categoriaActiva);

        // 3. Coincidencia de precio
        let coincidePrecio = true;
        if (rangoPrecio === '0-100') {
            coincidePrecio = precio <= 100000;
        } else if (rangoPrecio === '100-200') {
            coincidePrecio = precio > 100000 && precio <= 200000;
        } else if (rangoPrecio === '200+') {
            coincidePrecio = precio > 200000;
        }

        if (coincideTexto && coincideCategoria && coincidePrecio) {
            card.style.display = '';
            visibles++;
        } else {
            card.style.display = 'none';
        }
    });

    const counter = document.getElementById('totalServiciosCount');
    if (counter) counter.textContent = visibles;

    const noResults = document.getElementById('noResultsMsg');
    if (noResults) {
        noResults.style.display = (visibles === 0) ? 'block' : 'none';
    }
}

export function filtrarPorPrecio() {
    filtrarServicios();
}

export function ordenarServicios() {
    const select = document.getElementById('ordenarSelect');
    if (!select) return;

    const criterio = select.value;
    const grid = document.getElementById('catCardsGrid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.cat-card-item'));

    cards.sort((a, b) => {
        const precioA = parseFloat(a.getAttribute('data-precio') || '0');
        const precioB = parseFloat(b.getAttribute('data-precio') || '0');
        const popA = parseFloat(a.getAttribute('data-popularidad') || '0');
        const popB = parseFloat(b.getAttribute('data-popularidad') || '0');
        const nomA = (a.getAttribute('data-nombre') || '').toLowerCase();
        const nomB = (b.getAttribute('data-nombre') || '').toLowerCase();

        if (criterio === 'menor_precio') {
            return precioA - precioB;
        } else if (criterio === 'mayor_precio') {
            return precioB - precioA;
        } else if (criterio === 'nombre_asc') {
            return nomA.localeCompare(nomB);
        } else {
            return popB - popA;
        }
    });

    const noResults = document.getElementById('noResultsMsg');
    cards.forEach(card => grid.appendChild(card));
    if (noResults) grid.appendChild(noResults);
}

export function cambiarVista(tipo) {
    const grid = document.getElementById('catCardsGrid');
    const btnGrid = document.getElementById('btnViewGrid');
    const btnList = document.getElementById('btnViewList');

    if (!grid) return;

    if (tipo === 'list') {
        grid.classList.add('list-view');
        if (btnList) btnList.classList.add('active');
        if (btnGrid) btnGrid.classList.remove('active');
    } else {
        grid.classList.remove('list-view');
        if (btnGrid) btnGrid.classList.add('active');
        if (btnList) btnList.classList.remove('active');
    }
}

export function toggleFavorito(button, nombreServicio) {
    if (!button) return;
    button.classList.toggle('active');
    const icon = button.querySelector('i');
    const esFavorito = button.classList.contains('active');

    if (esFavorito) {
        if (icon) {
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid');
        }
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: `"${nombreServicio}" añadido a favoritos`,
                showConfirmButton: false,
                timer: 2000
            });
        }
    } else {
        if (icon) {
            icon.classList.remove('fa-solid');
            icon.classList.add('fa-regular');
        }
    }
}

export function limpiarFiltros() {
    const inputBuscar = document.getElementById('buscarServicioInput');
    if (inputBuscar) inputBuscar.value = '';

    const selectPrecio = document.getElementById('precioSelect');
    if (selectPrecio) selectPrecio.value = 'todos';

    const selectOrdenar = document.getElementById('ordenarSelect');
    if (selectOrdenar) selectOrdenar.value = 'populares';

    const tabTodas = document.querySelector('.cat-tab-item[data-categoria="todas"]');
    filtrarPorCategoria('todas', tabTodas);
}

// Retrocompatibilidad global
window.filtrarPorCategoria = filtrarPorCategoria;
window.filtrarServicios = filtrarServicios;
window.filtrarPorPrecio = filtrarPorPrecio;
window.ordenarServicios = ordenarServicios;
window.cambiarVista = cambiarVista;
window.toggleFavorito = toggleFavorito;
window.limpiarFiltros = limpiarFiltros;

document.addEventListener('DOMContentLoaded', initCatalogo);
