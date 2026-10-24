# Explicación del código de TrabajadorController.php

## 1. ¿Para qué sirve este archivo?

Este archivo pertenece a mi proyecto LashApp y se encarga de administrar el perfil de los trabajadores.

Desde aquí puedo mostrar la información personal y profesional del trabajador, consultar estadísticas sobre sus citas, conocer su calificación promedio, actualizar sus datos personales, cambiar su foto de perfil y modificar su contraseña.

En pocas palabras, este controlador permite que cada trabajador pueda gestionar su información desde su propio perfil.

## 2. Importación de las herramientas que voy a utilizar

```php
namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
```

En esta primera parte indico dónde se encuentra mi archivo y preparo las herramientas que necesito.

- `namespace App\Http\Controllers`: indica que mi archivo pertenece a la carpeta de los controladores.
- `use App\Models\Cita`: me permite consultar la información de las citas.
- `use App\Models\Resena`: me permite consultar las reseñas que dejan los clientes.
- `use App\Models\Servicio`: me permite consultar los servicios disponibles.
- `use App\Models\Trabajador`: me permite trabajar con la información profesional del trabajador.
- `use Illuminate\Http\Request`: me permite recibir la información que se envía desde los formularios.
- `use Illuminate\Support\Facades\Auth`: me permite identificar al usuario que tiene la sesión iniciada.
- `use Illuminate\Support\Facades\File`: me permite trabajar con las carpetas y los archivos, por ejemplo, al guardar una foto.
- `use Illuminate\Support\Facades\Hash`: me permite proteger las contraseñas antes de guardarlas.

Con estas herramientas puedo realizar las diferentes acciones que necesita el perfil del trabajador.

## 3. Función perfil(): mostrar el perfil profesional

```php
public function perfil()
{
    $user = Auth::user();
```

Esta función se encarga de mostrar la información del trabajador cuando entra a su perfil.

Primero utilizo `Auth::user()` para obtener la información de la persona que tiene la sesión iniciada. Esa información queda guardada en la variable `$user`.

### Buscar o crear el registro del trabajador

```php
$trabajador = $user?->trabajador ?? Trabajador::firstOrCreate(
    ['user_id' => $user?->id],
    [
        'especialidad' => 'Lash Artist & Brow Designer',
        'descripcion' => 'Especialista en pestañas pelo a pelo, volumen ruso y diseño de cejas.',
        'estado' => 'activo',
    ]
);
```

En esta parte reviso si el usuario ya tiene un registro como trabajador.

- Si ya tiene su registro, utilizo esa información.
- Si no lo tiene, intento crear uno nuevo con una especialidad, una descripción y el estado activo.

La especialidad inicial es `Lash Artist & Brow Designer`, y la descripción indica que se especializa en pestañas y diseño de cejas.

El símbolo `??` permite utilizar la segunda opción cuando la primera no tiene un valor disponible. Por su parte, `?->` permite acceder a una propiedad sin generar un error si el valor anterior es nulo.

### Obtener el identificador del trabajador

```php
$trabajadorId = $trabajador?->id;
```

Aquí guardo el identificador del trabajador en una variable llamada `$trabajadorId`.

Este dato me sirve para buscar únicamente las citas y las reseñas que están relacionadas con ese trabajador.

### Contar el total de citas

```php
$totalCitas = Cita::where('trabajador_id', $trabajadorId)->count();
```

Con esta instrucción cuento todas las citas que tiene asignadas el trabajador.

Por ejemplo, si tiene diez citas registradas, la variable `$totalCitas` tendrá el valor de diez.

### Contar las citas atendidas

```php
$citasAtendidas = Cita::where('trabajador_id', $trabajadorId)
    ->whereIn('estado', [
        'confirmado',
        'finalizado',
        'completado',
        'completada',
        'realizada'
    ])
    ->count();
```

Aquí cuento las citas del trabajador que tienen alguno de los estados indicados.

Utilizo `whereIn` para revisar varios estados al mismo tiempo. De esta manera, el sistema cuenta las citas que se encuentran en alguno de esos estados.

