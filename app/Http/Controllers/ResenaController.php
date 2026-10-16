<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resena;
use App\Models\Cita;

class ResenaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        // Si el usuario es trabajador (rol 2), cargar vista especializada de trabajador
        if ($user && (int)$user->role_id === 2) {
            $trabajador = $user->trabajador ?? \App\Models\Trabajador::where('user_id', $user->id)->first();
            $trabajadorId = $trabajador?->id;

            $query = Resena::with(['cliente.user', 'cita.servicio', 'cita.trabajador.user']);
            if ($trabajadorId) {
                $query->whereHas('cita', function ($q) use ($trabajadorId) {
                    $q->where('trabajador_id', $trabajadorId);
                });
            }

            $resenas = $query->orderByDesc('fecha_resena')->orderByDesc('id')->get();

            // Si aún no tiene reseñas específicas de sus citas, mostrar las del salón para enriquecer la vista
            if ($resenas->isEmpty()) {
                $resenas = Resena::with(['cliente.user', 'cita.servicio', 'cita.trabajador.user'])
                    ->orderByDesc('fecha_resena')
                    ->orderByDesc('id')
                    ->get();
            }

            $totalResenas = $resenas->count();
            $promedioCalificacion = $totalResenas > 0 ? round($resenas->avg('calificacion'), 1) : 5.0;

            $positivas = $resenas->where('calificacion', '>=', 4)->count();
            $neutras = $resenas->where('calificacion', 3)->count();
            $negativas = $resenas->where('calificacion', '<=', 2)->count();

            $c5 = $resenas->where('calificacion', 5)->count();
            $c4 = $resenas->where('calificacion', 4)->count();
            $c3 = $resenas->where('calificacion', 3)->count();
            $c2 = $resenas->where('calificacion', 2)->count();
            $c1 = $resenas->where('calificacion', 1)->count();

            $pct5 = $totalResenas > 0 ? round(($c5 / $totalResenas) * 100, 1) : 0;
            $pct4 = $totalResenas > 0 ? round(($c4 / $totalResenas) * 100, 1) : 0;
            $pct3 = $totalResenas > 0 ? round(($c3 / $totalResenas) * 100, 1) : 0;
            $pct2 = $totalResenas > 0 ? round(($c2 / $totalResenas) * 100, 1) : 0;
            $pct1 = $totalResenas > 0 ? round(($c1 / $totalResenas) * 100, 1) : 0;

            return view('trabajador.resenas.resenas_trabajador', compact(
                'resenas',
                'totalResenas',
                'promedioCalificacion',
                'positivas',
                'neutras',
                'negativas',
                'c5', 'c4', 'c3', 'c2', 'c1',
                'pct5', 'pct4', 'pct3', 'pct2', 'pct1'
            ));
        }

        $resenas = Resena::with(['cliente.user', 'cita.servicio'])
            ->orderByDesc('fecha_resena')
            ->orderByDesc('id')
            ->get();

        $totalResenas = $resenas->count();
        $promedioCalificacion = $totalResenas > 0 ? round($resenas->avg('calificacion'), 1) : 5.0;

        // Distribución por estrellas
        $c5 = $resenas->where('calificacion', 5)->count();
        $c4 = $resenas->where('calificacion', 4)->count();
        $c3 = $resenas->where('calificacion', 3)->count();
        $c2 = $resenas->where('calificacion', 2)->count();
        $c1 = $resenas->where('calificacion', 1)->count();

        $pct5 = $totalResenas > 0 ? round(($c5 / $totalResenas) * 100) : 100;
        $pct4 = $totalResenas > 0 ? round(($c4 / $totalResenas) * 100) : 0;
        $pct3 = $totalResenas > 0 ? round(($c3 / $totalResenas) * 100) : 0;
        $pct2 = $totalResenas > 0 ? round(($c2 / $totalResenas) * 100) : 0;
        $pct1 = $totalResenas > 0 ? round(($c1 / $totalResenas) * 100) : 0;

        // Citas disponibles para reseñar
        $citasSinResena = Cita::with(['cliente.user', 'servicio'])
            ->whereDoesntHave('resena')
            ->orderByDesc('id')
            ->get()
            ->map(function ($c) {
                $nombreCliente = $c->cliente?->user 
                    ? ($c->cliente->user->name . ' ' . ($c->cliente->user->apellido ?? '')) 
                    : 'Cliente #' . $c->cliente_id;
                $nombreServicio = $c->servicio?->nombre ?? 'Servicio';

                return [
                    'id_cita' => $c->id,
                    'cliente' => trim($nombreCliente),
                    'servicio' => $nombreServicio,
                ];
            });

        $todasCitas = Cita::with(['cliente.user', 'servicio'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($c) {
                $nombreCliente = $c->cliente?->user 
                    ? ($c->cliente->user->name . ' ' . ($c->cliente->user->apellido ?? '')) 
                    : 'Cliente #' . $c->cliente_id;
                $nombreServicio = $c->servicio?->nombre ?? 'Servicio';

                return [
                    'id_cita' => $c->id,
                    'cliente' => trim($nombreCliente),
                    'servicio' => $nombreServicio,
                ];
            });

        return view('admin.resenas.resenas', compact(
            'resenas',
            'totalResenas',
            'promedioCalificacion',
            'pct5',
            'pct4',
            'pct3',
            'pct2',
            'pct1',
            'citasSinResena',
            'todasCitas'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_cita' => 'required|exists:citas,id',
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|max:1000',
        ]);

        $cita = Cita::findOrFail($request->input('id_cita'));

        Resena::create([
            'cliente_id' => $cita->cliente_id,
            'cita_id' => $cita->id,
            'calificacion' => (int) $request->input('calificacion', 5),
            'comentario' => $request->input('comentario'),
            'fecha_resena' => now(),
        ]);

        return redirect()->route('resenas.index')->with('success', 'Valoración guardada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $resena = Resena::findOrFail($id);
        $resena->delete();

        return redirect()->route('resenas.index')->with('success', 'Reseña eliminada correctamente.');
    }
}
