<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/icons.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Campus Connect - Fiji's Campus Social Network</title>
    <meta name="description" content="Campus Connect brings Fiji's students, faculty, recruiters and staff together in one place to share, network, and find opportunities.">
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
    <a class="nav-link" href="#features">Features</a>
    <a class="nav-link" href="#audience">Who it's for</a>
    <a class="nav-link" href="login.php">Log in</a>
    <a class="btn btn-primary" href="register.php"><?= icon('arrow-right') ?> Sign up</a>
</nav>

<header class="hero">
    <div class="hero-copy">
        <span class="eyebrow"><?= icon('network', 'icon') ?> Built for campuses across Fiji</span>
        <h1>Your campus,<br>now <span class="accent">connected.</span></h1>
        <p class="lead">
            Campus Connect brings students, faculty, recruiters and staff together in one place —
            share updates, find campus events, message your network, and discover job and
            internship opportunities close to home.
        </p>
        <div class="hero-ctas">
            <a class="btn btn-primary" href="register.php"><?= icon('arrow-right') ?> Create your account</a>
            <a class="btn btn-ghost" href="login.php">Log in</a>
        </div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <div class="art-card">
            <div class="art-avatar">A</div>
            <div class="art-body">
                <div class="art-name">Ana Waqa <span style="font-weight:400;color:var(--color-muted-foreground)">· Student</span></div>
                <div class="art-text">Placement interviews at USP career fair this Friday — who's going? 🎓</div>
                <div class="art-actions">
                    <span><?= icon('thumb') ?> 24</span>
                    <span><?= icon('chat') ?> 8</span>
                    <span><?= icon('share') ?> 3</span>
                </div>
            </div>
        </div>
        <div class="art-card">
            <div class="art-avatar" style="background:var(--color-accent-dark)">R</div>
            <div class="art-body">
                <div class="art-name">Ratu Logistics <span style="font-weight:400;color:var(--color-muted-foreground)">· Recruiter</span></div>
                <div class="art-text">We're hiring 3 IT interns for our Nadi office — apply on Campus Connect.</div>
                <div class="art-actions">
                    <span><?= icon('thumb') ?> 41</span>
                    <span><?= icon('chat') ?> 12</span>
                    <span><?= icon('share') ?> 9</span>
                </div>
            </div>
        </div>
    </div>
</header>

<section class="section" id="features">
    <div class="section-head">
        <h2>Everything your campus community needs</h2>
        <p>One network for every role on campus — built to keep students, staff and industry in step with each other.</p>
    </div>
    <div class="feature-grid">
        <div class="feature-card">
            <div class="feature-icon tone-primary"><?= icon('chat', 'icon') ?></div>
            <h3>Campus feed</h3>
            <p>Share updates, ask questions, and keep up with what's happening across your campus — with likes, comments and shares.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon tone-accent"><?= icon('briefcase', 'icon') ?></div>
            <h3>Jobs & internships</h3>
            <p>Recruiters and placement staff post real opportunities for students and graduates, right where students already are.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon tone-pacific"><?= icon('calendar', 'icon') ?></div>
            <h3>Campus events</h3>
            <p>Career fairs, workshops, and campus activities — posted by staff and easy to find in one place.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon tone-primary"><?= icon('mail', 'icon') ?></div>
            <h3>Direct messaging</h3>
            <p>Message any student, lecturer, staff member or recruiter on the network directly — no extra app needed.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon tone-accent"><?= icon('network', 'icon') ?></div>
            <h3>Built for every role</h3>
            <p>Separate, tailored profiles for students, faculty, recruiters and placement staff — everyone gets what's relevant to them.</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon tone-pacific"><?= icon('shield', 'icon') ?></div>
            <h3>A trusted network</h3>
            <p>Sign up with your details and verify your account by email or phone — Campus Connect is built for real campus communities.</p>
        </div>
    </div>
</section>

<section class="section audience-section" id="audience">
    <div class="section-head">
        <h2>Who's already here</h2>
        <p>Whatever your role on campus, Campus Connect has a place for you.</p>
    </div>
    <div class="audience-grid">
        <div class="audience-card">
            <?= icon('network', 'icon') ?>
            <h3>Students</h3>
            <p>Connect with classmates, follow your programme, and find jobs and internships before you graduate.</p>
        </div>
        <div class="audience-card">
            <?= icon('megaphone', 'icon') ?>
            <h3>Faculty</h3>
            <p>Share announcements, post events, and stay connected with your students and department.</p>
        </div>
        <div class="audience-card">
            <?= icon('briefcase', 'icon') ?>
            <h3>Recruiters</h3>
            <p>Reach students and graduates directly with job and internship posts tailored to your industry.</p>
        </div>
        <div class="audience-card">
            <?= icon('calendar', 'icon') ?>
            <h3>Placement Staff</h3>
            <p>Organise campus events, coordinate with recruiters, and keep students in the loop.</p>
        </div>
    </div>
</section>

<div class="cta-band">
    <h2>Join your campus community today</h2>
    <p>It only takes a couple of minutes to get started.</p>
    <a class="btn btn-primary" href="register.php"><?= icon('arrow-right') ?> Create your free account</a>
</div>

<footer class="site-footer">
    <div class="brand">
        <img src="assets/img/logo-mark.svg" alt="">
        <span>Campus Connect</span>
    </div>
    <div class="footer-links">
        <a href="login.php">Log in</a>
        <a href="register.php">Sign up</a>
        <a href="#features">Features</a>
    </div>
    <div>&copy; <?= date('Y') ?> Campus Connect, Fiji.</div>
</footer>

</body>
</html>
