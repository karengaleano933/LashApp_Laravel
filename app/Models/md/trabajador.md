# Explicación de mi modelo `Trabajador.php` (Código por código en mis palabras)

En este archivo controlo y administro la **ficha laboral y profesional de cada especialista** (lashistas, técnicas en pestañas y diseñadoras de cejas) de mi aplicación **LashApp**.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la vida real dentro del salón.

---

## 1. ¿Qué representa este modelo en mi aplicación?

En LashApp tengo separados los datos de acceso de los datos profesionales:
- En `User.php` guardo los datos generales de la persona para que pueda iniciar sesión (su correo, contraseña, rol, teléfono y foto).
- En este modelo `Trabajador.php` guardo su **perfil profesional**: en qué técnicas es especialista (por ejemplo: *"Efecto Foxy"*, *"Volumen Ruso"*, *"Lifting de Pestañas"*), una breve biografía sobre su experiencia y si se encuentra activa para recibir clientas.

Además, este modelo es el puente que conecta a la especialista con sus **turnos de trabajo** (`Horario`) y con todas las **citas agendadas** (`Cita`).

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Con esto le indico al servidor y al intérprete que a partir de aquí todo el contenido debe ejecutarse como código PHP.
* **`namespace App\Models;`**:  
  Aquí defino el **espacio de nombres** de mi archivo. Le dice a Laravel que esta clase pertenece al paquete o carpeta lógica `App\Models`. Gracias a esto, Laravel puede encontrar y cargar mi modelo automáticamente cuando lo necesite en cualquier controlador, seeder o vista usando `use App\Models\Trabajador;`, sin riesgo de que choque con otras clases que tengan nombres similares.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Esto me permite conectar este modelo con una fábrica de datos (`TrabajadorFactory`), lo cual es muy útil para generar especialistas ficticias o de prueba cuando estoy poblando la base de datos con seeders.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase base `Model` del núcleo de Laravel (Eloquent ORM). Es la clase madre que contiene toda la magia: me da métodos automáticos como `all()`, `find()`, `where()`, `create()`, `update()` y `delete()`, para que yo no tenga que escribir consultas SQL complejas a mano.

---

## 4. Declaración de la clase y uso del Trait

```php
class Trabajador extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Trabajador extends Model`**:  
  Aquí declaro formalmente mi clase llamada `Trabajador` y la hago **heredar** (`extends`) de `Model`. Al extender de `Model`, mi clase se transforma instantáneamente en un modelo Eloquent, adquiriendo la capacidad de comunicarse directamente con la tabla correspondiente en la base de datos.
* **`use HasFactory;`**:  
  Aquí dentro de la clase incorporo el trait que importé arriba. Con esta sola línea le doy la habilidad a mi modelo de utilizar fábricas mediante `Trabajador::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'trabajadores';
```

### ¿Por qué escribí esto?
Por convención, Laravel asume que los nombres de las tablas en la base de datos están en inglés y en plural. Si mi modelo se llama `Trabajador`, Laravel intentaría buscar por defecto una tabla llamada `trabajadors`.  
Como el idioma de mi base de datos es español y mi tabla real se llama `trabajadores`, utilizo la propiedad protegida `$table` para ordenarle a Laravel: *"No adivines el nombre, conéctate exactamente a la tabla `trabajadores`"*.

---

## 6. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'user_id',
        'especialidad',
        'descripcion',
        'estado',
    ];
