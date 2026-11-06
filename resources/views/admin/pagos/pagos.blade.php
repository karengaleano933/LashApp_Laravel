<!-- Incluir Header superior de Admin -->
@include('layouts.header', ['titulo' => 'Gestión de Pagos'])
@include('layouts.sidebaradmin')

<!-- Estilos y Scripts específicos de pagos -->
@vite([
    'resources/css/pagos.css',
    'resources/js/modules/admin/pagos.js'
])

<style>
.pagos-main-grid {
    display: grid !important;
    grid-template-columns: minmax(0, 1fr) 300px !important;
    gap: 20px !important;
    align-items: start !important;
}
.pagos-tabla-card {
    min-width: 0 !important;
    overflow: hidden !important;
}
.table-responsive {
    width: 100% !important;
    overflow-x: auto !important;
    -webkit-overflow-scrolling: touch !important;
}
.pagos-table {
    width: 100% !important;
    min-width: 890px !important;
    border-collapse: collapse !important;
}
.td-acciones {
    text-align: center !important;
    white-space: nowrap !important;
    width: 130px !important;
    min-width: 130px !important;
}
.acciones-wrapper {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    white-space: nowrap !important;
}
.btn-accion {
    width: 32px !important;
    height: 32px !important;
    border-radius: 9px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 13px !important;
    border: none !important;
    cursor: pointer !important;
    transition: all 0.2s ease !important;
    flex-shrink: 0 !important;
}
.btn-ticket-pago {
    color: #7c1db8 !important;
    background: #f6effb !important;
    border: 1px solid #ead6f6 !important;
}
.btn-ticket-pago:hover {
    background: #edd8f7 !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 3px 8px rgba(124, 29, 184, 0.2) !important;
}
.btn-editar-pago {
    color: #0284c7 !important;
    background: #e0f2fe !important;
    border: 1px solid #bae6fd !important;
}
.btn-editar-pago:hover {
    background: #bae6fd !important;
    color: #0369a1 !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 3px 8px rgba(2, 132, 199, 0.2) !important;
}
.btn-eliminar-pago {
    color: #dc2626 !important;
    background: #fee2e2 !important;
    border: 1px solid #fecaca !important;
}
.btn-eliminar-pago:hover {
    background: #fecaca !important;
    color: #b91c1c !important;
    transform: translateY(-2px) !important;
    box-shadow: 0 3px 8px rgba(220, 38, 38, 0.2) !important;
}
@media (max-width: 1200px) {
    .pagos-main-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- Contenido Principal -->
<div class="page-content" id="pagosContainer" data-base-url="{{ url('/admin/pagos') }}">

<!-- ══════════════════ ALERTAS DE ACCIÓN ══════════════════ -->
@if (session('success'))
    <div class="alert alert-success" style="padding: 14px 18px; border-radius: 12px; background: #eaf6ef; color: #2e6946; border: 1px solid #cbe9d6; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
        <i class="fa-solid fa-circle-check" style="font-size: 18px; color: #16a34a;"></i>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger" style="padding: 14px 18px; border-radius: 12px; background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
        <i class="fa-solid fa-circle-exclamation" style="font-size: 18px; color: #dc2626;"></i>
        <span>{{ session('error') }}</span>
    </div>
@endif

@if (isset($errors) && $errors->any())
    <div class="alert alert-danger" style="padding: 14px 18px; border-radius: 12px; background: #fdf2f2; color: #991b1b; border: 1px solid #fecaca; margin-bottom: 20px;">
        <ul style="margin: 0; padding-left: 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- ==========================================
     ENCABEZADO
========================================== -->
<div class="pagos-header">
    <div class="pagos-titulo">
        <div class="pagos-icono">
            <i class="fa-solid fa-money-bill-wave"></i>
        </div>
        <div>
            <h1>Gestión de Pagos</h1>
            <p>Monitorea y registra las transacciones y comprobantes de pago</p>
        </div>
    </div>
    <button type="button" class="btn-nuevo-pago" id="abrirNuevoPago">
        <i class="fa-solid fa-plus"></i> Registrar Pago
    </button>
</div>

<!-- ==========================================
     TARJETAS DE ESTADÍSTICAS
========================================== -->
<div class="pagos-cards">

    <div class="pago-card">
        <div class="pago-card-icon green">
            <i class="fa-solid fa-sack-dollar"></i>
        </div>
        <div class="pago-card-body">
            <span>Total Recaudado</span>
            <strong>${{ number_format((float)($totalRecaudado ?? 0), 0, ',', '.') }}</strong>
            <small class="green-text">
                <i class="fa-solid fa-circle-check"></i> Pagos aprobados
            </small>
        </div>
    </div>

    <div class="pago-card">
        <div class="pago-card-icon purple">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="pago-card-body">
            <span>Pagos Completados</span>
            <strong>{{ $pagosCompletados ?? 0 }}</strong>
            <small class="purple-text">
                <i class="fa-solid fa-check-double"></i> Transacciones
            </small>
        </div>
    </div>

    <div class="pago-card">
        <div class="pago-card-icon gold">
            <i class="fa-solid fa-clock"></i>
        </div>
        <div class="pago-card-body">
            <span>Pagos Pendientes</span>
            <strong>{{ $pagosPendientes ?? 0 }}</strong>
            <small class="gold-text">
                <i class="fa-solid fa-hourglass-half"></i> Por confirmar
            </small>
        </div>
    </div>

    <div class="pago-card">
        <div class="pago-card-icon blue">
            <i class="fa-solid fa-receipt"></i>
        </div>
        <div class="pago-card-body">
            <span>Total Transacciones</span>
            <strong>{{ $totalTransacc ?? 0 }}</strong>
            <small class="blue-text">Registros en sistema</small>
        </div>
    </div>

</div>

<!-- ==========================================
     CONTENIDO PRINCIPAL
========================================== -->
<div class="pagos-main-grid">

    <!-- TABLA DE PAGOS -->
    <div class="pagos-tabla-card">

        <div class="pagos-tabla-header">
            <h3>
                <i class="fa-solid fa-list-check"></i>
                Historial de Pagos y Comprobantes
            </h3>

            <div class="pagos-header-actions">
                <select id="filtroMetodo">
                    <option value="">Todos los métodos</option>
                    <option value="Efectivo">Efectivo</option>
                    <option value="Transferencia">Transferencia / Nequi</option>
                </select>
            </div>
        </div>

        <!-- BUSCADOR -->
        <div class="pagos-buscador">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="buscarPago" placeholder="Buscar por cliente, servicio, referencia o método...">
        </div>

        <!-- TABLA -->
        <div class="table-responsive" style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table id="tablaPagos" class="pagos-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr>
                        <th style="min-width: 140px; padding-left: 20px;">Cliente</th>
                        <th style="min-width: 110px;">Servicio</th>
                        <th style="min-width: 100px;">Método</th>
                        <th style="min-width: 95px; white-space: nowrap;">Monto</th>
                        <th style="min-width: 130px; max-width: 180px;">Referencia</th>
                        <th style="min-width: 110px;">Comprobante</th>
                        <th style="min-width: 95px;">Estado</th>
                        <th style="min-width: 125px; white-space: nowrap;">Fecha</th>
                        <th class="text-center" style="text-align: center; min-width: 130px; width: 130px; white-space: nowrap;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagos ?? [] as $pago)
                        @php
                            $pagoId = is_array($pago) ? ($pago['id'] ?? $pago['id_pago'] ?? '') : ($pago->id ?? $pago->id_pago ?? '');
                            $citaId = is_array($pago) ? ($pago['cita_id'] ?? $pago['id_cita'] ?? '') : ($pago->cita_id ?? $pago->id_cita ?? '');
                            
                            $clienteNombre = is_array($pago) 
                                ? ($pago['cliente'] ?? 'Cliente General')
                                : ($pago->cita?->cliente?->user ? ($pago->cita->cliente->user->name . ' ' . ($pago->cita->cliente->user->apellido ?? '')) : 'Cliente General');

                            $servicioNombre = is_array($pago)
                                ? ($pago['servicio'] ?? 'Servicio')
                                : ($pago->cita?->servicio?->nombre ?? 'Servicio');

                            $metodo = is_array($pago) ? ($pago['metodo_pago'] ?? 'Efectivo') : ($pago->metodo_pago ?? 'Efectivo');
                            $monto = is_array($pago) ? ($pago['monto'] ?? 0) : ($pago->monto ?? 0);
                            $referencia = is_array($pago) ? ($pago['referencia'] ?? $pago['observaciones'] ?? '') : ($pago->observaciones ?? '');
                            $estado = strtolower(is_array($pago) ? ($pago['estado'] ?? 'completado') : ($pago->estado ?? 'completado'));
                            
                            $comprobanteRuta = is_array($pago) 
                                ? ($pago['comprobante_ruta'] ?? '') 
                                : ($pago->comprobante?->ruta_archivo ?? '');

                            $rawFecha = is_array($pago) ? ($pago['fecha_pago'] ?? null) : ($pago->fecha_pago ?? null);
                            $fechaFormateada = $rawFecha ? \Carbon\Carbon::parse($rawFecha)->format('d/m/Y, h:i a') : 'Sin fecha';
                        @endphp
                        <tr data-metodo="{{ $metodo }}">
                            <td style="min-width: 140px; padding-left: 20px;">
                                <strong style="color: #2a0d3e;">{{ $clienteNombre }}</strong>
                            </td>

                            <td>{{ $servicioNombre }}</td>

                            <td>
                                <span class="metodo-badge">
                                    <i class="fa-solid {{ (stripos($metodo, 'efectivo') !== false) ? 'fa-money-bill-wave' : 'fa-mobile-screen-button' }}"></i>
                                    {{ $metodo }}
                                </span>
                            </td>

                            <td style="white-space: nowrap;">
                                <strong>${{ number_format((float)$monto, 0, ',', '.') }}</strong>
                            </td>

                            <td style="max-width: 180px; font-size: 11.5px; line-height: 1.35; word-break: break-word;">
                                <small style="color: #64748b;">{{ !empty($referencia) ? $referencia : 'N/A' }}</small>
                            </td>

                            <td>
                                @if (!empty($comprobanteRuta))
                                    @php
                                        $fileSrc = str_starts_with($comprobanteRuta, 'http') ? $comprobanteRuta : asset($comprobanteRuta);
                                    @endphp
                                    <a href="{{ $fileSrc }}" target="_blank" class="metodo-badge" style="background:#e0e7ff; color:#3730a3; text-decoration:none;" title="Ver comprobante de pago">
                                        <i class="fa-solid fa-file-image"></i> Ver Comprobante
                                    </a>
                                @else
                                    <span style="color:#a795ad; font-size:11px;">Sin archivo</span>
                                @endif
                            </td>

                            <td>
                                <span class="estado-badge estado-{{ $estado }}">
                                    {{ ucfirst($estado) }}
                                </span>
                            </td>

                            <td style="white-space: nowrap; font-size: 12px; color: #555;">
                                {{ $fechaFormateada }}
                            </td>

                            <td class="td-acciones" style="text-align: center; white-space: nowrap; width: 130px; min-width: 130px;">
                                <div class="acciones-wrapper" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; white-space: nowrap;">
                                    @if(!empty($citaId))
                                        <button type="button" onclick="abrirModalTicketPOS('{{ route('ticket.ver', $citaId) }}?embed=1')" class="btn-accion btn-ticket-pago" title="Ver / Descargar Factura POS" style="width: 32px; height: 32px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; color: #7c1db8; background: #f6effb; border: 1px solid #ead6f6; cursor: pointer; transition: all 0.2s ease;">
                                            <i class="fa-solid fa-receipt"></i>
                                        </button>
                                    @endif

                                    <button type="button" class="btn-accion btn-editar-pago" 
                                        data-id="{{ $pagoId }}" 
                                        data-cita="{{ $citaId }}"
                                        data-metodo="{{ $metodo }}"
                                        data-monto="{{ $monto }}"
                                        data-estado="{{ $estado }}"
                                        data-referencia="{{ $referencia }}"
                                        title="Editar Pago"
                                        style="width: 32px; height: 32px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd; cursor: pointer; transition: all 0.2s ease;">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>

                                    <button type="button" class="btn-accion btn-eliminar-pago" 
                                        data-id="{{ $pagoId }}" 
                                        data-cliente="{{ $clienteNombre }}"
                                        title="Eliminar Pago"
                                        style="width: 32px; height: 32px; border-radius: 9px; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; color: #dc2626; background: #fee2e2; border: 1px solid #fecaca; cursor: pointer; transition: all 0.2s ease;">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="tabla-vacia">
                                No hay pagos o transacciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- PANEL DERECHO: MÉTODOS Y RESUMEN -->
    <div class="pagos-side">
        <div class="metodos-resumen-card">
            <h3><i class="fa-solid fa-wallet"></i> Métodos Aceptados</h3>

            <div class="metodos-item">
                <div class="metodos-item-info">
                    <div class="metodos-item-icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                    <div>
                        <strong>Efectivo</strong>
                        <span>Pago presencial en caja</span>
                    </div>
                </div>
            </div>

            <div class="metodos-item">
                <div class="metodos-item-info">
                    <div class="metodos-item-icon"><i class="fa-solid fa-mobile-screen-button"></i></div>
                    <div>
                        <strong>Transferencia (Nequi)</strong>
                        <span>Con soporte para comprobante</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- ==========================================
     MODAL REGISTRAR PAGO
========================================== -->
<div class="modal-pago-overlay" id="modalCrearPago">
    <div class="modal-pago-container">
        <div class="modal-pago-header">
            <h3>
                <i class="fa-solid fa-receipt"></i>
                Registrar Nuevo Pago
            </h3>
            <button type="button" class="btn-cerrar-modal" id="cerrarModalPago">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-pago-body">
            <form action="{{ route('pagos.store') }}" method="POST" enctype="multipart/form-data" id="formCrearPago">
                @csrf

                <div class="campo-pago">
                    <label>Seleccionar Cita / Cliente</label>
                    <select name="id_cita" id="id_cita_pago" required>
                        <option value="" disabled selected>Selecciona una cita para registrar pago</option>
                        @foreach ($citasParaPago ?? [] as $c)
                            @php
                                $cId = is_array($c) ? ($c['id_cita'] ?? $c['id'] ?? '') : ($c->id ?? '');
                                $cCliente = is_array($c) ? ($c['cliente'] ?? 'Cliente') : ($c->cliente?->user ? ($c->cliente->user->name . ' ' . ($c->cliente->user->apellido ?? '')) : 'Cliente');
                                $cServicio = is_array($c) ? ($c['servicio'] ?? 'Servicio') : ($c->servicio?->nombre ?? 'Servicio');
                                $cPrecio = is_array($c) ? ($c['precio'] ?? 0) : ($c->servicio?->precio ?? 0);
                            @endphp
                            <option value="{{ $cId }}" data-precio="{{ $cPrecio }}">
                                Cita #{{ $cId }} - {{ $cCliente }} ({{ $cServicio }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="campo-pago">
                    <label>Método de Pago</label>
                    <select name="metodo_pago" id="metodo_pago_select" required>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia (Nequi)</option>
                    </select>
                </div>

                <div class="campo-pago">
                    <label>Monto ($)</label>
                    <input type="number" step="0.01" name="monto" id="monto_pago" placeholder="0.00" required>
                </div>

                <div class="campo-pago">
                    <label>Número de Referencia (Opcional)</label>
                    <input type="text" name="referencia" placeholder="Ej. Nequi #982341">
                </div>

                <div class="campo-pago">
                    <label>Comprobante Nequi / Transferencia (Imagen o PDF)</label>
                    <input type="file" name="comprobante_nequi" accept="image/*,.pdf">
                    <small style="color:#8d7b96; display:block; margin-top:4px;">Adjunta la foto o captura del comprobante Nequi.</small>
                </div>

                <div class="campo-pago">
                    <label>Estado del Pago</label>
                    <select name="estado" required>
                        <option value="completado">Completado</option>
                        <option value="pendiente">Pendiente</option>
                    </select>
                </div>

                <div class="pago-form-buttons">
                    <button type="button" class="btn-cancelar-pago" id="cancelarModalPago">Cancelar</button>
                    <button type="submit" class="btn-guardar-pago">
                        <i class="fa-solid fa-floppy-disk"></i> Registrar Pago
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<!-- ==========================================
     MODAL EDITAR PAGO
========================================== -->
<div class="modal-pago-overlay" id="modalEditarPago">
    <div class="modal-pago-container">
        <div class="modal-pago-header">
            <h3>
                <i class="fa-solid fa-pen-to-square"></i>
                Editar Registro de Pago
            </h3>
            <button type="button" class="btn-cerrar-modal" id="cerrarModalEditarPago">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-pago-body">
            <form action="" method="POST" enctype="multipart/form-data" id="formEditarPago">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_id_pago">

                <div class="campo-pago">
                    <label>Método de Pago</label>
                    <select name="metodo_pago" id="edit_metodo_pago" required>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Transferencia">Transferencia (Nequi)</option>
                    </select>
                </div>

                <div class="campo-pago">
                    <label>Monto ($)</label>
                    <input type="number" step="0.01" name="monto" id="edit_monto_pago" required>
                </div>

                <div class="campo-pago">
                    <label>Número de Referencia</label>
                    <input type="text" name="referencia" id="edit_referencia_pago">
                </div>

                <div class="campo-pago">
                    <label>Actualizar Comprobante Nequi (Opcional)</label>
                    <input type="file" name="comprobante_nequi" accept="image/*,.pdf">
                </div>

                <div class="campo-pago">
                    <label>Estado del Pago</label>
                    <select name="estado" id="edit_estado_pago" required>
                        <option value="completado">Completado</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="rechazado">Rechazado</option>
                    </select>
                </div>

                <div class="pago-form-buttons">
                    <button type="button" class="btn-cancelar-pago" id="cancelarModalEditarPago">Cancelar</button>
                    <button type="submit" class="btn-guardar-pago">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulario invisible para Eliminar Pago -->
<form id="formEliminarPago" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@include('components.modal-ticket-pos')

</div> <!-- Cierre de page-content -->

@include('layouts.footer')

    </div> <!-- Cierre de .main-content-wrapper -->
</div> <!-- Cierre de .app-layout -->

</body>
</html>