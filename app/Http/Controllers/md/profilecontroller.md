# Explicación del código ProfileController.php

## 1. ¿Para qué sirve este archivo?

Este archivo se llama `ProfileController.php` y se encarga de manejar algunas funciones relacionadas con el perfil de los usuarios de LashApp.

Con este archivo puedo mostrar la información del perfil, permitir que el usuario actualice sus datos y darle la opción de eliminar su cuenta.

También permite que cada usuario llegue a la página de perfil que le corresponde según su rol dentro del sistema.

## 2. Importación de las clases

```php
namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
```

En esta parte indico dónde se encuentra el archivo y qué elementos necesito para realizar las acciones del perfil.

- `namespace App\Http\Controllers`: indica que este archivo pertenece a la carpeta de controladores.
- `ProfileUpdateRequest`: me permite revisar que los datos enviados para actualizar el perfil cumplan las condiciones establecidas.
- `RedirectResponse`: indica que algunas funciones devuelven una redirección hacia otra página.
- `Request`: me permite recibir la información de la solicitud que realiza el usuario.
- `Auth`: me permite manejar acciones relacionadas con el inicio y el cierre de sesión.
- `Redirect`: me permite enviar al usuario a otra página después de realizar una acción.
- `View`: sirve para indicar que una función devuelve una página, aunque en este código no se utiliza directamente en los métodos.

## 3. Inicio de la clase

```php
class ProfileController extends Controller
{
```

Aquí comienzo la clase llamada `ProfileController`.

Dentro de ella organizo las instrucciones que necesito para mostrar, actualizar y eliminar la cuenta de un usuario.

Al utilizar `extends Controller`, aprovecho las funciones que Laravel ofrece para trabajar con los controladores.

## 4. Método edit(): mostrar el perfil

```php
public function edit(Request $request)
{
    $user = $request->user();
```

Este método se encarga de mostrar la página del perfil.

Primero obtengo los datos del usuario que ha iniciado sesión y los guardo en la variable `$user`.

De esta manera, el sistema puede saber quién está ingresando y decidir qué página debe mostrarle.

### 4.1. Revisar si el usuario es trabajador

```php
if ($user && (int)$user->role_id === 2) {
    return redirect()->route('trabajador.perfil');
}
```

Aquí verifico si existe un usuario y si su rol es el número 2, que corresponde al trabajador en mi proyecto.

Si se cumple esa condición, lo envío a la página de perfil del trabajador mediante la ruta `trabajador.perfil`.

Esto permite que el trabajador ingrese a su propio perfil.

### 4.2. Revisar si el usuario es cliente

```php
if ($user && (int)$user->role_id === 3) {
    return redirect()->route('cliente.perfil');
}
```

En esta parte verifico si el usuario tiene el rol número 3, que corresponde al cliente.

Si es un cliente, lo envío a la página `cliente.perfil`.

Así, cada persona llega a la sección que corresponde a su rol dentro de LashApp.

### 4.3. Mostrar el perfil general

```php
return view('profile.edit', [
    'user' => $user,
]);
```

Si el usuario no pertenece a ninguno de los dos roles anteriores, el sistema muestra la página `profile.edit`.

También le envío los datos del usuario para que la página pueda mostrar su información.

En mi proyecto, esta parte puede utilizarse para el perfil general, por ejemplo, el del administrador, siempre que así estén configuradas las rutas y los permisos.

## 5. Método update(): actualizar la información del perfil

```php
public function update(ProfileUpdateRequest $request): RedirectResponse
{
    $request->user()->fill($request->validated());
```

Este método se encarga de actualizar los datos del usuario cuando modifica su perfil.

Primero recibe la información del formulario. Después utiliza `validated()` para obtener los datos que pasaron las revisiones establecidas en `ProfileUpdateRequest`.

Finalmente, `fill()` prepara esos datos para actualizar la información del usuario.

### 5.1. Revisar si cambió el correo electrónico

```php
if ($request->user()->isDirty('email')) {
    $request->user()->email_verified_at = null;
}
```

Aquí reviso si el usuario modificó su correo electrónico.

Si el correo cambió, el sistema deja vacía la fecha de verificación del correo.

