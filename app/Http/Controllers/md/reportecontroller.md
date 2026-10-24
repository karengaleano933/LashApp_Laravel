# Explicación del código ReporteController.php

## 1. ¿Para qué sirve este archivo?

Este archivo se llama `ReporteController.php` y se encarga de preparar la información que se muestra en la sección de reportes de LashApp.

Su función es reunir información de los pagos, las citas, los clientes, las reseñas y los servicios para mostrar un resumen de cómo está funcionando el salón de belleza.

Con este archivo puedo consultar los ingresos, conocer cuántas citas hay, revisar el promedio de las calificaciones, ver los ingresos de los últimos siete días, conocer cuáles servicios generan más ingresos y mostrar los últimos pagos registrados.

## 2. Importación de las clases

```php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Cita;
use App\Models\Cliente;
use App\Models\Resena;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
```

En esta parte indico dónde se encuentra el archivo y cuáles son los elementos que necesito para preparar los reportes.

- `namespace App\Http\Controllers`: indica que el archivo pertenece a la carpeta de controladores.
- `use Illuminate\Http\Request`: permite recibir información enviada desde formularios, aunque en este código no se utiliza.
- `use App\Models\Pago`: me permite consultar la información de los pagos.
- `use App\Models\Cita`: me permite consultar las citas registradas.
- `use App\Models\Cliente`: me permite consultar los clientes.
- `use App\Models\Resena`: me permite consultar las calificaciones de los clientes.
- `use App\Models\Servicio`: me permite consultar los servicios del salón.
- `use Carbon\Carbon`: me permite trabajar con las fechas para calcular los ingresos de los últimos siete días.
- `use Illuminate\Support\Facades\DB`: permite realizar operaciones con la base de datos, aunque en este código no se utiliza directamente.

## 3. Inicio del controlador

```php
class ReporteController extends Controller
{
```

Aquí comienzo la clase llamada `ReporteController`.

En esta clase reúno las instrucciones necesarias para preparar la información que se mostrará en la página de reportes de LashApp.

## 4. Método index()

```php
public function index()
{
```

Este método se encarga de reunir todos los datos que necesita la página de reportes.

Cuando se ingresa a esta sección, el sistema ejecuta las instrucciones que están dentro de este método, calcula las estadísticas y finalmente envía la información a la página correspondiente.

## 5. Calcular los ingresos totales

```php
$totalIngresosReal = (float) Pago::where('estado', 'completado')->sum('monto');

$totalIngresos = $totalIngresosReal > 0
    ? $totalIngresosReal
    : 3550000;
```

En esta parte calculo cuánto dinero se ha recibido mediante los pagos que aparecen como completados.

Primero busco los pagos que tienen el estado `completado` y sumo sus valores. El resultado se guarda en `$totalIngresosReal`.

Después reviso si el resultado es mayor que cero. Si hay ingresos registrados, utilizo el valor real. Si no hay ingresos, el código muestra un valor de `$3.550.000`.

Esto último funciona como un valor de ejemplo para que el reporte tenga una cifra que mostrar cuando todavía no existen ingresos registrados.

La palabra `(float)` permite manejar el resultado como un número que puede contener decimales.

## 6. Contar las citas registradas

```php
$totalCitasReal = Cita::count();

$totalCitas = $totalCitasReal > 0
    ? $totalCitasReal
    : 24;
```

Aquí cuento todas las citas que existen en la base de datos.

El resultado se guarda en `$totalCitasReal`. Después verifico si hay citas registradas.

Si existen citas, utilizo la cantidad real. Si no hay ninguna, el sistema muestra 24 como valor de ejemplo.

De esta manera, el reporte siempre tiene una cifra para mostrar, aunque el número 24 no provenga de citas reales.

## 7. Contar los clientes registrados

```php
$totalClientesReal = Cliente::count();

$totalClientes = $totalClientesReal > 0
    ? $totalClientesReal
    : 12;
```

En esta parte cuento los clientes que están registrados en LashApp.

Si encuentro clientes en la base de datos, muestro la cantidad real. Si todavía no hay ninguno, utilizo 12 como valor de ejemplo.

Esto permite mostrar el total de clientes en la página de reportes, aunque es importante tener presente que el valor 12 no representa clientes registrados realmente.

## 8. Calcular el promedio de las estrellas

```php
$promedioEstrellasReal = Resena::avg('calificacion');

$promedioEstrellas = $promedioEstrellasReal
    ? round($promedioEstrellasReal, 1)
    : 4.9;
```

Aquí calculo el promedio de las calificaciones que han dejado los clientes en sus reseñas.

Primero obtengo todas las calificaciones y calculo su promedio. Después redondeo el resultado a un decimal para que sea más fácil de leer.

Por ejemplo, si el promedio es 4.666, se muestra como 4.7.

Si no hay calificaciones o el resultado es cero, el sistema utiliza 4.9 como valor de ejemplo.

## 9. Contar las citas según su estado

