<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Trabajador;
use App\Models\Servicio;
use App\Models\Horario;
use App\Models\Cita;
use App\Models\Pago;
use App\Models\Resena;
use App\Models\Configuracion;

class LashAppSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar Configuración
        Configuracion::getInstancia();

        // 2. Clientes y Trabajadores existentes
        $clientes = Cliente::with('user')->get();
        $trabajador = Trabajador::first();
        $servicios = Servicio::all();
        $horario = Horario::first();

        if ($clientes->isEmpty() || !$trabajador || $servicios->isEmpty() || !$horario) {
            return;
        }

        // 3. Crear citas adicionales si hay pocas
        if (Cita::count() < 4) {
            $serviciosArray = $servicios->all();
            $estados = ['finalizado', 'finalizado', 'confirmado', 'pendiente'];

            foreach ($clientes as $idx => $cliente) {
                $serv = $serviciosArray[$idx % count($serviciosArray)];
                Cita::create([
                    'cliente_id' => $cliente->id,
                    'trabajador_id' => $trabajador->id,
                    'servicio_id' => $serv->id,
                    'horario_id' => $horario->id,
                    'fecha' => now()->subDays($idx * 2 + 1)->toDateString(),
                    'hora_inicio' => '10:00:00',
                    'hora_fin' => '11:30:00',
                    'estado' => $estados[$idx % count($estados)],
                    'observaciones' => 'Cita de control y aplicación ' . $serv->nombre,
                    'fecha_creacion' => now()->subDays($idx * 3 + 1),
                ]);
            }
        }

        $citas = Cita::with(['cliente.user', 'servicio'])->get();

        // 4. Crear pagos si la tabla pagos está vacía
        if (Pago::count() === 0) {
            $metodos = ['Transferencia', 'Efectivo', 'Transferencia', 'Efectivo'];
            $estados = ['completado', 'completado', 'completado', 'pendiente'];
            $referencias = ['Nequi #482910', 'Efectivo en caja', 'Nequi #771239', 'Pendiente comprobante'];

            foreach ($citas as $idx => $cita) {
                $precio = $cita->servicio?->precio ?? 120000;
                Pago::create([
                    'cita_id' => $cita->id,
                    'metodo_pago' => $metodos[$idx % count($metodos)],
                    'monto' => $precio,
                    'estado' => $estados[$idx % count($estados)],
                    'fecha_pago' => now()->subDays($idx * 2),
                    'observaciones' => $referencias[$idx % count($referencias)],
                ]);
            }
        }

        // 5. Crear reseñas si la tabla resenas está vacía
        if (Resena::count() === 0) {
            $comentarios = [
                '¡Me encantaron mis pestañas! La atención de Lorena fue súper profesional y el resultado quedó muy natural y duradero. 100% recomendada.',
                'Excelente servicio, el estudio es impecable y la técnica de volumen ruso es la mejor de la ciudad. Volveré sin duda.',
                'Muy buena atención al detalle, me explicaron todo el cuidado posterior. Mis pestañas quedaron hermosas.',
            ];
            $calificaciones = [5, 5, 4];

            $citasFinalizadas = $citas->where('estado', 'finalizado')->values();
            if ($citasFinalizadas->isEmpty()) {
                $citasFinalizadas = $citas;
            }

            foreach ($citasFinalizadas->take(3) as $idx => $cita) {
                Resena::create([
                    'cliente_id' => $cita->cliente_id,
                    'cita_id' => $cita->id,
                    'calificacion' => $calificaciones[$idx % count($calificaciones)],
                    'comentario' => $comentarios[$idx % count($comentarios)],
                    'fecha_resena' => now()->subDays($idx * 3 + 1),
                ]);
            }
        }
    }
}
