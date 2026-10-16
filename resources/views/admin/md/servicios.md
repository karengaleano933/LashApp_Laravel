# Explicación de mi Vista de Catálogo de Servicios (`servicios/servicios.blade.php`) (En mis palabras)

Esta vista es el **menú comercial de LashApp**. En ella se administran todos los tratamientos que ofrece el salón (como *Lifting de Pestañas*, *Extensiones Pelo a Pelo*, *Volumen Ruso*, *Diseño y Henna en Cejas*), definiendo sus precios, fotos de portada, duraciones en minutos y categorías.

---

## 1. Tarjetas de Estadísticas del Catálogo

En la parte superior agregué cuatro tarjetas informativas:

1. **Total Servicios:** El número total de procedimientos registrados en la base de datos.
2. **Servicios Activos:** Procedimientos habilitados que las clientas pueden ver y agendar actualmente.
3. **Categorías (`$totalCategorias`):** Las diferentes líneas de belleza que maneja el estudio (ej. *Pestañas Clásicas*, *Volumen*, *Cejas*, *Combos*).
4. **Inactivos / Pausados:** Tratamientos retirados de temporada o pausados temporalmente.

---

## 2. Tabla Interactiva del Catálogo

La tabla organiza la información de cada tratamiento de forma muy visual:

* **Foto en miniatura:** Muestra la imagen representativa del resultado del procedimiento. Si no tiene foto, muestra un ícono de reemplazo elegante.
* **Nombre y Descripción:** El título comercial del tratamiento y una breve explicación de qué incluye.
* **Categoría:** Insignia que identifica la especialidad.
* **Precio:** Formateado con el signo de pesos y separadores de miles (ej. `$95.000`).
* **Duración:** Tiempo en minutos que toma realizarlo (ej. `90 min`), crucial para que el calendario reserve la cantidad de horas exacta.
* **Estado:** Etiqueta verde si está *"Activo"* o gris/roja si está *"Inactivo"*.
* **Botones de acción:**
  * **Editar:** Abre el modal de edición precargando todos los datos del servicio.
  * **Eliminar:** Pide confirmación para eliminar o archivar el servicio.

---

## 3. Modal para Crear un Nuevo Servicio

Al hacer clic en **"+ Nuevo Servicio"**, se despliega una ventana modal con:

* **Formulario POST:** Apunta a `route('servicios.store')` con `@csrf` y `enctype="multipart/form-data"`.
* **Nombre:** Título del servicio.
* **Categoría:** Selector de especialidades disponibles.
* **Precio y Duración:** Campos numéricos para el costo y los minutos de trabajo.
* **Imagen del servicio:** Campo para subir una fotografía real del procedimiento (JPG, PNG o WebP).
* **Descripción:** Detalle técnico y cuidados para la clienta.
* **Estado:** Switch o selector para publicarlo inmediatamente o guardarlo como borrador.

---

## 4. Modal Dinámico de Edición

Para modificar precios o actualizar fotos sin salir de la página:

* El botón de editar en cada fila tiene atributos `data-*` (`data-id`, `data-nombre`, `data-precio`, `data-duracion`, etc.).
* Al hacer clic, JavaScript lee estos datos y llena automáticamente los campos del modal de edición.
* El formulario envía una petición `PUT` o `POST` a la ruta `servicios.update` correspondiente.

---

## 5. Filtros y Búsqueda en Vivo

* **Filtro por Categoría:** Permite listar únicamente tratamientos de *Pestañas*, *Cejas* o *Promociones*.
* **Buscador en tiempo real:** Encuentra servicios al instante mientras se teclea su nombre o rango de precio.
* **SweetAlert2:** Para confirmar antes de borrar un servicio y evitar que se afecte el historial de citas asociadas.

---

## En resumen: ¿Qué función cumple esta vista?
Es la vitrina comercial de LashApp: asegura que los precios estén siempre actualizados, calcula los tiempos correctos de agenda y le muestra a las clientas un catálogo profesional y ordenado.
