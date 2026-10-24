Explicación de mi modelo `Pago.php` (Código por código en mis palabras)

En este archivo controlo y administro el *módulo financiero, ingresos de dinero y cobros* de mi aplicación *LashApp*.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la administración real de la caja del salón.

---

 1. ¿Qué representa este modelo en mi aplicación?

Es el *recibo o comprobante contable digital* de cada cobro:
- Registra cuánto dinero pagó la clienta (`monto`), cómo lo pagó (`metodo_pago`: efectivo, Nequi, Daviplata, transferencia o tarjeta) y en qué estado se encuentra la transacción (`estado`: completado, pendiente o rechazado).
- Guarda la fecha y hora exacta del pago (`fecha_pago`) y cualquier nota o referencia bancaria (`observaciones`).
- Se conecta de forma directa con la cita atendida (`Cita`) y con las fotos o capturas de pantalla de las transferencias adjuntas (`Comprobante`).

---

 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

 Explicación de cada línea:
* `<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Le indica al servidor que a partir de aquí todas las instrucciones deben ser interpretadas como código ejecutable de PHP.
* `namespace App\Models;`**:  
  Define el *espacio de nombres** de este modelo. Organiza a la clase `Pago` dentro del directorio `App\Models`. Con esto, puedo importarla fácilmente en cualquier controlador financiero (como `PagoController` o `CitaController`) con `use App\Models\Pago;` sin generar colisiones con otras clases.

---

 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

 Explicación de cada línea:
*`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite enlazar este modelo con su fábrica (`PagoFactory`) para generar pagos y transacciones simuladas al poblar la base de datos con seeders durante el desarrollo.
*`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase base `Model` de Eloquent ORM. Al heredar de ella, el modelo adquiere de inmediato todas las funciones de base de datos como `create()`, `find()`, `where()`, `sum()`, relaciones y casts.

---

 4. Declaración de la clase y uso del Trait

```php
class Pago extends Model
{
    use HasFactory;
```
 Explicación de cada línea:
`class Pago extends Model`**:  
  Declaro mi clase `Pago` y la hago **heredar** (`extends`) de `Model`. Esto la transforma en un modelo Eloquent activo que representa cada fila de transacciones de pago en la base de datos.
`use HasFactory;`**:  
  Habilito el trait dentro de la clase para permitir la creación rápida de pagos de prueba mediante `Pago::factory()`.

---

 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'pagos';
```
 ¿Por qué escribí esto?
Por convención, Laravel busca tablas en inglés pluralizando el nombre del modelo. Con la propiedad protegida `$table = 'pagos'`, le indico a Laravel de manera explícita y sin lugar a dudas que trabaje directamente sobre mi tabla `pagos` en la base de datos.

---

 6. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'cita_id',
        'metodo_pago',
        'monto',
        'estado',
        'fecha_pago',
        'observaciones',
    ];
```

   ¿Qué significa y por qué lo uso?
La propiedad `$fillable` protege contra ataques de asignación masiva (Mass Assignment). Funciona como una lista blanca que le dice a Laravel cuáles campos sí pueden guardarse masivamente al procesar formularios o peticiones mediante `Pago::create($datos)` o `$pago->update($datos)`.
 Explicación de cada campo en mis palabras:
`'cita_id'`**:  
  Llave foránea que enlaza el cobro con la cita en la tabla `citas`. Así sé qué servicio y qué cita originó este ingreso.
`'metodo_pago'`**:  
  La forma en que la clienta canceló el valor (por ejemplo: `'Efectivo'`, `'Nequi'`, `'Daviplata'`, `'Transferencia'` o `'Tarjeta'`).
`'monto'`**:  
  El valor numérico en dinero cobrado a la clienta (por ejemplo: `80000.00`).
`'estado'`**:  
  El estado del dinero: `'completado'` si ya se recibió y verificó en la cuenta, `'pendiente'` si es una transferencia por validar, o `'rechazado'` si el comprobante no fue válido.
`'fecha_pago'`**:  
  Fecha y hora exacta en la que se efectuó el pago.
`'observaciones'`**:  
  Notas adicionales, como el número de confirmación de Nequi o detalles de cambio/vueltos.

---

 7. Conversión automática de tipos de datos (`$casts`)

```php
    protected $casts = [
        'fecha_pago' => 'datetime',
        'monto' => 'decimal:2',
    ];
