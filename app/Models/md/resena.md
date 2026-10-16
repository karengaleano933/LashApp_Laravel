# Explicación de mi modelo `Resena.php` (En mis palabras)

En este archivo controlo las **calificaciones con estrellas y comentarios** que dejan las clientas en **LashApp**. Es el modelo de la reputación y satisfacción del cliente: nos permite saber qué tan contenta quedó cada persona con su servicio de pestañas o cejas y evaluar el trabajo de cada especialista.

Aquí te cuento cómo funciona:

---

## 1. ¿Qué representa en el salón?

Es como el **libro de opiniones o las reseñas de Google del estudio**. Cuando una clienta termina su cita, puede entrar a su panel, calificar de 1 a 5 estrellas y escribir un mensajito contándonos cómo le fue (por ejemplo: *"¡Me encantaron mis pestañas volumen ruso, Andrea es la mejor!"* con 5 estrellas). Este modelo guarda esa opinión para siempre.

---

## 2. La tabla y los datos que guardo (`$table` y `$fillable`)

```php
protected $table = 'resenas';

protected $fillable = [
    'cliente_id',
    'cita_id',
    'calificacion',
    'comentario',
    'fecha_resena',
];
```

* **`$table = 'resenas'`:** Se comunica con la tabla `resenas` de la base de datos.
* **`cliente_id`:** El código de la clienta que escribió la opinión.
* **`cita_id`:** El código de la cita específica que se está evaluando. Esto es clave porque asegura que solo se pueda calificar un servicio real que ya se realizó.
* **`calificacion`:** El número de estrellas otorgadas (un número entero del 1 al 5).
* **`comentario`:** El texto con la opinión, halago o sugerencia que escribió la clienta.
* **`fecha_resena`:** La fecha y hora en la que se envió la reseña.

---

## 3. Las relaciones con otros modelos

### 1. La clienta que opinó (`cliente`)
```php
public function cliente()
{
    return $this->belongsTo(Cliente::class, 'cliente_id');
}
```
* **Qué significa:** *"Una reseña fue escrita por una clienta"* (`belongsTo`).
* **Para qué me sirve:** Me permite mostrar en pantalla el nombre y la foto de la clienta que dejó el comentario (`$resena->cliente->user->name`).

### 2. La cita calificada (`cita`)
```php
public function cita()
{
    return $this->belongsTo(Cita::class, 'cita_id');
}
```
* **Qué significa:** *"Una reseña pertenece a una cita"* (`belongsTo`).
* **Para qué me sirve:** A través de la cita puedo saber **qué especialista atendió a la clienta** (`$resena->cita->trabajador->user->name`) y **qué servicio se realizó** (`$resena->cita->servicio->nombre`). Así es como el sistema calcula el promedio de estrellas individual para cada trabajadora.

---

## En resumen: ¿Para qué sirve `Resena.php`?
* Guarda las estrellas (1 a 5) y opiniones de las clientas.
* Conecta la valoración con la clienta (`Cliente`) y con la cita realizada (`Cita`).
* Permite calcular el promedio de satisfacción general del salón y el puntaje de cada lashista.
