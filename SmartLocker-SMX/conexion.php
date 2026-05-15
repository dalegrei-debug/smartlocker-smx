<?php
$host     = 'localhost';
$dbname   = 'smartlocker';
$user     = 'webuser';
$password = '1234';

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?>
