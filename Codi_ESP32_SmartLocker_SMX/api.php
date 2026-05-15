<?php

// PIN correcte (pots canviar-lo)
$pin_correcto = "1234";

// Llegir el PIN enviat per l’ESP32
$pin_recibido = $_GET['pin'] ?? "";

// Comparar
if ($pin_recibido === $pin_correcto) {
    echo "OK";
} else {
    echo "ERROR";
}

?>
