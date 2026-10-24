# Explicación de mi modelo `Cita.php` (Código por código en mis palabras)

En este archivo controlo y administro el **núcleo y corazón operativo absoluto de mi aplicación LashApp**: el agendamiento y gestión integral de cada cita en el salón de belleza.

Casi toda la lógica del negocio converge en este modelo: conecta a la clienta, a la especialista (lashista), al servicio contratado, al turno de trabajo, a los pagos realizados y a las calificaciones o reseñas recibidas.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la vida real dentro del salón.

---

## 1. ¿Qué representa este modelo en mi aplicación?

Es la **hoja digital de reserva de la agenda**:
- Contiene toda la información de un compromiso: quién es la clienta (`cliente_id`), qué lashista la atenderá (`trabajador_id`), qué procedimiento se realizará (`servicio_id`) y en qué jornada laboral encaja (`horario_id`).
- Controla el tiempo exacto de atención (`fecha`, `hora_inicio`, `hora_fin`) para evitar que se empalmen dos citas en la misma camilla o con la misma especialista.
- Controla el ciclo de vida del compromiso (`estado`: `'pendiente'`, `'confirmado'`, `'finalizado'`, `'cancelado'`).
- Guarda notas especiales (`observaciones`) como alergias o preferencias de diseño, y la fecha exacta en la que se generó la reserva (`fecha_creacion`).

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Le comunica al servidor que todo el código subsiguiente debe ejecutarse bajo el intérprete de PHP.
* **`namespace App\Models;`**:  
  Declara el **espacio de nombres** de este modelo. Organiza a la clase `Cita` dentro de la carpeta lógica `App\Models`. Gracias a esto, puedo invocarla limpiamente desde cualquier controlador (como `CitaController`, `AgendaController` o `DashboardController`) usando `use App\Models\Cita;`.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite vincular el modelo con una fábrica (`CitaFactory`) para generar reservas ficticias y poblar el calendario con citas de prueba al ejecutar seeders.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase base `Model` de Eloquent ORM. Al extender de ella, el modelo adquiere todos los métodos para consultar agendas (`where()`, `whereBetween()`), verificar disponibilidad y gestionar relaciones de base de datos.

---

## 4. Declaración de la clase y uso del Trait

```php
class Cita extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Cita extends Model`**:  
  Declaro mi clase `Cita` y la hago **heredar** (`extends`) de `Model`. De esta forma, `Cita` se convierte en un modelo Eloquent activo que representa cada reserva registrada en la base de datos.
* **`use HasFactory;`**:  
  Habilito el trait dentro de la clase para permitir la generación rápida de citas mediante `Cita::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'citas';
```

### ¿Por qué escribí esto?
Por convención, Laravel busca tablas en inglés pluralizando el nombre del modelo. Con la propiedad protegida `$table = 'citas'`, le indico a Laravel de forma explícita que trabaje directamente sobre la tabla `citas` en la base de datos.

---

## 6. Desactivación de marcas de tiempo automáticas (`$timestamps`)

```php
    public $timestamps = false; // La tabla usa fecha_creacion en vez de created_at/updated_at
```

### ¿Por qué escribí esto y qué significa?
Por defecto, Laravel espera que todas las tablas de base de datos tengan dos columnas llamadas `created_at` y `updated_at`.  
Como el diseño de mi tabla en la base de datos utiliza un campo personalizado llamado `fecha_creacion` y no utiliza columnas en inglés, pongo `public $timestamps = false;` para apagar la gestión automática de fechas de Laravel y evitar errores SQL al intentar guardar o editar citas.

---

## 7. Atributos asignables de forma masiva (`$fillable`)

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

### ¿Qué significa y por qué lo uso?
La propiedad `$fillable` protege a mi aplicación contra ataques de asignación masiva (Mass Assignment). Funciona como una lista blanca de seguridad que le indica a Laravel cuáles atributos sí pueden ser guardados masivamente mediante `Cita::create($datos)` o `$cita->update($datos)`.

### Explicación de cada campo en mis palabras:
* **`'cliente_id'`**: Llave foránea que identifica a la clienta que solicitó la cita.
* **`'trabajador_id'`**: Llave foránea que apunta a la especialista o lashista que atenderá el procedimiento.
* **`'servicio_id'`**: Llave foránea del procedimiento reservado (ej: *Volumen Ruso*, *Lifting de Pestañas*).
* **`'horario_id'`**: Llave foránea que vincula la cita con la jornada laboral general de la trabajadora.
* **`'fecha'`**: El día exacto en que se realizará la cita (ej: `2026-10-25`).
* **`'hora_inicio'` y `'hora_fin'`**: El bloque horario de atención (ej: de `10:00:00` a `12:00:00`), calculado sumando la duración en minutos del servicio.
* **`'estado'`**: La condición actual de la reserva: `'pendiente'`, `'confirmado'`, `'finalizado'` o `'cancelado'`.
* **`'observaciones'`**: Notas especiales (alergias a adhesivos, solicitud de efecto foxy discreto, etc.).
* **`'fecha_creacion'`**: Fecha y hora exacta en la que se registró la reserva en el sistema.