```

 ¿Por qué escribí esto?
La propiedad `$casts` le enseña a Laravel cómo convertir automáticamente los atributos al leerlos o guardarlos en la base de datos:
`'fecha_pago' => 'datetime'`**:  
  Transforma la fecha de un simple texto a una instancia de `Carbon`. Esto me permite usar métodos como `$pago->fecha_pago->format('d/m/Y')` o filtrar reportes mensuales fácilmente.
`'monto' => 'decimal:2'`**:  
  Garantiza que el monto se trate siempre como un número decimal con 2 cifras decimales, evitando descuadres de centavos en la contabilidad y balances del negocio.

---

 8. Relación: Cita asociada al pago (`cita`)

```php
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
```

 Explicación detallada:
Tipo de relación:** `belongsTo` (Pertenece a).
¿Qué significa en mis palabras?**  
  Cada cobro o pago pertenece a una cita específica del salón.
Llave foránea:** Indico `'cita_id'`, que es el campo en mi tabla `pagos` que contiene el ID de la cita correspondiente.
¿Para qué me sirve en el código?**  
  Si tengo el pago en la mano, puedo navegar hacia atrás y saber todo sobre la clienta y el servicio:
  ```php
  // Saber quién pagó y qué servicio se realizó
  $nombreClienta = $pago->cita->cliente->user->name;
  $servicioPagado = $pago->cita->servicio->nombre;
  ```

---

 9. Relaciones: Comprobantes adjuntos (`comprobantes` y `comprobante`)

```php
    public function comprobantes()
    {
        return $this->hasMany(Comprobante::class, 'pago_id');
    }

    public function comprobante()
    {
        return $this->hasOne(Comprobante::class, 'pago_id')->latestOfMany();
    }
```

 Explicación detallada:
`comprobantes()` (`hasMany`):**  
  Un pago puede tener varios archivos de comprobante subidos (por ejemplo, si la clienta subió primero una captura cortada y luego la completa). Con `'pago_id'` conecto todos los registros asociados de la tabla `comprobantes`.
`comprobante()` con `latestOfMany()` (`hasOne`):**  
  Es un método de conveniencia de Eloquent muy potente. En lugar de iterar un arreglo, me devuelve directamente el **último comprobante subido** más reciente.
¿Para qué me sirve en el código?**  
  Puedo obtener al instante la imagen actual del recibo sin hacer bucles ni filtros manuales:
  ```php
  // Obtener la URL del último comprobante bancario para verificarlo
  $urlFoto = $pago->comprobante?->archivo_url;
  ```

---

 10. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| `cita()` | `belongsTo` | `Cita` | `cita_id` | La cita a la que corresponde este dinero, permitiendo conocer a la clienta y el servicio. |
| `comprobantes()` | `hasMany` | `Comprobante` | `pago_id` | La lista completa de fotos o soportes de pago adjuntos a la transacción. |
| `comprobante()` | `hasOne` + `latestOfMany()` | `Comprobante` | `pago_id` | El soporte o comprobante más reciente cargado para este pago. |

---

 11. Conceptos técnicos clave que usé en este modelo

1. Eloquent ORM:** Mapea la tabla financiera `pagos` como un objeto interactivo en Laravel.
2. $casts:** Convierte tipos de datos automáticamente (`datetime` para fechas con Carbon y `decimal:2` para precisión monetaria).
3. $fillable:** Mecanismo de seguridad para autorizar únicamente las columnas que se pueden modificar o crear en masa.
4. latestOfMany():** Modificador avanzado de Eloquent que simplifica relaciones de uno a muchos para obtener automáticamente el registro más reciente como una relación `hasOne`.
5. belongsTo:** Relación donde este modelo aloja la clave foránea (`cita_id`) que apunta a su registro padre.
