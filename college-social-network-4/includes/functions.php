<?php
require_once __DIR__ . '/../config/database.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// ------------------------------------------------------------
// Generate a 6-digit OTP, store its hash, and return the plain
// code so the caller can send it via email or SMS.
// ------------------------------------------------------------
function generate_otp(PDO $pdo, int $userId, string $channel): string
{
    $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hash = password_hash($code, PASSWORD_DEFAULT);

    // Expiry is computed by MySQL itself (NOW() + 10 minutes) rather than
    // by PHP, so there's no risk of PHP's and MySQL's clocks/timezones
    // disagreeing about what "10 minutes from now" means.
    $stmt = $pdo->prepare(
        "INSERT INTO otp_verifications (user_id, channel, code_hash, expires_at)
         VALUES (:uid, :channel, :hash, NOW() + INTERVAL 10 MINUTE)"
    );
    $stmt->execute([':uid' => $userId, ':channel' => $channel, ':hash' => $hash]);

    return $code;
}

// ------------------------------------------------------------
// Send the OTP. Stubbed for now — plug in PHPMailer for email
// or a provider like Twilio for SMS. Logs to error_log so the
// flow is testable before those integrations are wired up.
// ------------------------------------------------------------
function send_otp(string $destination, string $channel, string $code): void
{
    error_log("[OTP] Sending $channel code $code to $destination");
    // TODO: replace with real email (PHPMailer) / SMS (Twilio) integration
}

// ------------------------------------------------------------
// Verify a submitted OTP against the most recent unconsumed
// code for that user + channel.
// ------------------------------------------------------------
function verify_otp(PDO $pdo, int $userId, string $channel, string $submittedCode): bool
{
    $stmt = $pdo->prepare(
        "SELECT * FROM otp_verifications
         WHERE user_id = :uid AND channel = :channel
           AND consumed_at IS NULL AND expires_at >= NOW()
         ORDER BY created_at DESC LIMIT 1"
    );
    $stmt->execute([':uid' => $userId, ':channel' => $channel]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($submittedCode, $row['code_hash'])) {
        return false;
    }

    $pdo->prepare("UPDATE otp_verifications SET consumed_at = NOW() WHERE otp_id = :id")
        ->execute([':id' => $row['otp_id']]);

    $column = $channel === 'email' ? 'email_verified_at' : 'phone_verified_at';
    $pdo->prepare("UPDATE users SET $column = NOW() WHERE user_id = :uid")
        ->execute([':uid' => $userId]);

    return true;
}

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function current_user(PDO $pdo): ?array
{
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    return $stmt->fetch() ?: null;
}