Por ejemplo, si el trabajador tiene diez citas, pero solo seis aparecen con uno de esos estados, el resultado será seis.

### Consultar las reseñas del trabajador

```php
$resenas = Resena::whereHas('cita', function ($q) use ($trabajadorId) {
    $q->where('trabajador_id', $trabajadorId);
})->get();
```

En esta parte busco las reseñas que pertenecen a las citas de ese trabajador.

Primero reviso las citas relacionadas con cada reseña y después selecciono solamente las que corresponden al trabajador que estoy consultando.

Así evito mezclar sus calificaciones con las de otros trabajadores.

### Contar las reseñas y calcular la calificación promedio

```php
$totalResenas = $resenas->count();

$promedioCalificacion = $totalResenas > 0
    ? round($resenas->avg('calificacion'), 1)
    : 5.0;
```

Aquí realizo dos acciones.

- `$totalResenas`: cuenta cuántas reseñas tiene el trabajador.
- `$promedioCalificacion`: calcula el promedio de las calificaciones y lo redondea a un decimal.

Si el trabajador tiene reseñas, se calcula el promedio real. Si todavía no tiene ninguna, el código le asigna el valor de `5.0` como valor inicial.

Por ejemplo, si sus calificaciones son 4, 5 y 3, el promedio será 4.0.

Es importante tener en cuenta que ese 5.0 inicial es un valor predeterminado del código y no una calificación real obtenida de los clientes.

### Contar los servicios activos

```php
$serviciosTotal = Servicio::where('estado', 'activo')->count();
```

Con esta instrucción cuento todos los servicios que tienen el estado activo.

Este conteo corresponde a los servicios activos registrados en el sistema, no solamente a los servicios de un trabajador específico.

### Buscar la próxima cita

```php
$proximaCita = Cita::with(['servicio', 'cliente.user'])
    ->where('trabajador_id', $trabajadorId)
    ->whereIn('estado', ['confirmado', 'confirmada', 'pendiente'])
    ->orderBy('fecha', 'asc')
    ->orderBy('hora_inicio', 'asc')
    ->first();
```

En esta parte busco la próxima cita del trabajador.

- `with(['servicio', 'cliente.user'])`: también obtiene información del servicio y del cliente relacionado con la cita.
- `where('trabajador_id', $trabajadorId)`: busca las citas de ese trabajador.
- `whereIn('estado', ...)`: selecciona las citas que están confirmadas o pendientes, según los estados indicados.
- `orderBy('fecha', 'asc')`: organiza las citas desde la fecha más cercana en adelante.
- `orderBy('hora_inicio', 'asc')`: si varias citas tienen la misma fecha, las organiza por hora.
- `first()`: toma solamente el primer resultado.

Así puedo mostrar una cita próxima en el perfil del trabajador. Sin embargo, el código no compara la fecha y la hora con el momento actual, por lo que para asegurar que sea una cita futura tendría que agregar esa condición.

### Mostrar toda la información en la pantalla

```php
return view('trabajador.perfil.perfil_trabajador', compact(
    'user',
    'trabajador',
    'totalCitas',
    'citasAtendidas',
    'totalResenas',
    'promedioCalificacion',
    'serviciosTotal',
    'proximaCita'
));
```

Finalmente, envío toda la información a la pantalla del perfil profesional.

Con `compact` envío los datos que preparé para que la vista pueda mostrarlos.

Por ejemplo, en el perfil puedo visualizar el nombre del trabajador, el total de citas, las citas atendidas, sus reseñas, la calificación promedio, los servicios activos y la próxima cita encontrada.

## 4. Función updatePerfil(): actualizar los datos personales y profesionales

```php
public function updatePerfil(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:100',
        'apellido' => 'required|string|max:100',
        'telefono' => 'nullable|string|max:20',
        'documento' => 'nullable|string|max:30',
        'especialidad' => 'nullable|string|max:150',
        'descripcion' => 'nullable|string|max:1000',
    ]);
```

