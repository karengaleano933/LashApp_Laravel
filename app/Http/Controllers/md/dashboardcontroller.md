# Explicación detallada del DashboardController.php

## 1. ¿Qué hace este controlador?

El archivo `DashboardController.php` se encarga de controlar los paneles principales de LashApp. Su función es consultar la información almacenada en la base de datos y mostrarla de acuerdo con el rol del usuario que inició sesión.

En mi proyecto existen tres tipos de paneles:

- **Administrador:** puede visualizar estadísticas generales del negocio, como usuarios, clientes, trabajadores, citas, servicios y reseñas.
- **Cliente:** puede consultar sus citas, conocer su próxima cita, ver servicios destacados, revisar su actividad reciente y consultar las agendas disponibles.
- **Trabajador:** puede consultar estadísticas de sus citas, los clientes que atiende, las próximas citas, su calificación y los servicios más populares.

El controlador obtiene los datos, los organiza y los envía a las vistas para que aparezcan correctamente en la interfaz.

---

## 2. Inicio del archivo y espacio de nombres

```php
<?php

namespace App\Http\Controllers;
```

- `<?php`: indica que el archivo contiene código PHP.
- `namespace App\Http\Controllers;`: indica que este archivo pertenece al espacio de nombres donde Laravel organiza los controladores.

Esto permite que Laravel encuentre y organice correctamente el controlador dentro del proyecto.

## 3. Importación de las clases necesarias

```php
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
```

Estas instrucciones permiten utilizar diferentes clases sin tener que escribir su ruta completa cada vez.

- `Cita`: permite consultar las citas registradas.
- `Cliente`: permite consultar y crear registros relacionados con los clientes.
- `Horario`: permite consultar los horarios disponibles.
- `Resena`: permite consultar las reseñas y sus calificaciones.
- `Servicio`: permite consultar los servicios que ofrece el salón.
- `Trabajador`: permite consultar los trabajadores y sus datos.
- `User`: permite consultar los usuarios registrados en el sistema.
- `Carbon`: sirve para trabajar con fechas y horas.
- `Request`: representa las solicitudes HTTP, aunque en este controlador no se utiliza directamente.
- `Auth`: permite identificar al usuario que inició sesión.

## 4. Declaración de la clase

```php
class DashboardController extends Controller
{
```

Aquí se crea la clase `DashboardController`.

La palabra `extends Controller` significa que hereda funcionalidades del controlador principal de Laravel.

Dentro de esta clase se encuentran tres funciones principales:

1. `index()`: decide qué panel debe mostrarse y carga las estadísticas del administrador.
2. `dashboardCliente()`: prepara la información del panel del cliente.
3. `dashboardTrabajador()`: prepara la información del panel del trabajador.

---

# PARTE 1. FUNCIÓN INDEX()

## 5. Identificar al usuario que inició sesión

```php
public function index()
{
    $user = Auth::user();
```

- `public`: permite que la función pueda ser utilizada desde fuera de la clase.
- `function index()`: declara la función que se encarga de mostrar el panel principal.
- `Auth::user()`: obtiene los datos del usuario que inició sesión.
- `$user`: guarda ese usuario en una variable para poder utilizarlo después.

Por ejemplo, esta variable permite consultar el identificador, el nombre y el rol de la persona que está utilizando LashApp.

## 6. Verificar si el usuario es trabajador

```php
if ($user && $user->role_id === 2) {
    return $this->dashboardTrabajador();
}
```

Esta condición comprueba dos cosas:

- `$user`: verifica que exista un usuario autenticado.
- `$user->role_id === 2`: comprueba si el rol del usuario es el número 2, que en este código corresponde al trabajador.

El operador `&&` significa que ambas condiciones deben cumplirse.

El operador `===` compara el valor y también el tipo de dato.

Si se cumplen las condiciones, se ejecuta:

```php
return $this->dashboardTrabajador();
```

Esto significa que el sistema llama a la función `dashboardTrabajador()` y devuelve el resultado de esa función.

**En mis palabras:** primero reviso si la persona que inició sesión es un trabajador. Si lo es, le muestro su panel con la información correspondiente a su rol.

**Importante:** esta comparación supone que `role_id` es un número entero. Si Laravel lo recibe como texto, la condición podría no cumplirse. Se debe comprobar el tipo de dato que maneja el modelo.

## 7. Verificar si el usuario es cliente

```php
if ($user && $user->role_id !== 1) {
    return $this->dashboardCliente();
}
```

Esta condición revisa si existe un usuario y si su rol es diferente de `1`.

En este código, el rol `1` corresponde al administrador.

Si el usuario no es administrador, se llama a la función `dashboardCliente()`.

**En mis palabras:** si el usuario no es trabajador y tampoco tiene el rol de administrador, el sistema lo dirige al panel del cliente.

Hay un detalle importante: la condición considera cliente a cualquier usuario cuyo rol no sea `1`, siempre que no haya entrado antes en la condición del trabajador. Por eso, si existen otros roles, también podrían terminar en el panel del cliente.

Además, si `$user` es `null`, esta condición no se cumple y el código continúa hacia las consultas del administrador. Lo recomendable es proteger esta ruta mediante el sistema de autenticación de Laravel.

## 8. Consultar las estadísticas principales del administrador

```php
$totalUsuarios = User::count();
$totalClientes = Cliente::count();
$totalTrabajadores = Trabajador::where('estado', 'activo')->count();
$totalCitas = Cita::count();
```

Estas líneas consultan la base de datos para obtener las cantidades principales.

- `User::count()`: cuenta todos los usuarios registrados.
- `Cliente::count()`: cuenta los registros de clientes.
- `Trabajador::where('estado', 'activo')->count()`: cuenta solamente los trabajadores cuyo estado sea activo.
- `Cita::count()`: cuenta todas las citas registradas.

### ¿Qué significa `where()`?

`where()` permite establecer una condición en la consulta.

Por ejemplo:

```php
Trabajador::where('estado', 'activo')->count();
```

Significa que solamente se cuentan los trabajadores que tengan el estado `activo`.

### ¿Qué significa `count()`?

`count()` devuelve la cantidad de registros que cumplen las condiciones de la consulta.

