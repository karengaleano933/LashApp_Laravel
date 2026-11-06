# Explicación de mi archivo principal de Rutas Web (`routes/web.php`)

En este archivo tengo organizada toda la "libreta de direcciones" de mi aplicación **LashApp**. Cada ruta es como un camino o una puerta: cuando una persona (sea clienta, trabajadora o administradora) hace clic en un botón, escribe una dirección en su navegador o envía un formulario, este archivo le dice al sistema a qué pantalla debe llevarla o qué acción debe realizar (como agendar una cita, registrar un pago, guardar un servicio o cancelar un turno).

Para que nadie entre donde no debe, uso vigilantes o candados de seguridad (`middleware`), que revisan si la persona inició sesión y si tiene el rol correcto antes de dejarla pasar.

---

## 1. Conexión de mis herramientas y controladores principales

Aquí traigo a mis ayudantes (controladores), que son los cerebros encargados de procesar la información de cada parte del sistema:

| Código | ¿Qué hace? |
| :--- | :--- |
| `use App\Http\Controllers\ProfileController;` | Traigo a mi ayudante que maneja la información del perfil personal (cambiar nombre, correo y contraseña). |
| `use App\Http\Controllers\UsuarioController;` | Traigo a mi ayudante encargado de crear, editar, listar y desactivar cuentas de usuarias y trabajadoras. |
| `use App\Http\Controllers\CitaController;` | Traigo al encargado de organizar la agenda de citas, agendar turnos, cancelarlos o marcarlos como completados. |
| `use Illuminate\Support\Facades\Route;` | Traigo la herramienta básica de Laravel que me permite crear caminos y puertas de entrada web. |
| `use App\Http\Controllers\DashboardController;` | Traigo al ayudante que calcula los números y resúmenes para mostrar en las pantallas de inicio de cada rol. |

---

## 2. Puerta de Bienvenida y Pantalla Principal (`Dashboard`)

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/', function () { return view('welcome'); })->name('welcome');` | Es la portada principal de mi sitio web. Cuando alguien entra a la dirección raíz (`/`), le muestro la página de bienvenida con información del salón. |
| `Route::get('/dashboard', [DashboardController::class, 'index'])` | Camino al panel principal una vez que la persona inicia sesión. Llama a mi controlador para saber a dónde mandarla según su rol. |
| `->middleware(['auth', 'verified'])` | Le pongo dos candados: solo puede pasar si ya inició sesión (`auth`) y si verificó su correo electrónico (`verified`). |
| `->name('dashboard');` | Le doy un apodo rápido a esta ruta (`dashboard`) para llamarla en botones y menús sin tener que escribir la dirección completa. |

---

## 3. Pantallas de Inicio según el Rol (Cliente y Trabajador)

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/cliente/dashboard', [DashboardController::class, 'dashboardCliente'])` | Abre la pantalla de inicio exclusiva para la clienta, mostrándole sus próximas citas, promociones y accesos rápidos. |
| `->middleware(['auth'])->name('cliente.dashboard');` | Pide que la clienta haya iniciado sesión y le da el apodo `cliente.dashboard`. |
| `Route::get('/trabajador/dashboard', [DashboardController::class, 'dashboardTrabajador'])` | Abre la pantalla de inicio de la especialista, donde ve sus citas programadas para el día y su rendimiento. |
| `->middleware(['auth'])->name('trabajador.dashboard');` | Pide que la trabajadora tenga su sesión abierta y le da el apodo `trabajador.dashboard`. |
| `Route::get('/trabajador/citas', [CitaController::class, 'index'])` | Abre la agenda de citas completa de la trabajadora para que revise sus turnos y clientas asignadas. |
| `->middleware(['auth'])->name('trabajador.citas');` | Pide inicio de sesión obligatorio y le da el apodo `trabajador.citas`. |

---

## 4. Portal de la Clienta: Catálogo, Agendamiento, Citas e Historial

En este bloque atiendo todo lo que mis clientas necesitan ver y hacer desde su cuenta:

