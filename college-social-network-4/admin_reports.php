<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/includes/header.php';

if ($me['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $reportId = (int) $_POST['report_id'];
    $action = $_POST['action'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM reports WHERE report_id = :id");
    $stmt->execute([':id' => $reportId]);
    $report = $stmt->fetch();

    if ($report) {
        if ($action === 'delete_content') {
            if ($report['target_type'] === 'post') {
                $pdo->prepare("DELETE FROM posts WHERE post_id = :id")->execute([':id' => $report['target_id']]);
            } elseif ($report['target_type'] === 'comment') {
                $pdo->prepare("DELETE FROM comments WHERE comment_id = :id")->execute([':id' => $report['target_id']]);
            } elseif ($report['target_type'] === 'user') {
                $pdo->prepare("UPDATE users SET status = 'suspended' WHERE user_id = :id")->execute([':id' => $report['target_id']]);
            }
            $pdo->prepare("UPDATE reports SET status = 'reviewed' WHERE report_id = :id")->execute([':id' => $reportId]);
        } elseif ($action === 'dismiss') {
            $pdo->prepare("UPDATE reports SET status = 'dismissed' WHERE report_id = :id")->execute([':id' => $reportId]);
        }
    }
    header('Location: admin_reports.php');
    exit;
}

$reports = $pdo->query(
    "SELECT r.*, u.full_name AS reporter_name FROM reports r
     JOIN users u ON u.user_id = r.reporter_id
     WHERE r.status = 'open' ORDER BY r.created_at ASC"
)->fetchAll();

// Pull a short preview of each target so staff don't have to hunt for it.
foreach ($reports as &$r) {
    $r['preview'] = '(content no longer exists)';
    if ($r['target_type'] === 'post') {
        $stmt = $pdo->prepare("SELECT content FROM posts WHERE post_id = :id");
        $stmt->execute([':id' => $r['target_id']]);
        if ($row = $stmt->fetch()) $r['preview'] = mb_strimwidth($row['content'], 0, 120, '...');
    } elseif ($r['target_type'] === 'comment') {
        $stmt = $pdo->prepare("SELECT content FROM comments WHERE comment_id = :id");
        $stmt->execute([':id' => $r['target_id']]);
        if ($row = $stmt->fetch()) $r['preview'] = mb_strimwidth($row['content'], 0, 120, '...');
    } elseif ($r['target_type'] === 'user') {
        $stmt = $pdo->prepare("SELECT full_name FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $r['target_id']]);
        if ($row = $stmt->fetch()) $r['preview'] = $row['full_name'];
    }
}
unset($r);
?>

<div class="card">
    <h3><?= icon('flag') ?> Open Reports</h3>
    <?php foreach ($reports as $r): ?>
        <div class="pending-row" style="flex-direction:column;align-items:stretch;gap:8px">
            <div>
                <span class="badge"><?= htmlspecialchars(ucfirst($r['target_type'])) ?></span>
                reported by <?= htmlspecialchars($r['reporter_name']) ?> · <?= htmlspecialchars($r['created_at']) ?>
            </div>
            <div class="meta">Content: <?= htmlspecialchars($r['preview']) ?></div>
            <div class="meta">Reason: <?= htmlspecialchars($r['reason']) ?></div>
            <div class="actions">
                <form method="POST" class="action-form" onsubmit="return confirm('Delete this content / suspend this user?');">
                    <?= csrf_field() ?>
                    <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                    <input type="hidden" name="action" value="delete_content">
                    <button type="submit" class="action-btn"><?= icon('trash') ?> <?= $r['target_type'] === 'user' ? 'Suspend User' : 'Delete Content' ?></button>
                </form>
                <form method="POST" class="action-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="report_id" value="<?= $r['report_id'] ?>">
                    <input type="hidden" name="action" value="dismiss">
                    <button type="submit" class="action-btn">Dismiss</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($reports)): ?><p class="meta">No open reports.</p><?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
