<?php
session_start();
require_once 'conexion.php';

// Solo repartidores
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'repartidor') {
    header('Location: login.php');
    exit;
}

$usuario = htmlspecialchars($_SESSION['usuario']);

// Marcar caducados automáticamente
$conn->query("UPDATE paquetes SET estado='caducado' WHERE estado='pendiente' AND fecha_asignacion < NOW() - INTERVAL 5 DAY");

// Eliminar paquete caducado
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    $stmt = $conn->prepare("DELETE FROM paquetes WHERE id = ? AND estado = 'caducado'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header('Location: paquetes_caducados_repartidor.php?msg=ok');
    exit;
}

// Listar paquetes caducados
$resultado = $conn->query("SELECT *, DATEDIFF(NOW(), fecha_asignacion) AS dias_transcurridos FROM paquetes WHERE estado = 'caducado' ORDER BY fecha_asignacion ASC");
$total = $resultado ? $resultado->num_rows : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paquetes Caducados · SmartLocker</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0a0c10; --surface:#111318; --border:rgba(255,255,255,0.07);
            --accent:#4f8ef7; --glow:rgba(79,142,247,0.25); --accent2:#7c5cfc;
            --text:#f0f2f7; --muted:#6b7280; --ok:#22d3a5; --err:#f87171;
            --warn:#fb923c;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); min-height:100vh; color:var(--text); }
        body::before { content:''; position:fixed; top:-20%; left:-10%; width:700px; height:700px; border-radius:50%; background:radial-gradient(circle,rgba(248,113,113,.06),transparent 70%); pointer-events:none; }
        body::after  { content:''; position:fixed; bottom:-20%; right:-10%; width:600px; height:600px; border-radius:50%; background:radial-gradient(circle,rgba(124,92,252,.05),transparent 70%); pointer-events:none; }
        .grid-bg { position:fixed; inset:0; background:radial-gradient(circle,rgba(255,255,255,.03) 1px,transparent 1px) 0 0/40px 40px; pointer-events:none; }

        /* HEADER */
        header {
            position:sticky; top:0; z-index:100;
            background:rgba(10,12,16,0.85); backdrop-filter:blur(16px);
            border-bottom:1px solid var(--border);
            padding:0 32px; display:flex; align-items:center; justify-content:space-between; height:64px;
        }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .brand-icon { width:36px; height:36px; background:linear-gradient(135deg,var(--accent),var(--accent2)); border-radius:9px; display:grid; place-items:center; font-size:16px; box-shadow:0 4px 16px var(--glow); }
        .brand-text { font-family:'Syne',sans-serif; font-weight:800; font-size:17px; color:var(--text); letter-spacing:-.3px; }
        .brand-sub  { font-size:10px; color:var(--muted); letter-spacing:.5px; text-transform:uppercase; }
        .header-right { display:flex; align-items:center; gap:12px; }
        .btn-back { display:flex; align-items:center; gap:7px; padding:8px 14px; border-radius:9px; background:rgba(255,255,255,.05); border:1px solid var(--border); color:var(--muted); font-size:13px; font-weight:500; text-decoration:none; transition:background .2s,color .2s; }
        .btn-back:hover { background:rgba(255,255,255,.09); color:var(--text); }
        .avatar { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--accent),var(--accent2)); display:grid; place-items:center; font-family:'Syne',sans-serif; font-weight:800; font-size:14px; color:#fff; }

        /* MAIN */
        main { max-width:960px; margin:0 auto; padding:40px 32px 60px; position:relative; z-index:1; }

        @keyframes up { from { opacity:0; transform:translateY(16px); } }

        /* PAGE HEADER */
        .page-header { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:32px; animation:up .5s cubic-bezier(.16,1,.3,1) both; }
        .page-header-left { display:flex; align-items:center; gap:16px; }
        .page-icon { width:52px; height:52px; border-radius:14px; background:rgba(248,113,113,.12); border:1px solid rgba(248,113,113,.2); display:grid; place-items:center; font-size:24px; }
        .page-title { font-family:'Syne',sans-serif; font-size:22px; font-weight:800; letter-spacing:-.4px; }
        .page-sub { font-size:13px; color:var(--muted); margin-top:4px; }
        .total-badge {
            padding:6px 14px; border-radius:20px; font-size:13px; font-weight:700;
            background:rgba(248,113,113,.12); border:1px solid rgba(248,113,113,.25); color:var(--err);
            align-self:center;
        }

        /* ALERT */
        .alert { display:flex; align-items:center; gap:12px; padding:14px 18px; border-radius:12px; margin-bottom:24px; font-size:13px; animation:up .4s .1s cubic-bezier(.16,1,.3,1) both; }
        .alert.ok  { background:rgba(34,211,165,.08); border:1px solid rgba(34,211,165,.2); color:var(--ok); }
        .alert.info { background:rgba(251,146,60,.08); border:1px solid rgba(251,146,60,.2); color:var(--warn); }

        /* TABLE */
        .table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; animation:up .5s .15s cubic-bezier(.16,1,.3,1) both; }
        table { width:100%; border-collapse:collapse; }
        thead { background:rgba(248,113,113,.08); border-bottom:1px solid rgba(248,113,113,.15); }
        th { padding:13px 16px; text-align:left; font-size:11px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; color:var(--err); }
        td { padding:14px 16px; font-size:13px; border-bottom:1px solid var(--border); vertical-align:middle; }
        tbody tr:last-child td { border-bottom:none; }
        tbody tr { transition:background .15s; }
        tbody tr:hover { background:rgba(248,113,113,.04); }

        .badge-estado {
            display:inline-flex; align-items:center; gap:5px;
            padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700;
            background:rgba(248,113,113,.12); color:var(--err); border:1px solid rgba(248,113,113,.2);
        }
        .badge-estado::before { content:''; width:6px; height:6px; border-radius:50%; background:var(--err); }

        .dias-pill {
            display:inline-block; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700;
            background:rgba(251,146,60,.12); color:var(--warn); border:1px solid rgba(251,146,60,.2);
        }
        .dias-pill.critico { background:rgba(248,113,113,.15); color:var(--err); border-color:rgba(248,113,113,.3); }

        .taquilla-tag {
            display:inline-block; padding:3px 10px; border-radius:7px; font-size:12px; font-weight:700;
            background:rgba(79,142,247,.1); color:var(--accent); border:1px solid rgba(79,142,247,.2);
            font-family:'Syne',sans-serif;
        }

        .btn-del {
            display:inline-flex; align-items:center; gap:6px;
            padding:7px 14px; border-radius:9px; border:1px solid rgba(248,113,113,.25);
            background:rgba(248,113,113,.08); color:var(--err); font-size:12px; font-weight:600;
            text-decoration:none; cursor:pointer; transition:background .2s,border-color .2s;
            white-space:nowrap;
        }
        .btn-del:hover { background:rgba(248,113,113,.18); border-color:rgba(248,113,113,.4); }
        .btn-del svg { width:14px; height:14px; flex-shrink:0; }

        /* EMPTY */
        .empty-state { text-align:center; padding:64px 32px; animation:up .5s .15s cubic-bezier(.16,1,.3,1) both; }
        .empty-icon { font-size:52px; margin-bottom:16px; }
        .empty-state h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:800; margin-bottom:8px; color:var(--ok); }
        .empty-state p { font-size:13px; color:var(--muted); }

        /* CONFIRM MODAL */
        .overlay { position:fixed; inset:0; background:rgba(0,0,0,.75); backdrop-filter:blur(8px); z-index:200; display:none; place-items:center; }
        .overlay.show { display:grid; }
        .confirm-modal {
            background:var(--surface); border:1px solid rgba(248,113,113,.2); border-radius:20px;
            padding:36px 32px; width:340px; text-align:center;
            box-shadow:0 40px 80px rgba(0,0,0,.6);
            animation:up .35s cubic-bezier(.16,1,.3,1) both;
        }
        .confirm-icon { font-size:40px; margin-bottom:14px; }
        .confirm-modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:800; margin-bottom:8px; }
        .confirm-modal p { font-size:13px; color:var(--muted); margin-bottom:24px; line-height:1.6; }
        .confirm-info { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:10px; padding:10px 14px; margin-bottom:20px; font-size:12px; color:var(--muted); text-align:left; line-height:1.8; }
        .confirm-info span { color:var(--text); font-weight:500; }
        .confirm-btns { display:flex; gap:10px; }
        .btn-cancel { flex:1; padding:11px; border-radius:10px; border:1px solid var(--border); background:transparent; color:var(--muted); font-size:13px; font-weight:500; cursor:pointer; transition:background .2s; }
        .btn-cancel:hover { background:rgba(255,255,255,.05); }
        .btn-confirm-del { flex:1; padding:11px; border-radius:10px; border:none; background:var(--err); color:#fff; font-family:'Syne',sans-serif; font-size:13px; font-weight:700; cursor:pointer; transition:opacity .2s; }
        .btn-confirm-del:hover { opacity:.85; }
    </style>
</head>
<body>
<div class="grid-bg"></div>

<header>
    <a class="brand" href="index.php">
        <div class="brand-icon">🔐</div>
        <div>
            <div class="brand-text">SmartLocker</div>
            <div class="brand-sub">Solutions</div>
        </div>
    </a>
    <div class="header-right">
        <a class="btn-back" href="index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="15" height="15"><polyline points="15 18 9 12 15 6"/></svg>
            Volver
        </a>
        <div class="avatar"><?= strtoupper(substr($usuario, 0, 1)) ?></div>
    </div>
</header>

<main>

    <div class="page-header">
        <div class="page-header-left">
            <div class="page-icon">⚠️</div>
            <div>
                <div class="page-title">Paquetes Caducados</div>
                <div class="page-sub">Paquetes sin recoger pasados 5 días · Elimínalos de la taquilla</div>
            </div>
        </div>
        <?php if ($total > 0): ?>
            <div class="total-badge"><?= $total ?> paquete<?= $total !== 1 ? 's' : '' ?></div>
        <?php endif; ?>
    </div>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'ok'): ?>
        <div class="alert ok">✔ Paquete eliminado correctamente del sistema.</div>
    <?php endif; ?>

    <?php if ($total > 0): ?>
        <div class="alert info">⚠️ Estos paquetes llevan más de 5 días sin ser recogidos. Retíralos físicamente de la taquilla y elimínalos del sistema.</div>
    <?php endif; ?>

    <?php if ($total > 0): ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Paquete ID</th>
                    <th>Usuario</th>
                    <th>Taquilla</th>
                    <th>PIN</th>
                    <th>Fecha asignación</th>
                    <th>Días</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $resultado->fetch_assoc()):
                    $dias = (int)$row['dias_transcurridos'];
                    $critico = $dias >= 10;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($row['paquete_id']) ?></strong></td>
                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                    <td><span class="taquilla-tag"><?= $row['taquilla'] ?></span></td>
                    <td><code style="color:var(--muted);font-size:13px;"><?= $row['pin'] ?></code></td>
                    <td style="color:var(--muted)"><?= date('d/m/Y H:i', strtotime($row['fecha_asignacion'])) ?></td>
                    <td><span class="dias-pill <?= $critico ? 'critico' : '' ?>"><?= $dias ?> días</span></td>
                    <td><span class="badge-estado">caducado</span></td>
                    <td>
                        <a class="btn-del"
                           href="#"
                           onclick="confirmarEliminar(<?= $row['id'] ?>, '<?= htmlspecialchars($row['paquete_id']) ?>', '<?= $row['taquilla'] ?>', <?= $dias ?>); return false;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                            Eliminar
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php else: ?>
    <div class="table-wrap">
        <div class="empty-state">
            <div class="empty-icon">✅</div>
            <h3>Todo en orden</h3>
            <p>No hay paquetes caducados en este momento.</p>
        </div>
    </div>
    <?php endif; ?>

