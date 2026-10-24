# Explicación de mi modelo `Cliente.php` (Código por código en mis palabras)

En este archivo controlo y administro la **ficha personal, expediente y datos propios de cada clienta** en mi aplicación **LashApp**.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la atención personalizada dentro de mi salón de belleza.

---

## 1. ¿Qué representa este modelo en mi aplicación?

En LashApp tengo separados los datos de acceso de los datos del cliente:
- En `User.php` guardo las credenciales generales con las que la persona entra al sistema (nombre, correo, contraseña, teléfono y foto).
- En este modelo `Cliente.php` guardo su **expediente como clienta del salón**: su dirección de residencia y su fecha de cumpleaños (para poder enviarle promociones especiales en su mes o felicitarla).
- Además, conecta a la persona con todo su **historial de reservas** (`Cita`), lo que nos permite saber cuántas veces ha venido a realizarse extensiones de pestañas o cejas, qué servicios prefiere y cuándo tiene su próxima visita.

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Le comunica al servidor que todo el contenido a partir de esta línea debe interpretarse como código ejecutable de PHP.
* **`namespace App\Models;`**:  
  Declara el **espacio de nombres** del modelo. Organiza a la clase `Cliente` dentro del directorio `App\Models`. Con esto, puedo importarla fácilmente en cualquier controlador (como `ClienteController` o `CitaController`) con la instrucción `use App\Models\Cliente;`.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite enlazar este modelo con su fábrica (`ClienteFactory`) para generar clientas ficticias automáticamente cuando ejecuto seeders de prueba durante el desarrollo.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase base `Model` de Eloquent ORM. Al extender de ella, el modelo adquiere todos los métodos automáticos para consultar registros (`find()`, `where()`), crear fichas nuevas (`create()`) y relacionarse con usuarios y citas sin escribir SQL manual.

---

## 4. Declaración de la clase y uso del Trait

```php
class Cliente extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Cliente extends Model`**:  
  Declaro la clase `Cliente` y la hago **heredar** (`extends`) de `Model`. De esta forma, mi clase se transforma en un modelo Eloquent activo que representa cada fila de clientas en la base de datos.
* **`use HasFactory;`**:  
  Habilito el trait dentro de la clase para permitir la generación rápida de clientas de prueba mediante `Cliente::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'clientes';
```

### ¿Por qué escribí esto?
Por convención, Laravel busca las tablas en inglés pluralizando el nombre del modelo (buscaría `clientes` o intentaría pluralizar en inglés como `clientes` o `clientes_table`).  
Para evitar confusiones o discrepancias en los motores de bases de datos, utilizo la propiedad protegida `$table = 'clientes'` para fijar de manera explícita que este modelo trabaje directamente sobre la tabla `clientes`.

---

## 6. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'user_id',
        'direccion',
        'fecha_nacimiento',
    ];
```

### ¿Qué significa y por qué lo uso?
La propiedad `$fillable` protege a mi aplicación contra ataques de asignación masiva (Mass Assignment). Funciona como una lista blanca de seguridad que le indica a Laravel cuáles atributos sí pueden ser rellenados o modificados de golpe al procesar peticiones con `Cliente::create($datos)` o `$cliente->update($datos)`.

### Explicación de cada campo en mis palabras:
* **`'user_id'`**:  
  Llave foránea que conecta esta ficha clínica con la cuenta de la persona en la tabla `users`. Gracias a este número sé a qué usuario (con su nombre, teléfono, correo y foto) le pertenece esta ficha.
* **`'direccion'`**:  
  La dirección física de residencia o trabajo de la clienta.
* **`'fecha_nacimiento'`**:  
  El día de su cumpleaños, utilizado para campañas de fidelización, descuentos de aniversario y felicitaciones personalizadas.

---

## 7. Relación: Cuenta de usuario (`user`)

```php
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `belongsTo` (Pertenece a).
* **¿Qué significa en mis palabras?**  
  Cada expediente de clienta le pertenece a un único usuario del sistema.
* **Llave foránea:** Indico explícitamente `'user_id'`, que es la columna en la tabla `clientes` que guarda el ID de la tabla `users`.
* **¿Para qué me sirve en el código?**  
  Si tengo la ficha de la clienta en la mano, puedo acceder directamente a sus datos de contacto y perfil:
  ```php
  // Obtener el nombre, teléfono y foto de la clienta
  $nombre = $cliente->user->name;
  $telefono = $cliente->user->telefono;
  $correo = $cliente->user->email;
  $foto = $cliente->user->foto;
  ```

---

## 8. Relación: Historial de citas (`citas`)

```php
    public function citas()
    {
        return $this->hasMany(Cita::class, 'cliente_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `hasMany` (Tiene muchas).
* **¿Qué significa en mis palabras?**  
  Una clienta frecuente del salón agenda y asiste a muchas citas a lo largo del tiempo.
* **Llave foránea:** Indico `'cliente_id'`, que es el campo en la tabla `citas` que apunta a esta clienta.
* **¿Para qué me sirve en el código?**  
  Me permite consultar todo el historial de tratamientos que esa clienta se ha realizado en el salón:
  ```php
  // Obtener todas las citas agendadas por la clienta
  $misCitas = $cliente->citas;

  // Ver su próxima cita confirmada
  $proximaCita = $cliente->citas()
      ->where('estado', 'confirmado')
      ->where('fecha', '>=', now()->toDateString())
      ->orderBy('fecha')
      ->first();
  ```

---

## 9. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`user()`** | `belongsTo` | `User` | `user_id` | La información personal y cuenta de acceso de la persona (nombre, correo, teléfono, foto). |
| **`citas()`** | `hasMany` | `Cita` | `cliente_id` | Todo el historial de reservas y atenciones que la clienta ha tenido en el salón. |

---

## 10. Conceptos técnicos clave que usé en este modelo

1. **Eloquent ORM:** Mapea la tabla `clientes` como un objeto interactivo para gestionar la ficha de las personas consentidas del salón.
2. **Separación de responsabilidades:** Separa los datos de autenticación (`users`) de los datos de fidelización y expediente (`clientes`).
3. **`$table`:** Declaración explícita para evitar que Laravel aplique reglas de pluralización en inglés.
4. **`$fillable`:** Mecanismo de seguridad para prevenir asignaciones masivas no autorizadas.
5. **`belongsTo`:** Relación inversa de pertenencia hacia el modelo principal `User`.
6. **`hasMany`:** Relación de uno a muchos que conecta a la clienta con todas sus citas reservadas.
