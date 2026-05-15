<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SmartLocker</title>
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
        }
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

        label { display:block; font-size:13px; font-weight:500; color:#9ca3af; margin-bottom:8px; }

        .input-wrap { position:relative; }
        .input-wrap svg { position:absolute; left:14px; top:50%; transform:translateY(-50%); width:18px; height:18px; color:var(--muted); pointer-events:none; transition:color .2s; }
        .input-wrap:focus-within svg:first-child { color:var(--accent); }

        input[type=text], input[type=password] {
            width:100%; padding:12px 14px 12px 42px;
            background:rgba(255,255,255,.04); border:1px solid var(--border);
            border-radius:10px; color:var(--text); font-family:'DM Sans',sans-serif; font-size:15px; outline:none;
            transition:border-color .2s,background .2s,box-shadow .2s;
        }
        input:focus { border-color:var(--accent); background:rgba(79,142,247,.05); box-shadow:0 0 0 3px var(--glow); }
        input::placeholder { color:#3d4450; }
        input.invalid { border-color:rgba(248,113,113,.5); }

        .field-msg { font-size:12px; margin-top:5px; min-height:16px; }
        .field-msg.err { color:var(--err); }

        .btn-submit {
            width:100%; padding:14px; margin-top:20px;
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

        .register-link { text-align:center; font-size:14px; color:var(--muted); }
        .register-link a { color:var(--accent); text-decoration:none; font-weight:500; }
        .register-link a:hover { text-decoration:underline; }

        .toast {
            position:fixed; bottom:30px; left:50%; transform:translateX(-50%) translateY(20px);
            background:rgba(30,33,40,.98); border:1px solid var(--border); border-radius:10px;
            padding:12px 20px; font-size:14px; color:var(--text);
            opacity:0; pointer-events:none; transition:opacity .3s,transform .3s;
            backdrop-filter:blur(12px); z-index:100; white-space:nowrap;
        }
        .toast.show  { opacity:1; transform:translateX(-50%) translateY(0); }
        .toast.error { border-color:rgba(248,113,113,.3); }
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

    <h1>Bienvenido de nuevo</h1>
    <p class="sub">Inicia sesión para acceder a tus lockers</p>

    <form id="form" action="validar.php" method="POST" novalidate>

        <div class="form-group">
            <label for="usuario">Usuario</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input type="text" id="usuario" name="usuario" placeholder="Tu nombre de usuario" autocomplete="username" required>
            </div>
            <div class="field-msg" id="msg-usuario"></div>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="input-wrap">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input type="password" id="password" name="password" placeholder="Tu contraseña" autocomplete="current-password" required>

            </div>
            <div class="field-msg" id="msg-password"></div>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">
            <span id="btnText">Iniciar sesión</span>
            <div class="spinner" id="spinner"></div>
        </button>
    </form>

    <div class="divider"><span>¿No tienes cuenta?</span></div>
    <div class="register-link"><a href="register.php">Crear una cuenta →</a></div>
</div>

<div class="toast" id="toast"></div>

<script>
    const $ = id => document.getElementById(id);
    const pw = $('password'), user = $('usuario');

    // Toast
    const showToast = (msg, type) => {
        const t = $('toast');
        t.textContent = msg; t.className = `toast ${type} show`;
        setTimeout(() => t.classList.remove('show'), 3000);
    };

    // Submit
    $('form').addEventListener('submit', function(e) {
        const u = user.value.trim(), p = pw.value;

        if (!u) {
            e.preventDefault();
            $('msg-usuario').textContent = 'Introduce tu usuario';
            $('msg-usuario').className = 'field-msg err';
            user.classList.add('invalid');
            return;
        }
        if (!p) {
            e.preventDefault();
            $('msg-password').textContent = 'Introduce tu contraseña';
            $('msg-password').className = 'field-msg err';
            pw.classList.add('invalid');
            return;
        }

        // Todo OK — deja que el formulario haga submit normal
        $('btnText').style.display = 'none';
        $('spinner').style.display = 'block';
        $('submitBtn').classList.add('loading');
    });

    // Clear error on input
    [user, pw].forEach(input => {
        input.addEventListener('input', () => {
            input.classList.remove('invalid');
            const msg = input === user ? $('msg-usuario') : $('msg-password');
            msg.textContent = '';
        });
    });
</script>
</body>
</html>
