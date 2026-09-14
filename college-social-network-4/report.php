<?php
$pageTitle = 'Report Content';
require_once __DIR__ . '/includes/header.php';

$type = $_POST['target_type'] ?? $_GET['type'] ?? '';
$targetId = (int) ($_POST['target_id'] ?? $_GET['id'] ?? 0);
$validTypes = ['post', 'comment', 'user'];
$submitted = false;
$error = '';

if (!in_array($type, $validTypes, true) || $targetId <= 0) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $reason = trim($_POST['reason'] ?? '');
    if ($reason === '') {
        $error = 'Please describe why you\'re reporting this.';
    } else {
        $pdo->prepare("INSERT INTO reports (reporter_id, target_type, target_id, reason) VALUES (:uid, :type, :tid, :reason)")
            ->execute([':uid' => $me['user_id'], ':type' => $type, ':tid' => $targetId, ':reason' => $reason]);
        $submitted = true;
    }
}
?>

<div class="card">
    <h3><?= icon('flag') ?> Report <?= htmlspecialchars(ucfirst($type)) ?></h3>
    <?php if ($submitted): ?>
        <p class="success">Thanks — this has been reported to our team for review.</p>
        <a class="btn" href="dashboard.php">Back to Feed</a>
    <?php else: ?>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <p class="meta">Tell us what's wrong with this <?= htmlspecialchars($type) ?>. Reports are reviewed by staff.</p>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="target_type" value="<?= htmlspecialchars($type) ?>">
            <input type="hidden" name="target_id" value="<?= $targetId ?>">
            <textarea name="reason" rows="4" placeholder="What's the issue? (spam, harassment, inappropriate content, etc.)" required></textarea>
            <button type="submit">Submit Report</button>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
