# Explicación de mi `ClienteController.php` (En mis palabras)

En este controlador tengo toda la lógica para lo que hacen mis **clientas** en su portal privado de **LashApp**. Básicamente aquí manejo sus reseñas, la información de su perfil, la foto de perfil, el cambio de contraseña, la descarga de sus datos y la opción de desactivar su cuenta si ya no quieren usar la aplicación.

Aquí explico cómo lo construí y para qué sirve cada parte, sin enredos:

---

## 1. Lo que importo al principio (Mis herramientas)

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\Cliente;
use App\Models\Cita;
use App\Models\Resena;
use Carbon\Carbon;
```

Esto me sirve para:
* **`Request`:** Para recibir los datos de los formularios que llena la clienta (como su reseña o sus datos personales).
* **`Auth`:** Para saber exactamente quién es la clienta que tiene la sesión iniciada en ese momento (`Auth::user()`).
* **`Hash`:** Para encriptar la nueva contraseña de forma segura si la clienta decide cambiarla.
* **`File`:** Para gestionar archivos en el servidor (lo uso para crear la carpeta y guardar la foto de perfil que suba la clienta).
* **`User`, `Cliente`, `Cita`, `Resena`:** Son mis modelos para comunicarme con las tablas de la base de datos.
* **`Carbon`:** La librería que uso para manejar y formatear fechas y horas fácilmente.

---

## 2. La función `resenas()` — La pantalla de reseñas de la clienta

```php
public function resenas()
```

Esta función se ejecuta cuando la clienta entra al apartado de **Reseñas** en su menú:

1. **Identifico a la clienta:** Con `Auth::user()` sé quién está conectada y con `Cliente::firstOrCreate(['user_id' => $user->id])` me aseguro de que tenga su ficha en la tabla de clientes.
2. **Busco las reseñas que ella ya escribió:** Traigo sus opiniones anteriores con el nombre del servicio que se hizo y la especialista que la atendió (`$resenasCliente`).
3. **Busco las citas que ya completó pero aún no ha calificado:**
   ```php
   $citasSinResena = Cita::with(['servicio', 'trabajador.user'])
       ->where('cliente_id', $cliente->id)
       ->whereIn('estado', ['completado', 'completada', 'finalizado'])
       ->whereDoesntHave('resena')
       ...
   ```
   Esto es súper útil porque le muestra a la clienta recordatorios tipo: *"Ya terminaste tu Lifting de Pestañas, ¿cómo te fue? ¡Califícalo aquí!"*.
4. **Calculo las métricas:**
   - `$promedioStudio`: El promedio de estrellas de todo el salón.
   - `$totalResenasStudio`: Cuántas reseñas tiene el estudio en total.
   - `$totalResenasCliente`: Cuántas ha dejado esta clienta.
   - `$totalPorCalificar`: Cuántas citas terminadas tiene pendientes por calificar.
5. **Mando todo a la vista:** Con `return view('Clientes.resenas', compact(...))` pinto la pantalla con sus comentarios y los formularios para calificar.

---

## 3. La función `storeResena()` — Guardar una nueva calificación

```php
public function storeResena(Request $request)
```

Se activa cuando la clienta selecciona las estrellitas (1 a 5), escribe su comentario y le da a **"Enviar Reseña"**:

1. **Valido que todo venga bien:** Que el id de la cita exista, que la calificación sea entre 1 y 5, y que el comentario no esté vacío.
2. **Verifico que la cita sea de ella:** Por seguridad, compruebo que la cita le pertenezca a la clienta conectada para que nadie califique citas ajenas.
3. **Evito reseñas repetidas:** Reviso si esa cita ya tenía una reseña registrada (`Resena::where('cita_id', $cita->id)->exists()`), para que no califiquen dos veces el mismo servicio.
4. **Guardo la reseña:** Creo el registro en la tabla `resenas` con la fecha y hora actual.
5. **Respuesta amigable:** Le muestro un mensaje de agradecimiento: *"¡Muchas gracias por tu reseña! Tu opinión nos ayuda a seguir mejorando."*.

---

## 4. La función `perfil()` — Ver su información personal

```php
public function perfil()
```

Esta función le muestra a la clienta su panel de perfil:
1. Saco cuántas citas ha pedido en total (`$totalCitas`).
2. Cuántas de esas citas ya se realizaron exitosamente (`$citasRealizadas`).
3. Busco su **próxima cita agendada** (la más cercana en fecha y hora que esté confirmada o pendiente) para que la clienta la tenga presente apenas entre a su perfil.
4. Mando todos esos datos a `Clientes.perfilcliente`.

---

## 5. La función `updatePerfil()` — Cambiar sus datos personales

```php
public function updatePerfil(Request $request)
```

Cuando la clienta edita su información en el formulario:
1. Valido que el nombre y apellido sean obligatorios.
2. Actualizo en la tabla `users` su nombre, apellido, teléfono y cédula.
3. Actualizo en la tabla `clientes` su dirección y fecha de nacimiento.
4. Le muestro un mensaje confirmando: *"¡Tus datos de perfil se han actualizado con éxito!"*.

---

## 6. La función `updateFoto()` — Subir foto de perfil

```php
public function updateFoto(Request $request)
```

Cuando la clienta hace clic en la camarita y sube una foto desde su celular o computador:
1. Valido que sea una imagen real (jpeg, png, jpg, webp) y que no pese más de 2MB.
2. Creo la carpeta `public/uploads/perfil` si aún no existe.
3. Le pongo un nombre único al archivo para que no se sobreescriba (ej. `perfil_4_1792116000.jpg`).
4. Muevo la foto a esa carpeta y guardo la ruta en `$user->foto`.
5. Muestro el mensaje: *"¡Foto de perfil actualizada con éxito!"*.

---

## 7. La función `updatePassword()` — Cambiar la contraseña

```php
public function updatePassword(Request $request)
```

Cuando la clienta quiere actualizar su clave por seguridad:
1. Le pido su contraseña actual, la nueva y que la repita para confirmar.
2. **Seguridad:** Con `Hash::check(...)` verifico si la contraseña actual que escribió coincide con la que tiene guardada. Si no coincide, le aviso que está mal.
3. Si todo está correcto, encripto la nueva clave con `Hash::make()` y la guardo.
4. Le aviso con mensaje de éxito: *"¡Tu contraseña ha sido actualizada con éxito!"*.

---

## 8. La función `exportarDatos()` — Descargar su información (Privacidad)

```php
public function exportarDatos()
```

Hice esta función para cumplir con buenas prácticas de privacidad de datos (derecho de la clienta a tener su información):
- Empaqueta en un archivo descargable `.json` todos sus datos personales (nombre, correo, teléfono, dirección) junto con todo su historial de citas, qué especialista la atendió, cuánto pagó y qué reseñas dejó.
- La descarga se inicia sola en el navegador con el nombre `mis_datos_lashapp_[id].json`.

---

## 9. La función `eliminarCuenta()` — Desactivar su cuenta

```php
public function eliminarCuenta(Request $request)
```

Si una clienta decide no usar más la aplicación:
1. No borro sus citas históricas de la base de datos para no desordenar la contabilidad del salón; en su lugar, le pongo el estado en `'inactivo'` (`$user->estado = 'inactivo'`).
2. Le cierro la sesión inmediatamente con `Auth::logout()`.
3. Invalido su sesión y la redirijo al login con el mensaje: *"Tu cuenta ha sido desactivada correctamente."*.

---

## Resumen rápido de `ClienteController`:
- **`resenas()` & `storeResena()`:** Para que la clienta vea y califique los servicios que recibió.
- **`perfil()` & `updatePerfil()`:** Para ver y modificar sus datos personales.
- **`updateFoto()`:** Para subir su foto de perfil.
- **`updatePassword()`:** Para cambiar su contraseña de forma segura.
- **`exportarDatos()`:** Para descargar una copia de todos sus datos y citas.
- **`eliminarCuenta()`:** Para desactivar su cuenta limpiamente.
