# Explicación de mi Vista de Gestión de Usuarios (`gusuarios/gusuarios.blade.php`) (En mis palabras)

Esta vista es el **panel de administración de usuarios y seguridad** de **LashApp**. Permite controlar todas las cuentas registradas en el sistema (administradoras, trabajadoras/lashistas y clientas), asignar permisos, crear nuevos empleados, editar sus datos personales o desactivar cuentas.

---

## 1. Tarjetas de Estadísticas de Usuarios

En la parte superior agregué 5 tarjetas que muestran la composición de la comunidad de usuarios:

1. **Usuarios Totales (`$usuariosTotales`):** Cantidad global de cuentas creadas en el sistema.
2. **Usuarios Activos (`$usuariosActivos`):** Cuentas habilitadas con permiso para iniciar sesión y operar.
3. **Usuarios Inactivos (`$usuariosInactivos`):** Cuentas suspendidas o dadas de baja.
4. **Administradores (`$administradores`):** Cuentas con privilegios totales de administración.
5. **Último acceso:** Indicador visual de monitoreo en tiempo real del sistema.

---

## 2. Tabla Interactiva de Usuarios

La tabla principal centraliza la información de cada persona con un diseño muy intuitivo:

* **Columna Usuario:** Incluye un avatar circular con la inicial del nombre en mayúscula y el nombre completo con apellido.
* **Documento y Teléfono:** Cédula/DNI y número de WhatsApp o móvil para contacto directo.
* **Correo electrónico:** El correo con el que la persona inicia sesión.
* **Badge de Rol:** Una etiqueta con colores según su función:
  * `Administrador`: Morado / Azul oscuro.
  * `Trabajador / Lashista`: Morado claro / Fucsia.
  * `Cliente`: Rosado suave.
* **Estado:** Insignia verde para *"Activo"* y roja para *"Inactivo"*.
* **Fecha de Registro:** Formato legible con día, mes, año y hora exacta de creación.
* **Botones de acción:**
  * **Editar (`.btn-editar-usuario`):** Almacena en atributos `data-*` toda la información de la fila para abrir el modal de edición al instante.
  * **Eliminar (`.btn-eliminar-usuario`):** Abre una ventana de confirmación para evitar borrados accidentales.

---

## 3. Panel Lateral: Resumen de Roles y Permisos

A la derecha de la tabla, diseñé una tarjeta informativa que explica las responsabilidades de cada rol en el estudio:

* **Administrador:** Acceso completo al sistema, finanzas, métricas, configuración y catálogo.
* **Trabajador:** Especialistas que consultan su agenda diaria, confirman citas de sus clientas y revisan sus calificaciones.
* **Cliente:** Usuarias que pueden agendar citas, consultar su historial y dejar opiniones sobre los tratamientos.
* Cada ítem muestra cuántos usuarios pertenecen a esa categoría en tiempo real.

---

## 4. Modal para Crear Nuevo Usuario

Al presionar el botón **"+ Nuevo Usuario"**, se abre un formulario modal con validaciones:

* **Ruta de destino:** Envía un `POST` a `route('usuarios.store')` con `@csrf`.
* **Datos básicos:** Nombre, apellido, número de documento y teléfono.
* **Credenciales:** Correo electrónico y contraseña segura.
* **Asignación de Rol:** Menú desplegable que lista dinámicamente los roles existentes en la base de datos (`$roles`).
* **Estado:** Selector para dejarlo activo o inactivo desde el inicio.

---

## 5. Modal Dinámico para Editar Usuario

En lugar de recargar la página o crear vistas separadas, el botón de editar reutiliza un modal dinámico:

* Mediante JavaScript, escucha el clic del botón de editar, extrae los valores (`data-id`, `data-nombre`, `data-correo`, `data-rol`, etc.) y los inyecta en los campos del formulario.
* Actualiza la ruta del formulario a `usuarios.update` usando el método `PUT`.
* Permite cambiar el rol de un usuario o cambiar su estado a inactivo si ya no trabaja en el salón.

---

## 6. Interactividad y Búsqueda en Vivo

* **Filtro por Rol:** Un desplegable en la cabecera que permite aislar en pantalla únicamente a las trabajadoras, clientas o administradoras.
* **Buscador en tiempo real:** Filtra automáticamente filas por nombre, cédula o correo mientras se escribe.
* **Confirmación con SweetAlert2:** Para el borrado o desactivación de cuentas, protegiendo contra pérdida involuntaria de información.

---

## En resumen: ¿Qué función cumple esta vista?
Garantiza el control total de accesos al sistema, protege la privacidad de la información y permite gestionar al equipo de trabajo y clientas de forma rápida y segura.
