<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Horario;
use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HorarioController extends Controller
{
    /**
     * Muestra la lista de horarios y disponibilidad del trabajador.
     */
    public function index()
    {
        $user = Auth::user();
        $trabajador = $user?->trabajador ?? Trabajador::where('user_id', $user?->id)->first();

        if (!$trabajador && $user && ($user->role_id === 2 || $user->role_id === 1)) {
            $trabajador = Trabajador::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'especialidad' => 'Lash Artist & Brow Designer',
                    'descripcion' => 'Especialista en pestañas pelo a pelo, volumen ruso y diseño de cejas.',
                    'estado' => 'activo',
                ]
            );
        }

        $trabajadorId = $trabajador?->id;

        $horariosQuery = Horario::withCount('citas');

        if ($trabajadorId) {
            $horariosQuery->where('trabajador_id', $trabajadorId);
        }

        $horarios = $horariosQuery->orderBy('fecha', 'desc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        // Si el trabajador aún no tiene horarios registrados propios, mostrar los horarios generales del salón
        if ($horarios->isEmpty()) {
            $horarios = Horario::withCount('citas')->orderBy('fecha', 'desc')->orderBy('hora_inicio', 'asc')->get();
        }

        $totalHorarios = $horarios->count();
        $disponibles = $horarios->filter(function ($h) {
            return in_array(strtolower($h->estado), ['activo', 'disponible']) && ($h->citas_count == 0);
        })->count();

        $ocupados = $horarios->filter(function ($h) {
            return $h->citas_count > 0 || strtolower($h->estado) === 'ocupado';
        })->count();

        $proximoHorario = $horarios->filter(function ($h) {
            $fecha = $h->fecha ? Carbon::parse($h->fecha) : null;
            return $fecha && $fecha->greaterThanOrEqualTo(Carbon::today());
        })->sortBy('fecha')->first();

        $hoy = Carbon::today()->toDateString();

        return view('trabajador.mi horario.horario_trabajador', compact(
            'horarios',
            'totalHorarios',
            'disponibles',
            'ocupados',
            'proximoHorario',
            'hoy',
            'trabajador'
        ));
    }

    /**
     * Guarda un nuevo bloque de horario para el trabajador.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'estado' => 'nullable|string',
        ]);

        $user = Auth::user();
        $trabajador = $user?->trabajador ?? Trabajador::firstOrCreate(
            ['user_id' => $user?->id],
            [
                'especialidad' => 'Lash Artist & Brow Designer',
                'descripcion' => 'Especialista en pestañas pelo a pelo, volumen ruso y diseño de cejas.',
                'estado' => 'activo',
            ]
        );

        $estado = strtolower($request->input('estado', 'activo'));
        if ($estado === 'disponible') {
            $estado = 'activo';
        }

        Horario::create([
            'trabajador_id' => $trabajador->id,
            'fecha' => $request->input('fecha'),
            'hora_inicio' => $request->input('hora_inicio'),
            'hora_fin' => $request->input('hora_fin'),
            'estado' => $estado,
        ]);

        return back()->with('success', '¡Bloque de horario creado exitosamente!');
    }

    /**
     * Elimina un bloque de horario.
     */
    public function destroy($id)
    {
        $horario = Horario::findOrFail($id);

        $citasCount = Cita::where('horario_id', $horario->id)->count();
        if ($citasCount > 0) {
            return back()->with('error', 'No se puede eliminar este bloque de horario porque tiene citas asignadas.');
        }

        $horario->delete();

        return back()->with('success', '¡Bloque de horario eliminado correctamente!');
    }
}
