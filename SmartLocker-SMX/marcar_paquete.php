<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']); exit();
}

$paquete_id = trim($_POST['paquete_id'] ?? '');
$estado     = $_POST['estado']     ?? '';
$motivo     = trim($_POST['motivo'] ?? '');

if (!$paquete_id || !in_array($estado, ['entregado', 'no_entregado', 'retirado_caducado'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos incorrectos']); exit();
}

if ($estado === 'entregado') {
    // El repartidor ya lo entregó manualmente — simplemente lo eliminamos de pendientes
    $stmt = $conn->prepare("DELETE FROM paquetes WHERE paquete_id=? AND estado='pendiente'");
    $stmt->bind_param('s', $paquete_id);

} elseif ($estado === 'no_entregado') {
    $stmt = $conn->prepare("DELETE FROM paquetes WHERE paquete_id=?");
    $stmt->bind_param('s', $paquete_id);

} elseif ($estado === 'retirado_caducado') {
    $stmt = $conn->prepare("UPDATE paquetes SET estado='recogido', fecha_recogida=NOW() WHERE paquete_id=?");
    $stmt->bind_param('s', $paquete_id);
}

$stmt->execute();
$ok = $stmt->affected_rows > 0;
$stmt->close();
$conn->close();

echo json_encode(['ok' => $ok, 'error' => $ok ? null : 'No se pudo actualizar']);
?>
