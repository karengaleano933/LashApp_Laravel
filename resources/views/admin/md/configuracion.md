# Explicación de mi Vista de Configuración (`configuracion/configuracion.blade.php`)

Esta vista es el panel de ajustes generales de LashApp. Desde aquí puedo personalizar la información de mi negocio (nombre, teléfono, correo, dirección, zona horaria y subir el logo oficial), definir las preferencias de la plataforma (idioma, moneda, formato de hora y fecha), establecer las políticas de agendamiento y cancelación de citas, y gestionar copias de seguridad o acciones avanzadas del sistema.

---

### 1. Inclusiones, estilos y alertas de estado

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header', ['titulo' => 'Configuración del Sistema'])` | Cargo la cabecera general asignándole como título de pestaña "Configuración del Sistema". |
| `@include('layouts.sidebaradmin')` | Cargo el menú lateral izquierdo de administración. |
| `@vite(['resources/css/configuracion.css', 'resources/js/modules/admin/configuracion.js'])` | Conecto los estilos visuales propios y el módulo JavaScript de configuración mediante Vite. |
| `@if (session('success')) ... @endif` | Muestro una alerta verde si los ajustes se guardaron correctamente en la base de datos. |
| `@if (session('error')) ... @endif` | Muestro una alerta roja si ocurrió algún problema al guardar. |
| `@if (isset($errors) && $errors->any()) ... @endif` | Muestro un recuadro rojo listando errores de validación si algún campo obligatorio se envió incorrectamente. |

---

### 2. Formulario y datos del negocio

| Código | ¿Qué hace? |
| :--- | :--- |
| `<form action="{{ route('configuracion.update') }}" method="POST" enctype="multipart/form-data" id="configForm"> @csrf` | Formulario general con `@csrf` y `multipart/form-data` que permite enviar tanto textos como el archivo de imagen del logo a la ruta de actualización en Laravel. |
| `<input type="text" id="nombre_estudio" name="nombre_estudio" value="{{ old('nombre_estudio', $configuracion->nombre_estudio ?? '...') }}" required>` | Campo para escribir el nombre comercial del salón o estudio de pestañas. |
| `<input type="text" id="telefono" name="telefono" ... required>` | Campo para el número telefónico o WhatsApp de contacto oficial del negocio. |
| `<input type="email" id="email_contacto" name="email_contacto" ... required>` | Campo para el correo electrónico del negocio. |
| `<input type="text" id="direccion" name="direccion" ... required>` | Campo para la dirección física del local comercial. |
| `<select id="zona_horaria" name="zona_horaria"> ... </select>` | Selector para definir la zona horaria del sistema (por defecto Bogotá GMT-05:00) para que las citas y recordatorios se sincronicen a la hora exacta de Colombia. |

---

### 3. Subida y previsualización del logo oficial

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="logo-display-card" id="logoPreviewBox">` | Cajita cuadrada donde se previsualiza el logo actual del negocio o el ícono de corona dorada si aún no hay logo subido. |
| `<input type="file" id="logoFileInput" name="logo" accept="image/png, image/jpeg, image/webp" style="display:none;" onchange="previewLogo(event)">` | Campo de selección de archivos oculto que solo acepta imágenes PNG, JPG o WEBP. |
| `<button type="button" class="btn-cambiar-logo" onclick="document.getElementById('logoFileInput').click()">` | Botón con ícono de nube que al presionarlo abre la ventana para que elija una imagen desde mi computador. |
| `<button type="submit" class="btn-guardar-cambios-p"> <i class="fa-solid fa-bookmark"></i> Guardar cambios </button>` | Botón morado principal para guardar la información del negocio en la base de datos. |

---

### 4. Tarjeta de configuración rápida y accesos a políticas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<a href="{{ route('citas.index') }}" class="quick-cfg-item"> ... <strong>Configuración de Citas</strong> ... </a>` | Tarjeta de acceso rápido que me lleva al módulo de citas. |
| `<a href="{{ route('pagos.index') }}" class="quick-cfg-item"> ... <strong>Métodos de Pago</strong> ... </a>` | Acceso rápido para administrar las pasarelas y métodos de pago. |
| `<a href="javascript:void(0)" onclick="mostrarModalImpuestos()" class="quick-cfg-item"> ... <strong>Impuestos y Tarifas</strong> ... </a>` | Abre una ventana modal emergente para configurar tarifas e impuestos aplicables. |
| `<a href="javascript:void(0)" onclick="mostrarModalPoliticas()" class="quick-cfg-item"> ... <strong>Políticas del Negocio</strong> ... </a>` | Abre una ventana emergente para revisar y redactar términos y condiciones del salón. |

