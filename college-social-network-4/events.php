<?php
$pageTitle = 'Events';
require_once __DIR__ . '/includes/header.php';

$canPost = in_array($me['role'], ['staff', 'admin'], true);

if ($canPost && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $eventDate = $_POST['event_date'] ?? '';
    $location = trim($_POST['location'] ?? '');

    if ($title !== '' && $eventDate !== '') {
        $pdo->prepare(
            "INSERT INTO events (posted_by, title, description, event_date, location, status)
             VALUES (:uid, :title, :desc, :date, :loc, 'approved')"
        )->execute([
            ':uid' => $me['user_id'], ':title' => $title, ':desc' => $description,
            ':date' => $eventDate, ':loc' => $location,
        ]);
        header('Location: /events.php?posted=1');
        exit;
    }
}

$events = $pdo->query(
    "SELECT e.*, u.full_name FROM events e JOIN users u ON u.user_id = e.posted_by
     WHERE e.status = 'approved' ORDER BY e.event_date ASC"
)->fetchAll();
?>

<?php if ($canPost): ?>
<div class="card">
    <h3><?= icon('calendar') ?> Post an Event</h3>
    <?php if (!empty($_GET['posted'])): ?><p class="meta">Your event has been published.</p><?php endif; ?>
    <form method="POST">
        <input type="text" name="title" placeholder="Event title" required>
        <textarea name="description" rows="3" placeholder="Description"></textarea>
        <input type="datetime-local" name="event_date" required>
        <input type="text" name="location" placeholder="Location">
        <button type="submit">Submit Event</button>
    </form>
</div>
<?php endif; ?>

<?php foreach ($events as $e): ?>
    <div class="card">
        <h3><?= htmlspecialchars($e['title']) ?></h3>
        <div class="meta"><?= htmlspecialchars($e['event_date']) ?> · <?= htmlspecialchars($e['location']) ?> · Posted by <?= htmlspecialchars($e['full_name']) ?></div>
        <p><?= nl2br(htmlspecialchars($e['description'])) ?></p>
    </div>
<?php endforeach; ?>
<?php if (empty($events)): ?><p class="meta">No upcoming events.</p><?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
