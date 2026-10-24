# Explicación de mi modelo `Notificacion.php` (Código por código en mis palabras)

En este archivo controlo y administro el **sistema de alertas, avisos internos y campanita de notificaciones** de mi aplicación **LashApp**.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la comunicación en tiempo real dentro del salón.

---

## 1. ¿Qué representa este modelo en mi aplicación?

Es el **buzón de alertas y notificaciones** que mantiene enteradas a las personas sobre eventos clave:
- Si una clienta agenda una cita, el sistema genera una notificación para la administradora y para la especialista asignada.
- Si una clienta sube una transferencia bancaria, se genera un aviso de pago pendiente por validar.
- Si una clienta deja una calificación de 5 estrellas con una opinión bonita, la lashista recibe una alerta para enterarse del elogio.
- Permite gestionar qué mensajes ya fueron abiertos (`leida = true`) y cuáles están pendientes de lectura (`leida = false`), alimentando el contador rojo de la campanita.

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Comunica al servidor que a partir de esta línea todas las sentencias corresponden a código ejecutable de PHP.
* **`namespace App\Models;`**:  
  Declara el **espacio de nombres** del archivo. Ubica a la clase `Notificacion` dentro del paquete `App\Models`. Gracias a esto, puedo llamarla limpiamente desde controladores o servicios usando `use App\Models\Notificacion;` sin generar colisiones con otras clases del framework o librerías externas.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite conectar este modelo con una fábrica (`NotificacionFactory`), muy útil para simular alertas de prueba en el entorno de desarrollo y verificar el diseño de la campanita en pantalla.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase base `Model` de Eloquent ORM. Al heredar de ella, este modelo adquiere todos los métodos para crear notificaciones (`create()`), contarlas (`count()`), filtrarlas (`where()`) y marcarlas como leídas (`update()`).

---

## 4. Declaración de la clase y uso del Trait

```php
class Notificacion extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Notificacion extends Model`**:  
  Declaro la clase `Notificacion` y le indico que **hereda** (`extends`) de `Model`. De esta forma, convierto esta clase en un modelo activo de Eloquent que representa cada registro de la tabla de notificaciones.
* **`use HasFactory;`**:  
  Habilito el trait dentro de la clase para permitir el uso del método `Notificacion::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'notificaciones';
```

### ¿Por qué escribí esto?
En español, el plural de *notificacion* es *notificaciones*, pero las reglas predeterminadas en inglés de Laravel intentarían buscar una tabla llamada *notificacions*.  
Con la propiedad protegida `$table = 'notificaciones'`, le ordeno explícitamente a Laravel que se conecte directamente a la tabla `notificaciones` de la base de datos.

---

## 6. Atributos asignables de forma masiva (`$fillable`)

```php
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

### ¿Qué significa y por qué lo uso?
La propiedad protegida `$fillable` es una lista blanca de seguridad conocida como protección contra asignación masiva (Mass Assignment). Le indica a Laravel cuáles atributos sí tienen permiso de ser guardados o modificados de forma directa mediante `Notificacion::create($datos)` o `$notificacion->update($datos)`.

### Explicación de cada campo en mis palabras:
* **`'user_id'`**:  
  Llave foránea que apunta a la tabla `users`. Identifica al usuario destinatario de la alerta (la administradora, una lashista o una clienta).
* **`'cita_id'`**:  
  Llave foránea opcional que apunta a la tabla `citas`. Si la alerta se originó por una reserva o cancelación de cita, este campo guarda la referencia directa a ese compromiso.
* **`'tipo'`**:  
  Define la categoría de la alerta (por ejemplo: `'agendada'`, `'pago'`, `'cancelada'`, `'resena'`, `'info'`). Esto me sirve en la interfaz para pintar el ícono adecuado y colores distintivos (verde para éxito, amarillo para advertencia, azul para información).
* **`'titulo'`**:  
  El encabezado breve y llamativo de la alerta (por ejemplo: *"Nueva Cita Agendada"* o *"Comprobante de Pago Subido"*).
* **`'mensaje'`**:  
  El texto detallado del aviso (por ejemplo: *"Mariana reservó Volumen Ruso para mañana a las 3:00 PM con la especialista Sofía"*).
* **`'leida'`**:  
  Un indicador de estado booleano (`true` o `false`) que señala si la persona ya hizo clic y leyó el mensaje.
* **`'fecha_envio'`**:  
  Fecha y hora exacta en la que se disparó la notificación.

---

## 7. Conversión automática de tipos de datos (`$casts`)

```php
    protected $casts = [
        'leida' => 'boolean',
        'fecha_envio' => 'datetime',
    ];
