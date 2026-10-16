# Explicación de mi `ResenaController.php` (En mis palabras)

En este archivo controlo todo el sistema de **calificaciones y opiniones** de mis clientas en **LashApp**. Es como el libro de visitas o las estrellas de Google del salón de pestañas: aquí veo qué tan felices salieron las clientas, qué comentarios dejaron y qué promedio de estrellas tiene cada especialista o el negocio en general.

Aquí te explico detalladamente cómo lo programé, paso a paso y sin enredos técnicos:

---

## 1. Las herramientas que uso (Lo que importo arriba)

Al iniciar el archivo llamo a estas tres cosas:

```php
use Illuminate\Http\Request;
use App\Models\Resena;
use App\Models\Cita;
```

* **`Request`:** Me ayuda a atrapar lo que la clienta escribe al calificar: cuántas estrellas nos da y el mensajito que nos deja.
* **`Resena`:** Es el modelo que habla directamente con mi tabla `resenas` en la base de datos (donde están guardadas las notas del 1 al 5 y las opiniones).
* **`Cita`:** Es el modelo de las citas agendadas, porque cada reseña debe pertenecer obligatoriamente a una cita real que ya se atendió en el salón.

---

## 2. La función `index()` — Ver todas las opiniones y calcular promedios

```php
public function index()
```

Esta función es el cerebro de la pantalla de reseñas. Hace algo muy inteligente: **se fija en quién está iniciando sesión**.

### Caso A: Si es una Trabajadora (Rol 2)
1. **Identifico a la empleada:** Con `auth()->user()` sé quién es, y busco su registro en la tabla de trabajadoras.
2. **Filtro solo sus reseñas:** Busco únicamente las opiniones de citas que atendió ella misma (`whereHas('cita', ...)`).
3. **Respaldo visual:** Si la chica es nueva y aún no tiene reseñas propias, le muestro las generales del salón para que su pantalla no se vea vacía y triste.
4. **Calculo todas sus estadísticas:**
   * **`totalResenas`:** Cuántas clientas se han tomado el tiempo de opinar sobre su trabajo.
   * **`promedioCalificacion`:** El promedio de estrellas (por ejemplo: `4.8` o `5.0`).
   * **`positivas` / `neutras` / `negativas`:** Las agrupo automáticamente para saber si las clientas están felices (4 o 5 estrellas), normales (3 estrellas) o inconformes (1 o 2 estrellas).
   * **Distribución de estrellas (`c1` a `c5`) y porcentajes (`pct1` a `pct5`):** Cuento cuántas de 5 estrellas tiene, cuántas de 4, etc., y saco el porcentaje para que en la pantalla se dibuje una barrita de progreso visual como la de Amazon o Google Play.
5. **Mando los datos a su vista:** Carga `trabajador.resenas.resenas_trabajador`.

### Caso B: Si es la Administradora
1. **Traigo absolutamente todas las reseñas del salón:** Con los datos de la clienta y el servicio que se realizó.
2. **Calculo las estadísticas globales:** El promedio general de todo el estudio de belleza y las barritas del 1 al 5 estrellas.
3. **Busco qué citas faltan por calificar:** Con `whereDoesntHave('resena')` encuentro citas terminadas a las que aún no les han dejado reseña, para que el administrador pueda registrar la opinión si la clienta la dio en persona o por WhatsApp.
4. **Mando todo a la vista del administrador:** Carga `admin.resenas.resenas`.

---

## 3. La función `store()` — Guardar una nueva opinión

```php
public function store(Request $request)
```

Se activa cuando alguien envía el formulario de calificación:

1. **Valido que los datos vengan bien:**
   * La cita debe existir obligatoriamente en el sistema (`exists:citas,id`).
   * La calificación debe ser un número entero entre 1 y 5 estrellas (`integer|min:1|max:5`).
   * El comentario no puede estar vacío y máximo puede tener 1000 letras.
2. **Asocio la cita:** Busco la cita con `Cita::findOrFail($request->input('id_cita'))`.
3. **Guardo la reseña en la base de datos:**
   ```php
   Resena::create([
       'cliente_id' => $cita->cliente_id,
       'cita_id' => $cita->id,
       'calificacion' => (int) $request->input('calificacion', 5),
       'comentario' => $request->input('comentario'),
       'fecha_resena' => now(),
   ]);
   ```
   Automáticamente le coloco el ID de la clienta, la cita, sus estrellas, el mensaje y la fecha y hora exacta del momento (`now()`).
4. **Redirecciono:** Regreso al listado con un mensajito verde de éxito: *"Valoración guardada exitosamente"*.

---

## 4. La función `destroy()` — Borrar una reseña

```php
public function destroy($id)
```

Si por alguna razón hay una reseña spam o con contenido inapropiado, la administradora puede borrarla:
1. Busca la reseña por su número de ID (`Resena::findOrFail($id)`).
2. La elimina de la base de datos con `$resena->delete()`.
3. Recarga la página avisando que la reseña fue eliminada correctamente.

---

## En resumen: ¿Qué hace este controlador?
* Le calcula la reputación y promedio de estrellas a las trabajadoras y al salón.
* Separa la pantalla de la empleada de la del administrador.
* Convierte las calificaciones en gráficos con porcentajes de satisfacción.
* Permite calificar citas y borrar comentarios no deseados.
