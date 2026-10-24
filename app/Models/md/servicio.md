# Explicación de mi modelo `Servicio.php` (Código por código en mis palabras)

En este archivo controlo y administro el **catálogo oficial de servicios y tratamientos de belleza** (pestañas pelo a pelo, volumen ruso, lifting, laminado de cejas, etc.) de mi aplicación **LashApp**.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la operativa real dentro del salón.

---

## 1. ¿Qué representa este modelo en mi aplicación?

Es la **carta comercial o menú de servicios** que ofrecemos a las clientas:
- Contiene los detalles económicos y técnicos de cada tratamiento: su nombre, descripción, cuánto cuesta (`precio`), cuánto tiempo tarda la especialista en realizarlo (`duracion_minuto`) y su foto promocional (`imagen_url`).
- Es un modelo crítico para el negocio: la `duracion_minuto` le permite a mi sistema calcular automáticamente la hora en que finaliza una cita (`hora_fin = hora_inicio + duracion_minuto`) para que los horarios no se crucen.
- Conecta cada procedimiento con la categoría a la que pertenece (`Categoria`) y con el historial de reservas donde se ha solicitado (`Cita`).

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura con la que le indico al servidor que el archivo contiene código fuente de PHP que debe ser interpretado y ejecutado.
* **`namespace App\Models;`**:  
  Define el **espacio de nombres** de la clase. Ubica a mi modelo dentro del paquete `App\Models`, organizándolo junto con los demás modelos de mi proyecto. Esto me permite importarlo limpiamente en controladores como `ServicioController` o `CitaController` usando la sentencia `use App\Models\Servicio;`.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite vincular este modelo con una fábrica de datos (`ServicioFactory`), muy útil para generar servicios de prueba automáticamente al ejecutar mis seeders de desarrollo.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase fundamental `Model` de Eloquent ORM. Al extender de ella, obtengo automáticamente toda la funcionalidad para consultar, guardar, actualizar o borrar registros en la base de datos sin tener que escribir consultas SQL manuales.

---

## 4. Declaración de la clase y uso del Trait

```php
class Servicio extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Servicio extends Model`**:  
  Declaro mi clase `Servicio` y hago que **herede** (`extends`) de `Model`. Esto transforma mi clase en un modelo activo de Eloquent, capaz de interactuar con la tabla de servicios como si cada fila fuera un objeto PHP.
* **`use HasFactory;`**:  
  Activo el trait dentro de la clase para que `Servicio` pueda utilizar fábricas mediante `Servicio::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'servicios';
```

### ¿Por qué escribí esto?
Por regla predeterminada, Laravel busca las tablas en inglés pluralizando el nombre del modelo (buscaría `servicios` sólo por coincidencia, o `servicios` en caso de intentar pluralizar en inglés como `servicios` o `servicios_table`).  
Para evitar cualquier ambigüedad o problema entre diferentes entornos y motores de base de datos, defino la propiedad protegida `$table = 'servicios'` asegurando que siempre apunte de forma exacta a mi tabla `servicios`.

---

## 6. Atributos asignables de forma masiva (`$fillable`)

