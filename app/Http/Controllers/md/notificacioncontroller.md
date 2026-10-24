# Explicación del código NotificacionController.php

## 1. ¿Para qué sirve este código?

Este código se encarga de manejar las notificaciones de mi sistema LashApp. Su función es mostrar avisos importantes, como cuando se agenda una cita o se registra un comprobante de pago.

También permite conocer cuántas notificaciones están sin leer y marcar todas las notificaciones como leídas.

Por ejemplo, cuando el administrador entra al sistema, puede consultar las notificaciones recientes y revisar si hay pagos pendientes de verificar o citas nuevas.

## 2. Importaciones del código

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notificacion;
use App\Models\Cita;
use Illuminate\Support\Facades\Auth;
```

Voy a explicar cada línea:

- `<?php`: indica que el archivo contiene código PHP.
- `namespace App\Http\Controllers;`: indica que este archivo pertenece a la carpeta donde se encuentran los controladores de Laravel.
- `use Illuminate\Http\Request;`: permite trabajar con la información que llega desde una solicitud. En este controlador está importado, aunque no se utiliza directamente.
- `use App\Models\Notificacion;`: me permite consultar, crear y actualizar las notificaciones guardadas en la base de datos.
- `use App\Models\Cita;`: me permite consultar las citas registradas en el sistema.
- `use Illuminate\Support\Facades\Auth;`: me permite conocer qué usuario ha iniciado sesión.

## 3. Creación de la clase

```php
class NotificacionController extends Controller
{
```

Con esta línea creo el controlador llamado `NotificacionController`.

La palabra `extends Controller` significa que hereda funciones del controlador principal de Laravel.

Dentro de esta clase escribo las funciones que necesito para consultar y administrar las notificaciones.

## 4. Función `index()`

```php
public function index()
{
```

Esta función se encarga de consultar las notificaciones y devolverlas en un formato que el sistema pueda utilizar.

En este caso, la información se devuelve como JSON, que es una forma organizada de enviar datos entre el servidor y la parte visual de la aplicación.

### 4.1. Obtener el usuario que inició sesión

```php
$user = Auth::user();
```

Con esta línea obtengo la información del usuario que tiene la sesión iniciada.

Por ejemplo, si el administrador ingresó a LashApp, esta variable contiene sus datos.

```php
if (!$user) {
    return response()->json([
        'count' => 0,
        'notificaciones' => []
    ]);
}
```

Aquí compruebo si existe un usuario con la sesión iniciada.

- `if`: significa que voy a comprobar una condición.
- `!$user`: significa que no hay un usuario identificado.
- `return`: termina la función y devuelve una respuesta.
- `response()->json()`: devuelve la información en formato JSON.
- `'count' => 0`: indica que hay cero notificaciones sin leer.
- `'notificaciones' => []`: devuelve una lista vacía.

Esto evita que el código continúe consultando las notificaciones cuando no hay un usuario autenticado.

### 4.2. Crear notificaciones de ejemplo

```php
if (Notificacion::count() === 0) {
    $cita = Cita::first();
    $citaId = $cita ? $cita->id : 1;
```

En esta parte compruebo si la tabla de notificaciones está vacía.

- `Notificacion::count()`: cuenta cuántas notificaciones existen.
- `=== 0`: comprueba que el resultado sea exactamente cero.
- `Cita::first()`: busca la primera cita registrada.
- `$cita ? $cita->id : 1`: si encuentra una cita, utiliza su identificador; si no encuentra ninguna, utiliza el número `1`.

El objetivo de esta parte es preparar una cita para relacionarla con las notificaciones de ejemplo.

**Importante:** estas notificaciones son datos de prueba. No se generan porque haya ocurrido realmente una cita nueva o un pago nuevo, sino porque el código las crea cuando no existe ninguna notificación en la tabla.

### 4.3. Primera notificación de ejemplo

```php
Notificacion::create([
    'user_id' => $user->id,
    'cita_id' => $citaId,
    'tipo' => 'agendada',
    'titulo' => 'Nueva Cita Agendada',
    'mensaje' => 'Valentina Gómez agendó Extensión de Pestañas Volumen Ruso para mañana a las 10:00 AM.',
    'leida' => false,
    'fecha_envio' => now()->subMinutes(15),
]);
```

Con este código creo una notificación de ejemplo que informa sobre una cita agendada.

Cada dato tiene una función:

- `user_id`: guarda el identificador del usuario al que se asigna la notificación.
- `cita_id`: relaciona la notificación con una cita.
- `tipo`: indica la categoría de la notificación; en este caso, `agendada`.
- `titulo`: contiene el título que se mostrará.
- `mensaje`: contiene la descripción del aviso.
- `leida`: indica si la notificación ya fue leída. `false` significa que todavía no.
- `fecha_envio`: guarda la fecha y hora del aviso.

La expresión `now()` obtiene la fecha y hora actuales, mientras que `subMinutes(15)` resta 15 minutos. Por eso, la notificación aparece como si se hubiera enviado hace 15 minutos.

### 4.4. Segunda notificación de ejemplo

```php
Notificacion::create([
    'user_id' => $user->id,
    'cita_id' => $citaId,
    'tipo' => 'info',
    'titulo' => 'Comprobante de Pago Subido',
    'mensaje' => 'Se ha registrado un comprobante Nequi por $120.000 pendiente de revisión.',
    'leida' => false,
    'fecha_envio' => now()->subHours(1),
]);
```

Aquí creo otra notificación de ejemplo relacionada con un comprobante de pago.

- `tipo` tiene el valor `info`, que identifica esta clase de aviso.
- `titulo` indica que se ha subido un comprobante.
- `mensaje` explica que hay un comprobante pendiente de revisión.
- `leida` está en `false`, por lo que aparece como no leído.
- `subHours(1)` hace que la fecha del aviso sea una hora anterior a la actual.

Esta notificación es de prueba y el código no comprueba que el comprobante mencionado exista realmente.

### 4.5. Tercera notificación de ejemplo

```php
Notificacion::create([
    'user_id' => $user->id,
    'cita_id' => $citaId,
    'tipo' => 'agendada',
    'titulo' => 'Nueva Reseña 5★ Recibida',
    'mensaje' => 'Camila Torres dejó una valoración de 5 estrellas: "¡Excelente atención!".',
    'leida' => true,
    'fecha_envio' => now()->subHours(3),
]);
```

Con esta parte creo una tercera notificación que informa sobre una reseña de cinco estrellas.

En este caso, `leida` tiene el valor `true`, lo que significa que la notificación ya aparece como leída.

La expresión `subHours(3)` indica que su fecha de envío corresponde a tres horas antes del momento actual.

Al igual que las anteriores, es una notificación de ejemplo, no una confirmación automática de que un cliente haya dejado esa reseña.

### 4.6. Consultar las últimas diez notificaciones

```php
$notificaciones = Notificacion::orderByDesc('fecha_envio')
    ->take(10)
    ->get()
    ->map(function ($n) {
```

Esta parte consulta las notificaciones que se van a mostrar.

- `Notificacion::`: indica que estoy trabajando con el modelo de notificaciones.
- `orderByDesc('fecha_envio')`: organiza los registros desde la fecha más reciente hasta la más antigua.
- `take(10)`: limita la consulta a diez notificaciones.
- `get()`: obtiene los registros de la base de datos.
- `map(function ($n) { ... })`: recorre las notificaciones y organiza la información de cada una en una estructura más sencilla.

La variable `$n` representa cada notificación que se está procesando.

### 4.7. Organizar la información de cada notificación

```php
return [
    'id' => $n->id,
    'tipo' => $n->tipo,
    'titulo' => $n->titulo,
    'mensaje' => $n->mensaje,
    'leida' => (bool)$n->leida,
    'tiempo' => $n->fecha_envio ? $n->fecha_envio->diffForHumans() : 'Hace un momento',
    'enlace' => $n->tipo === 'info' ? route('pagos.index') : route('citas.index'),
];
```

Aquí preparo los datos que se enviarán a la parte visual de LashApp.

- `id`: identifica la notificación.
- `tipo`: indica qué