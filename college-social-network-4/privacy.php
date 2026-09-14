<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - Campus Connect</title>
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
    <h1>Privacy Policy</h1>
    <p class="updated">Last updated: <?= date('F Y') ?></p>
    <div class="disclaimer">This is a plain-language template, not legal advice. Have a lawyer review and adapt it (including for Fiji's Privacy Act requirements) before a real launch.</div>

    <h2>1. What we collect</h2>
    <p>When you register, we collect your name, email, and optionally your phone number, plus role-specific details (e.g. programme and year of study for students, department for faculty). We also store what you post, comment, like, share, and the messages you send other members.</p>

    <h2>2. How we use it</h2>
    <p>We use your information to run Campus Connect: authenticating you, showing your profile and posts to other members, sending verification codes, and notifying you about activity on your account.</p>

    <h2>3. Who can see it</h2>
    <p>Your profile, posts, and comments are visible to other Campus Connect members. Direct messages are visible only to you and the recipient. We don't sell your data to third parties.</p>

    <h2>4. Uploaded files</h2>
    <p>Profile photos and resumes you upload are stored on our servers and are visible to other members according to where you post them (e.g. a resume is visible to anyone viewing your profile).</p>

    <h2>5. Your choices</h2>
    <p>You can edit or remove your profile information, delete your own posts and comments, and block other members at any time from your account.</p>

    <h2>6. Data retention</h2>
    <p>We keep your information while your account is active. Contact your campus administrator to request account deletion.</p>

    <h2>7. Contact</h2>
    <p>Questions about this policy? Reach out through your campus administrator.</p>
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
