# Explicación de mi modelo `Pago.php` (En mis palabras)

En este archivo controlo todo el **registro de dinero y transacciones financieras** de **LashApp**. Es el modelo que representa cada cobro realizado en el salón: cuánto pagó la clienta, si lo hizo en efectivo o por transferencia bancaria (Nequi, Daviplata, etc.), y si el dinero ya fue recibido o está pendiente de confirmación.

Aquí te explico cómo funciona paso a paso:

---

## 1. ¿Qué representa en la vida real?

Es como el **recibo o comprobante de cobro de la caja registradora**. Cada vez que una clienta paga por un servicio de pestañas o cejas, se genera una fila con este modelo. Nos sirve para saber cuánta plata entra a diario, qué citas ya están pagas y cuáles clientas tienen saldo pendiente.

---

## 2. La tabla y los datos que guardo (`$table` y `$fillable`)

```php
protected $table = 'pagos';

protected $fillable = [
    'cita_id',
    'metodo_pago',
    'monto',
    'estado',
    'fecha_pago',
    'observaciones',
];
```

* **`$table = 'pagos'`:** Conecta este modelo con la tabla `pagos` de la base de datos.
* **`cita_id`:** El código de la cita a la que corresponde este cobro.
* **`metodo_pago`:** Cómo pagó la clienta (por ejemplo: `'Efectivo'`, `'Nequi'`, `'Daviplata'`, `'Transferencia Bancaria'` o `'Tarjeta'`).
* **`monto`:** El valor exacto en pesos que se cobró (por ejemplo: `80000.00`).
* **`estado`:** Dice si el dinero ya ingresó (`'completado'`), si aún no se ha verificado (`'pendiente'`) o si fue descartado (`'rechazado'`).
* **`fecha_pago`:** El momento exacto (fecha y hora) en que se registró la transacción.
* **`observaciones`:** Notas adicionales o el número de referencia de la transferencia de Nequi.

---

## 3. Conversión de datos (`$casts`)

```php
protected $casts = [
    'fecha_pago' => 'datetime',
    'monto' => 'decimal:2',
];
```

* **`fecha_pago => 'datetime'`:** Trata la fecha del pago como un objeto de tiempo para poder sacar reportes por día o por mes con `Carbon`.
* **`monto => 'decimal:2'`:** Asegura que el dinero siempre se trate con 2 decimales para evitar descuadres de centavos en la contabilidad.

---

## 4. Las relaciones con otros modelos

### 1. La cita correspondiente (`cita`)
```php
public function cita()
{
    return $this->belongsTo(Cita::class, 'cita_id');
}
```
* **Qué significa:** *"Un pago pertenece a una cita"* (`belongsTo`).
* **Para qué me sirve:** Si tengo un pago, puedo hacer `$pago->cita->cliente->user->name` y saber al instante a qué clienta y a qué servicio perteneció ese dinero.

### 2. Las fotos de los comprobantes (`comprobantes` y `comprobante`)
```php
public function comprobantes()
{
    return $this->hasMany(Comprobante::class, 'pago_id');
}

public function comprobante()
{
    return $this->hasOne(Comprobante::class, 'pago_id')->latestOfMany();
}
```
* **`comprobantes`:** Trae todos los archivos o capturas de pantalla adjuntos a este pago.
* **`comprobante` con `latestOfMany()`:** Me da directamente la **última foto o archivo de comprobante subido**, ideal para mostrar el botón de *"Ver Comprobante"* en la tabla de pagos sin tener que hacer bucles.

---

## En resumen: ¿Para qué sirve `Pago.php`?
* Registra los ingresos de dinero por cada cita atendida.
* Controla el medio de pago (efectivo, Nequi, etc.) y su estado.
* Se conecta con la cita (`Cita`) y con las fotos de comprobantes bancarios (`Comprobante`).
* Es la base para calcular los reportes de ventas y ganancias del negocio.