```

### ¿Por qué escribí esto?
La propiedad `$casts` le enseña a Laravel cómo transformar los datos crudos de la base de datos al momento de usarlos en PHP:
* **`'leida' => 'boolean'`**:  
  Asegura que el campo siempre se maneje como un valor booleano nativo (`true` o `false`) en lugar de un número entero (`0` o `1`). Esto hace que expresiones como `if (!$notificacion->leida)` funcionen de manera limpia y predecible.
* **`'fecha_envio' => 'datetime'`**:  
  Transforma la fecha en una instancia de `Carbon`. Con esto puedo mostrar textos amigables y relativos al tiempo, como `$notificacion->fecha_envio->diffForHumans()` (*"hace 10 minutos"* o *"hace 2 horas"*).

---

## 8. Relación: Usuario destinatario (`user`)

```php
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `belongsTo` (Pertenece a).
* **¿Qué significa en mis palabras?**  
  Cada notificación le pertenece a un único usuario del sistema.
* **Llave foránea:** Indico `'user_id'`, que es la columna en la tabla `notificaciones` que almacena el ID del destinatario en la tabla `users`.
* **¿Para qué me sirve en el código?**  
  Me permite filtrar rápidamente las notificaciones que le corresponden únicamente a la persona que tiene la sesión iniciada en ese momento:
  ```php
  // Obtener las notificaciones no leídas de la usuaria actual
  $noLeidas = auth()->user()->notificaciones()->where('leida', false)->get();
  ```

---

## 9. Relación: Cita vinculada (`cita`)

```php
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `belongsTo` (Pertenece a).
* **¿Qué significa en mis palabras?**  
  Una notificación puede estar asociada a una cita específica del salón (si la alerta nació de un evento de reservas o pagos).
* **Llave foránea:** Indico `'cita_id'`, que apunta al `id` de la tabla `citas`.
* **¿Para qué me sirve en el código?**  
  Cuando la administradora o la lashista hace clic sobre la notificación en el menú desplegable, puedo redirigirla directamente al detalle completo de esa cita:
  ```php
  // Saber de qué cita provino la alerta
  $citaId = $notificacion->cita?->id;
  $nombreServicio = $notificacion->cita?->servicio->nombre;
  ```

---

## 10. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`user()`** | `belongsTo` | `User` | `user_id` | El usuario destinatario al que le pertenece y le aparece esta notificación. |
| **`cita()`** | `belongsTo` | `Cita` | `cita_id` | La cita que originó el aviso, permitiendo abrirla directamente al hacer clic. |

---

## 11. Conceptos técnicos clave que usé en este modelo

1. **Eloquent ORM:** Mapea la tabla de eventos y notificaciones como objetos manipulables en PHP.
2. **`$table`:** Declaración explícita para sobreescribir la pluralización en inglés y apuntar con exactitud a `notificaciones`.
3. **`$casts`:** Conversión automática de tipos de datos (`boolean` para banderas lógicas y `datetime` para manejo fluido de fechas con Carbon).
4. **`$fillable`:** Lista de campos autorizados para inserción o actualización masiva.
5. **`belongsTo`:** Relación de pertenencia hacia el usuario destinatario y hacia la cita de origen.