```

### ¿Qué significa y por qué lo uso?
La propiedad `$fillable` es una lista blanca de seguridad (Mass Assignment Protection). Le indica a Laravel cuáles columnas de la tabla sí permito que se guarden o modifiquen de golpe mediante métodos como `Trabajador::create($datos)` o `$trabajador->update($datos)`. Así evito que un usuario malintencionado intente alterar campos no autorizados desde un formulario.

### Explicación de cada campo en mis palabras:
* **`'user_id'`**:  
  Es la llave foránea que vincula este perfil profesional con un registro de la tabla `users`. Gracias a este número sé a cuál usuaria le pertenece esta ficha laboral (su nombre, correo, teléfono y foto).
* **`'especialidad'`**:  
  Guarda en qué técnicas se destaca la profesional (por ejemplo: *"Especialista en Volumen Ruso y Lifting de Pestañas"* o *"Diseño y Pigmentación de Cejas con Henna"*).
* **`'descripcion'`**:  
  Es un texto más largo donde se detalla la biografía, años de experiencia o certificaciones de la lashista. Esto se muestra en la web o aplicación para generar confianza en las clientas al momento de reservar.
* **`'estado'`**:  
  Indica la condición operativa de la trabajadora en el salón (por ejemplo: `'activo'` si está trabajando y disponible para recibir citas, o `'inactivo'` si se encuentra de permiso, vacaciones o ya no labora en el negocio).

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
  Una ficha de trabajadora le pertenece a un único usuario del sistema.
* **Llave foránea:** Indico explícitamente `'user_id'`, que es el campo en mi tabla `trabajadores` que guarda el ID de la tabla `users`.
* **¿Para qué me sirve en el código?**  
  Si tengo una instancia de una trabajadora (`$trabajador`), puedo acceder inmediatamente a todos los datos personales de su cuenta sin hacer consultas manuales:
  ```php
  $nombre = $trabajador->user->name;     // Obtengo su nombre
  $telefono = $trabajador->user->telefono; // Obtengo su teléfono
  $foto = $trabajador->user->foto;       // Obtengo su foto de perfil
  ```

---

## 8. Relación: Citas asignadas (`citas`)

```php
    public function citas()
    {
        return $this->hasMany(Cita::class, 'trabajador_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `hasMany` (Tiene muchas).
* **¿Qué significa en mis palabras?**  
  Una especialista o lashista atiende muchas citas a lo largo del tiempo.
* **Llave foránea:** Indico `'trabajador_id'`, que es la columna en la tabla `citas` que apunta a esta trabajadora.
* **¿Para qué me sirve en el código?**  
  Me permite consultar rápidamente toda la agenda de citas que tiene asignada una trabajadora:
  ```php
  // Obtener todas las citas de hoy de la trabajadora
  $misCitasDeHoy = $trabajador->citas()->where('fecha', now()->toDateString())->get();

  // Contar cuántas citas ha completado en el mes
  $totalAtendidas = $trabajador->citas()->where('estado', 'finalizado')->count();
  ```

---

## 9. Relación: Horarios y turnos laborales (`horarios`)

```php
    public function horarios()
    {
        return $this->hasMany(Horario::class, 'trabajador_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `hasMany` (Tiene muchos).
* **¿Qué significa en mis palabras?**  
  Una trabajadora tiene varios registros de horarios configurados (por ejemplo, su horario del lunes, del martes, etc.).
* **Llave foránea:** Indico `'trabajador_id'`, que es la columna en la tabla `horarios` que define a qué especialista le pertenece ese turno.
* **¿Para qué me sirve en el código?**  
  Me permite saber qué días y en qué rango de horas labora la especialista para que el sistema de reservas nunca permita agendar una cita fuera de su jornada o en su día de descanso:
  ```php
  // Ver los turnos configurados para la trabajadora
  $turnos = $trabajador->horarios;
  ```

---

## 10. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`user()`** | `belongsTo` | `User` | `user_id` | La información personal y cuenta de acceso de la especialista (nombre, correo, teléfono, foto). |
| **`citas()`** | `hasMany` | `Cita` | `trabajador_id` | El historial completo y la agenda de citas asignadas a la lashista. |
| **`horarios()`** | `hasMany` | `Horario` | `trabajador_id` | Los turnos y horas de atención en los que la especialista puede recibir clientas. |

---

## 11. Conceptos técnicos clave que usé en este modelo

1. **Eloquent ORM:** Es el mapeador objeto-relacional de Laravel que me permite tratar cada fila de la tabla `trabajadores` como un objeto PHP interactivo.
2. **`$table`:** Propiedad para sobreescribir el nombre plural en inglés predeterminado de Laravel y usar el nombre real en español (`trabajadores`).
3. **`$fillable`:** Mecanismo de seguridad para autorizar únicamente los campos que pueden ser rellenados de forma masiva desde formularios o peticiones JSON.
4. **`belongsTo`:** Relación inversa de pertenencia (de muchos a uno) donde la tabla actual contiene la llave foránea hacia otra tabla.
5. **`hasMany`:** Relación de uno a muchos donde la tabla foránea contiene la referencia hacia este modelo.
