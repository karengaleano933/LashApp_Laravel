# Explicación de mi modelo `Notificacion.php` (En mis palabras)

En este archivo controlo los **mensajes y avisos de la campanita** de **LashApp**. Es el modelo encargado de guardar las alertas que le llegan a la administradora o a las especialistas cuando pasa algo importante en el salón: una nueva cita reservada, un comprobante de pago subido o una nueva calificación de 5 estrellas recibida.

Aquí te explico cómo funciona paso a paso:

---

## 1. ¿Qué representa en la aplicación?

Es como el **buzón de avisos o la bandeja de alertas del celular**. Cada vez que ocurre un evento importante en el negocio, el sistema crea una fila con este modelo para que cuando la administradora o la trabajadora miren la campanita en la barra superior, vean el globito rojo con el número de avisos nuevos y puedan leer qué acaba de pasar.

---

## 2. La tabla y los datos que guardo (`$table` y `$fillable`)

```php
protected $table = 'notificaciones';

protected $fillable = [
    'user_id',
    'cita_id',
    'tipo',
    'titulo',
    'mensaje',
    'leida',
    'fecha_envio',
];
```

* **`$table = 'notificaciones'`:** Se comunica con la tabla `notificaciones` en la base de datos.
* **`user_id`:** El código del usuario al que le va a llegar la notificación (por ejemplo: la administradora o la lashista).
* **`cita_id`:** El código de la cita relacionada con la alerta (por si es un aviso de una reserva).
* **`tipo`:** La categoría de la notificación (por ejemplo: `'agendada'`, `'info'`, `'pago'`, etc.). Esto ayuda a pintar el ícono o color correcto en la pantalla.
* **`titulo`:** El encabezado en negrita (ejemplo: *"Nueva Cita Agendada"* o *"Comprobante Subido"*).
* **`mensaje`:** El texto explicativo con los detalles (ejemplo: *"Valentina agendó Volumen Ruso para mañana a las 10:00 AM"*).
* **`leida`:** Un valor verdadero o falso (`true`/`false`) que dice si el usuario ya vio el mensaje o si sigue pendiente.
* **`fecha_envio`:** El momento exacto en el que se generó la alerta.

---

## 3. Conversión de datos (`$casts`)

```php
protected $casts = [
    'leida' => 'boolean',
    'fecha_envio' => 'datetime',
];
```

* **`leida => 'boolean'`:** Asegura que en PHP el campo `leida` siempre sea un booleano real (`true` o `false`), para que no haya confusiones con ceros o unos (`0` o `1`).
* **`fecha_envio => 'datetime'`:** Convierte la fecha en objeto `Carbon`, permitiendo usar `$notificacion->fecha_envio->diffForHumans()` para mostrar frases humanas como *"hace 5 minutos"*.

---

## 4. Las relaciones con otros modelos

### 1. El usuario destinatario (`user`)
```php
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
```
* **Qué significa:** *"Una notificación pertenece a un usuario"* (`belongsTo`).
* **Para qué me sirve:** Me permite saber a quién va dirigida la alerta y filtrar solo las notificaciones de la persona que tiene la sesión iniciada.

### 2. La cita asociada (`cita`)
```php
public function cita()
{
    return $this->belongsTo(Cita::class, 'cita_id');
}
```
* **Qué significa:** *"Una notificación puede estar asociada a una cita"* (`belongsTo`).
* **Para qué me sirve:** Al hacer clic en la alerta, el sistema puede llevarte directamente a los detalles de esa cita específica.

---

## En resumen: ¿Para qué sirve `Notificacion.php`?
* Alimenta la campanita de notificaciones de la barra superior en tiempo real.
* Lleva el control de cuáles mensajes ya fueron leídos y cuáles no.
* Muestra avisos sobre citas, pagos y reseñas de manera amigable.