Esta función me permite modificar la información personal y profesional del trabajador.

Primero reviso que los datos cumplan con las condiciones establecidas.

- `required`: significa que el campo es obligatorio.
- `string`: indica que el dato debe ser texto.
- `max:100`: establece que el texto puede tener hasta 100 caracteres.
- `nullable`: permite que el campo quede vacío.

Por ejemplo, el nombre y el apellido son obligatorios, mientras que el teléfono, el documento, la especialidad y la descripción pueden quedar vacíos.

### Obtener y actualizar los datos personales

```php
$user = Auth::user();

$user->name = $request->input('name');
$user->apellido = $request->input('apellido');
$user->telefono = $request->input('telefono');
$user->documento = $request->input('documento');
$user->save();
```

Primero identifico al usuario que inició sesión.

Después actualizo su nombre, apellido, teléfono y documento con los datos que recibí del formulario.

Finalmente, utilizo `save()` para guardar los cambios.

### Actualizar la información profesional

```php
$trabajador = $user->trabajador ?? Trabajador::firstOrCreate([
    'user_id' => $user->id
]);

$trabajador->especialidad = $request->input(
    'especialidad',
    $trabajador->especialidad
);

$trabajador->descripcion = $request->input(
    'descripcion',
    $trabajador->descripcion
);

$trabajador->save();
```

En esta parte reviso si el usuario ya tiene un registro de trabajador. Si no lo tiene, intento crearlo.

Después actualizo su especialidad y su descripción.

Si alguno de esos datos no llega en la solicitud, utilizo el valor que ya estaba guardado. Al terminar, guardo los cambios del perfil profesional.

### Mostrar el mensaje de confirmación

```php
return back()->with(
    'success',
    '¡Tus datos de perfil profesional se han actualizado correctamente!'
);
```

Cuando termino de actualizar los datos, regreso a la pantalla anterior y muestro un mensaje para informar que la operación se realizó correctamente.

## 5. Función updateFoto(): actualizar la foto de perfil

```php
public function updateFoto(Request $request)
{
    $request->validate([
        'foto_perfil' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
    ]);
```

Esta función permite cambiar la foto de perfil del trabajador.

Primero reviso que el archivo cumpla con las condiciones establecidas:

- `required`: es obligatorio seleccionar una foto.
- `image`: el archivo debe ser una imagen.
- `mimes:jpeg,png,jpg,webp,gif`: permite los formatos indicados.
- `max:2048`: establece un tamaño máximo de 2048 KB, aproximadamente 2 MB.

Si la imagen no cumple con estas condiciones, no pasa la validación.

### Obtener al usuario y revisar la foto

```php
$user = Auth::user();

if ($request->hasFile('foto_perfil')) {
    $file = $request->file('foto_perfil');
    $dirUpload = public_path('uploads/perfil');
```

Aquí identifico al usuario que inició sesión y reviso si realmente se recibió una foto.

Si el archivo existe, lo guardo temporalmente en la variable `$file` y preparo la ruta donde quiero almacenar las imágenes.

La carpeta que utilizo es `public/uploads/perfil`.

### Crear la carpeta si no existe

```php
if (!File::exists($dirUpload)) {
    File::makeDirectory($dirUpload, 0755, true);
}
```

En esta parte reviso si la carpeta donde guardaré las fotos ya existe.

- `File::exists`: comprueba si la carpeta existe.
- `!`: significa que la condición se cumple cuando la carpeta no existe.
- `File::makeDirectory`: crea la carpeta si hace falta.

De esta manera, preparo el lugar donde se guardará la imagen.

### Crear el nombre de la imagen y guardarla

```php
$fileName = 'trabajador_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();

$file->move($dirUpload, $fileName);
```

Aquí creo un nombre para la imagen utilizando la palabra `trabajador`, el identificador del usuario, la hora actual y la extensión del archivo.

Por ejemplo, el nombre podría quedar así: `trabajador_5_1234567890.jpg`.