Estas cantidades sirven para alimentar las tarjetas de estadísticas del panel administrativo.

## 9. Consultar el resumen del negocio

```php
$citasPendientes = Cita::where('estado', 'pendiente')->count();
$totalServicios = Servicio::where('estado', 'activo')->count();
$totalResenas = Resena::count();
```

Aquí se calculan tres estadísticas adicionales:

- `$citasPendientes`: cuenta las citas cuyo estado sea `pendiente`.
- `$totalServicios`: cuenta los servicios que se encuentran activos.
- `$totalResenas`: cuenta todas las reseñas registradas.

Con esta información, el administrador puede conocer rápidamente cuántas citas están pendientes, cuántos servicios se ofrecen y cuántas reseñas han dejado los clientes.

## 10. Consultar las citas para el panel administrativo

```php
$citasRecientes = Cita::with([
    'cliente.user',
    'trabajador.user',
    'servicio'
])
    ->orderBy('fecha', 'asc')
    ->orderBy('hora_inicio', 'asc')
    ->take(5)
    ->get();
```

Esta consulta obtiene hasta cinco citas y también carga información relacionada con ellas.

### Explicación por partes

**`Cita::with(...)`**

Permite cargar las relaciones de la cita:

- `cliente.user`: obtiene el cliente y los datos de su usuario.
- `trabajador.user`: obtiene el trabajador y los datos de su usuario.
- `servicio`: obtiene la información del servicio reservado.

Esto facilita consultar nombres y servicios sin tener que hacer manualmente cada consulta por separado.

**`orderBy('fecha', 'asc')`**

Ordena las citas por fecha, desde la más antigua hasta la más reciente.

**`orderBy('hora_inicio', 'asc')`**

Si varias citas tienen la misma fecha, las organiza por la hora de inicio, de la más temprana a la más tarde.

**`take(5)`**

Limita el resultado a cinco registros.

**`get()`**

Ejecuta la consulta y obtiene los resultados.

Aunque el comentario habla de citas recientes, esta consulta no filtra las citas pasadas ni ordena por fecha de creación. En realidad, obtiene las cinco citas con las fechas y horas más próximas al inicio del orden establecido, incluidas posibles citas antiguas.

## 11. Traducir los estados de las citas

```php
$estadoMap = [
    'pendiente' => 'Pendiente',
    'confirmado' => 'Confirmada',
    'cancelado' => 'Cancelada',
    'finalizado' => 'Completada',
];
```

Aquí se crea un arreglo asociativo que relaciona el estado guardado en la base de datos con el texto que se mostrará en la interfaz.

Por ejemplo:

- `pendiente` se muestra como `Pendiente`.
- `confirmado` se muestra como `Confirmada`.
- `cancelado` se muestra como `Cancelada`.
- `finalizado` se muestra como `Completada`.

Esto permite que la interfaz muestre los estados con una presentación más clara para el usuario.

## 12. Organizar la información de cada cita

```php
$proximasCitas = $citasRecientes->map(function ($cita) use ($estadoMap) {
```

`map()` recorre la colección de citas y transforma cada registro en un arreglo con los datos necesarios para mostrarlo en el panel.

- `$cita`: representa cada cita que se está procesando.
- `function ($cita)`: define la función que se ejecuta para cada registro.
- `use ($estadoMap)`: permite utilizar dentro de esa función el arreglo de estados que se declaró anteriormente.

### Obtener el nombre del cliente

```php
$clienteNombre = $cita->cliente?->user
    ? trim($cita->cliente->user->name . ' ' . $cita->cliente->user->apellido)
    : 'Cliente';
```

Esta parte intenta obtener el nombre y apellido del cliente.

- `$cita->cliente?->user`: consulta al cliente y su usuario relacionado.
- `?->`: permite acceder a una propiedad solamente si el objeto anterior existe. Si no existe, devuelve `null` en lugar de provocar un error por intentar acceder a una propiedad inexistente.
- `trim()`: elimina los espacios al principio y al final del texto.
- `.`: une cadenas de texto. En este caso, une el nombre, un espacio y el apellido.
- `:`: introduce el resultado alternativo de la condición.

Si existe el usuario del cliente, se muestra su nombre completo. Si no existe, se utiliza el texto `Cliente`.

### Obtener el nombre del trabajador

```php
$trabajadorNombre = $cita->trabajador?->user
    ? trim($cita->trabajador->user->name . ' ' . $cita->trabajador->user->apellido)
    : 'Profesional';
```

Funciona de manera similar al nombre del cliente, pero utiliza la relación del trabajador.

Si encuentra sus datos, une el nombre y el apellido. Si no, utiliza `Profesional` como texto alternativo.

### Construir el arreglo de la cita

```php
return [
    'id' => $cita->id,
    'cliente' => $clienteNombre,
    'servicio' => $cita->servicio?->nombre ?? 'Servicio',
    'profesional' => $trabajadorNombre,
    'fecha_cita' => $cita->fecha ? $cita->fecha->format('Y-m-d') : now()->format('Y-m-d'),
    'hora_inicio' => $cita->hora_inicio,
    'estado' => $estadoMap[$cita->estado] ?? ucfirst($cita->estado),
];
```

Este arreglo organiza los datos que se utilizarán para mostrar cada cita.

- `'id'`: identificador de la cita.
- `'cliente'`: nombre del cliente.
- `'servicio'`: nombre del servicio. Si no existe, muestra `Servicio`.
- `'profesional'`: nombre del trabajador.
- `'fecha_cita'`: fecha de la cita en formato año-mes-día.
- `'hora_inicio'`: hora de inicio.
- `'estado'`: texto que representa el estado de la cita.

**¿Qué significa `??`?**

Es el operador de valor alternativo. Si el valor de la izquierda no existe o es `null`, utiliza el valor de la derecha.

Por ejemplo:

```php
$cita->servicio?->nombre ?? 'Servicio'
```

Si existe el nombre del servicio, lo utiliza. Si no, muestra `Servicio`.

**¿Qué hace `format('Y-m-d')`?**

Convierte la fecha al formato `2026-10-16`, por ejemplo.

**¿Qué hace `now()`?**

