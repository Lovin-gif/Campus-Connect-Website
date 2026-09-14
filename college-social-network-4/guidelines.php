<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Community Guidelines - Campus Connect</title>
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
    <h1>Community Guidelines</h1>
    <p class="updated">Last updated: <?= date('F Y') ?></p>

    <h2>Be respectful</h2>
    <p>Campus Connect is a shared space for students, faculty, recruiters, and staff. Treat others the way you'd want to be treated — no harassment, hate speech, or discrimination based on race, religion, gender, disability, or any other personal characteristic.</p>

    <h2>Post real, relevant content</h2>
    <p>Share genuine updates, questions, and opportunities. Don't post spam, scams, or misleading job listings. Job posts should be real opportunities you're authorized to advertise.</p>

    <h2>Be who you say you are</h2>
    <p>Don't impersonate another person, organisation, or institution. Recruiter accounts should represent an actual employer.</p>

    <h2>Keep it appropriate</h2>
    <p>Don't post unlawful, violent, sexually explicit, or otherwise inappropriate content. This is a professional and academic community.</p>

    <h2>Respect privacy</h2>
    <p>Don't share someone else's private information (contact details, personal photos, etc.) without their permission.</p>

    <h2>Reporting</h2>
    <p>Use the <strong>Report</strong> button on a post, comment, or profile to flag anything that breaks these guidelines. You can also <strong>Block</strong> a member to stop seeing their content and messages. Our team reviews reports and may remove content or suspend accounts that violate these guidelines.</p>
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
