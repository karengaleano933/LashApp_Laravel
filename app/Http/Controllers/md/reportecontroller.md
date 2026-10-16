# Explicación de mi `ReporteController.php` (En mis palabras)

En este archivo controlo todo el módulo de **estadísticas, gráficos y reportes financieros** de **LashApp**. Es la pantalla donde la dueña o administradora del negocio puede ver en gráficas bonitas y claras cómo van las ventas, qué día de la semana entra más plata, cuáles son los servicios más rentables y cuántas citas se han completado con éxito.

Aquí te explico detalladamente cómo calculo todos estos números:

---

## 1. Lo que importo al principio (Mis herramientas de análisis)

```php
use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Resena;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
```

* **`Pago`, `Cita`, `Cliente`, `Resena`, `Servicio`:** Todos mis modelos de datos para extraer los números del negocio.
* **`Carbon`:** Me permite viajar en el tiempo para sacar los últimos 7 días de la semana y poner los nombres de los días en español (*Lun, Mar, Mié, Jue, Vie, Sáb, Dom*).
* **`DB`:** Para consultas avanzadas a la base de datos si las necesito.

---

## 2. La función `index()` — Procesar todos los gráficos y métricas

```php
public function index()
```

Esta función hace toda la matemática pesada antes de pintar los gráficos en `admin.reportes.reportes`:

### 1. Las tarjetas de resumen (KPIs)
* **`totalIngresos`:** Sumo todos los pagos completados (`Pago::where('estado', 'completado')->sum('monto')`). Si el negocio está recién instalado y en ceros, tiene un valor de respaldo para que la gráfica no se vea muerta en las demostraciones.
* **`totalCitas`:** Cuántas citas se han agendado en total (`Cita::count()`).
* **`totalClientes`:** Cuántas personas están registradas como clientas (`Cliente::count()`).
* **`promedioEstrellas`:** El promedio general de satisfacción de las clientas según sus reseñas (`Resena::avg('calificacion')`).

### 2. El estado de las citas (Gráfico de Dona / Pastel)
Saco la cuenta exacta de:
* **`citasCompletadas`:** Citas atendidas exitosamente.
* **`citasPendientes`:** Citas agendadas que aún no han ocurrido.
* **`citasCanceladas`:** Citas que se cayeron o no se realizaron.
Con estos tres números, la vista dibuja un gráfico circular para ver qué porcentaje de clientas asiste cumplidamente.

### 3. Ingresos de los últimos 7 días (Gráfico de Líneas / Barras)
Hago un bucle `for` que va hacia atrás día por día durante la última semana:
```php
for ($i = 6; $i >= 0; $i--) {
    $fecha = Carbon::today()->subDays($i);
    $nombreDia = $fecha->locale('es')->isoFormat('ddd');
    ...
}
```
* Saco el nombre corto del día (*Lun, Mar...*).
* Busco en la base de datos cuánto dinero en pagos completados entró exactamente en esa fecha (`whereDate('fecha_pago', $fecha)`).
* Guardo los días en `$labelsDias` y los montos en `$datosIngresosDias`.
Esto se lo paso directo a **Chart.js** para que pinte una gráfica de barras con los picos de ventas de la semana.

### 4. Cuánto dinero deja cada servicio (Gráfico de Rentabilidad)
Recorro el catálogo de servicios (`Servicio::withCount('citas')->get()`):
* Por cada servicio (ejemplo: *Extensión Volumen Ruso*, *Lifting de Pestañas*, *Diseño de Cejas*), calculo cuánta plata ha generado en pagos completados.
* Guardo los nombres en `$labelsServicios` y los dineros en `$datosServicios`.
Así el gráfico me muestra con claridad cuál es el servicio estrella que más ingresos aporta al salón.

### 5. Los últimos 5 pagos
Traigo los últimos 5 cobros registrados con el nombre de la clienta y el servicio, para tener una tabla rápida de las últimas transacciones que se hicieron.

---

## En resumen: ¿Qué hace este controlador?
* Es el contador y analista financiero inteligente de LashApp.
* Convierte las filas de la base de datos en información visual y gráficos interactivos.
* Ayuda a tomar decisiones de negocio (saber qué servicio promocionar más y qué días hay más clientas).
* Mantiene un historial claro de ingresos y cumplimiento de citas.
