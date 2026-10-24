Tecnologías y elementos que utiliza
- PHP: es el lenguaje en el que está escrito el controlador.
- Laravel: es el framework que organiza las rutas, controladores, modelos, validaciones y vistas.
- Modelos: Cita, Cliente, Horario, Pago, Servicio y Trabajador representan las entidades con las que se trabaja en la base de datos.
- Carbon: me permite obtener y calcular fechas y horas, por ejemplo el día actual o la hora de finalización de una cita.
- Request: recibe los datos enviados desde un formulario o una petición.
- DB: es la fachada de Laravel para operaciones de base de datos. Está importada en el archivo, aunque no se utiliza directamente en el código mostrado.
3. Espacio de nombres e importaciones
namespace App\Http\Controllers;
Con esta línea indico que la clase pertenece al espacio de nombres de los controladores de Laravel.
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Horario;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
Importo los modelos que necesito para consultar y guardar citas, clientes, horarios, pagos, servicios y trabajadores. También importo Carbon para manejar fechas, Request para recibir y validar datos y DB para operaciones de base de datos. La importación de DB aparece en el archivo, aunque no se usa de forma directa en este controlador.
class CitaController extends Controller
Declaro la clase CitaController y hago que herede de Controller. De esta manera, puedo utilizar la estructura y las funciones generales que Laravel ofrece para los controladores.
4. Método index(): mostrar la gestión de citas
public function index()
Este método prepara la información que necesita la pantalla de citas. Primero identifica quién inició sesión y, según su rol, decide qué vista mostrar y qué datos consultar.
4.1. Identificación del usuario y del rol
$user = auth()->user();
Obtengo el usuario que tiene la sesión iniciada. Después verifico su role_id para mostrar la pantalla correspondiente.
if ($user && $user->role_id === 3) {
    $servicios = Servicio::where('estado', 'activo')->get();
    return view('Clientes.agendarcitas', compact('servicios'));
}
Si el usuario existe y su rol es 3, lo trato como cliente. Consulto únicamente los servicios activos y envío esa lista a la vista Clientes.agendarcitas. La función compact() permite pasar la variable servicios a la vista.
if ($user && $user->role_id === 2) {
    $trabajador = $user->trabajador
        ?? Trabajador::where('user_id', $user->id)->first();

    $id_trabajador = $trabajador?->id ?? 0;
}
Si el rol es 2, preparo la vista del trabajador. Primero intento obtener el trabajador relacionado con el usuario; si no existe esa relación, lo busco por user_id. El operador ?? utiliza el valor de la derecha cuando el de la izquierda es nulo. El operador ?-> permite consultar id sin provocar un error si el trabajador no existe; si no hay un identificador, utilizo 0.
4.2. Cálculo de fechas y estadísticas del trabajador
$hoy = Carbon::today()->format('Y-m-d');
$inicioSemana = Carbon::now()->startOfWeek()->format('Y-m-d');
$finSemana = Carbon::now()->endOfWeek()->format('Y-m-d');
Guardo la fecha de hoy, el inicio de la semana y el final de la semana con formato año-mes-día. Con estos valores puedo contar las citas de los periodos que aparecen en el panel.
$tieneCitasAsignadas = ($id_trabajador > 0)
    && Cita::where('trabajador_id', $id_trabajador)->exists();

$citasBase = Cita::query();

if ($tieneCitasAsignadas) {
    $citasBase->where('trabajador_id', $id_trabajador);
}
Compruebo si el trabajador tiene citas asignadas. Luego creo una consulta base de citas y, si tiene citas asignadas, la filtro por su identificador. Esto significa que, si no tiene citas asignadas, la consulta queda sin ese filtro y puede incluir citas de otros trabajadores. Conviene revisar si la intención es que cada trabajador vea exclusivamente sus propias citas.
$citasHoy = (clone $citasBase)->whereDate('fecha', $hoy)->count();

$citasSemana = (clone $citasBase)
    ->whereBetween('fecha', [$inicioSemana, $finSemana])
    ->count();

$citasComplet = (clone $citasBase)
    ->whereIn('estado', [
        'finalizado', 'completado', 'completada', 'realizada'
    ])->count();

