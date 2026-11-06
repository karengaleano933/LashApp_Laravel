# Explicación de mi Vista de Gestión de Pagos (`pagos/pagos.blade.php`)

Esta vista es el módulo financiero de LashApp para la administradora. Aquí controlo todos los ingresos del negocio: consulto el total de dinero recaudado, verifico pagos completados o pendientes, busco transacciones por cliente o método (Efectivo o Transferencia Nequi), reviso los comprobantes adjuntos (imágenes o PDF), registro nuevos pagos asociados a citas y edito o anulo pagos con confirmación segura.

---

### 1. Inclusión de componentes y alertas

| Código | ¿Qué hace? |
| :--- | :--- |
| `@include('layouts.header', ['titulo' => 'Gestión de Pagos'])` | Cargo la cabecera del sistema con el título de la pestaña. |
| `@include('layouts.sidebaradmin')` | Cargo el menú lateral de administración. |
| `@vite(['resources/css/pagos.css', 'resources/js/modules/admin/pagos.js'])` | Conecto los estilos de pagos y el script JavaScript modular mediante Vite. |
| `<div class="page-content" id="pagosContainer" data-base-url="{{ url('/admin/pagos') }}">` | Contenedor principal con la dirección URL base para enviar acciones desde JavaScript. |
| `@if (session('success')) ... @endif` | Cuadro verde que avisa cuando un pago se registró o modificó con éxito. |
| `@if (session('error')) ... @endif` | Cuadro rojo si ocurrió algún error procesando la transacción. |
| `@if (isset($errors) && $errors->any()) ... @endif` | Cuadro con la lista de campos que faltaron o tuvieron error de validación. |

---

### 2. Encabezado y tarjetas de métricas financieras

| Código | ¿Qué hace? |
| :--- | :--- |
| `<button type="button" class="btn-nuevo-pago" id="abrirNuevoPago">` | Botón verde para abrir la ventana modal y registrar un pago manual. |
| `<div class="pago-card"> ... ${{ number_format((float)($totalRecaudado ?? 0), 0, ',', '.') }} ... </div>` | Tarjeta verde con ícono de bolsa de dinero que calcula la suma total en pesos de todos los pagos aprobados. |
| `<div class="pago-card"> ... {{ $pagosCompletados ?? 0 }} ... </div>` | Tarjeta morada que cuenta cuántas transacciones han sido pagadas en su totalidad. |
| `<div class="pago-card"> ... {{ $pagosPendientes ?? 0 }} ... </div>` | Tarjeta dorada que muestra cuántos pagos están pendientes por verificar o cobrar. |
| `<div class="pago-card"> ... {{ $totalTransacc ?? 0 }} ... </div>` | Tarjeta azul que muestra el número total de transacciones registradas históricamente. |

---

### 3. Filtro por método y buscador en tiempo real

| Código | ¿Qué hace? |
| :--- | :--- |
| `<select id="filtroMetodo"> ... <option value="Efectivo">Efectivo</option> <option value="Transferencia">Transferencia / Nequi</option> </select>` | Menú desplegable para ver solo pagos en efectivo o solo pagos por Nequi/transferencia bancaria. |
| `<input type="text" id="buscarPago" placeholder="Buscar por cliente, servicio, referencia o método...">` | Campo buscador en vivo que filtra las filas de la tabla al instante mientras escribo. |

---

### 4. Tabla de historial de pagos y comprobantes

