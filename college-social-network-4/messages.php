<?php
$pageTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';

// Send a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    $receiverId = (int) $_POST['receiver_id'];
    $content = trim($_POST['content'] ?? '');
    if ($content !== '' && $receiverId > 0) {
        $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (:s, :r, :c)")
            ->execute([':s' => $me['user_id'], ':r' => $receiverId, ':c' => $content]);
    }
    header('Location: /messages.php?with=' . $receiverId);
    exit;
}

// List everyone the user has approved access to message (all approved users, minus self)
$contacts = $pdo->prepare(
    "SELECT user_id, full_name, role FROM users WHERE status = 'approved' AND user_id != :me ORDER BY full_name"
);
$contacts->execute([':me' => $me['user_id']]);
$contacts = $contacts->fetchAll();

$withId = isset($_GET['with']) ? (int) $_GET['with'] : null;
$thread = [];
if ($withId) {
    $stmt = $pdo->prepare(
        "SELECT m.*, u.full_name AS sender_name FROM messages m
         JOIN users u ON u.user_id = m.sender_id
         WHERE (sender_id = :me AND receiver_id = :w) OR (sender_id = :w AND receiver_id = :me)
         ORDER BY sent_at ASC"
    );
    $stmt->execute([':me' => $me['user_id'], ':w' => $withId]);
    $thread = $stmt->fetchAll();

    // Mark incoming as read
    $pdo->prepare("UPDATE messages SET is_read = TRUE WHERE sender_id = :w AND receiver_id = :me")
        ->execute([':w' => $withId, ':me' => $me['user_id']]);
}
?>

<div class="card">
    <h3><?= icon('network') ?> Contacts</h3>
    <?php foreach ($contacts as $c): ?>
        <div class="pending-row">
            <span><?= htmlspecialchars($c['full_name']) ?> <span class="badge"><?= htmlspecialchars($c['role']) ?></span></span>
            <a class="btn" href="/messages.php?with=<?= $c['user_id'] ?>">Chat</a>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($withId): ?>
<div class="card">
    <h3>Conversation</h3>
    <?php foreach ($thread as $m): ?>
        <div class="comment">
            <strong><?= $m['sender_id'] == $me['user_id'] ? 'You' : htmlspecialchars($m['sender_name']) ?>:</strong>
            <?= htmlspecialchars($m['content']) ?>
            <span class="meta"><?= htmlspecialchars($m['sent_at']) ?></span>
        </div>
    <?php endforeach; ?>
    <?php if (empty($thread)): ?><p class="meta">No messages yet — say hello.</p><?php endif; ?>

    <form method="POST" class="inline">
        <input type="hidden" name="action" value="send">
        <input type="hidden" name="receiver_id" value="<?= $withId ?>">
        <input type="text" name="content" placeholder="Type a message..." required>
        <button type="submit">Send</button>
    </form>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