Obtiene la fecha y hora actuales mediante Laravel.

En este código, si una cita no tiene fecha, se utiliza la fecha actual como alternativa.

**¿Qué hace `ucfirst()`?**

Convierte en mayúscula la primera letra de un texto. Por ejemplo, `pendiente` pasa a `Pendiente`.

Finalmente:

```php
})->toArray();
```

- `map()` transforma las citas.
- `toArray()` convierte la colección resultante en un arreglo PHP.

## 13. Enviar la información a la vista del administrador

```php
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
```

Esta instrucción devuelve la vista `admin.dashboard`, donde se muestra el panel administrativo.

`compact()` crea un arreglo con las variables que se indican. De esta manera, la vista puede utilizar sus valores.

Por ejemplo, la vista puede mostrar:

- El total de usuarios.
- El total de clientes.
- Los trabajadores activos.
- El total de citas.
- Las citas pendientes.
- Los servicios activos.
- El total de reseñas.
- La lista de citas que se preparó anteriormente.

**En mis palabras:** al terminar las consultas, envío todos los datos a la pantalla del administrador para que pueda visualizar el resumen general de LashApp.

---

# PARTE 2. FUNCIÓN DASHBOARDCLIENTE()

## 14. Identificar al cliente

```php
public function dashboardCliente()
{
    $user = Auth::user();
    $cliente = Cliente::firstOrCreate(['user_id' => $user?->id]);
    $clienteId = $cliente ? $cliente->id : null;
```

Esta función prepara toda la información del panel del cliente.

**`Auth::user()`** obtiene al usuario que inició sesión.

**`Cliente::firstOrCreate(...)`** busca un cliente cuyo `user_id` coincida con el identificador del usuario actual.

- Si encuentra el registro, lo devuelve.
- Si no lo encuentra, intenta crear uno nuevo con ese `user_id`.

**`$user?->id`** obtiene el identificador del usuario si existe.

**`$clienteId`** guarda el identificador del registro del cliente.

Este procedimiento busca asegurar que exista un registro de cliente asociado al usuario. Sin embargo, si no hay un usuario autenticado, la función podría intentar crear un registro con `user_id` nulo, dependiendo de las restricciones de la base de datos. Por eso conviene proteger el acceso a esta función con autenticación.

## 15. Consultar las citas del cliente

```php
$citasQuery = Cita::query();

if ($clienteId) {
    $citasQuery->where('cliente_id', $clienteId);
}
```

Primero se crea una consulta base para la tabla de citas.

`Cita::query()` permite construir una consulta sin ejecutarla inmediatamente.

Después, si existe un identificador de cliente, se agrega el filtro:

```php
where('cliente_id', $clienteId)
```

Esto permite consultar las citas que pertenecen a ese cliente.

Es importante tener en cuenta que, si `$clienteId` no existe, la consulta no recibe ningún filtro y podría terminar contando las citas de todos los clientes. Por eso debe garantizarse que el cliente exista antes de consultar sus datos.

## 16. Calcular las estadísticas de las citas

```php
$totalCitas = $citasQuery->count();

$citasPendientes = (clone $citasQuery)
    ->where('estado', 'pendiente')
    ->count();

$citasCompletadas = (clone $citasQuery)
    ->whereIn('estado', ['confirmado', 'finalizado', 'completado'])
    ->count();
```

Aquí se calculan tres datos importantes para el cliente.

**Total de citas**

```php
$totalCitas = $citasQuery->count();
```

Cuenta las citas que coinciden con la consulta del cliente.

**Citas pendientes**

```php
$citasPendientes = (clone $citasQuery)
    ->where('estado', 'pendiente')
    ->count();
```

Cuenta las citas que tienen el estado `pendiente`.

**Citas completadas**

```php
$citasCompletadas = (clone $citasQuery)
    ->whereIn('estado', ['confirmado', 'finalizado', 'completado'])
    ->count();
```

Cuenta las citas cuyo estado sea alguno de los indicados.

`whereIn()` permite comprobar si un valor pertenece a una lista de opciones.

**¿Para qué se utiliza `clone`?**

`clone` crea una copia de la consulta base. Así, cada cálculo puede agregar sus propios filtros sin modificar la consulta que se utiliza para los demás cálculos.

Un detalle de la lógica es que este código considera `confirmado` como una cita completada. Si en LashApp una cita confirmada todavía está pendiente de realizarse, sería mejor contar como completadas solamente los estados que realmente representen una cita realizada.

## 17. Buscar la próxima cita del cliente

```php
$proximaCitaModel = (clone $citasQuery)
    ->with(['servicio.categoria', 'trabajador.user'])
    ->whereDate('fecha', '>=', Carbon::today())
    ->whereIn('estado', ['pendiente', 'confirmado'])
    ->orderBy('fecha', 'asc')
    ->orderBy('hora_inicio', 'asc')
    ->first();
```

Esta consulta busca la primera cita que cumpla las condiciones indicadas.

### Explicación de las instrucciones

- `clone`: crea una copia de la consulta de citas del cliente.
- `with(['servicio.categoria', 'trabajador.user'])`: carga los datos del servicio, su categoría, el trabajador y su usuario.
- `whereDate('fecha', '>=', Carbon::today())`: busca citas cuya fecha sea igual o posterior al día actual.
- `whereIn('estado', ['pendiente', 'confirmado'])`: solamente considera citas pendientes o confirmadas.
- `orderBy('fecha', 'asc')`: ordena las fechas desde la más cercana.
- `orderBy('hora_inicio', 'asc')`: ordena por hora de inicio.
- `first()`: devuelve solamente el primer resultado que coincida con los filtros.

**En mis palabras:** busco la próxima cita que tenga el cliente, teniendo en cuenta que todavía esté pendiente o confirmada y que su fecha no haya pasado.

## 18. Buscar una cita alternativa si no hay una próxima cita

```php
if (!$proximaCitaModel && $clienteId) {
    $proximaCitaModel = (clone $citasQuery)
        ->with(['servicio.categoria', 'trabajador.user'])
        ->latest('fecha')
        ->first();
}
```

Esta condición se ejecuta cuando no se encuentra una próxima cita y sí existe un identificador de cliente.

