# Explicación de mi modelo `Servicio.php` (En mis palabras)

En este archivo controlo los **tratamientos y procedimientos de belleza** que vendemos en **LashApp**. Es el corazón de nuestro catálogo: aquí está guardado cuánto vale cada servicio, cuántos minutos se demora la especialista aplicándolo, su foto promocional y a qué categoría pertenece.

Aquí te cuento cómo lo tengo programado:

---

## 1. ¿Qué representa en el negocio?

Es cada uno de los **productos de belleza que ofrecemos en nuestra carta**. Por ejemplo: *"Extensión de Pestañas Volumen Ruso"* por un precio de `$120.000` con una duración de `120 minutos`. Es fundamental porque le dice al sistema cuánto cobrar y cuánto tiempo bloquear en la agenda de la trabajadora.

---

## 2. La tabla y los datos que guardo (`$table` y `$fillable`)

```php
protected $table = 'servicios';

protected $fillable = [
    'categoria_id',
    'nombre',
    'descripcion',
    'precio',
    'duracion_minuto',
    'estado',
    'imagen_url',
];
```

* **`$table = 'servicios'`:** Se conecta con la tabla `servicios` en la base de datos.
* **`categoria_id`:** El número que dice a qué grupo pertenece (por ejemplo: a la categoría de *Pestañas* o *Cejas*).
* **`nombre`:** El nombre comercial del servicio (ejemplo: *"Pestañas Pelo a Pelo"*).
* **`descripcion`:** Una explicación de qué incluye el procedimiento y qué beneficios tiene.
* **`precio`:** El valor en dinero que debe pagar la clienta.
* **`duracion_minuto`:** El tiempo exacto en minutos que toma realizarlo (por ejemplo: `60`, `90` o `120` minutos). Esto es vital para calcular a qué hora termina la cita.
* **`estado`:** Dice si está `'activo'` (disponible para agendar) o `'inactivo'` (pausado temporalmente).
* **`imagen_url`:** La dirección de la foto del resultado final del servicio para mostrar en las tarjetas del catálogo.

---

## 3. Las relaciones con otros modelos

Aquí conecto este servicio con el resto del sistema:

### 1. La categoría a la que pertenece (`categoria`)
```php
public function categoria()
{
    return $this->belongsTo(Categoria::class, 'categoria_id');
}
```
* **Qué significa:** *"Este servicio pertenece a una categoría"* (`belongsTo`).
* **Para qué me sirve:** Puedo poner `$servicio->categoria->nombre` y saber de inmediato a qué sección de la carta corresponde.

### 2. Las citas donde se ha pedido este servicio (`citas`)
```php
public function citas()
{
    return $this->hasMany(Cita::class, 'servicio_id');
}
```
* **Qué significa:** *"Un servicio se ha realizado en muchas citas"* (`hasMany`).
* **Para qué me sirve:** Me ayuda a sacar estadísticas como saber cuál es el servicio más vendido del salón, cuántas veces se pidió este mes o cuántos ingresos totales ha generado.

---

## En resumen: ¿Para qué sirve `Servicio.php`?
* Define las características, precios y fotos de cada tratamiento del catálogo.
* Establece la duración en minutos para calcular la agenda de citas automáticamente.
* Conecta los procedimientos con su categoría (`Categoria`) y con el historial de citas (`Cita`).