```php
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

### ¿Qué significa y por qué lo uso?
La propiedad `$fillable` protege a mi aplicación contra vulnerabilidades de asignación masiva (Mass Assignment). Define una lista blanca de campos que sí autorizo a ser creados o modificados en masa con `Servicio::create($request->all())` o `$servicio->update(...)`.

### Explicación de cada campo en mis palabras:
* **`'categoria_id'`**:  
  Es la llave foránea que asocia el servicio a una categoría específica de la tabla `categorias` (por ejemplo: *Pestañas*, *Cejas*, *Depilación*, *Combos*).
* **`'nombre'`**:  
  El nombre comercial del tratamiento (por ejemplo: *"Volumen Ruso 3D"*, *"Lifting de Pestañas + Tinte"*, *"Diseño de Cejas con Henna"*).
* **`'descripcion'`**:  
  Texto explicativo donde detallo qué incluye el procedimiento, cuidados posteriores y recomendaciones para que la clienta sepa exactamente qué está contratando.
* **`'precio'`**:  
  El costo en dinero del servicio (por ejemplo: `120000.00`). Es el valor que tomará el módulo de cobro y facturación al momento de agendar o liquidar la cita.
* **`'duracion_minuto'`**:  
  La duración estimada en minutos que demora la especialista en realizar el procedimiento (por ejemplo: `60`, `90`, `120` minutos). Esto es indispensable para la agenda del salón, pues bloquea el tiempo exacto en el calendario para que no se empalmen citas.
* **`'estado'`**:  
  Indica si el servicio está disponible para el público (`'activo'`) o si ha sido pausado temporalmente (`'inactivo'`) por falta de insumos o cambios de temporada.
* **`'imagen_url'`**:  
  La ruta o URL de la fotografía del trabajo terminado para mostrarla en las tarjetas y carruseles del catálogo visual de LashApp.

---

## 7. Relación: Categoría a la que pertenece (`categoria`)

```php
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `belongsTo` (Pertenece a).
* **¿Qué significa en mis palabras?**  
  Cada servicio individual pertenece a una categoría general (muchos servicios pertenecen a una categoría).
* **Llave foránea:** Indico explícitamente `'categoria_id'`, que es el campo en mi tabla `servicios` que almacena el ID de la tabla `categorias`.
* **¿Para qué me sirve en el código?**  
  Me permite acceder de inmediato a la información de la categoría desde una instancia del servicio:
  ```php
  // Obtener el nombre de la categoría del servicio
  $categoriaNombre = $servicio->categoria->nombre; // Ej: "Pestañas"
  ```

---

## 8. Relación: Citas donde se ha contratado este servicio (`citas`)

```php
    public function citas()
    {
        return $this->hasMany(Cita::class, 'servicio_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `hasMany` (Tiene muchas).
* **¿Qué significa en mis palabras?**  
  Un servicio puede haber sido reservado y realizado en muchas citas a lo largo del tiempo.
* **Llave foránea:** Indico `'servicio_id'`, que es la columna en la tabla `citas` que apunta a este servicio.
* **¿Para qué me sirve en el código?**  
  Me permite realizar consultas analíticas, reportes y métricas de negocio para el salón:
  ```php
  // Saber cuántas veces se ha reservado este servicio en total
  $totalVecesVendido = $servicio->citas()->count();

  // Obtener todas las citas realizadas con este servicio
  $citasConcluidas = $servicio->citas()->where('estado', 'finalizado')->get();
  ```

---

## 9. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`categoria()`** | `belongsTo` | `Categoria` | `categoria_id` | La categoría o grupo al que pertenece este tratamiento (ej. *Pestañas*, *Cejas*). |
| **`citas()`** | `hasMany` | `Cita` | `servicio_id` | Todas las reservas y atenciones agendadas en el salón donde se solicitó este servicio. |

---

## 10. Conceptos técnicos clave que usé en este modelo

1. **Eloquent ORM:** El sistema de mapeo objeto-relacional de Laravel que convierte cada registro de la tabla `servicios` en una instancia manipulable con métodos y propiedades PHP.
2. **`$table`:** Propiedad para declarar explícitamente el nombre de la tabla (`servicios`) en la base de datos.
3. **`$fillable`:** Mecanismo de seguridad para prevenir ataques de asignación masiva al autorizar únicamente los campos especificados.
4. **`belongsTo`:** Relación de muchos a uno en la que la tabla del modelo actual guarda la clave foránea hacia la tabla principal (`categoria_id` -> `categorias.id`).
5. **`hasMany`:** Relación de uno a muchos en la que múltiples registros externos contienen una clave foránea que apunta a este modelo (`citas.servicio_id` -> `servicios.id`).
