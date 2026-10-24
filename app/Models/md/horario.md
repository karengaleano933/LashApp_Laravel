# Explicación de mi modelo `Horario.php` (Código por código en mis palabras)

En este archivo controlo y administro los **turnos de trabajo, jornadas laborales y la disponibilidad horaria** de las especialistas de mi salón en **LashApp**.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la planificación diaria del negocio.

---

## 1. ¿Qué representa este modelo en mi aplicación?

Es el **cuadro de turnos y disponibilidad del equipo de trabajo**:
- Define con exactitud qué especialista (`trabajador_id`) labora en qué día (`fecha`), a qué hora inicia su atención (`hora_inicio`) y a qué hora finaliza su turno (`hora_fin`).
- Controla el estado del turno (`estado`: disponible, permiso, vacaciones o inactivo).
- Es el pilar del motor de reservas: cuando una clienta entra a agendar cita para pestañas o cejas, el sistema verifica primero este modelo para no permitir reservas en días u horas en los que la lashista no esté trabajando o fuera de su horario.
- Conecta cada jornada con la especialista (`Trabajador`) y con todas las citas que atenderá en ese bloque (`Cita`).

---

## 2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

### Explicación de cada línea:
* **`<?php`**:  
  Es la etiqueta de apertura obligatoria de PHP. Le comunica al servidor que todo el contenido a continuación debe ejecutarse como código PHP.
* **`namespace App\Models;`**:  
  Declara el **espacio de nombres** del archivo. Ubica a la clase `Horario` dentro de la carpeta lógica `App\Models`. Gracias a esto, puedo llamarla ordenadamente desde cualquier controlador (como `HorarioController` o `CitaController`) con la instrucción `use App\Models\Horario;`.

---

## 3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

