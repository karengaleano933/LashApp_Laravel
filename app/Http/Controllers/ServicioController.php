<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Support\Facades\File;

class ServicioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servicios = Servicio::with('categoria')->get();
        $categorias = Categoria::all();

        // Estadísticas
        $totalServicios = $servicios->count();
        $serviciosActivos = Servicio::where('estado', 'activo')->count();
        $totalCategorias = $categorias->count();
        $serviciosInact = Servicio::where('estado', 'inactivo')->count();

        return view('admin.servicios.servicios', compact(
            'servicios',
            'categorias',
            'totalServicios',
            'serviciosActivos',
            'totalCategorias',
            'serviciosInact'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'duracion_minuto' => 'required|numeric|min:1',
            'estado' => 'required|in:activo,inactivo',
            'descripcion' => 'nullable|string|max:500',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $servicio = new Servicio();
        $servicio->nombre = $request->input('nombre');
        $servicio->categoria_id = $request->input('categoria_id');
        $servicio->precio = $request->input('precio');
        $servicio->descripcion = $request->input('descripcion') ?? '';
        $servicio->estado = strtolower($request->input('estado', 'activo'));

        // Convertir duración a formato time (HH:MM:SS)
        $minutos = (int) $request->input('duracion_minuto', 30);
        $horas = floor($minutos / 60);
        $restoMin = $minutos % 60;
        $servicio->duracion_minuto = sprintf('%02d:%02d:00', $horas, $restoMin);

        // Manejo de imagen
        if ($request->hasFile('imagen')) {
            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            $rutaDestino = public_path('img/servicios');

            if (!File::isDirectory($rutaDestino)) {
                File::makeDirectory($rutaDestino, 0755, true, true);
            }

            $imagen->move($rutaDestino, $nombreImagen);
            $servicio->imagen_url = 'img/servicios/' . $nombreImagen;
        }

        $servicio->save();

        return redirect()->route('servicios.index')->with('success', 'Servicio creado exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'duracion_minuto' => 'required|numeric|min:1',
            'estado' => 'required|in:activo,inactivo',
            'descripcion' => 'nullable|string|max:500',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $servicio = Servicio::findOrFail($id);
        $servicio->nombre = $request->input('nombre');
        $servicio->categoria_id = $request->input('categoria_id');
        $servicio->precio = $request->input('precio');
        $servicio->descripcion = $request->input('descripcion') ?? '';
        $servicio->estado = strtolower($request->input('estado', 'activo'));

        // Convertir duración a formato time (HH:MM:SS)
        $minutos = (int) $request->input('duracion_minuto', 30);
        $horas = floor($minutos / 60);
        $restoMin = $minutos % 60;
        $servicio->duracion_minuto = sprintf('%02d:%02d:00', $horas, $restoMin);

        // Manejo de imagen
        if ($request->hasFile('imagen')) {
            // Eliminar imagen anterior si existe y no es remota
            if ($servicio->imagen_url && File::exists(public_path($servicio->imagen_url))) {
                File::delete(public_path($servicio->imagen_url));
            }

            $imagen = $request->file('imagen');
            $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
            $rutaDestino = public_path('img/servicios');

            if (!File::isDirectory($rutaDestino)) {
                File::makeDirectory($rutaDestino, 0755, true, true);
            }

            $imagen->move($rutaDestino, $nombreImagen);
            $servicio->imagen_url = 'img/servicios/' . $nombreImagen;
        }

        $servicio->save();

        return redirect()->route('servicios.index')->with('success', 'Servicio actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);

        // Si tiene imagen local, eliminarla
        if ($servicio->imagen_url && File::exists(public_path($servicio->imagen_url))) {
            File::delete(public_path($servicio->imagen_url));
        }

        $servicio->delete();

        return redirect()->route('servicios.index')->with('success', 'Servicio eliminado correctamente.');
    }
}
