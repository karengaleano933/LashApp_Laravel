# Explicación de mi `UsuarioController.php` (En mis palabras)

En este archivo tengo toda la lógica para manejar los usuarios de mi aplicación **LashApp**. Básicamente aquí controlo cómo se muestran, se crean, se editan y se eliminan las personas que usan el sistema (administradores, trabajadoras y clientes).

Aquí explico cómo lo hice y para qué sirve cada parte, sin palabras raras:

---

## 1. Lo que importo al principio (Las herramientas que uso)

Arriba del todo tengo estas líneas:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
```

Esto me sirve para:
* **`Request`:** Es lo que me permite atrapar los datos que la gente escribe en los formularios (nombre, teléfono, correo, etc.).
* **`Hash`:** Lo uso para encriptar las contraseñas. Nunca guardo contraseñas normales (como `123456`) en la base de datos por seguridad, así que con esto la contraseña se convierte en un código raro que nadie puede descifrar.
* **`Role`:** Es mi modelo para saber qué tipo de persona es en el sistema:
  - **1:** Administrador
  - **2:** Trabajador / Especialista
  - **3:** Cliente
* **`User`:** Es el modelo principal que se conecta directamente con mi tabla `users` en la base de datos.

---

## 2. La función `index()` — Para ver la lista y las estadísticas

```php
public function index()
```

Esta función se ejecuta cuando entro al módulo de **Usuarios**. Lo que hago aquí es:

1. **Traigo todos los usuarios:** Con `User::with('role')->get()` me traigo la lista de todas las personas registradas junto con su rol.
2. **Saco las cuentas para las tarjetas de arriba:**
   - Cuento cuántos usuarios hay en total (`$usuariosTotales`).
   - Cuento cuántos están activos (`$usuariosActivos`) y cuántos inactivos (`$usuariosInactivos`).
   - Cuento cuántos son administradores (`$administradores`), cuántos son trabajadoras (`$trabajadores`) y cuántos son clientes (`$clientes`).
3. **Mando todo a la vista:** Con `return view('admin.gusuarios.gusuarios', compact(...))` le paso toda esa información a la pantalla para que se pinten las tarjetas con los números y la tabla con los usuarios.

---

## 3. La función `create()` — Para mostrar el formulario de registro

```php
public function create()
```

Esta función es súper sencilla:
- Solo busca los roles que tengo en la base de datos (`Role::all()`).
- Me abre la pantallita del formulario (`admin.gusuarios.create`) con el menú desplegable de roles listo para que yo pueda registrar a alguien nuevo.

---

## 4. La función `store()` — Para guardar un usuario nuevo

```php
public function store(Request $request)
```

Esta es la que hace el trabajo cuando lleno el formulario y le doy al botón de **"Guardar"**:

1. **Creo el nuevo usuario:** Tomo los datos que me llegaron del formulario (rol, nombre, apellido, documento, teléfono, correo).
2. **Le encripto la contraseña:** Uso `Hash::make($request->post('password'))` para que la clave quede segura en la base de datos.
3. **Estado y autor:** Le pongo por defecto el estado en `'activo'` y guardo el ID de la persona que lo está creando (`created_by = auth()->id()`).
4. **Guardo:** Hago `$usuario->save()`.

### Lo chévere que le puse con los roles:
Para que no toque hacer doble trabajo, hice que el sistema cree automáticamente su perfil según el rol que le elegí:
- **Si es Cliente (rol 3):** De una vez le creo su registro en la tabla `clientes`.
- **Si es Trabajador o Admin (rol 1 o 2):** Le creo de una vez su registro en la tabla `trabajadores` con su especialidad (*"Lashista Profesional"* o *"Administrador / Máster"*), para que de inmediato pueda tener citas y horarios en el sistema.
5. **Redirección:** Me regresa a la lista de usuarios con un mensaje verde de éxito: *"¡Usuario registrado correctamente!"*.

---

## 5. Las funciones `show()` y `edit()`

```php
public function show(string $id) { }
public function edit(string $id) { }
```

Estas dos las dejé vacías porque en mi diseño yo uso **ventanas modales** (esas que se abren encima de la misma página) para ver y editar los datos rápido, así que no necesito crear una página aparte para cada una.

---

## 6. La función `update()` — Para actualizar los datos de un usuario

```php
public function update(Request $request, string $id)
```

Esta se ejecuta cuando edito a alguien desde la tabla:

1. **Busco a la persona:** La encuentro en la base de datos por su `id`.
2. **Actualizo sus datos:** Le cambio el nombre, apellido, documento, teléfono, correo, rol y estado (activo o inactivo) con lo que escribí.
3. **Cuidado con la contraseña:**
   - Si no escribí nada en el campo de contraseña, **no la toco** (así se queda con la clave que ya tenía).
   - Si escribí una clave nueva, ahí sí la encripto con `Hash::make()` y la actualizo.
4. **Sincronizo el rol si se lo cambié:**
   - Si era trabajadora y la pasé a cliente, le creo su ficha de cliente y le borro la de trabajadora.
   - Si la pasé a trabajadora, le creo su ficha en `trabajadores`.
5. **Guardo y aviso:** Guardo los cambios y mando el mensaje: *"Usuario actualizado exitosamente"*.

---

## 7. La función `destroy()` — Para eliminar a un usuario

```php
public function destroy(string $id)
```

Esta sirve para cuando le doy al botón de la papelera 🗑️ para borrar a alguien:

1. **Busco al usuario:** Con `User::findOrFail($id)`.
2. **Mi regla de seguridad (súper importante):**
   ```php
   if (auth()->id() == $usuario->id) {
       return back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
   }
   ```
   Esto lo puse para que **yo misma no me pueda borrar por accidente** mientras estoy usando mi cuenta de administradora, porque si me borro me quedo sin acceso al sistema.
3. **Borro en orden:** Primero le borro su ficha de cliente o de trabajadora (para que no queden datos sueltos o errores en la base de datos).
4. **Borro al usuario:** Hago `$usuario->delete()`.
5. **Mensaje:** Mando el mensaje confirmando: *"Usuario eliminado exitosamente"*.

---

## Resumen rápido de lo que hace cada función:

- **`index()`:** Me muestra la tabla con todos los usuarios y los números de cuántos hay de cada uno.
- **`create()`:** Me abre el formulario para meter a alguien nuevo.
- **`store()`:** Guarda los datos del nuevo usuario, le encripta la clave y le crea su ficha de cliente o trabajadora.
- **`update()`:** Guarda los cambios que le haga a un usuario y cuida que no se pierda la contraseña.
- **`destroy()`:** Borra al usuario limpiamente y no deja que me borre a mí misma.
