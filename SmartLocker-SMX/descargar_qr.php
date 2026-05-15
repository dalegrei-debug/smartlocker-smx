<?php
session_start();

$paquete_id = $_GET['id'];

$conexion = new mysqli("localhost", "webuser", "1234", "smartlocker");

$sql = "SELECT qr_code FROM paquetes WHERE paquete_id='$paquete_id'";
$res = $conexion->query($sql);

if ($res->num_rows == 0) {
    die("QR no encontrado.");
}

$qr = $res->fetch_assoc()['qr_code'];

$imagen = base64_decode($qr);

header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="qr_'.$paquete_id.'.png"');
header('Content-Length: ' . strlen($imagen));

echo $imagen;
exit;
?>
