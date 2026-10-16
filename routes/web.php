<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\CitaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/cliente/dashboard', [DashboardController::class, 'dashboardCliente'])
    ->middleware(['auth'])
    ->name('cliente.dashboard');

Route::get('/trabajador/dashboard', [DashboardController::class, 'dashboardTrabajador'])
    ->middleware(['auth'])
    ->name('trabajador.dashboard');

Route::get('/cliente/catalogo', function () {
    $categorias = \App\Models\Categoria::where('estado', 'activo')->get();
    $servicios = \App\Models\Servicio::with('categoria')->where('estado', 'activo')->get();
    return view('Clientes.catalogo', compact('servicios', 'categorias'));
})->middleware(['auth'])->name('cliente.catalogo');

Route::get('/cliente/agendar', function (\Illuminate\Http\Request $request) {
    $servicios = \App\Models\Servicio::with('categoria')->where('estado', 'activo')->get();
    $trabajadores = \App\Models\Trabajador::with('user')->where('estado', 'activo')->get();
    $user = auth()->user();
    $cliente = \App\Models\Cliente::firstOrCreate(['user_id' => $user?->id]);
    $misCitas = \App\Models\Cita::with(['servicio', 'trabajador.user', 'pago'])
        ->where('cliente_id', $cliente->id)
        ->orderBy('fecha', 'desc')
        ->orderBy('hora_inicio', 'desc')
        ->get();
    $servicioIdSeleccionado = $request->query('servicio_id');

    return view('Clientes.agendarcitas', compact('servicios', 'trabajadores', 'cliente', 'misCitas', 'servicioIdSeleccionado'));
})->middleware(['auth'])->name('cliente.agendar');

Route::get('/cliente/mis-citas', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    $cliente = \App\Models\Cliente::firstOrCreate(['user_id' => $user?->id]);

    // Obtener citas del cliente con sus relaciones
    $citas = \App\Models\Cita::with(['servicio.categoria', 'trabajador.user', 'pago', 'resena'])
        ->where('cliente_id', $cliente->id)
        ->orderBy('fecha', 'desc')
        ->orderBy('hora_inicio', 'desc')
        ->get();

    $hoy = \Carbon\Carbon::today();

    // Filtrar citas por estado
    $citasProximas = $citas->filter(function ($c) use ($hoy) {
        $fechaCita = $c->fecha ? \Carbon\Carbon::parse($c->fecha) : null;
        return $fechaCita && $fechaCita->greaterThanOrEqualTo($hoy) && in_array(strtolower($c->estado), ['pendiente', 'confirmado']);
    })->values();

    // Si no hay citas próximas con fecha futura, tomar las citas confirmadas o pendientes existentes
    if ($citasProximas->isEmpty()) {
        $citasProximas = $citas->whereIn('estado', ['confirmado', 'pendiente'])->values();
    }

    $citasPendientes = $citas->where('estado', 'pendiente')->values();
    $citasCompletadas = $citas->whereIn('estado', ['finalizado', 'completado'])->values();
    $citasCanceladas = $citas->where('estado', 'cancelado')->values();

    $countProximas = $citasProximas->count();
    $countPendientes = $citasPendientes->count();
    $countCompletadas = $citasCompletadas->count();
    $countCanceladas = $citasCanceladas->count();

    return view('Clientes.miscitas', compact(
        'citas',
        'citasProximas',
        'citasPendientes',
        'citasCompletadas',
        'citasCanceladas',
        'countProximas',
        'countPendientes',
        'countCompletadas',
        'countCanceladas'
    ));
})->middleware(['auth'])->name('cliente.citas');