</main>

<!-- MODAL CONFIRMACIÓN -->
<div class="overlay" id="overlay">
    <div class="confirm-modal">
        <div class="confirm-icon">🗑️</div>
        <h3>Eliminar paquete</h3>
        <p>¿Confirmas que has retirado físicamente el paquete de la taquilla?</p>
        <div class="confirm-info">
            <div>📦 Paquete: <span id="cPaq"></span></div>
            <div>🔒 Taquilla: <span id="cTaq"></span></div>
            <div>📅 Días caducado: <span id="cDias"></span></div>
        </div>
        <div class="confirm-btns">
            <button class="btn-cancel" onclick="cerrar()">Cancelar</button>
            <a class="btn-confirm-del" id="btnConfirm" href="#">Sí, eliminar</a>
        </div>
    </div>
</div>

<script>
    function confirmarEliminar(id, paq, taq, dias) {
        document.getElementById('cPaq').textContent  = paq;
        document.getElementById('cTaq').textContent  = taq;
        document.getElementById('cDias').textContent = dias + ' días';
        document.getElementById('btnConfirm').href   = 'paquetes_caducados_repartidor.php?eliminar=' + id;
        document.getElementById('overlay').classList.add('show');
    }
    function cerrar() {
        document.getElementById('overlay').classList.remove('show');
    }
    document.getElementById('overlay').addEventListener('click', e => {
        if (e.target === e.currentTarget) cerrar();
    });
</script>

<?php $conn->close(); ?>
</body>
</html>
