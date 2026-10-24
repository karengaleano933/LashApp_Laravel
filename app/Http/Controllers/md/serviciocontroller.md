# Explicación del código de ServicioController.php

## 1. ¿Para qué sirve este archivo?

Este archivo pertenece a mi proyecto LashApp y se encarga de administrar los servicios que ofrece el salón de belleza. Desde aquí puedo mostrar los servicios registrados, crear nuevos, actualizar su información y eliminarlos cuando sea necesario.

También puedo consultar las categorías, conocer cuántos servicios están activos o inactivos y mostrar cuáles son los más destacados según la cantidad de citas que tienen.

## 2. Importación de las herramientas que voy a utilizar

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Support\Facades\File;
```

En esta primera parte indico dónde se encuentra el controlador y qué herramientas necesito para trabajar.

- `namespace App\Http\Controllers`: indica que el archivo pertenece a la carpeta de controladores.
- `Illuminate\Http\Request`: me permite recibir los datos que se envían desde los formularios.
- `App\Models\Servicio`: me permite consultar, crear, actualizar y eliminar servicios.
- `App\Models\Categoria`: me permite consultar las categorías de los servicios.
- `Illuminate\Support\Facades\File`: me permite trabajar con los archivos, por ejemplo, crear carpetas y eliminar imágenes.

## 3. Función index(): mostrar los servicios

```php
public function index()
{
    $user = auth()->user();
    $servicios = Servicio::with('categoria')->withCount('citas')->get();
    $categorias = Categoria::all();
```

Esta función se encarga de mostrar el listado de servicios registrados en LashApp.

- `$user = auth()->user()`: obtiene la información del usuario que tiene la sesión iniciada.
- `$servicios = Servicio::with('categoria')->withCount('citas')->get()`: consulta todos los servicios, trae la información de sus categorías y cuenta cuántas citas tiene cada servicio.
- `$categorias = Categoria::all()`: consulta todas las categorías registradas.

### Estadísticas de los servicios

```php
$totalServicios = $servicios->count();
$serviciosActivos = Servicio::where('estado', 'activo')->count();
$totalCategorias = $categorias->count();
$serviciosInact = Servicio::where('estado', 'inactivo')->count();
```

En esta parte obtengo los números que puedo mostrar como estadísticas en la pantalla.

- `$totalServicios`: cuenta todos los servicios registrados.
- `$serviciosActivos`: cuenta los servicios que están activos.
- `$totalCategorias`: cuenta todas las categorías existentes.
- `$serviciosInact`: cuenta los servicios que están inactivos.

Esto me permite conocer rápidamente cómo está organizado el catálogo de servicios del salón.

### Consultar los servicios destacados

```php
$serviciosDestacados = Servicio::with('categoria')
    ->withCount('citas')
    ->orderBy('citas_count', 'desc')
    ->take(4)
    ->get();
```

Aquí busco los cuatro servicios que tienen la mayor cantidad de citas.

- `with('categoria')`: trae la categoría de cada servicio.
- `withCount('citas')`: cuenta las citas relacionadas con cada servicio.
- `orderBy('citas_count', 'desc')`: organiza los servicios desde el que tiene más citas hasta el que tiene menos.
- `take(4)`: selecciona solamente cuatro servicios.
- `get()`: obtiene los resultados.

De esta manera, puedo mostrar los servicios más solicitados en la pantalla.

### Mostrar la vista según el tipo de usuario

```php
if ($user && $user->role_id === 2) {
    return view('trabajador.servicios.servicios_trabajador', compact(
        'servicios',
        'categorias',
        'totalServicios',
        'serviciosActivos',
        'totalCategorias',
        'serviciosInact',
        'serviciosDestacados'
    ));
}
```

En esta parte reviso si hay un usuario con la sesión iniciada y si su rol es el número 2, que en mi proyecto corresponde al trabajador.

Si se cumple la condición, envío la información a la pantalla de servicios del trabajador.

```php
return view('admin.servicios.servicios', compact(
    'servicios',
    'categorias',
    'totalServicios',
    'serviciosActivos',
    'totalCategorias',
    'serviciosInact',
    'serviciosDestacados'
));
```

Si no se cumple la condición anterior, envío la información a la vista de servicios del administrador.

La función `compact()` me permite enviar las variables a la vista para que se puedan utilizar y mostrar.

**Importante:** este código selecciona la vista del administrador cuando el usuario no es un trabajador, incluso si no hay una sesión iniciada. La protección del acceso debe estar controlada también mediante las rutas o los permisos del sistema.

## 4. Función store(): registrar un servicio nuevo

```php
public function store(Request $request)
{
    $request->validate([
        'nombre' => 'required|string|max:150',
        'categoria_id' => 'required|exists:categorias,id',
        'precio' => 'required|numeric|min:0',
        'duracion_minuto' => 'required|numeric|min:1',
        'estado' => 'required|in:activo,inactivo',
        'descripcion' => 'nullable|string|max:500',
        'imagen' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
    ]);
```

Esta función se ejecuta cuando envío el formulario para registrar un servicio.

Antes de guardar los datos, valido la información que llega del formulario:

- `nombre`: es obligatorio y puede tener máximo 150 caracteres.
- `categoria_id`: es obligatorio y debe existir en la tabla de categorías.
- `precio`: es obligatorio y debe ser un número igual o mayor que cero.
- `duracion_minuto`: es obligatoria y debe ser un número mayor que cero.
- `estado`: debe ser `activo` o `inactivo`.
- `descripcion`: es opcional y puede tener máximo 500 caracteres.
- `imagen`: es opcional y debe ser una imagen JPG, JPEG, PNG o WEBP de máximo 2048 KB.

Estas validaciones ayudan a evitar que se guarden datos que no cumplen con las condiciones establecidas.

### Guardar los datos del servicio

```php
$servicio = new Servicio();
$servicio->nombre = $request->input('nombre');
$servicio->categoria_id = $request->input('categoria_id');
$servicio->precio = $request->input('precio');
$servicio->descripcion = $request->input('descripcion') ?? '';
$servicio->estado = strtolower($request->input('estado', 'activo'));
```

En esta parte creo un nuevo servicio y le asigno la información que recibo del formulario.

- `new Servicio()`: crea un nuevo registro de servicio.
- `nombre`: guarda el nombre del procedimiento.
- `categoria_id`: guarda la categoría a la que pertenece.
- `precio`: guarda el costo del servicio.
- `descripcion`: guarda la descripción o una cadena vacía si no se proporciona.
- `estado`: guarda el estado en letras minúsculas.

### Convertir la duración del servicio

```php
$minutos = (int) $request->input('duracion_minuto', 30);
$horas = floor($minutos / 60);
$restoMin = $minutos % 60;
$servicio->duracion_minuto = sprintf('%02d:%02d:00', $horas, $restoMin);
```

Aquí convierto la duración que recibo en minutos a un formato de hora, minutos y segundos.

- `$minutos`: obtiene la duración y la convierte a número entero.
- `$horas`: calcula cuántas horas completas tiene la duración.
- `$restoMin`: obtiene los minutos que sobran después de calcular las horas.
- `sprintf()`: organiza los valores en el formato `HH:MM:SS`.

Por ejemplo, si el servicio dura 90 minutos, se guarda como `01:30:00`.

Aunque el campo se llama `duracion_minuto`, en este código el valor se guarda en formato de tiempo, no como un número de minutos.

### Guardar la imagen del servicio

```php
if ($request->hasFile('imagen')) {
    $imagen = $request->file('imagen');
    $nombreImagen = time() . '_' . uniqid() . '.' . $imagen->getClientOriginalExtension();
    $rutaDestino = public_path('img/servicios');
```

En esta parte reviso si se adjuntó una imagen en el formulario.

Si existe, obtengo el archivo, genero un nombre para la imagen y establezco la carpeta donde se guardará.

- `hasFile('imagen')`: verifica si se envió una imagen.
- `file('imagen')`: obtiene el archivo.
- `time()`: agrega la hora actual expresada como marca de tiempo.
- `uniqid()`: genera una parte adicional para ayudar a evitar nombres repetidos.
- `getClientOriginalExtension()`: obtiene la extensión del archivo.
- `public_path()`: indica la ruta de la carpeta pública del proyecto.

```php
if (!File::isDirectory($rutaDestino)) {
    File::makeDirectory($rutaDestino, 0755, true, true);
}

$imagen->move($rutaDestino, $nombreImagen);
$servicio->imagen_url = 'img/servicios/' . $nombreImagen;
```

Aquí verifico si la carpeta existe. Si no existe, la creo.

Después muevo la imagen a esa carpeta y guardo su ruta en `imagen_url`, para poder localizarla y mostrarla posteriormente.

### Guardar el servicio en la base de datos

```php
$servicio->save();

return redirect()->route('servicios.index')
    ->with('success', 'Servicio creado exitosamente.');
```

Con `save()` guardo el servicio en la base de datos.

Después regreso al listado de servicios y envío un mensaje indicando que el registro se creó correctamente.

## 5. Función update(): actualizar un servicio

```php
public function update(Request $request, $id)
{
    $request->validate([
        // Validaciones de los datos
    ]);

    $servicio = Servicio::findOrFail($id);
```

Esta función se utiliza cuando quiero modificar un servicio que ya existe.

Primero valido los datos que llegan del formulario, utilizando las mismas reglas del registro. Después busco el servicio mediante su identificador.

`findOrFail($id)` busca el servicio en la base de datos. Si no lo encuentra, Laravel genera una respuesta de registro no encontrado.

### Actualizar la información

```php
$servicio->nombre = $request->input('nombre');
$servicio->categoria_id = $request->input('categoria_id');
$servicio->precio = $request->input('precio');
$servicio->descripcion = $request->input('descripcion') ?? '';
$servicio->estado = strtolower($request->input('estado', 'activo'));
```

Aquí reemplazo los datos anteriores por los nuevos valores recibidos del formulario.

También vuelvo a convertir la duración al formato `HH:MM:SS`, para mantener la misma estructura que utilizo cuando registro un servicio.

### Reemplazar la imagen anterior

```php
if ($request->hasFile('imagen')) {
    if ($servicio->imagen_url && File::exists(public_path($servicio->imagen_url))) {
        File::delete(public_path($servicio->imagen_url));
    }
```

En esta parte reviso si se envió una imagen nueva.

Si el servicio tiene una imagen anterior guardada en el servidor y el archivo existe, lo elimino para evitar conservar imágenes que ya no se necesitan.

Después genero un nombre para la imagen nueva, verifico que exista la carpeta de destino, guardo el archivo y actualizo la ruta en `imagen_url`.

Si no se envía una imagen nueva, el código conserva la ruta de la imagen que ya tenía el servicio.

### Guardar los cambios

```php
$servicio->save();

return redirect()->route('servicios.index')
    ->with('success', 'Servicio actualizado correctamente.');
```

Finalmente, guardo los cambios en la base de datos y regreso al listado con un mensaje de confirmación.

## 6. Función destroy(): eliminar un servicio

```php
public function destroy($id)
{
    $servicio = Servicio::findOrFail($id);
```

Esta función se encarga de eliminar un servicio del sistema.

Primero busco el servicio mediante su identificador. Si no existe, la operación no puede continuar.

### Eliminar la imagen y el servicio

```php
if ($servicio->imagen_url && File::exists(public_path($servicio->imagen_url))) {
    File::delete(public_path($servicio->imagen_url));
}

$servicio->delete();
```

Antes de eliminar el servicio, reviso si tiene una imagen guardada localmente.

Si el archivo existe, lo elimino del servidor. Después utilizo `delete()` para eliminar el registro del servicio de la base de datos.

**Importante:** si el servicio está relacionado con citas anteriores, debo revisar cómo están configuradas las relaciones de la base de datos. La eliminación podría fallar o afectar los registros relacionados, dependiendo de esas reglas.

### Finalizar la eliminación

```php
return redirect()->route('servicios.index')
    ->with('success', 'Servicio eliminado correctamente.');
```

Cuando termina la operación, regreso al listado y muestro un mensaje indicando que el servicio fue eliminado correctamente.

## 7. Resumen de las funciones

| Función | ¿Para qué sirve? |
|---|---|
| `index()` | Muestra los servicios, categorías, estadísticas y servicios destacados. |
| `store()` | Valida y registra un servicio nuevo, incluyendo su imagen si se adjunta. |
| `update()` | Modifica la información de un servicio y permite reemplazar su imagen. |
| `destroy()` | Elimina la imagen local, si existe, y el registro del servicio. |

## 8. Conclusión

Con este controlador puedo administrar el catálogo de servicios de mi proyecto LashApp. Puedo consultar los servicios y sus categorías, conocer cuáles están activos o inactivos, identificar los más solicitados y registrar nuevos procedimientos con su precio, duración, descripción e imagen.

También puedo actualizar la información cuando sea necesario o eliminar servicios que ya no se ofrezcan.

Lo que entendí es que este controlador conecta los formularios con la base de datos y permite que la información de los servicios se mantenga organizada dentro del sistema.