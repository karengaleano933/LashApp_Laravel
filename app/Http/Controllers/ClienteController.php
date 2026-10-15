<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Cita;
use App\Models\Resena;
use Carbon\Carbon;

class ClienteController extends Controller
{
    /**
     * Muestra la vista de reseñas del cliente con métricas y citas disponibles para valorar.
     */
    public function resenas()
    {
        $user = Auth::user();
        $cliente = Cliente::firstOrCreate(['user_id' => $user?->id]);

        // Reseñas realizadas por el cliente
        $resenasCliente = Resena::with(['cita.servicio.categoria', 'cita.trabajador.user'])
            ->where('cliente_id', $cliente->id)
            ->orderByDesc('fecha_resena')
            ->orderByDesc('id')
            ->get();

        // Citas completadas que aún no tienen reseña
        $citasSinResena = Cita::with(['servicio', 'trabajador.user'])
            ->where('cliente_id', $cliente->id)
            ->whereIn('estado', ['completado', 'completada', 'finalizado'])
            ->whereDoesntHave('resena')
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        // Métricas de reseñas
        $promedioStudio = round(Resena::avg('calificacion') ?: 5.0, 1);
        $totalResenasStudio = Resena::count();
        $totalResenasCliente = $resenasCliente->count();
        $totalPorCalificar = $citasSinResena->count();

        return view('Clientes.resenas', compact(
            'resenasCliente',
            'citasSinResena',
            'promedioStudio',
            'totalResenasStudio',
            'totalResenasCliente',
            'totalPorCalificar'
        ));
    }

    /**
     * Guarda una nueva reseña enviada por el cliente.
     */
    public function storeResena(Request $request)
    {
        $request->validate([
            'id_cita' => 'required|integer',
            'calificacion' => 'required|integer|min:1|max:5',
            'comentario' => 'required|string|max:1000',
        ]);

        $user = Auth::user();
        $cliente = Cliente::firstOrCreate(['user_id' => $user?->id]);

        $cita = Cita::where('id', $request->input('id_cita'))
            ->where('cliente_id', $cliente->id)
            ->first();

        if (!$cita) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Cita no encontrada o no te pertenece.'], 404);
            }
            return back()->with('error', 'Cita no encontrada o no autorizada.');
        }

        // Verificar si ya tiene reseña
        if (Resena::where('cita_id', $cita->id)->exists()) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Esta cita ya tiene una calificación registrada.'], 422);
            }
            return back()->with('error', 'Esta cita ya tiene una reseña registrada.');
        }

        $resena = Resena::create([
            'cliente_id' => $cliente->id,
            'cita_id' => $cita->id,
            'calificacion' => (int)$request->input('calificacion', 5),
            'comentario' => trim($request->input('comentario')),
            'fecha_resena' => now(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '¡Muchas gracias por tu reseña! Tu opinión nos ayuda a seguir mejorando.',
                'resena' => $resena
            ]);
        }

        return back()->with('success', '¡Muchas gracias por tu reseña! Tu opinión nos ayuda a seguir mejorando.');
    }

    /**
     * Muestra la vista del perfil del cliente.
     */
    public function perfil()
    {
        $user = Auth::user();
        $cliente = Cliente::firstOrCreate(['user_id' => $user?->id]);

        $totalCitas = Cita::where('cliente_id', $cliente->id)->count();
        $citasRealizadas = Cita::where('cliente_id', $cliente->id)
            ->whereIn('estado', ['completado', 'completada', 'finalizado'])
            ->count();

        // Próxima cita agendada
        $proximaCita = Cita::with(['servicio', 'trabajador.user'])
            ->where('cliente_id', $cliente->id)
            ->whereIn('estado', ['confirmado', 'confirmada', 'pendiente'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->first();

        return view('Clientes.perfilcliente', compact(
            'user',
            'cliente',
            'totalCitas',
            'citasRealizadas',
            'proximaCita'
        ));
    }

    /**
     * Actualiza la información personal del cliente.
     */
    public function updatePerfil(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'telefono' => 'nullable|string|max:20',
            'documento' => 'nullable|string|max:30',
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $user = Auth::user();
        $user->name = $request->input('nombre');
        $user->apellido = $request->input('apellido');
        $user->telefono = $request->input('telefono');
        $user->documento = $request->input('documento');
        $user->save();

        $cliente = Cliente::firstOrCreate(['user_id' => $user->id]);
        $cliente->direccion = $request->input('direccion');
        $cliente->fecha_nacimiento = $request->input('fecha_nacimiento');
        $cliente->save();

        return back()->with('success', '¡Tus datos de perfil se han actualizado con éxito!');
    }

    /**
     * Actualiza la foto de perfil del cliente.
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

            $fileName = 'perfil_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($dirUpload, $fileName);

            $user->foto = 'uploads/perfil/' . $fileName;
            $user->save();

            return back()->with('success', '¡Foto de perfil actualizada con éxito!');
        }

        return back()->with('error', 'No se pudo subir la foto de perfil.');
    }

    /**
     * Actualiza la contraseña del cliente.
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
            return back()->with('error', 'La contraseña actual no es correcta.');
        }

        $user->password = Hash::make($request->input('contrasena_nueva'));
        $user->save();

        return back()->with('success', '¡Tu contraseña ha sido actualizada con éxito!');
    }

    /**
     * Exporta los datos del usuario e historial de citas en formato JSON descargable.
     */
    public function exportarDatos()
    {
        $user = Auth::user();
        $cliente = Cliente::firstOrCreate(['user_id' => $user?->id]);

        $citas = Cita::with(['servicio', 'trabajador.user', 'pago', 'resena'])
            ->where('cliente_id', $cliente->id)
            ->orderByDesc('fecha')
            ->orderByDesc('hora_inicio')
            ->get();

        $data = [
            'sistema' => 'Oh La Lash by Lorena Ayala Studio',
            'fecha_exportacion' => now()->toDateTimeString(),
            'usuario' => [
                'id' => $user->id,
                'nombre' => $user->name,
                'apellido' => $user->apellido,
                'email' => $user->email,
                'telefono' => $user->telefono,
                'documento' => $user->documento,
                'direccion' => $cliente->direccion,
                'fecha_nacimiento' => $cliente->fecha_nacimiento,
                'fecha_registro' => $user->created_at?->toDateTimeString(),
            ],
            'historial_citas' => $citas->map(function ($c) {
                return [
                    'id' => $c->id,
                    'servicio' => $c->servicio?->nombre ?? 'Servicio',
                    'especialista' => $c->trabajador?->user 
                        ? ($c->trabajador->user->name . ' ' . ($c->trabajador->user->apellido ?? '')) 
                        : 'Lorena Ayala',
                    'fecha' => $c->fecha,
                    'hora' => $c->hora_inicio,
                    'precio' => $c->servicio?->precio ?? 0,
                    'estado' => $c->estado,
                    'pago' => $c->pago?->estado_pago ?? 'pendiente',
                    'resena' => $c->resena ? [
                        'calificacion' => $c->resena->calificacion,
                        'comentario' => $c->resena->comentario,
                        'fecha' => $c->resena->fecha_resena,
                    ] : null,
                ];
            }),
        ];

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, 'mis_datos_lashapp_' . $user->id . '.json', [
            'Content-Type' => 'application/json; charset=utf-8',
        ]);
    }

    /**
     * Desactiva la cuenta del cliente.
     */
    public function eliminarCuenta(Request $request)
    {
        $user = Auth::user();
        $user->estado = 'inactivo';
        $user->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Tu cuenta ha sido desactivada correctamente.');
    }
}
