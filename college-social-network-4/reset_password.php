<?php
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = '';
$done = false;

$reset = null;
if ($token !== '') {
    $stmt = $pdo->prepare(
        "SELECT * FROM password_resets WHERE token_hash = :hash AND used_at IS NULL AND expires_at >= NOW()
         ORDER BY created_at DESC LIMIT 1"
    );
    $stmt->execute([':hash' => hash('sha256', $token)]);
    $reset = $stmt->fetch();
}

if (!$reset) {
    $error = 'This link is invalid or has expired. Please request a new one.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $password = $_POST['password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :uid")
            ->execute([':hash' => $hash, ':uid' => $reset['user_id']]);
        $pdo->prepare("UPDATE password_resets SET used_at = NOW() WHERE reset_id = :id")
            ->execute([':id' => $reset['reset_id']]);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - Campus Connect</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo-mark.svg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <a class="auth-brand" href="index.php">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus<strong>Connect</strong></span>
    </a>
    <div class="auth-box">
        <h2>Reset Password</h2>
        <?php if ($done): ?>
            <p class="success">Your password has been reset.</p>
            <p><a href="login.php">Log In</a></p>
        <?php elseif ($error && !$reset): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
            <p><a href="forgot_password.php">Request a new link</a></p>
        <?php else: ?>
            <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <label>New Password</label>
                <input type="password" name="password" minlength="8" required autofocus autocomplete="new-password">
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
