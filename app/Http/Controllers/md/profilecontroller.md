# Explicación de mi `ProfileController.php` (En mis palabras)

Este archivo viene originalmente del sistema de autenticación de **Laravel Breeze**, pero lo adapté para que funcione perfecto con los diferentes roles de **LashApp**. Su trabajo es manejar el perfil personal del usuario que tiene la sesión iniciada: redirigirlo a su pantalla correcta, actualizar sus datos y, si el usuario lo desea, borrar su cuenta del sistema.

Aquí te cuento cómo funciona:

---

## 1. Lo que importo al principio (Mis herramientas)

```php
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
```

* **`ProfileUpdateRequest`:** Es un validador especial que revisa que el correo no esté repetido por otra persona y que los datos sean válidos.
* **`RedirectResponse` y `Redirect`:** Para enviar al usuario a otra página después de hacer una acción.
* **`Request`:** Para recibir los datos enviados desde el formulario.
* **`Auth`:** Para saber quién está conectado y cerrar su sesión de forma segura si decide eliminar su cuenta.

---

## 2. La función `edit()` — Redireccionar al perfil que le corresponde

```php
public function edit(Request $request)
```

Cuando alguien le da clic a "Mi Perfil" en cualquier parte del sistema, esta función revisa quién es:

1. **Si es Trabajadora (`role_id === 2`):** La envía a su propio perfil profesional (`redirect()->route('trabajador.perfil')`), donde puede ver sus citas atendidas, sus estrellas y cambiar su especialidad.
2. **Si es Clienta (`role_id === 3`):** La envía a su perfil de clienta (`redirect()->route('cliente.perfil')`).
3. **Si es Administrador:** Lo deja en la vista de edición estándar de Laravel (`profile.edit`) para cambiar su nombre, correo y contraseña.

> **¿Por qué hice esto?**  
> Para que nadie se confunda ni caiga en pantallas que no le corresponden. Cada rol tiene su propio diseño adaptado a lo que hace en el salón.

---

## 3. La función `update()` — Actualizar nombre y correo

```php
public function update(ProfileUpdateRequest $request): RedirectResponse
```

Cuando el usuario modifica su nombre o su correo electrónico:

1. **Rellena los nuevos datos:** Con `$request->user()->fill($request->validated())`.
2. **Si cambió el correo:**
   ```php
   if ($request->user()->isDirty('email')) {
       $request->user()->email_verified_at = null;
   }
   ```
   Con `isDirty('email')` el sistema detecta si el correo que escribió es diferente al que tenía antes. Si cambió de correo, borra la verificación previa para que vuelva a confirmar que el nuevo correo sí le pertenece.
3. **Guarda los cambios:** `$request->user()->save()`.
4. **Devuelve un aviso:** Redirige con el mensaje de estado de que el perfil fue actualizado.

---

## 4. La función `destroy()` — Eliminar la propia cuenta

```php
public function destroy(Request $request): RedirectResponse
```

Por seguridad y privacidad, cualquier usuario tiene derecho a borrar su cuenta si ya no quiere usar el sistema:

1. **Pide la contraseña obligatoria:** Con `'current_password'` verifica que quien está borrando la cuenta sea el dueño real y se sepa la contraseña actual.
2. **Cierra la sesión:** Con `Auth::logout()` para sacarlo del sistema.
3. **Borra al usuario de la base de datos:** `$user->delete()`.
4. **Limpia la sesión y tokens:** Invalida la sesión del navegador para que no queden rastros.
5. **Redirecciona:** Lo envía a la página de inicio pública del salón (`/`).

---

## En resumen: ¿Qué hace este controlador?
* Envía a cada rol a su pantalla de perfil especializada.
* Permite modificar el nombre y correo de la cuenta de forma segura.
* Si se cambia el correo, reinicia el estado de verificación.
* Permite eliminar la cuenta verificando la contraseña actual por seguridad.
