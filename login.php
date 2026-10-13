<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) { header('Location: admin.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND status = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: admin.php');
        exit;
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Vaal Solutions</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
    /* ============================================================
       AUTH LAYOUT — SPLIT-SCREEN PREMIUM
       ============================================================ */
    * { box-sizing: border-box; }

    body.auth-page {
        margin: 0;
        min-height: 100vh;
        background: #0b1220;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        display: flex;
        overflow-x: hidden;
    }

    /* ============ SPLIT SHELL ============ */
    .auth-split {
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        min-height: 100vh;
        width: 100%;
    }

    /* ============ LEFT — BRAND PANEL ============ */
    .auth-visual {
        position: relative;
        background:
            radial-gradient(circle at 20% 20%, rgba(10,132,255,.55), transparent 55%),
            radial-gradient(circle at 80% 75%, rgba(124,58,237,.55), transparent 55%),
            linear-gradient(135deg, #0b1220 0%, #131a2b 50%, #1e1b4b 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 48px 56px;
        overflow: hidden;
        position: relative;
        isolation: isolate;
    }

    .auth-visual::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image:
            linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: radial-gradient(ellipse at center, #000 30%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, #000 30%, transparent 80%);
        z-index: -1;
        pointer-events: none;
    }

    .auth-visual::after {
        content: '';
        position: absolute;
        bottom: -180px; right: -180px;
        width: 460px; height: 460px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(79,195,247,.35), transparent 65%);
        filter: blur(30px);
        z-index: -1;
        pointer-events: none;
        animation: floatOrb 14s ease-in-out infinite alternate;
    }

    @keyframes floatOrb {
        0%   { transform: translate(0, 0) scale(1); }
        100% { transform: translate(-40px, -30px) scale(1.15); }
    }

    .visual-brand {
        display: flex;
        align-items: center;
        gap: 14px;
        font-family: 'Sora', sans-serif;
        font-weight: 800;
        font-size: 1.25rem;
        letter-spacing: -0.02em;
        z-index: 1;
    }

    .visual-brand img {
        height: 46px;
        border-radius: 12px;
        background: rgba(255,255,255,.08);
        padding: 4px;
        border: 1px solid rgba(255,255,255,.15);
    }

    .visual-brand strong {
        background: linear-gradient(135deg, #ffffff, #a78bfa);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .visual-content {
        max-width: 480px;
        z-index: 1;
    }

    .visual-content h2 {
        font-family: 'Sora', sans-serif;
        font-size: clamp(1.9rem, 3vw, 2.6rem);
        font-weight: 800;
        line-height: 1.15;
        letter-spacing: -0.03em;
        margin: 0 0 20px;
        color: #fff;
    }

    .visual-content h2 span {
        background: linear-gradient(135deg, #4fc3f7, #a78bfa, #ec4899);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .visual-content p {
        color: rgba(255,255,255,.7);
        font-size: 1.02rem;
        line-height: 1.7;
        margin: 0 0 32px;
    }

    .visual-features {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .visual-feature {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: .92rem;
        color: rgba(255,255,255,.85);
    }

    .visual-feature .check {
        width: 32px; height: 32px;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(10,132,255,.3), rgba(124,58,237,.3));
        border: 1px solid rgba(79,195,247,.4);
        color: #4fc3f7;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .78rem;
        flex-shrink: 0;
    }

    .visual-footer {
        display: flex;
        align-items: center;
        gap: 20px;
        font-size: .8rem;
        color: rgba(255,255,255,.4);
        z-index: 1;
    }

    .visual-footer a { color: rgba(255,255,255,.55); transition: color .25s; }
    .visual-footer a:hover { color: #4fc3f7; }

    /* ============ RIGHT — FORM PANEL ============ */
    .auth-form-panel {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 40px;
        background: #f6f8fc;
        position: relative;
        overflow-y: auto;
    }

    .auth-form-panel::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 300px; height: 300px;
        background: radial-gradient(circle, rgba(10,132,255,.08), transparent 65%);
        pointer-events: none;
    }

    .auth-card {
        background: #fff;
        width: 100%;
        max-width: 420px;
        padding: 44px 40px;
        border-radius: 24px;
        box-shadow: 0 25px 60px rgba(11,18,32,.08), 0 10px 30px rgba(11,18,32,.04);
        border: 1px solid rgba(11,18,32,.04);
        position: relative;
        z-index: 1;
        animation: cardIn .55s cubic-bezier(.22,.61,.36,1) both;
    }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .auth-mobile-logo {
        display: none;
        justify-content: center;
        margin-bottom: 24px;
    }

    .auth-mobile-logo img {
        height: 60px;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(10,132,255,.2);
    }

    .auth-card h1 {
        font-family: 'Sora', sans-serif;
        font-size: 1.65rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        margin: 0 0 8px;
        color: #0b1220;
    }

    .auth-intro {
        color: #64748b;
        margin: 0 0 28px;
        font-size: .95rem;
    }

    /* ============ FORM ============ */
    .auth-form .form-group {
        margin-bottom: 18px;
        position: relative;
    }

    .auth-form label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: .82rem;
        color: #334155;
        letter-spacing: .01em;
    }

    .input-wrap { position: relative; }

    .input-wrap .input-icon {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: .9rem;
        pointer-events: none;
        transition: color .25s;
    }

    .auth-form input {
        width: 100%;
        padding: 13px 46px 13px 46px;
        border: 1.5px solid #e6e9ef;
        border-radius: 12px;
        outline: none;
        font-size: .95rem;
        font-family: inherit;
        background: #fafbfd;
        transition: all .25s cubic-bezier(.22,.61,.36,1);
        color: #0b1220;
    }

    .auth-form input::placeholder { color: #94a3b8; }

    .auth-form input:focus {
        border-color: #0a84ff;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(10,132,255,.12);
    }

    .input-wrap:focus-within .input-icon { color: #0a84ff; }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 8px;
        transition: all .2s;
        font-size: .85rem;
    }

    .toggle-password:hover { color: #0a84ff; background: rgba(10,132,255,.08); }

    /* ============ OPTIONS ROW ============ */
    .auth-options {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 4px 0 22px;
        font-size: .85rem;
    }

    .auth-options label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-weight: 500;
        cursor: pointer;
        margin: 0;
    }

    .auth-options input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #0a84ff;
        cursor: pointer;
    }

    .auth-options a {
        color: #0a84ff;
        font-weight: 600;
        font-size: .85rem;
    }
    .auth-options a:hover { text-decoration: underline; }

    /* ============ SUBMIT ============ */
    .auth-submit {
        width: 100%;
        padding: 15px;
        border: none;
        background: linear-gradient(135deg, #0a84ff, #7c3aed);
        color: #fff;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: all .3s cubic-bezier(.22,.61,.36,1);
        box-shadow: 0 12px 28px rgba(10,132,255,.3);
        letter-spacing: .01em;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .auth-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(10,132,255,.42);
    }

    .auth-submit:active { transform: translateY(0); }

    /* ============ DIVIDER ============ */
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 26px 0 20px;
        color: #94a3b8;
        font-size: .75rem;
        text-transform: uppercase;
        letter-spacing: .15em;
        font-weight: 600;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        flex: 1;
        height: 1px;
        background: linear-gradient(90deg, transparent, #e6e9ef, transparent);
    }

    /* ============ SWITCH ============ */
    .auth-switch {
        text-align: center;
        margin: 0;
        font-size: .9rem;
        color: #64748b;
    }

    .auth-switch a {
        color: #0a84ff;
        font-weight: 700;
        transition: color .25s;
    }
    .auth-switch a:hover { color: #7c3aed; }

    /* ============ ALERTS ============ */
    .error, .success {
        text-align: left;
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: .88rem;
        font-weight: 500;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: shakeIn .45s cubic-bezier(.22,.61,.36,1);
    }

    @keyframes shakeIn {
        0%   { opacity: 0; transform: translateX(-8px); }
        50%  { opacity: 1; transform: translateX(4px); }
        100% { opacity: 1; transform: translateX(0); }
    }

    .error {
        background: #fef2f2;
        color: #b91c1c;
        border-color: #fecaca;
    }

    .error::before {
        content: '\f06a';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #dc2626;
        font-size: 1rem;
    }

    .success {
        background: #f0fdf4;
        color: #15803d;
        border-color: #bbf7d0;
    }

    /* ============ BACK HOME LINK ============ */
    .back-home {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 26px;
        color: #64748b;
        font-size: .85rem;
        font-weight: 500;
        transition: color .25s;
    }
    .back-home:hover { color: #0a84ff; }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 900px) {
        .auth-split { grid-template-columns: 1fr; }
        .auth-visual { display: none; }
        .auth-mobile-logo { display: flex; }
        .auth-form-panel { padding: 32px 20px; min-height: 100vh; }
        .auth-card { padding: 36px 28px; }
    }

    @media (max-width: 480px) {
        .auth-card { padding: 30px 22px; border-radius: 20px; }
        .auth-card h1 { font-size: 1.45rem; }
        .auth-form input { padding: 12px 42px 12px 42px; font-size: .9rem; }
    }
    </style>
</head>
<body class="auth-page">

<div class="auth-split">

    <!-- ============ LEFT VISUAL ============ -->
    <aside class="auth-visual">
        <div class="visual-brand">
            <img src="images/log1.jpg" alt="Vaal Solutions">
            <strong>Vaal Solutions</strong>
        </div>

        <div class="visual-content">
            <h2>Welcome back to <span>secure access.</span></h2>
            <p>Sign in to manage your services, review messages, and update your website content — all from one clean dashboard.</p>

            <div class="visual-features">
                <div class="visual-feature">
                    <div class="check"><i class="fa fa-check"></i></div>
                    <span>Real-time content management</span>
                </div>
                <div class="visual-feature">
                    <div class="check"><i class="fa fa-check"></i></div>
                    <span>Secure, encrypted authentication</span>
                </div>
                <div class="visual-feature">
                    <div class="check"><i class="fa fa-check"></i></div>
                    <span>Full admin control from anywhere</span>
                </div>
            </div>
        </div>

        <div class="visual-footer">
            <span>© <?= date('Y') ?> Vaal Solutions (Pty) Ltd</span>
            <a href="index.php">← Back to site</a>
        </div>
    </aside>

    <!-- ============ RIGHT FORM ============ -->
    <main class="auth-form-panel">
        <div class="auth-card">

            <div class="auth-mobile-logo">
                <img src="images/log1.jpg" alt="Vaal Solutions">
            </div>

            <h1>Welcome back</h1>
            <p class="auth-intro">Log in to your account to continue.</p>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="post" class="auth-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-wrap">
                        <i class="fa fa-envelope input-icon"></i>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="you@example.com"
                            autocomplete="email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-options">
                    <label>
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                    <a href="#">Forgot password?</a>
                </div>

                <button class="auth-submit" type="submit">
                    <i class="fa fa-arrow-right-to-bracket"></i> Log in
                </button>
            </form>

            <div class="auth-divider">New here?</div>

            <p class="auth-switch">
                Don't have an account?
                <a href="register.php">Create one now →</a>
            </p>

            <div style="text-align:center">
                <a href="index.php" class="back-home">
                    <i class="fa fa-arrow-left"></i> Back to homepage
                </a>
            </div>
        </div>
    </main>

</div>

<script>
// ---- Password show/hide ----
const togglePwd = document.getElementById('togglePassword');
const pwdInput  = document.getElementById('password');
if (togglePwd && pwdInput) {
    togglePwd.addEventListener('click', () => {
        const isPwd = pwdInput.type === 'password';
        pwdInput.type = isPwd ? 'text' : 'password';
        togglePwd.innerHTML = isPwd
            ? '<i class="fa fa-eye-slash"></i>'
            : '<i class="fa fa-eye"></i>';
    });
}

// ---- Simple form feedback ----
document.getElementById('loginForm').addEventListener('submit', function (e) {
    const email = document.getElementById('email').value.trim();
    const pwd   = document.getElementById('password').value;
    if (!email || !pwd) {
        e.preventDefault();
        // Let native validation handle it
        return;
    }
    const btn = this.querySelector('.auth-submit');
    btn.innerHTML = '<i class="fa fa-circle-notch fa-spin"></i> Signing in...';
    btn.disabled = true;
});
</script>
</body>
</html>