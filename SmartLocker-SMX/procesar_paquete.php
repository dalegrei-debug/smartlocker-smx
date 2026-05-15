<?php
session_start();
$conexion = new mysqli("localhost", "webuser", "1234", "smartlocker");

require_once 'vendor/autoload.php';
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// Datos del formulario
$usuario = $_POST['usuario'];
$taquilla = $_POST['taquilla'];
$pin = rand(1000, 9999);

// Insertar paquete
$sql = "INSERT INTO paquetes (usuario, taquilla, pin, estado)
        VALUES ('$usuario', '$taquilla', '$pin', 'pendiente')";
$conexion->query($sql);

// ID del paquete recién creado
$paquete_id = $conexion->insert_id;

// Contenido del QR
$datosQR = "paquete_id=$paquete_id&usuario=$usuario";

// Opciones del QR
$options = new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel' => QRCode::ECC_L,
    'scale' => 5,
]);

// Generar QR
$qrImage = (new QRCode($options))->render($datosQR);
$qrImageBase64 = base64_encode($qrImage);

// Guardar QR en la BD
$conexion->query("UPDATE paquetes SET qr_code='$qrImageBase64' WHERE paquete_id='$paquete_id'");

// Volver al panel
header("Location: index.php?msg=paquete_creado");
exit;
?>
