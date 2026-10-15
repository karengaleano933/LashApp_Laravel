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

        // Si no existen notificaciones en el sistema, creamos un par de ejemplo
        if (Notificacion::count() === 0) {
            $cita = Cita::first();
            $citaId = $cita ? $cita->id : 1;

            Notificacion::create([
                'user_id' => $user->id,
                'cita_id' => $citaId,
                'tipo' => 'agendada',
                'titulo' => 'Nueva Cita Agendada',
                'mensaje' => 'Valentina Gómez agendó Extensión de Pestañas Volumen Ruso para mañana a las 10:00 AM.',
                'leida' => false,
                'fecha_envio' => now()->subMinutes(15),
            ]);

            Notificacion::create([
                'user_id' => $user->id,
                'cita_id' => $citaId,
                'tipo' => 'info',
                'titulo' => 'Comprobante de Pago Subido',
                'mensaje' => 'Se ha registrado un comprobante Nequi por $120.000 pendiente de revisión.',
                'leida' => false,
                'fecha_envio' => now()->subHours(1),
            ]);

            Notificacion::create([
                'user_id' => $user->id,
                'cita_id' => $citaId,
                'tipo' => 'agendada',
                'titulo' => 'Nueva Reseña 5★ Recibida',
                'mensaje' => 'Camila Torres dejó una valoración de 5 estrellas: "¡Excelente atención!".',
                'leida' => true,
                'fecha_envio' => now()->subHours(3),
            ]);
        }

        $notificaciones = Notificacion::orderByDesc('fecha_envio')
            ->take(10)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'tipo' => $n->tipo,
                    'titulo' => $n->titulo,
                    'mensaje' => $n->mensaje,
                    'leida' => (bool)$n->leida,
                    'tiempo' => $n->fecha_envio ? $n->fecha_envio->diffForHumans() : 'Hace un momento',
                    'enlace' => $n->tipo === 'info' ? route('pagos.index') : route('citas.index'),
                ];
            });

        $unreadCount = Notificacion::where('leida', false)->count();

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
        Notificacion::where('leida', false)->update(['leida' => true]);

        return response()->json(['success' => true, 'count' => 0]);
    }
}
