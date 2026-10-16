<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['id_rol'] != 2 && $_SESSION['usuario']['id_rol'] != 1)) {
    header('Location: ../../views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';

$usuarioSesion = $_SESSION['usuario'] ?? [];
$id_usuario    = (int)($usuarioSesion['id_usuario'] ?? 0);

// Identificar id_trabajador
$id_trabajador = 0;
$resTrab = $conn->query("SELECT id_trabajador FROM trabajadores WHERE id_usuario = {$id_usuario}");
if ($resTrab && $rowTrab = $resTrab->fetch_assoc()) {
    $id_trabajador = (int)$rowTrab['id_trabajador'];
}

// Procesar formulario para agregar nuevo horario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_horario'])) {
    $fecha       = trim($_POST['fecha'] ?? '');
    $hora_inicio = trim($_POST['hora_inicio'] ?? '');
    $hora_fin    = trim($_POST['hora_fin'] ?? '');
    $estado      = trim($_POST['estado'] ?? 'Disponible');

    if (!empty($fecha) && !empty($hora_inicio) && !empty($hora_fin)) {
        $stmtIns = $conn->prepare("INSERT INTO horarios (id_trabajador, fecha, hora_inicio, hora_fin, estado) VALUES (?, ?, ?, ?, ?)");
        if ($stmtIns) {
            $stmtIns->bind_param("issss", $id_trabajador, $fecha, $hora_inicio, $hora_fin, $estado);
            if ($stmtIns->execute()) {
                $_SESSION['alert'] = ['icon' => 'success', 'title' => '¡Horario Agregado!', 'text' => 'El bloque de horario ha sido registrado.'];
            }
            $stmtIns->close();
        }
    }
    header('Location: horario.php');
    exit;
}

// Procesar eliminación de horario
if (isset($_GET['eliminar'])) {
    $id_horario = (int)$_GET['eliminar'];
    if ($id_horario > 0) {
        // Verificar si existen citas asociadas a este horario
        $chkCitas = $conn->query("SELECT COUNT(*) AS total FROM citas WHERE id_horario = {$id_horario}");
        $totalC = 0;
        if ($chkCitas && $rowC = $chkCitas->fetch_assoc()) {
            $totalC = (int)($rowC['total'] ?? 0);
        }

        if ($totalC > 0) {
            $_SESSION['alert'] = [
                'icon'  => 'warning',
                'title' => 'No se puede eliminar',
                'text'  => 'Este bloque horario está asignado a una cita existente. Cancela o modifica la cita antes de eliminar el horario.'
            ];
        } else {
            try {
                $conn->query("DELETE FROM horarios WHERE id_horario = {$id_horario}");
                $_SESSION['alert'] = ['icon' => 'success', 'title' => '¡Horario Eliminado!', 'text' => 'El bloque horario fue eliminado correctamente.'];
            } catch (mysqli_sql_exception $e) {
                $_SESSION['alert'] = [
                    'icon'  => 'error',
                    'title' => 'Error al eliminar',
                    'text'  => 'No se pudo eliminar el horario debido a citas asociadas en el sistema.'
                ];
            }
        }
    }
    header('Location: horario.php');
    exit;
}

// Obtener horarios del trabajador
$horarios = [];
$whereHorario = ($id_trabajador > 0) ? "WHERE id_trabajador = {$id_trabajador}" : "WHERE 1=1";
$resH = $conn->query("SELECT * FROM horarios {$whereHorario} ORDER BY fecha ASC, hora_inicio ASC");
if ($resH && $resH->num_rows > 0) {
    while ($rH = $resH->fetch_assoc()) {
        $horarios[] = $rH;
    }
}

$hoy    = date('Y-m-d');
$titulo = 'Mi Horario';
$pageCss = './css/horario.css?v=' . filemtime(__DIR__ . '/css/horario.css');

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebaradmin.php';
?>

<!-- ══════════════════ ALERTA SWEETALERT ══════════════════ -->
<?php if (isset($_SESSION['alert'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon:  '<?= htmlspecialchars($_SESSION['alert']['icon']) ?>',
        title: '<?= htmlspecialchars($_SESSION['alert']['title']) ?>',
        text:  '<?= htmlspecialchars($_SESSION['alert']['text']) ?>',
        confirmButtonColor: '#7c1db8'
    });
});
</script>
<?php unset($_SESSION['alert']); ?>
<?php endif; ?>

<div class="citas-header">
    <div>
        <h1>Mi Horario y Disponibilidad</h1>
        <p>Configura tus turnos y horarios de atención</p>
    </div>
    <button type="button" class="btn-nueva-cita" id="abrirNuevoHorario">
        <i class="fa-solid fa-plus"></i> Agregar Horario
    </button>
