# Explicación detallada del código HorarioController.php

## 1. ¿Para qué sirve este código?

Este controlador se encarga de administrar los horarios de los trabajadores de mi proyecto LashApp. Me permite consultar los horarios registrados, conocer cuáles están disponibles y ocupados, crear nuevos bloques de horario y eliminar los que ya no se necesitan.

Por ejemplo, si una trabajadora va a atender clientes el día 20 de octubre de 2026, puedo registrar la hora de inicio y la hora de finalización para que ese horario quede disponible en el sistema.

También puedo evitar que se elimine un horario que ya tenga citas asignadas, para no afectar las reservas de los clientes.

## 2. Importaciones del código

```php
<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Horario;
use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
```

En esta parte importo las herramientas que necesito para que el controlador funcione.

- `<?php`: indica que el archivo está escrito en PHP.
- `namespace App\Http\Controllers;`: indica que mi archivo pertenece a la carpeta de controladores de Laravel.
- `use App\Models\Cita;`: me permite consultar las citas registradas en el sistema.
- `use App\Models\Horario;`: me permite consultar, crear y eliminar los horarios.
- `use App\Models\Trabajador;`: me permite consultar y crear los registros de los trabajadores.
- `use Carbon\Carbon;`: me permite trabajar con fechas, por ejemplo, conocer la fecha de hoy o identificar próximos horarios.
- `use Illuminate\Http\Request;`: me permite recibir los datos que llegan desde los formularios.
- `use Illuminate\Support\Facades\Auth;`: me permite obtener la información del usuario que inició sesión.

## 3. Creación de la clase

```php
class HorarioController extends Controller
{
```

Aquí creo la clase `HorarioController`, que contiene las funciones encargadas de manejar los horarios.

Con `extends Controller` indico que mi clase hereda las funciones básicas del controlador principal de Laravel.

Dentro de esta clase tengo tres funciones principales: `index()`, `store()` y `destroy()`.

# 4. Función `index()`: mostrar los horarios

```php
public function index()
{
```

Esta función se encarga de consultar y mostrar los horarios del trabajador.

Además, calcula cuántos horarios hay en total, cuántos están disponibles, cuántos están ocupados y cuál es el próximo horario registrado.

### 4.1. Obtener el usuario que inició sesión

```php
$user = Auth::user();
```

Con esta línea obtengo los datos del usuario que tiene la sesión iniciada.

Por ejemplo, si una trabajadora ingresa a su cuenta de LashApp, la variable `$user` contiene su información.

### 4.2. Buscar el registro del trabajador

```php
$trabajador = $user?->trabajador
    ?? Trabajador::where('user_id', $user?->id)->first();
```

Aquí busco el registro del trabajador que está relacionado con el usuario que inició sesión.

- `$user?->trabajador`: intenta obtener el registro del trabajador a través de la relación definida en el usuario.
- `?->`: permite acceder a una propiedad o relación solamente si el objeto anterior existe.
- `??`: si el primer resultado es nulo, utiliza la segunda opción.
- `Trabajador::where('user_id', $user?->id)`: busca un trabajador cuyo `user_id` coincida con el identificador del usuario.
- `first()`: obtiene el primer registro que encuentre.

En otras palabras, primero intento encontrar al trabajador mediante la relación del usuario y, si no lo encuentro, lo busco directamente en la tabla de trabajadores.

### 4.3. Crear un trabajador si todavía no existe

```php
if (!$trabajador && $user && ($user->role_id === 2 || $user->role_id === 1)) {
```

Aquí compruebo tres condiciones:

- `!$trabajador`: significa que todavía no encontré un registro de trabajador.
- `$user`: comprueba que exista un usuario.
- `$user->role_id === 2 || $user->role_id === 1`: comprueba si el rol del usuario es `2` o `1`.

El símbolo `||` significa **o**. Por eso, basta con que el usuario tenga uno de esos dos roles para cumplir esa parte de la condición.

Si se cumplen las condiciones, el código intenta crear el registro del trabajador.

