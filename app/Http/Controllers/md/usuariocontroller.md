# Explicación del código de UsuarioController.php

## 1. ¿Para qué sirve este archivo?

Este archivo pertenece a mi proyecto LashApp y se encarga de administrar los usuarios del sistema. Desde aquí puedo registrar usuarios nuevos, mostrar los que ya existen, actualizar sus datos y eliminarlos cuando sea necesario.

También permite diferenciar a los administradores, trabajadores y clientes, para que cada usuario tenga la información que le corresponde.

## 2. Importación de las herramientas que voy a utilizar

```php
namespace App\Http\Controllers;op

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
```

En esta primera parte estoy indicando dónde se encuentra mi controlador y qué herramientas voy a necesitar.

- `namespace App\Http\Controllers`: indica que este archivo pertenece a la carpeta donde se encuentran los controladores de mi proyecto.
- `use Illuminate\Http\Request`: me permite recibir la información que se envía desde los formularios.
- `use Illuminate\Support\Facades\Hash`: me permite proteger las contraseñas antes de guardarlas.
- `use App\Models\Role`: me permite trabajar con los roles de los usuarios.
- `use App\Models\User`: me permite consultar, registrar, actualizar y eliminar usuarios.

En pocas palabras, aquí preparo las herramientas que necesito para trabajar con los usuarios.

## 3. Función index(): mostrar los usuarios

```php
public function index()
{
    $usuarios = User::with('role')->get();
    $roles = Role::all();
```

Esta función se encarga de mostrar los usuarios que están registrados en el sistema.

- `public function index()`: es la función que utilizo para mostrar el listado de usuarios.
- `$usuarios = User::with('role')->get()`: busca todos los usuarios y también trae la información de sus roles.
- `$roles = Role::all()`: busca todos los roles que existen en el sistema.

### Conteos estadísticos

```php
$usuariosTotales = $usuarios->count();
$usuariosActivos = User::where('estado', 'activo')->count();
$usuariosInactivos = User::where('estado', 'inactivo')->count();
$administradores = User::where('role_id', 1)->count();
$trabajadores = User::where('role_id', 2)->count();
$clientes = User::where('role_id', 3)->count();
```

En esta parte cuento cuántos usuarios hay y cómo están distribuidos.

- `$usuariosTotales`: cuenta todos los usuarios registrados.
- `$usuariosActivos`: cuenta los usuarios que tienen el estado activo.
- `$usuariosInactivos`: cuenta los usuarios que tienen el estado inactivo.
- `$administradores`: cuenta los usuarios que tienen el rol número 1.
- `$trabajadores`: cuenta los usuarios que tienen el rol número 2.
- `$clientes`: cuenta los usuarios que tienen el rol número 3.

Estos números me sirven para mostrar estadísticas en la pantalla de administración.

### Mostrar la información en la vista

```php
return view('admin.gusuarios.gusuarios', compact(
    'usuarios',
    'roles',
    'usuariosTotales',
    'usuariosActivos',
    'usuariosInactivos',
    'administradores',
    'trabajadores',
    'clientes'
));
```

Aquí envío toda la información que reuní a la pantalla de gestión de usuarios.

La palabra `compact` me ayuda a enviar los datos para que la vista pueda utilizarlos y mostrarlos.

Por ejemplo, la pantalla puede mostrar una tabla con los usuarios y unas tarjetas con el total de administradores, trabajadores, clientes y usuarios activos.

## 4. Función create(): abrir el formulario de registro

```php
public function create()
{
    $roles = Role::all();
    return view('admin.gusuarios.create', compact('roles'));
}
```

Esta función se utiliza cuando quiero abrir el formulario para registrar un usuario nuevo.

- Primero busco todos los roles disponibles.
- Después envío esos roles a la pantalla de creación del usuario.

Esto permite que en el formulario aparezcan las opciones para seleccionar si la persona será administradora, trabajadora o cliente.

## 5. Función store(): registrar un usuario nuevo

```php
public function store(Request $request)
{
    $usuario = new User;
```

Esta función se ejecuta cuando envío el formulario de registro.

- `Request $request`: recibe la información que escribí en el formulario.
- `$usuario = new User`: crea un nuevo usuario para empezar a guardar sus datos.

### Guardar los datos del usuario

```php
$usuario->role_id = $request->post('role_id');
$usuario->name = $request->post('name');
$usuario->apellido = $request->post('apellido');
$usuario->documento = $request->post('documento');
$usuario->telefono = $request->post('telefono');
$usuario->email = $request->post('email');
```

En esta parte guardo los datos que la persona escribió en el formulario.

Por ejemplo, el nombre, apellido, documento, teléfono, correo electrónico y el rol que tendrá dentro del sistema.

