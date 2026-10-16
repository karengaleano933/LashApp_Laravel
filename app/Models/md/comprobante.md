# Explicación de mi modelo `Comprobante.php` (En mis palabras)

En este archivo controlo los **archivos y fotos de las transferencias** en **LashApp**. Cada vez que una clienta o la administradora sube un pantallazo de Nequi, Daviplata o un recibo en PDF para respaldar un pago, la información de ese archivo se guarda en este modelo.

Aquí te explico cómo funciona y para qué sirve:

---

## 1. ¿Qué representa en la vida real?

Es como el **archivador de recibos o tickets de pago**. Cuando una clienta transfiere por Nequi o por el banco, suele enviar el pantallazo de la transacción exitosa. Este modelo guarda la ruta en el servidor de esa imagen para que la administradora pueda abrirla y comprobar con sus propios ojos que el dinero sí entró antes de marcar la cita como pagada.

---

## 2. La tabla y los datos que guardo (`$table` y `$fillable`)

```php
protected $table = 'comprobantes';

protected $fillable = [
    'pago_id',
    'nombre_archivo',
    'tipo_archivo',
    'ruta_archivo',
    'fecha_subida',
];
```

* **`$table = 'comprobantes'`:** Se enlaza con la tabla `comprobantes` de la base de datos.
* **`pago_id`:** El código del pago en la tabla `pagos` al que pertenece este archivo.
* **`nombre_archivo`:** El nombre original que tenía la foto en el celular de la persona (ejemplo: `comprobante_nequi_valentina.jpg`).
* **`tipo_archivo`:** La extensión del archivo (`jpg`, `png`, `webp` o `pdf`).
* **`ruta_archivo`:** La ubicación física en la carpeta de nuestro servidor donde quedó guardada la foto (ejemplo: `img/comprobantes/1712345678_654321.jpg`).
* **`fecha_subida`:** El día y la hora exacta en la que se subió el archivo.

---

## 3. Conversión de fechas (`$casts`)

```php
protected $casts = [
    'fecha_subida' => 'datetime',
];
```

* Hace que `fecha_subida` sea tratada como un objeto de fecha real para poder saber hace cuántos minutos o días se adjuntó el recibo.

---

## 4. Su relación con el pago (`pago`)

```php
public function pago()
{
    return $this->belongsTo(Pago::class, 'pago_id');
}
```

* **Qué significa:** *"Un comprobante pertenece a un pago"* (`belongsTo`).
* **Para qué me sirve:** Si estoy viendo una foto de comprobante, puedo acceder a `$comprobante->pago->monto` o `$comprobante->pago->cita->cliente->user->name` para saber de inmediato a qué clienta y a qué monto de dinero corresponde esa imagen.

---

## En resumen: ¿Para qué sirve `Comprobante.php`?
* Respalda visualmente los pagos digitales con fotos de transferencias reales.
* Guarda la ruta exacta del archivo en el servidor para que se pueda ver en pantalla grande.
* Se conecta directamente con el modelo de pagos (`Pago`).
