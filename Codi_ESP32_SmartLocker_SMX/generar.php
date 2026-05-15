<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']); exit();
}

$destinatario   = trim($_POST['destinatario']   ?? '');
$taquilla       = $_POST['taquilla']            ?? '';
$pin_repartidor = trim($_POST['pin_repartidor'] ?? '');

if (!$destinatario || !in_array($taquilla, ['A','B'])) {
    echo json_encode(['ok' => false, 'error' => 'Faltan datos']); exit();
}
if (strlen($pin_repartidor) !== 4 || !ctype_digit($pin_repartidor)) {
    echo json_encode(['ok' => false, 'error' => 'El PIN debe tener 4 dígitos']); exit();
}

// Verificar que el PIN coincide con un paquete pendiente de esa taquilla
$stmt = $conn->prepare("SELECT paquete_id FROM paquetes WHERE taquilla=? AND pin_repartidor=? AND estado='pendiente'");
$stmt->bind_param('ss', $taquilla, $pin_repartidor);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    echo json_encode(['ok' => false, 'error' => 'PIN incorrecto para la taquilla ' . $taquilla]); exit();
}
$stmt->close();

// Verificar que el usuario destinatario existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ? AND estado = 'activo'");
$stmt->bind_param('s', $destinatario);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows === 0) {
    $stmt->close();
    echo json_encode(['ok' => false, 'error' => 'El usuario "' . htmlspecialchars($destinatario) . '" no existe']); exit();
}
$stmt->close();

// Generar PIN del cliente automáticamente
$pin_cliente = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
while ($pin_cliente === $pin_repartidor) {
    $pin_cliente = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
}

$id = 'PAQ-' . strtoupper(substr(md5(uniqid()), 0, 6));

$stmt = $conn->prepare("INSERT INTO paquetes (usuario, paquete_id, taquilla, pin, pin_repartidor, estado) VALUES (?, ?, ?, ?, ?, 'en_taquilla')");
$stmt->bind_param('sssss', $destinatario, $id, $taquilla, $pin_cliente, $pin_repartidor);

if ($stmt->execute()) {
    echo json_encode(['ok' => true, 'paquete_id' => $id, 'taquilla' => $taquilla, 'destinatario' => $destinatario]);
} else {
    echo json_encode(['ok' => false, 'error' => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