$citasCancelad = (clone $citasBase)
    ->whereIn('estado', ['cancelado', 'cancelada'])
    ->count();
Calculo cuatro indicadores: citas de hoy, citas de la semana, citas completadas y citas canceladas. Uso clone para duplicar la consulta base y agregar filtros independientes, evitando que un filtro aplicado a una métrica afecte a las demás.
4.3. Obtener y ordenar las citas
$proximasCitasModel = (clone $citasBase)
    ->with(['cliente.user', 'trabajador.user', 'servicio'])
    ->orderBy('fecha', 'asc')
    ->orderBy('hora_inicio', 'asc')
    ->get();
Consulto las citas con sus relaciones de cliente, usuario, trabajador y servicio. Con with() cargo esas relaciones junto con las citas para reducir consultas repetidas. Después ordeno primero por fecha y luego por hora de inicio, de la más temprana a la más tardía. get() ejecuta la consulta y trae los resultados.
4.4. Normalización de los estados
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
Creo un arreglo que convierte las distintas formas en que puede estar guardado un estado en un texto uniforme para mostrarlo en pantalla. Por ejemplo, finalizado y realizada se presentan como Completada.
$proximasCitas = $proximasCitasModel
    ->map(function ($cita) use ($estadoMap) {
        // Se preparan los datos de cada cita.
    })
    ->toArray();
Recorro cada cita para preparar solamente los datos que necesita la vista. Obtengo el nombre del cliente y del profesional; si falta alguna relación, muestro un texto alternativo. También incluyo el servicio, la hora, la fecha, el identificador y el estado legible. Finalmente convierto la colección en un arreglo.

En el código completo, dentro de la función map(), el nombre del cliente se construye con el nombre y el apellido de su usuario. Se hace algo similar con el profesional. El operador ?-> evita acceder a una relación que no exista y trim() elimina espacios sobrantes.
4.5. Listas para formularios del trabajador
$listaClientes = Cliente::with('user')->get()->map(function ($c) {
    return [
        'id_cliente' => $c->id,
        'nombre_completo' => $c->user
            ? trim($c->user->name . ' ' . ($c->user->apellido ?? ''))
            : 'Cliente #' . $c->id,
    ];
})->toArray();
Obtengo los clientes junto con sus usuarios y preparo su identificador y nombre completo. Si no hay un usuario relacionado, utilizo un texto alternativo con el número del cliente.
$listaServicios = Servicio::where('estado', 'activo')
    ->get()
    ->map(function ($s) {
        return [
            'id_servicio' => $s->id,
            'nombre' => $s->nombre,
            'precio' => $s->precio,
        ];
    })->toArray();
Consulto los servicios activos y preparo el identificador, el nombre y el precio. Esta lista sirve para mostrar opciones disponibles en el formulario.
$listaTrabajadores = Trabajador::where('estado', 'activo')
    ->with('user')
    ->get()
    ->map(function ($t) {
        return [
            'id_trabajador' => $t->id,
            'nombre_completo' => $t->user
                ? trim($t->user->name . ' ' . $t->user->apellido)
                : 'Profesional #' . $t->id,
        ];
    })->toArray();
Consulto los trabajadores activos, cargo sus usuarios relacionados y preparo su identificador y nombre completo. Estos datos sirven para seleccionar un profesional.
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
Al terminar, envío las estadísticas y listas a la vista del trabajador. compact() crea un arreglo con las variables que necesita la pantalla.
4.6. Vista del administrador
Si el usuario no entra en los casos anteriores, el método prepara la información para la vista del administrador. Calcula la fecha actual, el inicio y final de la semana y el inicio del mes.
$hoy = Carbon::today()->format('Y-m-d');
$inicioSemana = Carbon::now()->startOfWeek();
$finSemana = Carbon::now()->endOfWeek();
$inicioMes = Carbon::now()->startOfMonth();
Estos valores se utilizan como límites de las consultas estadísticas.
$citasHoy = Cita::whereDate('fecha', $hoy)->count();

