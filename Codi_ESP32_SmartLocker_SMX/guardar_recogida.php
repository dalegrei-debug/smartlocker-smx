<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']);
    exit();
}

$usuario    = isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Anónimo';
$paquete_id = isset($_POST['paquete_id']) ? $conn->real_escape_string($_POST['paquete_id']) : '';
$pin        = isset($_POST['pin'])        ? $conn->real_escape_string($_POST['pin'])        : '';
$taquilla   = isset($_POST['taquilla'])   ? $conn->real_escape_string($_POST['taquilla'])   : '';

if (!$paquete_id || !$pin || !$taquilla) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit();
}

// 1. Guardar en historial
$stmt = $conn->prepare("INSERT INTO historial_paquetes (usuario, paquete_id, pin, taquilla) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssss', $usuario, $paquete_id, $pin, $taquilla);
$stmt->execute();
$stmt->close();

// 2. Marcar paquete como recogido (sin filtro de usuario por si hay discrepancia)
$stmt2 = $conn->prepare("UPDATE paquetes SET estado='recogido', fecha_recogida=NOW() WHERE paquete_id=? AND estado IN ('en_taquilla','pendiente')");
$stmt2->bind_param('s', $paquete_id);
$stmt2->execute();
$affected = $stmt2->affected_rows;
$stmt2->close();

$conn->close();
echo json_encode(['ok' => $affected > 0, 'affected' => $affected, 'error' => $affected === 0 ? 'No se actualizó ningún paquete' : null]);
?>
