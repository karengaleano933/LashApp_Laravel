# Explicación de mi modelo `Horario.php` (En mis palabras)

En este archivo controlo los **turnos de trabajo y la disponibilidad** de las especialistas de **LashApp**. Es el modelo que define qué días trabaja cada empleada, a qué hora empieza su turno y a qué hora termina.

Aquí te explico cómo funciona y por qué es tan importante para el orden del salón:

---

## 1. ¿Qué representa en la vida real?

Es como el **cuadro de turnos colgado en la pared del salón**. Si una especialista trabaja el viernes de 9:00 AM a 6:00 PM, esa jornada se guarda en este modelo. Gracias a esto, el sistema sabe exactamente qué horas están libres y evita que una clienta intente agendar un servicio a una hora en la que la trabajadora no está en el local o ya salió de su jornada.

---

## 2. La tabla y los datos que guardo (`$table` y `$fillable`)

```php
protected $table = 'horarios';

protected $fillable = [
    'trabajador_id',
    'fecha',
    'hora_inicio',
    'hora_fin',
    'estado',
];
```

* **`$table = 'horarios'`:** Se comunica con la tabla `horarios` en la base de datos.
* **`trabajador_id`:** El código de la especialista a quien le pertenece este turno.
* **`fecha`:** El día específico del calendario en el que va a trabajar (por ejemplo: `2026-10-20`).
* **`hora_inicio`:** La hora en la que comienza a recibir clientas (por ejemplo: `09:00:00`).
* **`hora_fin`:** La hora en la que termina su jornada laboral (por ejemplo: `18:00:00`).
* **`estado`:** Dice si el turno está `'activo'` (disponible para citas) o `'inactivo'` (turno cancelado o bloqueado por permiso).

---

## 3. Conversión de fechas (`$casts`)

```php
protected $casts = [
    'fecha' => 'date',
];
```

* **`'fecha' => 'date'`:** Hace que Laravel trate el campo `fecha` como un objeto de tipo fecha real (`Carbon`). Así puedo formatearlo fácilmente a cosas como `"Viernes 20 de Octubre"` sin tener que hacer conversiones complicadas a mano.

---

## 4. Las relaciones con otros modelos

### 1. La especialista dueña del horario (`trabajador`)
```php
public function trabajador()
{
    return $this->belongsTo(Trabajador::class, 'trabajador_id');
}
```
* **Qué significa:** *"Este horario pertenece a una trabajadora"* (`belongsTo`).
* **Para qué me sirve:** Si tengo un horario, puedo consultar `$horario->trabajador->user->name` para saber de quién es ese turno y pintar su nombre en el calendario.

### 2. Las citas agendadas dentro de este horario (`citas`)
```php
public function citas()
{
    return $this->hasMany(Cita::class, 'horario_id');
}
```
* **Qué significa:** *"Un turno u horario puede contener muchas citas"* (`hasMany`).
* **Para qué me sirve:** Me permite ver qué clientas van a ser atendidas a lo largo de esa jornada laboral completa.

---

## En resumen: ¿Para qué sirve `Horario.php`?
* Define las horas y fechas en las que las lashistas están disponibles para atender.
* Evita cruces de citas y reservas fuera del horario laboral.
* Conecta las jornadas con la trabajadora (`Trabajador`) y sus citas del día (`Cita`).
