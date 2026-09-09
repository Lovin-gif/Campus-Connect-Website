<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$me = current_user($pdo);
if ($me['role'] !== 'admin') { header('Location: /dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) $_POST['user_id'];
    $decision = $_POST['decision'] === 'approve' ? 'approved' : 'rejected';
    $pdo->prepare("UPDATE users SET status = :s, approved_by = :me, approved_at = NOW() WHERE user_id = :id")
        ->execute([':s' => $decision, ':me' => $me['user_id'], ':id' => $userId]);
    header('Location: /admin/users.php');
    exit;
}

$pending = $pdo->query(
    "SELECT * FROM users WHERE status = 'pending' ORDER BY created_at ASC"
)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Users</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app">
<nav class="topnav">
    <a class="brand" href="/dashboard.php">CSN</a>
    <a href="/admin/index.php">Admin Panel</a>
    <span class="spacer"></span>
    <a href="/logout.php">Log out</a>
</nav>
<main class="container">
    <div class="card">
        <h3>Pending Registrations</h3>
        <?php foreach ($pending as $u): ?>
            <div class="pending-row">
                <span>
                    <?= htmlspecialchars($u['full_name']) ?>
                    <span class="badge"><?= htmlspecialchars($u['role']) ?></span>
                    <br><span class="meta"><?= htmlspecialchars($u['email']) ?><?= $u['phone_number'] ? ' · ' . htmlspecialchars($u['phone_number']) : '' ?></span>
                </span>
                <span>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <input type="hidden" name="decision" value="approve">
                        <button type="submit">Approve</button>
                    </form>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                        <input type="hidden" name="decision" value="reject">
                        <button type="submit" class="reject">Reject</button>
                    </form>
                </span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($pending)): ?><p class="meta">No pending registrations.</p><?php endif; ?>
    </div>
</main>
</body>
</html>