</div>

<div class="dashboard-card" style="background:#fff; border-radius:20px; border:1px solid #e8daf3; padding:24px;">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h3 style="font-family:'Playfair Display',Georgia,serif; font-size:18px; color:#2a0d3e; margin:0;">
            <i class="fa-solid fa-clock" style="color:#7c1db8;"></i> Mis Bloques de Horario
        </h3>
    </div>

    <div class="table-responsive">
        <table class="citas-table" style="width:100%; border-collapse:collapse; font-size:13px;">
            <thead>
                <tr style="text-align:left; border-bottom:1px solid #f0e6f8; color:#7a5a90;">
                    <th style="padding:10px 12px;">Fecha</th>
                    <th style="padding:10px 12px;">Hora Inicio</th>
                    <th style="padding:10px 12px;">Hora Fin</th>
                    <th style="padding:10px 12px;">Estado</th>
                    <th style="padding:10px 12px; text-align:center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($horarios)): ?>
                    <?php foreach ($horarios as $h): ?>
                        <tr style="border-bottom:1px solid #f8f2fc;">
                            <td style="padding:12px; color:#2a0d3e; font-weight:700;">
                                <i class="fa-regular fa-calendar" style="color:#7c1db8; margin-right:6px;"></i>
                                <?= date('d/m/Y', strtotime($h['fecha'])) ?>
                            </td>
                            <td style="padding:12px; color:#2a0d3e;">
                                <?= date('g:i a', strtotime($h['hora_inicio'])) ?>
                            </td>
                            <td style="padding:12px; color:#2a0d3e;">
                                <?= date('g:i a', strtotime($h['hora_fin'])) ?>
                            </td>
                            <td style="padding:12px;">
                                <span class="badge-pill-status" style="padding:4px 12px; border-radius:15px; font-size:11px; font-weight:700; <?= strtolower($h['estado']) === 'disponible' ? 'background:#d1fae5; color:#065f46;' : 'background:#fef3c7; color:#92400e;' ?>">
                                    <?= htmlspecialchars($h['estado']) ?>
                                </span>
                            </td>
                            <td style="padding:12px; text-align:center;">
                                <a href="horario.php?eliminar=<?= $h['id_horario'] ?>" 
                                   style="background:#fee2e2; color:#dc2626; border:none; width:32px; height:32px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; text-decoration:none;"
                                   onclick="return confirm('¿Eliminar este bloque horario?');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding:40px; color:#7a5a90;">
                            No tienes bloques de horario registrados aún.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Agregar Horario -->
<div id="modalNuevoHorario" class="modal-lash-overlay">
    <div class="modal-lash-contenido">
        <div class="modal-lash-header">
            <div class="modal-lash-titulo">
                <i class="fa-solid fa-clock"></i>
                <span>Nuevo Bloque Horario</span>
            </div>
            <button type="button" class="modal-lash-cerrar" id="cerrarNuevoHorario">&times;</button>
        </div>

        <form action="horario.php" method="POST">
            <input type="hidden" name="crear_horario" value="1">
            <div class="modal-lash-body">
                <div class="modal-lash-grid">
                    <div class="modal-lash-campo">
                        <label for="fecha">Fecha</label>
                        <input type="date" name="fecha" id="fecha" value="<?= $hoy ?>" required>
                    </div>
                    <div class="modal-lash-campo">
                        <label for="hora_inicio">Hora Inicio</label>
                        <input type="time" name="hora_inicio" id="hora_inicio" value="09:00" required>
                    </div>
                    <div class="modal-lash-campo">
                        <label for="hora_fin">Hora Fin</label>
                        <input type="time" name="hora_fin" id="hora_fin" value="10:00" required>
                    </div>
                    <div class="modal-lash-campo">
                        <label for="estado">Estado</label>
                        <select name="estado" id="estado">
                            <option value="Disponible">Disponible</option>
                            <option value="Ocupado">Ocupado</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-lash-footer">
                <button type="button" class="btn-secundario" id="cancelarNuevoHorario">Cancelar</button>
                <button type="submit" class="btn-primario">Guardar Horario</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalNuevoHorario');
    const abrir = document.getElementById('abrirNuevoHorario');
    const cerrar = document.getElementById('cerrarNuevoHorario');
    const cancel = document.getElementById('cancelarNuevoHorario');

    if (abrir && modal) abrir.addEventListener('click', () => modal.classList.add('activo'));
    if (cerrar && modal) cerrar.addEventListener('click', () => modal.classList.remove('activo'));
    if (cancel && modal) cancel.addEventListener('click', () => modal.classList.remove('activo'));
});
</script>

<?php require_once __DIR__ . '/../layouts/Footer.php'; ?>
