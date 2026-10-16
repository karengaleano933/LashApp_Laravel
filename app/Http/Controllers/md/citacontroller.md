# Explicación de mi `CitaController.php` (En mis palabras)

Este es el **corazón de mi negocio**, porque en **LashApp** todo gira alrededor de las citas de pestañas y cejas. En este archivo controlo la agenda completa: quién atiende a quién, a qué hora, qué servicio se hace, el cálculo del tiempo y hasta el registro automático del pago.

Aquí te explico cómo lo hice y para qué sirve cada parte, sin palabras técnicas:

---

## 1. Lo que importo al principio (Mis herramientas)

```php
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Horario;
use App\Models\Pago;
use App\Models\Servicio;
use App\Models\Trabajador;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
```

Esto me sirve para:
* **`Cita`, `Cliente`, `Horario`, `Pago`, `Servicio`, `Trabajador`:** Son todos mis modelos para hablar con las diferentes tablas de la base de datos (conectar clienta con servicio, especialista, horario y pago).
* **`Carbon`:** La uso muchísimo aquí para saber qué día es hoy, calcular el inicio y fin de la semana, y sumar minutos a las horas (por ejemplo, si una cita empieza a las 10:00 am y el servicio dura 90 minutos, calcular que termina a las 11:30 am).
* **`Request`:** Para recibir los datos de la cita que se está agendando en el formulario.

---

## 2. La función `index()` — Ver la agenda según quién esté conectado

```php
public function index()
```

Hice que esta función sea inteligente y se adapte según el rol de la persona que entra:

### A. Si es una Clienta (Rol 3):
- La manda directo a la pantalla para agendar su cita (`Clientes.agendarcitas`) con la lista de servicios activos para que elija.

### B. Si es una Especialista / Trabajadora (Rol 2):
- Busca su ID de trabajadora (`$id_trabajador`).
- Saca sus métricas personales:
  - `$citasHoy`: Citas que tiene para el día de hoy.
  - `$citasSemana`: Citas programadas para esta semana.
  - `$citasComplet`: Cuántas citas ya finalizó exitosamente.
  - `$citasCancelad`: Cuántas le han cancelado.
- Carga su lista de próximas citas ordenadas por fecha y hora.
- La envía a su vista especializada: `trabajador.citas.citas_trabajador`.

### C. Si es el Administrador (Rol 1):
- Calcula las métricas generales de todo el salón.
- Prepara los listados desplegables de clientas, servicios y especialistas para que el admin pueda agendar o filtrar por cualquier persona.
- Abre la vista de gestión completa de citas (`admin.citas.citas`).

---

## 3. La función `store()` — Agendar una cita nueva y procesar el pago

```php
public function store(Request $request)
```

Esta es la función más completa. Cuando alguien termina de llenar el formulario de agendar y le da a **"Confirmar Cita"**:

1. **Aseguro la clienta:** Si quien agenda es una clienta con su cuenta, me aseguro de que esté registrada en la tabla de clientes.
2. **Asignación de especialista:** Si en el formulario dejaron *"Cualquier profesional disponible"*, el sistema le asigna automáticamente la primera especialista activa.
3. **Valido los datos:** Que la fecha, la hora, el servicio y la clienta existan y sean válidos.
4. **Cálculo automático de la hora fin:**
   ```php
   $duracion = $servicio->duracion_minuto;
   ...
   $horaFin = (clone $horaInicio)->addMinutes($minutos)->format('H:i:s');
   ```
   Busco cuánto dura el servicio (por ejemplo, 60 minutos) y se lo sumo a la hora de inicio para saber exactamente a qué hora termina el turno.
5. **Aseguro el bloque de horario:** Con `Horario::firstOrCreate(...)` vinculo o creo el bloque en la tabla `horarios` para que la base de datos se mantenga perfectamente relacionada.
6. **Guardo la cita:** Creo el registro en la tabla `citas` con estado `'pendiente'` o `'confirmado'`, la fecha, el horario y las observaciones.
7. **💳 ¡Registro de pago automático!**
   De una vez creo el registro en la tabla `pagos` con el precio del servicio, el método seleccionado (tarjeta, PSE, efectivo) y si fue por pasarela lo marco como `'completado'`.
8. **Respuesta:** Mando un mensaje celebrando: *"¡Tu cita ha sido agendada con éxito y tu pago procesado!"*.

---

## 4. La función `cancelar()` — Cancelar una cita

```php
public function cancelar($id)
```

Cuando una clienta o el administrador necesita cancelar una cita:
1. **Busco la cita:** Con `Cita::findOrFail($id)`.
2. **Escudo de seguridad:** Si quien cancela es una clienta, el sistema revisa que esa cita sea de ella. Si intenta cancelar la cita de otra persona, el sistema la frena con un error 403.
3. **Cambio el estado:** Le pongo a la cita `$cita->estado = 'cancelado'`.
4. **Guardo y regreso:** Con el mensaje verde: *"La cita fue cancelada exitosamente"*.

---

## Resumen rápido de `CitaController`:
- **`index()`:** Muestra la agenda adaptada según si eres administradora, trabajadora o clienta.
- **`store()`:** Agenda la cita, calcula la hora de salida según la duración del tratamiento y crea el pago en el sistema.
- **`cancelar()`:** Cancela la cita de forma segura sin borrar los datos históricos.
