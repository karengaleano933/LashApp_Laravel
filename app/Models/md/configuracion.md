# Explicación de mi modelo `Configuracion.php` (Código por código en mis palabras)

En este archivo controlo y administro los **parámetros globales, la identidad del negocio y las reglas operativas** de mi aplicación **LashApp**.

A diferencia de los demás modelos donde existen cientos de registros de usuarias o citas, este modelo está diseñado para almacenar **una única fila maestra** con la configuración central de mi salón de belleza.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en el funcionamiento del sistema.

---

## 1. ¿Qué representa este modelo en mi aplicación?

Es el **cerebro de configuración y membrete institucional** del salón:
- Guarda los datos de contacto y marca: nombre comercial (*Oh La Lash by Lorena Ayala Studio*), teléfono de WhatsApp, correo, dirección física y ruta del logo.
- Controla las reglas del motor de citas: antelación mínima para reservar o cancelar, tiempo de desinfección entre citas (`tiempo_entre_citas = '15m'`), duración estándar y términos de la política de cancelación.
- Parámetros regionales y fiscales: moneda (`COP`), porcentaje de IVA (`19%`), zona horaria (`America/Bogota`) y formatos de fecha/hora.
- Implementa un patrón **Singleton inteligente** a través del método `getInstancia()` que auto-crea la configuración si la tabla está vacía, asegurando que el sistema jamás falle por falta de datos.

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Le comunica al servidor que todo el contenido a partir de este punto debe interpretarse como código ejecutable de PHP.
* **`namespace App\Models;`**:  
  Declara el **espacio de nombres** de este modelo. Organiza a la clase `Configuracion` dentro de la carpeta lógica `App\Models`. Gracias a esto, puedo invocarla en cualquier parte (controladores, vistas Blade, proveedores de servicios) mediante `use App\Models\Configuracion;`.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite enlazar este modelo con una fábrica (`ConfiguracionFactory`) en caso de necesitar generar datos de prueba automatizados.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase base `Model` de Eloquent ORM. Al heredar de ella, este modelo adquiere todos los métodos nativos para consultar (`firstOrCreate()`), actualizar (`update()`) y persistir datos en la base de datos sin escribir SQL manual.

---

## 4. Declaración de la clase y uso del Trait

```php
class Configuracion extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Configuracion extends Model`**:  
  Declaro la clase `Configuracion` y la hago **heredar** (`extends`) de `Model`. De este modo, la clase se convierte en un modelo Eloquent activo que gestiona la tabla de configuraciones.
* **`use HasFactory;`**:  
  Habilito el trait dentro de la clase para permitir el uso del método `Configuracion::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'configuraciones';
```

### ¿Por qué escribí esto?
Por convención, Laravel pluraliza en inglés los nombres de las tablas (buscaría `configuracions`).  
Como el nombre real en mi base de datos en español es `configuraciones`, especifico la propiedad protegida `$table = 'configuraciones'` para obligar a Laravel a conectarse exactamente a esa tabla.

---

## 6. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'nombre_estudio',
        'telefono',
        'email_contacto',
        'direccion',
        'zona_horaria',
        'logo_path',
        'idioma',
        'moneda',
        'formato_fecha',
        'formato_hora',
        'reservas_online',
        'recordatorios_auto',
        'antelacion_reservar',
        'antelacion_cancelar',
        'tiempo_entre_citas',
        'duracion_predeterminada',
        'politica_cancelacion',
        'iva_porcentaje',
        'ultima_copia_seguridad',
    ];
