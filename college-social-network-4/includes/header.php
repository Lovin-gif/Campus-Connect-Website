<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/icons.php';
require_login();
$me = current_user($pdo);
$currentPage = basename($_SERVER['SCRIPT_NAME']);

$unreadCount = $pdo->prepare("SELECT COUNT(*) c FROM notifications WHERE user_id = :uid AND is_read = FALSE");
$unreadCount->execute([':uid' => $me['user_id']]);
$unreadCount = (int) $unreadCount->fetch()['c'];
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?? 'Campus Connect' ?> - Campus Connect</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo-mark.svg">
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="app">
<nav class="topnav">
    <a class="brand" href="dashboard.php">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus<strong>Connect</strong></span>
    </a>
    <a href="dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"><?= icon('chat', 'icon nav-icon') ?> Feed</a>
    <a href="events.php" class="<?= $currentPage === 'events.php' ? 'active' : '' ?>"><?= icon('calendar', 'icon nav-icon') ?> Events</a>
    <a href="messages.php" class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>"><?= icon('mail', 'icon nav-icon') ?> Messages</a>
    <?php if ($me['role'] === 'admin'): ?>
        <a href="admin_reports.php" class="<?= $currentPage === 'admin_reports.php' ? 'active' : '' ?>"><?= icon('shield', 'icon nav-icon') ?> Reports</a>
    <?php endif; ?>
    <form action="search.php" method="GET" class="nav-search">
        <?= icon('search', 'icon nav-search-icon') ?>
        <input type="text" name="q" placeholder="Search Campus Connect..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
    </form>
    <span class="spacer"></span>
    <a href="notifications.php" class="nav-bell <?= $currentPage === 'notifications.php' ? 'active' : '' ?>">
        <?= icon('bell', 'icon nav-icon') ?>
        <?php if ($unreadCount > 0): ?><span class="nav-badge"><?= $unreadCount > 9 ? '9+' : $unreadCount ?></span><?php endif; ?>
    </a>
    <span class="me">Hi, <a href="profile.php?id=<?= $me['user_id'] ?>"><?= htmlspecialchars($me['full_name']) ?></a> <span class="role-tag"><?= htmlspecialchars($me['role']) ?></span></span>
    <a href="logout.php" class="logout-link"><?= icon('logout', 'icon nav-icon') ?> Log out</a>
</nav>
<main class="container">