Route::get('/cliente/historial', function (\Illuminate\Http\Request $request) {
    $user = auth()->user();
    $cliente = \App\Models\Cliente::firstOrCreate(['user_id' => $user?->id]);

    // Consultar citas reales del cliente con sus relaciones desde la base de datos
    $citas = \App\Models\Cita::with(['servicio.categoria', 'trabajador.user', 'pago', 'resena'])
        ->where('cliente_id', $cliente->id)
        ->orderBy('fecha', 'desc')
        ->orderBy('hora_inicio', 'desc')
        ->get();

    // Estadísticas reales calculadas desde la base de datos
    $totalCitas = $citas->count();
    $serviciosRealizados = $citas->whereIn('estado', ['completado', 'completada', 'finalizado'])->count();

    $totalInvertido = $citas->sum(function ($c) {
        if ($c->pago && $c->pago->monto > 0) {
            return (float)$c->pago->monto;
        }
        if (in_array(strtolower($c->estado), ['completado', 'completada', 'finalizado', 'confirmado', 'confirmada'])) {
            return (float)($c->servicio?->precio ?? 0);
        }
        return 0;
    });

    // Calificación real de las reseñas del cliente o general
    $resenas = \App\Models\Resena::where('cliente_id', $cliente->id)->get();
    if ($resenas->count() > 0) {
        $calificacionPromedio = round($resenas->avg('calificacion'), 1);
    } else {
        $promedioGeneral = \App\Models\Resena::avg('calificacion');
        $calificacionPromedio = $promedioGeneral ? round($promedioGeneral, 1) : 5.0;
    }

    // Servicios y trabajadores activos desde la base de datos para los filtros
    $serviciosFiltro = \App\Models\Servicio::where('estado', 'activo')->get();
    $profesionalesFiltro = \App\Models\Trabajador::with('user')->where('estado', 'activo')->get();

    return view('Clientes.historial', compact(
        'citas',
        'totalCitas',
        'serviciosRealizados',
        'totalInvertido',
        'calificacionPromedio',
        'serviciosFiltro',
        'profesionalesFiltro'
    ));
})->middleware(['auth'])->name('cliente.historial');

// Portal Cliente: Reseñas y Perfil
use App\Http\Controllers\ClienteController;

Route::middleware(['auth'])->group(function () {
    Route::get('/cliente/resenas', [ClienteController::class, 'resenas'])->name('cliente.resenas');
    Route::post('/cliente/resenas', [ClienteController::class, 'storeResena'])->name('cliente.resenas.store');

    Route::get('/cliente/perfil', [ClienteController::class, 'perfil'])->name('cliente.perfil');
    Route::post('/cliente/perfil/actualizar', [ClienteController::class, 'updatePerfil'])->name('cliente.perfil.update');
    Route::post('/cliente/perfil/foto', [ClienteController::class, 'updateFoto'])->name('cliente.perfil.foto');
    Route::post('/cliente/perfil/password', [ClienteController::class, 'updatePassword'])->name('cliente.perfil.password');
    Route::get('/cliente/perfil/exportar', [ClienteController::class, 'exportarDatos'])->name('cliente.perfil.exportar');
    Route::post('/cliente/perfil/eliminar', [ClienteController::class, 'eliminarCuenta'])->name('cliente.perfil.eliminar');
});

Route::get('/admin/citas', [CitaController::class, 'index'])->middleware(['auth'])->name('citas.index');
Route::post('/admin/citas', [CitaController::class, 'store'])->middleware(['auth'])->name('citas.store');
Route::get('/admin/citas/cancelar/{id}', [CitaController::class, 'cancelar'])->middleware(['auth'])->name('citas.cancelar');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ConfiguracionController;

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/formusuarios', [UsuarioController::class, 'create'])->name('usuarios.create');
    Route::post('/registro', [UsuarioController::class, 'store'])->name('usuarios.store');
    Route::get('/listausuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::put('/admin/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');
    Route::delete('/admin/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');

    // Gestión de Servicios
    Route::get('/admin/servicios', [ServicioController::class, 'index'])->name('servicios.index');
    Route::post('/admin/servicios', [ServicioController::class, 'store'])->name('servicios.store');
    Route::put('/admin/servicios/{id}', [ServicioController::class, 'update'])->name('servicios.update');
    Route::delete('/admin/servicios/{id}', [ServicioController::class, 'destroy'])->name('servicios.destroy');

    // Gestión de Pagos
    Route::get('/admin/pagos', [PagoController::class, 'index'])->name('pagos.index');
    Route::post('/admin/pagos', [PagoController::class, 'store'])->name('pagos.store');
    Route::put('/admin/pagos/{id}', [PagoController::class, 'update'])->name('pagos.update');
    Route::delete('/admin/pagos/{id}', [PagoController::class, 'destroy'])->name('pagos.destroy');

    // Gestión de Reseñas
    Route::get('/admin/resenas', [ResenaController::class, 'index'])->name('resenas.index');
    Route::post('/admin/resenas', [ResenaController::class, 'store'])->name('resenas.store');
    Route::delete('/admin/resenas/{id}', [ResenaController::class, 'destroy'])->name('resenas.destroy');

    // Reportes y Rendimiento
    Route::get('/admin/reportes', [ReporteController::class, 'index'])->name('reportes.index');

    // Configuración del Sistema
    Route::get('/admin/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::post('/admin/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

    // Notificaciones del Sistema
    Route::get('/admin/notificaciones', [\App\Http\Controllers\NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::post('/admin/notificaciones/marcar-leidas', [\App\Http\Controllers\NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcarLeidas');
});