Cada dato se guarda en el espacio que le corresponde.

### Proteger la contraseña y guardar el estado

```php
$usuario->password = Hash::make($request->post('password'));
$usuario->estado = 'activo';
$usuario->created_by = auth()->id();
$usuario->save();
```

Aquí realizo cuatro acciones importantes:

- `Hash::make(...)`: protege la contraseña antes de guardarla. Así no se almacena directamente como texto normal.
- `$usuario->estado = 'activo'`: establece que el usuario nuevo quedará activo.
- `$usuario->created_by = auth()->id()`: guarda el identificador de la persona que está registrando al usuario, siempre que haya una sesión autenticada.
- `$usuario->save()`: guarda finalmente el usuario en la base de datos.

### Crear el registro de cliente o trabajador

```php
if ($usuario->role_id == 3) {
    \App\Models\Cliente::firstOrCreate([
        'user_id' => $usuario->id
    ]);
}
```

Aquí reviso si el usuario que acabo de registrar tiene el rol número 3, que corresponde al cliente.

Si es así, creo su registro en la tabla de clientes. `firstOrCreate` primero revisa si ya existe ese registro y, si no existe, lo crea.

```php
elseif (in_array($usuario->role_id, [1, 2])) {
    \App\Models\Trabajador::firstOrCreate(
        ['user_id' => $usuario->id],
        [
            'especialidad' => $usuario->role_id == 1
                ? 'Administrador / Máster'
                : 'Lashista Profesional',
            'descripcion' => 'Especialista en belleza de la mirada',
            'estado' => 'activo'
        ]
    );
}
```

En esta otra parte reviso si el usuario tiene el rol 1 o 2.

Si es administrador, le asigno la especialidad `Administrador / Máster`. Si es trabajador, le asigno `Lashista Profesional`.

También guardo una descripción y establezco el estado como activo.

De esta manera, el usuario queda relacionado con el registro de trabajador que utiliza mi sistema.

### Regresar al listado

```php
return redirect()
    ->route('usuarios.index')
    ->with('success', '¡Usuario registrado correctamente!');
```

Cuando termino de registrar al usuario, regreso a la pantalla de gestión de usuarios y envío un mensaje indicando que el registro se realizó correctamente.

## 6. Función show(): consultar un usuario específico

```php
public function show(string $id)
{
    //
}
```

Esta función está preparada para mostrar la información de un usuario específico.

- `$id`: representa el identificador del usuario que quiero consultar.
- `//`: indica que todavía no he escrito las instrucciones para realizar esa consulta.

Por ahora, esta función está vacía y no realiza ninguna acción.

## 7. Función edit(): preparar la edición de un usuario

```php
public function edit(string $id)
{
    //
}
```

Esta función está pensada para abrir el formulario donde puedo editar los datos de un usuario.

El identificador `$id` permite saber qué usuario quiero modificar. Sin embargo, como el contenido está vacío, todavía no realiza esa tarea.

En este código, la actualización se hace directamente mediante la función `update`.

## 8. Función update(): actualizar los datos de un usuario

```php
public function update(Request $request, string $id)
{
    $usuario = User::findOrFail($id);
```

Esta función se utiliza cuando quiero modificar la información de un usuario que ya existe.

- `Request $request`: recibe los datos nuevos del formulario.
- `$id`: identifica al usuario que voy a modificar.
- `User::findOrFail($id)`: busca al usuario por su identificador. Si no lo encuentra, Laravel genera un error de registro no encontrado.

### Modificar los datos personales

```php
$usuario->name = $request->input('name');
$usuario->apellido = $request->input('apellido');
$usuario->documento = $request->input('documento');
$usuario->telefono = $request->input('telefono');
$usuario->email = $request->input('email');
```

Aquí reemplazo los datos anteriores por los nuevos valores que recibo del formulario.

Por ejemplo, si una persona cambia su número de teléfono, puedo actualizarlo sin tener que crear otro usuario.

### Cambiar la contraseña si es necesario

```php
if ($request->filled('password')) {
    $usuario->password = Hash::make($request->input('password'));
}
```

En esta parte reviso si se escribió una contraseña nueva.

Si el campo tiene información, la protejo y la guardo. Si el campo está vacío, con este código conservo la contraseña que ya tenía el usuario.

### Actualizar el rol y el estado

```php
$usuario->role_id = $request->input('role_id');
$usuario->estado = strtolower($request->input('estado', 'activo'));
$usuario->save();
```

Aquí puedo cambiar el rol del usuario y su estado.

- `role_id`: guarda el rol seleccionado.
- `strtolower(...)`: convierte el estado a letras minúsculas.
- `'activo'`: es el valor que se utiliza si no se recibe un estado en el formulario.
- `save()`: guarda los cambios en la base de datos.

