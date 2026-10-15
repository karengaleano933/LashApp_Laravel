<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Horario;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Trabajador;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Muestra el resumen general del dashboard administrativo o de cliente según su rol.
     */
    public function index()
    {
        $user = Auth::user();

        // Si es cliente, mostrar el dashboard de cliente
        if ($user && $user->role_id !== 1) {
            return $this->dashboardCliente();
        }

        // Métricas principales de Admin
        $totalUsuarios = User::count();
        $totalClientes = Cliente::count();
        $totalTrabajadores = Trabajador::where('estado', 'activo')->count();
        $totalCitas = Cita::count();

        // Resumen del negocio
        $citasPendientes = Cita::where('estado', 'pendiente')->count();
        $totalServicios = Servicio::where('estado', 'activo')->count();
        $totalResenas = Resena::count();

        // Próximas citas (las 5 más recientes)
        $citasRecientes = Cita::with(['cliente.user', 'trabajador.user', 'servicio'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->take(5)
            ->get();

        $estadoMap = [
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmada',
            'cancelado' => 'Cancelada',
            'finalizado' => 'Completada',
        ];

        $proximasCitas = $citasRecientes->map(function ($cita) use ($estadoMap) {
            $clienteNombre = $cita->cliente?->user
                ? trim($cita->cliente->user->name . ' ' . $cita->cliente->user->apellido)
                : 'Cliente';

            $trabajadorNombre = $cita->trabajador?->user
                ? trim($cita->trabajador->user->name . ' ' . $cita->trabajador->user->apellido)
                : 'Profesional';

            return [
                'id' => $cita->id,
                'cliente' => $clienteNombre,
                'servicio' => $cita->servicio?->nombre ?? 'Servicio',
                'profesional' => $trabajadorNombre,
                'fecha_cita' => $cita->fecha ? $cita->fecha->format('Y-m-d') : now()->format('Y-m-d'),
                'hora_inicio' => $cita->hora_inicio,
                'estado' => $estadoMap[$cita->estado] ?? ucfirst($cita->estado),
            ];
        })->toArray();

        return view('admin.dashboard', compact(
            'totalUsuarios',
            'totalClientes',
            'totalTrabajadores',
            'totalCitas',
            'citasPendientes',
            'totalServicios',
            'totalResenas',
            'proximasCitas'
        ));
    }

    /**
     * Dashboard específico para clientes conectado 100% a la Base de Datos.
     */
    public function dashboardCliente()
    {
        $user = Auth::user();
        $cliente = Cliente::firstOrCreate(['user_id' => $user?->id]);
        $clienteId = $cliente ? $cliente->id : null;

        // Citas del cliente
        $citasQuery = Cita::query();
        if ($clienteId) {
            $citasQuery->where('cliente_id', $clienteId);
        }

        $totalCitas = $citasQuery->count();
        $citasPendientes = (clone $citasQuery)->where('estado', 'pendiente')->count();
        $citasCompletadas = (clone $citasQuery)->whereIn('estado', ['confirmado', 'finalizado', 'completado'])->count();

        // Próxima cita del cliente
        $proximaCitaModel = (clone $citasQuery)
            ->with(['servicio.categoria', 'trabajador.user'])
            ->whereDate('fecha', '>=', Carbon::today())
            ->whereIn('estado', ['pendiente', 'confirmado'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora_inicio', 'asc')
            ->first();

        if (!$proximaCitaModel && $clienteId) {
            $proximaCitaModel = (clone $citasQuery)->with(['servicio.categoria', 'trabajador.user'])->latest('fecha')->first();
        }

        $proximaCita = null;
        if ($proximaCitaModel) {
            $img = asset('img/volumen_ruso.jpg');
            if (!empty($proximaCitaModel->servicio?->imagen_url)) {
                $img = filter_var($proximaCitaModel->servicio->imagen_url, FILTER_VALIDATE_URL) 
                    ? $proximaCitaModel->servicio->imagen_url 
                    : asset($proximaCitaModel->servicio->imagen_url);
            }

            $proximaCita = [
                'id' => $proximaCitaModel->id,
                'servicio' => $proximaCitaModel->servicio?->nombre ?? 'Extensiones de Pestañas',
                'subservicio' => $proximaCitaModel->servicio?->categoria?->nombre ?? 'Pestañas',
                'profesional' => $proximaCitaModel->trabajador?->user 
                    ? trim($proximaCitaModel->trabajador->user->name . ' ' . ($proximaCitaModel->trabajador->user->apellido ?? '')) 
                    : 'Andrea',
                'fecha_cita' => $proximaCitaModel->fecha ? $proximaCitaModel->fecha->format('Y-m-d') : now()->format('Y-m-d'),
                'hora_inicio' => $proximaCitaModel->hora_inicio ?? '10:00:00',
                'estado' => ucfirst($proximaCitaModel->estado),
                'imagen_url' => $img,
            ];
        }

        // Catálogo Destacado real de la Base de Datos
        $serviciosDestacados = Servicio::with('categoria')
            ->where('estado', 'activo')
            ->take(4)
            ->get();

        // Actividad Reciente real de la Base de Datos
        $actividadReciente = [];
        if ($clienteId) {
            $citasRecientes = Cita::with('servicio')
                ->where('cliente_id', $clienteId)
                ->latest('fecha_creacion')
                ->take(3)
                ->get();

            foreach ($citasRecientes as $cr) {
                $actividadReciente[] = [
                    'tipo' => 'cita',
                    'titulo' => 'Cita ' . ucfirst($cr->estado),
                    'desc' => ($cr->servicio?->nombre ?? 'Servicio') . ' - ' . ($cr->fecha ? $cr->fecha->translatedFormat('l d \d\e F') : '') . ', ' . Carbon::parse($cr->hora_inicio)->format('g:i a'),
                    'tiempo' => $cr->fecha_creacion ? Carbon::parse($cr->fecha_creacion)->diffForHumans() : 'Reciente',
                    'icono' => $cr->estado === 'finalizado' ? 'fa-regular fa-circle-check' : 'fa-regular fa-calendar',
                    'color' => $cr->estado === 'finalizado' ? 'green' : 'purple',
                ];
            }

            $resenasRecientes = Resena::where('cliente_id', $clienteId)
                ->latest('fecha_resena')
                ->take(2)
                ->get();

            foreach ($resenasRecientes as $rr) {
                $actividadReciente[] = [
                    'tipo' => 'resena',
                    'titulo' => 'Reseña enviada (' . $rr->calificacion . '★)',
                    'desc' => \Illuminate\Support\Str::limit($rr->comentario, 60),
                    'tiempo' => $rr->fecha_resena ? Carbon::parse($rr->fecha_resena)->diffForHumans() : 'Reciente',
                    'icono' => 'fa-regular fa-star',
                    'color' => 'pink',
                ];
            }
        }

        // Agendas y horarios disponibles reales de la Base de Datos
        $horarios = Horario::with('trabajador.user')->where('estado', 'activo')->get();
        $agendasDisponibles = $horarios->map(function ($h) {
            $trab = $h->trabajador?->user;
            $trabNombre = $trab ? trim($trab->name . ' ' . $trab->apellido) : 'Lorena Ayala';
            return [
                'id_horario' => $h->id,
                'fecha' => !empty($h->fecha) ? Carbon::parse($h->fecha)->toDateString() : now()->toDateString(),
                'hora_inicio' => $h->hora_inicio,
                'hora_fin' => $h->hora_fin,
                'trabajador_nombre' => $trabNombre,
                'especialidad' => $h->trabajador?->especialidad ?? 'Lash Artist & Brow Designer',
            ];
        })->toArray();

        // Trabajadores reales para modal de agenda
        $trabajadores = Trabajador::with('user')->where('estado', 'activo')->get()->map(function ($t) {
            return [
                'id_trabajador' => $t->id,
                'nombre_completo' => $t->user ? trim($t->user->name . ' ' . ($t->user->apellido ?? '')) : 'Especialista',
                'especialidad' => $t->especialidad ?? 'Especialista',
            ];
        })->toArray();

        return view('Clientes.dashboardcliente', compact(
            'totalCitas',
            'citasPendientes',
            'citasCompletadas',
            'proximaCita',
            'serviciosDestacados',
            'actividadReciente',
            'agendasDisponibles',
            'trabajadores'
        ));
    }
}
