<?php
require_once 'conexion.php';

$paquete = $_GET['paquete'] ?? '';

$stmt = $conn->prepare("
    SELECT estado
    FROM paquetes
    WHERE paquete_id = ?
");

$stmt->bind_param("s", $paquete);
$stmt->execute();

$res = $stmt->get_result()->fetch_assoc();

echo json_encode([
    'recogido' => isset($res['estado']) && $res['estado'] === 'recogido'
]);

$stmt->close();
$conn->close();
?>
