# Explicación de mi modelo `Categoria.php` (Código por código en mis palabras)

En este archivo controlo y administro las **categorías, familias o grupos temáticos de servicios** de mi aplicación **LashApp**.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la organización comercial del salón de belleza.

---

## 1. ¿Qué representa este modelo en mi aplicación?

Es el **clasificador y organizador del menú de tratamientos del salón**:
- Agrupa los servicios en familias claras para que la carta no sea una lista desordenada: por ejemplo, la categoría *Pestañas* agrupa procedimientos como Clásicas, Volumen Ruso y Efecto Foxy; mientras que la categoría *Cejas* agrupa Diseño con Henna, Laminado y Microblading.
- Permite filtrar en la web y en la app las opciones según el interés de la clienta.
- Controla el estado (`estado`: `'activo'` o `'inactivo'`) para ocultar o mostrar temporadas completas de servicios de forma inmediata.
- Conecta cada categoría con todos los tratamientos asociados (`Servicio`).

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Le comunica al servidor que todo el contenido a partir de este punto debe interpretarse y procesarse como código PHP.
* **`namespace App\Models;`**:  
  Declara el **espacio de nombres** del modelo. Ubica a la clase `Categoria` dentro del directorio `App\Models`. Gracias a esto, puedo invocarla limpiamente desde cualquier controlador (como `CategoriaController`, `ServicioController` o `HomeController`) usando `use App\Models\Categoria;`.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite enlazar este modelo con su fábrica (`CategoriaFactory`) para generar categorías de prueba automáticamente al ejecutar seeders en desarrollo.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase madre `Model` de Eloquent ORM. Al heredar de ella, este modelo adquiere todos los métodos automáticos para consultar registros (`all()`, `find()`, `where()`), crear nuevas categorías (`create()`) y gestionar relaciones con sus servicios.

---

## 4. Declaración de la clase y uso del Trait

```php
class Categoria extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Categoria extends Model`**:  
  Declaro la clase `Categoria` y la hago **heredar** (`extends`) de `Model`. De esta forma, mi clase se convierte formalmente en un modelo Eloquent activo que representa cada fila de la tabla `categorias`.
* **`use HasFactory;`**:  
  Habilito el trait dentro de la clase para permitir la creación ágil de categorías mediante `Categoria::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'categorias';
```

### ¿Por qué escribí esto?
Por convención, Laravel busca las tablas en inglés pluralizando el nombre del modelo (buscaría `categorias` o intentaría pluralizar en inglés como `categorias` o `categorias_table`).  
Para evitar cualquier ambigüedad en los diferentes entornos o motores de bases de datos, utilizo la propiedad protegida `$table = 'categorias'` para asegurar que Laravel se conecte exactamente a mi tabla `categorias`.

---

## 6. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];
```

### ¿Qué significa y por qué lo uso?
La propiedad protegida `$fillable` protege a mi aplicación contra ataques de asignación masiva (Mass Assignment). Funciona como una lista blanca de seguridad que le indica a Laravel cuáles atributos sí pueden ser rellenados o modificados de golpe al procesar peticiones con `Categoria::create($datos)` o `$categoria->update($datos)`.

### Explicación de cada campo en mis palabras:
* **`'nombre'`**:  
  El nombre distintivo de la categoría (por ejemplo: `'Pestañas'`, `'Cejas'`, `'Lifting'`, `'Combos Promocionales'`).
* **`'descripcion'`**:  
  Un texto descriptivo donde se explica qué tipo de procedimientos engloba esta sección de la carta.
* **`'estado'`**:  
  El estado operativo de la categoría: `'activo'` si está disponible para que las clientas elijan servicios de este grupo, o `'inactivo'` si se desea ocultar temporalmente todo el grupo de la vista pública.

---

## 7. Relación: Servicios pertenecientes a esta categoría (`servicios`)

```php
    public function servicios()
    {
        return $this->hasMany(Servicio::class, 'categoria_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `hasMany` (Tiene muchos).
* **¿Qué significa en mis palabras?**  
  Una sola categoría agrupa y contiene muchos servicios de belleza diferentes.
* **Llave foránea:** Indico explícitamente `'categoria_id'`, que es la columna en la tabla `servicios` que apunta al `id` de esta categoría.
* **¿Para qué me sirve en el código?**  
  Me permite traer de forma directa y optimizada todos los tratamientos que pertenecen a este grupo para mostrarlos en el catálogo o menú de reservas:
  ```php
  // Obtener todos los servicios activos de la categoría Pestañas
  $categoriaPestanas = Categoria::where('nombre', 'Pestañas')->first();
  $serviciosDisponibles = $categoriaPestanas->servicios()->where('estado', 'activo')->get();
  ```

---

## 8. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`servicios()`** | `hasMany` | `Servicio` | `categoria_id` (en tabla `servicios`) | Todos los tratamientos y servicios de belleza agrupados bajo esta categoría. |

---

## 9. Conceptos técnicos clave que usé en este modelo

1. **Eloquent ORM:** Mapea la tabla `categorias` como un objeto interactivo que organiza la carta comercial de LashApp.
2. **`$table`:** Declaración explícita para sobreescribir la convención de pluralización en inglés y conectar exactamente con `categorias`.
3. **`$fillable`:** Mecanismo de seguridad para prevenir la inyección de atributos no autorizados en asignaciones masivas.
4. **`hasMany`:** Relación de uno a muchos donde la tabla de destino (`servicios`) almacena la clave foránea (`categoria_id`) que referencia a este registro.
