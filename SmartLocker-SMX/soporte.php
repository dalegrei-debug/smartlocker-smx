<?php
session_start();

$conexion = new mysqli("localhost", "webuser", "1234", "smartlocker");
$usuario  = $_SESSION['usuario'] ?? null;
$rol      = $_SESSION['rol']     ?? 'consumidor';

// Reiniciar chat
if (isset($_GET['reset'])) {
    unset($_SESSION['chat']);
}
if (!isset($_SESSION['chat'])) {
    $_SESSION['chat'] = [];
}

// Procesar mensaje
if (isset($_POST['mensaje'])) {
    $msgOriginal = trim($_POST['mensaje']);
    $msg         = strtolower($msgOriginal);
    $_SESSION['chat'][] = ['user', $msgOriginal];

    $respuesta = "No he entendido tu mensaje. ¿Puedes explicarlo de otra manera? Puedes preguntarme por tus paquetes, el PIN, la taquilla o el historial.";

    // SALUDOS
    if (preg_match('/^(hola|buenas|hey|holaa|buenos días|buenas tardes|buenas noches|saludos)/', $msg)) {
        $nombre = $usuario ? ", $usuario" : "";
        $respuesta = "¡Hola$nombre! 👋 Soy el asistente de SmartLocker. Puedo ayudarte con tus paquetes, PINs, taquillas e historial. ¿Qué necesitas?";
    }

    // PAQUETES PENDIENTES
    elseif (preg_match('/pendiente|paquete|mis paquetes|tengo algo/', $msg)) {
        if ($usuario) {
            $sql = $conexion->prepare("SELECT COUNT(*) AS total FROM paquetes WHERE usuario=? AND estado='pendiente'");
            $sql->bind_param('s', $usuario);
            $sql->execute();
            $total = $sql->get_result()->fetch_assoc()['total'];
            $sql->close();
            if ($total > 0) {
                $respuesta = "Tienes **$total paquete" . ($total > 1 ? 's' : '') . " pendiente" . ($total > 1 ? 's' : '') . "** de recoger. 📦 Puedes verlos en <a href='mis_paquetes.php'>Mis paquetes</a>.";
            } else {
                $respuesta = "No tienes paquetes pendientes ahora mismo. 📭 Cuando llegue uno te aparecerá en tu bandeja.";
            }
        } else {
            $respuesta = "Debes iniciar sesión para consultar tus paquetes.";
        }
    }

    // HISTORIAL
    elseif (preg_match('/historial|recogido|anteriores|recogidas/', $msg)) {
        $respuesta = "Tu historial de recogidas está en <a href='historial.php'>Historial</a>. Ahí puedes ver todos los paquetes que has recogido con fecha y PIN.";
    }

    // TAQUILLA / PROBLEMA AL ABRIR
    elseif (preg_match('/taquilla|abrir|no abre|bloqueada|atascada/', $msg)) {
        $respuesta = "Si la taquilla no abre, comprueba que el PIN sea correcto y que no haya caducado (10 minutos). Si sigue fallando, contacta con un administrador en persona.";
    }

    // PIN
    elseif (preg_match('/pin|código|clave|contraseña de la taquilla/', $msg)) {
        $respuesta = "El PIN aparece cuando pulsas **Retirar paquete** en tu bandeja. Cada PIN es válido durante **10 minutos**. Si no te aparece, puede que el repartidor aún no haya asignado el paquete a una taquilla.";
    }

    // CADUCADO
    elseif (preg_match('/caducado|caduca|expirado|días sin recoger/', $msg)) {
        $respuesta = "Los paquetes caducan a los **5 días** sin ser recogidos. Si tu paquete ha caducado, contacta con el repartidor para que lo retire y te lo reasigne.";
    }

    // REGISTRARSE
    elseif (preg_match('/registrar|crear cuenta|cuenta nueva|sign up/', $msg)) {
        $respuesta = "Puedes crear una cuenta en <a href='register.php'>Registro</a>. Elige si eres consumidor o repartidor al registrarte.";
    }

    // CONTRASEÑA OLVIDADA
    elseif (preg_match('/contraseña|password|olvidé|no recuerdo/', $msg)) {
        $respuesta = "Si olvidaste tu contraseña, contacta con el administrador del sistema para que la restablezca.";
    }

    // REPARTIDOR: GENERAR / TURNO
    elseif ($rol === 'repartidor' && preg_match('/turno|entregar|paquete nuevo|generar|asignar/', $msg)) {
        $respuesta = "Como repartidor, puedes recibir tu turno desde el panel principal con el botón **Recibir turno**. Los paquetes aparecerán en <a href='paquetes_asignados.php'>Paquetes asignados</a> para marcarlos como entregados.";
    }

    // REPARTIDOR: CADUCADOS
    elseif ($rol === 'repartidor' && preg_match('/caducado|retirar/', $msg)) {
        $respuesta = "Los paquetes caducados aparecen en <a href='paquetes_asignados.php'>Paquetes asignados</a> con una sección roja. Márcalos como retirados una vez los hayas recogido de la taquilla.";
    }

    // CERRAR SESIÓN / LOGOUT
    elseif (preg_match('/cerrar sesión|logout|salir|desconectar/', $msg)) {
        $respuesta = "Puedes cerrar sesión desde el botón **Salir** en la esquina superior derecha del panel.";
    }

    // AYUDA GENERAL
    elseif (preg_match('/ayuda|help|qué puedes|qué sabes|opciones/', $msg)) {
        $respuesta = "Puedo ayudarte con: 📦 **Paquetes pendientes** · 🔑 **PINs** · 🔒 **Taquillas** · 📅 **Historial** · ⏰ **Paquetes caducados** · 👤 **Cuenta y sesión**. ¿Sobre qué quieres saber?";
    }

    // DESPEDIDA
    elseif (preg_match('/gracias|adios|adiós|hasta luego|nos vemos|bye/', $msg)) {
        $respuesta = "¡Hasta luego! 👋 Si necesitas algo más, aquí estaré.";
    }

    $_SESSION['chat'][] = ['bot', $respuesta];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte — SmartLocker</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0a0c10; --surface:#111318; --border:rgba(255,255,255,0.07);
            --accent:#4f8ef7; --glow:rgba(79,142,247,0.25); --accent2:#7c5cfc;
            --text:#f0f2f7; --muted:#6b7280; --ok:#22d3a5; --err:#f87171;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); min-height:100vh; color:var(--text); display:flex; flex-direction:column; }
        body::before, body::after { content:''; position:fixed; border-radius:50%; pointer-events:none; animation:drift 12s ease-in-out infinite alternate; }
        body::before { top:-20%; left:-10%; width:700px; height:700px; background:radial-gradient(circle,rgba(79,142,247,.08),transparent 70%); }
        body::after  { bottom:-20%; right:-10%; width:600px; height:600px; background:radial-gradient(circle,rgba(124,92,252,.07),transparent 70%); animation-duration:15s; animation-direction:alternate-reverse; }
        @keyframes drift { to { transform:translate(40px,30px); } }
        .grid-bg { position:fixed; inset:0; background:radial-gradient(circle,rgba(255,255,255,.03) 1px,transparent 1px) 0 0/40px 40px; pointer-events:none; }

        /* HEADER */
        header { position:sticky; top:0; z-index:100; background:rgba(10,12,16,0.85); backdrop-filter:blur(16px); border-bottom:1px solid var(--border); padding:0 32px; display:flex; align-items:center; justify-content:space-between; height:64px; flex-shrink:0; }
        .brand { display:flex; align-items:center; gap:10px; text-decoration:none; }
        .brand-icon { width:36px; height:36px; background:linear-gradient(135deg,var(--accent),var(--accent2)); border-radius:9px; display:grid; place-items:center; font-size:16px; box-shadow:0 4px 16px var(--glow); }
        .brand-text { font-family:'Syne',sans-serif; font-weight:800; font-size:17px; color:var(--text); letter-spacing:-.3px; }
        .brand-sub  { font-size:10px; color:var(--muted); letter-spacing:.5px; text-transform:uppercase; }
        .header-right { display:flex; align-items:center; gap:12px; }
        .avatar { width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--accent),var(--accent2)); display:grid; place-items:center; font-family:'Syne',sans-serif; font-weight:800; font-size:14px; color:#fff; }
        .user-info-header { text-align:right; }
        .user-name { font-size:14px; font-weight:500; }
        .user-role { font-size:11px; color:var(--muted); }
        .btn-back { display:flex; align-items:center; gap:7px; padding:8px 14px; border-radius:9px; background:rgba(255,255,255,.04); border:1px solid var(--border); color:var(--muted); font-size:13px; font-weight:500; text-decoration:none; transition:color .2s; }
        .btn-back:hover { color:var(--text); }
        .btn-back svg { width:15px; height:15px; }

        /* CHAT */
        .chat-wrap { flex:1; display:flex; flex-direction:column; max-width:760px; width:100%; margin:0 auto; padding:28px 32px 32px; position:relative; z-index:1; min-height:0; }

        .chat-title { font-family:'Syne',sans-serif; font-size:22px; font-weight:800; margin-bottom:20px; display:flex; align-items:center; gap:10px; }
        .chat-title span { background:linear-gradient(90deg,var(--accent),var(--accent2)); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }

        .messages { flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:12px; padding:4px 2px; margin-bottom:20px; scrollbar-width:thin; scrollbar-color:rgba(79,142,247,.3) transparent; min-height:300px; max-height:calc(100vh - 280px); }
        .messages::-webkit-scrollbar { width:4px; }
        .messages::-webkit-scrollbar-thumb { background:rgba(79,142,247,.3); border-radius:99px; }

        .msg { display:flex; flex-direction:column; max-width:75%; animation:msgIn .3s cubic-bezier(.16,1,.3,1) both; }
        @keyframes msgIn { from { opacity:0; transform:translateY(8px); } }

        .msg.user { align-self:flex-end; align-items:flex-end; }
        .msg.bot  { align-self:flex-start; align-items:flex-start; }

        .msg-label { font-size:11px; color:var(--muted); margin-bottom:4px; font-weight:500; }

        .msg-bubble { padding:12px 16px; border-radius:14px; font-size:14px; line-height:1.6; }
        .msg.user .msg-bubble { background:linear-gradient(135deg,var(--accent),var(--accent2)); color:#fff; border-radius:14px 14px 4px 14px; }
        .msg.bot  .msg-bubble { background:var(--surface); border:1px solid var(--border); color:var(--text); border-radius:14px 14px 14px 4px; }
        .msg.bot  .msg-bubble a { color:var(--accent); }
        .msg.bot  .msg-bubble strong, .msg.bot .msg-bubble b { color:var(--accent); font-weight:600; }

        /* INPUT */
        .input-area { display:flex; gap:10px; }
        .input-area input {
            flex:1; padding:13px 16px; background:var(--surface); border:1px solid var(--border);
            border-radius:11px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:14px; outline:none;
            transition:border-color .2s, box-shadow .2s;
        }
        .input-area input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--glow); }
        .input-area input::placeholder { color:#3d4450; }
        .btn-send {
            padding:13px 22px; border:none; border-radius:11px; cursor:pointer;
            background:linear-gradient(135deg,var(--accent),var(--accent2));
            color:#fff; font-family:'Syne',sans-serif; font-size:14px; font-weight:700;
            transition:transform .15s, box-shadow .2s; box-shadow:0 4px 16px var(--glow);
            white-space:nowrap;
        }
        .btn-send:hover { transform:translateY(-1px); box-shadow:0 6px 24px var(--glow); }
        .btn-reset { display:inline-flex; align-items:center; gap:6px; margin-top:12px; padding:8px 14px; border-radius:9px; background:rgba(248,113,113,.08); border:1px solid rgba(248,113,113,.2); color:var(--err); font-size:12px; font-weight:500; text-decoration:none; transition:background .2s; }
        .btn-reset:hover { background:rgba(248,113,113,.15); }

        /* BOT INTRO */
        .bot-intro { display:flex; align-items:center; gap:10px; padding:14px 16px; background:rgba(79,142,247,.06); border:1px solid rgba(79,142,247,.15); border-radius:12px; margin-bottom:16px; font-size:13px; color:var(--muted); }
        .bot-intro-icon { font-size:22px; }
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
        <?php if ($usuario): ?>
        <div class="user-info-header">
            <div class="user-name"><?= htmlspecialchars($usuario) ?></div>
            <div class="user-role"><?= ucfirst($rol) ?></div>
        </div>
        <div class="avatar"><?= strtoupper(substr($usuario, 0, 1)) ?></div>
        <?php endif; ?>
        <a class="btn-back" href="index.php">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m12 19-7-7 7-7"/></svg>
            Volver
        </a>
    </div>
</header>

<div class="chat-wrap">
    <div class="chat-title">💬 <span>Chat de soporte</span></div>

    <div class="bot-intro">
        <div class="bot-intro-icon">🤖</div>
        <span>Hola<?= $usuario ? ', <strong>' . htmlspecialchars($usuario) . '</strong>' : '' ?>. Puedo ayudarte con paquetes, PINs, taquillas e historial. Escribe tu pregunta abajo.</span>
    </div>

    <div class="messages" id="messages">
        <?php foreach ($_SESSION['chat'] as $c):
            $clase = $c[0] === 'user' ? 'user' : 'bot';
            $label = $clase === 'user' ? '👤 Tú' : '🤖 Bot';
            // Permitir HTML básico en respuestas del bot
            $texto = $clase === 'bot' ? $c[1] : htmlspecialchars($c[1]);
        ?>
        <div class="msg <?= $clase ?>">
            <div class="msg-label"><?= $label ?></div>
            <div class="msg-bubble"><?= $texto ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <form method="POST" class="input-area" id="chatForm">
        <input type="text" name="mensaje" id="inputMsg" placeholder="Escribe tu mensaje..." required autocomplete="off">
        <button type="submit" class="btn-send">Enviar ↑</button>
    </form>
</div>

<script>
    // Scroll al fondo al cargar
    const msgs = document.getElementById('messages');
    msgs.scrollTop = msgs.scrollHeight;

    // Scroll al fondo al enviar
    document.getElementById('chatForm').addEventListener('submit', () => {
        setTimeout(() => msgs.scrollTop = msgs.scrollHeight, 100);
    });
</script>
</body>
</html>