```php
$trabajador = Trabajador::firstOrCreate(
    ['user_id' => $user->id],
    [
        'especialidad' => 'Lash Artist & Brow Designer',
        'descripcion' => 'Especialista en pestañas pelo a pelo, volumen ruso y diseño de cejas.',
        'estado' => 'activo',
    ]
);
```

Con `firstOrCreate()` busco un trabajador que tenga el `user_id` indicado. Si ya existe, lo devuelve; si no existe, crea uno con los datos proporcionados.

Los datos que se utilizan para crear el registro son:

- `user_id`: identifica al usuario relacionado con el trabajador.
- `especialidad`: guarda su especialidad.
- `descripcion`: contiene una descripción de los servicios que realiza.
- `estado`: indica que el trabajador queda registrado como activo.

Esto ayuda a que el sistema tenga un registro de trabajador asociado al usuario.

**Importante:** esta creación automática utiliza una especialidad y una descripción predeterminadas. Además, la comparación estricta `===` funciona correctamente si `role_id` es un número entero; si llega como texto, conviene convertirlo a entero antes de compararlo.

### 4.4. Obtener el identificador del trabajador

```php
$trabajadorId = $trabajador?->id;
```

Aquí guardo el identificador del trabajador en la variable `$trabajadorId`.

Si encontré un trabajador, obtengo su `id`. Si no existe, el resultado será nulo.

Este identificador me sirve para consultar los horarios que le pertenecen.

### 4.5. Preparar la consulta de horarios

```php
$horariosQuery = Horario::withCount('citas');
```

En esta línea comienzo a consultar los horarios.

- `Horario::`: indica que estoy trabajando con el modelo de horarios.
- `withCount('citas')`: permite obtener también la cantidad de citas relacionadas con cada horario.

Por ejemplo, si un horario tiene dos citas relacionadas, Laravel puede incluir el dato `citas_count` con el valor `2`.

Esto me ayuda a identificar si un horario tiene citas asignadas.

### 4.6. Filtrar los horarios del trabajador

```php
if ($trabajadorId) {
    $horariosQuery->where('trabajador_id', $trabajadorId);
}
```

Aquí compruebo si tengo el identificador del trabajador.

Si existe, filtro la consulta para obtener únicamente los horarios cuyo `trabajador_id` coincida con ese identificador.

De esta manera, el sistema intenta mostrar los horarios relacionados con el trabajador que estoy consultando.

### 4.7. Ordenar y obtener los horarios

```php
$horarios = $horariosQuery->orderBy('fecha', 'desc')
    ->orderBy('hora_inicio', 'asc')
    ->get();
```

Con este código organizo los horarios antes de mostrarlos.

- `orderBy('fecha', 'desc')`: ordena las fechas desde las más recientes hasta las más antiguas.
- `orderBy('hora_inicio', 'asc')`: organiza las horas de inicio de menor a mayor dentro del orden establecido por la fecha.
- `get()`: obtiene los registros consultados.

Así puedo mostrar los horarios de forma organizada.

### 4.8. Mostrar horarios generales si no hay horarios propios

```php
if ($horarios->isEmpty()) {
    $horarios = Horario::withCount('citas')
        ->orderBy('fecha', 'desc')
        ->orderBy('hora_inicio', 'asc')
        ->get();
}
```

En esta parte compruebo si la consulta anterior no encontró ningún horario.

- `isEmpty()`: verifica si la colección está vacía.
- Si está vacía, realizo otra consulta para obtener todos los horarios registrados, sin filtrar por trabajador.

La intención es mostrar los horarios generales del salón cuando el trabajador todavía no tiene horarios propios registrados.

**Hay algo importante que debo tener en cuenta:** esta segunda consulta obtiene todos los horarios, aunque pertenezcan a otros trabajadores. Por eso, si cada trabajador debe ver únicamente sus propios horarios, debo revisar esta condición para evitar que se mezclen los horarios.

# 5. Calcular las estadísticas de los horarios

### 5.1. Contar todos los horarios

```php
$totalHorarios = $horarios->count();
```

