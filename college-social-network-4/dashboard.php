<?php
$pageTitle = 'Feed';
require_once __DIR__ . '/includes/header.php';

// Handle new post submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_post') {
    $postType = in_array($_POST['post_type'] ?? '', ['general', 'job', 'announcement'], true)
        ? $_POST['post_type'] : 'general';
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');

    if ($content !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO posts (user_id, post_type, title, content, status)
             VALUES (:uid, :type, :title, :content, 'approved')"
        );
        $stmt->execute([':uid' => $me['user_id'], ':type' => $postType, ':title' => $title, ':content' => $content]);
        $postId = (int) $pdo->lastInsertId();

        if ($postType === 'job') {
            $pdo->prepare(
                "INSERT INTO job_details (post_id, job_title, location, deadline, apply_link)
                 VALUES (:pid, :jt, :loc, :dl, :link)"
            )->execute([
                ':pid' => $postId,
                ':jt' => trim($_POST['job_title'] ?? ''),
                ':loc' => trim($_POST['location'] ?? ''),
                ':dl' => $_POST['deadline'] ?: null,
                ':link' => trim($_POST['apply_link'] ?? ''),
            ]);
        }
        header('Location: /dashboard.php?posted=1');
        exit;
    }
}

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_comment') {
    $postId = (int) $_POST['post_id'];
    $content = trim($_POST['comment'] ?? '');
    if ($content !== '') {
        $pdo->prepare("INSERT INTO comments (post_id, user_id, content) VALUES (:pid, :uid, :content)")
            ->execute([':pid' => $postId, ':uid' => $me['user_id'], ':content' => $content]);
    }
    header('Location: /dashboard.php#post-' . $postId);
    exit;
}

// Fetch approved posts + author + job details
$posts = $pdo->query(
    "SELECT p.*, u.full_name, u.role, jd.job_title, jd.location, jd.deadline, jd.apply_link
     FROM posts p
     JOIN users u ON u.user_id = p.user_id
     LEFT JOIN job_details jd ON jd.post_id = p.post_id
     WHERE p.status = 'approved'
     ORDER BY p.created_at DESC"
)->fetchAll();

// Fetch comments grouped by post
$commentsByPost = [];
$allComments = $pdo->query(
    "SELECT c.*, u.full_name FROM comments c JOIN users u ON u.user_id = c.user_id ORDER BY c.created_at ASC"
)->fetchAll();
foreach ($allComments as $c) {
    $commentsByPost[$c['post_id']][] = $c;
}
?>

<div class="card">
    <h3>Share an update</h3>
    <?php if (!empty($_GET['posted'])): ?>
        <p class="meta">Your post has been published.</p>
    <?php endif; ?>
    <form method="POST" id="postForm">
        <input type="hidden" name="action" value="new_post">
        <select name="post_type" id="postType" onchange="document.getElementById('jobFields').style.display = this.value === 'job' ? 'block' : 'none'">
            <option value="general">General Post</option>
            <option value="job">Job Update</option>
            <?php if (in_array($me['role'], ['faculty', 'staff', 'admin'], true)): ?>
                <option value="announcement">Announcement</option>
            <?php endif; ?>
        </select>
        <input type="text" name="title" placeholder="Title (optional)">
        <textarea name="content" rows="3" placeholder="What's on your mind?" required></textarea>
        <div id="jobFields" style="display:none">
            <input type="text" name="job_title" placeholder="Job title">
            <input type="text" name="location" placeholder="Location">
            <input type="date" name="deadline">
            <input type="url" name="apply_link" placeholder="Apply link">
        </div>
        <button type="submit">Post</button>
    </form>
</div>

<?php foreach ($posts as $post): ?>
    <div class="card" id="post-<?= $post['post_id'] ?>">
        <div class="meta">
            <?= htmlspecialchars($post['full_name']) ?> · <?= htmlspecialchars($post['role']) ?> ·
            <?= htmlspecialchars($post['created_at']) ?>
            <span class="badge <?= $post['post_type'] ?>"><?= ucfirst($post['post_type']) ?></span>
        </div>
        <?php if ($post['title']): ?><h3><?= htmlspecialchars($post['title']) ?></h3><?php endif; ?>
        <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

        <?php if ($post['post_type'] === 'job' && $post['job_title']): ?>
            <p class="meta">
                <strong><?= htmlspecialchars($post['job_title']) ?></strong>
                <?php if ($post['location']): ?> · <?= htmlspecialchars($post['location']) ?><?php endif; ?>
                <?php if ($post['deadline']): ?> · Deadline: <?= htmlspecialchars($post['deadline']) ?><?php endif; ?>
            </p>
            <?php if ($post['apply_link']): ?>
                <a class="btn" href="<?= htmlspecialchars($post['apply_link']) ?>" target="_blank">Apply</a>
            <?php endif; ?>
        <?php endif; ?>

        <?php foreach ($commentsByPost[$post['post_id']] ?? [] as $c): ?>
            <div class="comment"><strong><?= htmlspecialchars($c['full_name']) ?>:</strong> <?= htmlspecialchars($c['content']) ?></div>
        <?php endforeach; ?>

        <form method="POST" class="inline">
            <input type="hidden" name="action" value="new_comment">
            <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
            <input type="text" name="comment" placeholder="Write a comment...">
            <button type="submit">Reply</button>
        </form>
    </div>
<?php endforeach; ?>

<?php if (empty($posts)): ?>
    <p class="meta">No posts yet.</p>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