### A. Ver el Catálogo de Servicios
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/cliente/catalogo', function () { ... })->middleware(['auth'])->name('cliente.catalogo');` | Abre el catálogo virtual para la clienta. |
| `$categorias = \App\Models\Categoria::where('estado', 'activo')->get();` | Busco en mi base de datos únicamente las categorías que estén activas (por ejemplo: Pestañas, Cejas, Depilación). |
| `$servicios = \App\Models\Servicio::with('categoria')->where('estado', 'activo')->get();` | Traigo todos los servicios disponibles y activos con su categoría correspondiente (precios, tiempos, fotos). |
| `return view('Clientes.catalogo', compact('servicios', 'categorias'));` | Le muestro a la clienta la pantalla del catálogo con todos los servicios y categorías listos para explorar. |

### B. Agendar una Cita Nueva
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/cliente/agendar', function (\Illuminate\Http\Request $request) { ... })->middleware(['auth'])->name('cliente.agendar');` | Abre la pantalla interactiva donde la clienta puede reservar un turno paso a paso. |
| `$servicios = \App\Models\Servicio::with('categoria')->where('estado', 'activo')->get();` | Traigo la lista de servicios activos para que la clienta elija cuál desea hacerse. |
| `$trabajadores = \App\Models\Trabajador::with('user')->where('estado', 'activo')->get();` | Traigo a las especialistas disponibles para que la clienta escoja con quién atenderse. |
| `$user = auth()->user();` | Identifico quién es la clienta que tiene abierta la sesión en este momento. |
| `$cliente = \App\Models\Cliente::firstOrCreate(['user_id' => $user?->id]);` | Me aseguro de que exista su registro como clienta; si es nueva, la creo automáticamente en el sistema. |
| `$misCitas = \App\Models\Cita::with(['servicio', 'trabajador.user', 'pago'])->where('cliente_id', $cliente->id)->orderBy('fecha', 'desc')->orderBy('hora_inicio', 'desc')->get();` | Consulto sus citas anteriores y recientes para que las tenga a la vista mientras agenda. |
| `$servicioIdSeleccionado = $request->query('servicio_id');` | Reviso si la clienta ya venía con un servicio preseleccionado desde el catálogo para marcarlo de una vez. |
| `return view('Clientes.agendarcitas', compact('servicios', 'trabajadores', 'cliente', 'misCitas', 'servicioIdSeleccionado'));` | Muestro la pantalla para agendar con el calendario, los horarios y los datos cargados. |

### C. Mis Citas (Seguimiento de citas agendadas)
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/cliente/mis-citas', function (\Illuminate\Http\Request $request) { ... })->middleware(['auth'])->name('cliente.citas');` | Abre la pantalla donde la clienta puede ver todas sus citas organizadas en pestañas. |
| `$user = auth()->user(); $cliente = \App\Models\Cliente::firstOrCreate(['user_id' => $user?->id]);` | Identifico a la clienta conectada. |
| `$citas = \App\Models\Cita::with(['servicio.categoria', 'trabajador.user', 'pago', 'resena'])->where('cliente_id', $cliente->id)->orderBy('fecha', 'desc')->orderBy('hora_inicio', 'desc')->get();` | Traigo todas las citas de la clienta junto con el servicio, la especialista asignada, el pago y si ya dejó reseña. |
| `$hoy = \Carbon\Carbon::today();` | Tomo la fecha de hoy para poder comparar qué citas son futuras y cuáles ya pasaron. |
| `$citasProximas = $citas->filter(function ($c) use ($hoy) { ... })->values();` | Separo las citas que están programadas para hoy o fechas futuras y que están pendientes o confirmadas. |
| `$citasPendientes = $citas->where('estado', 'pendiente')->values();` | Separo únicamente las citas que están esperando confirmación. |
| `$citasCompletadas = $citas->whereIn('estado', ['finalizado', 'completado'])->values();` | Separo las citas donde la clienta ya fue atendida con éxito. |
| `$citasCanceladas = $citas->where('estado', 'cancelado')->values();` | Separo las citas que fueron canceladas. |
| `$countProximas = ...; $countPendientes = ...; $countCompletadas = ...; $countCanceladas = ...;` | Cuento cuántas citas hay en cada grupo para poner los numeritos en las pestañas del menú. |
| `return view('Clientes.miscitas', compact(...));` | Muestro la pantalla con las tarjetas de citas y sus contadores al día. |

