 Explicación de mi modelo `Role.php` (Código por código en mis palabras)

En este archivo controlo y administro los *roles, niveles de acceso y tipos de personas* que interactúan en mi aplicación *LashApp*.

Aquí explico, línea por línea y bloque por bloque, cómo construí este código, qué significa cada parte como si lo estuviera contando yo misma y para qué sirve en la operativa real dentro del salón.

---

1. ¿Qué representa este modelo en mi aplicación?

En mi negocio no todas las personas tienen las mismas funciones ni los mismos permisos:
- *Administrador:* La dueña o encargada del salón. Tiene control total: puede modificar precios, crear nuevos servicios, asignar citas, ver balances y gestionar la configuración.
- *Trabajador (Especialista / Lashista):* Las técnicas de pestañas y cejas. Tienen acceso a su propia agenda, pueden consultar sus turnos laborales, marcar citas como realizadas y ver las reseñas de sus clientas.
- *Cliente:* Las usuarias que reservan citas. Tienen acceso al catálogo público, pueden programar citas, ver su historial de servicios y calificar la atención.

Este modelo `Role.php` representa la tabla donde defino estos roles para que el sistema y los middlewares puedan restringir o conceder accesos según corresponda.

---

2. Apertura del archivo y espacio de nombres (Namespace)

```php
<?php

namespace App\Models;
```

 Explicación de cada línea:
`<?php`:  
  Es la etiqueta estándar obligatoria de PHP. Le comunica al servidor web y al motor PHP que el código a continuación debe ser interpretado como instrucciones de PHP.
*`namespace App\Models;`**:  
 Define el **espacio de nombres** de este modelo. Organiza la clase `Role` dentro del paquete `App\Models`. Gracias a esto, puedo invocarla en cualquier controlador, middleware o seeder con `use App\Models\Role;`, manteniendo una arquitectura ordenada y libre de conflictos de nombres.

---

3. Importación de dependencias (`use`)

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
```

Explicación de cada línea:
*`use Illuminate\Database\Eloquent\Factories\HasFactory;`**:  
 Importo el trait `HasFactory`. Me permite vincular el modelo con una fábrica de datos (`RoleFactory`), lo cual facilita la generación automática de roles de prueba en los seeders y pruebas automatizadas del proyecto.
*`use Illuminate\Database\Eloquent\Model;`**:  
 Importo la clase base `Model` de Eloquent ORM. Al heredar de ella, mi clase adquiere inmediatamente todos los métodos nativos de Laravel para consultar registros (`all()`, `find()`, `where()`), persistir cambios y gestionar relaciones.

---

 4. Declaración de la clase y uso del Trait

```php
class Role extends Model
{
    use HasFactory;
```

 Explicación de cada línea:
*`class Role extends Model`**:  
 Declaro la clase `Role` y le indico a PHP que **hereda** (`extends`) de la clase `Model`. Con esta herencia, `Role` se convierte formalmente en un modelo de base de datos de Eloquent. Por convención, Laravel asocia automáticamente este modelo con la tabla `roles` (plural en inglés de `role`).
*`use HasFactory;`**:  
 Incorporo el trait dentro de la definición de la clase, habilitando el uso del método `Role::factory()`.

---

 5. Atributos asignables de forma masiva (`$fillable`)

```php
    protected $fillable = [
        'nombre',
        'descripcion',
    ];
```

 ¿Qué significa y por qué lo uso?
La propiedad protegida `$fillable` es una lista blanca de seguridad conocida como protección contra asignación masiva (Mass Assignment). Solo los campos que coloque dentro de este arreglo pueden ser llenados de forma masiva utilizando métodos como `Role::create(...)` o `$role->update(...)`, evitando modificaciones indebidas.

 Explicación de cada campo en mis palabras:
*`'nombre'`**:  
 El nombre distintivo del rol dentro del sistema (por ejemplo: `'Administrador'`, `'Trabajador'` o `'Cliente'`). Este valor es clave para validar permisos y controlar las rutas a las que cada usuario puede entrar.
*`'descripcion'`**:  
 Un texto descriptivo donde se detalla el alcance o las funciones que desempeña este rol en LashApp (por ejemplo: *"Acceso administrativo completo al salón"* o *"Acceso exclusivo para gestión de agenda de clientas"*).

---
 6. Relación: Usuarios que tienen este rol (`users`)

```php
    public function users()
    {
        return $this->hasMany(User::class);
    }
```

 Explicación detallada:
*Tipo de relación:** `hasMany` (Tiene muchos).
*¿Qué significa en mis palabras?**  
  Un rol puede pertenecer a muchos usuarios del sistema. Por ejemplo, el rol *"Trabajador"* lo comparten todas las lashistas contratadas, y el rol *"Cliente"* lo tienen todas las personas que reservan citas.
*Llave foránea implícita:** Al usar `hasMany(User::class)`, Laravel asume por convención que en la tabla `users` existe una columna llamada `role_id` que apunta al `id` de este modelo.
*¿Para qué me sirve en el código?**  
  Me permite consultar rápidamente a todos los usuarios agrupados bajo este rol:
  ```php
  // Obtener todas las personas con el rol de Trabajador
  $rolTrabajador = Role::where('nombre', 'Trabajador')->first();
  $misTrabajadoras = $rolTrabajador->users;

  // Contar cuántas clientas registradas tengo
  $rolCliente = Role::where('nombre', 'Cliente')->first();
  $totalClientas = $rolCliente->users()->count();
  ```

---

 7. Resumen de las relaciones en una tabla

| Método | Tipo de Relación Eloquent | Modelo Relacionado | Llave Foránea Asumida | ¿Qué me permite consultar? |
| :--- | :--- | :--- | :--- | :--- |
| **`users()`** | `hasMany` | `User` | `role_id` (en tabla `users`) | Todos los usuarios registrados en el sistema que tienen asignado este rol. |

---

8. Conceptos técnicos clave que usé en este modelo

1. *Eloquent ORM:** El sistema que transforma cada registro de la tabla `roles` en un objeto PHP interactivo.
2. *Convención de nomenclatura de tablas:** Al no definir `$table`, Laravel deduce automáticamente que el modelo `Role` se mapea con la tabla `roles`.
3. *`$fillable`:** Mecanismo de seguridad que restringe qué campos se pueden rellenar de forma masiva desde formularios o llamadas a la API.
4. *`hasMany`:** Relación de uno a muchos donde la tabla de destino (`users`) almacena la clave foránea (`role_id`) que referencia a este registro.
