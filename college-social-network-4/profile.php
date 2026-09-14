<?php
$pageTitle = 'Profile';
require_once __DIR__ . '/includes/header.php';

$profileId = (int) ($_GET['id'] ?? 0);
if ($profileId <= 0) $profileId = $me['user_id'];
$isOwnProfile = $profileId === (int) $me['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isOwnProfile) {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'follow') {
        $pdo->prepare("INSERT IGNORE INTO follows (follower_id, followee_id) VALUES (:me, :them)")
            ->execute([':me' => $me['user_id'], ':them' => $profileId]);
        notify($pdo, $profileId, $me['full_name'] . ' started following you', 'profile.php?id=' . $me['user_id']);
    } elseif ($action === 'unfollow') {
        $pdo->prepare("DELETE FROM follows WHERE follower_id = :me AND followee_id = :them")
            ->execute([':me' => $me['user_id'], ':them' => $profileId]);
    } elseif ($action === 'block') {
        $pdo->prepare("INSERT IGNORE INTO blocks (blocker_id, blocked_id) VALUES (:me, :them)")
            ->execute([':me' => $me['user_id'], ':them' => $profileId]);
        // A block also removes any follow relationship between the two.
        $pdo->prepare("DELETE FROM follows WHERE (follower_id = :me AND followee_id = :them) OR (follower_id = :them2 AND followee_id = :me2)")
            ->execute([':me' => $me['user_id'], ':them' => $profileId, ':them2' => $profileId, ':me2' => $me['user_id']]);
    } elseif ($action === 'unblock') {
        $pdo->prepare("DELETE FROM blocks WHERE blocker_id = :me AND blocked_id = :them")
            ->execute([':me' => $me['user_id'], ':them' => $profileId]);
    }
    header('Location: profile.php?id=' . $profileId);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id AND status != 'suspended'");
$stmt->execute([':id' => $profileId]);
$profile = $stmt->fetch();

if (!$profile) {
    header('Location: dashboard.php');
    exit;
}

$roleDetails = null;
$roleTable = ['student' => 'student_profiles', 'faculty' => 'faculty_profiles', 'recruiter' => 'recruiter_profiles', 'staff' => 'staff_profiles'][$profile['role']] ?? null;
if ($roleTable) {
    $rd = $pdo->prepare("SELECT * FROM $roleTable WHERE user_id = :id");
    $rd->execute([':id' => $profileId]);
    $roleDetails = $rd->fetch() ?: null;
}

$iBlockedThem = false;
$theyBlockedMe = false;
if (!$isOwnProfile) {
    $b = $pdo->prepare("SELECT blocker_id FROM blocks WHERE (blocker_id = :me AND blocked_id = :them) OR (blocker_id = :them2 AND blocked_id = :me2)");
    $b->execute([':me' => $me['user_id'], ':them' => $profileId, ':them2' => $profileId, ':me2' => $me['user_id']]);
    foreach ($b->fetchAll() as $row) {
        if ((int) $row['blocker_id'] === (int) $me['user_id']) $iBlockedThem = true;
        else $theyBlockedMe = true;
    }
}

$followerCount = $pdo->prepare("SELECT COUNT(*) c FROM follows WHERE followee_id = :id");
$followerCount->execute([':id' => $profileId]);
$followerCount = (int) $followerCount->fetch()['c'];

$followingCount = $pdo->prepare("SELECT COUNT(*) c FROM follows WHERE follower_id = :id");
$followingCount->execute([':id' => $profileId]);
$followingCount = (int) $followingCount->fetch()['c'];

$amFollowing = false;
if (!$isOwnProfile) {
    $f = $pdo->prepare("SELECT 1 FROM follows WHERE follower_id = :me AND followee_id = :them");
    $f->execute([':me' => $me['user_id'], ':them' => $profileId]);
    $amFollowing = (bool) $f->fetch();
}
?>