---

### 5. Preferencias generales de la plataforma

| Código | ¿Qué hace? |
| :--- | :--- |
| `<select id="idioma" name="idioma"> ... </select>` | Menú para seleccionar el idioma del sistema (Español, Inglés o Portugués). |
| `<select id="moneda" name="moneda"> ... </select>` | Menú para seleccionar la moneda principal (Peso Colombiano COP, Dólar USD o Euro EUR). |
| `<select id="formato_fecha" name="formato_fecha"> ... </select>` | Permite escoger el formato de fecha preferido (ej. `DD/MM/YYYY`). |
| `<select id="formato_hora" name="formato_hora"> ... </select>` | Permite alternar entre formato de 12 horas con am/pm o formato de 24 horas militar. |
| `<input type="checkbox" name="reservas_online" value="1" {{ ... ? 'checked' : '' }}>` | Interruptor tipo switch para encender o apagar la posibilidad de que las clientas reserven solas desde la web. |
| `<input type="checkbox" name="recordatorios_auto" value="1" {{ ... ? 'checked' : '' }}>` | Interruptor tipo switch para habilitar el envío automático de notificaciones de recordatorio antes de cada cita. |

---

### 6. Políticas y reglas de agendamiento de citas

| Código | ¿Qué hace? |
| :--- | :--- |
| `<select id="antelacion_reservar" name="antelacion_reservar"> ... </select>` | Tiempo mínimo de anticipación con el que una clienta puede reservar (ej. 1h, 2h, 4h o 24 horas antes). |
| `<select id="antelacion_cancelar" name="antelacion_cancelar"> ... </select>` | Tiempo límite antes de la cita para poder cancelar sin penalidad (ej. 6h, 12h, 24h o 48 horas antes). |
| `<select id="tiempo_entre_citas" name="tiempo_entre_citas"> ... </select>` | Espacio de descanso o desinfección entre cita y cita de una especialista (5, 10, 15 o 30 minutos). |
| `<select id="duracion_predeterminada" name="duracion_predeterminada"> ... </select>` | Duración promedio por defecto asignada a nuevos servicios (45, 60, 90 o 120 minutos). |
| `<textarea id="politica_cancelacion" name="politica_cancelacion" rows="3"> ... </textarea>` | Cuadro de texto donde redacto las reglas claras de cancelación que verán las clientas al reservar. |

---

### 7. Copias de seguridad y zona peligrosa

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="backup-dates"> ... </div>` | Muestra la fecha y hora exacta de la última copia de seguridad guardada y cuándo se ejecutará la siguiente copia automática. |
| `<button type="button" class="btn-backup-now" onclick="realizarBackup()"> ... Realizar copia ahora </button>` | Botón que genera un respaldo inmediato de la base de datos y archivos del sistema. |
| `<div class="danger-zone"> ... </div>` | Sección destacada en rojo para advertir que contiene acciones irreversibles. |
| `<button type="button" class="btn-danger-reset" onclick="confirmarRestablecer()"> ... Restablecer configuración </button>` | Botón que devuelve todos los ajustes a los valores iniciales de fábrica tras pedir confirmación de seguridad. |
| `<button type="button" class="btn-danger-delete" onclick="confirmarEliminarCuenta()"> ... Eliminar cuenta </button>` | Botón de máxima seguridad para solicitar la baja o eliminación de la cuenta administradora. |
| `@include('layouts.footer')` | Cargo el pie de página común del sistema. |