- `!$proximaCitaModel`: significa que no se encontró una cita en la consulta anterior.
- `latest('fecha')`: ordena por fecha de manera descendente, desde la más reciente hasta la más antigua.
- `first()`: obtiene la primera cita de ese orden.

Esto permite mostrar una cita anterior como alternativa cuando no hay citas futuras.

Es importante aclarar que, en ese caso, la variable `proximaCitaModel` puede contener una cita pasada. Por eso, el nombre de la variable deja de representar exactamente lo que contiene.

## 19. Preparar los datos de la cita para mostrarla

```php
$proximaCita = null;

if ($proximaCitaModel) {
```

Primero, `$proximaCita` se establece en `null`, lo que significa que inicialmente no hay una cita preparada para mostrar.

Después, si se encontró una cita, se ejecuta el bloque que organiza su información.

### Elegir una imagen predeterminada

```php
$img = asset('img/volumen_ruso.jpg');
```

`asset()` genera la dirección pública de un archivo que se encuentra dentro de la carpeta de recursos públicos del proyecto.

En este caso, se establece una imagen predeterminada para mostrar cuando el servicio no tenga una imagen propia.

### Comprobar si el servicio tiene una imagen

```php
if (!empty($proximaCitaModel->servicio?->imagen_url)) {
    $img = filter_var(
        $proximaCitaModel->servicio->imagen_url,
        FILTER_VALIDATE_URL
    )
        ? $proximaCitaModel->servicio->imagen_url
        : asset($proximaCitaModel->servicio->imagen_url);
}
```

Este bloque revisa si el servicio tiene una imagen registrada.

- `empty()`: comprueba si un valor está vacío.
- `!`: invierte el resultado. En este caso, significa que la imagen no está vacía.
- `filter_var(..., FILTER_VALIDATE_URL)`: comprueba si el texto tiene el formato de una URL válida.
- `? :`: es un operador ternario que elige entre dos resultados.
- Si la imagen es una URL válida, se utiliza directamente.
- Si no lo es, se interpreta como una ruta de un archivo del proyecto y se genera su dirección con `asset()`.

Por ejemplo, puede utilizar una dirección externa o una ruta como `img/servicios/pestanas.jpg`.

### Organizar los datos que se mostrarán

```php
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
```

Este arreglo organiza la información de la cita:

- `id`: identificador.
- `servicio`: nombre del servicio.
- `subservicio`: nombre de la categoría.
- `profesional`: nombre completo del trabajador.
- `fecha_cita`: fecha de la cita.
- `hora_inicio`: hora de inicio.
- `estado`: estado con la primera letra en mayúscula.
- `imagen_url`: dirección de la imagen seleccionada.

Si faltan algunos datos, se utilizan textos alternativos como `Extensiones de Pestañas`, `Pestañas` o `Andrea`.

Estos valores permiten que la pantalla no quede vacía cuando falte información. Sin embargo, si esos nombres no corresponden a los datos reales, sería mejor mostrar un texto neutral como `Servicio`, `Categoría` o `Profesional`.

## 20. Consultar los servicios destacados

```php
$serviciosDestacados = Servicio::with('categoria')
    ->where('estado', 'activo')
    ->take(4)
    ->get();
```

Esta consulta obtiene hasta cuatro servicios activos para mostrarlos como destacados en el panel del cliente.

- `with('categoria')`: carga la categoría de cada servicio.
- `where('estado', 'activo')`: filtra los servicios activos.
- `take(4)`: limita el resultado a cuatro servicios.
- `get()`: ejecuta la consulta y devuelve los registros.

En este código no se especifica un orden para elegir los cuatro servicios, por lo que no necesariamente serán los más populares o los más recientes. Son los primeros cuatro que devuelva la consulta.

## 21. Preparar la actividad reciente

```php
$actividadReciente = [];

if ($clienteId) {
```

Se crea un arreglo vacío para almacenar las actividades recientes del cliente.

Si existe un identificador de cliente, el sistema consulta sus citas y reseñas.

### Consultar las citas recientes

```php
$citasRecientes = Cita::with('servicio')
    ->where('cliente_id', $clienteId)
    ->latest('fecha_creacion')
    ->take(3)
    ->get();
```

Esta consulta obtiene hasta tres citas del cliente, ordenadas por el campo `fecha_creacion`, desde la más reciente hasta la más antigua.

- `with('servicio')`: carga la información del servicio.
- `where('cliente_id', $clienteId)`: limita la búsqueda a ese cliente.
- `latest('fecha_creacion')`: ordena por fecha de creación descendente.
- `take(3)`: limita el resultado a tres registros.
- `get()`: obtiene las citas.

### Recorrer las citas

```php
foreach ($citasRecientes as $cr) {
```

`foreach` recorre cada cita obtenida.

En cada vuelta, `$cr` representa una de las citas.

```php
$actividadReciente[] = [
    'tipo' => 'cita',
    'titulo' => 'Cita ' . ucfirst($cr->estado),
    'desc' => ($cr->servicio?->nombre ?? 'Servicio') . ' - ' .
        ($cr->fecha ? $cr->fecha->translatedFormat('l d \d\e F') : '') .
        ', ' . Carbon::parse($cr->hora_inicio)->format('g:i a'),
    'tiempo' => $cr->fecha_creacion
        ? Carbon::parse($cr->fecha_creacion)->diffForHumans()
        : 'Reciente',
    'icono' => $cr->estado === 'finalizado'
        ? 'fa-regular fa-circle-check'
        : 'fa-regular fa-calendar',
    'color' => $cr->estado === 'finalizado' ? 'green' : 'purple',
];
```

Cada cita se agrega al arreglo de actividad reciente.

