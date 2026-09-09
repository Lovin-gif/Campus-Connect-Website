<?php
require_once __DIR__ . '/functions.php';
require_login();
$me = current_user($pdo);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?? 'College Social Network' ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app">
<nav class="topnav">
    <a class="brand" href="/dashboard.php">CSN</a>
    <a href="/dashboard.php">Feed</a>
    <a href="/events.php">Events</a>
    <a href="/messages.php">Messages</a>
    <?php if ($me && $me['role'] === 'admin'): ?>
        <a href="/admin/index.php">Admin Panel</a>
    <?php endif; ?>
    <span class="spacer"></span>
    <span class="me">Hi, <?= htmlspecialchars($me['full_name']) ?> (<?= htmlspecialchars($me['role']) ?>)</span>
    <a href="/logout.php">Log out</a>
</nav>
<main class="container">
