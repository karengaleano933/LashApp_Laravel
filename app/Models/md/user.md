# Explicación de mi modelo `User.php` (En mis palabras)

Este archivo es el **documento de identidad principal** de cualquier persona que entra a **LashApp**. Ya sea la dueña del salón, una especialista en pestañas o una clienta que viene a ponerse bella, todos empiezan existiendo aquí como un usuario con su correo y contraseña.

Aquí te explico cómo lo construí y para qué sirve cada parte:

---

## 1. ¿Qué hace este modelo en la vida real?

Piensa en este archivo como la **llave maestra** del sistema. No importa si eres administradora, lashista o clienta: para poder entrar con tu correo y clave, necesitas una cuenta en la tabla `users`. Luego, según el rol que tengas, el sistema te da permisos y te abre las puertas que te corresponden.

---

## 2. Los datos que permito guardar (`Fillable`)

En la parte superior tengo esta lista de campos permitidos:

```php
#[Fillable([
    'role_id',
    'name',
    'apellido',
    'documento',
    'telefono',
    'email',
    'foto',
    'password',
    'estado',
    'created_by',
])]
```

**¿Para qué sirve cada campo?**
* **`role_id`:** El número que dice quién es esta persona (1 para Administradora, 2 para Trabajadora, 3 para Clienta).
* **`name` y `apellido`:** Su nombre completo.
* **`documento`:** Su cédula o documento de identidad para temas formales o facturación.
* **`telefono`:** Su número de celular o WhatsApp para contactarla o enviarle recordatorios.
* **`email`:** El correo con el que inicia sesión.
* **`foto`:** La ruta de la imagen de perfil que subió para que aparezca su cara redondita en la barra superior.
* **`password`:** Su contraseña secreta.
* **`estado`:** Dice si la cuenta está `'activo'` (puede entrar) o `'inactivo'` (cuenta pausada o bloqueada).
* **`created_by`:** Guarda el ID de la administradora que registró a este usuario.

---

## 3. Seguridad y privacidad (`Hidden` y `Casts`)

```php
#[Hidden(['password', 'remember_token'])]
```

* **`Hidden`:** Hace que cuando el sistema consulte a un usuario, **nunca exponga la contraseña ni el token de sesión**. Esto evita que por error un programador o una pantalla muestre la clave en texto visible.

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

* **`casts`:** Convierte automáticamente los datos:
  - `email_verified_at` lo trata como una fecha y hora real de calendario.
  - `password => 'hashed'` asegura que Laravel siempre trate la contraseña como un código encriptado y seguro.

---

## 4. Las conexiones con otros modelos (Mis Relaciones)

Aquí le enseño a Laravel cómo se conecta este usuario con el resto del salón:

### 1. Su rol (`role`)
```php
public function role()
{
    return $this->belongsTo(Role::class);
}
```
* **Qué significa:** *"Este usuario pertenece a un rol"*. Me permite hacer `$user->role->nombre` para saber de inmediato si es Administradora o Clienta.

### 2. Quién lo creó y a quiénes ha creado (`creator` y `createdUsers`)
```php
public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

public function createdUsers()
{
    return $this->hasMany(User::class, 'created_by');
}
```
* **Qué significa:** Me permite saber qué administradora registró a una trabajadora o ver la lista de todas las cuentas que ha creado una administradora en específico.

### 3. Su ficha de clienta (`cliente`)
```php
public function cliente()
{
    return $this->hasOne(Cliente::class, 'user_id');
}
```
* **Qué significa:** Si la persona es una clienta, este usuario tiene **una única ficha** en la tabla de clientes con su dirección y fecha de cumpleaños.

### 4. Su ficha de trabajadora (`trabajador`)
```php
public function trabajador()
{
    return $this->hasOne(Trabajador::class, 'user_id');
}
```
* **Qué significa:** Si la persona es una lashista, este usuario tiene **una única ficha** en la tabla de trabajadoras con su especialidad y experiencia profesional.

---

## En resumen: ¿Para qué sirve `User.php`?
* Es el centro de inicio de sesión y autenticación de LashApp.
* Conecta a una persona con su rol, su perfil de trabajadora o su perfil de clienta.
* Cuida las contraseñas para que siempre estén encriptadas y protegidas.