- `[]`: agrega un nuevo elemento al final del arreglo.
- `tipo`: identifica la actividad como una cita.
- `titulo`: construye un texto como `Cita Pendiente` o `Cita Finalizado`.
- `desc`: reúne el nombre del servicio, la fecha y la hora.
- `translatedFormat()`: permite mostrar una fecha con nombres traducidos, por ejemplo, el día de la semana y el mes, si la configuración de idioma lo permite.
- `Carbon::parse()`: convierte el valor de fecha u hora en un objeto que Carbon puede manejar.
- `format('g:i a')`: muestra la hora en formato de 12 horas, por ejemplo, `3:30 pm`.
- `diffForHumans()`: presenta el tiempo de forma relativa, como `hace 2 horas` o `hace 3 días`.
- `icono`: asigna un icono según el estado de la cita.
- `color`: asigna `green` si el estado es `finalizado` y `purple` en los demás casos.

Un detalle: el título usa `ucfirst()`, por lo que `finalizado` se mostraría como `Finalizado`, pero los demás textos también dependen de los estados que estén guardados en la base de datos.

### Consultar las reseñas recientes

```php
$resenasRecientes = Resena::where('cliente_id', $clienteId)
    ->latest('fecha_resena')
    ->take(2)
    ->get();
```

Esta consulta obtiene hasta dos reseñas del cliente.

- `where('cliente_id', $clienteId)`: filtra las reseñas por cliente.
- `latest('fecha_resena')`: ordena por fecha de reseña descendente.
- `take(2)`: limita el resultado a dos reseñas.
- `get()`: devuelve los registros.

### Agregar las reseñas a la actividad

```php
foreach ($resenasRecientes as $rr) {
    $actividadReciente[] = [
        'tipo' => 'resena',
        'titulo' => 'Reseña enviada (' . $rr->calificacion . '★)',
        'desc' => \Illuminate\Support\Str::limit($rr->comentario, 60),
        'tiempo' => $rr->fecha_resena
            ? Carbon::parse($rr->fecha_resena)->diffForHumans()
            : 'Reciente',
        'icono' => 'fa-regular fa-star',
        'color' => 'pink',
    ];
}
```

Cada reseña se agrega al mismo arreglo de actividad.

- `tipo`: indica que es una reseña.
- `titulo`: muestra que se envió una reseña y su calificación en estrellas.
- `Str::limit($rr->comentario, 60)`: limita el comentario a 60 caracteres para que no ocupe demasiado espacio en la pantalla.
- `tiempo`: indica cuánto tiempo ha pasado desde la reseña.
- `icono`: asigna un icono de estrella.
- `color`: asigna el color rosa.

La ruta completa `\Illuminate\Support\Str::limit()` permite utilizar la clase `Str` sin tener que importarla al principio del archivo.

En este punto, `$actividadReciente` puede contener hasta cinco elementos: tres citas y dos reseñas.

## 22. Consultar las agendas disponibles

```php
$horarios = Horario::with('trabajador.user')
    ->where('estado', 'activo')
    ->get();
```

Esta consulta obtiene los horarios que tienen el estado `activo`.

También carga la información del trabajador y su usuario mediante `with('trabajador.user')`.

**Importante:** el código filtra por el estado del horario, pero no comprueba si ese horario ya tiene una cita reservada. Por lo tanto, estar activo no garantiza que siga disponible.

### Organizar los horarios

```php
$agendasDisponibles = $horarios->map(function ($h) {
    $trab = $h->trabajador?->user;
    $trabNombre = $trab
        ? trim($trab->name . ' ' . $trab->apellido)
        : 'Lorena Ayala';

    return [
        'id_horario' => $h->id,
        'fecha' => !empty($h->fecha)
            ? Carbon::parse($h->fecha)->toDateString()
            : now()->toDateString(),
        'hora_inicio' => $h->hora_inicio,
        'hora_fin' => $h->hora_fin,
        'trabajador_nombre' => $trabNombre,
        'especialidad' => $h->trabajador?->especialidad
            ?? 'Lash Artist & Brow Designer',
    ];
})->toArray();
```

`map()` recorre los horarios y prepara un arreglo para cada uno.

- `$h`: representa el horario actual.
- `$trab`: guarda el usuario relacionado con el trabajador.
- `$trabNombre`: construye el nombre del trabajador o utiliza un texto alternativo.
- `id_horario`: identificador del horario.
- `fecha`: fecha del horario en formato `AAAA-MM-DD`.
- `hora_inicio`: hora de inicio.
- `hora_fin`: hora de finalización.
- `trabajador_nombre`: nombre del profesional.
- `especialidad`: especialidad del trabajador.

`!empty($h->fecha)` comprueba que la fecha no esté vacía.

`toDateString()` convierte la fecha a un texto con el formato de año, mes y día.

Si no existe una fecha, se utiliza la fecha actual. De igual forma, `Lorena Ayala` y `Lash Artist & Brow Designer` son valores alternativos definidos directamente en el código, no datos necesariamente consultados de la base de datos.

Finalmente, `toArray()` convierte el resultado en un arreglo.

## 23. Consultar los trabajadores activos

```php
$trabajadores = Trabajador::with('user')
    ->where('estado', 'activo')
    ->get()
    ->map(function ($t) {
        return [
            'id_trabajador' => $t->id,
            'nombre_completo' => $t->user
                ? trim($t->user->name . ' ' . ($t->user->apellido ?? ''))
                : 'Especialista',
            'especialidad' => $t->especialidad ?? 'Especialista',
        ];
    })->toArray();
```

Este bloque obtiene los trabajadores activos y prepara sus datos para mostrarlos en un modal o ventana de agenda.

- `with('user')`: carga el usuario relacionado con cada trabajador.
- `where('estado', 'activo')`: filtra los trabajadores activos.
- `get()`: obtiene los registros.
- `map()`: transforma cada trabajador en un arreglo.

Cada trabajador contiene:

- `id_trabajador`: identificador del trabajador.
- `nombre_completo`: nombre y apellido.
- `especialidad`: especialidad registrada.

Si no hay usuario relacionado, se utiliza `Especialista` como nombre alternativo.

## 24. Enviar la información a la vista del cliente

```php
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
```

Esta instrucción muestra la vista `Clientes.dashboardcliente` y le envía todos los datos preparados.

La vista recibe las estadísticas, la próxima cita, los servicios destacados, la actividad reciente, los horarios y los trabajadores activos.

Así, el cliente puede consultar la información de su cuenta desde un solo panel.

---

# PARTE 3. FUNCIÓN DASHBOARDTRABAJADOR()

