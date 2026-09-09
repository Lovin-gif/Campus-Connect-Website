<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$me = current_user($pdo);
if ($me['role'] !== 'admin') { header('Location: /dashboard.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postId = (int) $_POST['post_id'];
    $decision = $_POST['decision'] === 'approve' ? 'approved' : 'rejected';
    $pdo->prepare("UPDATE posts SET status = :s, reviewed_by = :me WHERE post_id = :id")
        ->execute([':s' => $decision, ':me' => $me['user_id'], ':id' => $postId]);
    header('Location: /admin/posts.php');
    exit;
}

$pending = $pdo->query(
    "SELECT p.*, u.full_name FROM posts p JOIN users u ON u.user_id = p.user_id
     WHERE p.status = 'pending' ORDER BY p.created_at ASC"
)->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Posts</title>
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
        <h3>Pending Posts</h3>
        <?php foreach ($pending as $p): ?>
            <div class="pending-row">
                <span>
                    <strong><?= htmlspecialchars($p['full_name']) ?></strong>
                    <span class="badge <?= $p['post_type'] ?>"><?= ucfirst($p['post_type']) ?></span>
                    <br><?= htmlspecialchars(mb_strimwidth($p['content'], 0, 100, '...')) ?>
                </span>
                <span>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="post_id" value="<?= $p['post_id'] ?>">
                        <input type="hidden" name="decision" value="approve">
                        <button type="submit">Approve</button>
                    </form>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="post_id" value="<?= $p['post_id'] ?>">
                        <input type="hidden" name="decision" value="reject">
                        <button type="submit" class="reject">Reject</button>
                    </form>
                </span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($pending)): ?><p class="meta">No pending posts.</p><?php endif; ?>
    </div>
</main>
</body>
</html>
