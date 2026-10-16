# Explicación de mi Vista de Gestión de Pagos (`pagos/pagos.blade.php`) (En mis palabras)

Esta vista es el **módulo de contabilidad y conciliación de pagos** de **LashApp**. Permite que la administración verifique el dinero que ingresa al salón, revise los comprobantes de transferencias bancarias (Nequi, Daviplata, Bancolombia), registre pagos en efectivo realizados en el estudio y apruebe o rechace transacciones.

---

## 1. Tarjetas de Resumen Financiero

En la parte superior agregué cuatro tarjetas métricas para tener una visión clara del flujo de caja:

1. **Total Recaudado (`$totalRecaudado`):** La suma total del dinero de todas las citas y pagos con estado aprobado, formateado con signo de pesos (`$`) y separadores de miles.
2. **Pagos Completados (`$pagosCompletados`):** Cantidad de transacciones verificadas con éxito.
3. **Pagos Pendientes (`$pagosPendientes`):** Pagos que las clientas enviaron con comprobante y que están esperando a que la administradora los verifique en su banco.
4. **Total Transacciones (`$totalTransacc`):** El volumen histórico de transacciones en el sistema.

---

## 2. Historial de Pagos y Comprobantes

La tabla central detalla cada movimiento financiero:

* **Cliente & Servicio:** Quién pagó y qué tratamiento de pestañas o cejas se le realizó.
* **Método de Pago:** Indica claramente si fue en *Efectivo*, *Transferencia*, *Nequi* o *Tarjeta*, con un ícono distintivo.
* **Monto:** La cantidad exacta pagada formateada en moneda.
* **Número de Referencia:** El código de aprobación del banco para rastrear transferencias duplicadas.
* **Columna de Comprobante:**
  * Si la clienta adjuntó una foto de la transferencia, aparece un botón interactivo *"Ver comprobante"*.
  * Si fue en efectivo, muestra una etiqueta indicando que no requiere archivo adjunto.
* **Estado:** Insignias de colores: *Aprobado* (verde), *Pendiente* (amarillo) o *Rechazado* (rojo).
* **Acciones:** Botones rápidos para aprobar el pago inmediatamente, rechazarlo si el dinero no entró a la cuenta, o eliminar registros erróneos.

---

## 3. Modal Visor de Comprobantes (Voucher Viewer)

Para no obligar a la administradora a descargar archivos pesados a su computador o abrir pestañas nuevas:

* Al hacer clic en *"Ver comprobante"*, se abre una ventana modal con fondo oscuro que amplía la captura del recibo bancario en alta resolución.
* Incluye un botón para rotar o cerrar la vista previa rápidamente.
* Así, la administradora puede comparar en segundos el saldo en su celular con la pantalla del sistema.

---

## 4. Modal para Registrar un Pago Manual

Diseñado para las clientas que pagan en el salón directamente en la recepción:

* **Formulario POST:** Envía los datos a la ruta correspondiente con `@csrf` y `enctype="multipart/form-data"`.
* **Asociación de Cita:** Permite vincular el pago a una cita existente.
* **Monto:** Valor cancelado por la clienta.
* **Método:** Selección entre efectivo, transferencia, datáfono o billetera digital.
* **Adjunto opcional:** Opción de subir foto del recibo si aplica.
* **Estado inicial:** Se puede registrar como *Aprobado* directamente si el dinero ya se recibió físicamente en caja.

---

## 5. Scripts y Búsqueda Interactiva

* **Filtro por Método:** Permite ver rápidamente solo los pagos en efectivo del día para cuadrar la caja física, o solo transferencias para cuadrar la cuenta de banco.
* **Buscador en tiempo real:** Encuentra transacciones por nombre de clienta, número de referencia bancaria o servicio.
* **Validación con SweetAlert2:** Para pedir confirmación antes de aprobar o rechazar un pago, asegurando la consistencia contable del salón.

---

## En resumen: ¿Qué valor aporta esta vista?
Evita fraudes con comprobantes falsos, permite cuadrar la caja diaria en minutos y mantiene un registro transparente de todos los ingresos de LashApp.
