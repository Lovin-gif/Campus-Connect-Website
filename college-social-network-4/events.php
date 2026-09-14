<?php
$pageTitle = 'Events';
require_once __DIR__ . '/includes/header.php';

$canPost = in_array($me['role'], ['staff', 'admin'], true);

if ($canPost && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'new_event') {
    verify_csrf();
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
        header('Location: events.php?posted=1');
        exit;
    }
}

// RSVP to an event (going / interested / remove)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'rsvp') {
    verify_csrf();
    $eventId = (int) $_POST['event_id'];
    $rsvpStatus = $_POST['rsvp_status'] ?? '';

    if ($rsvpStatus === 'none') {
        $pdo->prepare("DELETE FROM event_rsvps WHERE event_id = :eid AND user_id = :uid")
            ->execute([':eid' => $eventId, ':uid' => $me['user_id']]);
    } elseif (in_array($rsvpStatus, ['going', 'interested'], true)) {
        $pdo->prepare(
            "INSERT INTO event_rsvps (event_id, user_id, rsvp_status) VALUES (:eid, :uid, :status)
             ON DUPLICATE KEY UPDATE rsvp_status = :status2"
        )->execute([':eid' => $eventId, ':uid' => $me['user_id'], ':status' => $rsvpStatus, ':status2' => $rsvpStatus]);
    }
    header('Location: events.php#event-' . $eventId);
    exit;
}

$events = $pdo->prepare(
    "SELECT e.*, u.full_name,
            (SELECT COUNT(*) FROM event_rsvps r WHERE r.event_id = e.event_id AND r.rsvp_status = 'going') AS going_count,
            (SELECT COUNT(*) FROM event_rsvps r WHERE r.event_id = e.event_id AND r.rsvp_status = 'interested') AS interested_count,
            (SELECT rsvp_status FROM event_rsvps r WHERE r.event_id = e.event_id AND r.user_id = :me) AS my_rsvp
     FROM events e JOIN users u ON u.user_id = e.posted_by
     WHERE e.status = 'approved' ORDER BY e.event_date ASC"
);
$events->execute([':me' => $me['user_id']]);
$events = $events->fetchAll();
?>

<?php if ($canPost): ?>
<div class="card">
    <h3><?= icon('calendar') ?> Post an Event</h3>
    <?php if (!empty($_GET['posted'])): ?><p class="meta">Your event has been published.</p><?php endif; ?>
    <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="new_event">
        <input type="text" name="title" placeholder="Event title" required>
        <textarea name="description" rows="3" placeholder="Description"></textarea>
        <input type="datetime-local" name="event_date" required>
        <input type="text" name="location" placeholder="Location">
        <button type="submit">Submit Event</button>
    </form>
</div>
<?php endif; ?>

<?php foreach ($events as $e): ?>
    <div class="card" id="event-<?= $e['event_id'] ?>">
        <h3><?= htmlspecialchars($e['title']) ?></h3>
        <div class="meta"><?= htmlspecialchars($e['event_date']) ?> · <?= htmlspecialchars($e['location']) ?> · Posted by <?= htmlspecialchars($e['full_name']) ?></div>
        <p><?= nl2br(htmlspecialchars($e['description'])) ?></p>
        <div class="meta"><?= (int) $e['going_count'] ?> going · <?= (int) $e['interested_count'] ?> interested</div>
        <div class="actions">
            <form method="POST" class="action-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="rsvp">
                <input type="hidden" name="event_id" value="<?= $e['event_id'] ?>">
                <input type="hidden" name="rsvp_status" value="<?= $e['my_rsvp'] === 'going' ? 'none' : 'going' ?>">
                <button type="submit" class="action-btn<?= $e['my_rsvp'] === 'going' ? ' liked' : '' ?>">
                    <?= icon('check-circle') ?> <?= $e['my_rsvp'] === 'going' ? 'Going' : 'I\'m going' ?>
                </button>
            </form>
            <form method="POST" class="action-form">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="rsvp">
                <input type="hidden" name="event_id" value="<?= $e['event_id'] ?>">
                <input type="hidden" name="rsvp_status" value="<?= $e['my_rsvp'] === 'interested' ? 'none' : 'interested' ?>">
                <button type="submit" class="action-btn<?= $e['my_rsvp'] === 'interested' ? ' liked' : '' ?>">
                    <?= icon('star') ?> Interested
                </button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
<?php if (empty($events)): ?><p class="meta">No upcoming events.</p><?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