```

### ¿Qué significa y por qué lo uso?
La propiedad protegida `$fillable` protege a mi aplicación contra ataques de asignación masiva (Mass Assignment). Define la lista de columnas que sí tienen permiso de ser actualizadas en bloque desde el formulario de ajustes del panel administrativo.

### Explicación de cada campo en mis palabras:
* **`'nombre_estudio'`**: El nombre de la marca del salón que aparece en los encabezados, correos y facturas.
* **`'telefono'`**: Número oficial de contacto o línea de WhatsApp del salón.
* **`'email_contacto'`**: Correo institucional para notificaciones y atención al cliente.
* **`'direccion'`**: Ubicación física del local donde las clientas asisten a sus citas.
* **`'zona_horaria'`**: Zona horaria oficial del negocio (ej: `'America/Bogota'`) para que los relojes y citas coincidan con la hora local.
* **`'logo_path'`**: La ruta del archivo de imagen del logo para pintarlo en la barra de navegación y comprobantes.
* **`'idioma'`**: Código del idioma del sistema (ej: `'es'`).
* **`'moneda'`**: Símbolo o código monetario para los precios (ej: `'COP'`).
* **`'formato_fecha'` y `'formato_hora'`**: Preferencias visuales de fecha (`'DD/MM/YYYY'`) y reloj (`'12'` o `'24'` horas).
* **`'reservas_online'`**: Interruptor booleano (`true`/`false`) que permite habilitar o suspender temporalmente el agendamiento público por la web.
* **`'recordatorios_auto'`**: Activa o desactiva el envío automático de alertas recordatorias antes de las citas.
* **`'antelacion_reservar'`**: Tiempo mínimo requerido antes de agendar una cita (ej: `'2h'`).
* **`'antelacion_cancelar'`**: Tiempo límite de anticipación para que una clienta cancele su cita sin penalización (ej: `'12h'`).
* **`'tiempo_entre_citas'`**: Margen de tiempo para desinfección, limpieza de camilla y descanso de la lashista entre un servicio y otro (ej: `'15m'`).
* **`'duracion_predeterminada'`**: Duración base estimada si un servicio nuevo no define una duración específica (ej: `'90m'`).
* **`'politica_cancelacion'`**: Términos y condiciones legales que acepta la clienta antes de confirmar su reserva.
* **`'iva_porcentaje'`**: Valor numérico del impuesto al valor agregado aplicable a los cobros (ej: `19.00`).
* **`'ultima_copia_seguridad'`**: Fecha y hora en que se ejecutó el último respaldo de seguridad de la base de datos.

---

## 7. Conversión automática de tipos de datos (`$casts`)

```php
    protected $casts = [
        'reservas_online' => 'boolean',
        'recordatorios_auto' => 'boolean',
        'iva_porcentaje' => 'decimal:2',
        'ultima_copia_seguridad' => 'datetime',
    ];
```

### ¿Por qué escribí esto?
La propiedad `$casts` asegura que los datos se lean en PHP en el formato adecuado:
* **`'reservas_online' => 'boolean'` y `'recordatorios_auto' => 'boolean'`**:  
  Garantiza que estos interruptores se manipulen como valores booleanos reales (`true` o `false`), facilitando condiciones directas como `if ($config->reservas_online)`.
* **`'iva_porcentaje' => 'decimal:2'`**:  
  Fuerza el valor numérico a un decimal con 2 posiciones, evitando errores de cálculo en la liquidación de facturas.
* **`'ultima_copia_seguridad' => 'datetime'`**:  
  Convierte la fecha del backup en una instancia de `Carbon` para formatear frases como *"hace 1 día"*.

---

## 8. El método estático `getInstancia()` (Patrón Singleton con fallback)

```php
    /**
     * Get or create the singleton configuration instance.
     */
    public static function getInstancia(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'nombre_estudio' => 'Oh La Lash by Lorena Ayala Studio',
                'telefono' => '+57 300 123 4567',
                'email_contacto' => 'info@ohlalash.com',
                'direccion' => 'Calle 123 #45-67, Local 2, Medellín, Colombia',
                'zona_horaria' => 'America/Bogota',
                'idioma' => 'es',
                'moneda' => 'COP',
                'formato_fecha' => 'DD/MM/YYYY',
                'formato_hora' => '12',
                'reservas_online' => true,
                'recordatorios_auto' => true,
                'antelacion_reservar' => '2h',
                'antelacion_cancelar' => '12h',
                'tiempo_entre_citas' => '15m',
                'duracion_predeterminada' => '90m',
                'politica_cancelacion' => 'Las cancelaciones deben realizarse con mínimo 12 horas de anticipación para evitar cargos adicionales.',
                'iva_porcentaje' => 19.00,
                'ultima_copia_seguridad' => now()->subDay(),
            ]
        );
    }
```

### ¿Por qué construí este método y para qué sirve?
Es la función más inteligente de este modelo:
1. **Garantiza la existencia de la configuración:** Utilizo el método `firstOrCreate(['id' => 1], [...])`.
2. **Si el registro con `id = 1` ya existe:** Simplemente lo recupera de la base de datos y lo devuelve.
3. **Si el registro aún no existe (por ejemplo, en una instalación limpia o pruebas):** En lugar de que el sistema falle arrojando un error de `null` en las vistas, **crea el registro automáticamente en la base de datos** con los valores por defecto del salón.
4. **Fácil de usar en toda la aplicación:** Cualquier controlador o vista puede escribir `Configuracion::getInstancia()` con la total tranquilidad de que siempre recibirá un objeto válido y completo.

---

## 9. Conceptos técnicos clave que usé en este modelo

1. **Patrón Singleton:** Enfoque de diseño donde se asegura una única instancia central de configuración para toda la aplicación.
2. **`firstOrCreate()`:** Método de Eloquent que busca un registro según un criterio (`id = 1`) y, si no lo encuentra, lo inserta en la base de datos con los atributos secundarios proporcionados.
3. **`$table`:** Declaración explícita para sobreescribir la convención en inglés y enlazar con `configuraciones`.
4. **`$casts`:** Transformación automática de tipos (`boolean`, `decimal:2` y `datetime`).
5. **`$fillable`:** Protección de seguridad contra asignación masiva de campos no autorizados.