<div class="card profile-header">
    <?php if ($profile['avatar_path']): ?>
        <img src="<?= htmlspecialchars($profile['avatar_path']) ?>" alt="" class="profile-avatar">
    <?php else: ?>
        <div class="profile-avatar profile-avatar-fallback"><?= htmlspecialchars(mb_substr($profile['full_name'], 0, 1)) ?></div>
    <?php endif; ?>
    <div class="profile-header-info">
        <h2><?= htmlspecialchars($profile['full_name']) ?> <span class="badge"><?= htmlspecialchars(ucfirst($profile['role'])) ?></span></h2>
        <div class="meta"><?= $followerCount ?> followers · <?= $followingCount ?> following</div>

        <?php if ($theyBlockedMe): ?>
            <p class="meta">You can't view this profile.</p>
        <?php elseif ($isOwnProfile): ?>
            <a class="btn" href="edit_profile.php"><?= icon('edit') ?> Edit Profile</a>
        <?php else: ?>
            <div class="actions">
                <?php if ($iBlockedThem): ?>
                    <form method="POST" class="action-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="unblock">
                        <button type="submit" class="action-btn"><?= icon('ban') ?> Unblock</button>
                    </form>
                <?php else: ?>
                    <form method="POST" class="action-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="<?= $amFollowing ? 'unfollow' : 'follow' ?>">
                        <button type="submit" class="action-btn<?= $amFollowing ? ' liked' : '' ?>">
                            <?= icon($amFollowing ? 'user-check' : 'user-plus') ?> <?= $amFollowing ? 'Following' : 'Follow' ?>
                        </button>
                    </form>
                    <a class="action-form action-btn" href="messages.php?with=<?= $profileId ?>"><?= icon('mail') ?> Message</a>
                    <a class="action-form action-btn" href="report.php?type=user&id=<?= $profileId ?>"><?= icon('flag') ?> Report</a>
                    <form method="POST" class="action-form">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="block">
                        <button type="submit" class="action-btn"><?= icon('ban') ?> Block</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!$theyBlockedMe): ?>
<div class="card">
    <h3>About</h3>
    <?php if ($profile['role'] === 'student' && $roleDetails): ?>
        <p><strong>Programme:</strong> <?= htmlspecialchars($roleDetails['programme'] ?? '—') ?></p>
        <p><strong>Year of study:</strong> <?= htmlspecialchars((string) ($roleDetails['year_of_study'] ?? '—')) ?></p>
        <?php if (!empty($roleDetails['bio'])): ?><p><?= nl2br(htmlspecialchars($roleDetails['bio'])) ?></p><?php endif; ?>
        <?php if (!empty($roleDetails['resume_path'])): ?>
            <p><a class="btn" href="<?= htmlspecialchars($roleDetails['resume_path']) ?>" target="_blank"><?= icon('file') ?> View Resume</a></p>
        <?php endif; ?>
    <?php elseif ($profile['role'] === 'faculty' && $roleDetails): ?>
        <p><strong>Department:</strong> <?= htmlspecialchars($roleDetails['department'] ?? '—') ?></p>
        <p><strong>Title:</strong> <?= htmlspecialchars($roleDetails['title'] ?? '—') ?></p>
    <?php elseif ($profile['role'] === 'recruiter' && $roleDetails): ?>
        <p><strong>Company:</strong> <?= htmlspecialchars($roleDetails['company_name'] ?? '—') ?></p>
        <p><strong>Industry:</strong> <?= htmlspecialchars($roleDetails['industry'] ?? '—') ?></p>
        <?php if (!empty($roleDetails['company_website'])): ?>
            <p><a href="<?= htmlspecialchars($roleDetails['company_website']) ?>" target="_blank"><?= htmlspecialchars($roleDetails['company_website']) ?></a></p>
        <?php endif; ?>
    <?php elseif ($profile['role'] === 'staff' && $roleDetails): ?>
        <p><strong>Office:</strong> <?= htmlspecialchars($roleDetails['office'] ?? '—') ?></p>
        <p><strong>Position:</strong> <?= htmlspecialchars($roleDetails['position_title'] ?? '—') ?></p>
    <?php else: ?>
        <p class="meta">No additional details yet.</p>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
