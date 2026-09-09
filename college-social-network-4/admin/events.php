<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$me = current_user($pdo);
if ($me['role'] !== 'admin') { header('Location: /dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = (int) $_POST['event_id'];
    $decision = $_POST['decision'] === 'approve' ? 'approved' : 'rejected';
    $pdo->prepare("UPDATE events SET status = :s WHERE event_id = :id")
        ->execute([':s' => $decision, ':id' => $eventId]);
    header('Location: /admin/events.php');
    exit;
}

$pending = $pdo->query(
    "SELECT e.*, u.full_name FROM events e JOIN users u ON u.user_id = e.posted_by
     WHERE e.status = 'pending' ORDER BY e.event_date ASC"
)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Events</title>
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
        <h3>Pending Events</h3>
        <?php foreach ($pending as $e): ?>
            <div class="pending-row">
                <span>
                    <strong><?= htmlspecialchars($e['title']) ?></strong> by <?= htmlspecialchars($e['full_name']) ?>
                    <br><span class="meta"><?= htmlspecialchars($e['event_date']) ?> · <?= htmlspecialchars($e['location']) ?></span>
                </span>
                <span>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="event_id" value="<?= $e['event_id'] ?>">
                        <input type="hidden" name="decision" value="approve">
                        <button type="submit">Approve</button>
                    </form>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="event_id" value="<?= $e['event_id'] ?>">
                        <input type="hidden" name="decision" value="reject">
                        <button type="submit" class="reject">Reject</button>
                    </form>
                </span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($pending)): ?><p class="meta">No pending events.</p><?php endif; ?>
    </div>
</main>
</body>
</html>
