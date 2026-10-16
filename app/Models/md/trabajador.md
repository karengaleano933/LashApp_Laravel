# Explicación de mi modelo `Trabajador.php` (En mis palabras)

En este archivo controlo la **ficha profesional de cada especialista** (las lashistas y diseñadoras de cejas) de **LashApp**. Es el modelo que define a las empleadas del salón, sus habilidades, su biografía y las conecta con sus horarios de trabajo y las citas que deben atender.

Aquí te explico cómo lo tengo estructurado:

---

## 1. ¿Qué representa en la vida real?

Representa la **tarjeta de presentación o perfil profesional** de cada integrante de mi equipo de trabajo. Mientras que su cuenta de `User.php` le sirve para entrar al sistema con su clave, este modelo `Trabajador.php` guarda en qué técnicas es experta (por ejemplo: *"Efecto Foxy y Volumen Ruso"*), un texto sobre su experiencia y si actualmente está activa trabajando en el salón.

---

## 2. La tabla y los campos que guardo (`$table` y `$fillable`)

```php
protected $table = 'trabajadores';

protected $fillable = [
    'user_id',
    'especialidad',
    'descripcion',
    'estado',
];
```

* **`$table = 'trabajadores'`:** Le avisa a Laravel que este modelo consulta la tabla `trabajadores` de la base de datos.
* **`user_id`:** Es el código que la enlaza con su cuenta en la tabla `users` para saber su nombre real, foto y teléfono.
* **`especialidad`:** En qué se destaca (por ejemplo: *"Lash Artist & Brow Designer"* o *"Master en Pestañas Pelo a Pelo"*).
* **`descripcion`:** Un texto breve contando su trayectoria o filosofía de trabajo para inspirar confianza a las clientas.
* **`estado`:** Dice si está `'activo'` (disponible para recibir citas en el salón) o `'inactivo'` (si está de vacaciones o ya no trabaja con nosotros).

---

## 3. Las relaciones con otros modelos

Aquí conecto a la especialista con todo el ecosistema de LashApp:

### 1. Su cuenta de usuario (`user`)
```php
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
```
* **Qué significa:** *"Esta ficha de trabajadora pertenece a un usuario"* (`belongsTo`).
* **Para qué me sirve:** Si tengo una trabajadora en la mano, puedo escribir `$trabajador->user->name` y de inmediato tengo su nombre, o `$trabajador->user->foto` para pintar su foto en la agenda.

### 2. Las citas que atiende (`citas`)
```php
public function citas()
{
    return $this->hasMany(Cita::class, 'trabajador_id');
}
```
* **Qué significa:** *"Una trabajadora atiende muchas citas"* (`hasMany`).
* **Para qué me sirve:** Me permite ver toda la agenda de clientas que tiene asignadas: las que tiene hoy, las que completó ayer o las que tiene la próxima semana.

### 3. Sus horarios y turnos de trabajo (`horarios`)
```php
public function horarios()
{
    return $this->hasMany(Horario::class, 'trabajador_id');
}
```
* **Qué significa:** *"Una trabajadora tiene muchos horarios o turnos configurados"* (`hasMany`).
* **Para qué me sirve:** Me permite saber qué días viene a trabajar, a qué hora entra y a qué hora sale, para que el sistema nunca le programe una cita en su día de descanso o fuera de su turno.

---

## En resumen: ¿Para qué sirve `Trabajador.php`?
* Guarda la especialidad y el perfil laboral de cada lashista del equipo.
* Conecta a la empleada con su cuenta (`User`), sus citas de clientas (`Cita`) y sus turnos laborales (`Horario`).
* Permite que las clientas elijan con quién quieren atenderse según su especialidad.