Esto significa que el nuevo correo debe volver a verificarse antes de considerarse confirmado.

La función `isDirty('email')` permite comprobar si el correo tiene un cambio pendiente que todavía no se ha guardado.

### 5.2. Guardar los cambios

```php
$request->user()->save();
```

En esta parte guardo en la base de datos los cambios que se hicieron en el perfil.

Por ejemplo, si el usuario modificó su nombre o su correo electrónico, esta instrucción permite guardar la información actualizada.

### 5.3. Mostrar un mensaje después de actualizar

```php
return Redirect::route('profile.edit')
    ->with('status', 'profile-updated');
```

Después de guardar los cambios, envío al usuario a la ruta `profile.edit`.

También envío un mensaje con el estado `profile-updated`, que la página puede utilizar para mostrar una confirmación de que el perfil fue actualizado.

## 6. Método destroy(): eliminar la cuenta

```php
public function destroy(Request $request): RedirectResponse
{
```

Este método se encarga de eliminar la cuenta del usuario que ha iniciado sesión.

Como es una acción importante, primero se realizan algunas comprobaciones antes de eliminar la información.

### 6.1. Comprobar la contraseña

```php
$request->validateWithBag('userDeletion', [
    'password' => ['required', 'current_password'],
]);
```

Antes de eliminar la cuenta, el sistema solicita la contraseña actual del usuario.

- `required`: indica que la contraseña es obligatoria.
- `current_password`: comprueba que la contraseña ingresada sea la actual del usuario.

Si la contraseña no es correcta, la cuenta no se elimina y el sistema devuelve un error de validación.

Esto ayuda a evitar que una persona elimine una cuenta sin confirmar primero su identidad mediante la contraseña.

### 6.2. Obtener los datos del usuario

```php
$user = $request->user();
```

Aquí guardo los datos del usuario que está utilizando el sistema.

Necesito esta información para identificar qué cuenta se va a eliminar.

### 6.3. Cerrar la sesión

```php
Auth::logout();
```

Antes de eliminar la cuenta, cierro la sesión del usuario.

Así evito que continúe utilizando la sesión que tenía abierta mientras se elimina su cuenta.

### 6.4. Eliminar la cuenta

```php
$user->delete();
```

Esta instrucción elimina el registro del usuario de la base de datos.

Es importante tener presente que las consecuencias para otros datos relacionados, como citas o pagos, dependen de cómo estén configuradas las relaciones y las reglas de la base de datos.

### 6.5. Limpiar la sesión

```php
$request->session()->invalidate();
$request->session()->regenerateToken();
```

Después de eliminar la cuenta, limpio la sesión anterior y genero un nuevo código de seguridad para las siguientes solicitudes.

Esto ayuda a evitar que se siga utilizando la sesión que estaba abierta antes de eliminar la cuenta.

### 6.6. Regresar a la página principal

```php
return Redirect::to('/');
```

Finalmente, envío al usuario a la página principal de la aplicación.

De esta manera, después de eliminar su cuenta, ya no permanece en la sección de su perfil.

## 7. ¿Cómo funciona todo el archivo?

El funcionamiento de este archivo depende de la acción que realice el usuario.

- **Mostrar el perfil:** reviso quién inició sesión y lo envío a la página correspondiente según su rol.
- **Actualizar el perfil:** recibo los datos, reviso que sean válidos, guardo los cambios y muestro una confirmación.
- **Eliminar la cuenta:** solicito la contraseña actual, cierro la sesión, elimino la cuenta y envío al usuario a la página principal.

Cada método tiene una función diferente, pero todos ayudan a manejar la información de las cuentas de los usuarios en LashApp.

## 8. Conclusión

Con este archivo puedo administrar las funciones principales relacionadas con los perfiles de los usuarios. Aprendí que puedo mostrar una página diferente según el rol, actualizar información y eliminar una cuenta cuando el usuario lo solicita.

También entiendo la importancia de comprobar los datos antes de guardar los cambios y de solicitar la contraseña actual antes de eliminar una cuenta.

De esta manera, `ProfileController.php` ayuda a organizar y controlar las acciones relacionadas con los perfiles dentro de LashApp.