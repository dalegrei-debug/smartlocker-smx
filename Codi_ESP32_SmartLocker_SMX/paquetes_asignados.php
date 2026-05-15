<?php
session_start();
require_once 'conexion.php';
require_once 'caducar_paquetes.php';

$usuario = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Usuario';

// Fase 1: Pendiente de entregar
$stmt = $conn->prepare("SELECT paquete_id, usuario as destinatario, taquilla, pin_repartidor, fecha_asignacion FROM paquetes WHERE estado='pendiente' ORDER BY fecha_asignacion ASC");
$stmt->execute();
$paquetes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fase 2: Pendiente por recoger (repartidor entregó, cliente no ha recogido)
$stmt2 = $conn->prepare("SELECT paquete_id, usuario as destinatario, taquilla, fecha_asignacion FROM paquetes WHERE estado='en_taquilla' ORDER BY fecha_asignacion ASC");
$stmt2->execute();
$en_taquilla = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

// Fase 3: Caducados
$stmt3 = $conn->prepare("SELECT paquete_id, usuario as destinatario, taquilla, pin_retirada, fecha_asignacion FROM paquetes WHERE estado='caducado' ORDER BY fecha_asignacion ASC");
$stmt3->execute();
$caducados = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt3->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paquetes asignados — SmartLocker</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0a0c10; --surface:#111318; --border:rgba(255,255,255,0.07);
            --accent:#4f8ef7; --glow:rgba(79,142,247,0.25); --accent2:#7c5cfc;
            --text:#f0f2f7; --muted:#6b7280; --ok:#22d3a5; --err:#f87171; --warn:#fb923c;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); min-height:100vh; color:var(--text); }
        body::before, body::after { content:''; position:fixed; border-radius:50%; pointer-events:none; animation:drift 12s ease-in-out infinite alternate; }
        body::before { top:-20%; left:-10%; width:700px; height:700px; background:radial-gradient(circle,rgba(79,142,247,.08),transparent 70%); }
        body::after  { bottom:-20%; right:-10%; width:600px; height:600px; background:radial-gradient(circle,rgba(124,92,252,.07),transparent 70%); animation-duration:15s; animation-direction:alternate-reverse; }
        @keyframes drift { to { transform:translate(40px,30px); } }
        .grid-bg { position:fixed; inset:0; background:radial-gradient(circle,rgba(255,255,255,.03) 1px,transparent 1px) 0 0/40px 40px; pointer-events:none; }

        header { position:sticky; top:0; z-index:100; background:rgba(10,12,16,0.85); backdrop-filter:blur(16px); border-bottom:1px solid var(--border); padding:0 32px; display:flex; align-items:center; justify-content:space-between; height:64px; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .brand-icon { width:36px; height:36px; background:linear-gradient(135deg,var(--accent),var(--accent2)); border-radius:9px; display:grid; place-items:center; font-size:16px; box-shadow:0 4px 16px var(--glow); }
        .brand-text { font-family:'Syne',sans-serif; font-weight:800; font-size:17px; color:var(--text); letter-spacing:-.3px; }
        .brand-sub  { font-size:10px; color:var(--muted); letter-spacing:.5px; text-transform:uppercase; }
        .header-right { display:flex; align-items:center; gap:12px; }
        .avatar { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--accent),var(--accent2)); display:grid; place-items:center; font-family:'Syne',sans-serif; font-weight:800; font-size:14px; color:#fff; }
        .user-info { text-align:right; }
        .user-name { font-size:14px; font-weight:500; }
        .user-role { font-size:11px; color:var(--muted); }
        .btn-logout { display:flex; align-items:center; gap:7px; padding:8px 14px; border-radius:9px; background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2); color:var(--err); font-size:13px; font-weight:500; text-decoration:none; transition:background .2s; }
        .btn-logout:hover { background:rgba(248,113,113,0.18); }
        .btn-logout svg { width:15px; height:15px; }

        main { max-width:900px; margin:0 auto; padding:40px 32px 60px; position:relative; z-index:1; }
        @keyframes up { from { opacity:0; transform:translateY(16px); } }

        .top-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; animation:up .5s cubic-bezier(.16,1,.3,1) both; flex-wrap:wrap; gap:14px; }
        .top-bar h1 { font-family:'Syne',sans-serif; font-size:24px; font-weight:800; letter-spacing:-.5px; }
        .top-bar h1 span { background:linear-gradient(90deg,var(--accent),var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        .btn-back { display:flex; align-items:center; gap:7px; padding:9px 16px; border-radius:9px; background:rgba(255,255,255,.04); border:1px solid var(--border); color:var(--muted); font-size:13px; font-weight:500; text-decoration:none; transition:color .2s; }
        .btn-back:hover { color:var(--text); }
        .btn-back svg { width:15px; height:15px; }

        .summary { display:flex; gap:12px; margin-bottom:24px; flex-wrap:wrap; animation:up .5s .05s cubic-bezier(.16,1,.3,1) both; }
        .pill { padding:8px 16px; border-radius:99px; font-size:13px; font-weight:500; border:1px solid; display:flex; align-items:center; gap:6px; }
        .pill.orange { background:rgba(251,146,60,.08); border-color:rgba(251,146,60,.2); color:var(--warn); }
        .pill.muted  { background:rgba(255,255,255,.03); border-color:var(--border); color:var(--muted); }
        .pill.caducado { background:rgba(248,113,113,.08); border-color:rgba(248,113,113,.25); color:var(--err); }

        /* CADUCADOS BANNER */
        .caducados-banner {
            display:flex; align-items:center; justify-content:space-between;
            background:rgba(248,113,113,.06); border:1px solid rgba(248,113,113,.25);
            border-radius:14px; padding:18px 22px; margin-bottom:24px;
            animation:up .5s .08s cubic-bezier(.16,1,.3,1) both;
        }
        .caducados-banner-left { display:flex; align-items:center; gap:14px; }
        .caducados-icon { width:46px; height:46px; border-radius:12px; background:rgba(248,113,113,.12); display:grid; place-items:center; font-size:22px; flex-shrink:0; }
        .caducados-title { font-family:'Syne',sans-serif; font-size:15px; font-weight:700; color:var(--err); margin-bottom:4px; }
        .caducados-sub   { font-size:12px; color:var(--muted); }
        .caducados-count { font-family:'Syne',sans-serif; font-size:36px; font-weight:800; color:var(--err); opacity:.6; }

        .paq-list { display:flex; flex-direction:column; gap:10px; animation:up .5s .1s cubic-bezier(.16,1,.3,1) both; }

        .paq-item { background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:18px 20px; display:flex; align-items:center; gap:16px; transition:border-color .2s, opacity .3s; }
        .paq-item.entregado   { border-color:rgba(34,211,165,.25); opacity:.6; }
        .paq-item.no-entregado { border-color:rgba(248,113,113,.25); opacity:.6; }
        .paq-item.caducado-item { border-color:rgba(251,146,60,.3); background:rgba(251,146,60,.03); }

        .paq-icon { width:42px; height:42px; border-radius:11px; background:rgba(251,146,60,.1); display:grid; place-items:center; font-size:20px; flex-shrink:0; transition:background .3s; }
        .paq-item.entregado    .paq-icon { background:rgba(34,211,165,.1); }
        .paq-item.no-entregado .paq-icon { background:rgba(248,113,113,.1); }

        .paq-info { flex:1; min-width:0; }
        .paq-info strong { font-family:'Syne',sans-serif; font-size:15px; font-weight:700; color:var(--text); display:block; margin-bottom:4px; }
        .paq-info span { font-size:12px; color:var(--muted); }

        .paq-actions { display:flex; gap:8px; flex-shrink:0; }

        .btn-entregado, .btn-no-entregado {
            padding:8px 16px; border-radius:9px; border:1px solid; font-family:'Syne',sans-serif;
            font-size:12px; font-weight:700; cursor:pointer; transition:all .2s; background:none;
        }
        .btn-entregado    { border-color:rgba(34,211,165,.3); color:var(--ok); }
        .btn-entregado:hover    { background:rgba(34,211,165,.1); }
        .btn-no-entregado { border-color:rgba(248,113,113,.3); color:var(--err); }
        .btn-no-entregado:hover { background:rgba(248,113,113,.1); }
        .btn-entregado:disabled, .btn-no-entregado:disabled { opacity:.3; cursor:not-allowed; }

        .estado-badge { font-size:12px; font-weight:600; padding:6px 12px; border-radius:99px; }
        .estado-badge.ok  { background:rgba(34,211,165,.1); border:1px solid rgba(34,211,165,.2); color:var(--ok); }
        .estado-badge.err { background:rgba(248,113,113,.1); border:1px solid rgba(248,113,113,.2); color:var(--err); }

        .empty-wrap { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:60px 20px; text-align:center; color:var(--muted); }
        .empty-wrap div { font-size:40px; margin-bottom:12px; }
        .empty-wrap p { font-size:14px; }

        .toast { position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(20px); background:rgba(30,33,40,.98); border:1px solid var(--border); border-radius:10px; padding:12px 20px; font-size:14px; color:var(--text); opacity:0; pointer-events:none; transition:opacity .3s,transform .3s; backdrop-filter:blur(12px); z-index:100; white-space:nowrap; }
        .toast.show    { opacity:1; transform:translateX(-50%) translateY(0); }
        .toast.success { border-color:rgba(34,211,165,.3); }
        .toast.error   { border-color:rgba(248,113,113,.3); }

        .overlay { position:fixed; inset:0; background:rgba(0,0,0,.7); backdrop-filter:blur(8px); z-index:200; display:none; place-items:center; }
        .overlay.show { display:grid; }
        .modal { background:var(--surface); border:1px solid var(--border); border-radius:20px; padding:36px; width:420px; max-width:95vw; box-shadow:0 40px 80px rgba(0,0,0,.6); animation:upModal .4s cubic-bezier(.16,1,.3,1) both; }
        @keyframes upModal { from { opacity:0; transform:translateY(20px) scale(.97); } }
        .modal-icon { width:52px; height:52px; border-radius:14px; background:rgba(248,113,113,.12); display:grid; place-items:center; font-size:24px; margin:0 auto 16px; border:1px solid rgba(248,113,113,.2); }
        .modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:800; text-align:center; margin-bottom:6px; }
        .modal p  { font-size:13px; color:var(--muted); text-align:center; margin-bottom:20px; }
        .modal textarea { width:100%; padding:12px 14px; background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:10px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:14px; outline:none; resize:vertical; min-height:100px; transition:border-color .2s,box-shadow .2s; margin-bottom:6px; }
        .modal textarea:focus { border-color:var(--err); box-shadow:0 0 0 3px rgba(248,113,113,.15); }
        .modal textarea::placeholder { color:#3d4450; }
        .modal-msg-err { font-size:12px; color:var(--err); min-height:16px; margin-bottom:16px; }
        .modal-btns { display:flex; gap:10px; }
        .btn-cancelar { flex:1; padding:12px; border-radius:10px; border:1px solid var(--border); background:rgba(255,255,255,.04); color:var(--muted); font-family:'Syne',sans-serif; font-size:14px; font-weight:700; cursor:pointer; transition:background .2s; }
        .btn-cancelar:hover { background:rgba(255,255,255,.08); color:var(--text); }
        .btn-confirmar-no { flex:1; padding:12px; border-radius:10px; background:rgba(248,113,113,.15); border:1px solid rgba(248,113,113,.3); color:var(--err); font-family:'Syne',sans-serif; font-size:14px; font-weight:700; cursor:pointer; transition:background .2s; }
        .btn-confirmar-no:hover { background:rgba(248,113,113,.25); }

        /* MODAL MOTIVO */
        .overlay { position:fixed; inset:0; background:rgba(0,0,0,.7); backdrop-filter:blur(8px); z-index:200; display:none; place-items:center; }
        .overlay.show { display:grid; }
        .modal { background:var(--surface); border:1px solid var(--border); border-radius:20px; padding:36px; width:420px; max-width:95vw; box-shadow:0 40px 80px rgba(0,0,0,.6); animation:up .4s cubic-bezier(.16,1,.3,1) both; position:relative; }
        @keyframes up { from { opacity:0; transform:translateY(20px) scale(.97); } }
        .modal-icon { width:52px; height:52px; border-radius:14px; background:rgba(248,113,113,.12); display:grid; place-items:center; font-size:24px; margin:0 auto 16px; border:1px solid rgba(248,113,113,.2); }
        .modal h3 { font-family:'Syne',sans-serif; font-size:18px; font-weight:800; text-align:center; margin-bottom:6px; }
        .modal p  { font-size:13px; color:var(--muted); text-align:center; margin-bottom:20px; }
        .modal textarea {
            width:100%; padding:12px 14px; background:rgba(255,255,255,.04); border:1px solid var(--border);
            border-radius:10px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:14px;
            outline:none; resize:vertical; min-height:100px; transition:border-color .2s, box-shadow .2s;
            margin-bottom:6px;
        }
        .modal textarea:focus { border-color:var(--err); box-shadow:0 0 0 3px rgba(248,113,113,.15); }
        .modal textarea::placeholder { color:#3d4450; }
        .modal-msg-err { font-size:12px; color:var(--err); min-height:16px; margin-bottom:16px; }
        .modal-btns { display:flex; gap:10px; }
        .btn-cancelar { flex:1; padding:12px; border-radius:10px; border:1px solid var(--border); background:rgba(255,255,255,.04); color:var(--muted); font-family:'Syne',sans-serif; font-size:14px; font-weight:700; cursor:pointer; transition:background .2s; }
        .btn-cancelar:hover { background:rgba(255,255,255,.08); }
        .btn-confirmar-no { flex:1; padding:12px; border-radius:10px; border:none; background:rgba(248,113,113,.15); border:1px solid rgba(248,113,113,.3); color:var(--err); font-family:'Syne',sans-serif; font-size:14px; font-weight:700; cursor:pointer; transition:background .2s; }
        .btn-confirmar-no:hover { background:rgba(248,113,113,.25); }
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
            <div class="user-role">Repartidor</div>
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
        <h1>Paquetes <span>asignados</span></h1>
        <a class="btn-back" href="index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Volver al panel
        </a>
    </div>

    <div class="summary">
        <?php if (!empty($paquetes)): ?>
        <div class="pill orange">⏳ <?= count($paquetes) ?> pendiente<?= count($paquetes) > 1 ? 's' : '' ?> de entregar</div>
        <?php else: ?>
        <div class="pill muted">Sin pendientes de entrega</div>
        <?php endif; ?>
        <?php if (!empty($en_taquilla)): ?>
        <div class="pill blue" style="background:rgba(79,142,247,.08);border-color:rgba(79,142,247,.25);color:var(--accent);">📬 <?= count($en_taquilla) ?> pendiente<?= count($en_taquilla) > 1 ? 's' : '' ?> por recoger</div>
        <?php endif; ?>
        <?php if (!empty($caducados)): ?>
        <div class="pill caducado">⚠ <?= count($caducados) ?> caducado<?= count($caducados) > 1 ? 's' : '' ?></div>
        <?php endif; ?>
    </div>

    <!-- RESUMEN CADUCADOS -->
    <?php if (!empty($caducados)): ?>
    <div class="caducados-banner">
        <div class="caducados-banner-left">
            <div class="caducados-icon">⏰</div>
            <div>
                <div class="caducados-title">Paquetes caducados — Requieren recogida</div>
                <div class="caducados-sub">Llevan más de 5 días en taquilla sin ser recogidos. El repartidor debe retirarlos.</div>
            </div>
        </div>
        <div class="caducados-count"><?= count($caducados) ?></div>
    </div>
    <?php endif; ?>

    <?php if (!empty($paquetes)): ?>
    <div class="paq-list">
        <?php foreach ($paquetes as $p):
            $fecha = date('d/m/Y · H:i', strtotime($p['fecha_asignacion']));
            $pid = htmlspecialchars($p['paquete_id']);
        ?>
        <div class="paq-item" id="item-<?= $pid ?>">
            <div class="paq-icon" id="icon-<?= $pid ?>">📦</div>
            <div class="paq-info">
                <strong><?= $pid ?></strong>
                <span>👤 <?= htmlspecialchars($p['destinatario']) ?> &nbsp;·&nbsp; 🔒 Taquilla <?= htmlspecialchars($p['taquilla'] ?? 'Sin asignar') ?> &nbsp;·&nbsp; 📅 <?= $fecha ?></span>
                <?php if (!empty($p['pin_repartidor'])): ?>
                <span style="margin-top:4px;display:flex;align-items:center;gap:6px;">
                    🔑 Tu PIN de apertura: <span style="font-family:monospace;font-size:16px;font-weight:800;color:var(--accent);letter-spacing:3px;"><?= htmlspecialchars($p['pin_repartidor']) ?></span>
                </span>
                <?php endif; ?>
            </div>
            <div class="paq-actions" id="actions-<?= $pid ?>">
                <button type="button" class="btn-entregado" onclick="marcar('<?= $pid ?>','entregado')">✓ Ya entregado</button>
                <button type="button" class="btn-no-entregado" onclick="marcar('<?= $pid ?>','no_entregado')">✕ No entregado</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="empty-wrap">
        <div>📭</div>
        <p>No tienes paquetes pendientes de entregar.</p>
        <p style="margin-top:8px;font-size:13px;color:var(--muted);">Pulsa "Recibir turno" en el panel para obtener nuevos paquetes.</p>
    </div>
    <?php endif; ?>

    <!-- FASE 2: PENDIENTE POR RECOGER -->
    <?php if (!empty($en_taquilla)): ?>
    <div style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin:32px 0 16px;display:flex;align-items:center;gap:10px;color:var(--accent);">
        📬 Pendiente por recoger
        <span style="flex:1;height:1px;background:rgba(79,142,247,.2);display:block;"></span>
        <span style="font-size:12px;background:rgba(79,142,247,.08);border:1px solid rgba(79,142,247,.2);padding:3px 10px;border-radius:99px;color:var(--accent);"><?= count($en_taquilla) ?> en taquilla</span>
    </div>
    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Estos paquetes ya están en la taquilla esperando a que el cliente los recoja.</p>
    <div class="paq-list">
        <?php foreach ($en_taquilla as $p):
            $fecha = date('d/m/Y · H:i', strtotime($p['fecha_asignacion']));
            $pid   = htmlspecialchars($p['paquete_id']);
            $dias  = floor((time() - strtotime($p['fecha_asignacion'])) / 86400);
        ?>
        <div class="paq-item" style="border-color:rgba(79,142,247,.2);background:rgba(79,142,247,.03);">
            <div class="paq-icon" style="background:rgba(79,142,247,.1);">📬</div>
            <div class="paq-info">
                <strong><?= $pid ?></strong>
                <span>👤 <?= htmlspecialchars($p['destinatario']) ?> &nbsp;·&nbsp; 🔒 Taquilla <?= htmlspecialchars($p['taquilla']) ?> &nbsp;·&nbsp; 📅 <?= $fecha ?>
                <?php if ($dias > 0): ?>&nbsp;·&nbsp; <span style="color:var(--warn);"><?= $dias ?> día<?= $dias > 1 ? 's' : '' ?> esperando</span><?php endif; ?>
                </span>
            </div>
            <div>
                <span style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:99px;font-size:12px;font-weight:500;background:rgba(79,142,247,.1);border:1px solid rgba(79,142,247,.2);color:var(--accent);">⏳ Esperando cliente</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($caducados)): ?>
    <div style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;margin:32px 0 16px;display:flex;align-items:center;gap:10px;color:var(--warn);">
        ⚠ Paquetes caducados
        <span style="flex:1;height:1px;background:rgba(251,146,60,.2);display:block;"></span>
        <span style="font-size:12px;background:rgba(251,146,60,.1);border:1px solid rgba(251,146,60,.25);padding:3px 10px;border-radius:99px;"><?= count($caducados) ?> sin recoger</span>
    </div>
    <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">Estos paquetes llevan más de 5 días en la taquilla sin ser recogidos. El repartidor debe retirarlos.</p>
    <div class="paq-list">
        <?php foreach ($caducados as $p):
            $fecha = date('d/m/Y · H:i', strtotime($p['fecha_asignacion']));
            $dias  = floor((time() - strtotime($p['fecha_asignacion'])) / 86400);
            $pid   = htmlspecialchars($p['paquete_id']);
        ?>
        <div class="paq-item caducado-item" id="item-<?= $pid ?>">
            <div class="paq-icon" id="icon-<?= $pid ?>" style="background:rgba(251,146,60,.1);">⏰</div>
            <div class="paq-info">
                <strong><?= $pid ?></strong>
                <span>👤 <?= htmlspecialchars($p['destinatario']) ?> &nbsp;·&nbsp; 🔒 Taquilla <?= htmlspecialchars($p['taquilla']) ?> &nbsp;·&nbsp; 📅 <?= $fecha ?> &nbsp;·&nbsp; <span style="color:var(--warn);font-weight:600;"><?= $dias ?> días sin recoger</span></span>
                <?php if (!empty($p['pin_retirada'])): ?>
                <span style="margin-top:6px;display:flex;align-items:center;gap:6px;">
                    🔑 PIN de retirada: <span style="font-family:monospace;font-size:18px;font-weight:800;color:var(--warn);letter-spacing:3px;"><?= htmlspecialchars($p['pin_retirada']) ?></span>
                </span>
                <?php endif; ?>
            </div>
            <div class="paq-actions" id="actions-<?= $pid ?>">
                <button type="button" class="btn-entregado" onclick="retirarCaducado('<?= $pid ?>')">📤 Retirado</button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<div class="toast" id="toast"></div>

<script>
    const showToast = (msg, type) => {
        const t = document.getElementById('toast');
        t.textContent = msg; t.className = `toast ${type} show`;
        setTimeout(() => t.classList.remove('show'), 3000);
    };

    function retirarCaducado(id) {
        const fd = new FormData();
        fd.append('paquete_id', id);
        fd.append('estado', 'retirado_caducado');
        fetch('marcar_paquete.php', { method:'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    const item = document.getElementById('item-' + id);
                    document.getElementById('icon-' + id).textContent = '✅';
                    document.getElementById('actions-' + id).innerHTML = '<span class="estado-badge ok">✓ Retirado</span>';
                    showToast('📦 ' + id + ' retirado correctamente', 'success');
                    setTimeout(() => item.remove(), 1500);
                } else { showToast('Error: ' + d.error, 'error'); }
            })
            .catch(() => showToast('Error de conexión', 'error'));
    }

    let paqPendienteId = null;

    function marcar(id, estado) {
        if (estado === 'no_entregado') {
            paqPendienteId = id;
            document.getElementById('motivoTexto').value = '';
            document.getElementById('motivoError').textContent = '';
            document.getElementById('overlayMotivo').classList.add('show');
            setTimeout(() => document.getElementById('motivoTexto').focus(), 200);
            return;
        }
        // Entregado
        const btns = document.querySelectorAll(`#actions-${id} button`);
        btns.forEach(b => b.disabled = true);
        const fd = new FormData();
        fd.append('paquete_id', id);
        fd.append('estado', 'entregado');
        fetch('marcar_paquete.php', { method:'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    const item = document.getElementById('item-' + id);
                    item.classList.add('entregado');
                    document.getElementById('icon-' + id).textContent = '✅';
                    document.getElementById('actions-' + id).innerHTML = '<span class="estado-badge ok">✓ Entregado</span>';
                    showToast('📦 ' + id + ' entregado correctamente', 'success');
                    setTimeout(() => item.remove(), 1500);
                } else {
                    btns.forEach(b => b.disabled = false);
                    showToast('Error: ' + d.error, 'error');
                }
            })
            .catch(() => { btns.forEach(b => b.disabled = false); showToast('Error de conexión', 'error'); });
    }

    function cerrarMotivo() {
        document.getElementById('overlayMotivo').classList.remove('show');
        paqPendienteId = null;
    }

    function confirmarNoEntregado() {
        const motivo = document.getElementById('motivoTexto').value.trim();
        if (!motivo) { document.getElementById('motivoError').textContent = 'Por favor indica el motivo.'; return; }
        const fd = new FormData();
        fd.append('paquete_id', paqPendienteId);
        fd.append('estado', 'no_entregado');
        fd.append('motivo', motivo);
        fetch('marcar_paquete.php', { method:'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    const id = paqPendienteId;
                    cerrarMotivo();
                    const item = document.getElementById('item-' + id);
                    if (item) { item.style.transition = 'opacity .4s,transform .4s'; item.style.opacity = '0'; item.style.transform = 'translateX(20px)'; setTimeout(() => item.remove(), 400); }
                    showToast('Incidencia registrada', 'error');
                } else { document.getElementById('motivoError').textContent = d.error; }
            })
            .catch(() => { document.getElementById('motivoError').textContent = 'Error de conexión'; });
    }

    document.getElementById('overlayMotivo').addEventListener('click', e => { if (e.target === e.currentTarget) cerrarMotivo(); });
</script>

<!-- MODAL MOTIVO NO ENTREGADO -->
<div class="overlay" id="overlayMotivo">
    <div class="modal">
        <div class="modal-icon">❌</div>
        <h3>¿Por qué no se entregó?</h3>
        <p>Indica el motivo para registrar la incidencia.</p>
        <textarea id="motivoTexto" placeholder="Ej: El destinatario no estaba, taquilla llena, dirección incorrecta..."></textarea>
        <div class="modal-msg-err" id="motivoError"></div>
        <div class="modal-btns">
            <button type="button" class="btn-cancelar" onclick="cerrarMotivo()">Cancelar</button>
            <button type="button" class="btn-confirmar-no" onclick="confirmarNoEntregado()">Confirmar</button>
        </div>
    </div>
</div>
</body>
</html>
