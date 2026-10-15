<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuracion;
use Illuminate\Support\Facades\File;

class ConfiguracionController extends Controller
{
    /**
     * Display configuration view.
     */
    public function index()
    {
        $usuario = Auth::user();
        $configuracion = Configuracion::getInstancia();

        return view('admin.configuracion.configuracion', compact('usuario', 'configuracion'));
    }

    /**
     * Update settings in database.
     */
    public function update(Request $request)
    {
        $request->validate([
            'nombre_estudio' => 'required|string|max:150',
            'telefono' => 'required|string|max:50',
            'email_contacto' => 'required|email|max:100',
            'direccion' => 'required|string|max:255',
            'zona_horaria' => 'nullable|string|max:80',
            'idioma' => 'nullable|string|max:10',
            'moneda' => 'nullable|string|max:10',
            'formato_fecha' => 'nullable|string|max:20',
            'formato_hora' => 'nullable|string|max:10',
            'antelacion_reservar' => 'nullable|string|max:20',
            'antelacion_cancelar' => 'nullable|string|max:20',
            'tiempo_entre_citas' => 'nullable|string|max:20',
            'duracion_predeterminada' => 'nullable|string|max:20',
            'politica_cancelacion' => 'nullable|string|max:1000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $config = Configuracion::getInstancia();

        $config->nombre_estudio = $request->input('nombre_estudio');
        $config->telefono = $request->input('telefono');
        $config->email_contacto = $request->input('email_contacto');
        $config->direccion = $request->input('direccion');
        
        if ($request->filled('zona_horaria')) {
            $config->zona_horaria = $request->input('zona_horaria');
        }

        if ($request->filled('idioma')) {
            $config->idioma = $request->input('idioma');
        }

        if ($request->filled('moneda')) {
            $config->moneda = $request->input('moneda');
        }

        if ($request->filled('formato_fecha')) {
            $config->formato_fecha = $request->input('formato_fecha');
        }

        if ($request->filled('formato_hora')) {
            $config->formato_hora = $request->input('formato_hora');
        }

        $config->reservas_online = $request->has('reservas_online');
        $config->recordatorios_auto = $request->has('recordatorios_auto');

        if ($request->filled('antelacion_reservar')) {
            $config->antelacion_reservar = $request->input('antelacion_reservar');
        }

        if ($request->filled('antelacion_cancelar')) {
            $config->antelacion_cancelar = $request->input('antelacion_cancelar');
        }

        if ($request->filled('tiempo_entre_citas')) {
            $config->tiempo_entre_citas = $request->input('tiempo_entre_citas');
        }

        if ($request->filled('duracion_predeterminada')) {
            $config->duracion_predeterminada = $request->input('duracion_predeterminada');
        }

        if ($request->has('politica_cancelacion')) {
            $config->politica_cancelacion = $request->input('politica_cancelacion');
        }

        // Subir logo si se seleccionó uno
        if ($request->hasFile('logo')) {
            $archivo = $request->file('logo');
            $extension = $archivo->getClientOriginalExtension();
            $nombreGuardar = 'logo_studio_' . time() . '.' . $extension;
            $destino = public_path('img/logo');

            if (!File::isDirectory($destino)) {
                File::makeDirectory($destino, 0755, true, true);
            }

            // Eliminar logo anterior si existe y no es el por defecto
            if ($config->logo_path && File::exists(public_path($config->logo_path))) {
                File::delete(public_path($config->logo_path));
            }

            $archivo->move($destino, $nombreGuardar);
            $config->logo_path = 'img/logo/' . $nombreGuardar;
        }

        $config->save();

        return redirect()->route('configuracion.index')->with('success', 'Configuración del estudio guardada y sincronizada en la base de datos.');
    }
}
