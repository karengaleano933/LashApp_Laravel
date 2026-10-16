# Explicación de mi `TrabajadorController.php` (En mis palabras)

En este archivo controlo todo lo que tiene que ver con el **perfil de las trabajadoras** (las especialistas en pestañas y cejas) en **LashApp**. Desde aquí la empleada puede ver su tarjeta de presentación con sus estadísticas de trabajo, cambiar su foto de perfil, actualizar su biografía o especialidad, y cambiar su contraseña personal.

Te explico cómo funciona cada pedazo de código:

---

## 1. Mis herramientas importadas (Las librerías)

Arriba del archivo tengo estas líneas:

```php
use App\Models\Cita;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Trabajador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
```

* **`Cita`, `Resena`, `Servicio`, `Trabajador`:** Son mis modelos para conectar con la base de datos y traer citas, opiniones de clientes, servicios del catálogo y la información profesional de la empleada.
* **`Request`:** Recibe los datos que la trabajadora escribe en los formularios de su perfil.
* **`Auth`:** Me dice quién es la persona que está conectada en ese instante.
* **`File`:** Me ayuda a crear carpetas en el servidor y mover archivos (como las fotos de perfil).
* **`Hash`:** Encripta y verifica contraseñas de forma segura.

---

## 2. La función `perfil()` — La hoja de vida y estadísticas de la trabajadora

```php
public function perfil()
```

Esta función carga la pantalla principal de su perfil (`trabajador.perfil.perfil_trabajador`). Hace cálculos muy chéveres para que ella vea cómo va su rendimiento:

1. **Aseguro su perfil:** Con `Trabajador::firstOrCreate` me aseguro de que si la trabajadora es nueva, el sistema le cree su ficha profesional automáticamente sin que se rompa la pantalla.
2. **Cuento sus citas:**
   * **`totalCitas`:** Cuántas citas le han agendado en total en su historia.
   * **`citasAtendidas`:** Cuántas clientas ya atendió con éxito (citas en estado `'finalizado'`, `'completado'`, etc.).
3. **Calculo su promedio de estrellas:**
   Busco todas las reseñas que le han dejado sus clientas y saco su calificación promedio con `avg('calificacion')` (por ejemplo: `4.9` estrellas).
4. **Busco su próxima cita:**
   Con una consulta ordenada por fecha y hora, busco la cita que tiene más cercana en el reloj para mostrarle un aviso en grande tipo: *"Tu próxima clienta es Andrea Gómez a las 3:00 PM para Pestañas Volumen Ruso"*.
5. **Mando todo a la vista:** La trabajadora ve su foto, sus números de éxito, su especialidad y su agenda inmediata.

---

## 3. La función `updatePerfil()` — Actualizar datos personales y profesionales

```php
public function updatePerfil(Request $request)
```

Cuando la trabajadora quiere cambiar su número de teléfono o escribir una nueva descripción de su experiencia:

1. **Valido los campos:** Que el nombre y apellido sean obligatorios, y que los textos no superen el límite de caracteres.
2. **Guardo los datos personales en la tabla `users`:** Actualizo su nombre, apellido, teléfono y documento.
3. **Guardo los datos profesionales en la tabla `trabajadores`:** Actualizo su especialidad (ejemplo: *"Experta en Efecto Foxy y Lifting"*) y su descripción biográfica.
4. **Aviso de éxito:** Le devuelvo un mensaje verde: *"¡Tus datos de perfil profesional se han actualizado correctamente!"*.

---

## 4. La función `updateFoto()` — Subir una nueva foto de perfil

```php
public function updateFoto(Request $request)
```

Esta función es súper importante para que las trabajadoras tengan su foto real y las clientas las reconozcan:

1. **Valido la imagen:** Que sea un archivo de foto real (`jpeg, png, jpg, webp, gif`) y que no pese más de 2MB para no llenar el servidor.
2. **Creo la carpeta si no existe:** Con `File::makeDirectory(public_path('uploads/perfil'), ...)` creo la carpeta si es la primera vez que se sube una foto.
3. **Le pongo un nombre único al archivo:** Uso el ID de la chica y la hora exacta (`time()`) para que los nombres nunca se repitan (por ejemplo: `trabajador_5_1712345678.jpg`).
4. **Muevo la foto y guardo la ruta:** Guardo la foto en el disco del servidor y pongo la dirección en su cuenta de usuario (`$user->foto = 'uploads/perfil/...'`).
5. **Listo:** La pantalla recarga y ya aparece su nueva foto redonda en el menú y en su perfil.

---

## 5. La función `updatePassword()` — Cambiar la contraseña

```php
public function updatePassword(Request $request)
```

Para la seguridad de la cuenta de la empleada:

1. **Pido tres cosas:**
   * La contraseña actual.
   * La nueva contraseña (mínimo 6 letras/números).
   * La confirmación (debe coincidir exactamente con la nueva).
2. **Verifico la contraseña vieja:** Con `Hash::check()` comparo si la clave actual que escribió coincide con la que está guardada en la base de datos. Si se equivocó, le muestro un error rojo: *"La contraseña actual no coincide"*.
3. **Guardo la nueva clave:** Con `Hash::make()` encripto la nueva contraseña y la guardo.
4. **Aviso:** Le muestro un mensaje de éxito: *"¡Tu contraseña ha sido actualizada con éxito!"*.

---

## En resumen: ¿Qué hace este controlador?
* Le da a cada especialista su propio espacio para gestionar su identidad en el negocio.
* Muestra métricas de citas atendidas y estrellas ganadas.
* Permite subir fotos reales a la carpeta del sistema.
* Gestiona la seguridad con cambio de contraseña seguro.