Con `count()` cuento cuántos horarios hay en la colección que obtuve.

El resultado se guarda en `$totalHorarios` y puede utilizarse para mostrar el total de horarios en la pantalla.

### 5.2. Contar los horarios disponibles

```php
$disponibles = $horarios->filter(function ($h) {
    return in_array(strtolower($h->estado), ['activo', 'disponible'])
        && ($h->citas_count == 0);
})->count();
```

Esta parte cuenta los horarios que cumplen las condiciones para considerarse disponibles.

- `filter()`: revisa cada horario y conserva los que cumplen la condición.
- `$h`: representa cada horario que estoy revisando.
- `$h->estado`: obtiene el estado del horario.
- `strtolower()`: convierte el estado a letras minúsculas.
- `in_array()`: comprueba si el estado está dentro de una lista.
- `['activo', 'disponible']`: son los estados que se aceptan.
- `&&`: significa **y**, por lo que las dos condiciones deben cumplirse.
- `$h->citas_count == 0`: comprueba que el horario no tenga citas relacionadas.
- `count()`: cuenta los horarios que pasaron el filtro.

Por ejemplo, si un horario tiene el estado `activo` y no tiene citas asignadas, se cuenta como disponible.

En cambio, si está activo, pero tiene una cita, no se cuenta como disponible.

### 5.3. Contar los horarios ocupados

```php
$ocupados = $horarios->filter(function ($h) {
    return $h->citas_count > 0
        || strtolower($h->estado) === 'ocupado';
})->count();
```

Aquí cuento los horarios que considero ocupados.

Un horario se cuenta como ocupado cuando cumple al menos una de estas condiciones:

- Tiene una o más citas relacionadas.
- Su estado es `ocupado`.

El símbolo `>` significa mayor que. Por eso, `$h->citas_count > 0` comprueba que exista al menos una cita.

El símbolo `||` significa **o**, así que basta con que una de las condiciones se cumpla.

Después, `count()` cuenta todos los horarios que cumplen alguna de ellas.

### 5.4. Obtener el próximo horario

```php
$proximoHorario = $horarios->filter(function ($h) {
    $fecha = $h->fecha ? Carbon::parse($h->fecha) : null;

    return $fecha && $fecha->greaterThanOrEqualTo(Carbon::today());
})->sortBy('fecha')->first();
```

Esta parte busca el próximo horario a partir de la fecha de hoy.

Primero reviso cada horario y obtengo su fecha.

- `$h->fecha`: contiene la fecha del horario.
- `Carbon::parse($h->fecha)`: convierte esa fecha en un objeto que permite trabajar con ella.
- `: null`: si el horario no tiene fecha, guardo un valor nulo.
- `$fecha && ...`: comprueba que la fecha exista antes de compararla.
- `Carbon::today()`: obtiene la fecha de hoy.
- `greaterThanOrEqualTo()`: comprueba si la fecha es igual o posterior a hoy.
- `sortBy('fecha')`: ordena los horarios encontrados desde la fecha más antigua hasta la más reciente.
- `first()`: obtiene el primer horario de esa lista ordenada.

Así puedo obtener el horario más cercano a la fecha actual, siempre que exista uno hoy o en el futuro.

**Detalle importante:** esta consulta no comprueba si el horario está disponible o si ya tiene citas. Solamente revisa la fecha. Por eso, el próximo horario encontrado podría estar ocupado.

### 5.5. Obtener la fecha de hoy

```php
$hoy = Carbon::today()->toDateString();
```

Con esta línea obtengo la fecha actual y la convierto en un texto con formato de año, mes y día.

Por ejemplo, podría devolver `2026-10-16`.

Guardo el resultado en `$hoy` para enviarlo a la vista. Allí puede utilizarse para comparar fechas o resaltar el día actual.

### 5.6. Enviar la información a la pantalla

```php
return view('trabajador.mi horario.horario_trabajador', compact(
    'horarios',
    'totalHorarios',
    'disponibles',
    'ocupados',
    'proximoHorario',
    'hoy',
    'trabajador'
));
```