### D. Historial Completo y Estadísticas de la Clienta
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/cliente/historial', function (\Illuminate\Http\Request $request) { ... })->middleware(['auth'])->name('cliente.historial');` | Abre el historial completo de belleza de la clienta. |
| `$totalCitas = $citas->count();` | Calculo cuántas citas en total ha tenido conmigo en el salón. |
| `$serviciosRealizados = $citas->whereIn('estado', ['completado', 'completada', 'finalizado'])->count();` | Cuento cuántos tratamientos completó con éxito. |
| `$totalInvertido = $citas->sum(function ($c) { ... });` | Sumo todo el dinero que la clienta ha invertido en sus tratamientos de belleza. |
| `$calificacionPromedio = ...;` | Calculo el promedio de estrellas o satisfacción de las opiniones que ha dejado. |
| `$serviciosFiltro = ...; $profesionalesFiltro = ...;` | Cargo las listas de servicios y especialistas para que la clienta pueda filtrar su historial por fecha, especialista o tratamiento. |
| `return view('Clientes.historial', compact(...));` | Muestro la pantalla con los resúmenes de dinero, número de citas y la lista filtrable. |

---

## 5. Portal de la Clienta: Reseñas y Perfil Personal

| Código | ¿Qué hace? |
| :--- | :--- |
| `use App\Http\Controllers\ClienteController;` | Traigo al ayudante encargado de tramitar las opiniones y el perfil de la clienta. |
| `Route::middleware(['auth'])->group(function () { ... });` | Candado de seguridad para que solo clientas con sesión iniciada puedan hacer estas operaciones: |
| `Route::get('/cliente/resenas', [ClienteController::class, 'resenas'])->name('cliente.resenas');` | Abre la pantalla donde la clienta ve las opiniones que ha escrito sobre sus tratamientos. |
| `Route::post('/cliente/resenas', [ClienteController::class, 'storeResena'])->name('cliente.resenas.store');` | Recibe la calificación (estrellas) y el comentario nuevo que la clienta redactó y lo guarda. |
| `Route::get('/cliente/perfil', [ClienteController::class, 'perfil'])->name('cliente.perfil');` | Abre la pantalla de ajustes de cuenta de la clienta (datos personales, foto, seguridad). |
| `Route::post('/cliente/perfil/actualizar', [ClienteController::class, 'updatePerfil'])->name('cliente.perfil.update');` | Guarda los cambios en su nombre, teléfono o correo. |
| `Route::post('/cliente/perfil/foto', [ClienteController::class, 'updateFoto'])->name('cliente.perfil.foto');` | Recibe y guarda la nueva foto de perfil subida por la clienta. |
| `Route::post('/cliente/perfil/password', [ClienteController::class, 'updatePassword'])->name('cliente.perfil.password');` | Cambia la clave secreta de acceso de la clienta verificando su contraseña actual. |
| `Route::get('/cliente/perfil/exportar', [ClienteController::class, 'exportarDatos'])->name('cliente.perfil.exportar');` | Descarga un archivo con toda la información personal y citas de la clienta para su privacidad. |
| `Route::post('/cliente/perfil/eliminar', [ClienteController::class, 'eliminarCuenta'])->name('cliente.perfil.eliminar');` | Permite a la clienta solicitar la desactivación o borrado permanente de su cuenta si así lo desea. |

---

