# Explicación de mi modelo `Configuracion.php` (En mis palabras)

En este archivo controlo los **parámetros globales y la configuración del salón** en **LashApp**. A diferencia de los otros modelos donde hay cientos de filas de citas o usuarios, este modelo está diseñado para guardar **una única fila con las reglas y la identidad de nuestro estudio**: el nombre de la empresa, logo, teléfono, moneda, políticas de cancelación y tiempos entre servicios.

Aquí te explico cómo funciona y su truco especial:

---

## 1. ¿Qué representa en la vida real?

Es como el **manual de operaciones y el membrete oficial del negocio**. En lugar de escribir el nombre del salón o las políticas de cancelación quemadas en el código, las guardamos aquí en la base de datos. Si mañana cambiamos de local o de número de WhatsApp, solo lo editamos en la pantalla de configuración y automáticamente cambia en toda la aplicación.

---

## 2. La tabla y los campos que guardo (`$table` y `$fillable`)

```php
protected $table = 'configuraciones';

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

* **`nombre_estudio`:** El nombre de la marca (ejemplo: *"Oh La Lash by Lorena Ayala Studio"*).
* **`telefono` y `email_contacto`:** Medios oficiales de atención a las clientas.
* **`direccion`:** Dónde queda ubicado el local físico.
* **`logo_path`:** La ruta del archivo del logo del estudio.
* **`zona_horaria`, `idioma`, `moneda`:** Parámetros regionales (ejemplo: `America/Bogota`, `es`, `COP`).
* **`reservas_online`:** Booleano que activa o apaga las reservas por internet.
* **`recordatorios_auto`:** Si se envían avisos automáticos.
* **`antelacion_reservar` / `antelacion_cancelar`:** Con cuántas horas mínimas se permite agendar o cancelar citas.
* **`tiempo_entre_citas`:** Los minutos de descanso y desinfección entre clienta y clienta (ejemplo: `15m`).
* **`politica_cancelacion`:** Los términos y condiciones que acepta la clienta al agendar.
* **`iva_porcentaje`:** Porcentaje de impuestos aplicable a la facturación.

---

## 3. Conversión de datos (`$casts`)

```php
protected $casts = [
    'reservas_online' => 'boolean',
    'recordatorios_auto' => 'boolean',
    'iva_porcentaje' => 'decimal:2',
    'ultima_copia_seguridad' => 'datetime',
];
```

* Los switches de reservas y recordatorios se manejan como booleanos puros (`true` o `false`).
* El IVA siempre tiene formato decimal con 2 dígitos.
* La fecha del último backup se trata como un objeto de fecha y hora.

---

## 4. El método especial `getInstancia()` (Patrón Singleton Inteligente)

```php
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
            'politica_cancelacion' => 'Las cancelaciones deben realizarse con mínimo 12 horas de anticipación...',
            'iva_porcentaje' => 19.00,
            'ultima_copia_seguridad' => now()->subDay(),
        ]
    );
}
```

### ¿Por qué hice esta función y para qué sirve?
Es una de las mejores cosas que tiene el sistema:
1. Siempre busca el registro con **`id = 1`**.
2. **Si no existe en la base de datos (por ejemplo, recién instalado el sistema):** En vez de lanzar un error o mostrar una pantalla en blanco con campos vacíos, **la crea automáticamente con valores de fábrica coherentes**.
3. Así me aseguro de que cualquier parte de la aplicación (los encabezados, el pie de página, los recibos o los controladores) que llame a `Configuracion::getInstancia()` siempre reciba los datos listos sin fallar jamás.

---

## En resumen: ¿Para qué sirve `Configuracion.php`?
* Centraliza los datos del salón (nombre, logo, WhatsApp, dirección).
* Define las reglas del juego para agendamientos y cancelaciones de citas.
* Cuenta con un método seguro (`getInstancia`) que auto-crea la configuración si la tabla está vacía.
