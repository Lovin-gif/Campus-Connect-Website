<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service - Campus Connect</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo-mark.svg">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>
<nav class="site-nav">
    <a class="brand" href="index.php">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus<strong>Connect</strong></span>
    </a>
    <span class="spacer"></span>
    <a class="nav-link" href="login.php">Log in</a>
    <a class="btn btn-primary" href="register.php">Sign up</a>
</nav>

<div class="legal-content">
    <h1>Terms of Service</h1>
    <p class="updated">Last updated: <?= date('F Y') ?></p>
    <div class="disclaimer">This is a plain-language template, not legal advice. Have a lawyer review and adapt it (including for Fiji's Online Safety Act and Privacy Act requirements) before a real launch.</div>

    <h2>1. Who can use Campus Connect</h2>
    <p>Campus Connect is for students, faculty, recruiters, and placement staff connected to participating campuses in Fiji. You must provide accurate information when you register and keep your account credentials to yourself.</p>

    <h2>2. Your content</h2>
    <p>You keep ownership of what you post. By posting, you allow other members to view, comment on, like, and share it within Campus Connect. You're responsible for what you post — don't share anything you don't have the right to share.</p>

    <h2>3. Acceptable use</h2>
    <p>Don't use Campus Connect to harass, threaten, or discriminate against others; post spam or misleading job listings; impersonate someone else; or share unlawful content. See our <a href="guidelines.php">Community Guidelines</a> for details.</p>

    <h2>4. Moderation</h2>
    <p>We may remove content or suspend accounts that violate these terms or our Community Guidelines, including in response to reports from other members.</p>

    <h2>5. Account termination</h2>
    <p>You may stop using Campus Connect at any time. We may suspend or terminate accounts that violate these terms.</p>

    <h2>6. Disclaimer</h2>
    <p>Campus Connect is provided "as is." Job and event postings are made by members and third parties — we don't guarantee their accuracy.</p>

    <h2>7. Contact</h2>
    <p>Questions about these terms? Reach out through your campus administrator.</p>
</div>

<footer class="site-footer">
    <div class="brand">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus Connect</span>
    </div>
    <div class="footer-links">
        <a href="terms.php">Terms</a>
        <a href="privacy.php">Privacy</a>
        <a href="guidelines.php">Guidelines</a>
    </div>
    <div>&copy; <?= date('Y') ?> Campus Connect, Fiji.</div>
</footer>
</body>
</html>
