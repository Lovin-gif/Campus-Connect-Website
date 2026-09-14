<?php
$pageTitle = 'Edit Post';
require_once __DIR__ . '/includes/header.php';

$postId = (int) ($_GET['id'] ?? $_POST['post_id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, jd.job_title, jd.location, jd.deadline, jd.apply_link
                        FROM posts p LEFT JOIN job_details jd ON jd.post_id = p.post_id
                        WHERE p.post_id = :pid");
$stmt->execute([':pid' => $postId]);
$post = $stmt->fetch();

if (!$post || (int) $post['user_id'] !== (int) $me['user_id'] || $post['shared_from_post_id']) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($content === '') {
        $error = 'Post content cannot be empty.';
    } else {
        $pdo->prepare("UPDATE posts SET title = :title, content = :content, updated_at = NOW() WHERE post_id = :pid AND user_id = :uid")
            ->execute([':title' => $title, ':content' => $content, ':pid' => $postId, ':uid' => $me['user_id']]);

        if ($post['post_type'] === 'job') {
            $pdo->prepare(
                "UPDATE job_details SET job_title = :jt, location = :loc, deadline = :dl, apply_link = :link WHERE post_id = :pid"
            )->execute([
                ':jt' => trim($_POST['job_title'] ?? ''),
                ':loc' => trim($_POST['location'] ?? ''),
                ':dl' => $_POST['deadline'] ?: null,
                ':link' => trim($_POST['apply_link'] ?? ''),
                ':pid' => $postId,
            ]);
        }
        header('Location: dashboard.php#post-' . $postId);
        exit;
    }
}
?>

<div class="card">
    <h3><?= icon('edit') ?> Edit Post</h3>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="post_id" value="<?= $postId ?>">
        <input type="text" name="title" placeholder="Title (optional)" value="<?= htmlspecialchars($post['title'] ?? '') ?>">
        <textarea name="content" rows="4" required><?= htmlspecialchars($post['content']) ?></textarea>
        <?php if ($post['post_type'] === 'job'): ?>
            <input type="text" name="job_title" placeholder="Job title" value="<?= htmlspecialchars($post['job_title'] ?? '') ?>">
            <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($post['location'] ?? '') ?>">
            <input type="date" name="deadline" value="<?= htmlspecialchars($post['deadline'] ?? '') ?>">
            <input type="url" name="apply_link" placeholder="Apply link" value="<?= htmlspecialchars($post['apply_link'] ?? '') ?>">
        <?php endif; ?>
        <button type="submit">Save Changes</button>
        <a class="btn" href="dashboard.php#post-<?= $postId ?>" style="background:var(--color-border);color:var(--color-foreground)">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
