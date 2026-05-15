<?php
session_start();
require_once 'conexion.php';
require_once 'caducar_paquetes.php';

$usuario  = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Usuario';
$rol      = isset($_SESSION['rol'])     ? $_SESSION['rol'] : 'consumidor';
$autoRetiro   = isset($_GET['paquete'])  ? htmlspecialchars($_GET['paquete'])  : '';
$autoTaquilla = isset($_GET['taquilla']) ? htmlspecialchars($_GET['taquilla']) : '';

$pendientes        = [];
$caducados         = [];
$taquillasOcupadas = [];
$paquetesPendientesRepartidor = 0;

if (isset($_SESSION['usuario'])) {
    // Paquetes pendientes del consumidor
    $stmt = $conn->prepare("SELECT paquete_id, taquilla, pin FROM paquetes WHERE usuario=? AND estado='en_taquilla' ORDER BY fecha_asignacion DESC");
    $stmt->bind_param('s', $_SESSION['usuario']);
    $stmt->execute();
    $pendientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if ($rol === 'repartidor') {
        // Caducados
        $stmt2 = $conn->prepare("SELECT paquete_id, usuario as destinatario, taquilla, fecha_asignacion FROM paquetes WHERE estado='caducado' ORDER BY fecha_asignacion ASC");
        $stmt2->execute();
        $caducados = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();

        // Taquillas ocupadas = paquetes en_taquilla
        $stmt3 = $conn->prepare("SELECT DISTINCT taquilla FROM paquetes WHERE estado='en_taquilla' AND taquilla IN ('A','B')");
        $stmt3->execute();
        $res3 = $stmt3->get_result();
        while ($row = $res3->fetch_assoc()) $taquillasOcupadas[] = $row['taquilla'];
        $stmt3->close();

        // Paquetes pendientes del repartidor (aún no entregados)
        $stmt4 = $conn->prepare("SELECT COUNT(*) as total FROM paquetes WHERE estado='pendiente'");
        $stmt4->execute();
        $paquetesPendientesRepartidor = (int)$stmt4->get_result()->fetch_assoc()['total'];
        $stmt4->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartLocker Solutions</title>
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
        .user-name { font-size:14px; font-weight:500; color:var(--text); }
        .user-role { font-size:11px; color:var(--muted); }
        .btn-logout { display:flex; align-items:center; gap:7px; padding:8px 14px; border-radius:9px; background:rgba(248,113,113,0.1); border:1px solid rgba(248,113,113,0.2); color:var(--err); font-size:13px; font-weight:500; text-decoration:none; transition:background .2s; }
        .btn-logout:hover { background:rgba(248,113,113,0.18); }
        .btn-logout svg { width:15px; height:15px; }

        main { max-width:900px; margin:0 auto; padding:40px 32px 60px; position:relative; z-index:1; }
        @keyframes up { from { opacity:0; transform:translateY(16px); } }

        .welcome { margin-bottom:32px; animation:up .5s cubic-bezier(.16,1,.3,1) both; }
        .welcome-label { font-size:13px; color:var(--muted); margin-bottom:6px; }
        .welcome h1 { font-family:'Syne',sans-serif; font-size:26px; font-weight:800; letter-spacing:-.5px; }
        .welcome h1 span { background:linear-gradient(90deg,var(--accent),var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }

        .cards { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:32px; }
        .card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:22px; text-decoration:none; color:inherit; display:flex; flex-direction:column; gap:10px; transition:border-color .2s,transform .2s,box-shadow .2s; animation:up .5s cubic-bezier(.16,1,.3,1) both; }
        .card:nth-child(1){animation-delay:.05s} .card:nth-child(2){animation-delay:.10s} .card:nth-child(3){animation-delay:.15s}
        .card:hover { border-color:rgba(79,142,247,.3); transform:translateY(-2px); box-shadow:0 8px 32px rgba(0,0,0,.3); }
        .card-icon { width:42px; height:42px; border-radius:11px; display:grid; place-items:center; font-size:19px; }
        .card-icon.blue   { background:rgba(79,142,247,.12); }
        .card-icon.green  { background:rgba(34,211,165,.12); }
        .card-icon.orange { background:rgba(251,146,60,.12); }
        .card h3 { font-family:'Syne',sans-serif; font-size:15px; font-weight:700; }
        .card p  { font-size:13px; color:var(--muted); line-height:1.5; flex:1; }
        .card-link { font-size:12px; color:var(--accent); font-weight:500; }

        .section-title { font-family:'Syne',sans-serif; font-size:15px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:10px; }
        .section-title::after { content:''; flex:1; height:1px; background:var(--border); }

        /* BANDEJA */
        .bandeja { background:rgba(79,142,247,.06); border:1px solid rgba(79,142,247,.2); border-radius:14px; padding:16px 20px; margin-bottom:28px; animation:up .5s cubic-bezier(.16,1,.3,1) both; }
        .bandeja-header { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
        .bandeja-header svg { width:18px; height:18px; color:var(--accent); flex-shrink:0; }
        .bandeja-header span { font-family:'Syne',sans-serif; font-size:14px; font-weight:700; color:var(--accent); }
        .bandeja-header .badge { margin-left:auto; background:var(--accent); color:#fff; font-size:11px; font-weight:700; padding:2px 8px; border-radius:99px; }
        .bandeja-item { display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:10px; background:rgba(255,255,255,.03); border:1px solid var(--border); margin-bottom:8px; }
        .bandeja-item:last-child { margin-bottom:0; }
        .bandeja-paq { flex:1; }
        .bandeja-paq strong { font-size:14px; font-weight:600; color:var(--text); display:block; }
        .bandeja-paq span   { font-size:12px; color:var(--muted); }
        .btn-taq { padding:7px 14px; border-radius:8px; border:1px solid var(--border); background:rgba(255,255,255,.04); color:var(--muted); font-family:'Syne',sans-serif; font-size:12px; font-weight:700; cursor:pointer; transition:all .2s; }
        .btn-taq:hover { border-color:rgba(79,142,247,.4); color:var(--text); background:rgba(79,142,247,.08); }
        .btn-taq.a { border-color:rgba(79,142,247,.3); color:var(--accent); background:rgba(79,142,247,.08); }
        .btn-taq.b { border-color:rgba(124,92,252,.3); color:var(--accent2); background:rgba(124,92,252,.08); }

        /* RETIRAR BOX */
        .retirar-box { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:32px; display:flex; flex-direction:column; align-items:center; text-align:center; gap:16px; animation:up .5s .2s cubic-bezier(.16,1,.3,1) both; margin-bottom:24px; }
        .retirar-icon { width:64px; height:64px; border-radius:18px; background:linear-gradient(135deg,rgba(79,142,247,.15),rgba(124,92,252,.15)); display:grid; place-items:center; font-size:28px; border:1px solid rgba(79,142,247,.2); }
        .retirar-box h2 { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; }
        .retirar-box p  { font-size:14px; color:var(--muted); max-width:380px; line-height:1.6; }
        .btn-retirar { padding:14px 36px; border-radius:12px; border:none; cursor:pointer; background:linear-gradient(135deg,var(--accent),var(--accent2)); color:#fff; font-family:'Syne',sans-serif; font-size:15px; font-weight:700; box-shadow:0 4px 24px var(--glow); transition:transform .15s,box-shadow .2s,opacity .2s; display:flex; align-items:center; gap:8px; }
        .btn-retirar:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 6px 32px rgba(79,142,247,.45); }
        .btn-retirar svg { width:18px; height:18px; }

        /* TAQUILLA STATUS */
        .taquillas-status { display:flex; gap:12px; width:100%; max-width:340px; }
        .taq-card { flex:1; padding:12px 16px; border-radius:10px; border:1px solid; text-align:center; }
        .taq-card.libre   { border-color:rgba(34,211,165,.3); background:rgba(34,211,165,.06); }
        .taq-card.ocupada { border-color:rgba(248,113,113,.3); background:rgba(248,113,113,.06); }
        .taq-card .taq-emoji { font-size:20px; margin-bottom:4px; }
        .taq-card .taq-name { font-family:'Syne',sans-serif; font-weight:700; font-size:14px; }
        .taq-card.libre   .taq-name { color:var(--ok); }
        .taq-card.ocupada .taq-name { color:var(--err); }
        .taq-card .taq-estado { font-size:11px; color:var(--muted); margin-top:2px; }

        /* REPARTIDOR ASIGNAR */
        .input-wrap { position:relative; width:100%; max-width:340px; }
        .input-wrap svg { position:absolute; left:14px; top:50%; transform:translateY(-50%); width:18px; height:18px; color:var(--muted); pointer-events:none; }
        .input-wrap input { width:100%; padding:12px 14px 12px 42px; background:rgba(255,255,255,.04); border:1px solid var(--border); border-radius:10px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:15px; outline:none; transition:border-color .2s,box-shadow .2s; }
        .input-wrap input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--glow); }
        .input-wrap input::placeholder { color:#3d4450; }
        .btn-taquilla { padding:10px 28px; border-radius:10px; border:1px solid var(--border); background:rgba(255,255,255,.04); color:var(--muted); font-family:'Syne',sans-serif; font-size:14px; font-weight:700; cursor:pointer; transition:all .2s; }
        .btn-taquilla:hover:not(:disabled) { border-color:rgba(79,142,247,.4); color:var(--text); }
        .btn-taquilla.selected { background:rgba(79,142,247,.12); border-color:var(--accent); color:var(--accent); box-shadow:0 0 0 3px var(--glow); }
        .btn-taquilla:disabled { opacity:.35; cursor:not-allowed; }

        /* CADUCADOS BANNER */
        .caducados-banner { background:rgba(248,113,113,.06); border:1px solid rgba(248,113,113,.25); border-radius:14px; padding:18px 22px; margin-top:24px; display:flex; align-items:center; justify-content:space-between; }
        .caducados-banner-left { display:flex; align-items:center; gap:14px; }
        .caducados-icon { width:46px; height:46px; border-radius:12px; background:rgba(248,113,113,.12); display:grid; place-items:center; font-size:22px; flex-shrink:0; }
        .caducados-title { font-family:'Syne',sans-serif; font-size:15px; font-weight:700; color:var(--err); margin-bottom:4px; }
        .caducados-sub   { font-size:12px; color:var(--muted); }
        .caducados-count { font-family:'Syne',sans-serif; font-size:36px; font-weight:800; color:var(--err); opacity:.6; }

        /* PIN MODAL */
        .overlay { position:fixed; inset:0; background:rgba(0,0,0,.7); backdrop-filter:blur(8px); z-index:200; display:none; place-items:center; }
        .overlay.show { display:grid; }
        .modal { background:var(--surface); border:1px solid var(--border); border-radius:20px; padding:40px 36px; width:360px; text-align:center; box-shadow:0 40px 80px rgba(0,0,0,.6); animation:up .4s cubic-bezier(.16,1,.3,1) both; position:relative; }
        .modal-close { position:absolute; top:16px; right:16px; background:none; border:none; color:var(--muted); cursor:pointer; font-size:20px; line-height:1; transition:color .2s; }
        .modal-close:hover { color:var(--text); }
        .modal-icon { width:60px; height:60px; border-radius:16px; background:linear-gradient(135deg,var(--accent),var(--accent2)); display:grid; place-items:center; font-size:26px; margin:0 auto 16px; box-shadow:0 4px 24px var(--glow); }
        .modal h3 { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; margin-bottom:6px; }
        .modal .modal-paq { font-size:12px; color:var(--accent); font-weight:500; margin-bottom:16px; }
        .modal p  { font-size:13px; color:var(--muted); margin-bottom:20px; }
        .pin-display { display:flex; gap:10px; justify-content:center; margin-bottom:10px; }
        .pin-digit { width:52px; height:60px; border-radius:12px; background:rgba(79,142,247,.08); border:1px solid rgba(79,142,247,.25); display:grid; place-items:center; font-family:'Syne',sans-serif; font-size:26px; font-weight:800; color:var(--accent); animation:popIn .4s cubic-bezier(.16,1,.3,1) both; }
        .pin-digit:nth-child(1){animation-delay:.05s} .pin-digit:nth-child(2){animation-delay:.10s} .pin-digit:nth-child(3){animation-delay:.15s} .pin-digit:nth-child(4){animation-delay:.20s}
        @keyframes popIn { from { opacity:0; transform:scale(.7); } }
        .pin-note { font-size:12px; color:var(--muted); margin-bottom:20px; }
        .pin-note span { color:var(--ok); font-weight:500; }
        .check-recogida { display:flex; align-items:center; gap:10px; margin-top:4px; cursor:pointer; padding:12px 14px; border-radius:11px; border:1px solid var(--border); background:rgba(255,255,255,.02); font-size:13px; color:var(--muted); transition:border-color .2s,background .2s; user-select:none; }
        .check-recogida:hover { border-color:rgba(34,211,165,.3); background:rgba(34,211,165,.04); }
        .check-recogida input { display:none; }
        .check-box { width:20px; height:20px; border-radius:6px; flex-shrink:0; border:1.5px solid #4b5563; display:grid; place-items:center; transition:background .2s,border-color .2s; }
        .check-box svg { opacity:0; transition:opacity .15s; color:#fff; }
        .check-recogida.checked { border-color:rgba(34,211,165,.4); background:rgba(34,211,165,.06); color:var(--ok); }
        .check-recogida.checked .check-box { background:var(--ok); border-color:var(--ok); }
        .check-recogida.checked .check-box svg { opacity:1; }
        .modal-success { display:none; text-align:center; }
        .modal-success.show { display:block; }
        .modal-main.hide { display:none; }
        .success-icon { width:72px; height:72px; border-radius:50%; margin:0 auto 16px; background:rgba(34,211,165,.12); border:2px solid rgba(34,211,165,.3); display:grid; place-items:center; font-size:28px; animation:popIn .5s cubic-bezier(.16,1,.3,1) both; }
        .modal-success h3 { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; color:var(--ok); margin-bottom:8px; }
        .modal-success p { font-size:13px; color:var(--muted); margin-bottom:16px; }
        .success-detail { font-size:12px; color:#4b5563; background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:10px; padding:12px 16px; margin-bottom:20px; text-align:left; line-height:2; }
        .success-detail span { color:var(--text); font-weight:500; }
        .btn-cerrar-ok { width:100%; padding:13px; border-radius:11px; border:none; cursor:pointer; background:linear-gradient(135deg,var(--accent),var(--accent2)); color:#fff; font-family:'Syne',sans-serif; font-size:14px; font-weight:700; transition:opacity .2s; }
        .btn-cerrar-ok:hover { opacity:.85; }

        /* TURNO MODAL */
        .overlay-turno { position:fixed; inset:0; background:rgba(0,0,0,.75); backdrop-filter:blur(8px); z-index:300; display:none; place-items:center; }
        .overlay-turno.show { display:grid; }
        .modal-turno { background:var(--surface); border:1px solid var(--border); border-radius:20px; padding:36px; width:460px; max-width:95vw; max-height:85vh; overflow-y:auto; box-shadow:0 40px 80px rgba(0,0,0,.6); animation:up .4s cubic-bezier(.16,1,.3,1) both; position:relative; scrollbar-width:thin; scrollbar-color:rgba(79,142,247,.3) transparent; }
        .modal-turno::-webkit-scrollbar { width:4px; }
        .modal-turno::-webkit-scrollbar-thumb { background:rgba(79,142,247,.3); border-radius:99px; }
        .turno-header { text-align:center; margin-bottom:24px; }
        .turno-header .turno-icon { font-size:32px; margin-bottom:10px; }
        .turno-header h3 { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; margin-bottom:4px; }
        .turno-header p  { font-size:13px; color:var(--muted); }
        .turno-paq { background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:12px; padding:16px; margin-bottom:12px; }
        .turno-paq:last-of-type { margin-bottom:20px; }
        .turno-paq-top { display:flex; align-items:center; gap:10px; margin-bottom:12px; }
        .turno-paq-icon { width:36px; height:36px; border-radius:9px; background:rgba(79,142,247,.1); display:grid; place-items:center; font-size:16px; flex-shrink:0; }
        .turno-paq-info strong { font-family:'Syne',sans-serif; font-size:14px; font-weight:700; color:var(--text); display:block; }
        .turno-paq-info span   { font-size:12px; color:var(--muted); }
        .turno-taq-btns { display:flex; gap:8px; }
        .turno-btn-taq { flex:1; padding:9px; border-radius:9px; border:1px solid var(--border); background:rgba(255,255,255,.04); color:var(--muted); font-family:'Syne',sans-serif; font-size:13px; font-weight:700; cursor:pointer; transition:all .2s; }
        .turno-btn-taq:hover { border-color:rgba(79,142,247,.4); color:var(--text); }
        .turno-btn-taq.sel-a { background:rgba(79,142,247,.12); border-color:var(--accent); color:var(--accent); }
        .turno-btn-taq.sel-b { background:rgba(124,92,252,.12); border-color:var(--accent2); color:var(--accent2); }
        .turno-btn-taq:disabled { opacity:.3; cursor:not-allowed; }
        .turno-badge { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:99px; font-size:12px; font-weight:500; background:rgba(34,211,165,.1); border:1px solid rgba(34,211,165,.2); color:var(--ok); }
        .btn-confirmar-turno { width:100%; padding:13px; border-radius:11px; border:none; cursor:pointer; background:linear-gradient(135deg,var(--accent),var(--accent2)); color:#fff; font-family:'Syne',sans-serif; font-size:14px; font-weight:700; transition:opacity .2s; }
        .btn-confirmar-turno:hover { opacity:.88; }
        .btn-confirmar-turno:disabled { opacity:.4; cursor:not-allowed; }

        /* TOAST */
        .toast { position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(20px); background:rgba(30,33,40,.98); border:1px solid var(--border); border-radius:10px; padding:12px 20px; font-size:14px; color:var(--text); opacity:0; pointer-events:none; transition:opacity .3s,transform .3s; backdrop-filter:blur(12px); z-index:100; white-space:nowrap; }
        .toast.show    { opacity:1; transform:translateX(-50%) translateY(0); }
        .toast.success { border-color:rgba(34,211,165,.3); }
        .toast.error   { border-color:rgba(248,113,113,.3); }
    </style>
</head>
<body>
<div class="grid-bg"></div>

<header>
    <a class="brand" href="#">
        <div class="brand-icon">🔐</div>
        <div>
            <div class="brand-text">SmartLocker</div>
            <div class="brand-sub">Solutions</div>
        </div>
    </a>
    <div class="header-right">
        <div class="user-info">
            <div class="user-name"><?= $usuario ?></div>
            <div class="user-role"><?= ucfirst($rol) ?></div>
        </div>
        <div class="avatar"><?= strtoupper(substr($usuario, 0, 1)) ?></div>
        <a class="btn-logout" href="logout.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Salir
        </a>
    </div>
</header>

<main>
    <div class="welcome">
        <div class="welcome-label">Panel de control</div>
        <h1>Hola, <span><?= $usuario ?></span> 👋</h1>
    </div>

    <?php if ($rol === 'consumidor'): ?>
    <!-- ══════════ CONSUMIDOR ══════════ -->
    <div class="cards">
        <a class="card" href="mis_paquetes.php">
            <div class="card-icon blue">📦</div>
            <h3>Mis paquetes</h3>
            <p>Consulta tus paquetes pendientes de recoger.</p>
            <div class="card-link">Ver paquetes →</div>
        </a>
        <a class="card" href="historial.php">
            <div class="card-icon green">🕓</div>
            <h3>Historial</h3>
            <p>Revisa el registro de tus recogidas anteriores.</p>
            <div class="card-link">Ver historial →</div>
        </a>
        <a class="card" href="chat.php">
            <div class="card-icon orange">🎧</div>
            <h3>Soporte</h3>
            <p>¿Tienes un problema? Estamos aquí para ayudarte.</p>
            <div class="card-link">Contactar →</div>
        </a>
    </div>

    <div class="section-title">Tus paquetes pendientes</div>
    <?php if (!empty($pendientes)): ?>
    <div class="bandeja">
        <div class="bandeja-header">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <span>Paquetes listos para recoger</span>
            <div class="badge"><?= count($pendientes) ?></div>
        </div>
        <?php foreach ($pendientes as $p): ?>
        <div class="bandeja-item">
            <div style="font-size:22px">📦</div>
            <div class="bandeja-paq">
                <strong><?= htmlspecialchars($p['paquete_id']) ?></strong>
                <span>Taquilla <?= htmlspecialchars($p['taquilla']) ?> · Pendiente de recoger</span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <a href="mis_paquetes.php" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;font-size:13px;color:var(--accent);text-decoration:none;font-weight:500;">Ver mis paquetes →</a>
    </div>
    <?php else: ?>
    <div class="bandeja" style="text-align:center;padding:32px 20px;">
        <div style="font-size:36px;margin-bottom:10px;">📭</div>
        <div style="font-family:'Syne',sans-serif;font-size:15px;font-weight:700;color:var(--muted);">No tienes paquetes pendientes</div>
        <div style="font-size:13px;color:var(--muted);margin-top:4px;">Cuando llegue uno aparecerá aquí</div>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <!-- ══════════ REPARTIDOR ══════════ -->
    <div class="cards">
        <a class="card" href="paquetes_asignados.php">
            <div class="card-icon blue">📋</div>
            <h3>Paquetes asignados</h3>
            <p>Consulta los paquetes pendientes de entregar.</p>
            <div class="card-link">Ver paquetes →</div>
        </a>
        <a class="card" href="historial.php">
            <div class="card-icon green">🕓</div>
            <h3>Historial</h3>
            <p>Revisa el registro de todas las entregas realizadas.</p>
            <div class="card-link">Ver historial →</div>
        </a>
        <a class="card" href="chat.php">
            <div class="card-icon orange">🎧</div>
            <h3>Soporte</h3>
            <p>¿Tienes un problema? Estamos aquí para ayudarte.</p>
            <div class="card-link">Contactar →</div>
        </a>
    </div>

    <div class="section-title">Turno de reparto</div>
    <div class="retirar-box">
        <div class="retirar-icon">🚛</div>
        <h2>Recibir paquetes del turno</h2>
        <p>Se asignará un paquete por cada taquilla libre (máximo 2). Las ocupadas no recibirán hasta que se recojan.</p>

        <div class="taquillas-status">
            <?php foreach(['A','B'] as $taq):
                $ocupada = in_array($taq, $taquillasOcupadas);
            ?>
            <div class="taq-card <?= $ocupada ? 'ocupada' : 'libre' ?>">
                <div class="taq-emoji"><?= $ocupada ? '🔒' : '✅' ?></div>
                <div class="taq-name">Taquilla <?= $taq ?></div>
                <div class="taq-estado"><?= $ocupada ? 'Ocupada' : 'Libre' ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php
        $todasOcupadas    = count($taquillasOcupadas) >= 2;
        $limitePendientes = $paquetesPendientesRepartidor >= 2;
        $btnBloqueado     = $todasOcupadas || $limitePendientes;
        ?>
        <button type="button" class="btn-retirar" id="btnTurno" onclick="recibirTurno()"
            <?= $btnBloqueado ? 'disabled style="opacity:.4;cursor:not-allowed;"' : '' ?>>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
            <?= $btnBloqueado ? 'No disponible' : 'Recibir turno' ?>
        </button>
        <?php if ($todasOcupadas): ?>
        <div style="font-size:13px;color:var(--err);background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);border-radius:10px;padding:12px 16px;max-width:340px;line-height:1.6;">
            🔒 Las dos taquillas están llenas. No puedes recibir más paquetes hasta que los consumidores recojan los actuales.
        </div>
        <?php elseif ($limitePendientes): ?>
        <div style="font-size:13px;color:var(--warn);background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);border-radius:10px;padding:12px 16px;max-width:340px;line-height:1.6;">
            ⚠ Ya tienes 2 paquetes pendientes de entregar. Márcalos como entregados en <a href="paquetes_asignados.php" style="color:var(--accent);">Paquetes asignados</a> antes de recibir más.
        </div>
        <?php endif; ?>
    </div>

    <div class="section-title" style="margin-top:28px;">Asignar paquete manualmente</div>
    <div class="retirar-box">
        <div class="retirar-icon">📬</div>
        <h2>Entregar a un usuario</h2>
        <p>Introduce el usuario destinatario y selecciona la taquilla. Después introduce el PIN en el teclado de la taquilla para abrirla.</p>

        <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input type="text" id="inputDestinatario" placeholder="Nombre de usuario destinatario" oninput="onDestinatarioInput()">
        </div>

        <div id="msg-destinatario" style="font-size:12px;min-height:16px;"></div>

        <div style="display:flex;gap:12px;">
            <?php foreach(['A','B'] as $taq):
                $ocupada = in_array($taq, $taquillasOcupadas);
            ?>
            <button type="button" class="btn-taquilla" id="manualBtn<?= $taq ?>"
                onclick="seleccionarTaquilla('<?= $taq ?>')"
                <?= $ocupada ? 'disabled title="Taquilla ' . $taq . ' ocupada"' : '' ?>>
                <?= $ocupada ? '🔒' : '✅' ?> Taquilla <?= $taq ?>
            </button>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn-retirar" id="btnGenerar" onclick="generarPaquete()" disabled style="opacity:.4;cursor:not-allowed;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
            Asignar paquete
        </button>
    </div>

    <?php if (!empty($caducados)): ?>
    <div class="caducados-banner">
        <div class="caducados-banner-left">
            <div class="caducados-icon">⏰</div>
            <div>
                <div class="caducados-title"><?= count($caducados) ?> paquete<?= count($caducados) > 1 ? 's' : '' ?> caducado<?= count($caducados) > 1 ? 's' : '' ?></div>
                <div class="caducados-sub">Llevan más de 5 días sin ser recogidos. Debes retirarlos.</div>
            </div>
        </div>
        <a href="paquetes_asignados.php" style="padding:10px 18px;border-radius:10px;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);color:var(--err);font-family:'Syne',sans-serif;font-size:13px;font-weight:700;text-decoration:none;">Ver caducados →</a>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</main>

<!-- PIN MODAL -->
<div class="overlay" id="overlay">
    <div class="modal">
        <button class="modal-close" onclick="cerrarModal()">✕</button>
        <div class="modal-main" id="modalMain">
            <div class="modal-icon">🔑</div>
            <h3>PIN de retirada</h3>
            <div class="modal-paq" id="modalPaqLabel"></div>
            <p>Introduce este código en el teclado de la taquilla</p>
            <div class="pin-display" id="pinDisplay"></div>
            <div class="pin-note">Válido durante <span id="countdown">10:00</span></div>
            <label class="check-recogida" id="checkLabel">
                <input type="checkbox" id="checkRecogida" onchange="onCheck(this)">
                <span class="check-box"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" width="13" height="13"><polyline points="20 6 9 17 4 12"/></svg></span>
                He recogido mi paquete correctamente
            </label>
        </div>
        <div class="modal-success" id="modalSuccess">
            <div class="success-icon">✅</div>
            <h3>¡Recogida confirmada!</h3>
            <p>Tu paquete ha sido marcado como recogido.</p>
            <div class="success-detail">
                <div>📦 Paquete: <span id="sPaq"></span></div>
                <div>🔒 Taquilla: <span id="sTaq"></span></div>
                <div>🔑 PIN: <span id="sPin"></span></div>
                <div>🕐 Hora: <span id="sHora"></span></div>
            </div>
            <button class="btn-cerrar-ok" onclick="cerrarYRecargar()">Aceptar</button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<!-- MODAL TURNO -->
<div class="overlay-turno" id="overlayTurno">
    <div class="modal-turno" id="modalTurno">
        <div class="turno-header">
            <div class="turno-icon">🚛</div>
            <h3>Paquetes del turno</h3>
            <p>Selecciona la taquilla para cada paquete y confirma la asignación.</p>
        </div>
        <div id="turnoPaquetes"></div>
        <button type="button" class="btn-confirmar-turno" id="btnConfirmarTurno" onclick="confirmarTurno()" disabled>
            Confirmar asignación
        </button>
    </div>
</div>

<script>
    let pinActual = '', paqueteActual = '', taquillaActual = '';
    let timerInterval = null;

    const urlPaquete  = <?= json_encode($autoRetiro) ?>;
    const urlTaquilla = <?= json_encode($autoTaquilla) ?>;

    function abrirPin(paquete, taquilla, pin) {
        paqueteActual  = paquete;
        taquillaActual = taquilla;
        pinActual      = pin;

        document.getElementById('modalPaqLabel').textContent = paquete + ' · Taquilla ' + taquilla;
        document.getElementById('pinDisplay').innerHTML = pin.split('').map(d => `<div class="pin-digit">${d}</div>`).join('');

        const cb = document.getElementById('checkRecogida');
        cb.checked = false;
        document.getElementById('checkLabel').classList.remove('checked');
        document.getElementById('modalMain').classList.remove('hide');
        document.getElementById('modalSuccess').classList.remove('show');

        clearInterval(timerInterval);
        let seg = 600;
        tick(seg);
        timerInterval = setInterval(() => { seg--; tick(seg); if (seg <= 0) { clearInterval(timerInterval); cerrarModal(); } }, 1000);

        document.getElementById('overlay').classList.add('show');
    }

    function tick(s) {
        const m = String(Math.floor(s/60)).padStart(2,'0');
        const ss = String(s%60).padStart(2,'0');
        document.getElementById('countdown').textContent = m+':'+ss;
    }

    function onCheck(cb) {
        document.getElementById('checkLabel').classList.toggle('checked', cb.checked);
        if (cb.checked) setTimeout(confirmar, 600);
    }

    function confirmar() {
        clearInterval(timerInterval);
        const fd = new FormData();
        fd.append('paquete_id', paqueteActual);
        fd.append('pin',        pinActual);
        fd.append('taquilla',   taquillaActual);
        fetch('guardar_recogida.php', { method:'POST', body: fd })
            .then(r => r.json())
            .then(d => { if (!d.ok) console.error('Error BD:', d.error); })
            .catch(e => console.error(e));

        document.getElementById('sPaq').textContent  = paqueteActual;
        document.getElementById('sTaq').textContent  = taquillaActual;
        document.getElementById('sPin').textContent  = pinActual;
        document.getElementById('sHora').textContent = new Date().toLocaleTimeString('es-ES',{hour:'2-digit',minute:'2-digit'});
        document.getElementById('modalMain').classList.add('hide');
        document.getElementById('modalSuccess').classList.add('show');
    }

    function cerrarModal() {
        document.getElementById('overlay').classList.remove('show');
        clearInterval(timerInterval);
        setTimeout(() => {
            document.getElementById('modalMain').classList.remove('hide');
            document.getElementById('modalSuccess').classList.remove('show');
            document.getElementById('checkRecogida').checked = false;
            document.getElementById('checkLabel').classList.remove('checked');
        }, 300);
    }

    function cerrarYRecargar() {
        cerrarModal();
        setTimeout(() => location.reload(), 300);
    }

    // Abrir desde bandeja o URL
    function abrirDesdeBandeja(paquete, taquilla, pin) { abrirPin(paquete, taquilla, pin); }
    if (urlPaquete && urlTaquilla) window.addEventListener('load', () => abrirPin(urlPaquete, urlTaquilla, '????'));

    document.getElementById('overlay').addEventListener('click', e => { if (e.target === e.currentTarget) cerrarModal(); });

    // Toast
    const showToast = (msg, type) => {
        const t = document.getElementById('toast');
        t.textContent = msg; t.className = `toast ${type} show`;
        setTimeout(() => t.classList.remove('show'), 3000);
    };

    // ── ASIGNACIÓN MANUAL ──
    let taquillaSeleccionada = '';

    function onDestinatarioInput() {
        actualizarBtnGenerar();
        document.getElementById('msg-destinatario').textContent = '';
        document.getElementById('msg-destinatario').style.color = '';
    }

    function seleccionarTaquilla(t) {
        taquillaSeleccionada = t;
        ['A','B'].forEach(x => {
            const b = document.getElementById('manualBtn' + x);
            if (b && !b.disabled) b.className = 'btn-taquilla' + (x === t ? ' selected' : '');
        });
        actualizarBtnGenerar();
    }

    function actualizarBtnGenerar() {
        const dest = document.getElementById('inputDestinatario')?.value.trim();
        const pinR = document.getElementById('inputPinRepartidor')?.value.trim();
        const btn  = document.getElementById('btnGenerar');
        const ok   = taquillaSeleccionada && dest && pinR?.length === 4;
        btn.disabled      = !ok;
        btn.style.opacity = ok ? '1' : '.4';
        btn.style.cursor  = ok ? 'pointer' : 'not-allowed';
    }

    function generarPaquete() {
        const dest = document.getElementById('inputDestinatario').value.trim();
        const pinR = document.getElementById('inputPinRepartidor').value.trim();
        const btn  = document.getElementById('btnGenerar');

        btn.disabled = true; btn.style.opacity = '.5';

        const fd = new FormData();
        fd.append('taquilla',       taquillaSeleccionada);
        fd.append('destinatario',   dest);
        fd.append('pin_repartidor', pinR);

        fetch('generar.php', { method:'POST', body: fd })
            .then(r => r.json())
            .then(d => {
                if (d.ok) {
                    const msg = document.getElementById('msg-destinatario');
                    msg.textContent = '✓ Paquete asignado a "' + d.destinatario + '" · Taquilla ' + d.taquilla;
                    msg.style.color = 'var(--ok)';
                    taquillaSeleccionada = '';
                    document.getElementById('inputDestinatario').value = '';
                    document.getElementById('inputPinRepartidor').value = '';
                    ['A','B'].forEach(x => { const b = document.getElementById('manualBtn' + x); if (b) b.className = 'btn-taquilla'; });
                    setTimeout(() => location.reload(), 1800);
                } else {
                    const msg = document.getElementById('msg-destinatario');
                    msg.textContent = d.error;
                    msg.style.color = 'var(--err)';
                }
                btn.disabled = false; btn.style.opacity = '1';
                actualizarBtnGenerar();
            })
            .catch(() => { showToast('Error de conexión', 'error'); btn.disabled = false; btn.style.opacity = '1'; });
    }
    let turnoData = [];
    const turnoSeleccion = {};
    const taquillasOcupadas = <?= json_encode(array_values($taquillasOcupadas)) ?>;

    function recibirTurno() {
        const btn = document.getElementById('btnTurno');
        btn.disabled = true; btn.style.opacity = '.5';
        btn.innerHTML = '⏳ Recibiendo...';

        fetch('recibir_turno.php', { method:'POST' })
            .then(r => r.json())
            .then(d => {
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg> Recibir turno';
                if (d.ok && d.asignados && d.asignados.length > 0) {
                    turnoData = d.asignados;
                    abrirTurnoModal(d.asignados);
                } else {
                    btn.disabled = false; btn.style.opacity = '1';
                    showToast('⚠ ' + (d.error || 'No hay taquillas libres'), 'error');
                }
            })
            .catch(() => {
                btn.disabled = false; btn.style.opacity = '1';
                btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg> Recibir turno';
                showToast('Error de conexión', 'error');
            });
    }

    function abrirTurnoModal(paquetes) {
        const container = document.getElementById('turnoPaquetes');
        container.innerHTML = paquetes.map((p, i) => {
            const aOcupada = taquillasOcupadas.includes('A');
            const bOcupada = taquillasOcupadas.includes('B');
            return `
            <div class="turno-paq" id="turno-paq-${i}">
                <div class="turno-paq-top">
                    <div class="turno-paq-icon">📦</div>
                    <div class="turno-paq-info">
                        <strong>${p.paquete_id}</strong>
                        <span>👤 ${p.destinatario}</span>
                    </div>
                    <div style="margin-left:auto;text-align:right;">
                        <div style="font-size:11px;color:var(--muted);margin-bottom:2px;">Tu PIN de apertura</div>
                        <div style="font-family:monospace;font-size:20px;font-weight:800;color:var(--accent);letter-spacing:4px;">${p.pin_repartidor}</div>
                    </div>
                </div>
                <div class="turno-taq-btns" id="turno-btns-${i}">
                    <button type="button" class="turno-btn-taq" id="turno-a-${i}"
                        onclick="selTurnoTaq(${i}, 'A', '${p.paquete_id}')"
                        ${aOcupada ? 'disabled title="Taquilla A ocupada"' : ''}>
                        ${aOcupada ? '🔒' : '✅'} Taquilla A
                    </button>
                    <button type="button" class="turno-btn-taq" id="turno-b-${i}"
                        onclick="selTurnoTaq(${i}, 'B', '${p.paquete_id}')"
                        ${bOcupada ? 'disabled title="Taquilla B ocupada"' : ''}>
                        ${bOcupada ? '🔒' : '✅'} Taquilla B
                    </button>
                </div>
            </div>`;
        }).join('');

        document.getElementById('btnConfirmarTurno').disabled = true;
        document.getElementById('overlayTurno').classList.add('show');
    }

    function selTurnoTaq(idx, taq, paqId) {
        // Si ya estaba seleccionada otra taquilla para este paquete, liberarla
        const prev = turnoSeleccion[paqId];
        turnoSeleccion[paqId] = taq;

        document.getElementById('turno-a-' + idx).className = 'turno-btn-taq' + (taq === 'A' ? ' sel-a' : '');
        document.getElementById('turno-b-' + idx).className = 'turno-btn-taq' + (taq === 'B' ? ' sel-b' : '');

        // Bloquear la taquilla elegida en los demás paquetes del turno
        turnoData.forEach((p, i) => {
            if (i === idx) return;
            const btnA = document.getElementById('turno-a-' + i);
            const btnB = document.getElementById('turno-b-' + i);
            if (btnA) btnA.disabled = taq === 'A' || taquillasOcupadas.includes('A');
            if (btnB) btnB.disabled = taq === 'B' || taquillasOcupadas.includes('B');
        });

        const todosSeleccionados = turnoData.every(p => turnoSeleccion[p.paquete_id]);
        document.getElementById('btnConfirmarTurno').disabled = !todosSeleccionados;
    }

    function confirmarTurno() {
        const btn = document.getElementById('btnConfirmarTurno');
        btn.disabled = true; btn.textContent = '⏳ Guardando...';

        const promises = turnoData.map(p => {
            const fd = new FormData();
            fd.append('paquete_id', p.paquete_id);
            fd.append('taquilla',   turnoSeleccion[p.paquete_id]);
            return fetch('asignar_taquilla.php', { method:'POST', body: fd }).then(r => r.json());
        });

        Promise.all(promises).then(resultados => {
            const errores = resultados.filter(r => !r.ok);
            if (errores.length === 0) {
                document.getElementById('overlayTurno').classList.remove('show');
                showToast('✅ Paquetes listos en paquetes asignados', 'success');
                setTimeout(() => location.href = 'paquetes_asignados.php', 1200);
            } else {
                btn.disabled = false; btn.textContent = 'Confirmar asignación';
                showToast('Error al asignar algunas taquillas', 'error');
            }
        }).catch(() => {
            btn.disabled = false; btn.textContent = 'Confirmar asignación';
            showToast('Error de conexión', 'error');
        });
    }
</script>
</body>
</html>
