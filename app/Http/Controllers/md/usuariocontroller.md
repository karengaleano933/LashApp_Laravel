# 📖 Guía Fácil de entender: ¿Qué hace `UsuarioController.php`?

> **Imagina que este archivo es:**  
> La **recepcionista principal** o el **encargado de personal** de nuestro estudio de pestañas *LashApp*.  
> Cada vez que alguien quiere ver la lista de usuarios, registrar a una nueva empleada, crear un cliente, cambiar datos o eliminar a alguien, esta "recepcionista" es quien se encarga de hacer el papeleo, verificar la seguridad y guardar todo en orden en el archivador (la base de datos).

---

## 🛠️ 1. Las herramientas que usa al inicio (Líneas 1 a 9)

Al principio del archivo verás varias líneas que empiezan por `use`:

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
```

¿Para qué sirve cada una?
* **`Request` (El Formulario o Mensajero):** Es la hoja de papel donde viaja la información que el usuario escribe en la pantalla (su nombre, correo, teléfono, etc.) hacia el sistema.
* **`Hash` (La Máquina Secreta de Contraseñas):** Es un candado de seguridad. Nunca debemos guardar contraseñas en texto claro (como `123456`). Esta herramienta convierte cualquier contraseña en un código secreto indescifrable (ej. `$2y$10$e7...`) para que nadie, ni siquiera los administradores, puedan ver la clave original.
* **`Role` (La Lista de Cargos):** Le dice al sistema qué tipo de persona es:
  - **1:** Administrador (dueño/a o encargado general).
  - **2:** Trabajador / Especialista (quien atiende las citas y hace pestañas/cejas).
  - **3:** Cliente (quien agenda y paga por el servicio).
* **`User` (El Gran Archivador de Personas):** Representa la tabla principal donde están guardadas todas las personas registradas en la aplicación.

---

## 📋 2. Función `index()` — Ver la lista de usuarios y los números del negocio

```php
public function index()
```

### ¿Qué hace en la vida real?
Cuando haces clic en el menú en **"Usuarios"**, esta función se despierta para organizar lo que vas a ver en pantalla:

1. **Trae a todos los usuarios:** Va al archivador (`User::with('role')->get()`) y saca la lista completa de personas, junto con su rol (si es admin, trabajadora o clienta).
2. **Cuenta los números para las tarjetas de arriba:**
   - ¿Cuántos usuarios hay en total? (`usuariosTotales`)
   - ¿Cuántos están activos trabajando o comprando? (`usuariosActivos`)
   - ¿Cuántos están suspendidos o inactivos? (`usuariosInactivos`)
   - ¿Cuántos son administradores? (`administradores`)
   - ¿Cuántas son especialistas/trabajadoras? (`trabajadores`)
   - ¿Cuántos son clientes? (`clientes`)
3. **Pasa la información a la pantalla:** Le entrega toda esta lista y los conteos a la vista `admin.gusuarios.gusuarios` para que los números y tablas se muestren con colores y tarjetas bonitas.

---

## 📝 3. Función `create()` — Mostrar el formulario de registro

```php
public function create()
```

### ¿Qué hace en la vida real?
Cuando presionas el botón **"+ Nuevo Usuario"**:
1. Busca la lista de roles disponibles (`Role::all()`).
2. Muestra la pantalla con el formulario limpio y un menú desplegable para que elijas qué cargo tendrá (Administrador, Trabajador o Cliente).

---

## 💾 4. Función `store()` — Guardar un nuevo usuario en la base de datos

```php
public function store(Request $request)
```

### ¿Qué hace paso a paso?
Esta es una de las funciones más importantes. Cuando terminas de llenar el formulario y tocas **"Guardar Usuario"**, hace lo siguiente:

1. **Crea una nueva ficha de persona:** Toma los datos que escribiste:
   - Rol que le asignaste.
   - Nombre y Apellido.
   - Documento de identidad (cédula).
   - Teléfono o WhatsApp.
   - Correo electrónico.
2. **Protege la contraseña:** Pasa la clave por la máquina secreta `Hash::make()` para que se guarde de forma segura.
3. **Estado inicial:** Lo activa de inmediato (`estado = 'activo'`).
4. **Firma quién lo creó:** Anota en el sistema el ID del administrador que estaba conectado en ese momento (`created_by = auth()->id()`).
5. **Guarda en la base de datos:** Con `$usuario->save()`.

### 🌟 ¡La magia automática de roles!
El sistema no se queda solo ahí. Dependiendo de quién sea, le crea su perfil correspondiente automáticamente:
- **Si es Cliente (Rol 3):** Le crea de una vez su registro en la tabla de `clientes`.
- **Si es Trabajador o Administrador (Rol 1 o 2):** Le crea de una vez su ficha de especialista en la tabla `trabajadores` con su especialidad (*"Lashista Profesional"* o *"Administrador / Máster"*), para que de inmediato pueda tener horarios y recibir citas.
6. **Te avisa que todo salió bien:** Te regresa a la lista con un aviso verde que dice: *"¡Usuario registrado correctamente!"*.

---

## 🔍 5. Funciones `show()` y `edit()`

```php
public function show(string $id) { }
public function edit(string $id) { }
```

### ¿Por qué están vacías?
En nuestra aplicación moderna utilizamos **ventanas modales emergentes** (pop-ups) dentro de la misma pantalla para editar o ver detalles rápidos, por lo que no hace falta cargar una página entera diferente solo para eso.

---

## ✏️ 6. Función `update()` — Modificar los datos de un usuario existente

```php
public function update(Request $request, string $id)
```

### ¿Qué hace en la vida real?
Cuando le das clic al lápiz ✏️ de **"Editar"** en cualquier usuario y guardas los cambios:

1. **Busca a la persona:** La localiza en la base de datos por su número único (`id`).
2. **Actualiza lo que cambiaste:** Nombre, apellido, cédula, teléfono, correo y estado (Activo o Inactivo).
3. **Manejo inteligente de la contraseña:**
   - Si dejaste la casilla de contraseña vacía, **no la cambia**, para no borrarle la que ya tenía.
   - Si escribiste una contraseña nueva, la encripta y la actualiza.
4. **Sincronización de rol si cambió:**
   - Si antes era trabajadora y ahora pasa a ser Clienta, limpia su ficha de trabajadora y le abre ficha de cliente.
   - Si pasa a ser Especialista, le asegura su ficha en el equipo de trabajo.
5. **Mensaje de éxito:** Guarda y avisa: *"Usuario actualizado exitosamente"*.

---

## 🗑️ 7. Función `destroy()` — Eliminar a un usuario

```php
public function destroy(string $id)
```

### ¿Qué hace y qué cuidados toma?
Cuando tocas el botón de la papelera 🗑️:

1. **Busca al usuario:** Lo encuentra en el archivador.
2. **🛡️ Escudo de seguridad (¡Muy importante!):**
   ```php
   if (auth()->id() == $usuario->id) {
       return back()->with('error', 'No puedes eliminar tu propia cuenta de administrador.');
   }
   ```
   **Evita que te borres a ti misma por error.** Si estás conectada como administradora, el sistema te protege para que no te quites el acceso.
3. **Limpieza en orden:** Antes de borrar al usuario principal, borra sus registros hijos (su ficha de cliente o trabajadora) para que no queden datos huérfanos o errores en la base de datos.
4. **Borra al usuario:** Lo retira definitivamente del archivador.
5. **Mensaje final:** Muestra un aviso de confirmación: *"Usuario eliminado exitosamente"*.

---

## 📌 En resumen:
| Función en el código | ¿Qué significa en lenguaje normal? |
| :--- | :--- |
| **`index()`** | "Muéstrame la lista de todas las personas y cuántos hay de cada tipo". |
| **`create()`** | "Ábreme el formulario en blanco para registrar a alguien nuevo". |
| **`store()`** | "Guarda a esta nueva persona, asegúrale la clave y asígnale su rol". |
| **`update()`** | "Cambia los datos que acabo de corregir de esta persona". |
| **`destroy()`** | "Borra a esta persona del sistema de forma limpia y segura". |
