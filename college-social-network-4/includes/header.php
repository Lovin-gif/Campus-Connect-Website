<?php
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/icons.php';
require_login();
$me = current_user($pdo);
$currentPage = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= $pageTitle ?? 'Campus Connect' ?> - Campus Connect</title>
    <link rel="icon" type="image/svg+xml" href="/assets/img/logo-mark.svg">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body class="app">
<nav class="topnav">
    <a class="brand" href="/dashboard.php">
        <img src="/assets/img/logo-mark.svg" alt="">
        <span>Campus<strong>Connect</strong></span>
    </a>
    <a href="/dashboard.php" class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>"><?= icon('chat', 'icon nav-icon') ?> Feed</a>
    <a href="/events.php" class="<?= $currentPage === 'events.php' ? 'active' : '' ?>"><?= icon('calendar', 'icon nav-icon') ?> Events</a>
    <a href="/messages.php" class="<?= $currentPage === 'messages.php' ? 'active' : '' ?>"><?= icon('mail', 'icon nav-icon') ?> Messages</a>
    <span class="spacer"></span>
    <span class="me">Hi, <?= htmlspecialchars($me['full_name']) ?> <span class="role-tag"><?= htmlspecialchars($me['role']) ?></span></span>
    <a href="/logout.php" class="logout-link"><?= icon('logout', 'icon nav-icon') ?> Log out</a>
</nav>
<main class="container">
