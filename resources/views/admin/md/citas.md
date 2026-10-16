# Explicación de mi Vista de Gestión de Citas (`citas/citas.blade.php`) (En mis palabras)

Esta vista es el **corazón operativo de LashApp** para la administradora. Desde aquí puede revisar toda la agenda del salón, ver qué clientas vienen hoy, filtrar citas por especialista o fecha, ver un calendario mensual interactivo y agendar nuevas citas rápidamente con una ventana modal.

---

## 1. Encabezado y Alertas del Sistema

La vista arranca con el título del módulo y el botón principal de acción:

* **Botón `+ Nueva cita`:** Abre el modal interactivo sin tener que cambiar de página.
* **Mensajes de éxito (`session('success')`):** Cuando se crea o cancela una cita exitosamente en el controlador, aquí aparece una alerta verde con ícono de verificación.
* **Mensajes de validación (`$errors->any()`):** Si falta algún dato obligatorio (por ejemplo, la hora o el cliente), se despliega una caja roja listando los errores claramente.

---

## 2. Tarjetas de Métricas de Citas

Coloqué cuatro tarjetas para monitorear el ritmo de trabajo del salón:

1. **Citas Hoy (`$citasHoy`):** Cuántas clientas están programadas exclusivamente para el día actual.
2. **Citas esta semana (`$citasSemana`):** La carga laboral de la semana en curso.
3. **Citas completadas (`$citasComplet`):** Citas que ya fueron atendidas exitosamente en el mes.
4. **Citas canceladas (`$citasCancelad`):** Citas anuladas para evaluar la tasa de cancelación.

---

## 3. Tabla Interactiva de Próximas Citas

La tabla principal muestra los datos de forma muy limpia y organizada:

* **Cliente:** Muestra un avatar circular con la inicial de su nombre en mayúscula y su nombre completo.
* **Servicio:** Una píldora de color con un ícono de spa y el nombre del tratamiento (ej. *Lifting de Pestañas*, *Volumen Ruso*).
* **Hora y Fecha:** Formateadas con formato legible de 12 horas (`g:i a`) y fecha (`d/m/Y`).
* **Profesional:** La lashista asignada a atender a la clienta.
* **Estado:** Un badge estilizado con colores (`Pendiente` en amarillo, `Confirmada` en verde, `Cancelada` en rojo).
* **Botón de acción (Cancelar):** Al hacer clic, no cancela de golpe, sino que invoca una función JavaScript `confirmarCancelar(id)` que muestra una ventana de confirmación segura con SweetAlert2.

---

## 4. Panel Lateral: Calendario Dinámico y Filtros

Al lado derecho de la tabla diseñé dos herramientas muy útiles:

### A. Calendario Dinámico en JavaScript
* No requiere librerías pesadas externas: lo programé con JavaScript puro.
* Calcula los días del mes actual, el primer día de la semana y resalta automáticamente el día de hoy (`cal-today`).
* Permite navegar con flechitas hacia atrás (`<`) y adelante (`>`) entre los diferentes meses del año.
* Incluye una leyenda de colores para identificar citas confirmadas, canceladas o pendientes.

### B. Bloque de Buscar y Filtrar
* **Buscador en tiempo real:** Filtra al instante en la tabla por nombre de cliente o servicio.
* **Filtro por profesional:** Un menú desplegable que carga dinámicamente las trabajadoras activas del salón (`$listaTrabajadores`).
* **Filtro por estado:** Permite aislar solo las citas *Confirmadas*, *Pendientes*, *Completadas* o *Canceladas*.
* **Filtro por fecha:** Un campo de tipo `date` para revisar la agenda de cualquier día en específico.

---

## 5. Modal Emergente para Agendar Nueva Cita

Para que la administradora no pierda tiempo navegando a otra página, el botón **"Nueva cita"** despliega un modal supercompleto sobre la misma pantalla:

* **Formulario POST:** Envía los datos a `citas.store` protegido con `@csrf`.
* **Select de Clientes:** Carga la lista `$listaClientes`, mostrando nombre y correo para no confundir clientas con nombres parecidos.
* **Select de Servicios:** Carga `$listaServicios` mostrando el nombre, precio formateado en pesos colombianos y la duración en minutos (ej. `$85.000 (90 min)`).
* **Select de Especialistas:** Carga `$listaTrabajadores` con su nombre y especialidad.
* **Estado inicial:** Por defecto viene seleccionado en *Pendiente* o *Confirmada*.
* **Fecha y Hora:** Con restricción `min` para evitar agendar citas en fechas del pasado por error humano.
* **Observaciones:** Un campo de texto para apuntar alergias, preferencias o detalles especiales del diseño de la clienta.

---

## 6. Scripts y Comportamiento en JavaScript

Al final de la vista incluí la lógica interactiva:

* **DataTables:** Configurado en español para ofrecer paginación fluida y búsqueda instantánea.
* **Filtros conectados:** Los campos de búsqueda y selects activan `.search().draw()` de DataTables en tiempo real sin recargar la página.
* **SweetAlert2:** Para pedir confirmación elegante antes de cancelar cualquier cita (*"¿Cancelar esta cita? Esta acción actualizará el estado"*).
* **Control del Modal:** Se puede abrir con el botón, cerrar con la X, con el botón "Cancelar", haciendo clic en el fondo oscuro exterior, o simplemente presionando la tecla **Escape**.

---

## En resumen: ¿Qué soluciona esta vista?
Es la agenda integral del salón: organiza el tiempo de las lashistas, evita cruces de horarios, permite agendar en menos de 1 minuto y ofrece control total sobre las citas del día y la semana.
