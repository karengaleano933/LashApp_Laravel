# Explicación del código ResenaController.php

## 1. ¿Para qué sirve este archivo?

Este archivo se llama `ResenaController.php` y se encarga de manejar las reseñas que los clientes dejan en LashApp sobre los servicios del salón de belleza.

En este archivo puedo consultar las reseñas, ver las calificaciones de los clientes, calcular los promedios, registrar nuevas opiniones y eliminar las reseñas que ya están guardadas.

También permite que el administrador y los trabajadores tengan una vista de las reseñas según su función dentro del sistema.

## 2. Importación de las clases

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Resena;
use App\Models\Cita;
```

En esta parte indico dónde se encuentra mi archivo y qué elementos necesito para que funcione.

- `namespace App\Http\Controllers`: indica que este archivo pertenece a la carpeta de controladores de mi proyecto.
- `use Illuminate\Http\Request`: me permite recibir la información que se envía desde los formularios.
- `use App\Models\Resena`: me permite trabajar con las reseñas que están guardadas en la base de datos.
- `use App\Models\Cita`: me permite consultar las citas que tienen registradas los clientes.

## 3. Inicio de la clase

```php
class ResenaController extends Controller
{
```

Aquí comienzo la clase llamada `ResenaController`.

Esta clase reúne las instrucciones que necesito para manejar las reseñas de mi aplicación. Al utilizar `extends Controller`, aprovecho las funciones que Laravel ofrece para trabajar con los controladores.

## 4. Método index()

```php
public function index()
{
    $user = auth()->user();
```

Este método es el encargado de mostrar la página de las reseñas.

La primera instrucción obtiene los datos del usuario que ha iniciado sesión. De esta manera, puedo saber quién está utilizando el sistema y mostrarle la información que corresponde.

### 4.1. Verificar si el usuario es trabajador

```php
if ($user && (int)$user->role_id === 2) {
```

Aquí verifico si existe un usuario que haya iniciado sesión y si su rol es el número 2, que en mi proyecto corresponde al trabajador.

Si cumple esta condición, el sistema ejecuta las instrucciones destinadas a mostrar la página de reseñas del trabajador.

### 4.2. Buscar al trabajador

```php
$trabajador = $user->trabajador
    ?? \App\Models\Trabajador::where('user_id', $user->id)->first();

$trabajadorId = $trabajador?->id;
```

En esta parte busco los datos del trabajador relacionado con el usuario que inició sesión.

Primero intento encontrarlo mediante la relación que existe entre el usuario y el trabajador. Si no lo encuentro de esa manera, realizo otra búsqueda usando su identificador.

Después guardo el número del trabajador en la variable `$trabajadorId`, porque lo necesito para identificar las reseñas relacionadas con sus citas.

### 4.3. Consultar las reseñas

```php
$query = Resena::with([
    'cliente.user',
    'cita.servicio',
    'cita.trabajador.user'
]);
```

Aquí preparo la consulta de las reseñas y también solicito información relacionada con ellas.

Por ejemplo, puedo obtener el nombre del cliente, el servicio que recibió y los datos del trabajador que atendió la cita.

Esto me ayuda a mostrar información más completa en la página, en lugar de mostrar solamente el número de cada registro.

### 4.4. Mostrar las reseñas relacionadas con el trabajador

```php
if ($trabajadorId) {
    $query->whereHas('cita', function ($q) use ($trabajadorId) {
        $q->where('trabajador_id', $trabajadorId);
    });
}
```

En este bloque verifico si encontré el número del trabajador.

Si lo tengo, busco las reseñas que están relacionadas con las citas que le pertenecen. Así puedo identificar las opiniones que han dejado los clientes sobre sus servicios.

### 4.5. Ordenar las reseñas

```php
$resenas = $query
    ->orderByDesc('fecha_resena')
    ->orderByDesc('id')
    ->get();
```

Aquí obtengo las reseñas y las organizo para mostrar primero las más recientes.

Si varias tienen la misma fecha, utilizo el número de cada reseña para establecer el orden.

### 4.6. Mostrar otras reseñas si no hay ninguna del trabajador

```php
if ($resenas->isEmpty()) {
    $resenas = Resena::with([
        'cliente.user',
        'cita.servicio',
        'cita.trabajador.user'
    ])
    ->orderByDesc('fecha_resena')
    ->orderByDesc('id')
    ->get();
}
```

En esta parte verifico si el trabajador no tiene reseñas relacionadas con sus propias citas.

Si no encuentra ninguna, el sistema muestra las reseñas generales del salón. Esto permite que la página tenga información para mostrar.

Sin embargo, en este caso también pueden aparecer opiniones de otros trabajadores, no solamente las del trabajador que inició sesión.

## 5. Calcular las estadísticas de las reseñas

### 5.1. Contar las reseñas

```php
$totalResenas = $resenas->count();
```

Esta instrucción cuenta cuántas reseñas hay y guarda el resultado en la variable `$totalResenas`.

Por ejemplo, si existen 20 reseñas, la variable tendrá el valor 20.

### 5.2. Calcular el promedio de las calificaciones

```php
$promedioCalificacion = $totalResenas > 0
    ? round($resenas->avg('calificacion'), 1)
    : 5.0;
```

Aquí calculo el promedio de las estrellas que han dejado los clientes.

Si hay reseñas, sumo sus calificaciones y obtengo el promedio. Después lo redondeo a un decimal para que sea más fácil de leer.

Si no hay reseñas, el código establece el promedio en 5.0. Esto es lo que hace actualmente, aunque sería mejor mostrar que todavía no hay calificaciones.

### 5.3. Clasificar las opiniones

```php
$positivas = $resenas->where('calificacion', '>=', 4)->count();
$neutras = $resenas->where('calificacion', 3)->count();
$negativas = $resenas->where('calificacion', '<=', 2)->count();
```

En esta parte clasifico las reseñas según las estrellas que recibieron.

- Las positivas son las que tienen 4 o 5 estrellas.
- Las neutras son las que tienen 3 estrellas.
- Las negativas son las que tienen 1 o 2 estrellas.

Esto me permite conocer cómo están calificando los clientes los servicios del salón.

### 5.4. Contar las estrellas de cada calificación

```php
$c5 = $resenas->where('calificacion', 5)->count();
$c4 = $resenas->where('calificacion', 4)->count();
$c3 = $resenas->where('calificacion', 3)->count();
$c2 = $resenas->where('calificacion', 2)->count();
$c1 = $resenas->where('calificacion', 1)->count();
```

Aquí cuento cuántas reseñas tienen cada cantidad de estrellas.

Por ejemplo, `$c5` guarda la cantidad de reseñas de cinco estrellas, mientras que `$c1` guarda la cantidad de reseñas de una estrella.

Con estos datos puedo mostrar cómo se distribuyen las calificaciones de los clientes.

### 5.5. Calcular el porcentaje de cada calificación

```php
$pct5 = $totalResenas > 0
    ? round(($c5 / $totalResenas) * 100, 1)
    : 0;
```

Esta instrucción calcula qué porcentaje de las reseñas corresponde a las cinco estrellas.

Primero divide la cantidad de reseñas de cinco estrellas entre el total. Después multiplica el resultado por 100 para obtener el porcentaje.

El mismo cálculo se realiza con las variables `$pct4`, `$pct3`, `$pct2` y `$pct1`.

Si no hay reseñas, el porcentaje queda en cero para estas estadísticas del trabajador.

### 5.6. Mostrar la página del trabajador

```php
return view(
    'trabajador.resenas.resenas_trabajador',
    compact(
        'resenas',
        'totalResenas',
        'promedioCalificacion',
        'positivas',
        'neutras',
        'negativas',
        'c5', 'c4', 'c3', 'c2', 'c1',
        'pct5', 'pct4', 'pct3', 'pct2', 'pct1'
    )
);
```

Aquí envío toda la información que calculé a la página de reseñas del trabajador.

Utilizo `compact()` para pasar las variables que la página necesita, como el total de reseñas, el promedio, las cantidades de estrellas y los porcentajes.

De esta manera, el trabajador puede consultar las estadísticas desde su propia vista.

## 6. Mostrar las reseñas del administrador

```php
$resenas = Resena::with(['cliente.user', 'cita.servicio'])
    ->orderByDesc('fecha_resena')
    ->orderByDesc('id')
    ->get();
```

Si el usuario no entra en la condición del trabajador, el código continúa con la consulta que prepara las reseñas para la vista administrativa.

Aquí obtengo las reseñas junto con los datos del cliente y del servicio. Después las organizo desde la más reciente hasta la más antigua.

Esta parte permite preparar la información que se mostrará en la página del administrador.

### 6.1. Calcular las estadísticas del administrador

```php
$totalResenas = $resenas->count();

$promedioCalificacion = $totalResenas > 0
    ? round($resenas->avg('calificacion'), 1)
    : 5.0;
```

Estas instrucciones hacen lo mismo que en la parte del trabajador: cuentan las reseñas y calculan el promedio de las calificaciones.

### 6.2. Contar las estrellas

```php
$c5 = $resenas->where('calificacion', 5)->count();
$c4 = $resenas->where('calificacion', 4)->count();
$c3 = $resenas->where('calificacion', 3)->count();
$c2 = $resenas->where('calificacion', 2)->count();
$c1 = $resenas->where('calificacion', 1)->count();
```

Aquí cuento las reseñas que recibieron una, dos, tres, cuatro y cinco estrellas.

Esta información sirve para conocer las calificaciones que han dejado los clientes en el salón.

### 6.3. Calcular los porcentajes

```php
$pct5 = $totalResenas > 0
    ? round(($c5 / $totalResenas) * 100)
    : 100;

$pct4 = $totalResenas > 0
    ? round(($c4 / $totalResenas) * 100)
    : 0;
```

En esta parte calculo los porcentajes de las calificaciones para mostrarlos en la vista del administrador.

El mismo procedimiento se repite con las tres, dos y una estrella.

Cuando existen reseñas, los porcentajes se redondean a números enteros. Si no existen, el porcentaje de cinco estrellas queda en 100 y los demás en cero. Esto es lo que está