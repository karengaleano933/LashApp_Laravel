# Explicación de mi `DashboardController.php` (En mis palabras)

Este archivo es como la **torre de control** de toda mi aplicación **LashApp**. Cuando cualquier persona inicia sesión y entra al sistema, este controlador decide qué pantalla mostrarle y prepara todos los números, gráficas y listas que verá en su pantalla de inicio.

Tiene 3 versiones de pantalla completamente distintas según quién entra:
1. **El Administrador:** Ve cómo va todo el negocio (dinero, citas de todos, clientas y empleadas).
2. **La Trabajadora:** Ve su agenda del día, cuántas clientas tiene para hoy, sus servicios más pedidos y su promedio de estrellas.
3. **La Clienta:** Ve cuándo es su próxima cita de pestañas, qué servicios nuevos hay, y un historial de lo que ha hecho.

Aquí te cuento cómo lo programé a fondo:

---

## 1. Lo que importo al principio (Mis modelos y herramientas)

```php
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Horario;
use App\Models\Resena;
use App\Models\Servicio;
use App\Models\Trabajador;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
```

* **`Cita`, `Cliente`, `Horario`, `Resena`, `Servicio`, `Trabajador`, `User`:** Casi todos los modelos de mi base de datos, porque el dashboard necesita resumir un poquito de todo.
* **`Carbon`:** La librería de fechas de Laravel. Me sirve para saber qué día es hoy, formatear horas (`3:00 PM`) y poner textos bonitos como *"hace 2 horas"* o *"hace 3 días"*.
* **`Auth`:** Para saber quién está conectado y cuál es su rol.

---

## 2. La función `index()` — El semáforo de roles

```php
public function index()
```

Esta función es lo primero que se ejecuta cuando entras a `/dashboard`. Funciona como un vigilante en la puerta:

1. **Si es Trabajadora (`role_id === 2`):** La envía de una vez a la función `$this->dashboardTrabajador()`.
2. **Si es Clienta (`role_id === 3`):** La envía directo a `$this->dashboardCliente()`.
3. **Si es Administradora (`role_id === 1`):** Se queda aquí y calcula las métricas globales del estudio:
   * **`totalUsuarios`:** Todo el mundo registrado.
   * **`totalClientes`:** Cuántas clientas tenemos.
   * **`totalTrabajadores`:** Cuántas lashistas activas hay trabajando.
   * **`totalCitas`:** Cuántas citas se han agendado en la historia del salón.
   * **`citasPendientes`:** Citas que están esperando confirmación.
   * **`totalServicios`:** Servicios activos que ofrecemos (Lifting, Pelo a pelo, Volumen Ruso, etc.).
   * **`totalResenas`:** Número de opiniones recibidas.
   * **`proximasCitas`:** Las 5 citas más cercanas en el tiempo con el nombre de la clienta, la trabajadora asignada y el servicio.
4. **Muestra la vista:** Abre `admin.dashboard`.

---

## 3. La función `dashboardCliente()` — La experiencia para la clienta

```php
public function dashboardCliente()
```

Pensada 100% para consentir a la clienta y hacerle la vida fácil:

1. **Aseguro su cuenta de clienta:** Con `Cliente::firstOrCreate` me aseguro de que su usuario tenga su ficha de clienta lista.
2. **Cuento sus citas personales:** Cuántas citas tiene en total, cuántas pendientes y cuántas ya completó.
3. **Busco su próxima cita:** Reviso si tiene una cita de hoy en adelante (`>= Carbon::today()`). Si tiene una, le preparo una tarjeta hermosa con la foto del servicio, la fecha, la hora y el nombre de la especialista que la atenderá.
4. **Servicios destacados:** Le muestro los 4 servicios más llamativos de la base de datos con sus fotos y precios por si quiere antojarse de algo nuevo.
5. **Línea de tiempo de actividad reciente:** Creo una lista ordenada de lo que ha hecho, mezclando citas agendadas y reseñas que ha escrito con íconos de colores y tiempos relativos (*"hace 15 minutos"* con `diffForHumans()`).
6. **Agendas y especialistas disponibles:** Le preparo los horarios y los nombres de las trabajadoras disponibles para que pueda agendar una nueva cita desde el botón rápido de su pantalla.
7. **Carga la vista:** Muestra `Clientes.dashboardcliente`.

---

## 4. La función `dashboardTrabajador()` — El panel de control de la especialista

```php
public function dashboardTrabajador()
```

Esta pantalla está diseñada como la libreta de trabajo diaria de la lashista:

1. **Identifico a la trabajadora conectada:** Busco su ID en la base de datos.
2. **Cuento su trabajo de hoy y del mes:**
   * **`citasHoy`:** Cuántas citas tiene programadas exclusivamente para el día de hoy (`whereDate('fecha', $hoy)`). ¡Así sabe cómo va a estar su jornada!
   * **`totalCitas`:** Su histórico de citas.
   * **`citasPendientes`:** Citas por atender.
   * **`citasCompletadas`:** Clientas que ya atendió.
   * **`totalClientes`:** Cuántas personas distintas han pasado por sus manos (usando `distinct('cliente_id')`).
3. **Su agenda próxima:** Una tabla con las próximas 7 citas ordenadas por fecha y hora para que no se le pase ninguna clienta.
4. **Su calificación promedio:** Calculo el promedio real de estrellas que le han dado sus clientas (`Resena::whereHas('cita', ...)->avg('calificacion')`).
5. **Servicios más populares:** Analizo cuáles servicios son los que más se piden en el salón (con `Servicio::withCount('citas')`) y le calculo el porcentaje de popularidad con íconos bonitos para que vea qué técnica está en tendencia.
6. **Carga la vista:** Muestra `trabajador.dashboardtrabajador`.

---

## En resumen: ¿Qué hace este controlador?
* Es el director de tráfico que reparte a cada persona según su rol.
* Evita que un cliente vea datos privados del negocio o que una trabajadora modifique cosas de administración.
* Saca cuentas en tiempo real de citas, ganancias, clientas atendidas y calificaciones.
* Alimenta con datos vivos las tres pantallas principales de inicio de LashApp.
