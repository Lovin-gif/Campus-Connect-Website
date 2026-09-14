<?php
$pageTitle = 'Messages';
require_once __DIR__ . '/includes/header.php';

// Send a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send') {
    verify_csrf();
    $receiverId = (int) $_POST['receiver_id'];
    $content = trim($_POST['content'] ?? '');
    if ($content !== '' && $receiverId > 0 && !is_blocked_between($pdo, $me['user_id'], $receiverId)) {
        $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (:s, :r, :c)")
            ->execute([':s' => $me['user_id'], ':r' => $receiverId, ':c' => $content]);
        notify($pdo, $receiverId, $me['full_name'] . ' sent you a message', 'messages.php?with=' . $me['user_id']);
    }
    header('Location: messages.php?with=' . $receiverId);
    exit;
}

// List everyone the user can message: all approved users, minus
// self and anyone blocked in either direction.
$contacts = $pdo->prepare(
    "SELECT user_id, full_name, role FROM users
     WHERE status = 'approved' AND user_id != :me
       AND user_id NOT IN (
           SELECT blocked_id FROM blocks WHERE blocker_id = :me2
           UNION SELECT blocker_id FROM blocks WHERE blocked_id = :me3
       )
     ORDER BY full_name"
);
$contacts->execute([':me' => $me['user_id'], ':me2' => $me['user_id'], ':me3' => $me['user_id']]);
$contacts = $contacts->fetchAll();

$withId = isset($_GET['with']) ? (int) $_GET['with'] : null;
$thread = [];
$blocked = false;
if ($withId) {
    $blocked = is_blocked_between($pdo, $me['user_id'], $withId);
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
            <span><a href="profile.php?id=<?= $c['user_id'] ?>"><?= htmlspecialchars($c['full_name']) ?></a> <span class="badge"><?= htmlspecialchars($c['role']) ?></span></span>
            <a class="btn" href="messages.php?with=<?= $c['user_id'] ?>">Chat</a>
        </div>
    <?php endforeach; ?>
    <?php if (empty($contacts)): ?><p class="meta">No contacts yet.</p><?php endif; ?>
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

    <?php if ($blocked): ?>
        <p class="meta">You can't message this user.</p>
    <?php else: ?>
        <form method="POST" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="send">
            <input type="hidden" name="receiver_id" value="<?= $withId ?>">
            <input type="text" name="content" placeholder="Type a message..." required>
            <button type="submit">Send</button>
        </form>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