$citasSemana = Cita::whereBetween('fecha', [
    $inicioSemana->format('Y-m-d'),
    $finSemana->format('Y-m-d')
])->count();

$citasComplet = Cita::where('estado', 'finalizado')
    ->where('fecha', '>=', $inicioMes->format('Y-m-d'))
    ->count();

$citasCancelad = Cita::where('estado', 'cancelado')
    ->where('fecha', '>=', $inicioMes->format('Y-m-d'))
    ->count();
En el panel administrativo calculo las citas de hoy y de esta semana. También cuento las citas finalizadas y canceladas desde el inicio del mes. A diferencia del bloque del trabajador, estas consultas no se filtran por un trabajador específico.
$citasQuery = Cita::with([
        'cliente.user',
        'trabajador.user',
        'servicio'
    ])
    ->orderBy('fecha', 'asc')
    ->orderBy('hora_inicio', 'asc')
    ->get();
Obtengo todas las citas con los datos relacionados y las ordeno por fecha y hora. Después uso map() para convertir cada registro en un arreglo con la información que mostrará la tabla de citas.
$listaClientes = Cliente::whereHas('user', function ($q) {
    $q->where('role_id', 3);
})->with('user')->get()->map(function ($c) {
    // Se prepara el identificador, nombre y correo del cliente.
});
Para el formulario del administrador, filtro los clientes para incluir únicamente aquellos cuyo usuario tiene el rol 3. whereHas() permite filtrar un modelo según una condición en una relación.
$listaServicios = Servicio::where('estado', 'activo')
    ->get()
    ->map(function ($s) {
        $duracion = $s->duracion_minuto;

        if (is_string($duracion) && str_contains($duracion, ':')) {
            $partes = explode(':', $duracion);
            $minutos = ((int) $partes[0] * 60) + (int) $partes[1];
        } else {
            $minutos = (int) $duracion;
        }

        return [
            'id_servicio' => $s->id,
            'nombre' => $s->nombre,
            'precio' => $s->precio,
            'duracion_minutos' => $minutos > 0 ? $minutos : 45,
        ];
    });
Además de consultar los servicios activos, convierto su duración a minutos. Si está guardada como una hora, por ejemplo 01:30:00, separo sus partes y convierto las horas a minutos antes de sumarlas. Si ya es un número, lo convierto directamente a entero. Al enviar los datos, uso 45 minutos como duración predeterminada cuando el resultado no es mayor que cero.
$listaTrabajadores = Trabajador::where('estado', 'activo')
    ->whereHas('user', function ($q) {
        $q->whereIn('role_id', [1, 2]);
    })
    ->with(['user.role'])
    ->get()
    ->map(function ($t) {
        $rolLabel = $t->user?->role_id == 1
            ? 'Administrador'
            : ($t->especialidad ?: 'Lashista');

        return [
            'id_trabajador' => $t->id,
            'nombre_completo' => $t->user
                ? trim($t->user->name . ' ' . $t->user->apellido)
                : 'Profesional #' . $t->id,
            'especialidad' => $rolLabel,
        ];
    });
En la lista de profesionales incluyo trabajadores activos cuyo usuario tenga rol 1 o 2. Cargo también la relación del rol. Si el usuario es administrador, el texto de especialidad muestra Administrador; en caso contrario, muestra la especialidad registrada o Lashista como alternativa.
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
Por último, envío todas las métricas y listas a la vista admin.citas.citas, que es la pantalla de gestión de citas del administrador.
5. Método store(): registrar una cita y su pago
public function store(Request $request)
Este método recibe la información enviada desde el formulario y crea la cita en la base de datos. También prepara el horario correspondiente y registra un pago asociado.
5.1. Identificar al cliente y asignar profesional
$user = auth()->user();

