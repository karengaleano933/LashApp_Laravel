<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Cita;
use App\Models\Comprobante;
use Illuminate\Support\Facades\File;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pagos = Pago::with(['cita.cliente.user', 'cita.servicio', 'comprobante'])
            ->orderByDesc('fecha_pago')
            ->orderByDesc('id')
            ->get();

        // Citas para asociar al registrar pago
        $citasParaPago = Cita::with(['cliente.user', 'servicio'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($c) {
                $nombreCliente = $c->cliente?->user 
                    ? ($c->cliente->user->name . ' ' . ($c->cliente->user->apellido ?? '')) 
                    : 'Cliente #' . $c->cliente_id;
                $nombreServicio = $c->servicio?->nombre ?? 'Servicio General';
                $precio = $c->servicio?->precio ?? 0;

                return [
                    'id_cita' => $c->id,
                    'cliente' => trim($nombreCliente),
                    'servicio' => $nombreServicio,
                    'precio' => $precio,
                ];
            });

        // Métricas de pagos
        $totalRecaudado = Pago::where('estado', 'completado')->sum('monto');
        $pagosCompletados = Pago::where('estado', 'completado')->count();
        $pagosPendientes = Pago::where('estado', 'pendiente')->count();
        $totalTransacc = Pago::count();

        return view('admin.pagos.pagos', compact(
            'pagos',
            'citasParaPago',
            'totalRecaudado',
            'pagosCompletados',
            'pagosPendientes',
            'totalTransacc'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_cita' => 'required|exists:citas,id',
            'metodo_pago' => 'required|string|max:50',
            'monto' => 'required|numeric|min:0',
            'estado' => 'required|in:completado,pendiente,rechazado,cancelado,Completado,Pendiente,Cancelado',
            'referencia' => 'nullable|string|max:100',
            'comprobante_nequi' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:4096',
        ]);

        $estado = strtolower($request->input('estado', 'completado'));
        if ($estado === 'cancelado') {
            $estado = 'rechazado';
        }

        $pago = new Pago();
        $pago->cita_id = $request->input('id_cita');
        $pago->metodo_pago = $request->input('metodo_pago');
        $pago->monto = $request->input('monto');
        $pago->estado = $estado;
        $pago->fecha_pago = now();
        $pago->observaciones = $request->input('referencia');
        $pago->save();

        // Subir comprobante si se adjuntó
        if ($request->hasFile('comprobante_nequi')) {
            $archivo = $request->file('comprobante_nequi');
            $extension = $archivo->getClientOriginalExtension();
            $nombreOriginal = $archivo->getClientOriginalName();
            $nombreGuardar = time() . '_' . uniqid() . '.' . $extension;

            $destino = public_path('img/comprobantes');
            if (!File::isDirectory($destino)) {
                File::makeDirectory($destino, 0755, true, true);
            }

            $archivo->move($destino, $nombreGuardar);

            Comprobante::create([
                'pago_id' => $pago->id,
                'nombre_archivo' => $nombreOriginal,
                'tipo_archivo' => $extension,
                'ruta_archivo' => 'img/comprobantes/' . $nombreGuardar,
                'fecha_subida' => now(),
            ]);
        }

        return redirect()->route('pagos.index')->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'metodo_pago' => 'required|string|max:50',
            'monto' => 'required|numeric|min:0',
            'estado' => 'required|in:completado,pendiente,rechazado,cancelado,Completado,Pendiente,Cancelado',
            'referencia' => 'nullable|string|max:100',
            'comprobante_nequi' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:4096',
        ]);

        $pago = Pago::findOrFail($id);

        $estado = strtolower($request->input('estado', 'completado'));
        if ($estado === 'cancelado') {
            $estado = 'rechazado';
        }

        $pago->metodo_pago = $request->input('metodo_pago');
        $pago->monto = $request->input('monto');
        $pago->estado = $estado;
        if ($request->filled('referencia')) {
            $pago->observaciones = $request->input('referencia');
        }
        $pago->save();

        // Subir comprobante nuevo si se adjuntó
        if ($request->hasFile('comprobante_nequi')) {
            $archivo = $request->file('comprobante_nequi');
            $extension = $archivo->getClientOriginalExtension();
            $nombreOriginal = $archivo->getClientOriginalName();
            $nombreGuardar = time() . '_' . uniqid() . '.' . $extension;

            $destino = public_path('img/comprobantes');
            if (!File::isDirectory($destino)) {
                File::makeDirectory($destino, 0755, true, true);
            }

            $archivo->move($destino, $nombreGuardar);

            // Eliminar archivos antiguos de comprobante si existían
            foreach ($pago->comprobantes as $viejo) {
                if (File::exists(public_path($viejo->ruta_archivo))) {
                    File::delete(public_path($viejo->ruta_archivo));
                }
                $viejo->delete();
            }

            Comprobante::create([
                'pago_id' => $pago->id,
                'nombre_archivo' => $nombreOriginal,
                'tipo_archivo' => $extension,
                'ruta_archivo' => 'img/comprobantes/' . $nombreGuardar,
                'fecha_subida' => now(),
            ]);
        }

        return redirect()->route('pagos.index')->with('success', 'Registro de pago actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pago = Pago::findOrFail($id);

        // Eliminar archivos físicos de comprobantes
        foreach ($pago->comprobantes as $comp) {
            if (File::exists(public_path($comp->ruta_archivo))) {
                File::delete(public_path($comp->ruta_archivo));
            }
            $comp->delete();
        }

        $pago->delete();

        return redirect()->route('pagos.index')->with('success', 'Registro de pago eliminado.');
    }
}
