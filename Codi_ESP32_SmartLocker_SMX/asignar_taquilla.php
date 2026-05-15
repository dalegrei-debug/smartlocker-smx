<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']); exit();
}

$paquete_id = trim($_POST['paquete_id'] ?? '');
$taquilla   = $_POST['taquilla'] ?? '';

if (!$paquete_id || !in_array($taquilla, ['A','B'])) {
    echo json_encode(['ok' => false, 'error' => 'Datos incorrectos']); exit();
}

// Actualizar taquilla — el estado sigue siendo 'pendiente' hasta que el repartidor lo marque como entregado
$stmt = $conn->prepare("UPDATE paquetes SET taquilla=? WHERE paquete_id=? AND estado='pendiente'");
$stmt->bind_param('ss', $taquilla, $paquete_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {
    echo json_encode(['ok' => true]);
} else {
    // Verificar si el paquete existe aunque no se haya actualizado
    $check = $conn->prepare("SELECT paquete_id FROM paquetes WHERE paquete_id=?");
    $check->bind_param('s', $paquete_id);
    $check->execute();
    $check->store_result();
    $existe = $check->num_rows > 0;
    $check->close();
    $conn->close();
    echo json_encode($existe ? ['ok' => true] : ['ok' => false, 'error' => 'Paquete no encontrado']);
    exit();
}

$conn->close();
?>