if ($user && $user->role_id === 3) {
    $cliente = Cliente::firstOrCreate(['user_id' => $user->id]);
    $request->merge(['id_cliente' => $cliente->id]);
}
Si quien agenda es un cliente, busco su registro en la tabla clientes y lo creo si todavía no existe. Luego agrego su identificador a la solicitud para que no tenga que elegir manualmente su propio cliente.
if (empty($request->id_trabajador)
    || !Trabajador::where('id', $request->id_trabajador)->exists()) {
    $primerTrabajador = Trabajador::where('estado', 'activo')->first();
    $request->merge(['id_trabajador' => $primerTrabajador?->id ?? 1]);
}
Si no se envía un trabajador válido, busco el primer trabajador activo y uso su identificador. Si no se encuentra, el código utiliza 1 como alternativa. Esta alternativa requiere que exista un trabajador con ese identificador para que la validación posterior pase.
5.2. Validar los datos recibidos
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
Antes de guardar, valido los datos. Los identificadores de cliente, servicio y trabajador son obligatorios y deben existir en sus respectivas tablas. La fecha debe tener un formato válido y la hora de inicio es obligatoria. Estado y método de pago pueden omitirse, y las observaciones aceptan texto de hasta 500 caracteres.
5.3. Consultar el servicio y calcular la hora final
$servicio = Servicio::findOrFail($request->id_servicio);
Busco el servicio seleccionado. Si no existe, Laravel detiene el proceso con una respuesta de error.
$duracion = $servicio->duracion_minuto;
$minutos = 45;

if (is_string($duracion) && str_contains($duracion, ':')) {
    $partes = explode(':', $duracion);
    $minutos = ((int) $partes[0] * 60) + (int) $partes[1];
} elseif (is_numeric($duracion) && $duracion > 0) {
    $minutos = (int) $duracion;
}

$horaInicio = Carbon::parse($request->hora_inicio);
$horaFin = (clone $horaInicio)->addMinutes($minutos)->format('H:i:s');
Tomo la duración del servicio y la convierto a minutos, tanto si está guardada como una hora como si está guardada como un número. Si no se puede utilizar una duración válida, mantengo el valor inicial de 45 minutos. Después convierto la hora inicial en un objeto Carbon y le sumo la duración para calcular la hora de finalización.
5.4. Unificar el estado de la cita
$estadoInput = strtolower($request->estado ?? 'pendiente');

$estadoDb = match ($estadoInput) {
    'confirmada', 'confirmado' => 'confirmado',
    'cancelada', 'cancelado' => 'cancelado',
    'completada', 'finalizado' => 'finalizado',
    default => 'pendiente',
};
Convierto el estado recibido a minúsculas y, si no se envió ninguno, uso pendiente. La expresión match agrupa las distintas formas de escribir los estados y las adapta a los valores que se guardan en la base de datos.
5.5. Crear o reutilizar el horario
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
Busco un horario con el mismo trabajador, fecha y hora de inicio. Si ya existe, lo reutilizo; si no, lo creo con la hora final calculada y estado activo. Esto permite obtener el identificador del horario que necesita la cita.
5.6. Guardar la cita
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
Creo el registro de la cita con los identificadores relacionados, la fecha, las horas de inicio y fin, el estado, las observaciones y la fecha de creación. El método create() guarda la información si los campos están permitidos en la configuración fillable o guarded del modelo.
5.7. Registrar el pago
$metodo = $request->metodo_pago ?? 'tarjeta';

$estadoPago = in_array(strtolower($metodo), ['tarjeta', 'pse', 'digital'])
    ? 'completado'
    : 'pendiente';

$detallePago = 'Pago mediante pasarela (' . ucfirst($metodo) . ')';

if ($request->cuotas_pago) {
    $detallePago .= ' - ' . $request->cuotas_pago . ' cuota(s)';
}
Si no se indica un método de pago, uso tarjeta. Según el código, los métodos tarjeta, PSE y digital quedan con estado completado; los demás quedan pendientes. También preparo una descripción del pago y agrego la cantidad de cuotas cuando se recibe ese dato.
Pago::create([
    'cita_id' => $cita->id,
    'metodo_pago' => ucfirst($metodo),
    'monto' => $servicio->precio,
    'estado' => $estadoPago,
    'fecha_pago' => now(),
    'observaciones' => $detallePago,
]);
Guardo el pago asociado a la cita recién creada. El monto corresponde al precio del servicio, y también registro el método, el estado, la fecha y el detalle preparado.
5.8. Responder según el tipo de solicitud
if ($request->ajax() || $request->wantsJson()) {
    return response()->json([
        'success' => true,
        'message' => '¡Tu cita ha sido agendada con éxito y tu pago procesado!',
        'cita' => $cita,
    ]);
}
Si la solicitud se hizo mediante AJAX o espera una respuesta JSON, devuelvo una respuesta estructurada con un indicador de éxito, un mensaje y los datos de la cita.
if ($user && $user->role_id === 3) {
    return redirect()
        ->route('cliente.dashboard')
        ->with('success', '¡Tu cita ha sido agendada con éxito!');
}

