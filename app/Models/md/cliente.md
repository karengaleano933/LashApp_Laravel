# Explicación de mi modelo `Cliente.php` (En mis palabras)

En este archivo controlo la **ficha personal de cada clienta** que visita mi estudio de belleza en **LashApp**. Mientras que el modelo `User.php` guarda su cuenta de acceso (correo y clave), este modelo `Cliente.php` guarda los datos propios de su vida como clienta consentida de nuestro salón.

Aquí te explico cómo funciona paso a paso:

---

## 1. ¿Qué representa en la vida real?

Es como la **carpeta o ficha clínica de belleza** que tendrías en un archivador del salón. Cada vez que una persona se registra para hacerse pestañas o cejas, se le crea un registro aquí para saber detalles como su dirección o cuándo cumple años (¡así podemos felicitarla o darle una promoción especial!).

---

## 2. La tabla y los campos que guardo (`$table` y `$fillable`)

```php
protected $table = 'clientes';

protected $fillable = [
    'user_id',
    'direccion',
    'fecha_nacimiento',
];
```

* **`$table = 'clientes'`:** Le dice a Laravel que este modelo se comunica con la tabla llamada `clientes` en la base de datos.
* **`user_id`:** Es el número que amarra esta ficha con su cuenta principal de usuario en la tabla `users`. Así sé de quién es esta ficha.
* **`direccion`:** El lugar de residencia o trabajo de la clienta.
* **`fecha_nacimiento`:** El día de su cumpleaños.

---

## 3. Las relaciones con otros modelos

Aquí conecto a la clienta con el resto de la aplicación:

### 1. Su cuenta de usuario (`user`)
```php
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
```
* **Qué significa:** *"Esta ficha de clienta pertenece a un usuario"* (`belongsTo`).
* **Para qué me sirve:** Si tengo una clienta, puedo poner `$cliente->user->name`, `$cliente->user->telefono` o `$cliente->user->email` y de una vez tengo su nombre, su teléfono para llamarla o su correo para escribirle.

### 2. Su historial de citas (`citas`)
```php
public function citas()
{
    return $this->hasMany(Cita::class, 'cliente_id');
}
```
* **Qué significa:** *"Una clienta tiene muchas citas"* (`hasMany`).
* **Para qué me sirve:** Me permite ver todo el historial de citas que esa persona ha agendado en la historia del salón: cuándo vino por primera vez, qué servicios se ha hecho, si asistió o canceló, y cuándo tiene su próxima cita.

---

## En resumen: ¿Para qué sirve `Cliente.php`?
* Guarda la información específica de las clientas del estudio de pestañas.
* Conecta a la persona con su cuenta de acceso (`User`) y con todas sus citas agendadas (`Cita`).
* Permite personalizar la atención al cliente sabiendo su historial y fecha de cumpleaños.