## 25. Identificar al trabajador

```php
public function dashboardTrabajador()
{
    $user = Auth::user();
    $trabajador = $user?->trabajador
        ?? Trabajador::where('user_id', $user?->id)->first();

    $trabajadorId = $trabajador?->id;
```

Esta función prepara la información del panel del trabajador.

- `Auth::user()`: obtiene el usuario autenticado.
- `$user?->trabajador`: intenta obtener el trabajador mediante la relación definida en el modelo `User`.
- `??`: si esa relación no devuelve un trabajador, busca uno mediante `user_id`.
- `where('user_id', $user?->id)`: filtra por el identificador del usuario.
- `first()`: obtiene el primer registro que coincida.
- `$trabajadorId`: guarda el identificador del trabajador encontrado.

El código utiliza dos formas de encontrar el registro, por si la primera relación no devuelve resultados.

## 26. Comprobar si el trabajador tiene citas asignadas

```php
$tieneCitasAsignadas = ($trabajadorId > 0)
    && Cita::where('trabajador_id', $trabajadorId)->exists();
```

Esta condición comprueba si el identificador del trabajador es mayor que cero y si existe al menos una cita asignada a él.

- `$trabajadorId > 0`: comprueba que el identificador sea mayor que cero.
- `&&`: exige que ambas condiciones sean verdaderas.
- `exists()`: comprueba si la consulta encuentra al menos un registro.

El resultado se guarda en `$tieneCitasAsignadas`, que será verdadero o falso.

### Crear la consulta de citas

```php
$citasQuery = Cita::query();

if ($tieneCitasAsignadas) {
    $citasQuery->where('trabajador_id', $trabajadorId);
}
```

Se crea una consulta base de citas.

Si el trabajador tiene citas asignadas, se filtra la consulta por su identificador.

**Este punto requiere atención:** si el trabajador no tiene citas asignadas, la consulta no se filtra y puede incluir las citas de todos los trabajadores. Lo más seguro sería aplicar el filtro por `trabajador_id` siempre que exista un trabajador identificado, incluso si todavía no tiene citas.

De esa manera, un trabajador nuevo podría ver sus estadísticas en cero sin consultar información de otros profesionales.

## 27. Obtener la fecha de hoy

```php
$hoy = Carbon::today()->toDateString();
```

Esta instrucción obtiene la fecha actual y la convierte en un texto con el formato `AAAA-MM-DD`.

Por ejemplo, podría devolver `2026-10-16`.

La variable `$hoy` se utiliza para consultar las citas programadas para ese día.

## 28. Calcular las estadísticas del trabajador

```php
$totalCitas = (clone $citasQuery)->count();

$citasHoy = (clone $citasQuery)
    ->whereDate('fecha', $hoy)
    ->count();

$citasPendientes = (clone $citasQuery)
    ->where('estado', 'pendiente')
    ->count();

$citasCompletadas = (clone $citasQuery)
    ->whereIn('estado', [
        'confirmado',
        'finalizado',
        'completado',
        'completada',
        'realizada'
    ])
    ->count();

$totalClientes = (clone $citasQuery)
    ->distinct('cliente_id')
    ->count('cliente_id');
```

Estas consultas calculan cinco estadísticas.

**Total de citas**

Cuenta las citas que coinciden con la consulta base.

**Citas de hoy**

`whereDate('fecha', $hoy)` filtra las citas cuya fecha sea la de hoy.

**Citas pendientes**

Cuenta las citas cuyo estado sea `pendiente`.

**Citas completadas**

Cuenta las citas con alguno de los estados indicados. Se incluyen varias formas de escribir los estados para admitir diferentes valores que puedan existir en la base de datos.

**Total de clientes**

`distinct('cliente_id')` busca valores diferentes de identificadores de clientes, y `count('cliente_id')` cuenta los clientes distintos asociados a las citas consultadas.

Esto permite contar a un cliente una sola vez aunque tenga varias citas.

Al igual que antes, `clone` permite que cada cálculo agregue filtros sin alterar la consulta base.

## 29. Consultar las citas que se mostrarán en el panel

```php
$citasRecientes = (clone $citasQuery)
    ->with(['cliente.user', 'servicio', 'trabajador.user'])
    ->orderBy('fecha', 'desc')
    ->orderBy('hora_inicio', 'desc')
    ->take(7)
    ->get();
```

Esta consulta obtiene hasta siete citas y carga las relaciones necesarias.

- `cliente.user`: obtiene el cliente y su usuario.
- `servicio`: obtiene los datos del servicio.
- `trabajador.user`: obtiene el trabajador y su usuario.
- `orderBy('fecha', 'desc')`: ordena las fechas de la más reciente a la más antigua.
- `orderBy('hora_inicio', 'desc')`: ordena las horas de mayor a menor dentro de una misma fecha.
- `take(7)`: limita los resultados a siete.
- `get()`: ejecuta la consulta.

Aunque el comentario habla de próximas citas, el orden descendente puede mostrar primero citas pasadas. Para mostrar realmente las próximas citas, sería necesario filtrar por la fecha actual o posterior y ordenar de forma ascendente.

## 30. Traducir los estados de las citas

```php
$estadoMap = [
    'pendiente' => 'Pendiente',
    'confirmado' => 'Confirmada',
    'confirmada' => 'Confirmada',
    'cancelado' => 'Cancelada',
    'cancelada' => 'Cancelada',
    'finalizado' => 'Completada',
    'completada' => 'Completada',
    'completado' => 'Completada',
    'realizada' => 'Completada',
];
```

Este arreglo relaciona los estados almacenados en la base de datos con los textos que se mostrarán en el panel.

Se incluyen varias formas de escribir algunos estados, por ejemplo `confirmado` y `confirmada`, para que ambos se muestren como `Confirmada`.

## 31. Organizar los datos de cada cita del trabajador

```php
$proximasCitas = $citasRecientes->map(function ($cita) use ($estadoMap) {
```

`map()` recorre las citas y transforma cada una en un arreglo que puede utilizar la vista.

### Obtener el nombre del cliente

