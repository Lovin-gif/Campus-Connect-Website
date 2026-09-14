<?php
$pageTitle = 'Edit Comment';
require_once __DIR__ . '/includes/header.php';

$commentId = (int) ($_GET['id'] ?? $_POST['comment_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM comments WHERE comment_id = :cid");
$stmt->execute([':cid' => $commentId]);
$comment = $stmt->fetch();

if (!$comment || (int) $comment['user_id'] !== (int) $me['user_id']) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $content = trim($_POST['content'] ?? '');
    if ($content === '') {
        $error = 'Comment cannot be empty.';
    } else {
        $pdo->prepare("UPDATE comments SET content = :content, updated_at = NOW() WHERE comment_id = :cid AND user_id = :uid")
            ->execute([':content' => $content, ':cid' => $commentId, ':uid' => $me['user_id']]);
        header('Location: dashboard.php#post-' . $comment['post_id']);
        exit;
    }
}
?>

<div class="card">
    <h3><?= icon('edit') ?> Edit Comment</h3>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="comment_id" value="<?= $commentId ?>">
        <textarea name="content" rows="3" required><?= htmlspecialchars($comment['content']) ?></textarea>
        <button type="submit">Save Changes</button>
        <a class="btn" href="dashboard.php#post-<?= $comment['post_id'] ?>" style="background:var(--color-border);color:var(--color-foreground)">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