Al final de `index()` envío los datos a la vista donde se muestra el horario del trabajador.

- `view('trabajador.mi horario.horario_trabajador')`: indica la vista que quiero mostrar.
- `compact()`: reúne las variables que la vista necesita.

Las variables que envío son:

- `$horarios`: la lista de horarios.
- `$totalHorarios`: la cantidad total de horarios.
- `$disponibles`: la cantidad de horarios disponibles.
- `$ocupados`: la cantidad de horarios ocupados.
- `$proximoHorario`: el próximo horario encontrado.
- `$hoy`: la fecha actual.
- `$trabajador`: los datos del trabajador consultado.

De esta manera, la pantalla puede mostrar tanto la lista de horarios como sus estadísticas.

# 6. Función `store()`: registrar un horario

```php
public function store(Request $request)
{
```

Esta función me permite crear un nuevo bloque de horario para un trabajador.

Recibe los datos enviados desde el formulario mediante `Request $request`.

### 6.1. Validar los datos del formulario

```php
$request->validate([
    'fecha' => 'required|date',
    'hora_inicio' => 'required',
    'hora_fin' => 'required',
    'estado' => 'nullable|string',
]);
```

Antes de guardar el horario, verifico que los datos cumplan las condiciones establecidas.

- `fecha`: debe estar presente y tener un formato de fecha válido.
- `required`: indica que el campo es obligatorio.
- `date`: comprueba que la fecha sea válida.
- `hora_inicio`: es obligatoria.
- `hora_fin`: también es obligatoria.
- `estado`: puede quedar vacío.
- `string`: indica que, si se envía el estado, debe ser texto.

Esta validación revisa que los campos obligatorios estén presentes, pero no comprueba por sí sola que la hora final sea posterior a la hora inicial ni que el bloque tenga una duración válida.

### 6.2. Obtener el usuario y buscar al trabajador

```php
$user = Auth::user();

$trabajador = $user?->trabajador ?? Trabajador::firstOrCreate(
    ['user_id' => $user?->id],
    [
        'especialidad' => 'Lash Artist & Brow Designer',
        'descripcion' => 'Especialista en pestañas pelo a pelo, volumen ruso y diseño de cejas.',
        'estado' => 'activo',
    ]
);
```

Primero obtengo el usuario que inició sesión.

Después busco el trabajador relacionado con ese usuario. Si no lo encuentro mediante la relación, utilizo `firstOrCreate()` para buscarlo por su `user_id` y crearlo si no existe.

Si debo crear el registro, le asigno una especialidad, una descripción y el estado `activo`.

Así consigo el registro del trabajador al que voy a asociar el nuevo horario.

**Importante:** este código supone que existe un usuario autenticado. Si la función puede ejecutarse sin una sesión iniciada, debo validar esa situación antes de intentar crear el trabajador.

### 6.3. Definir el estado del horario

```php
$estado = strtolower($request->input('estado', 'activo'));

if ($estado === 'disponible') {
    $estado = 'activo';
}
```

Aquí obtengo el estado enviado desde el formulario.

- `input('estado', 'activo')`: obtiene el estado; si no se envía, utiliza `activo`.
- `strtolower()`: convierte el texto a minúsculas.
- `if`: comprueba una condición.
- Si el estado es `disponible`, lo cambio a `activo`.

Esto permite guardar los estados de forma más uniforme. Por ejemplo, si el formulario envía `Disponible`, el sistema lo convierte primero a minúsculas y después lo cambia a `activo`.

### 6.4. Guardar el nuevo bloque de horario

```php
Horario::create([
    'trabajador_id' => $trabajador->id,
    'fecha' => $request->input('fecha'),
    'hora_inicio' => $request->input('hora_inicio'),
    'hora_fin' => $request->input('hora_fin'),
    'estado' => $estado,
]);
```

Con esta parte creo un nuevo registro en la tabla de horarios.

Cada campo tiene una función:

