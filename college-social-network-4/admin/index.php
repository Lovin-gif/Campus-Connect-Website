<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();
$me = current_user($pdo);
if ($me['role'] !== 'admin') { header('Location: /dashboard.php'); exit; }

$pendingUsers = $pdo->query("SELECT COUNT(*) c FROM users WHERE status = 'pending'")->fetch()['c'];
$pendingPosts = $pdo->query("SELECT COUNT(*) c FROM posts WHERE status = 'pending'")->fetch()['c'];
$pendingEvents = $pdo->query("SELECT COUNT(*) c FROM events WHERE status = 'pending'")->fetch()['c'];
$pageTitle = 'Admin Panel';
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app">
<nav class="topnav">
    <a class="brand" href="/dashboard.php">CSN</a>
    <a href="/dashboard.php">Feed</a>
    <a href="/admin/index.php">Admin Panel</a>
    <span class="spacer"></span>
    <a href="/logout.php">Log out</a>
</nav>
<main class="container">
    <div class="card">
        <h3>Admin Overview</h3>
        <div class="pending-row"><span>Pending user registrations</span><a class="btn" href="/admin/users.php"><?= $pendingUsers ?> to review</a></div>
        <div class="pending-row"><span>Pending posts</span><a class="btn" href="/admin/posts.php"><?= $pendingPosts ?> to review</a></div>
        <div class="pending-row"><span>Pending events</span><a class="btn" href="/admin/events.php"><?= $pendingEvents ?> to review</a></div>
    </div>
</main>
</body>
</html>