| Código | ¿Qué hace? |
| :--- | :--- |
| `<table id="tablaPagos" class="pagos-table">` | Tabla principal con columnas para Cliente, Servicio, Método, Monto, Referencia, Comprobante, Estado, Fecha y Acciones. |
| `@forelse ($pagos ?? [] as $pago) ... @empty ... @endforelse` | Recorro cada pago registrado; si no hay movimientos, muestro un mensaje de tabla vacía. |
| `<strong>{{ $clienteNombre }}</strong>` | Muestro el nombre del cliente que realizó el pago. |
| `<td>{{ $servicioNombre }}</td>` | Muestro el tratamiento o servicio correspondiente a ese cobro. |
| `<span class="metodo-badge"> ... {{ $metodo }}</span>` | Pastilla visual que muestra si se pagó en Efectivo (ícono de billete) o por Nequi (ícono de celular). |
| `<strong>${{ number_format((float)$monto, 0, ',', '.') }}</strong>` | Monto del pago formateado con puntos de miles en pesos colombianos. |
| `<small>{{ !empty($referencia) ? $referencia : 'N/A' }}</small>` | Código de referencia o número de aprobación de la transacción. |
| `@if (!empty($comprobanteRuta)) <a href="{{ $fileSrc }}" target="_blank" ...> Ver Comprobante </a> @else <span>Sin archivo</span> @endif` | Si el cliente adjuntó captura o soporte de pago, coloco un botón azul para abrir la imagen o PDF en una nueva pestaña; si no, indico que no hay archivo. |
| `<span class="estado-badge estado-{{ $estado }}">{{ ucfirst($estado) }}</span>` | Etiqueta con color según el estado (verde si está completado, amarillo si está pendiente, rojo si fue rechazado). |
| `{{ $fechaFormateada }}` | Fecha y hora exacta de la transacción en formato `d/m/Y, h:i a`. |
| `<button class="btn-accion btn-editar-pago" data-id="..." data-monto="..." ...>` | Botón con ícono de lápiz que abre el modal de edición con los datos del pago cargados. |
| `<button class="btn-accion btn-eliminar-pago" data-id="..." data-cliente="...">` | Botón con ícono de papelera para eliminar la transacción tras confirmar con SweetAlert2. |

---

### 5. Panel lateral de métodos aceptados

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="metodos-resumen-card"> ... </div>` | Tarjeta lateral informativa con los métodos habilitados en el estudio: Efectivo (en caja física) y Transferencia Nequi (con soporte digital). |

---

### 6. Modal para registrar un nuevo pago

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="modal-pago-overlay" id="modalCrearPago">` | Ventana emergente para registrar cobros manuales. |
| `<form action="{{ route('pagos.store') }}" method="POST" enctype="multipart/form-data" id="formCrearPago"> @csrf` | Formulario protegido con `@csrf` y soporte para subir archivos (`multipart/form-data`). |
| `<select name="id_cita" id="id_cita_pago" required> ... </select>` | Lista desplegable con las citas pendientes de cobro (`$citasParaPago`), mostrando el número de cita, nombre de clienta, servicio y precio sugerido. |
| `<select name="metodo_pago" id="metodo_pago_select" required> ... </select>` | Selector para elegir si fue cancelado en Efectivo o Transferencia Nequi. |
| `<input type="number" step="0.01" name="monto" id="monto_pago" required>` | Campo numérico donde se digita el dinero recibido. |
| `<input type="text" name="referencia" placeholder="Ej. Nequi #982341">` | Campo opcional para escribir el número de comprobante o nota bancaria. |
| `<input type="file" name="comprobante_nequi" accept="image/*,.pdf">` | Selector para adjuntar la foto o captura de pantalla del comprobante de transferencia. |
| `<select name="estado" required> <option value="completado">Completado</option> <option value="pendiente">Pendiente</option> </select>` | Permite fijar si el pago ya quedó cancelado en su totalidad o en espera. |
| `<button type="submit" class="btn-guardar-pago"> Registrar Pago </button>` | Botón final para guardar el registro en la base de datos. |

---

### 7. Modal de edición y eliminación

| Código | ¿Qué hace? |
| :--- | :--- |
| `<div class="modal-pago-overlay" id="modalEditarPago">` | Ventana emergente para corregir el valor, método, referencia o estado de un pago ya existente. |
| `<form action="" method="POST" enctype="multipart/form-data" id="formEditarPago"> @csrf @method('PUT')` | Formulario configurado con `@method('PUT')` para actualizar la transacción. |
| `<form id="formEliminarPago" method="POST" style="display: none;"> @csrf @method('DELETE') </form>` | Formulario invisible con método `DELETE` para borrar el pago cuando confirmo en la alerta de advertencia. |
| `@include('layouts.footer')` | Pie de página común del sistema. |
