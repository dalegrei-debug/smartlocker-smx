<?php
session_start();
require_once 'conexion.php';

$usuario = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Usuario';
$rol     = isset($_SESSION['rol'])     ? $_SESSION['rol'] : 'consumidor';

if ($rol === 'repartidor') {
    // Repartidor: solo ve los paquetes ya entregados (recogidos), no los pendientes
    $stmt = $conn->prepare("SELECT p.paquete_id, p.taquilla, p.pin, p.estado, p.usuario as destinatario, p.fecha_asignacion, p.fecha_recogida FROM paquetes p WHERE p.estado='recogido' ORDER BY p.fecha_recogida DESC");
    $stmt->execute();
    $paquetes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    // Consumidor: ve sus paquetes recogidos desde la tabla paquetes
    $stmt = $conn->prepare("SELECT paquete_id, taquilla, pin, fecha_recogida as fecha FROM paquetes WHERE usuario=? AND estado='recogido' ORDER BY fecha_recogida DESC");
    $stmt->bind_param('s', $_SESSION['usuario']);
    $stmt->execute();
    $paquetes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial — SmartLocker</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0a0c10; --surface:#111318; --border:rgba(255,255,255,0.07);
            --accent:#4f8ef7; --glow:rgba(79,142,247,0.25); --accent2:#7c5cfc;
            --text:#f0f2f7; --muted:#6b7280; --ok:#22d3a5; --err:#f87171;
        }
        * { box-sizing:border-box; margin:0; padding:0; }

        body { font-family:'DM Sans',sans-serif; background:var(--bg); min-height:100vh; color:var(--text); }
        body::before, body::after { content:''; position:fixed; border-radius:50%; pointer-events:none; animation:drift 12s ease-in-out infinite alternate; }
        body::before { top:-20%; left:-10%; width:700px; height:700px; background:radial-gradient(circle,rgba(79,142,247,.08),transparent 70%); }
        body::after  { bottom:-20%; right:-10%; width:600px; height:600px; background:radial-gradient(circle,rgba(124,92,252,.07),transparent 70%); animation-duration:15s; animation-direction:alternate-reverse; }
        @keyframes drift { to { transform:translate(40px,30px); } }
        .grid-bg { position:fixed; inset:0; background:radial-gradient(circle,rgba(255,255,255,.03) 1px,transparent 1px) 0 0/40px 40px; pointer-events:none; }

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
        .avatar { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--accent),var(--accent2)); display:grid; place-items:center; font-family:'Syne',sans-serif; font-weight:800; font-size:14px; color:#fff; }
        .user-info { text-align:right; }
        .user-name { font-size:14px; font-weight:500; color:var(--text); }
        .user-role { font-size:11px; color:var(--muted); }
        .btn-logout { display:flex; align-items:center; gap:7px; padding:8px 14px; border-radius:9px; background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2); color:var(--err); font-size:13px; font-weight:500; text-decoration:none; transition:background .2s,border-color .2s; }
        .btn-logout:hover { background:rgba(248,113,113,0.18); border-color:rgba(248,113,113,0.4); }
        .btn-logout svg { width:15px; height:15px; }

        main { max-width:900px; margin:0 auto; padding:40px 32px 60px; position:relative; z-index:1; }
        @keyframes up { from { opacity:0; transform:translateY(16px); } }

        .top-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; animation:up .5s cubic-bezier(.16,1,.3,1) both; flex-wrap:wrap; gap:14px; }
        .top-bar h1 { font-family:'Syne',sans-serif; font-size:24px; font-weight:800; letter-spacing:-.5px; }
        .top-bar h1 span { background:linear-gradient(90deg,var(--accent),var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .btn-back { display:flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; background:rgba(255,255,255,.04); border:1px solid var(--border); color:var(--muted); font-size:13px; font-weight:500; text-decoration:none; transition:color .2s,border-color .2s; }
        .btn-back:hover { color:var(--text); border-color:rgba(255,255,255,.15); }
        .btn-back svg { width:15px; height:15px; }

        .summary { display:flex; gap:12px; margin-bottom:28px; flex-wrap:wrap; animation:up .5s .05s cubic-bezier(.16,1,.3,1) both; }
        .pill { padding:8px 16px; border-radius:99px; font-size:13px; font-weight:500; border:1px solid; display:flex; align-items:center; gap:6px; }
        .pill.green { background:rgba(34,211,165,.08); border-color:rgba(34,211,165,.2); color:var(--ok); }
        .pill.blue  { background:rgba(79,142,247,.08); border-color:rgba(79,142,247,.2); color:var(--accent); }
        .pill.muted { background:rgba(255,255,255,.03); border-color:var(--border); color:var(--muted); }

        .search-wrap { position:relative; margin-bottom:20px; animation:up .5s .1s cubic-bezier(.16,1,.3,1) both; }
        .search-wrap svg { position:absolute; left:14px; top:50%; transform:translateY(-50%); width:17px; height:17px; color:var(--muted); pointer-events:none; }
        .search-input { width:100%; padding:11px 14px 11px 42px; background:var(--surface); border:1px solid var(--border); border-radius:11px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:14px; outline:none; transition:border-color .2s,box-shadow .2s; }
        .search-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--glow); }
        .search-input::placeholder { color:#3d4450; }

        .table-wrap { background:var(--surface); border:1px solid var(--border); border-radius:16px; overflow:hidden; animation:up .5s .15s cubic-bezier(.16,1,.3,1) both; }
        .table-header { display:grid; grid-template-columns:1.4fr 0.8fr 0.8fr 1.4fr 0.8fr; padding:12px 20px; border-bottom:1px solid var(--border); }
        .table-header span { font-size:11px; font-weight:500; color:var(--muted); text-transform:uppercase; letter-spacing:.5px; }
        .table-row { display:grid; grid-template-columns:1.4fr 0.8fr 0.8fr 1.4fr 0.8fr; padding:14px 20px; border-bottom:1px solid rgba(255,255,255,.03); align-items:center; transition:background .15s; }
        .table-row:last-child { border-bottom:none; }
        .table-row:hover { background:rgba(255,255,255,.02); }

        .paq-id { font-family:'Syne',sans-serif; font-size:14px; font-weight:700; color:var(--text); display:flex; align-items:center; gap:8px; }
        .dot-ok { width:7px; height:7px; border-radius:50%; background:var(--ok); box-shadow:0 0 6px var(--ok); flex-shrink:0; }
        .cell { font-size:13px; color:var(--muted); }
        .cell.mono { font-family:monospace; font-size:14px; color:var(--text); letter-spacing:2px; }
        .badge-ok { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:99px; font-size:11px; font-weight:500; background:rgba(34,211,165,.1); border:1px solid rgba(34,211,165,.2); color:var(--ok); }

        .empty { text-align:center; padding:60px 20px; color:var(--muted); }
        .empty-icon { font-size:40px; margin-bottom:12px; }
        .empty p { font-size:14px; }
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
        <div class="user-info">
            <div class="user-name"><?= $usuario ?></div>
            <div class="user-role">Usuario estándar</div>
        </div>
        <div class="avatar"><?= strtoupper(substr($usuario, 0, 1)) ?></div>
        <a class="btn-logout" href="logout.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Salir
        </a>
    </div>
</header>

<main>
    <div class="top-bar">
        <h1>Historial de <span>recogidas</span></h1>
        <a class="btn-back" href="index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Volver al panel
        </a>
    </div>

    <div class="summary">
        <?php if ($rol === 'repartidor'): ?>
        <div class="pill green">📦 <?= count($paquetes) ?> paquetes en total</div>
        <?php if (!empty($paquetes)): ?>
        <div class="pill blue">📅 Último: <?= date('d/m/Y', strtotime($paquetes[0]['fecha_asignacion'])) ?></div>
        <?php endif; ?>
        <?php else: ?>
        <div class="pill green">✅ <?= count($paquetes) ?> paquetes recogidos</div>
        <?php if (!empty($paquetes)): ?>
        <div class="pill blue">📅 Último: <?= date('d/m/Y', strtotime($paquetes[0]['fecha'])) ?></div>
        <?php else: ?>
        <div class="pill muted">Aún no has recogido ningún paquete</div>
        <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if (!empty($paquetes)): ?>
    <div class="search-wrap">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input class="search-input" type="text" id="searchInput" placeholder="Buscar por ID, usuario, taquilla o PIN..." oninput="filtrar()">
    </div>

    <div class="table-wrap">
        <?php if ($rol === 'repartidor'): ?>
        <div class="table-header" style="grid-template-columns:1.2fr 1.2fr 0.7fr 0.7fr 1.2fr 0.8fr">
            <span>ID Paquete</span>
            <span>Destinatario</span>
            <span>Taquilla</span>
            <span>PIN</span>
            <span>Fecha asignación</span>
            <span>Estado</span>
        </div>
        <div id="tableBody">
            <?php foreach ($paquetes as $p):
                $fecha  = date('d/m/Y · H:i', strtotime($p['fecha_asignacion']));
                $search = strtolower($p['paquete_id'].' '.$p['destinatario'].' '.$p['taquilla'].' '.$p['pin']);
                $recogido = $p['estado'] === 'recogido';
            ?>
            <div class="table-row" style="grid-template-columns:1.2fr 1.2fr 0.7fr 0.7fr 1.2fr 0.8fr" data-search="<?= htmlspecialchars($search) ?>">
                <div class="paq-id"><span class="dot-ok" style="background:<?= $recogido ? 'var(--ok)' : 'var(--warn)' ?>;box-shadow:0 0 6px <?= $recogido ? 'var(--ok)' : 'var(--warn)' ?>"></span><?= htmlspecialchars($p['paquete_id']) ?></div>
                <div class="cell">👤 <?= htmlspecialchars($p['destinatario']) ?></div>
                <div class="cell">🔒 <?= htmlspecialchars($p['taquilla']) ?></div>
                <div class="cell mono"><?= htmlspecialchars($p['pin']) ?></div>
                <div class="cell">📅 <?= $fecha ?></div>
                <div>
                    <?php if ($recogido): ?>
                    <span class="badge-ok">✓ Recogido</span>
                    <?php else: ?>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:99px;font-size:11px;font-weight:500;background:rgba(251,146,60,.1);border:1px solid rgba(251,146,60,.2);color:var(--warn);">⏳ Pendiente</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php else: ?>
        <div class="table-header" style="grid-template-columns:1.4fr 0.8fr 0.8fr 1.4fr 0.8fr">
            <span>ID Paquete</span>
            <span>Taquilla</span>
            <span>PIN</span>
            <span>Fecha y hora</span>
            <span>Estado</span>
        </div>
        <div id="tableBody">
            <?php foreach ($paquetes as $p):
                $fechaFormato = date('d/m/Y · H:i', strtotime($p['fecha']));
                $search = strtolower($p['paquete_id'].' '.$p['taquilla'].' '.$p['pin']);
            ?>
            <div class="table-row" style="grid-template-columns:1.4fr 0.8fr 0.8fr 1.4fr 0.8fr" data-search="<?= htmlspecialchars($search) ?>">
                <div class="paq-id"><span class="dot-ok"></span><?= htmlspecialchars($p['paquete_id']) ?></div>
                <div class="cell">🔒 <?= htmlspecialchars($p['taquilla']) ?></div>
                <div class="cell mono"><?= htmlspecialchars($p['pin']) ?></div>
                <div class="cell">📅 <?= $fechaFormato ?></div>
                <div><span class="badge-ok">✓ Recogido</span></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="empty" id="emptySearch" style="display:none;">
            <div class="empty-icon">🔍</div>
            <p>No se encontraron paquetes con ese criterio.</p>
        </div>
    </div>

    <?php else: ?>
    <div class="table-wrap">
        <div class="empty">
            <div class="empty-icon">📭</div>
            <p><?= $rol === 'repartidor' ? 'Aún no has asignado ningún paquete.' : 'Todavía no has recogido ningún paquete.' ?></p>
        </div>
    </div>
    <?php endif; ?>
</main>

<script>
    function filtrar() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('.table-row');
        let visible = 0;
        rows.forEach(r => {
            const match = r.dataset.search.includes(q);
            r.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        document.getElementById('emptySearch').style.display = visible === 0 ? 'block' : 'none';
    }
</script>
</body>
</html>