```php
$clienteNombre = $cita->cliente?->user
    ? trim($cita->cliente->user->name . ' ' . ($cita->cliente->user->apellido ?? ''))
    : 'Cliente LashApp';
```

Busca el nombre y apellido del cliente.

Si existe el usuario, une sus datos con `trim()`. Si no existe, utiliza `Cliente LashApp`.

### Obtener el nombre del trabajador

```php
$trabajadorNombre = $cita->trabajador?->user
    ? trim($cita->trabajador->user->name . ' ' . $cita->trabajador->user->apellido)
    : 'Profesional';
```

Busca el nombre del profesional relacionado con la cita. Si no encuentra los datos, muestra `Profesional`.

### Construir los datos de la cita

```php
return [
    'id' => $cita->id,
    'cliente' => $clienteNombre,
    'servicio' => $cita->servicio?->nombre ?? 'Servicio',
    'profesional' => $trabajadorNombre,
    'fecha_cita' => $cita->fecha ? $cita->fecha->format('Y-m-d') : now()->format('Y-m-d'),
    'hora_inicio' => $cita->hora_inicio ?? '09:00:00',
    'estado' => $estadoMap[strtolower($cita->estado)] ?? ucfirst($cita->estado),
];
```

Cada cita se transforma en un arreglo con su identificador, cliente, servicio, profesional, fecha, hora y estado.

- `strtolower()`: convierte el texto a minúsculas para facilitar la búsqueda en `$estadoMap`.
- `??`: permite establecer valores alternativos cuando falta un dato.
- `format('Y-m-d')`: organiza la fecha.
- `now()`: proporciona la fecha actual si la cita no tiene fecha.
- `'09:00:00'`: es la hora alternativa cuando no hay una hora de inicio.

Finalmente:

```php
})->toArray();
```

Convierte los datos organizados en un arreglo PHP para utilizarlos en la vista.

## 32. Calcular la calificación promedio

```php
$calificacionQuery = Resena::query();

if ($tieneCitasAsignadas) {
    $calificacionQuery->whereHas('cita', function ($q) use ($trabajadorId) {
        $q->where('trabajador_id', $trabajadorId);
    });
}
```

Primero se crea una consulta sobre las reseñas.

Si el trabajador tiene citas asignadas, se utiliza `whereHas()` para seleccionar las reseñas cuya cita relacionada pertenezca a ese trabajador.

- `whereHas('cita', ...)`: filtra según la existencia de una relación que cumpla una condición.
- `$q`: representa la consulta de la relación `cita`.
- `use ($trabajadorId)`: permite utilizar el identificador del trabajador dentro de la función.

Esto supone que el modelo `Resena` tiene una relación llamada `cita`.

### Obtener el promedio

```php
$calificacionAvg = $calificacionQuery->avg('calificacion');

if (!$calificacionAvg) {
    $calificacionAvg = Resena::avg('calificacion') ?: 4.9;
}

$calificacionPromedio = round($calificacionAvg, 1);
```

**`avg('calificacion')`** calcula el promedio de las calificaciones registradas.

Si no se obtiene un promedio o el resultado es cero, el código intenta calcular el promedio general de todas las reseñas.

Si tampoco hay un promedio general distinto de cero, utiliza `4.9` como valor alternativo.

`round($calificacionAvg, 1)` redondea el resultado a un decimal. Por ejemplo, `4.666` se mostraría como `4.7`.

**Detalle importante:** cuando un trabajador no tiene reseñas, el valor `4.9` puede dar la impresión de que recibió esa calificación. Sería más transparente mostrar `0`, `Sin calificaciones` o `Sin reseñas`, según lo que se quiera presentar en el panel.

Además, la consulta de reseñas solamente se filtra por trabajador si `$tieneCitasAsignadas` es verdadero. Si no tiene citas, el código puede terminar mostrando el promedio de todas las reseñas del negocio.

## 33. Consultar los servicios más demandados

```php
$serviciosPopularesRaw = Servicio::withCount('citas')
    ->where('estado', 'activo')
    ->orderBy('citas_count', 'desc')
    ->take(4)
    ->get();
```

Esta consulta busca los cuatro servicios activos que tienen más citas relacionadas.

- `withCount('citas')`: agrega a cada servicio una cantidad de citas relacionadas, disponible como `citas_count`.
- `where('estado', 'activo')`: filtra los servicios activos.
- `orderBy('citas_count', 'desc')`: ordena desde el servicio con más citas hasta el que tiene menos.
- `take(4)`: limita el resultado a cuatro servicios.
- `get()`: obtiene los resultados.

El resultado se guarda en `$serviciosPopularesRaw`, que contiene los servicios y sus respectivas cantidades de citas.

## 34. Calcular el total de citas del negocio

```php
$totalCitasServicios = Cita::count() ?: 1;
```

Esta línea cuenta todas las citas registradas en el negocio.

El operador `?:` es una forma abreviada de establecer un valor alternativo cuando el resultado es falso.

Si el total de citas es cero, se utiliza `1` para evitar una división entre cero en el cálculo siguiente.

Sin embargo, si el sistema no tiene citas, este valor alternativo sirve solamente para el cálculo y no significa que realmente exista una cita.

## 35. Asignar iconos a los servicios

```php
$iconosServicios = [
    'fa-solid fa-eye',
    'fa-solid fa-wand-magic-sparkles',
    'fa-solid fa-hand-sparkles',
    'fa-solid fa-spa',
];
```

Este arreglo contiene cuatro clases de iconos de Font Awesome.

Cada servicio puede recibir uno de estos iconos para que su representación en la interfaz sea más visual.

## 36. Calcular los porcentajes de popularidad

```php
$serviciosPopulares = $serviciosPopularesRaw->map(function ($s, $idx) use (
    $totalCitasServicios,
    $iconosServicios
) {
    $porcentaje = round(($s->citas_count / $totalCitasServicios) * 100);

    return [
        'nombre' => $s->nombre,
        'porcentaje' => $porcentaje > 0 ? $porcentaje : (85 - ($idx * 15)),
        'icono' => $iconosServicios[$idx % count($iconosServicios)],
    ];
})->toArray();
```

Este bloque transforma cada servicio en un arreglo con su nombre, porcentaje e icono.

