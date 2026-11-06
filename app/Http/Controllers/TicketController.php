<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Configuracion;
use App\Models\Pago;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Muestra el comprobante o factura tipo POS para una cita específica.
     */
    public function verTicket(Request $request, $id)
    {
        $cita = Cita::with(['cliente.user', 'trabajador.user', 'servicio.categoria', 'pago'])->findOrFail($id);
        $user = auth()->user();

        // Validar permisos de acceso:
        // Si el usuario es Cliente (role_id === 3), solo puede ver su propia cita/ticket
        if ($user && $user->role_id === 3) {
            $cliente = $user->cliente;
            if (!$cliente || $cita->cliente_id !== $cliente->id) {
                abort(403, 'No tienes autorización para consultar esta factura / ticket POS.');
            }
        }

        $configuracion = Configuracion::getInstancia();
        $pago = $cita->pago;

        $numeroTicket = $pago?->numero_ticket ?? ('POS-' . date('Y') . '-' . str_pad($cita->id, 5, '0', STR_PAD_LEFT));
        $total = $pago ? (float) $pago->monto : (float) ($cita->servicio?->precio ?? 0);

        // Cálculo de IVA si está configurado en el sistema
        $ivaPorcentaje = (float) ($configuracion->iva_porcentaje ?? 0);
        $subtotal = $total;
        $ivaMonto = 0;

        if ($ivaPorcentaje > 0) {
            $subtotal = round($total / (1 + ($ivaPorcentaje / 100)), 2);
            $ivaMonto = round($total - $subtotal, 2);
        }

        $autoPrint = $request->query('print') == '1';

        return view('pos.ticket', compact(
            'cita',
            'pago',
            'configuracion',
            'numeroTicket',
            'total',
            'subtotal',
            'ivaPorcentaje',
            'ivaMonto',
            'autoPrint'
        ));
    }

    /**
     * Permite consultar el ticket POS directamente a partir del ID de pago.
     */
    public function verTicketPorPago(Request $request, $id)
    {
        $pago = Pago::findOrFail($id);
        return $this->verTicket($request, $pago->cita_id);
    }
}