## 6. Rutas de Citas para la Administración

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/admin/citas', [CitaController::class, 'index'])->middleware(['auth'])->name('citas.index');` | Abre el calendario y listado general de todas las citas del negocio para la administradora. |
| `Route::post('/admin/citas', [CitaController::class, 'store'])->middleware(['auth'])->name('citas.store');` | Guarda una nueva cita reservada manualmente por la administradora desde el panel. |
| `Route::get('/admin/citas/cancelar/{id}', [CitaController::class, 'cancelar'])->middleware(['auth'])->name('citas.cancelar');` | Cancela una cita específica según su número de identificación (`id`). |
| `Route::get('/admin/citas/completar/{id}', [CitaController::class, 'completar'])->middleware(['auth'])->name('citas.completar');` | Marca una cita como terminada cuando la clienta ya recibió su atención en el salón. |

---

## 7. Gestión de Servicios (Tanto para Admin como para Trabajador)

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/admin/servicios', [\App\Http\Controllers\ServicioController::class, 'index'])->name('servicios.index');` | Abre la lista de servicios del salón para la administradora (ver precios, tiempos y categorías). |
| `Route::get('/trabajador/servicios', [\App\Http\Controllers\ServicioController::class, 'index'])->name('trabajador.servicios');` | Permite a la trabajadora ver el catálogo de servicios que puede realizar. |
| `Route::post('/admin/servicios', [\App\Http\Controllers\ServicioController::class, 'store'])->name('servicios.store');` | Guarda en el catálogo un nuevo tratamiento (ejemplo: "Volumen Hawaiano", $120.000, 120 min). |
| `Route::put('/admin/servicios/{id}', [\App\Http\Controllers\ServicioController::class, 'update'])->name('servicios.update');` | Modifica el precio, tiempo o descripción de un servicio existente según su `id`. |
| `Route::delete('/admin/servicios/{id}', [\App\Http\Controllers\ServicioController::class, 'destroy'])->name('servicios.destroy');` | Elimina o apaga un servicio del catálogo para que ya no pueda ser agendado. |

---

## 8. Portal de la Trabajadora: Horario, Reseñas y Perfil

| Código | ¿Qué hace? |
| :--- | :--- |
| `use App\Http\Controllers\HorarioController; use App\Http\Controllers\TrabajadorController;` | Traigo a los ayudantes encargados del turno y datos de las colaboradoras. |
| `Route::get('/trabajador/horario', [HorarioController::class, 'index'])->name('trabajador.horario');` | Abre la pantalla donde la especialista define sus días y horas de trabajo semanal. |
| `Route::post('/trabajador/horario', [HorarioController::class, 'store'])->name('trabajador.horario.store');` | Guarda una nueva jornada de atención (ejemplo: Lunes de 9:00 AM a 6:00 PM). |
| `Route::delete('/trabajador/horario/{id}', [HorarioController::class, 'destroy'])->name('trabajador.horario.destroy');` | Quita un horario o bloque de atención de la especialista según su `id`. |
| `Route::get('/trabajador/resenas', [\App\Http\Controllers\ResenaController::class, 'index'])->name('trabajador.resenas');` | Muestra a la trabajadora los comentarios y estrellas que le han calificado las clientas que ella atendió. |
| `Route::get('/trabajador/perfil', [TrabajadorController::class, 'perfil'])->name('trabajador.perfil');` | Abre la pantalla del perfil profesional de la especialista. |
| `Route::post('/trabajador/perfil/actualizar', [TrabajadorController::class, 'updatePerfil'])->name('trabajador.perfil.update');` | Guarda cambios en su especialidad, teléfono o datos de contacto. |
| `Route::post('/trabajador/perfil/foto', [TrabajadorController::class, 'updateFoto'])->name('trabajador.perfil.foto');` | Guarda la nueva fotografía profesional de la especialista. |
| `Route::post('/trabajador/perfil/password', [TrabajadorController::class, 'updatePassword'])->name('trabajador.perfil.password');` | Actualiza la contraseña de acceso de la especialista. |

---

## 9. Rutas Generales de Perfil y Carga de Rutas de Autenticación

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');` | Abre la pantalla general de edición de perfil. |
| `Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');` | Actualiza los datos básicos del perfil (nombre y correo). |
| `Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');` | Permite dar de baja o eliminar el perfil del usuario. |
| `require __DIR__.'/auth.php';` | Aquí conecto e incluyo todas las rutas de login, registro y recuperación de clave desde el archivo `auth.php`. |

