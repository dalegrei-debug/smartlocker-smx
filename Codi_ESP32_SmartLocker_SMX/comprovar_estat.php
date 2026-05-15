<?php

require_once 'conexion.php';

$paquete = $_GET['paquete'] ?? '';

$stmt = $conn->prepare("
    SELECT estado
    FROM paquetes
    WHERE paquete_id = ?
    LIMIT 1
");

$stmt->bind_param('s', $paquete);

$stmt->execute();

$result = $stmt->get_result();

$row = $result->fetch_assoc();

echo $row['estado'] ?? 'desconocido';

$stmt->close();
$conn->close();

?>
