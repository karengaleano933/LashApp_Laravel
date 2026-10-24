<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacion;
use App\Models\Cita;
use Illuminate\Support\Facades\Auth;

class NotificacionController extends Controller
{
    /**
     * Get recent notifications as JSON.
     */
    public function index()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['count' => 0, 'notificaciones' => []]);
        }

        // Si no existen notificaciones en el sistema y existe al menos una cita, creamos notificaciones de ejemplo
        if (Notificacion::count() === 0) {
            $cita = Cita::first();
            if ($cita) {
                try {
                    Notificacion::create([
                        'user_id' => $user->id,
                        'cita_id' => $cita->id,
                        'tipo' => 'agendada',
                        'titulo' => 'Nueva Cita Agendada',
                        'mensaje' => 'Valentina Gómez agendó Extensión de Pestañas Volumen Ruso para mañana a las 10:00 AM.',
                        'leida' => false,
                        'fecha_envio' => now()->subMinutes(15),
                    ]);

                    Notificacion::create([
                        'user_id' => $user->id,
                        'cita_id' => $cita->id,
                        'tipo' => 'info',
                        'titulo' => 'Comprobante de Pago Subido',
                        'mensaje' => 'Se ha registrado un comprobante Nequi por $120.000 pendiente de revisión.',
                        'leida' => false,
                        'fecha_envio' => now()->subHours(1),
                    ]);

                    Notificacion::create([
                        'user_id' => $user->id,
                        'cita_id' => $cita->id,
                        'tipo' => 'agendada',
                        'titulo' => 'Nueva Reseña 5★ Recibida',
                        'mensaje' => 'Camila Torres dejó una valoración de 5 estrellas: "¡Excelente atención!".',
                        'leida' => true,
                        'fecha_envio' => now()->subHours(3),
                    ]);
                } catch (\Throwable $e) {
                    // Silenciar para no bloquear la interfaz en caso de inconsistencia de datos
                }
            }
        }

        $query = Notificacion::query();
        if ($user->role_id === 3) {
            // Clientes: ver sus propias notificaciones
            $query->where('user_id', $user->id);
        }

        $notificaciones = $query->orderByDesc('fecha_envio')
            ->take(10)
            ->get()
            ->map(function ($n) use ($user) {
                $enlace = route('citas.index');
                if ($user->role_id === 3) {
                    $enlace = route('cliente.citas');
                } elseif ($user->role_id === 2) {
                    $enlace = route('trabajador.citas');
                } elseif ($n->tipo === 'info') {
                    $enlace = route('pagos.index');
                }

                return [
                    'id' => $n->id,
                    'tipo' => $n->tipo,
                    'titulo' => $n->titulo,
                    'mensaje' => $n->mensaje,
                    'leida' => (bool)$n->leida,
                    'tiempo' => $n->fecha_envio ? $n->fecha_envio->diffForHumans() : 'Hace un momento',
                    'enlace' => $enlace,
                ];
            });

        $unreadCount = ($user->role_id === 3)
            ? Notificacion::where('user_id', $user->id)->where('leida', false)->count()
            : Notificacion::where('leida', false)->count();

        return response()->json([
            'count' => $unreadCount,
            'notificaciones' => $notificaciones,
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function marcarTodasLeidas()
    {
        $user = Auth::user();
        if ($user) {
            if ($user->role_id === 3) {
                Notificacion::where('user_id', $user->id)->where('leida', false)->update(['leida' => true]);
            } else {
                Notificacion::where('leida', false)->update(['leida' => true]);
            }
        }

        return response()->json(['success' => true, 'count' => 0]);
    }
}