Por ejemplo, si recibo el estado `INACTIVO`, se guarda como `inactivo`.

### Sincronizar el registro de cliente o trabajador

```php
if ($usuario->role_id == 3) {
    \App\Models\Cliente::firstOrCreate([
        'user_id' => $usuario->id
    ]);
    \App\Models\Trabajador::where('user_id', $usuario->id)->delete();
}
```

Si cambio el rol del usuario a cliente, reviso que tenga su registro en la tabla de clientes.

También elimino su registro de trabajador, porque ya no tendría ese tipo de rol.

```php
elseif (in_array($usuario->role_id, [1, 2])) {
    \App\Models\Trabajador::firstOrCreate(
        ['user_id' => $usuario->id],
        [
            'especialidad' => $usuario->role_id == 1
                ? 'Administrador / Máster'
                : 'Lashista Profesional',
            'descripcion' => 'Especialista en belleza de la mirada',
            'estado' => 'activo'
        ]
    );
}
```

Si el usuario pasa a ser administrador o trabajador, reviso que tenga su registro en la tabla de trabajadores.

Si no existe, lo creo con la especialidad que corresponda.

### Finalizar la actualización

```php
return redirect()
    ->route('usuarios.index')
    ->with('success', 'Usuario actualizado exitosamente');
```

Después de guardar los cambios, regreso al listado de usuarios y muestro un mensaje para informar que la actualización terminó correctamente.

## 9. Función destroy(): eliminar un usuario

```php
public function destroy(string $id)
{
    $usuario = User::findOrFail($id);
```

Esta función se encarga de eliminar un usuario del sistema.

Primero busco al usuario utilizando su identificador. Si no existe, no puedo continuar con la eliminación.

### Evitar que el administrador se elimine a sí mismo

```php
if (auth()->id() == $usuario->id) {
    return back()->with(
        'error',
        'No puedes eliminar tu propia cuenta de administrador.'
    );
}
```

Esta condición revisa si el usuario que quiero eliminar es el mismo que tiene la sesión iniciada.

Si ambos identificadores son iguales, cancelo la operación y regreso a la pantalla anterior con un mensaje de error.

Esto evita que el administrador elimine su propia cuenta mientras la está utilizando.

### Eliminar los registros relacionados

```php
\App\Models\Cliente::where('user_id', $usuario->id)->delete();
\App\Models\Trabajador::where('user_id', $usuario->id)->delete();
$usuario->delete();
```

Aquí realizo la eliminación en tres pasos:

1. Elimino el registro relacionado con el cliente, si existe.
2. Elimino el registro relacionado con el trabajador, si existe.
3. Elimino al usuario de la tabla principal.

Hago esto para evitar que queden registros relacionados con un usuario que ya no existe.

**Importante:** estas instrucciones eliminan los registros de cliente y trabajador de forma permanente. Si esas tablas tienen información importante relacionada con citas, pagos u otros datos, debo revisar las relaciones antes de eliminarlos.

### Regresar a la pantalla principal

```php
return redirect()
    ->route('usuarios.index')
    ->with('success', 'Usuario eliminado exitosamente');
```

Finalmente, regreso al listado de usuarios y muestro un mensaje que indica que el usuario fue eliminado correctamente.

## 10. Resumen de las funciones

| Función     | ¿Para qué sirve?                                                                                      |
| ----------- | ----------------------------------------------------------------------------------------------------- |
| `index()`   | Muestra los usuarios y las estadísticas.                                                              |
| `create()`  | Abre el formulario para registrar un usuario.                                                         |
| `store()`   | Registra un usuario nuevo y crea su registro de cliente o trabajador.                                 |
| `show()`    | Está preparada para consultar un usuario específico, pero está vacía.                                 |
| `edit()`    | Está preparada para editar un usuario, pero está vacía.                                               |
| `update()`  | Modifica los datos, el rol, el estado y, si corresponde, la contraseña.                               |
| `destroy()` | Elimina al usuario y sus registros relacionados, evitando que el administrador se elimine a sí mismo. |

## 11. Conclusión

Con este controlador puedo administrar los usuarios de mi proyecto LashApp. Puedo consultar quiénes están registrados, contar los usuarios activos e inactivos, registrar nuevas personas, modificar sus datos y eliminar usuarios cuando sea necesario.

También puedo diferenciar a los administradores, trabajadores y clientes, para que cada uno tenga su información correspondiente.

Lo más importante que entendí es que este archivo se encarga de recibir las solicitudes, trabajar con la información de los usuarios y enviar los resultados a las pantallas del sistema.
