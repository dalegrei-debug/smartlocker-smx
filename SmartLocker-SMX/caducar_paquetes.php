<?php
// Se incluye en index.php y paquetes_asignados.php
// Caduca los paquetes con más de 5 días en en_taquilla y les genera un PIN de retirada

$stmt = $conn->prepare("SELECT paquete_id FROM paquetes WHERE estado='en_taquilla' AND fecha_asignacion < NOW() - INTERVAL 5 DAY AND (pin_retirada IS NULL OR pin_retirada = '')");
$stmt->execute();
$aExpirar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

foreach ($aExpirar as $p) {
    $pin_retirada = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    $stmt = $conn->prepare("UPDATE paquetes SET estado='caducado', pin_retirada=? WHERE paquete_id=?");
    $stmt->bind_param('ss', $pin_retirada, $p['paquete_id']);
    $stmt->execute();
    $stmt->close();
}
?>
