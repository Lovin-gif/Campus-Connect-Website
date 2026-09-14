<?php
$pageTitle = 'Search';
require_once __DIR__ . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$people = $posts = $events = [];

if ($q !== '') {
    $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $q) . '%';

    $people = $pdo->prepare(
        "SELECT u.user_id, u.full_name, u.role,
                sp.programme, fp.department, rp.company_name, stp.office
         FROM users u
         LEFT JOIN student_profiles sp ON sp.user_id = u.user_id
         LEFT JOIN faculty_profiles fp ON fp.user_id = u.user_id
         LEFT JOIN recruiter_profiles rp ON rp.user_id = u.user_id
         LEFT JOIN staff_profiles stp ON stp.user_id = u.user_id
         WHERE u.status = 'approved' AND u.user_id != :me
           AND u.user_id NOT IN (
               SELECT blocked_id FROM blocks WHERE blocker_id = :me2
               UNION SELECT blocker_id FROM blocks WHERE blocked_id = :me3
           )
           AND (u.full_name LIKE :q1 OR sp.programme LIKE :q2 OR fp.department LIKE :q3
                OR rp.company_name LIKE :q4 OR stp.office LIKE :q5)
         ORDER BY u.full_name LIMIT 20"
    );
    $people->execute([':me' => $me['user_id'], ':me2' => $me['user_id'], ':me3' => $me['user_id'],
        ':q1' => $like, ':q2' => $like, ':q3' => $like, ':q4' => $like, ':q5' => $like]);
    $people = $people->fetchAll();

    $posts = $pdo->prepare(
        "SELECT p.post_id, p.title, p.content, p.created_at, u.full_name
         FROM posts p JOIN users u ON u.user_id = p.user_id
         WHERE p.status = 'approved' AND (p.title LIKE :q1 OR p.content LIKE :q2)
           AND p.user_id NOT IN (
               SELECT blocked_id FROM blocks WHERE blocker_id = :me
               UNION SELECT blocker_id FROM blocks WHERE blocked_id = :me2
           )
         ORDER BY p.created_at DESC LIMIT 20"
    );
    $posts->execute([':q1' => $like, ':q2' => $like, ':me' => $me['user_id'], ':me2' => $me['user_id']]);
    $posts = $posts->fetchAll();

    $events = $pdo->prepare(
        "SELECT event_id, title, description, event_date, location FROM events
         WHERE status = 'approved' AND (title LIKE :q1 OR description LIKE :q2)
         ORDER BY event_date ASC LIMIT 20"
    );
    $events->execute([':q1' => $like, ':q2' => $like]);
    $events = $events->fetchAll();
}
?>

<?php if ($q === ''): ?>
    <p class="meta">Search for people, posts, or events above.</p>
<?php else: ?>
    <div class="card">
        <h3><?= icon('network') ?> People</h3>
        <?php foreach ($people as $p): ?>
            <div class="pending-row">
                <span><a href="profile.php?id=<?= $p['user_id'] ?>"><?= htmlspecialchars($p['full_name']) ?></a>
                    <span class="badge"><?= htmlspecialchars(ucfirst($p['role'])) ?></span>
                    <?php $sub = $p['programme'] ?? $p['department'] ?? $p['company_name'] ?? $p['office'] ?? null; ?>
                    <?php if ($sub): ?><span class="meta"><?= htmlspecialchars($sub) ?></span><?php endif; ?>
                </span>
                <a class="btn" href="profile.php?id=<?= $p['user_id'] ?>">View</a>
            </div>
        <?php endforeach; ?>
        <?php if (empty($people)): ?><p class="meta">No people found.</p><?php endif; ?>
    </div>

    <div class="card">
        <h3><?= icon('chat') ?> Posts</h3>
        <?php foreach ($posts as $p): ?>
            <div class="pending-row">
                <span><a href="dashboard.php#post-<?= $p['post_id'] ?>"><?= htmlspecialchars($p['title'] ?: mb_strimwidth($p['content'], 0, 60, '...')) ?></a>
                    <span class="meta">by <?= htmlspecialchars($p['full_name']) ?></span>
                </span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($posts)): ?><p class="meta">No posts found.</p><?php endif; ?>
    </div>

    <div class="card">
        <h3><?= icon('calendar') ?> Events</h3>
        <?php foreach ($events as $e): ?>
            <div class="pending-row">
                <span><a href="events.php#event-<?= $e['event_id'] ?>"><?= htmlspecialchars($e['title']) ?></a>
                    <span class="meta"><?= htmlspecialchars($e['event_date']) ?> · <?= htmlspecialchars($e['location']) ?></span>
                </span>
            </div>
        <?php endforeach; ?>
        <?php if (empty($events)): ?><p class="meta">No events found.</p><?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