```php
$citasCompletadas = Cita::whereIn(
    'estado',
    ['confirmado', 'finalizado', 'completado']
)->count();

$citasPendientes = Cita::where('estado', 'pendiente')->count();

$citasCanceladas = Cita::where('estado', 'cancelado')->count();
```

En esta parte organizo las citas según el estado que tienen registrado.

- **Citas completadas:** cuenta las citas cuyo estado es `confirmado`, `finalizado` o `completado`.
- **Citas pendientes:** cuenta las citas que todavía aparecen como `pendiente`.
- **Citas canceladas:** cuenta las citas que tienen el estado `cancelado`.

Utilizo estas cantidades para mostrar cómo se encuentran las citas del salón.

Es importante tener en cuenta que el código agrupa las citas confirmadas, finalizadas y completadas en una sola categoría, aunque no necesariamente significan lo mismo.

### 9.1. Mostrar valores de ejemplo si no hay citas

```php
if (
    $citasCompletadas === 0 &&
    $citasPendientes === 0 &&
    $citasCanceladas === 0
) {
    $citasCompletadas = 18;
    $citasPendientes = 4;
    $citasCanceladas = 2;
}
```

Aquí reviso si las tres categorías tienen un resultado de cero.

Si no hay citas en ninguno de esos estados, el sistema coloca valores de ejemplo: 18 completadas, 4 pendientes y 2 canceladas.

Estos números sirven para mostrar una distribución de ejemplo, pero no corresponden a datos reales de la base de datos.

## 10. Calcular los ingresos de los últimos siete días

```php
$labelsDias = [];
$datosIngresosDias = [];

for ($i = 6; $i >= 0; $i--) {
```

En esta parte preparo la información que necesito para mostrar los ingresos de los últimos siete días en una gráfica.

Creo dos listas vacías:

- `$labelsDias`: guarda los nombres de los días.
- `$datosIngresosDias`: guarda los ingresos correspondientes a cada día.

Después utilizo un ciclo `for` para recorrer siete días, comenzando seis días antes de hoy y terminando en el día actual.

### 10.1. Obtener las fechas y los nombres de los días

```php
$fecha = Carbon::today()->subDays($i);

$nombreDia = $fecha->locale('es')->isoFormat('ddd');

$labelsDias[] = ucfirst($nombreDia);
```

Aquí obtengo cada fecha que necesito consultar.

`Carbon::today()` toma la fecha de hoy y `subDays($i)` permite retroceder la cantidad de días indicada.

Después convierto la fecha en un nombre corto del día en español, como `lun`, `mar` o `mié`.

Finalmente, `ucfirst()` coloca la primera letra en mayúscula y guardo el nombre en la lista `$labelsDias`.

Así puedo mostrar los nombres de los días en la gráfica.

### 10.2. Consultar los ingresos de cada día

```php
$ingresoDia = (float) Pago::where('estado', 'completado')
    ->whereDate('fecha_pago', $fecha->toDateString())
    ->sum('monto');
```

En esta parte consulto cuánto dinero se recibió en cada fecha.

Primero busco los pagos que están completados. Después selecciono únicamente los pagos cuya fecha corresponde al día que estoy revisando.

Por último, sumo sus valores y guardo el resultado en `$ingresoDia`.

De esta forma puedo conocer cuánto dinero se recibió en cada uno de los últimos siete días.

### 10.3. Colocar ingresos de ejemplo cuando no existen pagos

```php
if ($ingresoDia == 0 && $totalIngresosReal == 0) {
    $ingresoDia = [
        320000,
        450000,
        390000,
        520000,
        680000,
        890000,
        300000
    ][6 - $i] ?? 350000;
}
```

Aquí reviso si los ingresos de ese día son cero y si tampoco existen ingresos reales registrados en todo el sistema.

Si se cumplen ambas condiciones, asigno una cantidad de ejemplo al día correspondiente.

Los valores utilizados son $320.000, $450.000, $390.000, $520.000, $680.000, $890.000 y $300.000.

Esto permite que la gráfica muestre movimiento aunque todavía no haya pagos reales. Por eso, cuando se utilizan estos valores, la gráfica no representa los ingresos reales del salón.

### 10.4. Guardar los ingresos de cada día

```php
$datosIngresosDias[] = $ingresoDia;
```

Después de calcular los ingresos de cada fecha, guardo el resultado en la lista `$datosIngresosDias`.

Al terminar el ciclo, tengo los nombres de los siete días y sus respectivos valores de ingresos para mostrarlos en la gráfica.

## 11. Consultar los servicios del salón

```php
$servicios = Servicio::withCount('citas')->get();

$labelsServicios = [];
$datosServicios = [];
```

Aquí consulto los servicios registrados en LashApp.

Con `withCount('citas')` también obtengo la cantidad de citas relacionadas con cada servicio.

Después creo dos listas vacías:

- `$labelsServicios`: guarda los nombres de los servicios.
- `$datosServicios`: guarda los valores que se mostrarán en la gráfica de servicios.

## 12. Calcular los ingresos de cada servicio

