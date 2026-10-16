# Explicación de mi `ServicioController.php` (En mis palabras)

En este controlador manejo todo el **catálogo de tratamientos y servicios** de mi estudio de belleza (LashApp): lifting de pestañas, extensiones clásicas, volumen ruso, diseño de cejas con henna, laminado, etc. Aquí controlo los precios, la duración de cada sesión, las fotos de los tratamientos y si están activos o pausados.

Aquí explico cómo lo hice paso a paso:

---

## 1. Lo que importo al principio (Mis herramientas)

```php
use Illuminate\Http\Request;
use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Support\Facades\File;
```

Esto me sirve para:
* **`Request`:** Para recibir la información cuando creo o edito un servicio (nombre, precio, duración en minutos, etc.).
* **`Servicio` & `Categoria`:** Mis modelos para consultar y guardar los servicios y organizarlos por categorías (ej. "Pestañas", "Cejas", "Uñas").
* **`File`:** Para gestionar las fotos de los servicios (crear la carpeta y borrar la foto vieja si la cambio).

---

## 2. La función `index()` — Ver el catálogo y las métricas

```php
public function index()
```

Cuando entro a la sección de **Servicios**:

1. **Traigo todos los servicios con su categoría:** Con `with('categoria')` y `withCount('citas')` para saber de una vez cuántas citas ha tenido cada tratamiento.
2. **Saco las estadísticas:**
   - `$totalServicios`: Cuántos servicios tengo registrados en total.
   - `$serviciosActivos`: Cuántos están disponibles para que las clientas los agenden.
   - `$totalCategorias`: Cuántas categorías tengo activas.
   - `$serviciosInact`: Cuántos servicios están pausados.
   - `$serviciosDestacados`: Los 4 servicios más populares (los que más citas tienen).
3. **Muestro la vista según el rol:**
   - Si es una trabajadora (`role_id === 2`), le muestro su panel especializado `trabajador.servicios.servicios_trabajador`.
   - Si es el administrador, le muestro el panel administrativo `admin.servicios.servicios`.

---

## 3. La función `store()` — Crear un nuevo servicio

```php
public function store(Request $request)
```

Cuando presiono el botón **"+ Nuevo Servicio"** y lleno el formulario:

1. **Valido los datos:** Que tenga nombre, que pertenezca a una categoría existente, que el precio sea mayor o igual a 0, y que la duración sea en minutos.
2. **Convierto la duración:** Como en el formulario pongo los minutos (ej. 90 minutos) pero en la base de datos se guarda como formato de hora `HH:MM:SS`, hago este cálculo:
   ```php
   $minutos = (int) $request->input('duracion_minuto', 30);
   $horas = floor($minutos / 60);
   $restoMin = $minutos % 60;
   $servicio->duracion_minuto = sprintf('%02d:%02d:00', $horas, $restoMin);
   ```
   Así `90` minutos se guarda automáticamente como `01:30:00`.
3. **Guardo la foto del servicio:** Si subí una imagen representativa del tratamiento, la guardo en `public/img/servicios/` con un nombre único para que no se sobreescriba.
4. **Guardo en la base de datos:** Con `$servicio->save()`.
5. **Mensaje de éxito:** Mando el aviso: *"Servicio creado exitosamente"*.

---

## 4. La función `update()` — Editar un servicio existente

```php
public function update(Request $request, $id)
```

Cuando toco el botón de editar en un servicio y cambio el precio, el nombre o el estado:

1. **Busco el servicio:** Por su `$id`.
2. **Actualizo los campos:** Nombre, categoría, precio, descripción y estado (activo o inactivo).
3. **Reconvierto la duración:** Si cambié el tiempo del tratamiento, recalculé las horas y minutos.
4. **Reemplazo de imagen limpio:** Si subí una foto nueva, el sistema **borra automáticamente la foto anterior** del servidor con `File::delete(...)` para no acumular archivos basura que ocupen espacio en el disco.
5. **Guardo los cambios:** Y muestro el mensaje: *"Servicio actualizado correctamente"*.

---

## 5. La función `destroy()` — Eliminar un servicio

```php
public function destroy($id)
```

Cuando elimino un servicio del catálogo:
1. **Busco el servicio:** Con `Servicio::findOrFail($id)`.
2. **Borro su foto del servidor:** Para dejar todo limpio.
3. **Borro el registro de la base de datos:** Con `$servicio->delete()`.
4. **Aviso:** *"Servicio eliminado correctamente"*.

---

## Resumen rápido de `ServicioController`:
- **`index()`:** Muestra la lista de tratamientos con sus precios, fotos y los servicios más populares.
- **`store()`:** Crea un nuevo servicio, calcula la duración en horas/minutos y sube su foto.
- **`update()`:** Cambia precios o detalles y reemplaza la foto vieja.
- **`destroy()`:** Borra el servicio y su imagen del sistema.
