# Explicación de mi modelo `Comprobante.php` (Código por código en mis palabras)

En este archivo controlo y administro los **archivos, capturas de pantalla y soportes digitales de pago** de mi aplicación **LashApp**.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la validación real de los ingresos del salón.

---

## 1. ¿Qué representa este modelo en mi aplicación?

Es el **archivo digital de evidencias y soportes bancarios**:
- Cuando una clienta paga por transferencia bancaria (Nequi, Daviplata, Bancolombia, etc.), el sistema le permite adjuntar una foto o captura de pantalla del recibo exitoso.
- Este modelo guarda los metadatos de esa imagen o PDF: el nombre original del archivo, su formato (`jpg`, `png`, `pdf`), la ruta física donde quedó almacenado en el servidor (`ruta_archivo`) y la fecha exacta de subida (`fecha_subida`).
- Se conecta directamente con el registro de la transacción (`Pago`) para que la administradora pueda abrir la imagen en modal o pantalla completa y verificar la transferencia antes de aprobar el cobro.

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Le indica al servidor que el contenido a continuación debe ser ejecutado como código PHP.
* **`namespace App\Models;`**:  
  Declara el **espacio de nombres** de este modelo. Organiza la clase `Comprobante` dentro del paquete `App\Models`. Gracias a esto, puedo importarla en cualquier controlador (como `PagoController`) mediante la sentencia `use App\Models\Comprobante;`.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite asociar este modelo con una fábrica (`ComprobanteFactory`) para simular la subida de comprobantes en pruebas automatizadas y seeders.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase base `Model` de Eloquent ORM. Al heredar de ella, el modelo adquiere todos los métodos para crear registros (`create()`), consultar rutas de archivos y relacionarse con los pagos.

---

## 4. Declaración de la clase y uso del Trait

```php
class Comprobante extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Comprobante extends Model`**:  
  Declaro la clase `Comprobante` y la hago **heredar** (`extends`) de `Model`. De esta forma, la clase se transforma en un modelo Eloquent activo que representa los soportes de pago en la base de datos.
* **`use HasFactory;`**:  
  Habilito el trait dentro de la clase para permitir el uso del método `Comprobante::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'comprobantes';
```

### ¿Por qué escribí esto?
Por convención, Laravel busca tablas en inglés pluralizando el nombre del modelo. Con la propiedad protegida `$table = 'comprobantes'`, le indico a Laravel de manera explícita que trabaje sobre la tabla `comprobantes` en la base de datos, evitando que intente buscar nombres erróneos en inglés.

---

## 6. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'pago_id',
        'nombre_archivo',
        'tipo_archivo',
        'ruta_archivo',
        'fecha_subida',
    ];
```

### ¿Qué significa y por qué lo uso?
La propiedad protegida `$fillable` protege a mi aplicación contra ataques de asignación masiva (Mass Assignment). Funciona como una lista blanca que le indica a Laravel cuáles columnas sí pueden guardarse masivamente al procesar una subida de archivos mediante `Comprobante::create($datos)`.

### Explicación de cada campo en mis palabras:
* **`'pago_id'`**:  
  Llave foránea que enlaza el soporte con el registro de la transacción en la tabla `pagos`.
* **`'nombre_archivo'`**:  
  El nombre original del archivo tal como estaba en el dispositivo de la clienta (por ejemplo: `comprobante_nequi_valentina.jpeg`).
* **`'tipo_archivo'`**:  
  El tipo MIME o extensión del archivo (por ejemplo: `image/jpeg`, `image/png` o `application/pdf`).
* **`'ruta_archivo'`**:  
  La ruta relativa de almacenamiento en el disco del servidor donde guardé el archivo físico (por ejemplo: `comprobantes/2026/10/pago_125_ref892.jpg`).
* **`'fecha_subida'`**:  
  Fecha y hora exacta en la que se completó la subida del soporte.

---

## 7. Conversión automática de tipos de datos (`$casts`)

```php
    protected $casts = [
        'fecha_subida' => 'datetime',
    ];
```

### ¿Por qué escribí esto?
* **`'fecha_subida' => 'datetime'`**:  
  Convierte el valor de texto de la base de datos en un objeto `Carbon`. Esto me permite usar métodos de fecha como `$comprobante->fecha_subida->diffForHumans()` para saber hace cuánto tiempo se subió el soporte, o formatearlo a `'d/m/Y H:i A'`.

---

## 8. Relación: Pago al que pertenece (`pago`)

```php
    public function pago()
    {
        return $this->belongsTo(Pago::class, 'pago_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `belongsTo` (Pertenece a).
* **¿Qué significa en mis palabras?**  
  Cada comprobante o archivo adjunto le pertenece a un registro de pago específico.
* **Llave foránea:** Indico `'pago_id'`, que es la columna en la tabla `comprobantes` que apunta a la tabla `pagos`.
* **¿Para qué me sirve en el código?**  
  Si estoy revisando un archivo, puedo navegar hacia atrás y saber todo sobre la transacción, la cita y la persona que pagó:
  ```php
  // Saber el monto pagado que respalda este comprobante
  $monto = $comprobante->pago->monto;

  // Saber qué clienta envió este soporte
  $nombreClienta = $comprobante->pago->cita->cliente->user->name;
  ```

---

## 9. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`pago()`** | `belongsTo` | `Pago` | `pago_id` | El registro de pago asociado al soporte, permitiendo conocer el monto, estado, cita y clienta. |

---

## 10. Conceptos técnicos clave que usé en este modelo

1. **Eloquent ORM:** Mapea la tabla `comprobantes` como un objeto interactivo para manipular archivos de soporte de transferencias.
2. **`$table`:** Declaración explícita para sobreescribir la pluralización en inglés y apuntar con exactitud a `comprobantes`.
3. **`$casts`:** Transformación automática de `'fecha_subida'` a instancia de `Carbon` (`datetime`).
4. **`$fillable`:** Lista de seguridad que autoriza los atributos que pueden ser asignados de forma masiva.
5. **`belongsTo`:** Relación inversa de pertenencia que conecta el archivo de evidencia con su registro maestro en `Pago`.
