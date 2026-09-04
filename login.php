<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $loginId = trim($_POST['login_id'] ?? '');
    $pin = trim($_POST['pin'] ?? '');

    $stmt = $con->prepare('SELECT user_id, name, pin_hash, role FROM users WHERE user_id = ?');
    $stmt->bind_param('s', $loginId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($pin, $user['pin_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];

        header('Location: ' . ($user['role'] === 'admin' ? 'dashboard.php' : 'pos.php'));
        exit();
    }

    $error = 'Incorrect ID or PIN.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in - Banas Hardware POS</title>
    <link rel="icon" href="assets/images/favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="assets/css/base.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/pages/login.css">
</head>
<body class="login-body">
    <div class="login-card">
        <img class="login-logo" src="assets/images/Logo.png" alt="Banas Hardware">
        <h1>Welcome back</h1>
        <p class="login-subtitle">Sign in to Banas Hardware POS</p>

        <?php if ($error !== ''): ?>
            <p class="form-error" role="alert" style="margin-bottom: var(--space-4);"><?= e($error) ?></p>
        <?php endif; ?>

        <form method="post" class="login-form" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="login_id">Login ID</label>
                <input class="input" type="text" id="login_id" name="login_id" placeholder="000-000-000" pattern="\d{3}-\d{3}-\d{3}" autocomplete="off" required>
            </div>
            <div class="field">
                <label for="pin">PIN</label>
                <div class="password-field-wrap">
                    <input class="input" type="password" id="pin" name="pin" placeholder="6-digit PIN" autocomplete="off" required>
                    <button type="button" class="password-toggle" data-toggle-for="pin" aria-label="Show PIN"><?= icon('eye') ?></button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Sign in</button>
        </form>
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>
