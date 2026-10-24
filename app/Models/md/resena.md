 Explicación de mi modelo `Resena.php` (Código por código en mis palabras)

En este archivo controlo y administro el *módulo de satisfacción, calificaciones con estrellas y opiniones de las clientas* en mi aplicación *LashApp*.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la experiencia real dentro de mi salón de belleza.

---

1. ¿Qué representa este modelo en mi aplicación?

Es el *libro digital de opiniones y reputación* de mi estudio:
- Guarda la calificación (de 1 a 5 estrellas), el comentario con la experiencia de la clienta y el momento exacto en que envió la reseña (`fecha_resena`).
- Es un modelo indispensable para la calidad del salón: conecta directamente con la cita (`Cita`) y la clienta (`Cliente`). A través de esa cita, el sistema puede determinar de forma automática qué especialista atendió el servicio (`$resena->cita->trabajador`) y calcular el promedio de estrellas de cada lashista.
- Garantiza transparencia y confianza para las nuevas usuarias que visitan la plataforma.

---

 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

 Explicación de cada línea:
*`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Le indica al servidor que el contenido a continuación debe ser procesado como código PHP.
*`namespace App\Models;`**:  
  Define el **espacio de nombres** del archivo. Ubica la clase `Resena` dentro de la carpeta lógica `App\Models`. Gracias a esto, puedo llamarla en cualquier controlador (como `ResenaController` o `HomeController`) usando `use App\Models\Resena;` sin conflictos ni errores de carga.

---

 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

 Explicación de cada línea:
*`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
 Importo el trait `HasFactory`. Me da la posibilidad de conectar este modelo con una fábrica (`ResenaFactory`) para crear valoraciones y comentarios de prueba cuando ejecuto seeders de desarrollo.
*`use Illuminate\Database\Eloquent\Model;`**:  
 Importo la clase base `Model` de Eloquent ORM. Es la clase que me proporciona todos los métodos automáticos para interactuar con la base de datos (`create()`, `find()`, `where()`, `avg()`, etc.) sin necesidad de escribir consultas SQL puras.

---

 4. Declaración de la clase y uso del Trait

```php
class Resena extends Model
{
    use HasFactory;
```

 Explicación de cada línea:
*`class Resena extends Model`**:  
  Declaro la clase `Resena` y la hago **heredar** (`extends`) de `Model`. De esta forma, mi clase se convierte formalmente en un modelo Eloquent, lista para representar los registros de las opiniones en la base de datos.
*`use HasFactory;`**:  
  Habilito el trait dentro de la clase para que `Resena` pueda generar instancias de prueba mediante `Resena::factory()`.

---

 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'resenas';
```

 ¿Por qué escribí esto?
En español, la palabra lleva la letra 'ñ' (reseñas), pero en bases de datos evitamos caracteres especiales y usamos `resenas`. Además, las reglas de pluralización en inglés de Laravel no reconocen palabras en español y buscarían `resenas` de forma impredecible.  
Con la propiedad protegida `$table = 'resenas'`, le ordeno explícitamente a Laravel que se conecte de manera exacta a mi tabla `resenas`.

---

 6. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'cliente_id',
        'cita_id',
        'calificacion',
        'comentario',
        'fecha_resena',
    ];
```

 ¿Qué significa y por qué lo uso?
La propiedad `$fillable` actúa como una lista blanca de seguridad frente a vulnerabilidades de asignación masiva (Mass Assignment). Le indica a Laravel cuáles atributos sí tienen autorización para ser guardados o actualizados directamente desde una petición o formulario.

 Explicación de cada campo en mis palabras:
*`'cliente_id'`**:  
 Llave foránea que apunta a la tabla `clientes`. Registra el identificador de la clienta que redactó la reseña.
*`'cita_id'`**:  
 Llave foránea que apunta a la tabla `citas`. Vincula la opinión con el servicio real que se completó. Esto evita reseñas falsas, asegurando que solo se califique una cita auténtica y finalizada.
*`'calificacion'`**:  
 Puntaje numérico (generalmente entre 1 y 5 estrellas) otorgado por la clienta al servicio y atención recibida.
*`'comentario'`**:  
  El texto libre donde la clienta expresa sus opiniones, elogios o sugerencias sobre el tratamiento de pestañas o cejas recibido.
* **`'fecha_resena'`**:  
  Fecha y hora exacta en la que se envió la reseña para poder ordenarlas cronológicamente de las más recientes a las más antiguas.

---

7. Relación: Clienta que redactó la opinión (`cliente`)

```php
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
```

 Explicación detallada:
* Tipo de relación:** `belongsTo` (Pertenece a).
* ¿Qué significa en mis palabras?**  
  Cada reseña pertenece y fue escrita por una única clienta.
* Llave foránea:** Indico `'cliente_id'`, que es el campo en mi tabla `resenas` que guarda el ID de la tabla `clientes`.
* ¿Para qué me sirve en el código?**  
  Me permite acceder a los datos personales de la clienta para mostrar su nombre y foto junto al comentario en la vista de testimonios:
  ```php
  // Obtener el nombre y foto de quien escribió la reseña
  $nombreClienta = $resena->cliente->user->name;
  $fotoClienta = $resena->cliente->user->foto;
  ```

---

 8. Relación: Cita a la que pertenece la reseña (`cita`)

```php
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
```

 Explicación detallada:
* Tipo de relación:** `belongsTo` (Pertenece a).
* ¿Qué significa en mis palabras?**  
  Cada reseña evalúa una única cita de belleza específica.
* Llave foránea:** Indico `'cita_id'`, que es la columna en la tabla `resenas` que almacena el ID de la tabla `citas`.
* ¿Para qué me sirve en el código?**  
  Es la relación más poderosa para la analítica del salón, ya que mediante la cita puedo saber *qué servicio se realizó* y *qué trabajadora lo aplicó*:
  ```php
  // Saber a qué especialista están elogiando o calificando
  $nombreLashista = $resena->cita->trabajador->user->name;

  // Saber qué servicio se evaluó
  $servicioEvaluado = $resena->cita->servicio->nombre;
  ```

---

 9. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| *`cliente()`** | `belongsTo` | `Cliente` | `cliente_id` | La ficha y datos personales de la clienta que publicó la opinión (nombre, foto, teléfono). |
| *`cita()`** | `belongsTo` | `Cita` | `cita_id` | La cita específica evaluada, permitiendo descubrir la lashista encargada y el servicio brindado. |

---

 10. Conceptos técnicos clave que usé en este modelo

1. *Eloquent ORM:** El sistema de Laravel que modela cada opinión como un objeto dinámico con métodos y relaciones.
2. *`$table`:** Propiedad para fijar manualmente la tabla `resenas` en la base de datos.
3. *`$fillable`:** Lista de seguridad que autoriza los campos que se pueden guardar masivamente desde un formulario de feedback.
4. *`belongsTo`:** Relación inversa donde la tabla actual (`resenas`) porta la clave foránea hacia las tablas maestras (`clientes` y `citas`).
5. *Navegación anidada:** Capacidad de encadenar relaciones como `$resena->cita->trabajador->user->name` para cruzar datos sin escribir consultas complejas.
