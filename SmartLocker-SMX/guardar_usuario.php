<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php';

$username = trim($_POST['usuario']  ?? '');
$password = $_POST['password']      ?? '';
$confirm  = $_POST['confirm']       ?? '';
$rol      = $_POST['rol']           ?? '';

// Validaciones
if (!$username || !$password) {
    header("Location: register.php?error=campos_vacios"); exit();
}
if ($password !== $confirm) {
    header("Location: register.php?error=passwords_no_coinciden"); exit();
}
if (strlen($password) < 8) {
    header("Location: register.php?error=password_corta"); exit();
}
if (!in_array($rol, ['consumidor', 'repartidor'])) {
    header("Location: register.php?error=rol_invalido"); exit();
}

// Comprobar si el usuario ya existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE username = ?");
if (!$stmt) {
    die("Error prepare: " . $conn->error);
}
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    header("Location: register.php?error=usuario_existe"); exit();
}
$stmt->close();

// Insertar con contraseña hasheada y rol
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO usuarios (username, password_hash, rol, estado) VALUES (?, ?, ?, 'activo')");
if (!$stmt) {
    die("Error prepare insert: " . $conn->error);
}
$stmt->bind_param('sss', $username, $hash, $rol);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header("Location: login.php?registro=ok");
} else {
    $error = $stmt->error;
    $stmt->close();
    $conn->close();
    die("Error al guardar: " . $error);
}
exit();
?>
