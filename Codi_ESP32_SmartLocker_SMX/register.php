<?php
$error = $_GET["error"] ?? "";
$mensajes = [
    "campos_vacios"          => "Por favor rellena todos los campos.",
    "passwords_no_coinciden" => "Las contraseñas no coinciden.",
    "password_corta"         => "La contraseña debe tener mínimo 8 caracteres.",
    "usuario_existe"         => "Ese nombre de usuario ya está en uso.",
    "rol_invalido"           => "Selecciona un tipo de cuenta.",
    "error_bd"               => "Error al guardar. Inténtalo de nuevo.",
];
$msgError = $mensajes[$error] ?? "";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro — SmartLocker</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:#0a0c10; --surface:#111318; --border:rgba(255,255,255,0.07);
            --accent:#4f8ef7; --glow:rgba(79,142,247,0.25); --accent2:#7c5cfc;
            --text:#f0f2f7; --muted:#6b7280; --ok:#22d3a5; --err:#f87171;
        }
        * { box-sizing:border-box; margin:0; padding:0; }

        body { font-family:'DM Sans',sans-serif; background:var(--bg); min-height:100vh; display:grid; place-items:center; overflow:hidden; }
        body::before, body::after { content:''; position:fixed; border-radius:50%; pointer-events:none; animation:drift 12s ease-in-out infinite alternate; }
        body::before { top:-30%; left:-20%; width:700px; height:700px; background:radial-gradient(circle,rgba(79,142,247,.12),transparent 70%); }
        body::after  { bottom:-20%; right:-15%; width:600px; height:600px; background:radial-gradient(circle,rgba(124,92,252,.1),transparent 70%); animation-duration:15s; animation-direction:alternate-reverse; }
        @keyframes drift { to { transform:translate(40px,30px); } }

        .grid-bg { position:fixed; inset:0; background:radial-gradient(circle,rgba(255,255,255,.04) 1px,transparent 1px) 0 0/40px 40px; pointer-events:none; }

        .card {
            position:relative; z-index:10; width:420px;
            background:var(--surface); border:1px solid var(--border); border-radius:20px; padding:44px 40px;
            box-shadow:0 0 0 1px rgba(255,255,255,.03),0 40px 80px rgba(0,0,0,.6),0 0 60px rgba(79,142,247,.06);
            animation:up .6s cubic-bezier(.16,1,.3,1) both;
            max-height:90vh; overflow-y:auto; scrollbar-width:thin; scrollbar-color:rgba(79,142,247,.3) transparent;
        }
        .card::-webkit-scrollbar { width:4px; }
        .card::-webkit-scrollbar-track { background:transparent; }
        .card::-webkit-scrollbar-thumb { background:rgba(79,142,247,.3); border-radius:99px; }
        @keyframes up { from { opacity:0; transform:translateY(28px) scale(.97); } }

        .card-accent { position:absolute; top:0; left:0; right:0; height:3px; border-radius:20px 20px 0 0; background:linear-gradient(90deg,var(--accent),var(--accent2)); }

        .brand { display:flex; align-items:center; gap:10px; margin-bottom:32px; }
        .brand-icon { width:40px; height:40px; background:linear-gradient(135deg,var(--accent),var(--accent2)); border-radius:10px; display:grid; place-items:center; font-size:18px; box-shadow:0 4px 20px var(--glow); }
        .brand-text { font-family:'Syne',sans-serif; font-weight:800; font-size:18px; color:var(--text); letter-spacing:-.3px; }
        .brand-sub  { font-size:11px; color:var(--muted); letter-spacing:.5px; text-transform:uppercase; }

        h1  { font-family:'Syne',sans-serif; font-size:26px; font-weight:700; color:var(--text); letter-spacing:-.5px; }
        .sub { font-size:14px; color:var(--muted); margin:6px 0 28px; }

        .form-group { margin-bottom:16px; animation:up .6s cubic-bezier(.16,1,.3,1) both; }
        .form-group:nth-child(1){animation-delay:.10s}
        .form-group:nth-child(2){animation-delay:.15s}
        .form-group:nth-child(3){animation-delay:.20s}
        .form-group:nth-child(4){animation-delay:.25s}

        label { display:block; font-size:13px; font-weight:500; color:#9ca3af; margin-bottom:8px; }

        .input-wrap { position:relative; }
        .input-wrap svg { position:absolute; left:14px; top:50%; transform:translateY(-50%); width:18px; height:18px; color:var(--muted); pointer-events:none; transition:color .2s; }
        .input-wrap:focus-within svg:first-child { color:var(--accent); }

        input[type=text], input[type=password], input[type=email] {
            width:100%; padding:12px 14px 12px 42px;
            background:rgba(255,255,255,.04); border:1px solid var(--border);
            border-radius:10px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:15px; outline:none;
            transition:border-color .2s,background .2s,box-shadow .2s;
        }
        input:focus { border-color:var(--accent); background:rgba(79,142,247,.05); box-shadow:0 0 0 3px var(--glow); }
        input::placeholder { color:#3d4450; }
        input.valid   { border-color:rgba(34,211,165,.4); }
        input.invalid { border-color:rgba(248,113,113,.5); }

        .strength-bar  { height:3px; background:rgba(255,255,255,.06); border-radius:99px; margin-top:8px; overflow:hidden; }
        .strength-fill { height:100%; width:0; border-radius:99px; transition:width .4s,background .4s; }
        .strength-lbl  { font-size:11px; color:var(--muted); margin-top:5px; height:14px; transition:color .3s; }

        .field-msg { font-size:12px; margin-top:5px; min-height:16px; }
        .field-msg.ok  { color:var(--ok); }
        .field-msg.err { color:var(--err); }

        .btn-submit {
            width:100%; padding:14px; margin-top:4px;
            background:linear-gradient(135deg,var(--accent),var(--accent2));
            border:none; color:#fff; font-family:'Syne',sans-serif; font-size:15px; font-weight:700;
            border-radius:10px; cursor:pointer; letter-spacing:.3px;
            transition:transform .15s,box-shadow .2s,opacity .2s;
            box-shadow:0 4px 24px rgba(79,142,247,.3);
        }
        .btn-submit:hover { transform:translateY(-1px); box-shadow:0 6px 32px rgba(79,142,247,.45); }
        .btn-submit:active { transform:none; }
        .btn-submit.loading { opacity:.7; pointer-events:none; }

        .spinner { display:none; width:18px; height:18px; border:2px solid rgba(255,255,255,.3); border-top-color:#fff; border-radius:50%; animation:spin .6s linear infinite; margin:0 auto; }
        @keyframes spin { to { transform:rotate(360deg); } }

        .divider { display:flex; align-items:center; gap:14px; margin:22px 0; }
        .divider::before, .divider::after { content:''; flex:1; height:1px; background:var(--border); }
        .divider span { font-size:12px; color:var(--muted); white-space:nowrap; }

        .login-link { text-align:center; font-size:14px; color:var(--muted); }
        .login-link a { color:var(--accent); text-decoration:none; font-weight:500; }
        .login-link a:hover { text-decoration:underline; }

        .error-banner { background:rgba(248,113,113,.1); border:1px solid rgba(248,113,113,.3); border-radius:10px; padding:11px 14px; font-size:13px; color:var(--err); margin-bottom:16px; text-align:center; }

        .rol-selector { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-top:2px; }
        .rol-selector input[type=radio] { display:none; }
        .rol-card { display:flex; flex-direction:column; align-items:center; gap:6px; padding:16px 10px; border-radius:12px; cursor:pointer; border:1px solid var(--border); background:rgba(255,255,255,.03); transition:all .2s; text-align:center; }
        .rol-card:hover { border-color:rgba(79,142,247,.3); background:rgba(79,142,247,.05); }
        .rol-selector input[type=radio]:checked + .rol-card { border-color:var(--accent); background:rgba(79,142,247,.1); box-shadow:0 0 0 3px var(--glow); }
        .rol-icon  { font-size:26px; }
        .rol-title { font-family:'Syne',sans-serif; font-size:14px; font-weight:700; color:var(--text); }
        .rol-desc  { font-size:11px; color:var(--muted); line-height:1.4; }

        .toast {
            position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(20px);
            background:rgba(30,33,40,.98); border:1px solid var(--border); border-radius:10px;
            padding:12px 20px; font-size:14px; color:var(--text);
            opacity:0; pointer-events:none; transition:opacity .3s,transform .3s;
            backdrop-filter:blur(12px); z-index:100; white-space:nowrap;
        }
        .toast.show    { opacity:1; transform:translateX(-50%) translateY(0); }
        .toast.success { border-color:rgba(34,211,165,.3); }
        .toast.error   { border-color:rgba(248,113,113,.3); }
    </style>
</head>
<body>
<div class="grid-bg"></div>

<div class="card">
    <div class="card-accent"></div>

    <div class="brand">
        <div class="brand-icon">🔐</div>
        <div>
            <div class="brand-text">SmartLocker</div>
            <div class="brand-sub">Solutions</div>
        </div>
    </div>

    <h1>Crear cuenta</h1>
    <p class="sub">Únete y gestiona tus lockers de forma inteligente</p>

    <?php if ($msgError): ?>
    <div class="error-banner"><?= htmlspecialchars($msgError) ?></div>
    <?php endif; ?>

    <form id="form" action="guardar_usuario.php" method="POST" novalidate>

        <div class="form-group">
            <label for="usuario">Nombre de usuario</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input type="text" id="usuario" name="usuario" placeholder="ej. carlos_m" autocomplete="username" required>
            </div>
            <div class="field-msg" id="msg-usuario"></div>
        </div>

        <div class="form-group">
            <label for="email">Correo electrónico</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" autocomplete="email" required>
            </div>
            <div class="field-msg" id="msg-email"></div>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
            </div>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
            <div class="strength-lbl" id="strengthLbl"></div>
            <div class="field-msg" id="msg-password"></div>
        </div>

        <div class="form-group">
            <label for="confirm">Repetir contraseña</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" id="confirm" name="confirm" placeholder="Repite tu contraseña" autocomplete="new-password" required>
            </div>
            <div class="field-msg" id="msg-confirm"></div>
        </div>

        <div class="form-group">
            <label>Tipo de cuenta</label>
            <div class="rol-selector">
                <input type="radio" name="rol" id="rol-consumidor" value="consumidor" required>
                <label for="rol-consumidor" class="rol-card">
                    <span class="rol-icon">📦</span>
                    <span class="rol-title">Consumidor</span>
                    <span class="rol-desc">Recoge paquetes y consulta tu historial</span>
                </label>
                <input type="radio" name="rol" id="rol-repartidor" value="repartidor">
                <label for="rol-repartidor" class="rol-card">
                    <span class="rol-icon">🚚</span>
                    <span class="rol-title">Repartidor</span>
                    <span class="rol-desc">Asigna paquetes a taquillas</span>
                </label>
            </div>
            <div class="field-msg" id="msg-rol"></div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <span id="btnText">Crear cuenta</span>
            <div class="spinner" id="spinner"></div>
        </button>
    </form>

    <div class="divider"><span>¿Ya tienes una cuenta?</span></div>
    <div class="login-link"><a href="login.php">Iniciar sesión →</a></div>
</div>

<div class="toast" id="toast"></div>

<script>
    const $ = id => document.getElementById(id);
    const pw = $('password'), user = $('usuario'), email = $('email'), confirm = $('confirm');

    // Password strength
    pw.addEventListener('input', () => {
        const v = pw.value;
        const score = !v.length ? 0 : [v.length>=8, /[A-Z]/.test(v), /\d/.test(v), /\W/.test(v)].filter(Boolean).length;
        const levels = [
            ['0%','transparent',''],
            ['25%','var(--err)','⚠ Muy débil'],
            ['50%','#fb923c','⚡ Débil'],
            ['75%','#facc15','✓ Buena'],
            ['100%','var(--ok)','✦ Muy segura']
        ];
        const [w, bg, text] = levels[score];
        const fill = $('strengthFill'), lbl = $('strengthLbl');
        fill.style.width = w; fill.style.background = bg;
        lbl.textContent = text; lbl.style.color = bg;
    });

    // Field validation
    function addValidation(input, msgId, check) {
        const msg = $(msgId);
        const run = () => {
            const { ok, text } = check(input.value);
            msg.textContent = text;
            msg.className = 'field-msg ' + (ok ? 'ok' : 'err');
            input.classList.toggle('valid', ok);
            input.classList.toggle('invalid', !ok && !!input.value);
        };
        input.addEventListener('blur', run);
        input.addEventListener('input', () => input.classList.contains('invalid') && run());
    }

    addValidation(user, 'msg-usuario', v => {
        if (v.length < 3) return { ok:false, text:'Mínimo 3 caracteres' };
        if (!/^\w+$/.test(v)) return { ok:false, text:'Solo letras, números y _' };
        return { ok:true, text:'✓ Nombre disponible' };
    });
    addValidation(email, 'msg-email', v => {
        const ok = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v);
        return { ok, text: ok ? '✓ Email válido' : 'Introduce un email válido' };
    });
    addValidation(pw, 'msg-password', v => ({
        ok: v.length >= 8, text: v.length >= 8 ? '✓ Contraseña aceptada' : 'Mínimo 8 caracteres'
    }));
    addValidation(confirm, 'msg-confirm', v => ({
        ok: v === pw.value && v.length > 0, text: v === pw.value && v.length > 0 ? '✓ Las contraseñas coinciden' : 'Las contraseñas no coinciden'
    }));
    // Re-validar confirm si cambia la contraseña principal
    pw.addEventListener('input', () => { if (confirm.value) confirm.dispatchEvent(new Event('input')); });

    // Toast
    const showToast = (msg, type) => {
        const t = $('toast');
        t.textContent = msg; t.className = `toast ${type} show`;
        setTimeout(() => t.classList.remove('show'), 3000);
    };

    // Submit
    $('form').addEventListener('submit', function(e) {
        const rolSeleccionado = document.querySelector('input[name="rol"]:checked');
        if (!user.value.trim() || !email.value.trim() || !pw.value || !confirm.value) {
            e.preventDefault(); return showToast('Por favor rellena todos los campos', 'error');
        }
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
            e.preventDefault(); return showToast('Email no válido', 'error');
        }
        if (pw.value.length < 8) {
            e.preventDefault(); return showToast('Contraseña demasiado corta', 'error');
        }
        if (pw.value !== confirm.value) {
            e.preventDefault(); return showToast('Las contraseñas no coinciden', 'error');
        }
        if (!rolSeleccionado) {
            e.preventDefault();
            $('msg-rol').textContent = 'Selecciona un tipo de cuenta';
            $('msg-rol').className = 'field-msg err';
            return;
        }
        // Todo OK — submit normal
        $('btnText').style.display = 'none';
        $('spinner').style.display = 'block';
        $('submitBtn').classList.add('loading');
    });
</script>
</body>
</html>
