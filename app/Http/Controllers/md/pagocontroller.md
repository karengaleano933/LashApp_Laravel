# Explicación de mi `PagoController.php` (En mis palabras)

En este archivo controlo todo el **dinero y las transacciones** de **LashApp**. Es como la caja registradora del salón de belleza: aquí registro cuánto pagó cada clienta, por cuál medio (efectivo, Nequi, Daviplata, tarjeta), si el pago ya entró o quedó pendiente, e incluso guardo las fotos de los comprobantes de transferencia.

Te explico cómo está construido:

---

## 1. Lo que importo al principio (Mis herramientas)

```php
use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Cita;
use App\Models\Comprobante;
use Illuminate\Support\Facades\File;
```

* **`Request`:** Para recibir los datos de la venta (el monto en pesos, el método de pago, el comprobante adjunto).
* **`Pago`:** Mi modelo para la tabla `pagos` (donde se guarda cada cobro realizado).
* **`Cita`:** Porque cada pago debe estar amarrado a una cita específica para saber a qué servicio y clienta corresponde.
* **`Comprobante`:** El modelo donde guardo el registro del archivo o foto de la transferencia.
* **`File`:** Para manipular archivos físicos en el servidor: crear carpetas, mover las fotos de los comprobantes o borrarlas cuando se elimina un pago.

---

## 2. La función `index()` — Ver la lista de pagos y el dinero recaudado

```php
public function index()
```

Cuando entro al módulo de **Pagos**, esta función hace los cálculos financieros para las tarjetas superiores:

1. **Traigo todos los pagos:** Con `Pago::with(['cita.cliente.user', 'cita.servicio', 'comprobante'])` traigo la lista completa junto con el nombre de la clienta, el servicio que se hizo y la foto del recibo si la hay.
2. **Preparo las citas disponibles para cobrar:** Hago una lista de citas (`citasParaPago`) con el nombre de la clienta y el precio del servicio para que cuando vaya a registrar un pago nuevo, solo tenga que elegir la cita de una lista desplegable y el precio se ponga solo.
3. **Métricas de la caja:**
   * **`totalRecaudado`:** Suma todo el dinero de los pagos que están en estado `'completado'` (`Pago::where('estado', 'completado')->sum('monto')`). ¡Esto me da el total de plata que ha entrado al negocio!
   * **`pagosCompletados`:** Cuántos pagos ya se cobraron con éxito.
   * **`pagosPendientes`:** Cuántos pagos faltan por cobrar o confirmar.
   * **`totalTransacc`:** El número total de transacciones registradas.
4. **Mando todo a la pantalla:** Muestra la vista `admin.pagos.pagos` con las tarjetas de dinero y la tabla detallada.

---

## 3. La función `store()` — Registrar un nuevo cobro

```php
public function store(Request $request)
```

Cuando una clienta termina su cita y paga su servicio:

1. **Valido la información:**
   * La cita debe existir (`exists:citas,id`).
   * El método de pago es obligatorio (Efectivo, Nequi, etc.).
   * El monto debe ser un número positivo (`numeric|min:0`).
   * El estado debe ser válido (`completado`, `pendiente`, `rechazado`).
   * El comprobante (si suben uno) debe ser imagen o PDF de máximo 4MB.
2. **Creo el pago:** Guardo la cita, el medio de pago, el dinero, la fecha actual (`now()`) y el número de referencia o nota que escribieron.
3. **Guardo la foto del comprobante de Nequi / Transferencia:**
   * Si adjuntaron foto, creo la carpeta `public/img/comprobantes` si aún no existe.
   * Le genero un nombre único con la hora y un código al azar (`time() . '_' . uniqid()`) para que ningún archivo se sobreescriba.
   * Muevo el archivo a esa carpeta y guardo el registro en la tabla `comprobantes`.
4. **Aviso:** Devuelvo al listado con el mensaje verde: *"Pago registrado exitosamente"*.

---

## 4. La función `update()` — Editar o corregir un pago

```php
public function update(Request $request, $id)
```

Si me equivoqué en el valor o si una clienta que tenía el pago en "pendiente" ya transfirió:

1. **Busco el pago:** Con `Pago::findOrFail($id)`.
2. **Actualizo los valores:** Modifico el método, el valor, el estado y las observaciones.
3. **Si subieron un comprobante nuevo:**
   * Guardo el nuevo archivo en la carpeta.
   * **Borro el archivo viejo del servidor:** Para no acumular basura que gaste espacio en el disco, busco los comprobantes anteriores y los elimino con `File::delete()`.
   * Guardo la nueva ruta en la base de datos.
4. **Aviso:** Muestro *"Registro de pago actualizado"*.

---

## 5. La función `destroy()` — Eliminar un pago

```php
public function destroy($id)
```

Si se registró un pago por error o duplicado:
1. Busco el pago por su ID.
2. Reviso si tenía fotos de comprobantes subidas y **las borro físicamente del servidor** con `File::delete(public_path(...))`.
3. Borro el registro de la base de datos con `$pago->delete()`.
4. Devuelvo el mensaje: *"Registro de pago eliminado"*.

---

## En resumen: ¿Qué hace este controlador?
* Lleva las cuentas claras del dinero recaudado en LashApp.
* Soporta pagos en efectivo y transferencias digitales (Nequi, Daviplata, etc.).
* Almacena y gestiona las fotos de los comprobantes de pago sin desperdiciar espacio en disco.
* Alimenta las tarjetas estadísticas de ingresos de la administración.
