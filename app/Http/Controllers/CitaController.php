<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Horario;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CitaController extends Controller
{
    /**
     * Muestra la vista de gestión de citas con datos reales de la base de datos.
     */
    public function index()
    {
        $user = auth()->user();

        // Si el usuario es un Cliente (role_id 3), mostrar la vista de agendar citas
        if ($user && $user->role_id === 3) {
            $servicios = Servicio::where('estado', 'activo')->get();
            return view('Clientes.agendarcitas', compact('servicios'));
        }

        // Si el usuario es Trabajador (role_id 2), mostrar la vista de citas_trabajador
        if ($user && $user->role_id === 2) {
            $trabajador = $user->trabajador ?? Trabajador::where('user_id', $user->id)->first();
            $id_trabajador = $trabajador?->id ?? 0;

            $hoy = Carbon::today()->format('Y-m-d');
            $inicioSemana = Carbon::now()->startOfWeek()->format('Y-m-d');
            $finSemana = Carbon::now()->endOfWeek()->format('Y-m-d');

            $citasBase = Cita::query();
            if ($id_trabajador > 0) {
                $citasBase->where('trabajador_id', $id_trabajador);
            }

            $citasHoy = (clone $citasBase)->whereDate('fecha', $hoy)->count();
            $citasSemana = (clone $citasBase)->whereBetween('fecha', [$inicioSemana, $finSemana])->count();
            $citasComplet = (clone $citasBase)->whereIn('estado', ['finalizado', 'completado', 'completada', 'realizada'])->count();
            $citasCancelad = (clone $citasBase)->whereIn('estado', ['cancelado', 'cancelada'])->count();

            $proximasCitasModel = (clone $citasBase)
                ->with(['cliente.user', 'trabajador.user', 'servicio'])
                ->orderBy('fecha', 'asc')
                ->orderBy('hora_inicio', 'asc')
                ->get();

            if ($proximasCitasModel->isEmpty()) {
                $proximasCitasModel = Cita::with(['cliente.user', 'trabajador.user', 'servicio'])
                    ->orderBy('fecha', 'asc')
                    ->orderBy('hora_inicio', 'asc')
                    ->get();
            }

            $estadoMap = [
                'pendiente' => 'Pendiente',
                'confirmado' => 'Confirmada',
                'confirmada' => 'Confirmada',
                'cancelado' => 'Cancelada',
                'cancelada' => 'Cancelada',
                'finalizado' => 'Completada',
                'completado' => 'Completada',
                'completada' => 'Completada',
                'realizada' => 'Completada',
            ];

            $proximasCitas = $proximasCitasModel->map(function ($cita) use ($estadoMap) {
                $clienteNombre = $cita->cliente?->user
                    ? trim($cita->cliente->user->name . ' ' . ($cita->cliente->user->apellido ?? ''))
                    : 'Cliente';

                $trabajadorNombre = $cita->trabajador?->user
                    ? trim($cita->trabajador->user->name . ' ' . ($cita->trabajador->user->apellido ?? ''))
                    : 'Profesional';

                return [
                    'id_cita' => $cita->id,
                    'cliente' => $clienteNombre,
                    'servicio' => $cita->servicio?->nombre ?? 'Servicio',
                    'hora_inicio' => $cita->hora_inicio,
                    'fecha_cita' => $cita->fecha ? $cita->fecha->format('Y-m-d') : date('Y-m-d'),
                    'profesional' => $trabajadorNombre,
                    'estado' => $estadoMap[strtolower($cita->estado)] ?? ucfirst($cita->estado),
                ];
            })->toArray();

            $listaClientes = Cliente::with('user')->get()->map(function ($c) {
                return [
                    'id_cliente' => $c->id,
                    'nombre_completo' => $c->user ? trim($c->user->name . ' ' . $c->user->apellido) : 'Cliente #' . $c->id,
                ];
            })->toArray();

            $listaServicios = Servicio::where('estado', 'activo')->get()->map(function ($s) {
                return [
                    'id_servicio' => $s->id,
                    'nombre' => $s->nombre,
                    'precio' => $s->precio,
                ];
            })->toArray();

            $listaTrabajadores = Trabajador::where('estado', 'activo')->with('user')->get()->map(function ($t) {
                return [
                    'id_trabajador' => $t->id,
                    'nombre_completo' => $t->user ? trim($t->user->name . ' ' . $t->user->apellido) : 'Profesional #' . $t->id,
                ];
            })->toArray();

            return view('trabajador.citas.citas_trabajador', compact(
                'citasHoy',
                'citasSemana',
                'citasComplet',
                'citasCancelad',
                'proximasCitas',
                'listaClientes',
                'listaServicios',
                'listaTrabajadores',
                'id_trabajador',
                'hoy'
            ));
        }

        $hoy = Carbon::today()->format('Y-m-d');
        $inicioSemana = Carbon::now()->startOfWeek();
        $finSemana = Carbon::now()->endOfWeek();
        $inicioMes = Carbon::now()->startOfMonth();

        // Métricas
        $citasHoy = Cita::whereDate('fecha', $hoy)->count();
        $citasSemana = Cita::whereBetween('fecha', [$inicioSemana->format('Y-m-d'), $finSemana->format('Y-m-d')])->count();
        $citasComplet = Cita::where('estado', 'finalizado')
            ->where('fecha', '>=', $inicioMes->format('Y-m-d'))
            ->count();
        $citasCancelad = Cita::where('estado', 'cancelado')
            ->where('fecha', '>=', $inicioMes->format('Y-m-d'))
            ->count();

        // Próximas citas ordenadas por fecha y hora
        $citasQuery = Cita::with(['cliente.user', 'trabajador.user', 'servicio'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->get();

        $proximasCitas = $citasQuery->map(function ($cita) {
            $clienteNombre = $cita->cliente?->user
                ? trim($cita->cliente->user->name . ' ' . $cita->cliente->user->apellido)
                : 'Cliente';

            $trabajadorNombre = $cita->trabajador?->user
                ? trim($cita->trabajador->user->name . ' ' . $cita->trabajador->user->apellido)
                : 'Profesional';

            // Mapear estado a texto capitalizado para la vista
            $estadoMap = [
                'pendiente' => 'Pendiente',
                'confirmado' => 'Confirmada',
                'cancelado' => 'Cancelada',
                'finalizado' => 'Completada',
            ];
            $estadoTexto = $estadoMap[$cita->estado] ?? ucfirst($cita->estado);

            return [
                'id_cita' => $cita->id,
                'cliente' => $clienteNombre,
                'servicio' => $cita->servicio?->nombre ?? 'Servicio',
                'hora_inicio' => $cita->hora_inicio,
                'fecha_cita' => $cita->fecha->format('Y-m-d'),
                'profesional' => $trabajadorNombre,
                'estado' => $estadoTexto,
            ];
        });

        // Listados para los selects del modal de nueva cita
        // CLIENTES: Únicamente usuarios con rol de cliente (role_id = 3)
        $listaClientes = Cliente::whereHas('user', function ($q) {
            $q->where('role_id', 3);
        })->with('user')->get()->map(function ($c) {
            return [
                'id_cliente' => $c->id,
                'nombre_completo' => $c->user ? trim($c->user->name . ' ' . $c->user->apellido) : 'Cliente #' . $c->id,
                'correo' => $c->user?->email ?? '',
            ];
        });

        $listaServicios = Servicio::where('estado', 'activo')->get()->map(function ($s) {
            // Manejar duracion_minuto ya sea formato time '01:00:00' o entero
            $duracion = $s->duracion_minuto;
            if (is_string($duracion) && str_contains($duracion, ':')) {
                $partes = explode(':', $duracion);
                $minutos = ((int)$partes[0] * 60) + (int)$partes[1];
            } else {
                $minutos = (int)$duracion;
            }

            return [
                'id_servicio' => $s->id,
                'nombre' => $s->nombre,
                'precio' => $s->precio,
                'duracion_minutos' => $minutos > 0 ? $minutos : 45,
            ];
        });

        // PROFESIONALES: Usuarios con rol de trabajador (role_id = 2) o administrador (role_id = 1)
        $listaTrabajadores = Trabajador::where('estado', 'activo')
            ->whereHas('user', function ($q) {
                $q->whereIn('role_id', [1, 2]);
            })
            ->with(['user.role'])->get()->map(function ($t) {
                $rolLabel = $t->user?->role_id == 1 ? 'Administrador' : ($t->especialidad ?: 'Lashista');
                return [
                    'id_trabajador' => $t->id,
                    'nombre_completo' => $t->user ? trim($t->user->name . ' ' . $t->user->apellido) : 'Profesional #' . $t->id,
                    'especialidad' => $rolLabel,
                ];
            });

        return view('admin.citas.citas', compact(
            'citasHoy',
            'citasSemana',
            'citasComplet',
            'citasCancelad',
            'proximasCitas',
            'listaClientes',
            'listaServicios',
            'listaTrabajadores',
            'hoy'
        ));
    }

    /**
     * Guarda una nueva cita en la base de datos (con registro de pago).
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Si es cliente, asegurar que el cliente existe en la tabla clientes
        if ($user && $user->role_id === 3) {
            $cliente = Cliente::firstOrCreate(['user_id' => $user->id]);
            $request->merge(['id_cliente' => $cliente->id]);
        }

        // Si no se especificó trabajador o se seleccionó "Cualquier profesional", asignar el primer trabajador activo
        if (empty($request->id_trabajador) || !Trabajador::where('id', $request->id_trabajador)->exists()) {
            $primerTrabajador = Trabajador::where('estado', 'activo')->first();
            $request->merge(['id_trabajador' => $primerTrabajador?->id ?? 1]);
        }

        $request->validate([
            'id_cliente' => 'required|exists:clientes,id',
            'id_servicio' => 'required|exists:servicios,id',
            'id_trabajador' => 'required|exists:trabajadores,id',
            'fecha_cita' => 'required|date',
            'hora_inicio' => 'required',
            'estado' => 'nullable|string',
            'metodo_pago' => 'nullable|string',
            'observaciones' => 'nullable|string|max:500',
        ]);

        $servicio = Servicio::findOrFail($request->id_servicio);

        // Calcular hora_fin sumando la duración del servicio
        $duracion = $servicio->duracion_minuto;
        $minutos = 45;
        if (is_string($duracion) && str_contains($duracion, ':')) {
            $partes = explode(':', $duracion);
            $minutos = ((int)$partes[0] * 60) + (int)$partes[1];
        } elseif (is_numeric($duracion) && $duracion > 0) {
            $minutos = (int)$duracion;
        }

        $horaInicio = Carbon::parse($request->hora_inicio);
        $horaFin = (clone $horaInicio)->addMinutes($minutos)->format('H:i:s');

        // Mapear estado al enum de la base de datos ('pendiente','confirmado','cancelado','finalizado')
        $estadoInput = strtolower($request->estado ?? 'pendiente');
        $estadoDb = match ($estadoInput) {
            'confirmada', 'confirmado' => 'confirmado',
            'cancelada', 'cancelado' => 'cancelado',
            'completada', 'finalizado' => 'finalizado',
            default => 'pendiente',
        };

        // Buscar o crear un registro en horarios para satisfacer la clave foránea requerida
        $horario = Horario::firstOrCreate(
            [
                'trabajador_id' => $request->id_trabajador,
                'fecha' => $request->fecha_cita,
                'hora_inicio' => $horaInicio->format('H:i:s'),
            ],
            [
                'hora_fin' => $horaFin,
                'estado' => 'activo',
            ]
        );

        $cita = Cita::create([
            'cliente_id' => $request->id_cliente,
            'trabajador_id' => $request->id_trabajador,
            'servicio_id' => $request->id_servicio,
            'horario_id' => $horario->id,
            'fecha' => $request->fecha_cita,
            'hora_inicio' => $horaInicio->format('H:i:s'),
            'hora_fin' => $horaFin,
            'estado' => $estadoDb,
            'observaciones' => $request->observaciones,
            'fecha_creacion' => now(),
        ]);

        // Registrar Pago en la base de datos
        $metodo = $request->metodo_pago ?? 'tarjeta';
        $estadoPago = in_array(strtolower($metodo), ['tarjeta', 'pse', 'digital']) ? 'completado' : 'pendiente';
        $detallePago = 'Pago mediante pasarela (' . ucfirst($metodo) . ')';
        if ($request->cuotas_pago) {
            $detallePago .= ' - ' . $request->cuotas_pago . ' cuota(s)';
        }

        Pago::create([
            'cita_id' => $cita->id,
            'metodo_pago' => ucfirst($metodo),
            'monto' => $servicio->precio,
            'estado' => $estadoPago,
            'fecha_pago' => now(),
            'observaciones' => $detallePago,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Tu cita para "' . $servicio->nombre . '" ha sido agendada con éxito y tu pago procesado!',
                'cita' => $cita,
            ]);
        }

        if ($user && $user->role_id === 3) {
            return redirect()
                ->route('cliente.dashboard')
                ->with('success', '¡Tu cita para "' . $servicio->nombre . '" ha sido agendada con éxito y tu pago procesado!');
        }

        return redirect()
            ->route('citas.index')
            ->with('success', '¡Cita agendada correctamente!');
    }

    /**
     * Cancela una cita cambiando su estado a 'cancelado'.
     */
    public function cancelar($id)
    {
        $cita = Cita::findOrFail($id);
        $user = auth()->user();

        // Si es cliente, verificar que la cita le pertenezca
        if ($user && $user->role_id === 3) {
            $cliente = Cliente::where('user_id', $user->id)->first();
            if (!$cliente || $cita->cliente_id !== $cliente->id) {
                abort(403, 'No tienes permiso para cancelar esta cita.');
            }
        }

        $cita->estado = 'cancelado';
        $cita->save();

        if ($user && $user->role_id === 3) {
            return redirect()
                ->back()
                ->with('success', 'La cita fue cancelada exitosamente.');
        }

        return redirect()
            ->route('citas.index')
            ->with('success', 'La cita fue cancelada exitosamente.');
    }
}
