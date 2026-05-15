<?php
session_start();
require_once 'conexion.php';

$usuario = isset($_SESSION['usuario']) ? htmlspecialchars($_SESSION['usuario']) : 'Usuario';

$stmt = $conn->prepare("SELECT paquete_id, taquilla, pin, fecha_asignacion FROM paquetes WHERE usuario=? AND estado='en_taquilla' ORDER BY fecha_asignacion DESC");
$stmt->bind_param('s', $_SESSION['usuario']);
$stmt->execute();
$paquetes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis paquetes — SmartLocker</title>
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
        .pill.orange { background:rgba(251,146,60,.08); border-color:rgba(251,146,60,.2); color:var(--warn); }
        .pill.muted  { background:rgba(255,255,255,.03); border-color:var(--border); color:var(--muted); }

        .paquetes-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px,1fr)); gap:16px; animation:up .5s .1s cubic-bezier(.16,1,.3,1) both; }

        .paquete-card { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:22px; display:flex; flex-direction:column; gap:14px; transition:border-color .2s,transform .2s,box-shadow .2s; }
        .paquete-card:hover { border-color:rgba(251,146,60,.3); transform:translateY(-2px); box-shadow:0 8px 32px rgba(0,0,0,.3); }

        .card-top { display:flex; align-items:center; justify-content:space-between; }
        .paq-icon { width:42px; height:42px; border-radius:11px; background:rgba(251,146,60,.1); display:grid; place-items:center; font-size:20px; }
        .badge-pendiente { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:99px; font-size:11px; font-weight:500; background:rgba(251,146,60,.1); border:1px solid rgba(251,146,60,.25); color:var(--warn); }
        .badge-pendiente .dot { width:6px; height:6px; border-radius:50%; background:var(--warn); box-shadow:0 0 6px var(--warn); animation:pulse 1.5s ease-in-out infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

        .paq-id   { font-family:'Syne',sans-serif; font-size:17px; font-weight:800; color:var(--text); }
        .paq-info { font-size:13px; color:var(--muted); display:flex; flex-direction:column; gap:5px; }
        .paq-info span { display:flex; align-items:center; gap:7px; }

        .btn-retirar-card { width:100%; padding:11px; border-radius:10px; border:none; cursor:pointer; background:linear-gradient(135deg,var(--accent),var(--accent2)); color:#fff; font-family:'Syne',sans-serif; font-size:13px; font-weight:700; transition:opacity .2s,transform .15s; box-shadow:0 4px 16px var(--glow); display:flex; align-items:center; justify-content:center; gap:7px; }
        .btn-retirar-card:hover { opacity:.88; transform:translateY(-1px); }
        .btn-retirar-card svg { width:15px; height:15px; }

        .empty-wrap { background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:60px 20px; text-align:center; color:var(--muted); }
        .empty-wrap .empty-icon { font-size:48px; margin-bottom:16px; }
        .empty-wrap p { font-size:14px; margin-bottom:12px; }
        .empty-wrap a { font-size:13px; color:var(--accent); text-decoration:none; }
        .empty-wrap a:hover { text-decoration:underline; }

        .overlay { position:fixed; inset:0; background:rgba(0,0,0,.7); backdrop-filter:blur(8px); z-index:200; display:none; place-items:center; }
        .overlay.show { display:grid; }
        .modal { background:var(--surface); border:1px solid var(--border); border-radius:20px; padding:40px 36px; width:360px; text-align:center; box-shadow:0 40px 80px rgba(0,0,0,.6); animation:up .4s cubic-bezier(.16,1,.3,1) both; position:relative; }
        .modal-close { position:absolute; top:16px; right:16px; background:none; border:none; color:var(--muted); cursor:pointer; font-size:20px; line-height:1; transition:color .2s; }
        .modal-close:hover { color:var(--text); }
        .modal-icon { width:60px; height:60px; border-radius:16px; background:linear-gradient(135deg,var(--accent),var(--accent2)); display:grid; place-items:center; font-size:26px; margin:0 auto 16px; box-shadow:0 4px 24px var(--glow); }
        .modal h3 { font-family:'Syne',sans-serif; font-size:20px; font-weight:800; margin-bottom:6px; }
        .modal .modal-paq { font-size:12px; color:var(--accent); font-weight:500; margin-bottom:16px; }
        .modal p  { font-size:13px; color:var(--muted); margin-bottom:24px; }
        .pin-display { display:flex; gap:10px; justify-content:center; margin-bottom:12px; }
        .pin-digit { width:52px; height:60px; border-radius:12px; background:rgba(79,142,247,.08); border:1px solid rgba(79,142,247,.25); display:grid; place-items:center; font-family:'Syne',sans-serif; font-size:26px; font-weight:800; color:var(--accent); animation:popIn .4s cubic-bezier(.16,1,.3,1) both; }
        .pin-digit:nth-child(1){animation-delay:.05s} .pin-digit:nth-child(2){animation-delay:.10s} .pin-digit:nth-child(3){animation-delay:.15s} .pin-digit:nth-child(4){animation-delay:.20s}
        @keyframes popIn { from { opacity:0; transform:scale(.7); } }
        .pin-hint { font-size:12px; color:var(--muted); padding:10px 14px; background:rgba(255,255,255,.03); border:1px solid var(--border); border-radius:10px; line-height:1.6; }
        .pin-hint strong { color:var(--ok); }
	.mensaje-ok {

    margin-top: 20px;

    padding: 25px;

    border-radius: 18px;

    background: rgba(34,211,165,.06);

    border: 1px solid rgba(34,211,165,.18);

    animation: up .3s ease;
}

.ok-icon {

    width: 70px;
    height: 70px;

    margin: 0 auto 20px;

    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 34px;

    background: rgba(34,211,165,.08);

    border: 2px solid rgba(34,211,165,.3);
}

.mensaje-ok h2 {

    text-align: center;

    color: #22d3a5;

    font-family: 'Syne', sans-serif;

    margin-bottom: 10px;
}

.mensaje-ok p {

    text-align: center;

    color: #9ca3af;

    margin-bottom: 20px;
}

.ok-info {

    background: rgba(255,255,255,.03);

    border: 1px solid rgba(255,255,255,.05);

    border-radius: 14px;

    padding: 16px;

    display: flex;
    flex-direction: column;

    gap: 10px;
}
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
        <h1>Mis <span>paquetes</span></h1>
        <a class="btn-back" href="index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Volver al panel
        </a>
    </div>

    <div class="summary">
        <?php if (!empty($paquetes)): ?>
        <div class="pill orange">⏳ <?= count($paquetes) ?> pendiente<?= count($paquetes) > 1 ? 's' : '' ?> de recoger</div>
        <?php else: ?>
        <div class="pill muted">Sin paquetes pendientes</div>
        <?php endif; ?>
    </div>

    <?php if (!empty($paquetes)): ?>
    <div class="paquetes-grid">
        <?php foreach ($paquetes as $p):
            $fecha = date('d/m/Y · H:i', strtotime($p['fecha_asignacion']));
            $pid   = htmlspecialchars($p['paquete_id'], ENT_QUOTES);
            $ptaq  = htmlspecialchars($p['taquilla'],   ENT_QUOTES);
            $ppin  = htmlspecialchars($p['pin'] ?? '',  ENT_QUOTES);
        ?>
        <div class="paquete-card">
            <div class="card-top">
                <div class="paq-icon">📦</div>
                <div class="badge-pendiente"><span class="dot"></span> Pendiente</div>
            </div>
            <div class="paq-id"><?= $pid ?></div>
            <div class="paq-info">
                <span>🔒 Taquilla <strong style="color:var(--text)"><?= $ptaq ?></strong></span>
                <span>📅 Asignado: <?= $fecha ?></span>
            </div>
            <button type="button" class="btn-retirar-card" onclick="abrirPin('<?= $pid ?>','<?= $ptaq ?>','<?= $ppin ?>')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                Ver PIN de retirada
            </button>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="empty-wrap">
        <div class="empty-icon">📭</div>
        <p>No tienes paquetes pendientes de recoger.</p>
        <a href="historial.php">Ver historial de recogidas →</a>
    </div>
    <?php endif; ?>
</main>

<div class="overlay" id="overlay">
    <div class="modal">
        <button class="modal-close" onclick="cerrarModal()">✕</button>
        <div class="modal-icon">🔑</div>
        <h3>PIN de retirada</h3>
        <div class="modal-paq" id="modalPaqLabel"></div>
        <p>Introduce este código en el teclado de la taquilla</p>
        <div class="pin-display" id="pinDisplay"></div>
	<div id="monitor"></div>
        <div class="pin-hint">
            Dirígete a la taquilla <strong id="hintTaquilla"></strong> e introduce el PIN en el teclado. La puerta se abrirá automáticamente.
        </div>
    </div>
</div>

<script>

let intervalEstado;

function abrirPin(paquete, taquilla, pin) {

    document.getElementById('modalPaqLabel').textContent =
        paquete + ' · Taquilla ' + taquilla;

    document.getElementById('pinDisplay').innerHTML =
        pin.split('').map(d =>
            `<div class="pin-digit">${d}</div>`
        ).join('');

    document.getElementById('hintTaquilla').textContent =
        taquilla;

    document.getElementById('overlay').classList.add('show');

    intervalEstado = setInterval(async () => {

        try {

            const respuesta =
                await fetch('comprovar_estat.php?paquete=' + paquete);

            const estado = await respuesta.text();

            document.getElementById('monitor').innerText =
                estado;

            if (estado.trim() == "recogido") {

                document.getElementById('monitor').innerHTML = `

                    <div class="mensaje-ok">

                        <div class="ok-icon">
                            ✅
                        </div>

                        <h2>¡Recogida confirmada!</h2>

                        <p>
                            Tu paquete ha sido marcado como recogido.
                        </p>

                        <div class="ok-info">

                            <div>
                                📦 Paquete:
                                <strong>${paquete}</strong>
                            </div>

                            <div>
                                🔒 Taquilla:
                                <strong>${taquilla}</strong>
                            </div>

                            <div>
                                🔑 PIN:
                                <strong>${pin}</strong>
                            </div>

                            <div>
                                🕒 Hora:
                                <strong>
                                    ${new Date().toLocaleTimeString()}
                                </strong>
                            </div>

                        </div>

                    </div>

                `;

                clearInterval(intervalEstado);
            }

        } catch(error) {

            console.log(error);
        }

    }, 1000);
}

function cerrarModal() {

    clearInterval(intervalEstado);

    document.getElementById('overlay')
        .classList.remove('show');
}

document.getElementById('overlay')
.addEventListener('click', e => {

    if (e.target === e.currentTarget) {

        cerrarModal();
    }
});

</script>
</body>
</html>
