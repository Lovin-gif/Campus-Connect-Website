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

// Toggle a like on a post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'like') {
    $postId = (int) $_POST['post_id'];
    $existing = $pdo->prepare("SELECT 1 FROM post_likes WHERE post_id = :pid AND user_id = :uid");
    $existing->execute([':pid' => $postId, ':uid' => $me['user_id']]);
    if ($existing->fetch()) {
        $pdo->prepare("DELETE FROM post_likes WHERE post_id = :pid AND user_id = :uid")
            ->execute([':pid' => $postId, ':uid' => $me['user_id']]);
    } else {
        $pdo->prepare("INSERT INTO post_likes (post_id, user_id) VALUES (:pid, :uid)")
            ->execute([':pid' => $postId, ':uid' => $me['user_id']]);
    }
    header('Location: /dashboard.php#post-' . $postId);
    exit;
}

// Share a post: reposts it to the sharer's own feed. Always points
// at the original post, even when sharing something that is itself
// a share, so shares never chain.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'share') {
    $postId = (int) $_POST['post_id'];
    $stmt = $pdo->prepare("SELECT post_id, post_type, shared_from_post_id FROM posts WHERE post_id = :pid");
    $stmt->execute([':pid' => $postId]);
    $original = $stmt->fetch();

    if ($original) {
        $rootId = $original['shared_from_post_id'] ?: $original['post_id'];
        $pdo->prepare(
            "INSERT INTO posts (user_id, post_type, title, content, status, shared_from_post_id)
             VALUES (:uid, :type, NULL, '', 'approved', :root)"
        )->execute([':uid' => $me['user_id'], ':type' => $original['post_type'], ':root' => $rootId]);
    }
    header('Location: /dashboard.php?shared=1#post-' . $postId);
    exit;
}

// Fetch approved posts + author + job details + like/share counts.
// Shares carry no content of their own — orig_* columns pull in the
// original post (and its job details) to render inline.
$posts = $pdo->prepare(
    "SELECT p.*, u.full_name, u.role, jd.job_title, jd.location, jd.deadline, jd.apply_link,
            (SELECT COUNT(*) FROM post_likes pl WHERE pl.post_id = p.post_id) AS like_count,
            EXISTS(SELECT 1 FROM post_likes pl2 WHERE pl2.post_id = p.post_id AND pl2.user_id = :me) AS liked_by_me,
            (SELECT COUNT(*) FROM posts sp WHERE sp.shared_from_post_id = p.post_id) AS share_count,
            op.title AS orig_title, op.content AS orig_content, op.post_type AS orig_post_type,
            op.created_at AS orig_created_at, ou.full_name AS orig_author_name, ou.role AS orig_author_role,
            ojd.job_title AS orig_job_title, ojd.location AS orig_job_location,
            ojd.deadline AS orig_job_deadline, ojd.apply_link AS orig_job_apply_link
     FROM posts p
     JOIN users u ON u.user_id = p.user_id
     LEFT JOIN job_details jd ON jd.post_id = p.post_id
     LEFT JOIN posts op ON op.post_id = p.shared_from_post_id
     LEFT JOIN users ou ON ou.user_id = op.user_id
     LEFT JOIN job_details ojd ON ojd.post_id = op.post_id
     WHERE p.status = 'approved'
     ORDER BY p.created_at DESC"
);
$posts->execute([':me' => $me['user_id']]);
$posts = $posts->fetchAll();

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
    <?php if (!empty($_GET['shared'])): ?>
        <p class="meta">Shared to your feed.</p>
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
        <?php if ($post['shared_from_post_id']): ?>
            <p class="meta"><?= icon('share', 'icon') ?> shared a post</p>
            <div class="shared-card">
                <div class="meta">
                    <?= htmlspecialchars($post['orig_author_name']) ?> · <?= htmlspecialchars($post['orig_author_role']) ?> ·
                    <?= htmlspecialchars($post['orig_created_at']) ?>
                </div>
                <?php if ($post['orig_title']): ?><h3><?= htmlspecialchars($post['orig_title']) ?></h3><?php endif; ?>
                <p><?= nl2br(htmlspecialchars($post['orig_content'])) ?></p>

                <?php if ($post['orig_post_type'] === 'job' && $post['orig_job_title']): ?>
                    <p class="meta">
                        <strong><?= htmlspecialchars($post['orig_job_title']) ?></strong>
                        <?php if ($post['orig_job_location']): ?> · <?= htmlspecialchars($post['orig_job_location']) ?><?php endif; ?>
                        <?php if ($post['orig_job_deadline']): ?> · Deadline: <?= htmlspecialchars($post['orig_job_deadline']) ?><?php endif; ?>
                    </p>
                    <?php if ($post['orig_job_apply_link']): ?>
                        <a class="btn" href="<?= htmlspecialchars($post['orig_job_apply_link']) ?>" target="_blank">Apply</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
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
        <?php endif; ?>

        <div class="actions">
            <form method="POST" class="action-form">
                <input type="hidden" name="action" value="like">
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                <button type="submit" class="action-btn<?= $post['liked_by_me'] ? ' liked' : '' ?>">
                    <?= icon('thumb') ?> <?= $post['liked_by_me'] ? 'Liked' : 'Like' ?><?= $post['like_count'] > 0 ? ' · ' . $post['like_count'] : '' ?>
                </button>
            </form>
            <form method="POST" class="action-form">
                <input type="hidden" name="action" value="share">
                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                <button type="submit" class="action-btn">
                    <?= icon('share') ?> Share<?= $post['share_count'] > 0 ? ' · ' . $post['share_count'] : '' ?>
                </button>
            </form>
        </div>

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
