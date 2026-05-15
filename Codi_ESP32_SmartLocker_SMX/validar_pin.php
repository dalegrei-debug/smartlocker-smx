<?php
require_once 'conexion.php';

$pin = trim($_GET['pin'] ?? '');

if (strlen($pin) !== 4 || !ctype_digit($pin)) {

    echo "ERROR";
    exit();
}

// CLIENT
$stmt = $conn->prepare("
SELECT paquete_id, taquilla
FROM paquetes
WHERE pin=?
AND estado='en_taquilla'
LIMIT 1
");

$stmt->bind_param('s', $pin);
$stmt->execute();

$row = $stmt->get_result()->fetch_assoc();

$stmt->close();

if ($row) {

    $upd = $conn->prepare("
    UPDATE paquetes
    SET estado='recogido',
        fecha_recogida=NOW()
    WHERE paquete_id=?
    ");

    $upd->bind_param('s', $row['paquete_id']);
    $upd->execute();
    $upd->close();

    $conn->close();

    echo "USER_" . $row['taquilla'];

    exit();
}

// REPARTIDOR ENTREGA
$stmt2 = $conn->prepare("
SELECT paquete_id, taquilla
FROM paquetes
WHERE pin_repartidor=?
AND estado='pendiente'
LIMIT 1
");

$stmt2->bind_param('s', $pin);
$stmt2->execute();

$row2 = $stmt2->get_result()->fetch_assoc();

$stmt2->close();

if ($row2) {

    $upd2 = $conn->prepare("
    UPDATE paquetes
    SET estado='en_taquilla'
    WHERE paquete_id=?
    ");

    $upd2->bind_param('s', $row2['paquete_id']);
    $upd2->execute();
    $upd2->close();

    $conn->close();

    echo "REP_" . $row2['taquilla'];

    exit();
}

// REPARTIDOR RETIRA CADUCADO
$stmt3 = $conn->prepare("
SELECT paquete_id, taquilla
FROM paquetes
WHERE pin_retirada=?
AND estado='caducado'
LIMIT 1
");

$stmt3->bind_param('s', $pin);
$stmt3->execute();

$row3 = $stmt3->get_result()->fetch_assoc();

$stmt3->close();

if ($row3) {

    $upd3 = $conn->prepare("
    UPDATE paquetes
    SET estado='recogido',
        fecha_recogida=NOW()
    WHERE paquete_id=?
    ");

    $upd3->bind_param('s', $row3['paquete_id']);
    $upd3->execute();
    $upd3->close();

    $conn->close();

    echo "CAD_" . $row3['taquilla'];

    exit();
}

$conn->close();

echo "ERROR";
?>
