# Explicación de mi `HorarioController.php` (En mis palabras)

En este controlador manejo la **disponibilidad y los turnos de trabajo** de las especialistas en **LashApp**. Aquí cada trabajadora puede definir qué días atiende, a qué horas empieza y termina cada turno, y ver qué bloques ya están reservados con citas y cuáles están libres para que las clientas puedan agendar.

Aquí explico cómo lo construí y para qué sirve cada función:

---

## 1. Lo que importo al principio (Mis herramientas)

```php
use App\Models\Cita;
use App\Models\Horario;
use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
```

Esto me sirve para:
* **`Horario`, `Trabajador`, `Cita`:** Mis modelos para consultar los turnos, saber a qué especialista pertenecen y revisar si ya tienen citas agendadas asociadas.
* **`Carbon`:** Para revisar fechas, saber qué día es hoy y filtrar qué horarios son futuros.
* **`Auth`:** Para saber qué especialista es la que tiene la sesión iniciada en ese momento.
* **`Request`:** Para recibir la fecha y las horas cuando se crea un nuevo bloque.

---

## 2. La función `index()` — Ver los horarios y la disponibilidad

```php
public function index()
```

Cuando la especialista entra a **"Mi Horario"** en el menú:

1. **Identifico a la trabajadora conectada:** Busco su ficha en la tabla `trabajadores` vinculada a su usuario.
2. **Traigo sus bloques de horario:** Traigo los horarios de esa trabajadora ordenados por fecha y hora, y con `withCount('citas')` para saber de una vez si ese bloque ya tiene una cita de una clienta o está libre.
3. **Respaldo inteligente:** Si la trabajadora es nueva y aún no ha registrado horarios propios, el sistema le muestra los horarios generales del estudio para que la pantalla no se vea vacía.
4. **Calculo las estadísticas:**
   - `$totalHorarios`: Cuántos bloques de turnos tiene en total.
   - `$disponibles`: Cuántos turnos tiene libres sin ninguna cita.
   - `$ocupados`: Cuántos turnos ya tienen clientas agendadas.
   - `$proximoHorario`: La fecha más cercana a partir de hoy en la que tiene turno programado.
5. **Mando todo a la vista:** Muestro la pantalla `trabajador.mi horario.horario_trabajador` con las tarjetas de métricas, los filtros de búsqueda y la tabla con los turnos.

---

## 3. La función `store()` — Agregar un nuevo bloque de turno

```php
public function store(Request $request)
```

Cuando la especialista presiona el botón **"+ Agregar Bloque Horario"** y llena la fecha y las horas:

1. **Valido los datos:** Que la fecha sea válida y que tenga hora de inicio y hora de fin obligatorias.
2. **Normalizo el estado:** Si en el formulario seleccionó "Disponible", lo guardo en la base de datos como `'activo'` para mantener el estándar.
3. **Guardo en la base de datos:** Creo el nuevo registro en la tabla `horarios` asignándoselo automáticamente al ID de la trabajadora conectada.
4. **Mensaje de éxito:** Mando la notificación verde: *"¡Bloque de horario creado exitosamente!"*.

---

## 4. La función `destroy()` — Eliminar un bloque de horario

```php
public function destroy($id)
```

Cuando toco el botón de la papelera 🗑️ en un horario:

1. **Busco el bloque:** Con `Horario::findOrFail($id)`.
2. **🛡️ Escudo de protección contra desastres:**
   ```php
   $citasCount = Cita::where('horario_id', $horario->id)->count();
   if ($citasCount > 0) {
       return back()->with('error', 'No se puede eliminar este bloque de horario porque tiene citas asignadas.');
   }
   ```
   **Evito borrar horarios que ya tengan clientas citadas.** Si una clienta ya reservó cita en esa fecha y hora, el sistema frena la eliminación y avisa con una advertencia para no dejar la cita huérfana.
3. **Eliminación segura:** Si el horario está libre y sin citas, lo borro con `$horario->delete()`.
4. **Aviso de confirmación:** Muestro el mensaje: *"¡Bloque de horario eliminado correctamente!"*.

---

## Resumen rápido de `HorarioController`:
- **`index()`:** Muestra el calendario de turnos de la especialista con los bloques libres y ocupados.
- **`store()`:** Registra nuevos horarios de atención.
- **`destroy()`:** Borra un bloque libre y protege los turnos que ya tienen citas agendadas.
