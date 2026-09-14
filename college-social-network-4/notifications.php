<?php
$pageTitle = 'Notifications';
require_once __DIR__ . '/includes/header.php';

// Visiting the page marks everything read.
$pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = :uid AND is_read = FALSE")
    ->execute([':uid' => $me['user_id']]);

$notifications = $pdo->prepare(
    "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT 50"
);
$notifications->execute([':uid' => $me['user_id']]);
$notifications = $notifications->fetchAll();
?>

<div class="card">
    <h3><?= icon('bell') ?> Notifications</h3>
    <?php foreach ($notifications as $n): ?>
        <div class="pending-row">
            <?php if ($n['link']): ?>
                <a href="<?= htmlspecialchars($n['link']) ?>"><?= htmlspecialchars($n['message']) ?></a>
            <?php else: ?>
                <span><?= htmlspecialchars($n['message']) ?></span>
            <?php endif; ?>
            <span class="meta"><?= htmlspecialchars($n['created_at']) ?></span>
        </div>
    <?php endforeach; ?>
    <?php if (empty($notifications)): ?><p class="meta">No notifications yet.</p><?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
