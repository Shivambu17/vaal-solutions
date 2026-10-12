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
</head>
<body class="auth-page">
    <div class="logo"><img src="images/logo.jpg" alt="Logo"></div>
    <main class="auth-card">
        <h1>Welcome back</h1>
        <p class="auth-intro">Log in to your account.</p>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="post" class="auth-form">
            <div class="form-group">
                <label for="email">Email address</label>
                <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" autocomplete="current-password" required>
            </div>
            <button class="auth-submit" type="submit">Log in</button>
            <p class="auth-switch">Don't have an account? <a href="register.php">Sign up</a></p>
        </form>
    </main>
</body>
</html>