- `trabajador_id`: identifica al trabajador al que pertenece el horario.
- `fecha`: guarda el día en que se realizará el horario.
- `hora_inicio`: guarda la hora en que comienza.
- `hora_fin`: guarda la hora en que termina.
- `estado`: guarda el estado que preparé anteriormente.

Por ejemplo, podría registrar un bloque para el 20 de octubre, desde las 9:00 a. m. hasta las 12:00 p. m., con el estado `activo`.

Para que `Horario::create()` funcione, los campos deben estar permitidos en la propiedad `$fillable` del modelo `Horario` o configurados para permitir su asignación.

### 6.5. Mostrar un mensaje de confirmación

```php
return back()->with('success', '¡Bloque de horario creado exitosamente!');
```

Después de guardar el horario, regreso a la página anterior.

- `back()`: devuelve al usuario a la página anterior.
- `with('success', ...)`: envía un mensaje temporal indicando que el horario se creó correctamente.

Así puedo mostrar una confirmación en la pantalla.

# 7. Función `destroy()`: eliminar un horario

```php
public function destroy($id)
{
```

Esta función me permite eliminar un bloque de horario utilizando su identificador.

### 7.1. Buscar el horario

```php
$horario = Horario::findOrFail($id);
```

Con esta línea busco el horario que quiero eliminar.

- `findOrFail($id)`: busca el registro por su identificador.
- Si lo encuentra, lo guarda en `$horario`.
- Si no existe, Laravel devuelve un error 404.

### 7.2. Comprobar si tiene citas asignadas

```php
$citasCount = Cita::where('horario_id', $horario->id)->count();
```

Antes de eliminar el horario, cuento cuántas citas están relacionadas con él.

- `Cita::`: indica que estoy consultando las citas.
- `where('horario_id', $horario->id)`: selecciona las citas cuyo `horario_id` coincide con el identificador del horario.
- `count()`: cuenta cuántas citas cumplen esa condición.

El resultado se guarda en `$citasCount`.

### 7.3. Evitar la eliminación si existen citas

```php
if ($citasCount > 0) {
    return back()->with(
        'error',
        'No se puede eliminar este bloque de horario porque tiene citas asignadas.'
    );
}
```

Aquí compruebo si el horario tiene una o más citas.

- `if`: permite evaluar la condición.
- `$citasCount > 0`: significa que hay al menos una cita asignada.
- `return back()`: regresa a la página anterior.
- `with('error', ...)`: envía un mensaje para explicar por qué no se puede eliminar.

Esto es importante porque evita borrar un horario que todavía está relacionado con reservas de clientes.

### 7.4. Eliminar el horario

```php
$horario->delete();
```

Esta línea elimina el registro del horario de la base de datos.

Solamente se ejecuta cuando la comprobación anterior indica que no hay citas asignadas.

### 7.5. Mostrar el mensaje final

```php
return back()->with(
    'success',
    '¡Bloque de horario eliminado correctamente!'
);
```

Finalmente, regreso a la página anterior y envío un mensaje indicando que el horario se eliminó correctamente.

# 8. Resumen de las funciones

| Función | ¿Qué hace? |
|---|---|
| `index()` | Consulta los horarios y calcula estadísticas de disponibilidad y ocupación. |
| `store()` | Registra un nuevo bloque de horario y lo relaciona con un trabajador. |
| `destroy()` | Comprueba si hay citas relacionadas y elimina el horario únicamente si no las tiene. |

# 9. Conclusión

Con este controlador puedo administrar los horarios de los trabajadores de LashApp. Puedo consultar las fechas y horas registradas, revisar cuántos horarios están disponibles u ocupados, crear nuevos bloques y eliminar aquellos que ya no se necesitan.

También puedo consultar las citas relacionadas con cada horario para evitar eliminar uno que tenga reservas asignadas.

De esta manera, el sistema me ayuda a organizar la disponibilidad de los trabajadores y a llevar un mejor control de los horarios para atender a los clientes.

Además, debo tener en cuenta que es importante validar que las horas sean correctas, evitar horarios duplicados y asegurar que cada trabajador pueda administrar solamente los horarios que le corresponden.