return redirect()
    ->route('citas.index')
    ->with('success', '¡Cita agendada correctamente!');
Si la cita fue agendada por un cliente, lo redirijo al panel del cliente y muestro un mensaje de confirmación. Para los demás usuarios, regreso a la pantalla de citas. with('success', ...) permite enviar un mensaje temporal para mostrarlo en la siguiente vista.
6. Método cancelar(): cancelar una cita
public function cancelar($id)
Este método recibe el identificador de la cita que se desea cancelar.
$cita = Cita::findOrFail($id);
$user = auth()->user();
Busco la cita por su identificador. Si no existe, Laravel genera una respuesta de error. También obtengo el usuario que inició sesión.
if ($user && $user->role_id === 3) {
    $cliente = Cliente::where('user_id', $user->id)->first();

    if (!$cliente || $cita->cliente_id !== $cliente->id) {
        abort(403, 'No tienes permiso para cancelar esta cita.');
    }
}
Si el usuario es cliente, verifico que la cita realmente le pertenezca. Busco su registro en la tabla clientes y comparo el identificador con el cliente asociado a la cita. Si no coincide o no existe, devuelvo un error 403 de acceso prohibido.
$cita->estado = 'cancelado';
$cita->save();
Cuando se permite la operación, cambio el estado de la cita a cancelado y guardo el cambio en la base de datos.
if ($user && $user->role_id === 3) {
    return redirect()->back()
        ->with('success', 'La cita fue cancelada exitosamente.');
}

return redirect()->route('citas.index')
    ->with('success', 'La cita fue cancelada exitosamente.');
Después de cancelar, si el usuario es cliente regreso a la página anterior. En los demás casos lo envío a la lista de citas. En ambos casos muestro un mensaje de confirmación.
7. Funciones y operadores importantes
Elemento	Explicación
auth()->user()	Obtiene el usuario autenticado.
where()	Aplica una condición para filtrar registros.
whereDate()	Filtra usando la fecha de un campo.
whereBetween()	Busca valores dentro de un rango.
whereIn()	Busca valores dentro de una lista.
count()	Cuenta los registros que cumplen los filtros.
exists()	Comprueba si existe al menos un registro.
with()	Carga relaciones entre modelos.
map()	Recorre una colección y transforma sus elementos.
toArray()	Convierte una colección en un arreglo.
compact()	Prepara variables para enviarlas a una vista.
firstOrCreate()	Busca un registro y lo crea si no lo encuentra.
findOrFail()	Busca un registro y genera un error si no existe.
validate()	Valida los datos recibidos antes de continuar.
??	Usa un valor alternativo si el de la izquierda es nulo.
?->	Permite acceder a una propiedad o método solo si el objeto no es nulo.
clone	Crea una copia del objeto de consulta para usar filtros independientes.
now()	Obtiene la fecha y hora actuales usando Laravel.
abort(403, ...)	Detiene la petición con un error de acceso prohibido.


8. Flujo general del controlador
1. El usuario entra a la sección de citas y el sistema identifica su rol.
2. El controlador consulta las citas y los datos que necesita cada pantalla.
3. Cuando se agenda una cita, recibe y valida los datos del formulario.
4. Consulta el servicio, calcula la hora de finalización y normaliza el estado.
5. Busca o crea el horario y guarda la cita.
6. Registra un pago relacionado con la cita.
7. Devuelve una respuesta JSON o redirige al usuario con un mensaje.
8. Si se cancela una cita, verifica los permisos del cliente, cambia el estado y guarda el cambio.