---

## 10. Módulo Privado de Administradora (`role:admin`)

Todas estas rutas tienen un cerrojo doble: piden haber iniciado sesión (`auth`) y tener estrictamente el rol de **administradora** (`role:admin`). Ninguna clienta ni especialista puede acceder a ellas:

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::middleware(['auth', 'role:admin'])->group(function () { ... });` | Agrupo las rutas bajo la máxima seguridad administrativa. |

### A. Gestión de Usuarios
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/formusuarios', [UsuarioController::class, 'create'])->name('usuarios.create');` | Abre el formulario para registrar una nueva cuenta de administradora, especialista o clienta. |
| `Route::post('/registro', [UsuarioController::class, 'store'])->name('usuarios.store');` | Recibe los datos del formulario y da de alta al nuevo usuario en la base de datos. |
| `Route::get('/listausuarios', [UsuarioController::class, 'index'])->name('usuarios.index');` | Muestra la tabla de todos los usuarios registrados con buscador y filtros por rol. |
| `Route::put('/admin/usuarios/{id}', [UsuarioController::class, 'update'])->name('usuarios.update');` | Guarda cambios en los datos o rol de un usuario existente según su `id`. |
| `Route::delete('/admin/usuarios/{id}', [UsuarioController::class, 'destroy'])->name('usuarios.destroy');` | Da de baja o elimina una cuenta de usuario según su `id`. |

### B. Gestión de Pagos y Caja
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/admin/pagos', [PagoController::class, 'index'])->name('pagos.index');` | Abre el módulo contable con la lista de pagos, anticipos y totales recaudados. |
| `Route::post('/admin/pagos', [PagoController::class, 'store'])->name('pagos.store');` | Registra el cobro de una cita (efectivo, Nequi, tarjeta o transferencia). |
| `Route::put('/admin/pagos/{id}', [PagoController::class, 'update'])->name('pagos.update');` | Modifica un pago ya registrado si hubo un error en el monto o método de cobro. |
| `Route::delete('/admin/pagos/{id}', [PagoController::class, 'destroy'])->name('pagos.destroy');` | Anula o elimina un registro de pago del historial contable. |

### C. Gestión de Reseñas
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/admin/resenas', [ResenaController::class, 'index'])->name('resenas.index');` | Abre la pantalla de moderación de reseñas para leer comentarios y calificaciones recibidas. |
| `Route::post('/admin/resenas', [ResenaController::class, 'store'])->name('resenas.store');` | Permite a la administradora responder una reseña o registrar una opinión manual. |
| `Route::delete('/admin/resenas/{id}', [ResenaController::class, 'destroy'])->name('resenas.destroy');` | Elimina una reseña que sea ofensiva o inadecuada según su `id`. |

### D. Reportes del Negocio
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/admin/reportes', [ReporteController::class, 'index'])->name('reportes.index');` | Abre la pantalla ejecutiva con gráficos de ventas, servicios más demandados y desempeño de trabajadoras. |

### E. Configuración del Negocio
| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::get('/admin/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');` | Abre los ajustes generales de la estética (nombre, horario general, WhatsApp, políticas). |
| `Route::post('/admin/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');` | Guarda en el sistema los nuevos ajustes de configuración. |

---

## 11. Notificaciones del Sistema

| Código | ¿Qué hace? |
| :--- | :--- |
| `Route::middleware(['auth'])->group(function () { ... });` | Protege las rutas para que solo usuarias identificadas puedan ver o tocar las notificaciones. |
| `Route::get('/notificaciones', [\App\Http\Controllers\NotificacionController::class, 'index'])->name('notificaciones.index');` | Abre el centro de notificaciones con la lista de alertas (citas nuevas, pagos y cancelaciones). |
| `Route::post('/notificaciones/marcar-leidas', [\App\Http\Controllers\NotificacionController::class, 'marcarTodasLeidas'])->name('notificaciones.marcarLeidas');` | Marca todas las notificaciones pendientes como "ya leídas" con un solo clic para limpiar la campanita. |