### Calcular el porcentaje

```php
$porcentaje = round(($s->citas_count / $totalCitasServicios) * 100);
```

El cálculo divide las citas del servicio entre el total de citas del negocio y multiplica el resultado por 100.

Por ejemplo, si un servicio tiene 20 citas y el negocio tiene 100 citas, el resultado es 20 %.

`round()` redondea el porcentaje a un número entero.

### Asignar un porcentaje alternativo

```php
'porcentaje' => $porcentaje > 0
    ? $porcentaje
    : (85 - ($idx * 15)),
```

Si el porcentaje calculado es mayor que cero, se utiliza ese resultado.

Si es cero, se utiliza un valor alternativo basado en la posición del servicio:

- Primer servicio: 85.
- Segundo servicio: 70.
- Tercer servicio: 55.
- Cuarto servicio: 40.

Estos valores son asignados manualmente para que la interfaz tenga un porcentaje visible cuando el servicio no tenga citas.

Sin embargo, **no representan porcentajes reales de popularidad**. Si se busca mostrar estadísticas verdaderas, sería mejor conservar el cero cuando no haya citas.

### Seleccionar un icono

```php
'icono' => $iconosServicios[$idx % count($iconosServicios)],
```

- `$idx`: indica la posición del servicio dentro del recorrido.
- `count($iconosServicios)`: obtiene la cantidad de iconos disponibles.
- `%`: calcula el residuo de una división.

El operador `%` permite recorrer los iconos de manera circular. Si hubiera más servicios que iconos, los iconos comenzarían a repetirse.

Al terminar, `toArray()` convierte la colección de servicios preparados en un arreglo PHP.

## 37. Enviar los datos a la vista del trabajador

```php
return view('trabajador.dashboardtrabajador', compact(
    'totalCitas',
    'citasHoy',
    'citasPendientes',
    'citasCompletadas',
    'totalClientes',
    'proximasCitas',
    'calificacionPromedio',
    'serviciosPopulares'
));
```

Esta instrucción muestra la vista `trabajador.dashboardtrabajador` y le envía los datos necesarios.

La pantalla puede utilizar:

- El total de citas.
- Las citas programadas para hoy.
- Las citas pendientes.
- Las citas completadas.
- La cantidad de clientes distintos.
- La lista de citas.
- La calificación promedio.
- Los servicios más populares.

De esta manera, el trabajador tiene un resumen de la actividad del negocio que se presenta en su panel.

---

# PARTE 4. CONCEPTOS IMPORTANTES DEL CÓDIGO

## 38. Operadores y funciones que utilizo

| Código | ¿Qué significa? |
|---|---|
| `=` | Asigna un valor a una variable. |
| `==` | Compara dos valores sin exigir que sean del mismo tipo. |
| `===` | Compara dos valores y exige que sean del mismo tipo. |
| `!==` | Comprueba que el valor o el tipo sea diferente. |
| `&&` | Significa “y”; ambas condiciones deben cumplirse. |
| `!` | Invierte una condición. |
| `?->` | Accede a una propiedad o método si el objeto existe. |
| `??` | Utiliza un valor alternativo si el de la izquierda es `null`. |
| `?:` | Devuelve el valor de la izquierda si es verdadero; de lo contrario, el alternativo. |
| `.` | Une cadenas de texto. |
| `%` | Obtiene el residuo de una división. |
| `count()` | Cuenta registros. |
| `where()` | Agrega una condición a una consulta. |
| `whereIn()` | Comprueba si un valor está entre varias opciones. |
| `whereDate()` | Filtra por fecha. |
| `with()` | Carga relaciones entre modelos. |
| `map()` | Recorre y transforma una colección. |
| `foreach` | Recorre los elementos de un arreglo o colección. |
| `clone` | Crea una copia de un objeto, como una consulta. |
| `compact()` | Prepara un arreglo con variables para enviarlas a la vista. |
| `return view()` | Devuelve una vista para mostrarla al usuario. |

## 39. Aspectos que revisaría para mejorar el controlador

Después de estudiar el código, considero importante revisar los siguientes puntos:

1. **Acceso según el rol:** proteger las rutas con autenticación y autorización para que un usuario no pueda entrar a un panel que no le corresponde.
2. **Citas del trabajador:** aplicar siempre el filtro por su identificador, incluso si todavía no tiene citas.
3. **Citas realmente próximas:** ordenar y filtrar las citas futuras, evitando mostrar primero citas antiguas.
4. **Estados de las citas:** mantener los mismos estados en todo el sistema para que las estadísticas sean correctas.
5. **Calificaciones:** evitar mostrar `4.9` como si fuera una calificación real cuando no existen reseñas.
6. **Porcentajes de servicios:** no reemplazar un porcentaje real de cero por valores inventados si el panel pretende mostrar estadísticas reales.
7. **Horarios disponibles:** comprobar si un horario ya tiene una cita reservada antes de presentarlo como disponible.
8. **Datos alternativos:** revisar nombres como `Andrea` y `Lorena Ayala`, y textos como `Extensiones de Pestañas`, para que no se muestren como información real cuando solamente son valores de respaldo.
9. **Cliente autenticado:** garantizar que exista un usuario y un registro de cliente antes de consultar sus citas.
10. **Importaciones:** eliminar `Request` si no se va a utilizar en este controlador.

Estas observaciones no significan que todo el controlador esté mal. Son detalles que conviene revisar para que la información que aparece en LashApp sea más precisa y segura.

---

# Conclusión

En conclusión, el `DashboardController.php` es el encargado de preparar la información de los tres paneles de LashApp. Primero identifica el rol del usuario y después consulta la base de datos para obtener las estadísticas y los datos que necesita cada pantalla.

En el panel administrativo se muestran las estadísticas generales del negocio. En el panel del cliente se consultan sus citas, servicios, actividad y horarios. En el panel del trabajador se presentan sus citas, clientes, calificaciones y los servicios más populares.

Lo más importante que aprendí al revisar este código es que el controlador no se encarga directamente del diseño de la pantalla. Su función principal es obtener, organizar y enviar los datos a las vistas, mientras que las vistas se encargan de presentarlos al usuario.
