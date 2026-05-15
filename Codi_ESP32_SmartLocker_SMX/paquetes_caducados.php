<?php
session_start();
require_once 'conexion.php';

// Solo administradores (rol 'user')
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit;
}

// Ejecutar caducidad automáticamente al cargar la página
$conn->query("UPDATE paquetes 
              SET estado = 'caducado' 
              WHERE estado = 'pendiente' 
              AND fecha_asignacion < NOW() - INTERVAL 5 DAY");

// Eliminar paquete si se solicita
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM paquetes WHERE id = ? AND estado = 'caducado'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: paquetes_caducados.php?msg=eliminado');
    exit;
}

// Obtener todos los paquetes caducados
$resultado = $conn->query("SELECT * FROM paquetes WHERE estado = 'caducado' ORDER BY fecha_asignacion ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paquetes Caducados</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            color: #333;
            padding: 30px;
        }

        h1 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #c0392b;
        }

        .msg {
            background: #fdecea;
            border-left: 4px solid #c0392b;
            padding: 10px 16px;
            margin-bottom: 20px;
            border-radius: 4px;
            color: #c0392b;
        }

        .msg.ok {
            background: #eafaf1;
            border-color: #27ae60;
            color: #27ae60;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        thead {
            background: #c0392b;
            color: #fff;
        }

        th, td {
            padding: 12px 16px;
            text-align: left;
            font-size: 0.9rem;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        tbody tr:hover {
            background: #fdecea;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.78rem;
            font-weight: 600;
            background: #fdecea;
            color: #c0392b;
        }

        .btn-eliminar {
            background: #c0392b;
            color: #fff;
            border: none;
            padding: 6px 14px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-eliminar:hover {
            background: #a93226;
        }

        .empty {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 1rem;
        }

        .back {
            display: inline-block;
            margin-bottom: 20px;
            color: #555;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .back:hover { color: #c0392b; }
    </style>
</head>
<body>

    <a href="index.php" class="back">← Volver al inicio</a>
    <h1>📦 Paquetes Caducados</h1>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado'): ?>
        <div class="msg ok">✔ Paquete eliminado correctamente.</div>
    <?php endif; ?>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Paquete ID</th>
                    <th>Taquilla</th>
                    <th>PIN</th>
                    <th>Estado</th>
                    <th>Fecha Asignación</th>
                    <th>Días caducado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch_assoc()):
                    $dias = (int) ((time() - strtotime($row['fecha_asignacion'])) / 86400);
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                    <td><?= htmlspecialchars($row['paquete_id']) ?></td>
                    <td><?= $row['taquilla'] ?></td>
                    <td><?= $row['pin'] ?></td>
                    <td><span class="badge">caducado</span></td>
                    <td><?= $row['fecha_asignacion'] ?></td>
                    <td><?= $dias ?> días</td>
                    <td>
                        <a href="paquetes_caducados.php?eliminar=<?= $row['id'] ?>"
                           class="btn-eliminar"
                           onclick="return confirm('¿Eliminar este paquete?')">
                            Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty">✅ No hay paquetes caducados.</div>
    <?php endif; ?>

    <?php $conn->close(); ?>
</body>
</html>
