# Explicación de mi `ConfiguracionController.php` (En mis palabras)

En este archivo controlo todos los **ajustes generales y la identidad de mi negocio** en **LashApp**. Es la pantalla donde personalizo los datos del salón: el nombre de la empresa, el número de WhatsApp, la dirección del local, el logo oficial, los horarios de atención y las reglas de agendamiento (como con cuántas horas de anticipación puede cancelar o apartar una clienta).

Aquí te explico cómo funciona cada línea:

---

## 1. Lo que importo al principio (Mis herramientas)

```php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Configuracion;
use Illuminate\Support\Facades\File;
```

* **`Request`:** Para recibir todo lo que escribo o cambio en el panel de configuración.
* **`Auth`:** Para validar que la persona que edita la configuración tenga permisos de administración.
* **`Configuracion`:** Mi modelo especial que guarda una única fila con los datos principales del negocio (nombre, dirección, logo, etc.).
* **`File`:** Para crear la carpeta donde se guarda el logo del estudio y borrar el logo viejo si decido cambiarlo.

---

## 2. La función `index()` — Ver los ajustes actuales

```php
public function index()
```

Esta función simplemente abre la pantalla de ajustes (`admin.configuracion.configuracion`):

1. **Obtengo el usuario:** Traigo a la persona conectada (`Auth::user()`).
2. **Cargo la configuración única:** Uso `Configuracion::getInstancia()`. Este método es muy especial porque si es la primera vez que se entra al sistema, crea la fila con datos por defecto automáticamente para que los campos del formulario nunca aparezcan vacíos o rotos.
3. **Muestro la vista:** Pasa las variables a la pantalla para rellenar los campos de texto con la información actual del salón.

---

## 3. La función `update()` — Guardar los cambios del negocio

```php
public function update(Request $request)
```

Cuando cambio algún dato del salón y le doy clic a **"Guardar Configuración"**:

1. **Valido que todo esté en orden:**
   * El nombre del estudio, teléfono, correo de contacto y dirección son obligatorios.
   * El correo debe tener formato de email real.
   * El logo (si elijo uno) debe ser una imagen real (`jpeg, png, jpg, webp`) y no puede pesar más de 2MB.
2. **Actualizo la información básica:**
   * Nombre de la empresa, teléfono de atención y correo.
   * Dirección física del local.
   * Moneda (`COP $`, `USD $`), zona horaria e idioma.
   * Formato preferido de fechas y horas (`12h` o `24h`).
3. **Reglas de las citas y reservas:**
   * **`reservas_online`:** Activa o desactiva si las clientas pueden agendar solas por la web (`$request->has('reservas_online')`).
   * **`recordatorios_auto`:** Si el sistema envía recordatorios automáticos.
   * **`antelacion_reservar`:** Con cuántas horas mínimas de anticipación se puede agendar.
   * **`antelacion_cancelar`:** Con cuánto tiempo antes puede cancelar una cita sin penalización.
   * **`tiempo_entre_citas`:** Tiempo de descanso/limpieza entre una clienta y otra (por ejemplo: 15 minutos).
   * **`politica_cancelacion`:** El texto con los términos y condiciones del salón.
4. **Subida del nuevo logo del estudio:**
   * Si seleccioné una imagen para el logo, creo la carpeta `public/img/logo`.
   * Si ya había un logo anterior guardado, **lo borro del disco** con `File::delete()` para no dejar fotos viejas olvidadas en el servidor.
   * Le pongo un nombre único (`logo_studio_1712345678.png`), lo muevo a la carpeta y guardo la ruta en la base de datos.
5. **Guardo todo en la base de datos:** `$config->save()`.
6. **Aviso:** Redirijo al formulario con el mensaje verde: *"Configuración del estudio guardada y sincronizada en la base de datos"*.

---

## En resumen: ¿Qué hace este controlador?
* Centraliza la identidad visual y datos de contacto de LashApp en un solo lugar.
* Controla las políticas de cancelación y tiempos entre servicios.
* Maneja la subida y sustitución limpia del logo de la empresa.
* Garantiza que el negocio funcione con sus parámetros personalizados en toda la aplicación.
