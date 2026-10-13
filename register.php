<?php
require_once 'config.php';
if (isset($_SESSION['user_id'])) { header('Location: admin.php'); exit; }

$error = '';
$success = '';
$old = ['name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';
    $old['name']  = $name;
    $old['email'] = $email;

    if ($name === '' || $email === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'This email is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'editor')");
            $stmt->execute([$name, $email, $hash]);
            $success = 'Account created successfully! You can now log in.';
            $old = ['name' => '', 'email' => ''];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Vaal Solutions</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
    /* ============================================================
       AUTH LAYOUT — SPLIT-SCREEN PREMIUM (Register)
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
            radial-gradient(circle at 20% 20%, rgba(124,58,237,.55), transparent 55%),
            radial-gradient(circle at 80% 75%, rgba(10,132,255,.55), transparent 55%),
            linear-gradient(135deg, #1e1b4b 0%, #131a2b 50%, #0b1220 100%);
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 48px 56px;
        overflow: hidden;
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
        top: -180px; left: -180px;
        width: 460px; height: 460px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(236,72,153,.35), transparent 65%);
        filter: blur(30px);
        z-index: -1;
        pointer-events: none;
        animation: floatOrb2 15s ease-in-out infinite alternate;
    }

    @keyframes floatOrb2 {
        0%   { transform: translate(0, 0) scale(1); }
        100% { transform: translate(40px, 40px) scale(1.15); }
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
        background: linear-gradient(135deg, #ffffff, #4fc3f7);
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
        background: linear-gradient(135deg, #ec4899, #a78bfa, #4fc3f7);
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
        background: linear-gradient(135deg, rgba(124,58,237,.35), rgba(236,72,153,.35));
        border: 1px solid rgba(236,72,153,.4);
        color: #f0abfc;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .78rem;
        flex-shrink: 0;
    }

    .visual-steps {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 8px;
    }

    .visual-step {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 14px 16px;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 14px;
        transition: all .3s cubic-bezier(.22,.61,.36,1);
    }

    .visual-step:hover {
        background: rgba(255,255,255,.08);
        border-color: rgba(236,72,153,.35);
        transform: translateX(4px);
    }

    .step-num {
        width: 28px; height: 28px;
        border-radius: 8px;
        background: linear-gradient(135deg, #0a84ff, #7c3aed);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: .8rem;
        flex-shrink: 0;
        font-family: 'Sora', sans-serif;
    }

    .step-text { flex: 1; }
    .step-text strong {
        display: block;
        color: #fff;
        font-size: .9rem;
        font-weight: 600;
        margin-bottom: 2px;
    }
    .step-text small {
        color: rgba(255,255,255,.55);
        font-size: .78rem;
        line-height: 1.4;
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
    .visual-footer a:hover { color: #f0abfc; }

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
        background: radial-gradient(circle, rgba(124,58,237,.08), transparent 65%);
        pointer-events: none;
    }

    .auth-card {
        background: #fff;
        width: 100%;
        max-width: 440px;
        padding: 42px 40px;
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
        box-shadow: 0 10px 30px rgba(124,58,237,.2);
    }

    .auth-card h1 {
        font-family: 'Sora', sans-serif;
        font-size: 1.6rem;
        font-weight: 800;
        letter-spacing: -0.025em;
        margin: 0 0 8px;
        color: #0b1220;
    }

    .auth-intro {
        color: #64748b;
        margin: 0 0 26px;
        font-size: .95rem;
    }

    /* ============ FORM ============ */
    .auth-form .form-group {
        margin-bottom: 16px;
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
        border-color: #7c3aed;
        background: #fff;
        box-shadow: 0 0 0 4px rgba(124,58,237,.12);
    }

    .input-wrap:focus-within .input-icon { color: #7c3aed; }

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

    .toggle-password:hover { color: #7c3aed; background: rgba(124,58,237,.08); }

    /* ============ PASSWORD STRENGTH ============ */
    .pwd-strength {
        margin-top: 8px;
        opacity: 0;
        max-height: 0;
        overflow: hidden;
        transition: all .35s cubic-bezier(.22,.61,.36,1);
    }

    .pwd-strength.show { opacity: 1; max-height: 60px; margin-top: 10px; }

    .strength-bar {
        display: flex;
        gap: 4px;
        margin-bottom: 6px;
    }

    .strength-seg {
        flex: 1;
        height: 4px;
        background: #e6e9ef;
        border-radius: 4px;
        transition: background .3s;
    }

    .strength-seg.active-weak   { background: linear-gradient(90deg, #ef4444, #f87171); }
    .strength-seg.active-fair   { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .strength-seg.active-good   { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .strength-seg.active-strong { background: linear-gradient(90deg, #10b981, #34d399); }

    .strength-label {
        font-size: .75rem;
        font-weight: 600;
        color: #94a3b8;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .strength-label span:last-child {
        font-weight: 700;
        transition: color .3s;
    }

    /* ============ MATCH INDICATOR ============ */
    .match-hint {
        margin-top: 6px;
        font-size: .75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 6px;
        opacity: 0;
        transition: opacity .3s;
    }

    .match-hint.show { opacity: 1; }
    .match-hint.ok   { color: #10b981; }
    .match-hint.bad  { color: #ef4444; }

    /* ============ SUBMIT ============ */
    .auth-submit {
        width: 100%;
        padding: 15px;
        border: none;
        background: linear-gradient(135deg, #7c3aed, #0a84ff);
        color: #fff;
        border-radius: 12px;
        font-size: 1rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        transition: all .3s cubic-bezier(.22,.61,.36,1);
        box-shadow: 0 12px 28px rgba(124,58,237,.3);
        letter-spacing: .01em;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 8px;
    }

    .auth-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 40px rgba(124,58,237,.42);
    }

    .auth-submit:active { transform: translateY(0); }

    /* ============ DIVIDER ============ */
    .auth-divider {
        display: flex;
        align-items: center;
        gap: 14px;
        margin: 22px 0 18px;
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
        color: #7c3aed;
        font-weight: 700;
        transition: color .25s;
    }
    .auth-switch a:hover { color: #0a84ff; }

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
        flex-shrink: 0;
    }

    .success {
        background: #f0fdf4;
        color: #15803d;
        border-color: #bbf7d0;
    }

    .success::before {
        content: '\f058';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        color: #16a34a;
        font-size: 1rem;
        flex-shrink: 0;
    }

    /* ============ BACK HOME LINK ============ */
    .back-home {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 22px;
        color: #64748b;
        font-size: .85rem;
        font-weight: 500;
        transition: color .25s;
    }
    .back-home:hover { color: #7c3aed; }

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
        .auth-card h1 { font-size: 1.4rem; }
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
            <h2>Start your journey with <span>Vaal Solutions.</span></h2>
            <p>Create your account in under a minute and get instant access to your personalized dashboard.</p>

            <div class="visual-steps">
                <div class="visual-step">
                    <div class="step-num">1</div>
                    <div class="step-text">
                        <strong>Create your account</strong>
                        <small>Just name, email and a password</small>
                    </div>
                </div>
                <div class="visual-step">
                    <div class="step-num">2</div>
                    <div class="step-text">
                        <strong>Verify and log in</strong>
                        <small>Instant access — no waiting</small>
                    </div>
                </div>
                <div class="visual-step">
                    <div class="step-num">3</div>
                    <div class="step-text">
                        <strong>Explore your dashboard</strong>
                        <small>Manage everything in one place</small>
                    </div>
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

            <h1>Create your account</h1>
            <p class="auth-intro">Sign up to get started — it only takes a minute.</p>

            <?php if ($error): ?>
                <p class="error"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <?php if ($success): ?>
                <p class="success"><?= htmlspecialchars($success) ?></p>
            <?php endif; ?>

            <form method="post" class="auth-form" id="registerForm" novalidate>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-wrap">
                        <i class="fa fa-user input-icon"></i>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="John Doe"
                            autocomplete="name"
                            value="<?= htmlspecialchars($old['name']) ?>"
                            required
                        >
                    </div>
                </div>

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
                            value="<?= htmlspecialchars($old['email']) ?>"
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
                            placeholder="At least 8 characters"
                            autocomplete="new-password"
                            minlength="8"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="password" aria-label="Show password">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div class="pwd-strength" id="pwdStrength">
                        <div class="strength-bar">
                            <div class="strength-seg" id="seg1"></div>
                            <div class="strength-seg" id="seg2"></div>
                            <div class="strength-seg" id="seg3"></div>
                            <div class="strength-seg" id="seg4"></div>
                        </div>
                        <div class="strength-label">
                            <span>Password strength</span>
                            <span id="strengthText">—</span>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm">Confirm Password</label>
                    <div class="input-wrap">
                        <i class="fa fa-lock input-icon"></i>
                        <input
                            type="password"
                            id="confirm"
                            name="confirm"
                            placeholder="Re-enter your password"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="toggle-password" data-target="confirm" aria-label="Show password">
                            <i class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div class="match-hint" id="matchHint"></div>
                </div>

                <button class="auth-submit" type="submit">
                    <i class="fa fa-user-plus"></i> Create account
                </button>
            </form>

            <div class="auth-divider">Already a member?</div>

            <p class="auth-switch">
                Already have an account?
                <a href="login.php">Log in →</a>
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
/* ============ SHOW / HIDE PASSWORD ============ */
document.querySelectorAll('.toggle-password').forEach(btn => {
    btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.target);
        const isPwd = target.type === 'password';
        target.type = isPwd ? 'text' : 'password';
        btn.innerHTML = isPwd
            ? '<i class="fa fa-eye-slash"></i>'
            : '<i class="fa fa-eye"></i>';
    });
});

/* ============ PASSWORD STRENGTH ============ */
const pwdInput       = document.getElementById('password');
const pwdStrength    = document.getElementById('pwdStrength');
const strengthText   = document.getElementById('strengthText');
const segments       = [
    document.getElementById('seg1'),
    document.getElementById('seg2'),
    document.getElementById('seg3'),
    document.getElementById('seg4')
];

function scorePassword(pw) {
    if (!pw) return 0;
    let score = 0;
    if (pw.length >= 8)  score++;
    if (pw.length >= 12) score++;
    if (/[A-Z]/.test(pw) && /[a-z]/.test(pw)) score++;
    if (/\d/.test(pw)) score++;
    if (/[^A-Za-z0-9]/.test(pw)) score++;
    return Math.min(4, score);
}

function updateStrength() {
    const pw = pwdInput.value;
    if (!pw) {
        pwdStrength.classList.remove('show');
        return;
    }
    pwdStrength.classList.add('show');

    const score = scorePassword(pw);
    const level = ['', 'weak', 'fair', 'good', 'strong'][score] || 'weak';
    const labels = { weak: 'Weak', fair: 'Fair', good: 'Good', strong: 'Strong' };

    segments.forEach((seg, i) => {
        seg.className = 'strength-seg' + (i < score ? ' active-' + level : '');
    });

    strengthText.textContent = labels[level];
    const colors = { weak: '#ef4444', fair: '#f59e0b', good: '#3b82f6', strong: '#10b981' };
    strengthText.style.color = colors[level];
}

pwdInput.addEventListener('input', updateStrength);

/* ============ PASSWORD MATCH ============ */
const confirmInput = document.getElementById('confirm');
const matchHint    = document.getElementById('matchHint');

function checkMatch() {
    const p1 = pwdInput.value;
    const p2 = confirmInput.value;

    if (!p2) { matchHint.classList.remove('show'); return; }

    matchHint.classList.add('show');
    if (p1 === p2) {
        matchHint.className = 'match-hint show ok';
        matchHint.innerHTML = '<i class="fa fa-check-circle"></i> Passwords match';
    } else {
        matchHint.className = 'match-hint show bad';
        matchHint.innerHTML = '<i class="fa fa-circle-xmark"></i> Passwords do not match';
    }
}

confirmInput.addEventListener('input', checkMatch);
pwdInput.addEventListener('input', checkMatch);

/* ============ SUBMIT FEEDBACK ============ */
document.getElementById('registerForm').addEventListener('submit', function (e) {
    const pwd = pwdInput.value;
    const cnf = confirmInput.value;

    if (pwd !== cnf) {
        e.preventDefault();
        return;
    }
    if (pwd.length < 8) {
        e.preventDefault();
        return;
    }
    const btn = this.querySelector('.auth-submit');
    btn.innerHTML = '<i class="fa fa-circle-notch fa-spin"></i> Creating account...';
    btn.disabled = true;
});
</script>
</body>
</html>