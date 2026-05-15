<?php
session_start();
require_once 'conexion.php';

$username = trim($_POST['usuario']  ?? '');
$password = $_POST['password']      ?? '';

if (!$username || !$password) {
    header("Location: login.php?error=campos_vacios"); exit();
}

$stmt = $conn->prepare("SELECT id, username, password_hash, rol FROM usuarios WHERE username = ? AND estado = 'activo'");
$stmt->bind_param('s', $username);
$stmt->execute();
$stmt->bind_result($id, $user, $hash, $rol);
$stmt->fetch();
$stmt->close();
$conn->close();

if ($id && password_verify($password, $hash)) {
    $_SESSION['usuario'] = $user;
    $_SESSION['rol']     = $rol;
    $_SESSION['id']      = $id;
    header("Location: index.php");
} else {
    header("Location: login.php?error=credenciales");
}
exit();
?>
