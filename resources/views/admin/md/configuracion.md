# Explicación de mi Vista de Configuración (`configuracion/configuracion.blade.php`) (En mis palabras)

Esta vista es el **panel de control de los ajustes de LashApp**. Permite que la dueña del negocio o la administradora configure toda la información de la marca, los horarios, las políticas de reserva, la moneda, el logotipo y las reglas del salón sin tener que tocar una sola línea de código en el servidor.

---

## 1. El formulario principal y envío de archivos

Toda la pantalla está envuelta en un formulario que apunta a la ruta `configuracion.update`:

```blade
<form action="{{ route('configuracion.update') }}" method="POST" enctype="multipart/form-data" id="configForm">
    @csrf
```

* **`enctype="multipart/form-data"`:** Indispensable porque permite subir el archivo de imagen del logo del salón.
* **`@csrf`:** Token de seguridad obligatorio de Laravel para evitar ataques de falsificación de petición.
* **Gestión de alertas:** Muestra mensajes de éxito (`session('success')`) tras guardar cambios, o listas de errores en caso de datos inválidos.

---

## 2. Bloque: Información del Negocio y Logotipo

En esta sección se configuran los datos de contacto visibles para las clientas:

* **Nombre del negocio:** Nombre comercial (ej. *Oh La Lash by Lorena Ayala Studio*).
* **Teléfono y Correo:** Los canales oficiales de contacto y soporte.
* **Dirección física:** La ubicación exacta de la sede o estudio.
* **Zona horaria:** Menú desplegable para sincronizar los calendarios y horas (Bogotá, Ciudad de México, Caracas, Santiago, Madrid).
* **Carga interactiva de Logo:**
  * Un cuadro de vista previa (`logoPreviewBox`) que muestra el logo actual o un diseño por defecto con una corona dorada.
  * Un botón *"Cambiar logo"* conectado a un `input type="file"` invisible.
  * Una función JavaScript `previewLogo(event)` que lee la imagen seleccionada y la muestra inmediatamente en pantalla antes de enviar el formulario.

---

## 3. Bloque: Accesos de Configuración Rápida

Al lado de la información del negocio, preparé cuatro accesos directos con íconos estilizados:

1. **Configuración de Citas:** Enlaza a la gestión principal de citas.
2. **Métodos de Pago:** Enlaza a la configuración de pasarelas, transferencias y bancos.
3. **Impuestos y Tarifas:** Abre una ventana modal para fijar porcentajes de IVA o recargos por festivos.
4. **Políticas del Negocio:** Abre un modal para redactar términos y condiciones de servicio o políticas de cancelación.

---

## 4. Bloque: Preferencias Generales de la Plataforma

Permite adaptar el sistema al país o preferencia operativa:

* **Idioma:** Español, Inglés o Portugués.
* **Moneda principal:** Peso Colombiano (COP), Dólar (USD) o Euro (EUR).
* **Formatos de Fecha y Hora:** `DD/MM/YYYY`, `MM/DD/YYYY`, formato de 12 horas con AM/PM o formato militar de 24 horas.
* **Interruptores (Switches tipo Toggle):**
  * *Permitir reservas online a clientes:* Activa o pausa si las clientas pueden agendar solas desde su portal.
  * *Enviar recordatorios automáticos:* Activa el envío de notificaciones automáticas previas a la cita.

---

## 5. Bloque: Políticas de Citas y Agendamiento

Aquí se definen las reglas de negocio para cuidar el tiempo de las especialistas:

* **Antelación mínima para reservar:** Desde 1 hora hasta 24 horas antes, evitando que alguien agende con 5 minutos de aviso sin dar tiempo de preparación.
* **Antelación mínima para cancelar:** Exige a la clienta avisar con 6, 12 o 24 horas de antelación si no podrá asistir.
* **Tiempo entre citas (Buffer time):** Espacio de 5 a 15 minutos entre servicio y servicio para que la lashista limpie la camilla, desinfecte pinzas y prepare sus materiales.
* **Duración predeterminada:** Tiempo base sugerido para los tratamientos.

---

## 6. Modales y Comportamiento Dinámico

* **Previsualización instantánea:** La imagen del logo cambia al instante en el navegador usando `FileReader` de JavaScript, permitiendo ver cómo queda antes de guardar.
* **Modales interactivos:** Para impuestos y políticas, permitiendo modificar cláusulas legales y porcentajes sin abandonar la pantalla principal.

---

## En resumen: ¿Qué beneficios da esta vista?
Hace que el sistema **LashApp** sea 100% personalizable y adaptable a cualquier sede de belleza, manteniendo centralizados el logo, las reglas de reserva y los datos comerciales.