Esto ayuda a que las fotos tengan nombres diferentes y evita utilizar directamente el nombre original del archivo.

Después utilizo `move()` para guardar la imagen en la carpeta que preparé.

### Guardar la ruta de la foto en el usuario

```php
$user->foto = 'uploads/perfil/' . $fileName;
$user->save();
```

En esta parte guardo en el usuario la ruta donde quedó almacenada la foto.

Así, el sistema puede utilizar esa ruta para mostrar la imagen en el perfil.

Después guardo los cambios en la base de datos.

### Mostrar el resultado

```php
return back()->with(
    'success',
    '¡Foto de perfil actualizada con éxito!'
);
```

Si todo sale bien, regreso a la pantalla anterior y muestro un mensaje de confirmación.

```php
return back()->with(
    'error',
    'No se pudo subir la foto de perfil.'
);
```

Si no se recibe el archivo, muestro un mensaje indicando que no se pudo subir la foto.

## 6. Función updatePassword(): cambiar la contraseña

```php
public function updatePassword(Request $request)
{
    $request->validate([
        'contrasena_actual' => 'required|string',
        'contrasena_nueva' => 'required|string|min:6',
        'contrasena_confirmar' => 'required|string|same:contrasena_nueva',
    ]);
```

Esta función permite que el trabajador cambie la contraseña de su cuenta.

Primero reviso que los campos cumplan con las condiciones necesarias.

- `contrasena_actual`: es obligatoria porque necesito comprobar la contraseña que tiene actualmente.
- `contrasena_nueva`: es obligatoria y debe tener al menos seis caracteres.
- `contrasena_confirmar`: es obligatoria y debe ser igual a la nueva contraseña.

Esto permite comprobar que el trabajador escribió y confirmó correctamente la contraseña nueva.

### Comprobar la contraseña actual

```php
$user = Auth::user();

if (!Hash::check(
    $request->input('contrasena_actual'),
    $user->password
)) {
    return back()->with(
        'error',
        'La contraseña actual no coincide.'
    );
}
```

Primero identifico al usuario que tiene la sesión iniciada.

Después comparo la contraseña actual que escribió en el formulario con la contraseña guardada en el sistema.

`Hash::check()` me permite realizar esta comparación sin tener que guardar la contraseña original como texto normal.

Si la contraseña no coincide, regreso a la pantalla anterior y muestro un mensaje de error. De esta manera, no continúo con el cambio.

### Guardar la nueva contraseña

```php
$user->password = Hash::make(
    $request->input('contrasena_nueva')
);

$user->save();
```

Si la contraseña actual es correcta, protejo la nueva contraseña y la guardo en la cuenta del trabajador.

Después utilizo `save()` para conservar el cambio en la base de datos.

### Mostrar el mensaje final

```php
return back()->with(
    'success',
    '¡Tu contraseña ha sido actualizada con éxito!'
);
```

Finalmente, regreso a la pantalla anterior y muestro un mensaje que confirma que la contraseña fue actualizada.

## 7. Resumen de las funciones

| Función | ¿Para qué sirve? |
|---|---|
| `perfil()` | Muestra la información del trabajador, sus estadísticas y una cita encontrada como próxima. |
| `updatePerfil()` | Permite modificar los datos personales y profesionales. |
| `updateFoto()` | Permite subir una nueva foto de perfil. |
| `updatePassword()` | Permite cambiar la contraseña comprobando primero la actual. |

## 8. Conclusión

Con este controlador puedo gestionar el perfil profesional de los trabajadores de mi proyecto LashApp.

Puedo mostrar información de sus citas, consultar las reseñas de los clientes, calcular su calificación promedio y conocer los servicios activos registrados en el sistema.

También puedo actualizar sus datos personales, cambiar su especialidad y descripción, subir una foto y modificar su contraseña de forma protegida.

Lo que entendí es que este archivo reúne las funciones necesarias para que el trabajador pueda mantener su perfil actualizado y consultar información importante sobre su actividad dentro de LashApp.