<?php
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['pending_verification_user_id'])) {
    header('Location: register.php');
    exit;
}

$userId  = (int) $_SESSION['pending_verification_user_id'];
$channel = $_SESSION['pending_verification_channel'];
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');

    if (verify_otp($pdo, $userId, $channel, $code)) {
        unset($_SESSION['pending_verification_user_id'], $_SESSION['pending_verification_channel']);
        $_SESSION['profile_setup_user_id'] = $userId;
        header('Location: profile_setup.php');
        exit;
    }
    $error = 'That code is incorrect or has expired.';
}

if (isset($_GET['resend'])) {
    $stmt = $pdo->prepare("SELECT email, phone_number FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $userId]);
    $user = $stmt->fetch();
    $destination = $channel === 'email' ? $user['email'] : $user['phone_number'];
    $code = generate_otp($pdo, $userId, $channel);
    send_otp($destination, $channel, $code);
    $error = 'A new code has been sent.';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Account - Campus Connect</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo-mark.svg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <a class="auth-brand" href="index.php">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus<strong>Connect</strong></span>
    </a>
    <div class="auth-box">
        <h2>Verify Your <?= $channel === 'email' ? 'Email' : 'Phone Number' ?></h2>
        <p>We sent a 6-digit code to your <?= $channel ?>. Enter it below to continue.</p>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <label>Verification Code</label>
            <input type="text" name="code" maxlength="6" required autofocus>
            <button type="submit">Verify</button>
        </form>
        <p><a href="verify.php?resend=1">Resend code</a></p>
    </div>
</body>
</html>
