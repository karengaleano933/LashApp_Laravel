<!-- ══════════════════════════════════════════════════════════
     MODAL FLOTANTE DE TICKET POS EN LA MISMA PANTALLA
══════════════════════════════════════════════════════════ -->
<div id="modalTicketPosGlobal" class="modal-ticket-pos-overlay" style="display: none;">
    <div class="modal-ticket-pos-box">
        <div class="modal-ticket-pos-header">
            <div class="modal-ticket-pos-title">
                <i class="fa-solid fa-receipt" style="color: #7c1db8; font-size: 16px;"></i>
                <span>Comprobante / Factura POS</span>
            </div>
            <div class="modal-ticket-pos-actions">
                <button type="button" class="btn-ticket-modal-download" onclick="descargarTicketDesdeModal()" title="Descargar ticket como imagen PNG en tu dispositivo">
                    <i class="fa-solid fa-download"></i> Descargar (PNG)
                </button>
                <button type="button" class="btn-ticket-modal-pdf" onclick="descargarTicketPDFDesdeModal()" title="Descargar ticket en formato PDF">
                    <i class="fa-solid fa-file-pdf"></i> Descargar PDF
                </button>
                <button type="button" class="btn-ticket-modal-close" onclick="cerrarModalTicketPOS()" title="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
        <div class="modal-ticket-pos-body">
            <div id="ticketModalLoader" style="display: flex; align-items: center; justify-content: center; height: 350px; color: #7c1db8; font-size: 14px; font-weight: 600; gap: 10px;">
                <i class="fa-solid fa-spinner fa-spin" style="font-size: 22px;"></i> Cargando ticket POS...
            </div>
            <iframe id="iframeTicketPosGlobal" src="" style="width: 100%; height: 560px; border: none; display: none;" onload="onTicketIframeLoad(this)"></iframe>
        </div>
    </div>
</div>

<style>
    .modal-ticket-pos-overlay {
        position: fixed;
        inset: 0;
        background: rgba(22, 10, 30, 0.75);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        z-index: 9999999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }

    .modal-ticket-pos-box {
        background: #ffffff;
        border-radius: 20px;
        width: 100%;
        max-width: 440px;
        max-height: 92vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4);
        border: 1px solid #ebdcf5;
        overflow: hidden;
        animation: animModalTicketIn 0.22s ease-out;
    }

    @keyframes animModalTicketIn {
        from { opacity: 0; transform: scale(0.94) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-ticket-pos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 18px;
        background: #ffffff;
        border-bottom: 1px solid #f3e8fa;
    }

    .modal-ticket-pos-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        font-size: 14px;
        color: #2a0d3e;
    }

    .modal-ticket-pos-actions {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-ticket-modal-download {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        border: none;
        padding: 7px 14px;
        border-radius: 9px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 3px 10px rgba(16, 185, 129, 0.3);
        transition: all 0.2s;
    }

    .btn-ticket-modal-download:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.4);
    }

    .btn-ticket-modal-pdf {
        background: #7c1db8;
        color: #ffffff;
        border: none;
        padding: 7px 13px;
        border-radius: 9px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 3px 10px rgba(124, 29, 184, 0.3);
        transition: all 0.2s;
    }

    .btn-ticket-modal-pdf:hover {
        background: #9324d9;
        transform: translateY(-1px);
    }

    .btn-ticket-modal-close {
        background: #f6effb;
        color: #7c1db8;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 15px;
        transition: all 0.2s;
    }

    .btn-ticket-modal-close:hover {
        background: #ebdcf5;
        transform: rotate(90deg);
    }

    .modal-ticket-pos-body {
        flex: 1;
        overflow-y: auto;
        background: #faf5fc;
    }
</style>

<script>
    function abrirModalTicketPOS(url) {
        const modal = document.getElementById('modalTicketPosGlobal');
        const iframe = document.getElementById('iframeTicketPosGlobal');
        const loader = document.getElementById('ticketModalLoader');
        if (modal && iframe) {
            if (loader) loader.style.display = 'flex';
            iframe.style.display = 'none';
            const separator = url.includes('?') ? '&' : '?';
            iframe.src = url.includes('embed=1') ? url : (url + separator + 'embed=1');
            modal.style.display = 'flex';
        }
    }

    function cerrarModalTicketPOS() {
        const modal = document.getElementById('modalTicketPosGlobal');
        const iframe = document.getElementById('iframeTicketPosGlobal');
        if (modal) modal.style.display = 'none';
        if (iframe) iframe.src = 'about:blank';
    }

    function onTicketIframeLoad(iframe) {
        const loader = document.getElementById('ticketModalLoader');
        if (iframe && iframe.src && iframe.src !== 'about:blank') {
            if (loader) loader.style.display = 'none';
            iframe.style.display = 'block';
        }
    }

    function descargarTicketDesdeModal() {
        const iframe = document.getElementById('iframeTicketPosGlobal');
        if (iframe && iframe.contentWindow && typeof iframe.contentWindow.descargarTicketPOS === 'function') {
            iframe.contentWindow.descargarTicketPOS();
        }
    }

    function descargarTicketPDFDesdeModal() {
        const iframe = document.getElementById('iframeTicketPosGlobal');
        if (iframe && iframe.contentWindow && typeof iframe.contentWindow.descargarTicketPDF === 'function') {
            iframe.contentWindow.descargarTicketPDF();
        } else if (iframe && iframe.contentWindow && typeof iframe.contentWindow.descargarTicketPOS === 'function') {
            iframe.contentWindow.descargarTicketPOS();
        }
    }

    function imprimirTicketIframe() {
        // En lugar de imprimir abre el flujo de descarga directa
        descargarTicketDesdeModal();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('modalTicketPosGlobal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) cerrarModalTicketPOS();
            });
        }
    });

    window.abrirModalTicketPOS = abrirModalTicketPOS;
    window.cerrarModalTicketPOS = cerrarModalTicketPOS;
    window.imprimirTicketIframe = imprimirTicketIframe;
    window.descargarTicketDesdeModal = descargarTicketDesdeModal;
    window.descargarTicketPDFDesdeModal = descargarTicketPDFDesdeModal;
</script>
