# Explicación detallada del código PagoController.php

## 1. ¿Para qué sirve este controlador?

Este código pertenece a mi proyecto LashApp y se encarga de administrar los pagos que se realizan por las citas de los clientes.

Con este controlador puedo consultar los pagos registrados, agregar pagos nuevos, modificar su información y eliminar los que ya no necesito. También puedo guardar los comprobantes de pago, como imágenes o documentos PDF.

Por ejemplo, si una clienta agenda un servicio de pestañas y realiza el pago por Nequi, desde este controlador puedo registrar cuánto pagó, cuál fue el método de pago, en qué estado se encuentra y guardar el comprobante si lo adjunta.

## 2. Importaciones del código

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Cita;
use App\Models\Comprobante;
use Illuminate\Support\Facades\File;
```

Voy a explicar cada línea:

- `<?php`: indica que voy a escribir código en el lenguaje PHP.
- `namespace App\Http\Controllers;`: indica que mi archivo pertenece a la carpeta de controladores de Laravel.
- `use Illuminate\Http\Request;`: me permite recibir los datos que llegan desde los formularios.
- `use App\Models\Pago;`: me permite trabajar con la información de los pagos guardados en la base de datos.
- `use App\Models\Cita;`: me permite consultar las citas que tienen los clientes.
- `use App\Models\Comprobante;`: me permite guardar y administrar la información de los comprobantes de pago.
- `use Illuminate\Support\Facades\File;`: me permite comprobar si existen carpetas y archivos, crear carpetas y eliminar archivos guardados.

## 3. Creación de la clase

```php
class PagoController extends Controller
{
```

Aquí creo mi controlador con el nombre `PagoController`.

La palabra `extends Controller` significa que mi controlador hereda funciones del controlador principal de Laravel.

Dentro de esta clase escribo las funciones que me permiten consultar, registrar, actualizar y eliminar pagos.

# 4. Función `index()`: mostrar los pagos

```php
public function index()
{
```

Esta función se encarga de consultar la información de los pagos para mostrarla en la pantalla de administración de LashApp.

### 4.1. Consultar los pagos registrados

```php
$pagos = Pago::with([
    'cita.cliente.user',
    'cita.servicio',
    'comprobante'
])
    ->orderByDesc('fecha_pago')
    ->orderByDesc('id')
    ->get();
```

En esta parte consulto todos los pagos que están guardados en la base de datos.

- `$pagos`: es la variable donde guardo los resultados.
- `Pago::`: indica que estoy consultando la información del modelo de pagos.
- `with()`: me permite traer información relacionada con cada pago.
- `'cita.cliente.user'`: permite consultar la cita relacionada con el pago, el cliente de esa cita y los datos de su usuario.
- `'cita.servicio'`: permite consultar el servicio asociado a la cita.
- `'comprobante'`: permite consultar el comprobante relacionado con el pago.
- `orderByDesc('fecha_pago')`: organiza los pagos desde la fecha más reciente hasta la más antigua.
- `orderByDesc('id')`: si hay pagos con la misma fecha, utiliza el identificador para ordenar primero los de mayor número.
- `get()`: obtiene los registros de la base de datos.

**Ejemplo:** si tengo cinco pagos registrados, esta consulta me permite obtenerlos junto con los datos relacionados que necesito para mostrarlos en la pantalla.

### 4.2. Consultar las citas para relacionarlas con los pagos

```php
$citasParaPago = Cita::with([
    'cliente.user',
    'servicio'
])
    ->orderByDesc('id')
    ->get()
    ->map(function ($c) {
```

Esta parte me permite obtener las citas que puedo relacionar con los pagos.

- `$citasParaPago`: guarda la información de las citas.
- `Cita::with()`: consulta las citas junto con los datos de sus clientes y servicios.
- `'cliente.user'`: permite obtener los datos del usuario relacionado con el cliente.
- `'servicio'`: permite obtener los datos del servicio solicitado.
- `orderByDesc('id')`: muestra primero las citas con identificadores más altos.
- `get()`: obtiene todas las citas.
- `map(function ($c) { ... })`: recorre las citas una por una para organizar la información que necesito.

La variable `$c` representa cada cita que estoy procesando.

### 4.3. Obtener el nombre del cliente

```php
$nombreCliente = $c->cliente?->user
    ? ($c->cliente->user->name . ' ' . ($c->cliente->user->apellido ?? ''))
    : 'Cliente #' . $c->cliente_id;
```

Aquí obtengo el nombre del cliente relacionado con la cita.

Primero, compruebo si existe el cliente y si tiene un usuario relacionado.

- `$c->cliente?->user`: comprueba si puedo acceder al usuario del cliente sin intentar leer una propiedad de un valor nulo.
- `?->`: permite acceder a una propiedad o relación solo si el objeto anterior existe.
- `$c->cliente->user->name`: obtiene el nombre del usuario.
- `$c->cliente->user->apellido`: obtiene su apellido.
- `?? ''`: si el apellido no existe, utiliza un texto vacío.
- `.`: une textos para formar un nombre completo.
- `: 'Cliente #' . $c->cliente_id`: si no encuentra el usuario relacionado, muestra un texto como `Cliente #5`, utilizando el identificador del cliente.

**Ejemplo:** si el nombre es Laura y el apellido es Gómez, el resultado sería `Laura Gómez`.

### 4.4. Obtener el nombre del servicio

```php
$nombreServicio = $c->servicio?->nombre ?? 'Servicio General';
```

Con esta línea obtengo el nombre del servicio relacionado con la cita.

Si existe el servicio, utilizo su nombre. Si no existe, muestro `Servicio General`.

El operador `??` sirve para utilizar un valor alternativo cuando el dato anterior es nulo.

### 4.5. Obtener el precio del servicio

```php
$precio = $c->servicio?->precio ?? 0;
```

Aquí obtengo el precio del servicio.

Si el servicio tiene un precio registrado, utilizo ese valor. Si no encuentro el servicio o su precio es nulo, utilizo `0`.

Esto evita que la variable quede sin un valor alternativo.

### 4.6. Organizar la información de cada cita

```php
return [
    'id_cita' => $c->id,
    'cliente' => trim($nombreCliente),
    'servicio' => $nombreServicio,
    'precio' => $precio,
];
```

Con este bloque preparo la información de cada cita.

- `id_cita`: guarda el identificador de la cita.
- `cliente`: guarda el nombre del cliente.
- `trim($nombreCliente)`: elimina los espacios sobrantes al inicio y al final del texto.
- `servicio`: guarda el nombre del servicio.
- `precio`: guarda el precio del servicio.

Finalmente, esta información queda organizada para utilizarla en el formulario donde se registra un pago.

### 4.7. Calcular el dinero recaudado

```php
$totalRecaudado = Pago::where('estado', 'completado')->sum('monto');
```

Esta línea calcula cuánto dinero se ha recaudado según los pagos que tienen el estado `completado`.

- `where('estado', 'completado')`: selecciona únicamente los pagos completados.
- `sum('monto')`: suma los montos de esos pagos.
- `$totalRecaudado`: guarda el resultado.

**Ejemplo:** si tengo tres pagos completados de $50.000, $80.000 y $120.000, el total recaudado sería de $250.000.

### 4.8. Contar los pagos completados

```php
$pagosCompletados = Pago::where('estado', 'completado')->count();
```

Aquí cuento cuántos pagos tienen el estado `completado`.

- `where()`: filtra los pagos por su estado.
- `count()`: cuenta cuántos registros cumplen esa condición.

El resultado me permite mostrar la cantidad de pagos que ya se han completado.

### 4.9. Contar los pagos pendientes

```php
$pagosPendientes = Pago::where('estado', 'pendiente')->count();
```

Esta línea cuenta los pagos que todavía están pendientes.

Por ejemplo, si hay cuatro pagos con ese estado, la variable `$pagosPendientes` tendrá el valor `4`.

### 4.10. Contar todas las transacciones

```php
$totalTransacc = Pago::count();
```

Con esta línea cuento todos los registros que existen en la tabla de pagos, sin importar si están completados, pendientes o rechazados.

El resultado se guarda en `$totalTransacc`.

### 4.11. Enviar la información a la vista

```php
return view('admin.pagos.pagos', compact(
    'pagos',
    'citasParaPago',
    'totalRecaudado',
    'pagosCompletados',
    'pagosPendientes',
    'totalTransacc'
));
```

Al final de esta función envío los datos a la pantalla de administración de pagos.

- `view('admin.pagos.pagos')`: indica qué vista quiero mostrar.
- `compact()`: reúne las variables para que la vista pueda utilizarlas.

De esta manera, la pantalla puede mostrar los pagos, las citas disponibles para asociar, el dinero recaudado y las estadísticas.

# 5. Función `store()`: registrar un pago nuevo

```php
public function store(Request $request)
{
```

Esta función me permite guardar un pago nuevo cuando se envía el formulario.

`Request $request` recibe los datos que la persona escribió o seleccionó en ese formulario.

### 5.1. Validar la información recibida

```php
$request->validate([
    'id_cita' => 'required|exists:citas,id',
    'metodo_pago' => 'required|string|max:50',
    'monto' => 'required|numeric|min:0',
    'estado' => 'required|in:completado,pendiente,rechazado,cancelado,Completado,Pendiente,Cancelado',
    'referencia' => 'nullable|string|max:100',
    'comprobante_nequi' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:4096',
]);
```

Antes de guardar el pago, verifico que la información cumpla las condiciones establecidas.

Voy a explicar cada regla:

- `required`: el campo es obligatorio.
- `exists:citas,id`: comprueba que el identificador de la cita exista en la columna `id` de la tabla `citas`.
- `string`: indica que el dato debe ser texto.
- `max:50`: permite un máximo de 50 caracteres para el método de pago.
- `numeric`: verifica que el monto sea numérico.
- `min:0`: evita que el monto sea negativo.
- `in:...`: permite únicamente los estados indicados en la lista.
- `nullable`: permite dejar el campo vacío.
- `file`: indica que el campo debe contener un archivo.
- `mimes:jpeg,png,jpg,webp,pdf`: permite los formatos de archivo especificados.
- `max:4096`: establece un tamaño máximo de 4096 kilobytes, es decir, aproximadamente 4 MB.

Esta validación ayuda a evitar que se guarden datos incorrectos.

### 5.2. Organizar el estado del pago

```php
$estado = strtolower($request->input('estado', 'completado'));

if ($estado === 'cancelado') {
    $estado = 'rechazado';
}
```

En esta parte obtengo el estado enviado desde el formulario.

- `$request->input('estado', 'completado')`: obtiene el estado. Si no se proporciona, utiliza `completado` como valor predeterminado.
- `strtolower()`: convierte el texto a letras minúsculas.
- `if`: comprueba una condición.
- `$estado === 'cancelado'`: verifica si el estado es `cancelado`.
- `$estado = 'rechazado'`: si está cancelado, lo cambia a `rechazado`.

Por ejemplo, si el formulario envía `Completado`, el código lo convierte en `completado`.

**Importante:** aunque se permite el estado `cancelado` en la validación, después se guarda como `rechazado`.

### 5.3. Crear el nuevo pago

```php
$pago = new Pago();
$pago->cita_id = $request->input('id_cita');
$pago->metodo_pago = $request->input('metodo_pago');
$pago->monto = $request->input('monto');
$pago->estado = $estado;
$pago->fecha_pago = now();
$pago->observaciones = $request->input('referencia');
$pago->save();
```

Aquí creo el registro del pago y guardo sus datos.

- `$pago = new Pago()`: crea un nuevo objeto de pago.
- `$pago->cita_id`: relaciona el pago con la cita seleccionada.
- `$pago->metodo_pago`: guarda el método de pago, como Nequi o transferencia, según las opciones que permita el formulario.
- `$pago->monto`: guarda el dinero registrado.
- `$pago->estado`: guarda el estado que preparé anteriormente.
- `$pago->fecha_pago = now()`: asigna la fecha y hora actuales.
- `$pago->observaciones`: guarda la referencia escrita en el formulario.
- `$pago->save()`: guarda el registro en la base de datos.

Después de guardar, `$pago->id` permite acceder al identificador del pago creado.

### 5.4. Comprobar si se adjuntó un comprobante

```php
if ($request->hasFile('comprobante_nequi')) {
```

Aquí compruebo si la persona adjuntó un archivo en el campo llamado `comprobante_nequi`.

Si no adjuntó ningún archivo, el sistema omite todo el bloque y continúa con la respuesta final.

Si adjuntó uno, comienza el proceso para guardarlo.

### 5.5. Obtener los datos del archivo

```php
$archivo = $request->file('comprobante_nequi');
$extension = $archivo->getClientOriginalExtension();
$nombreOriginal = $archivo->getClientOriginalName();
$nombreGuardar = time() . '_' . uniqid() . '.' . $extension;
```

En estas líneas obtengo la información del archivo que se adjuntó.

- `$archivo`: contiene el archivo recibido.
- `getClientOriginalExtension()`: obtiene su extensión, como `jpg` o `pdf`.
- `getClientOriginalName()`: obtiene el nombre original del archivo.
- `time()`: obtiene el tiempo actual expresado como un número.
- `uniqid()`: genera un identificador único para ayudar a crear un nombre diferente.
- `'.' . $extension`: agrega la extensión al nombre.

Con esto creo un nombre para guardar el archivo y reducir la posibilidad de que dos comprobantes tengan el mismo nombre.

### 5.6. Preparar la carpeta de comprobantes

```php
$destino = public_path('img/comprobantes');

if (!File::isDirectory($destino)) {
    File::makeDirectory($destino, 0755, true, true);
}
```

Aquí indico dónde voy a guardar físicamente los comprobantes.

- `public_path('img/comprobantes')`: obtiene la ruta de la carpeta `img/comprobantes` dentro de la carpeta pública del proyecto.
- `File::isDirectory($destino)`: comprueba si la carpeta existe.
- `!`: significa que estoy comprobando que no exista.
- `File::makeDirectory(...)`: crea la carpeta si hace falta.
- `0755`: establece los permisos de la carpeta en sistemas compatibles.
- `true`: permite crear también las carpetas superiores que hagan falta.
- El último `true`: permite que Laravel no genere un error si la carpeta ya existe en determinadas condiciones de creación.

Así me aseguro de tener una carpeta donde guardar los archivos.

### 5.7. Guardar el archivo

```php
$archivo->move($destino, $nombreGuardar);
```

Con esta línea muevo el archivo recibido a la carpeta de comprobantes y le asigno el nombre que generé anteriormente.

El archivo queda guardado físicamente en el servidor.

### 5.8. Guardar la información del comprobante

```php
Comprobante::create([
    'pago_id' => $pago->id,
    'nombre_archivo' => $nombreOriginal,
    'tipo_archivo' => $extension,
    'ruta_archivo' => 'img/comprobantes/' . $nombreGuardar,
    'fecha_subida' => now(),
]);
```

Después de guardar el archivo, registro su información en la base de datos.

- `Comprobante::create()`: crea un registro nuevo en la tabla de comprobantes.
- `pago_id`: relaciona el comprobante con el pago que acabo de registrar.
- `nombre_archivo`: guarda el nombre original del archivo.
- `tipo_archivo`: guarda la extensión.
- `ruta_archivo`: guarda la ruta donde se encuentra el archivo.
- `fecha_subida`: registra la fecha y hora en que se guardó.

De esta manera, mi sistema puede conocer dónde está el comprobante y a qué pago pertenece.

Para que `create()` funcione, los campos correspondientes deben estar permitidos en la propiedad `$fillable` del modelo `Comprobante` o configurados para permitir su asignación.

### 5.9. Mostrar un mensaje de confirmación

```php
return redirect()->route('pagos.index')
    ->with('success', 'Pago registrado exitosamente.');
```

Cuando termina el registro, regreso a la pantalla de pagos.

- `redirect()`: indica que voy a redirigir al usuario.
- `route('pagos.index')`: me lleva a la ruta que muestra los pagos.
- `with('success', ...)`: envía un mensaje temporal para informar que el registro fue exitoso.

# 6. Función `update()`: actualizar un pago

```php
public function update(Request $request, $id)
{
```

Esta función me permite modificar un pago que ya está registrado.

Recibe dos datos:

- `$request`: contiene la nueva información enviada desde el formulario.
- `$id`: identifica el pago que quiero modificar.

### 6.1. Validar los nuevos datos

```php
$request->validate([
    'metodo_pago' => 'required|string|max:50',
    'monto' => 'required|numeric|min:0',
    'estado' => 'required|in:completado,pendiente,rechazado,cancelado,Completado,Pendiente,Cancelado',
    'referencia' => 'nullable|string|max:100',
    'comprobante_nequi' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:4096',
]);
```

Antes de actualizar, compruebo que los nuevos datos sean válidos.

Las reglas funcionan igual que en la función `store()`, pero aquí no solicito nuevamente `id_cita`, porque el código no cambia la cita relacionada con el pago.

### 6.2. Buscar el pago que voy a modificar

```php
$pago = Pago::findOrFail($id);
```

Con esta línea busco el pago que corresponde al identificador recibido.

- `findOrFail($id)`: busca el registro por su identificador.
- Si lo encuentra, lo guarda en `$pago`.
- Si no lo encuentra, Laravel devuelve un error de tipo 404.

Así evito actualizar un pago que no existe.

### 6.3. Normalizar el estado

```php
$estado = strtolower($request->input('estado', 'completado'));

if ($estado === 'cancelado') {
    $estado = 'rechazado';
}
```

Esta parte funciona igual que en `store()`.

Convierto el estado a minúsculas y, si llega como `cancelado`, lo cambio a `rechazado` antes de guardarlo.

### 6.4. Actualizar la información del pago

```php
$pago->metodo_pago = $request->input('metodo_pago');
$pago->monto = $request->input('monto');
$pago->estado = $estado;

if ($request->filled('referencia')) {
    $pago->observaciones = $request->input('referencia');
}

$pago->save();
```

Aquí modifico los datos del pago.

- `metodo_pago`: actualiza el método de pago.
- `monto`: actualiza el valor del pago.
- `estado`: guarda el nuevo estado.
- `filled('referencia')`: comprueba que el campo tenga un valor.
- `observaciones`: actualiza la referencia si se escribió una nueva.
- `save()`: guarda los cambios en la base de datos.

Hay un detalle importante: si la referencia llega vacía, este código conserva la referencia anterior, porque solamente la actualiza cuando el campo tiene contenido.

### 6.5. Comprobar si se adjuntó un comprobante nuevo

```php
if ($request->hasFile('comprobante_nequi')) {
    $archivo = $request->file('comprobante_nequi');
    $extension = $archivo->getClientOriginalExtension();
    $nombreOriginal = $archivo->getClientOriginalName();
    $nombreGuardar = time() . '_' . uniqid() . '.' . $extension;
```

Si adjunto un comprobante nuevo, el sistema obtiene el archivo, su extensión y su nombre original.

También genera un nombre nuevo para guardarlo, igual que en la función de registro.

### 6.6. Preparar la carpeta y guardar el comprobante nuevo

```php
$destino = public_path('img/comprobantes');

if (!File::isDirectory($destino)) {
    File::makeDirectory($destino, 0755, true, true);
}

$archivo->move($destino, $nombreGuardar);
```

Primero compruebo si existe la carpeta donde guardo los comprobantes.

Si no existe, la creo. Después muevo el nuevo archivo a esa carpeta.

### 6.7. Eliminar los comprobantes anteriores

```php
foreach ($pago->comprobantes as $viejo) {
    if (File::exists(public_path($viejo->ruta_archivo))) {
        File::delete(public_path($viejo->ruta_archivo));
    }

    $viejo->delete();
}
```

Esta parte sirve para reemplazar los comprobantes anteriores cuando adjunto uno nuevo.

- `foreach`: recorre los comprobantes relacionados con el pago.
- `$pago->comprobantes`: obtiene los comprobantes relacionados con ese pago, siempre que la relación esté definida en el modelo.
- `File::exists()`: comprueba si el archivo anterior todavía existe físicamente.
- `File::delete()`: elimina el archivo anterior del servidor.
- `$viejo->delete()`: elimina su registro de la base de datos.

Esto ayuda a evitar que queden comprobantes antiguos relacionados con el pago después de reemplazarlos.

### 6.8. Registrar el nuevo comprobante

```php
Comprobante::create([
    'pago_id' => $pago->id,
    'nombre_archivo' => $nombreOriginal,
    'tipo_archivo' => $extension,
    'ruta_archivo' => 'img/comprobantes/' . $nombreGuardar,
    'fecha_subida' => now(),
]);
```

Después de eliminar los comprobantes anteriores, guardo los datos del nuevo comprobante.

De esta forma, el pago queda relacionado con el archivo que acabo de adjuntar.

### 6.9. Confirmar la actualización

```php
return redirect()->route('pagos.index')
    ->with('success', 'Registro de pago actualizado.');
```

Al finalizar, regreso a la pantalla de pagos y envío un mensaje que informa que el registro fue actualizado.

# 7. Función `destroy()`: eliminar un pago

```php
public function destroy($id)
{
```

Esta función me permite eliminar un pago utilizando su identificador.

### 7.1. Buscar el pago

```php
$pago = Pago::findOrFail($id);
```

Primero busco el pago que quiero eliminar.

Si no existe un registro con ese identificador, Laravel devuelve un error 404.

### 7.2. Eliminar los archivos de los comprobantes

```php
foreach ($pago->comprobantes as $comp) {
    if (File::exists(public_path($comp->ruta_archivo))) {
        File::delete(public_path($comp->ruta_archivo));
    }

    $comp->delete();
}
```

Antes de eliminar el pago, reviso si tiene comprobantes relacionados.

- `foreach`: recorre cada comprobante.
- `$comp`: representa el comprobante que estoy procesando.
- `File::exists()`: comprueba si el archivo existe.
- `File::delete()`: elimina el archivo físico del servidor.
- `$comp->delete()`: elimina el registro del comprobante de la base de datos.

Hago esto para que no queden archivos de comprobantes que ya no se necesitan.

### 7.3. Eliminar el pago

```php
$pago->delete();
```

Después de eliminar los comprobantes relacionados, elimino el registro del pago de la base de datos.

### 7.4. Mostrar el mensaje final

```php
return redirect()->route('pagos.index')
    ->with('success', 'Registro de pago eliminado.');
```

Finalmente, regreso a la pantalla de pagos y muestro un mensaje indicando que el registro fue eliminado.

# 8. Resumen de las funciones principales

| Función | ¿Qué hace? |
|---|---|
| `index()` | Consulta los pagos, las citas y las estadísticas para mostrar la información. |
| `store()` | Registra un pago nuevo y permite adjuntar un comprobante. |
| `update()` | Modifica los datos de un pago y permite reemplazar su comprobante. |
| `destroy()` | Elimina los comprobantes relacionados y el pago. |

# 9. Conclusión

Con este controlador puedo manejar los pagos de mi proyecto LashApp. Aprendí que cada función tiene una tarea diferente: una muestra la información, otra registra pagos, otra permite modificarlos y la última los elimina.

También puedo relacionar los pagos con las citas de los clientes, calcular cuánto dinero se ha recaudado y guardar los comprobantes para tener un mejor control de los registros.

De esta manera, el sistema me ayuda a organizar la información de los pagos y a facilitar su administración desde una sola pantalla.