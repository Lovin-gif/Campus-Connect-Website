<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $identifier = trim($_POST['identifier'] ?? ''); // email or phone
    $password   = $_POST['password'] ?? '';

    if ($identifier !== '' && login_is_locked_out($pdo, $identifier)) {
        $error = 'Too many failed attempts. Please wait ' . LOGIN_LOCKOUT_MINUTES . ' minutes and try again.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :id OR phone_number = :id");
        $stmt->execute([':id' => $identifier]);
        $user = $stmt->fetch();

        $passwordOk = $user && password_verify($password, $user['password_hash']);
        record_login_attempt($pdo, $identifier, $passwordOk);

        if (!$passwordOk) {
            $error = 'Incorrect email/phone or password.';
        } elseif (!$user['email_verified_at'] && !$user['phone_verified_at']) {
            $error = 'Please verify your account first.';
            $_SESSION['pending_verification_user_id'] = $user['user_id'];
            $_SESSION['pending_verification_channel'] = 'email';
        } elseif (!$user['profile_completed']) {
            $_SESSION['profile_setup_user_id'] = $user['user_id'];
            header('Location: profile_setup.php');
            exit;
        } else {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Log In - Campus Connect</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo-mark.svg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <a class="auth-brand" href="index.php">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus<strong>Connect</strong></span>
    </a>
    <div class="auth-box">
        <h2>Log In</h2>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <label>Email or Phone Number</label>
            <input type="text" name="identifier" required autofocus autocomplete="username">
            <label>Password</label>
            <input type="password" name="password" required autocomplete="current-password">
            <button type="submit">Log In</button>
        </form>
        <p><a href="forgot_password.php">Forgot your password?</a></p>
        <p>Don't have an account? <a href="register.php">Sign up</a></p>
    </div>
</body>
</html>
