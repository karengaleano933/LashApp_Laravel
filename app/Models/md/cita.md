# Explicación de mi modelo `Cita.php` (En mis palabras)

Este archivo es el **corazón absoluto de LashApp**. Casi todo en la aplicación gira alrededor de este modelo, porque representa cada vez que una clienta reserva una hora en el salón para colocarse pestañas, hacerse lifting o diseñarse las cejas. Conecta a la clienta, a la lashista, al servicio elegido, al pago y a la reseña en un solo lugar.

Aquí te explico detalladamente cómo lo construí:

---

## 1. ¿Qué representa en la vida real?

Es como cada una de las **hojas de la libreta de reservas del salón**. Contiene toda la información de un compromiso: quién es la clienta, qué especialista la atenderá, qué servicio se va a realizar, qué día y a qué hora empieza y termina, si ya pagó y qué comentarios u observaciones especiales hay que tener en cuenta (por ejemplo: *"alérgica al látex"* o *"prefiere volumen discreto"*).

---

## 2. La configuración especial de fechas (`$timestamps`)

```php
public $timestamps = false;
```

* **¿Por qué puse esto?**  
  Por defecto, Laravel espera que todas las tablas tengan columnas llamadas `created_at` y `updated_at`. Como mi tabla en la base de datos utiliza un campo personalizado llamado `fecha_creacion`, le apago los timestamps automáticos con `false` para que Laravel no intente buscar columnas que no existen y no se rompa la aplicación.

---

## 3. Los datos que guardo (`$fillable`)

```php
protected $fillable = [
    'cliente_id',
    'trabajador_id',
    'servicio_id',
    'horario_id',
    'fecha',
    'hora_inicio',
    'hora_fin',
    'estado',
    'observaciones',
    'fecha_creacion',
];
```

* **`cliente_id`:** El código de la clienta que pidió la cita.
* **`trabajador_id`:** El código de la especialista que va a poner las pestañas.
* **`servicio_id`:** El servicio que se reservó (ejemplo: Volumen Ruso).
* **`horario_id`:** El turno u horario en el que encaja la cita.
* **`fecha`:** El día de la cita (ejemplo: `2026-10-25`).
* **`hora_inicio` y `hora_fin`:** La hora exacta en que inicia y finaliza el servicio (ejemplo: de `10:00 AM` a `12:00 PM`).
* **`estado`:** El estado actual del compromiso: `'pendiente'`, `'confirmado'`, `'finalizado'` o `'cancelado'`.
* **`observaciones`:** Notas especiales dejadas por la clienta o por la administradora.
* **`fecha_creacion`:** El momento exacto en el que se hizo la reserva en el sistema.

---

## 4. Conversión inteligente de fechas (`$casts`)

```php
protected $casts = [
    'fecha' => 'date',
    'fecha_creacion' => 'datetime',
];
```

* Hace que la fecha y la fecha de creación se conviertan automáticamente en objetos `Carbon`. Así puedo mostrarlas en español como *"Lunes 25 de Octubre"* o usar `diffForHumans()` para decir *"agendada hace 10 minutos"*.

---

## 5. Las relaciones con todo el sistema

Este modelo es el punto de encuentro de casi toda la base de datos:

### 1. La clienta (`cliente`)
```php
public function cliente()
{
    return $this->belongsTo(Cliente::class, 'cliente_id');
}
```
* Me permite acceder a `$cita->cliente->user->name` para saber de inmediato el nombre de la persona que se va a atender.

### 2. La trabajadora (`trabajador`)
```php
public function trabajador()
{
    return $this->belongsTo(Trabajador::class, 'trabajador_id');
}
```
* Me dice qué especialista tiene asignada la cita (`$cita->trabajador->user->name`).

### 3. El servicio (`servicio`)
```php
public function servicio()
{
    return $this->belongsTo(Servicio::class, 'servicio_id');
}
```
* Me dice qué tratamiento se le va a hacer y cuánto vale (`$cita->servicio->precio`).

### 4. El horario (`horario`)
```php
public function horario()
{
    return $this->belongsTo(Horario::class, 'horario_id');
}
```
* Enlaza la cita con el turno general de la trabajadora.

### 5. Los pagos (`pagos` y `pago`)
```php
public function pagos()
{
    return $this->hasMany(Pago::class, 'cita_id');
}

public function pago()
{
    return $this->hasOne(Pago::class, 'cita_id')->latestOfMany();
}
```
* **`pagos`:** Trae todos los abonos o transacciones que se hayan hecho para esta cita.
* **`pago` con `latestOfMany()`:** Me da el **último pago más reciente** registrado para esa cita de forma directa.

### 6. La calificación y opinión (`resena`)
```php
public function resena()
{
    return $this->hasOne(Resena::class, 'cita_id');
}
```
* Conecta la cita con la reseña de estrellas y comentario que la clienta dejó después de haber sido atendida.

---

## En resumen: ¿Para qué sirve `Cita.php`?
* Es el registro central de toda la actividad de LashApp.
* Une en un solo lugar a la clienta, la trabajadora, el servicio, el cobro y la reseña.
* Controla las horas de inicio y fin para que no haya cruces ni citas duplicadas en el salón.
