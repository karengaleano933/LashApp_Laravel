Este modelo representa a los usuarios del sistema y define qué información se puede guardar, qué datos deben mantenerse ocultos y cómo se relaciona un usuario con otros registros de la aplicación.

El modelo está desarrollado en PHP y utiliza Eloquent, el sistema de Laravel que me permite trabajar con los registros de la base de datos mediante clases y métodos.
2. Espacio de nombres e importaciones
namespace App\Models;


Con esta línea indico que la clase User pertenece al espacio de nombres App\Models, donde se encuentran los modelos de mi aplicación.
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


Estas líneas importan las clases y herramientas que utiliza el modelo:
- UserFactory: hace referencia a la fábrica que puede generar usuarios de prueba.
- Fillable: permite indicar qué atributos pueden asignarse de manera masiva.
- Hidden: permite especificar qué atributos deben ocultarse al convertir el modelo en arreglos o JSON.
- HasFactory: permite utilizar fábricas para crear instancias del modelo.
- Authenticatable: es la clase base de Laravel que permite utilizar el modelo para la autenticación de usuarios.
- Notifiable: permite que los usuarios reciban notificaciones del sistema.
La línea MustVerifyEmail aparece comentada, por lo que no está siendo utilizada en este código.
3. Atributos que se pueden asignar masivamente
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


Con el atributo Fillable indico qué campos del usuario pueden asignarse de forma masiva, por ejemplo, al utilizar métodos de Eloquent como create() o fill().

Los campos que permito asignar son:
- role_id: identificador del rol del usuario.
- name: nombre.
- apellido: apellido.
- documento: documento de identificación.
- telefono: número de teléfono.
- email: correo electrónico.
- foto: fotografía del usuario.
- password: contraseña.
- estado: estado del usuario.
- created_by: identificador del usuario que creó el registro.
Esta configuración ayuda a controlar qué atributos se pueden completar mediante asignación masiva. Sin embargo, no reemplaza la validación de los datos recibidos desde formularios.
4. Datos que se mantienen ocultos
#[Hidden(['password', 'remember_token'])]


Con Hidden indico que los campos password y remember_token no deben aparecer cuando el modelo se convierte en un arreglo o en una respuesta JSON.

Esto ayuda a evitar que se expongan datos relacionados con la seguridad del usuario cuando se devuelve su información desde el sistema.
5. Declaración de la clase y traits
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;
}


Declaro la clase User y hago que extienda Authenticatable. Así, Laravel puede utilizarla para los procesos de autenticación, como el inicio de sesión.

También incluyo los traits HasFactory y Notifiable.
- HasFactory permite crear usuarios mediante fábricas, algo útil para pruebas y datos de desarrollo.
- Notifiable incorpora funcionalidades para enviar notificaciones al usuario.
El comentario @use HasFactory<UserFactory> indica el tipo de fábrica asociado para ayudar a las herramientas de análisis del código.
6. Conversión de atributos con casts()
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}


Este método indica cómo debe tratar Laravel ciertos atributos del modelo.
- email_verified_at => datetime: permite tratar la fecha de verificación del correo como un valor de fecha y hora.
- password => hashed: indica que Laravel debe aplicar un hash a la contraseña cuando se asigna al modelo, en lugar de guardarla como texto plano.
Con esta configuración, Laravel puede manejar esos datos de una manera adecuada para su uso en la aplicación.
7. Relación con el modelo Role
public function role()
{
    return $this->belongsTo(Role::class);
}


Con este método defino que un usuario pertenece a un rol.

Utilizo belongsTo porque el usuario está asociado a un registro del modelo Role, normalmente mediante la clave foránea role_id.

Esta relación me permite consultar el rol de un usuario para identificar qué tipo de permisos o funciones puede tener dentro del sistema, según los roles que haya definido en el proyecto.
8. Relación con el usuario creador
// Usuario creador
public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}


Este método representa la relación del usuario actual con el usuario que lo creó.

Como ambos registros pertenecen al mismo modelo User, es una relación de un modelo consigo mismo.

Indico explícitamente created_by como clave foránea. Así puedo consultar qué usuario creó el registro actual, siempre que ese campo tenga guardado el identificador correspondiente.
9. Relación con los usuarios creados
// Usuarios creados por este usuario
public function createdUsers()
{
    return $this->hasMany(User::class, 'created_by');
}


Esta relación representa el caso contrario a creator(): permite consultar los usuarios que fueron creados por el usuario actual.

Utilizo hasMany porque un usuario puede haber creado varios usuarios. La clave foránea created_by de esos registros apunta al identificador del usuario creador.

Por ejemplo, si un administrador registra varias cuentas, esta relación permite consultar las cuentas que creó.
10. Relación con el modelo Cliente
public function cliente()
{
    return $this->hasOne(Cliente::class, 'user_id');
}


Con este método indico que un usuario puede tener un registro asociado en el modelo Cliente.

Utilizo hasOne porque la relación está definida como uno a uno: el registro de cliente guarda la referencia al usuario mediante el campo user_id.

Esta relación permite acceder a la información del cliente desde el usuario, siempre que exista el registro relacionado.
11. Relación con el modelo Trabajador
public function trabajador()
{
    return $this->hasOne(Trabajador::class, 'user_id');
}


Esta relación funciona de manera similar a la de cliente.

Indico que el usuario puede tener un registro asociado en el modelo Trabajador, cuya clave foránea es user_id.

Así puedo consultar la información del trabajador desde el usuario, si ese registro existe en la base de datos.
12. Resumen de las relaciones
Método	Tipo de relación	¿Qué permite consultar?
role()	belongsTo	El rol al que pertenece el usuario.
creator()	belongsTo	El usuario que creó el registro actual.
createdUsers()	hasMany	Los usuarios creados por el usuario actual.
cliente()	hasOne	El registro de cliente asociado al usuario.
trabajador()	hasOne	El registro de trabajador asociado al usuario.

13. Conceptos importantes del código
- Modelo Eloquent: es la clase que permite representar y consultar registros de una tabla de la base de datos.
- Authenticatable: permite utilizar el modelo en la autenticación de Laravel.
- Fillable: define los atributos permitidos para la asignación masiva.
- Hidden: oculta determinados atributos al convertir el modelo en arreglo o JSON.
- HasFactory: permite utilizar fábricas para generar instancias del modelo.
- Notifiable: habilita funcionalidades de notificación.
- casts(): define cómo Laravel debe convertir o tratar ciertos atributos.
- belongsTo: define una relación en la que el modelo actual pertenece a otro registro.
- hasMany: define una relación en la que un registro puede tener varios registros relacionados.
- hasOne: define una relación en la que un registro puede tener un registro relacionado.