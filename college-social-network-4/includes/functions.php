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
const OTP_MAX_ATTEMPTS = 5;

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

    // A code that's been guessed at too many times is dead even if
    // it's still within its 10-minute window — the user must resend.
    if (!$row || $row['attempt_count'] >= OTP_MAX_ATTEMPTS) {
        return false;
    }

    if (!password_verify($submittedCode, $row['code_hash'])) {
        $pdo->prepare("UPDATE otp_verifications SET attempt_count = attempt_count + 1 WHERE otp_id = :id")
            ->execute([':id' => $row['otp_id']]);
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
        header('Location: login.php');
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

// ------------------------------------------------------------
// CSRF protection. Every state-changing form includes csrf_field();
// verify_csrf() must run before acting on any POST request.
// ------------------------------------------------------------
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        exit('Your session expired or this form was submitted from an untrusted source. Please go back and try again.');
    }
}

// ------------------------------------------------------------
// Login rate limiting. Counts recent failures for an identifier
// rather than keeping a running counter, so the window resets
// naturally as old attempts age out.
// ------------------------------------------------------------
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCKOUT_MINUTES = 15;

function login_is_locked_out(PDO $pdo, string $identifier): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) c FROM login_attempts
         WHERE identifier = :id AND succeeded = FALSE
           AND created_at >= NOW() - INTERVAL " . LOGIN_LOCKOUT_MINUTES . " MINUTE"
    );
    $stmt->execute([':id' => $identifier]);
    return (int) $stmt->fetch()['c'] >= LOGIN_MAX_ATTEMPTS;
}

function record_login_attempt(PDO $pdo, string $identifier, bool $succeeded): void
{
    $pdo->prepare("INSERT INTO login_attempts (identifier, succeeded) VALUES (:id, :ok)")
        ->execute([':id' => $identifier, ':ok' => $succeeded ? 1 : 0]);
}

// ------------------------------------------------------------
// In-app notifications, with an optional link to the relevant page.
// ------------------------------------------------------------
function notify(PDO $pdo, int $userId, string $message, ?string $link = null): void
{
    // Never notify someone about their own action.
    if ($userId === (int) ($_SESSION['user_id'] ?? 0)) return;
    $pdo->prepare("INSERT INTO notifications (user_id, message, link) VALUES (:uid, :msg, :link)")
        ->execute([':uid' => $userId, ':msg' => $message, ':link' => $link]);
}

// ------------------------------------------------------------
// True if either user has blocked the other. Used to hide content
// and disable messaging between blocked pairs.
// ------------------------------------------------------------
function is_blocked_between(PDO $pdo, int $userA, int $userB): bool
{
    $stmt = $pdo->prepare(
        "SELECT 1 FROM blocks WHERE (blocker_id = :a AND blocked_id = :b) OR (blocker_id = :b AND blocked_id = :a) LIMIT 1"
    );
    $stmt->execute([':a' => $userA, ':b' => $userB]);
    return (bool) $stmt->fetch();
}

// ------------------------------------------------------------
// Validate and store an uploaded file under uploads/<subdir>/,
// named by user id + a random suffix (never the original filename,
// so nothing user-controlled reaches the filesystem path). Returns
// the relative path to store in the DB, or null if the upload was
// missing/invalid. $kind 'image' additionally verifies the file is
// a real image via getimagesize(), regardless of its extension.
// ------------------------------------------------------------
function handle_upload(array $file, string $subdir, array $allowedExt, int $maxBytes, int $userId, string $kind = 'document'): ?string
{
    if (empty($file['name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] <= 0 || $file['size'] > $maxBytes) {
        return null;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return null;
    }
    if ($kind === 'image' && @getimagesize($file['tmp_name']) === false) {
        return null;
    }

    $dir = __DIR__ . '/../uploads/' . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $filename = $userId . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
        return null;
    }

    return 'uploads/' . $subdir . '/' . $filename;
}
