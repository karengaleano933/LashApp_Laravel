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

            $tieneCitasAsignadas = ($id_trabajador > 0) && Cita::where('trabajador_id', $id_trabajador)->exists();

            $citasBase = Cita::query();
            if ($tieneCitasAsignadas) {
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
     * Consulta los horarios disponibles para una fecha, servicio y especialista determinados.
     * Retorna una lista con el estado (disponible / ocupado) de cada franja horaria.
     */
    public function horasDisponibles(Request $request)
    {
        $fecha = $request->query('fecha', Carbon::today()->format('Y-m-d'));
        $idTrabajador = $request->query('id_trabajador');
        $idServicio = $request->query('id_servicio');

        // Determinar duración del servicio solicitado
        $minutos = 45;
        if (!empty($idServicio)) {
            $servicio = Servicio::find($idServicio);
            if ($servicio) {
                $duracion = $servicio->duracion_minuto;
                if (is_string($duracion) && str_contains($duracion, ':')) {
                    $partes = explode(':', $duracion);
                    $minutos = ((int)$partes[0] * 60) + (int)$partes[1];
                } elseif (is_numeric($duracion) && $duracion > 0) {
                    $minutos = (int)$duracion;
                }
            }
        }
        if ($minutos <= 0) $minutos = 45;

        // Franjas horarias operativas estándar del estudio (8:00 AM a 5:00 PM)
        $franjas = [
            '08:00' => '08:00 am',
            '09:00' => '09:00 am',
            '10:00' => '10:00 am',
            '11:00' => '11:00 am',
            '12:00' => '12:00 pm',
            '13:00' => '01:00 pm',
            '14:00' => '02:00 pm',
            '15:00' => '03:00 pm',
            '16:00' => '04:00 pm',
            '17:00' => '05:00 pm',
        ];

        // Obtener citas activas para la fecha (excluyendo canceladas)
        $citasDelDia = Cita::whereDate('fecha', $fecha)
            ->whereNotIn('estado', ['cancelado', 'cancelada'])
            ->get(['id', 'trabajador_id', 'hora_inicio', 'hora_fin', 'estado']);

        // Trabajadores activos
        $trabajadoresActivos = Trabajador::where('estado', 'activo')->get();
        if ($trabajadoresActivos->isEmpty()) {
            $trabajadoresActivos = Trabajador::all();
        }

        $ahora = Carbon::now();
        $esFechaPasada = Carbon::parse($fecha)->endOfDay()->isPast();
        $esHoy = Carbon::parse($fecha)->isToday();

        $resultadoHorarios = [];

        foreach ($franjas as $hora24 => $hora12) {
            $slotInicio = Carbon::parse($fecha . ' ' . $hora24 . ':00');
            $slotFin = (clone $slotInicio)->addMinutes($minutos);
            $slotIniStr = $slotInicio->format('H:i:s');
            $slotFinStr = $slotFin->format('H:i:s');

            // 1. Si la fecha es pasada o si es hoy y la hora ya pasó
            if ($esFechaPasada || ($esHoy && $slotInicio->lessThanOrEqualTo($ahora))) {
                $resultadoHorarios[] = [
                    'hora_24' => $hora24,
                    'hora_12' => $hora12,
                    'disponible' => false,
                    'motivo' => 'pasado',
                ];
                continue;
            }

            // 2. Si se eligió un especialista específico
            if (!empty($idTrabajador) && $idTrabajador > 0 && Trabajador::where('id', $idTrabajador)->exists()) {
                $ocupado = $citasDelDia->first(function ($c) use ($idTrabajador, $slotIniStr, $slotFinStr) {
                    if ($c->trabajador_id != $idTrabajador) return false;
                    $citaIni = $c->hora_inicio;
                    $citaFin = ($c->hora_fin && $c->hora_fin > $c->hora_inicio)
                        ? $c->hora_fin
                        : Carbon::parse($citaIni)->addMinutes(45)->format('H:i:s');
                    return ($citaIni < $slotFinStr && $citaFin > $slotIniStr);
                });

                $resultadoHorarios[] = [
                    'hora_24' => $hora24,
                    'hora_12' => $hora12,
                    'disponible' => !$ocupado,
                    'motivo' => $ocupado ? 'ocupado' : null,
                ];
            } else {
                // 3. "Cualquier Profesional": disponible si AL MENOS UN trabajador activo está libre
                $algunLibre = false;
                foreach ($trabajadoresActivos as $t) {
                    $ocupadoTrab = $citasDelDia->first(function ($c) use ($t, $slotIniStr, $slotFinStr) {
                        if ($c->trabajador_id != $t->id) return false;
                        $citaIni = $c->hora_inicio;
                        $citaFin = ($c->hora_fin && $c->hora_fin > $c->hora_inicio)
                            ? $c->hora_fin
                            : Carbon::parse($citaIni)->addMinutes(45)->format('H:i:s');
                        return ($citaIni < $slotFinStr && $citaFin > $slotIniStr);
                    });

                    if (!$ocupadoTrab) {
                        $algunLibre = true;
                        break;
                    }
                }

                $resultadoHorarios[] = [
                    'hora_24' => $hora24,
                    'hora_12' => $hora12,
                    'disponible' => $algunLibre,
                    'motivo' => $algunLibre ? null : 'ocupado',
                ];
            }
        }

        return response()->json([
            'success' => true,
            'fecha' => $fecha,
            'duracion_minutos' => $minutos,
            'horarios' => $resultadoHorarios,
        ]);
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

        $request->validate([
            'id_cliente' => 'required|exists:clientes,id',
            'id_servicio' => 'required|exists:servicios,id',
            'id_trabajador' => 'nullable',
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
        if ($minutos <= 0) $minutos = 45;

        $horaInicio = Carbon::parse($request->hora_inicio);
        $horaFin = (clone $horaInicio)->addMinutes($minutos)->format('H:i:s');
        $horaInicioStr = $horaInicio->format('H:i:s');
        $fechaCita = $request->fecha_cita;

        // Validar que la fecha y hora no hayan pasado
        $fechaHoraInicio = Carbon::parse($fechaCita . ' ' . $horaInicioStr);
        if ($fechaHoraInicio->isPast()) {
            $errorMsg = 'No puedes agendar una cita en una fecha u hora que ya ha pasado.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return redirect()->back()->withInput()->withErrors(['hora_inicio' => $errorMsg])->with('error', $errorMsg);
        }

        // VALIDACIÓN DE DISPONIBILIDAD: Que la hora no esté reservada por nadie más
        $idTrabajador = $request->id_trabajador;
        $trabajadorAsignadoId = null;

        if (!empty($idTrabajador) && $idTrabajador > 0 && Trabajador::where('id', $idTrabajador)->exists()) {
            // Verificar si el especialista seleccionado ya tiene una cita en ese rango
            $solapada = Cita::where('trabajador_id', $idTrabajador)
                ->whereDate('fecha', $fechaCita)
                ->whereNotIn('estado', ['cancelado', 'cancelada'])
                ->where(function ($q) use ($horaInicioStr, $horaFin) {
                    $q->where('hora_inicio', '<', $horaFin)
                      ->where('hora_fin', '>', $horaInicioStr);
                })
                ->exists();

            if ($solapada) {
                $errorMsg = 'La hora seleccionada (' . $horaInicio->format('g:i A') . ') ya no está disponible para esta especialista porque fue reservada previamente. Por favor selecciona otro horario.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['hora_inicio' => $errorMsg])->with('error', $errorMsg);
            }

            $trabajadorAsignadoId = $idTrabajador;
        } else {
            // "Cualquier Profesional": buscar qué especialista activo está libre en ese horario
            $trabajadoresActivos = Trabajador::where('estado', 'activo')->get();
            if ($trabajadoresActivos->isEmpty()) {
                $trabajadoresActivos = Trabajador::all();
            }

            foreach ($trabajadoresActivos as $t) {
                $ocupado = Cita::where('trabajador_id', $t->id)
                    ->whereDate('fecha', $fechaCita)
                    ->whereNotIn('estado', ['cancelado', 'cancelada'])
                    ->where(function ($q) use ($horaInicioStr, $horaFin) {
                        $q->where('hora_inicio', '<', $horaFin)
                          ->where('hora_fin', '>', $horaInicioStr);
                    })
                    ->exists();

                if (!$ocupado) {
                    $trabajadorAsignadoId = $t->id;
                    break;
                }
            }

            if (!$trabajadorAsignadoId) {
                $errorMsg = 'La hora seleccionada (' . $horaInicio->format('g:i A') . ') ya no está disponible ya que todos nuestros profesionales se encuentran ocupados en ese horario. Por favor selecciona otro horario.';
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['success' => false, 'message' => $errorMsg], 422);
                }
                return redirect()->back()->withInput()->withErrors(['hora_inicio' => $errorMsg])->with('error', $errorMsg);
            }
        }

        $request->merge(['id_trabajador' => $trabajadorAsignadoId]);

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

        // Generar número de ticket consecutivo único tipo POS
        $ultimoPagoId = Pago::max('id') ?? 0;
        $numeroTicket = 'POS-' . date('Y') . '-' . str_pad($ultimoPagoId + 1, 5, '0', STR_PAD_LEFT);

        $datosPago = [
            'cita_id' => $cita->id,
            'metodo_pago' => ucfirst($metodo),
            'monto' => $servicio->precio,
            'estado' => $estadoPago,
            'fecha_pago' => now(),
            'observaciones' => $detallePago,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('pagos', 'numero_ticket')) {
            $datosPago['numero_ticket'] = $numeroTicket;
        }

        $pago = Pago::create($datosPago);

        // Notificar a los administradores del nuevo pago y factura POS
        $clienteUser = $cita->cliente?->user ?? $user;
        $clienteNombre = $clienteUser ? trim($clienteUser->name . ' ' . ($clienteUser->apellido ?? '')) : 'Cliente';
        
        $admins = \App\Models\User::where('role_id', 1)->get();
        foreach ($admins as $admin) {
            \App\Models\Notificacion::create([
                'user_id' => $admin->id,
                'cita_id' => $cita->id,
                'tipo' => 'pago',
                'titulo' => 'Nueva Factura POS #' . $pago->numero_ticket,
                'mensaje' => 'El cliente ' . $clienteNombre . ' agendó "' . $servicio->nombre . '" y generó el ticket POS #' . $pago->numero_ticket . ' por valor de $' . number_format($pago->monto, 0, ',', '.') . ' (' . $pago->metodo_pago . ').',
                'leida' => false,
                'fecha_envio' => now(),
            ]);
        }

        // Notificar también al cliente con su comprobante
        if ($clienteUser) {
            \App\Models\Notificacion::create([
                'user_id' => $clienteUser->id,
                'cita_id' => $cita->id,
                'tipo' => 'pago',
                'titulo' => 'Ticket POS #' . $pago->numero_ticket . ' Generado',
                'mensaje' => '¡Tu reserva para ' . $servicio->nombre . ' está confirmada! Tu comprobante tipo POS #' . $pago->numero_ticket . ' ya está disponible para descargar.',
                'leida' => false,
                'fecha_envio' => now(),
            ]);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Tu cita para "' . $servicio->nombre . '" ha sido agendada con éxito y se generó tu Factura POS #' . $pago->numero_ticket . '!',
                'cita' => $cita,
                'pago' => $pago,
                'ticket_numero' => $pago->numero_ticket,
                'ticket_url' => route('ticket.ver', $cita->id),
            ]);
        }

        if ($user && $user->role_id === 3) {
            return redirect()
                ->route('cliente.citas')
                ->with('success', '¡Tu cita para "' . $servicio->nombre . '" ha sido agendada con éxito!')
                ->with('ticket_url', route('ticket.ver', $cita->id))
                ->with('ticket_numero', $pago->numero_ticket);
        }

        return redirect()
            ->route('citas.index')
            ->with('success', '¡Cita agendada correctamente! Ticket #' . $pago->numero_ticket);
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
            ->back()
            ->with('success', 'La cita fue cancelada exitosamente.');
    }

    /**
     * Marca una cita como realizada/finalizada ('finalizado').
     */
    public function completar($id)
    {
        $cita = Cita::findOrFail($id);
        $user = auth()->user();

        // Validar permisos si es trabajador o cliente
        if ($user && $user->role_id === 2) {
            $trabajador = $user->trabajador ?? Trabajador::where('user_id', $user->id)->first();
            if ($trabajador && $cita->trabajador_id !== $trabajador->id) {
                abort(403, 'No tienes permiso para gestionar esta cita.');
            }
        } elseif ($user && $user->role_id === 3) {
            abort(403, 'Los clientes no pueden marcar citas como realizadas.');
        }

        $cita->estado = 'finalizado';
        $cita->save();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡La cita ha sido marcada como realizada exitosamente!'
            ]);
        }

        return redirect()
            ->back()
            ->with('success', '¡La cita ha sido marcada como realizada exitosamente!');
    }
}