### Explicación de cada línea:
* **`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
  Importo el trait `HasFactory`. Me permite enlazar el modelo con una fábrica (`HorarioFactory`) para generar turnos de trabajo ficticios automáticamente al ejecutar seeders y pruebas.
* **`use Illuminate\Database\Eloquent\Model;`**:  
  Importo la clase madre `Model` de Eloquent ORM. Al extender de ella, el modelo hereda todos los métodos para consultar rangos de fechas (`whereBetween()`), verificar disponibilidad (`where()`), guardar registros y gestionar relaciones de base de datos.

---

## 4. Declaración de la clase y uso del Trait

```php
class Horario extends Model
{
    use HasFactory;
```

### Explicación de cada línea:
* **`class Horario extends Model`**:  
  Declaro la clase `Horario` y la hago **heredar** (`extends`) de `Model`. De esta manera, `Horario` se transforma en un modelo de base de datos de Eloquent, listo para gestionar los turnos laborales.
* **`use HasFactory;`**:  
  Habilito el trait dentro de la clase para permitir la creación de horarios mediante `Horario::factory()`.

---

## 5. Definición explícita de la tabla (`$table`)

```php
    protected $table = 'horarios';
```

### ¿Por qué escribí esto?
Por convención, Laravel busca tablas en inglés pluralizando el nombre del modelo. Con la propiedad protegida `$table = 'horarios'`, le indico a Laravel de forma explícita que trabaje directamente sobre la tabla `horarios` en la base de datos, evitando que intente buscar nombres erróneos en inglés.

---

## 6. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'trabajador_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'estado',
    ];
```

### ¿Qué significa y por qué lo uso?
La propiedad `$fillable` protege contra ataques de asignación masiva (Mass Assignment). Solo los campos declarados en este arreglo pueden ser guardados o modificados de golpe mediante métodos como `Horario::create($datos)` o `$horario->update($datos)`.

### Explicación de cada campo en mis palabras:
* **`'trabajador_id'`**:  
  Llave foránea que apunta a la tabla `trabajadores`. Identifica a qué especialista o lashista le corresponde este turno.
* **`'fecha'`**:  
  El día específico del calendario en el que la trabajadora estará disponible (por ejemplo: `2026-10-25`).
* **`'hora_inicio'`**:  
  La hora exacta en que comienza la jornada de atención de la lashista (por ejemplo: `09:00:00`).
* **`'hora_fin'`**:  
  La hora exacta en que termina su turno laboral (por ejemplo: `18:00:00`).
* **`'estado'`**:  
  Indica si el horario está habilitado para recibir citas (`'activo'`) o si fue bloqueado por permiso médico, descanso o feriado (`'inactivo'`).

---

## 7. Conversión automática de tipos de datos (`$casts`)

```php
    protected $casts = [
        'fecha' => 'date',
    ];
```

### ¿Por qué escribí esto?
* **`'fecha' => 'date'`**:  
  Convierte el valor almacenado en la columna `fecha` directamente en un objeto `Carbon`. Así puedo trabajar con fechas en PHP sin conversiones manuales:
  ```php
  // Imprimir fecha formateada en español
  $fechaFormateada = $horario->fecha->translatedFormat('l, d \d\e F'); // "Lunes, 25 de Octubre"
  
  // Saber si el horario corresponde al día de hoy
  if ($horario->fecha->isToday()) {
      // Turno de hoy
  }
  ```

---

## 8. Relación: Especialista asignada (`trabajador`)

```php
    public function trabajador()
    {
        return $this->belongsTo(Trabajador::class, 'trabajador_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `belongsTo` (Pertenece a).
* **¿Qué significa en mis palabras?**  
  Cada turno u horario le pertenece a una especialista específica.
* **Llave foránea:** Indico `'trabajador_id'`, que es el campo en mi tabla `horarios` que almacena el ID de la trabajadora.
* **¿Para qué me sirve en el código?**  
  Al pintar la agenda en la pantalla o validar disponibilidad, puedo obtener al instante los datos de la profesional:
  ```php
  // Saber el nombre de la lashista de este turno
  $nombreLashista = $horario->trabajador->user->name;
  ```

---

## 9. Relación: Citas atendidas en este turno (`citas`)

```php
    public function citas()
    {
        return $this->hasMany(Cita::class, 'horario_id');
    }
```

### Explicación detallada:
* **Tipo de relación:** `hasMany` (Tiene muchas).
* **¿Qué significa en mis palabras?**  
  En una misma jornada u horario laboral, la especialista puede atender muchas citas consecutivas a lo largo del día.
* **Llave foránea:** Indico `'horario_id'`, que es la columna en la tabla `citas` que apunta a este horario.
* **¿Para qué me sirve en el código?**  
  Me permite saber cuántas citas ya tiene agendadas en ese turno y calcular qué huecos libres quedan disponibles:
  ```php
  // Contar cuántas citas tiene programadas la especialista en este turno
  $citasProgramadas = $horario->citas()->where('estado', '!=', 'cancelado')->count();
  ```

---

## 10. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`trabajador()`** | `belongsTo` | `Trabajador` | `trabajador_id` | La especialista dueña del turno (nombre, especialidad, foto). |
| **`citas()`** | `hasMany` | `Cita` | `horario_id` | Todas las citas de clientas que fueron agendadas dentro de esta jornada laboral. |

---

## 11. Conceptos técnicos clave que usé en este modelo

1. **Eloquent ORM:** Mapea la tabla `horarios` como un objeto interactivo para gestionar la disponibilidad del salón.
2. **`$table`:** Declaración explícita para sobreescribir la pluralización en inglés y conectar exactamente con `horarios`.
3. **`$casts`:** Conversión automática de `'fecha'` a objeto `Carbon` (`date`) para facilitar comparaciones y formateos de calendario.
4. **`$fillable`:** Lista de seguridad que autoriza los atributos que pueden ser asignados de forma masiva.
5. **`belongsTo`:** Relación inversa de pertenencia que conecta el turno con el modelo `Trabajador`.
6. **`hasMany`:** Relación de uno a muchos que agrupa todas las citas registradas en esa jornada.
