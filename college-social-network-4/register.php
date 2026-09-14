<?php
require_once __DIR__ . '/includes/functions.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $role     = $_POST['role'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '') ?: null; // optional
    $password = $_POST['password'] ?? '';
    $agreed   = isset($_POST['agree_terms']);

    $validRoles = ['student', 'faculty', 'recruiter', 'staff'];

    if (!in_array($role, $validRoles, true)) {
        $error = 'Please select a valid account type.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!$agreed) {
        $error = 'You must agree to the Terms and Community Guidelines to sign up.';
    } else {
        // Check for existing email/phone
        $check = $pdo->prepare("SELECT user_id FROM users WHERE email = :email OR (phone_number IS NOT NULL AND phone_number = :phone)");
        $check->execute([':email' => $email, ':phone' => $phone]);

        if ($check->fetch()) {
            $error = 'An account with this email or phone number already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (role, full_name, email, phone_number, password_hash, status)
                 VALUES (:role, :name, :email, :phone, :hash, 'approved')"
            );
            $stmt->execute([
                ':role' => $role, ':name' => $fullName, ':email' => $email,
                ':phone' => $phone, ':hash' => $hash,
            ]);
            $userId = (int) $pdo->lastInsertId();

            // First-time authentication: verify via email (primary),
            // or phone if the user provided one instead/as well.
            $channel = 'email';
            $destination = $email;
            $code = generate_otp($pdo, $userId, $channel);
            send_otp($destination, $channel, $code);

            $_SESSION['pending_verification_user_id'] = $userId;
            $_SESSION['pending_verification_channel'] = $channel;

            header('Location: verify.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Campus Connect</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo-mark.svg">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <a class="auth-brand" href="index.php">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus<strong>Connect</strong></span>
    </a>
    <div class="auth-box">
        <h2>Create an Account</h2>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <?= csrf_field() ?>
            <label>Account Type</label>
            <select name="role" required>
                <option value="">-- Select --</option>
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
                <option value="recruiter">Recruiter</option>
                <option value="staff">Placement Officer / Staff</option>
            </select>

            <label>Full Name</label>
            <input type="text" name="full_name" required autocomplete="name">

            <label>Email</label>
            <input type="email" name="email" required autocomplete="email">

            <label>Phone Number (optional)</label>
            <input type="tel" name="phone" placeholder="e.g. +679 XXXXXXX" autocomplete="tel">

            <label>Password</label>
            <input type="password" name="password" minlength="8" required autocomplete="new-password">

            <label class="checkbox-label">
                <input type="checkbox" name="agree_terms" required>
                I agree to the <a href="terms.php" target="_blank">Terms</a> and
                <a href="guidelines.php" target="_blank">Community Guidelines</a>
            </label>

            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Log in</a></p>
    </div>
</body>
</html>
