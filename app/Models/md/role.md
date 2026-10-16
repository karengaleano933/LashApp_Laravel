# Explicación de mi modelo `Role.php` (En mis palabras)

En este archivo controlo los **tipos de personas o roles** que existen en mi aplicación **LashApp**. Es el modelo que define los niveles de permiso para que el sistema sepa qué puede hacer y qué tiene prohibido cada usuario.

Aquí te explico cómo funciona de manera súper sencilla:

---

## 1. ¿Qué representa este modelo en el salón?

En mi negocio no todo el mundo tiene los mismos permisos:
* **Rol 1 (Administrador):** La dueña del estudio. Puede ver la caja registradora, editar precios de servicios, borrar usuarios, configurar el salón y ver reportes de dinero.
* **Rol 2 (Trabajador / Especialista):** Las lashistas. Pueden ver su propia agenda de citas, marcar citas como realizadas, ver sus horarios y leer las opiniones que les dejan sus clientas.
* **Rol 3 (Cliente):** Las personas que visitan el salón. Pueden agendar citas, ver el catálogo de servicios, ver sus citas pasadas y dejar calificaciones de estrellas.

Este modelo `Role.php` es el que guarda esos 3 tipos en la base de datos.

---

## 2. Los datos que guarda (`$fillable`)

```php
protected $fillable = [
    'nombre',
    'descripcion',
];
```

* **`nombre`:** El nombre del rol (por ejemplo: `'Administrador'`, `'Trabajador'`, `'Cliente'`).
* **`descripcion`:** Una breve explicación de lo que hace ese rol en el sistema (por ejemplo: *"Acceso total al sistema y configuración general"*).

---

## 3. Su relación con los usuarios (`users`)

```php
public function users()
{
    return $this->hasMany(User::class);
}
```

* **Qué significa:** *"Un rol tiene muchos usuarios"* (`hasMany`).
* **Para qué me sirve:** Si quiero saber cuántos administradores, cuántas trabajadoras o cuántas clientas tengo en el sistema, puedo escribir `$rol->users` y Laravel me devuelve la lista completa de todas las personas que pertenecen a ese rol.

---

## En resumen: ¿Para qué sirve `Role.php`?
* Es el catálogo de cargos o niveles de acceso en LashApp.
* Agrupa a los usuarios según su función en el negocio.
* Permite que los controladores y pantallas decidan qué botones mostrar y qué cosas ocultar por seguridad.
