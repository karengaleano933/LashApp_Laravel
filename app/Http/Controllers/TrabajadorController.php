<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class TrabajadorController extends Controller
{
    /**
     * Muestra el perfil profesional del trabajador.
     */
    public function perfil()
    {
        $user = Auth::user();
        $trabajador = $user?->trabajador ?? Trabajador::firstOrCreate(
            ['user_id' => $user?->id],
            [
                'especialidad' => 'Lash Artist & Brow Designer',
                'descripcion' => 'Especialista en pestañas pelo a pelo, volumen ruso y diseño de cejas.',
                'estado' => 'activo',
            ]
        );

        $trabajadorId = $trabajador?->id;

        $totalCitas = Cita::where('trabajador_id', $trabajadorId)->count();
        $citasAtendidas = Cita::where('trabajador_id', $trabajadorId)
            ->whereIn('estado', ['confirmado', 'finalizado', 'completado', 'completada', 'realizada'])
            ->count();

        $resenas = Resena::whereHas('cita', function ($q) use ($trabajadorId) {
            $q->where('trabajador_id', $trabajadorId);
        })->get();

        $totalResenas = $resenas->count();
        $promedioCalificacion = $totalResenas > 0 ? round($resenas->avg('calificacion'), 1) : 5.0;

        $serviciosTotal = Servicio::where('estado', 'activo')->count();

        // Próxima cita agendada
        $proximaCita = Cita::with(['servicio', 'cliente.user'])
            ->where('trabajador_id', $trabajadorId)
            ->whereIn('estado', ['confirmado', 'confirmada', 'pendiente'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->first();

        return view('trabajador.perfil.perfil_trabajador', compact(
            'user',
            'trabajador',
            'totalCitas',
            'citasAtendidas',
            'totalResenas',
            'promedioCalificacion',
            'serviciosTotal',
            'proximaCita'
        ));
    }

    /**
     * Actualiza la información personal y profesional del trabajador.
     */
    public function updatePerfil(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'documento' => 'nullable|string|max:30',
            'especialidad' => 'nullable|string|max:150',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $user->name = $request->input('name');
        $user->apellido = $request->input('apellido');
        $user->telefono = $request->input('telefono');
        $user->documento = $request->input('documento');
        $user->save();

        $trabajador = $user->trabajador ?? Trabajador::firstOrCreate(['user_id' => $user->id]);
        $trabajador->especialidad = $request->input('especialidad', $trabajador->especialidad);
        $trabajador->descripcion = $request->input('descripcion', $trabajador->descripcion);
        $trabajador->save();

        return back()->with('success', '¡Tus datos de perfil profesional se han actualizado correctamente!');
    }

    /**
     * Actualiza la foto de perfil del trabajador.
     */
    public function updateFoto(Request $request)
    {
        $request->validate([
            'foto_perfil' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        $user = Auth::user();

        if ($request->hasFile('foto_perfil')) {
            $file = $request->file('foto_perfil');
            $dirUpload = public_path('uploads/perfil');

            if (!File::exists($dirUpload)) {
                File::makeDirectory($dirUpload, 0755, true);
            }

            $fileName = 'trabajador_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dirUpload, $fileName);

            $user->foto = 'uploads/perfil/' . $fileName;
            $user->save();

            return back()->with('success', '¡Foto de perfil actualizada con éxito!');
        }

        return back()->with('error', 'No se pudo subir la foto de perfil.');
    }

    /**
     * Actualiza la contraseña del trabajador.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'contrasena_actual' => 'required|string',
            'contrasena_nueva' => 'required|string|min:6',
            'contrasena_confirmar' => 'required|string|same:contrasena_nueva',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('contrasena_actual'), $user->password)) {
            return back()->with('error', 'La contraseña actual no coincide.');
        }

        $user->password = Hash::make($request->input('contrasena_nueva'));
        $user->save();

        return back()->with('success', '¡Tu contraseña ha sido actualizada con éxito!');
    }
}
