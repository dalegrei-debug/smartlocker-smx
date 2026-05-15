<?php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método no permitido']); exit();
}
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'repartidor') {
    echo json_encode(['ok' => false, 'error' => 'Sin permisos']); exit();
}

// Comprobar si ya hay 2 paquetes pendientes (recibidos pero aún no entregados)
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM paquetes WHERE estado='pendiente'");
$stmt->execute();
$pendientes = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

if ($pendientes >= 2) {
    echo json_encode(['ok' => false, 'error' => 'Ya tienes 2 paquetes pendientes. Márcalos como entregados antes de recibir más.']); exit();
}

// Taquillas ocupadas = paquetes en_taquilla esperando al consumidor
$stmt = $conn->prepare("SELECT COUNT(DISTINCT taquilla) as total FROM paquetes WHERE estado='en_taquilla' AND taquilla IN ('A','B')");
$stmt->execute();
$ocupadas = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

if ($ocupadas >= 2) {
    echo json_encode(['ok' => false, 'error' => 'Las dos taquillas están llenas. Espera a que los consumidores recojan sus paquetes.']); exit();
}

// Cuántos paquetes nuevos podemos insertar
$libres = 2 - $pendientes;
if ($libres <= 0) {
    echo json_encode(['ok' => false, 'error' => 'No hay espacio para más paquetes.']); exit();
}

// Obtener consumidores activos
$stmt = $conn->prepare("SELECT username FROM usuarios WHERE rol='consumidor' AND estado='activo'");
$stmt->execute();
$consumidores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($consumidores)) {
    echo json_encode(['ok' => false, 'error' => 'No hay consumidores registrados']); exit();
}

$asignados = [];
for ($i = 0; $i < $libres; $i++) {
    $dest           = $consumidores[array_rand($consumidores)]['username'];
    $id             = 'PAQ-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
    $pin_cliente    = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    $pin_repartidor = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    while ($pin_repartidor === $pin_cliente) {
        $pin_repartidor = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
    }

    $stmt = $conn->prepare("INSERT INTO paquetes (usuario, paquete_id, pin, pin_repartidor, estado) VALUES (?, ?, ?, ?, 'pendiente')");
    $stmt->bind_param('ssss', $dest, $id, $pin_cliente, $pin_repartidor);
    if ($stmt->execute()) {
        // Solo devolvemos el pin_repartidor — el pin del cliente nunca sale del servidor
        $asignados[] = ['paquete_id' => $id, 'destinatario' => $dest, 'pin_repartidor' => $pin_repartidor];
    }
    $stmt->close();
}

$conn->close();
echo json_encode(['ok' => true, 'total' => count($asignados), 'asignados' => $asignados]);
?>
