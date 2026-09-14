<?php
require_once __DIR__ . '/includes/functions.php';

const PASSWORD_RESET_MINUTES = 30;

$submitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim($_POST['email'] ?? '');

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $pdo->prepare(
            "INSERT INTO password_resets (user_id, token_hash, expires_at)
             VALUES (:uid, :hash, NOW() + INTERVAL " . PASSWORD_RESET_MINUTES . " MINUTE)"
        )->execute([':uid' => $user['user_id'], ':hash' => hash('sha256', $token)]);

        // Stubbed the same way as OTPs: logged, not actually emailed.
        error_log("[Password Reset] Link for $email: reset_password.php?token=$token");
    }

    // Same message regardless of whether the email exists, so this
    // form can't be used to check who has an account.
    $submitted = true;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - Campus Connect</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo-mark.svg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <a class="auth-brand" href="index.php">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus<strong>Connect</strong></span>
    </a>
    <div class="auth-box">
        <h2>Forgot Password</h2>
        <?php if ($submitted): ?>
            <p class="success">If an account exists for that email, we've sent a link to reset your password.</p>
        <?php else: ?>
            <p>Enter your email and we'll send you a link to reset your password.</p>
            <form method="POST">
                <?= csrf_field() ?>
                <label>Email</label>
                <input type="email" name="email" required autofocus autocomplete="email">
                <button type="submit">Send Reset Link</button>
            </form>
        <?php endif; ?>
        <p><a href="login.php">Back to Log In</a></p>
    </div>
</body>
</html>