```php
foreach ($servicios as $serv) {
    $labelsServicios[] = $serv->nombre;
```

En esta parte utilizo `foreach` para recorrer cada servicio que está registrado.

Por cada servicio, guardo su nombre en la lista `$labelsServicios`. Así puedo mostrar en la gráfica cuáles servicios se están comparando.

### 12.1. Consultar los pagos relacionados con cada servicio

```php
$ingresosServ = (float) Pago::where('estado', 'completado')
    ->whereHas('cita', function($q) use ($serv) {
        $q->where('servicio_id', $serv->id);
    })->sum('monto');
```

Aquí calculo cuánto dinero se ha recibido por los pagos completados relacionados con cada servicio.

Primero busco los pagos completados. Después reviso cuáles están relacionados con citas que corresponden al servicio que estoy recorriendo.

Finalmente sumo los valores de esos pagos.

Por ejemplo, si estoy revisando el servicio de extensiones de pestañas, el sistema busca los pagos completados de las citas asociadas a ese servicio.

### 12.2. Guardar el valor del servicio

```php
$datosServicios[] = $ingresosServ > 0
    ? $ingresosServ
    : (float) ($serv->precio * max(1, $serv->citas_count));
```

En esta parte decido qué valor voy a mostrar para cada servicio.

Si encuentro ingresos reales mayores que cero, utilizo esa cantidad.

Si no encuentro ingresos, calculo un valor de ejemplo multiplicando el precio del servicio por la cantidad de citas. Si no hay citas, `max(1, $serv->citas_count)` utiliza 1 como mínimo.

Esto permite mostrar un valor en la gráfica, pero ese resultado puede ser solamente una estimación y no representa necesariamente dinero recibido.

## 13. Mostrar servicios de ejemplo si no hay servicios registrados

```php
if (empty($labelsServicios)) {
    $labelsServicios = [
        'Ext. Pestañas',
        'Lifting Pestañas',
        'Diseño de Cejas'
    ];

    $datosServicios = [
        1850000,
        950000,
        750000
    ];
}
```

Aquí reviso si la lista de nombres de servicios está vacía.

Si no hay servicios registrados, el sistema muestra tres servicios de ejemplo: extensiones de pestañas, lifting de pestañas y diseño de cejas.

También les asigna valores de $1.850.000, $950.000 y $750.000.

Estos datos sirven para mostrar la gráfica, pero no son resultados reales de la base de datos.

## 14. Consultar los últimos pagos registrados

```php
$ultimosPagos = Pago::with([
    'cita.cliente.user',
    'cita.servicio'
])
    ->orderByDesc('fecha_pago')
    ->take(5)
    ->get();
```

En esta parte obtengo los últimos pagos registrados en el sistema.

También consulto los datos del cliente relacionado con la cita y el servicio que recibió.

Después organizo los pagos desde la fecha más reciente hasta la más antigua.

Con `take(5)` limito la consulta a cinco registros, por lo que la página solamente mostrará los cinco pagos más recientes.

## 15. Mostrar la página de reportes

```php
return view('admin.reportes.reportes', compact(
    'totalIngresos',
    'totalCitas',
    'totalClientes',
    'promedioEstrellas',
    'citasCompletadas',
    'citasPendientes',
    'citasCanceladas',
    'servicios',
    'labelsDias',
    'datosIngresosDias',
    'labelsServicios',
    'datosServicios',
    'ultimosPagos'
));
```

Esta es la última parte del método.

Aquí envío toda la información que preparé a la página `admin.reportes.reportes`.

Utilizo `compact()` para pasar las variables que necesita la vista, como los ingresos totales, las citas, los clientes, el promedio de estrellas, las estadísticas por estado, los datos de las gráficas y los últimos pagos.

De esta manera, la página puede mostrar toda la información en forma de resúmenes, gráficas y listados.

## 16. ¿Cómo funciona todo el archivo?

El funcionamiento de este archivo comienza cuando ingreso a la sección de reportes de LashApp.

Primero, el sistema consulta los pagos, las citas, los clientes y las reseñas para calcular las estadísticas principales.

Después organiza las citas por estado y calcula los ingresos de los últimos siete días. También consulta los servicios para preparar la información de la gráfica y obtiene los cinco pagos más recientes.

Si no encuentra algunos datos, utiliza ciertos valores de ejemplo que ya están escritos en el código.

Finalmente, envía toda la información a la página administrativa para que pueda visualizarla.

## 17. Conclusión

Con este archivo puedo reunir la información más importante de LashApp en una sola sección. Así puedo consultar los ingresos, conocer cuántas citas y clientes hay, revisar las calificaciones y observar los resultados de los servicios del salón.

También entiendo que los reportes me ayudan a tener una idea general del funcionamiento del negocio y a organizar mejor la información.

Algo importante que debo tener presente es que algunos valores están puestos como ejemplo. Si quiero que los reportes muestren solamente información real, debo revisar esas partes para que, cuando no existan datos, el sistema muestre cero o un mensaje indicando que todavía no hay información registrada.