---

## 8. Conversión automática de tipos de datos (`$casts`)

```php
    protected $casts = [
        'fecha' => 'date',
        'fecha_creacion' => 'datetime',
    ];
```

### ¿Por qué escribí esto?
* **`'fecha' => 'date'`**: Transforma la fecha en un objeto `Carbon`. Así puedo formatearla en vistas Blade en español (ej: `$cita->fecha->translatedFormat('l, d \d\e F')`) o compararla con `now()->toDateString()`.
* **`'fecha_creacion' => 'datetime'`**: Convierte el momento de reserva en una instancia de fecha y hora completa para mostrar textos amigables como `$cita->fecha_creacion->diffForHumans()` (*"agendada hace 15 minutos"*).

---

## 9. Relaciones del Modelo

Este modelo es el punto de encuentro de casi toda la base de datos de LashApp:

### 1. La clienta (`cliente`)
```php
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
```
* **Tipo:** `belongsTo`. Cada cita pertenece a una única clienta.
* **Uso:** Me permite acceder a `$cita->cliente->user->name` o `$cita->cliente->user->telefono` para saber de inmediato a quién vamos a atender o escribirle por WhatsApp.

### 2. La trabajadora o lashista (`trabajador`)
```php
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }
```
* **Tipo:** `belongsTo`. Cada cita está asignada a una única especialista.
* **Uso:** Me dice quién tiene asignada la atención (`$cita->trabajador->user->name`) y permite filtrar la agenda personal de cada empleada.

### 3. El servicio contratado (`servicio`)
```php
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
```
* **Tipo:** `belongsTo`. Cada cita corresponde a un servicio del catálogo.
* **Uso:** Me permite saber qué tratamiento se realizará y su precio (`$cita->servicio->nombre`, `$cita->servicio->precio`).

### 4. El horario laboral (`horario`)
```php
    public function horario()
    {
        return $this->belongsTo(Horario::class, 'horario_id');
    }
```
* **Tipo:** `belongsTo`. Vincula la cita con el turno general en el que fue programada.

### 5. El historial de pagos (`pagos`)
```php
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'cita_id');
    }
```
* **Tipo:** `hasMany`. Una cita puede tener múltiples abonos o transacciones de pago asociadas (ej: un anticipo del 50% y el saldo restante el día de la cita).

### 6. El último pago registrado (`pago`)
```php
    public function pago()
    {
        return $this->hasOne(Pago::class, 'cita_id')->latestOfMany();
    }
```
* **Tipo:** `hasOne` con `latestOfMany()`.
* **Uso:** Devuelve directamente el **pago más reciente** registrado para esa cita sin tener que iterar arreglos en las vistas.

### 7. La calificación y opinión (`resena`)
```php
    public function resena()
    {
        return $this->hasOne(Resena::class, 'cita_id');
    }
```
* **Tipo:** `hasOne`. Una cita puede tener una única reseña de satisfacción dejada por la clienta tras finalizar su servicio.

---

## 10. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`cliente()`** | `belongsTo` | `Cliente` | `cliente_id` | La clienta que reservó la cita (nombre, teléfono, correo). |
| **`trabajador()`** | `belongsTo` | `Trabajador` | `trabajador_id` | La especialista encargada de aplicar el tratamiento. |
| **`servicio()`** | `belongsTo` | `Servicio` | `servicio_id` | El procedimiento reservado, su costo y duración. |
| **`horario()`** | `belongsTo` | `Horario` | `horario_id` | El turno laboral en el que encaja la reserva. |
| **`pagos()`** | `hasMany` | `Pago` | `cita_id` | Todos los pagos o abonos registrados para la cita. |
| **`pago()`** | `hasOne` + `latestOfMany()` | `Pago` | `cita_id` | La transacción de cobro más reciente. |
| **`resena()`** | `hasOne` | `Resena` | `cita_id` | La calificación de estrellas y opinión dejada por la clienta. |

---

## 11. Conceptos técnicos clave que usé en este modelo

1. **Eloquent ORM:** Mapea la tabla central `citas` como un objeto interactivo que conecta a todas las entidades de la aplicación.
2. **`public $timestamps = false;`:** Desactiva las columnas predeterminadas `created_at` y `updated_at` para respetar el campo personalizado `fecha_creacion`.
3. **`$casts`:** Transformación automática de fechas a instancias de `Carbon` (`date` y `datetime`).
4. **`latestOfMany()`:** Modificador avanzado de Eloquent para obtener directamente el último registro de una relación uno a muchos como una relación `hasOne`.
5. **Navegación relacional multidireccional:** Capacidad de navegar fluidamente entre clientas, trabajadoras, pagos y calificaciones desde un solo registro.
