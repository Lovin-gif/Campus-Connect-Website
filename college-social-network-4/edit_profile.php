<?php
$pageTitle = 'Edit Profile';
require_once __DIR__ . '/includes/header.php';

$role = $me['role'];
$roleTable = ['student' => 'student_profiles', 'faculty' => 'faculty_profiles', 'recruiter' => 'recruiter_profiles', 'staff' => 'staff_profiles'][$role] ?? null;
$roleDetails = [];
if ($roleTable) {
    $rd = $pdo->prepare("SELECT * FROM $roleTable WHERE user_id = :id");
    $rd->execute([':id' => $me['user_id']]);
    $roleDetails = $rd->fetch() ?: [];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $fullName = trim($_POST['full_name'] ?? '');
    if ($fullName === '') {
        $error = 'Full name cannot be empty.';
    } else {
        $newAvatar = handle_upload($_FILES['avatar'] ?? [], 'avatars', ['jpg', 'jpeg', 'png', 'gif', 'webp'], 2 * 1024 * 1024, $me['user_id'], 'image');
        $avatarPath = $newAvatar ?? $me['avatar_path'];

        $pdo->prepare("UPDATE users SET full_name = :name, avatar_path = :avatar WHERE user_id = :uid")
            ->execute([':name' => $fullName, ':avatar' => $avatarPath, ':uid' => $me['user_id']]);

        if ($role === 'student') {
            $newResume = handle_upload($_FILES['resume'] ?? [], 'resumes', ['pdf', 'doc', 'docx'], 5 * 1024 * 1024, $me['user_id'], 'document');
            $resumePath = $newResume ?? ($roleDetails['resume_path'] ?? null);
            $pdo->prepare(
                "UPDATE student_profiles SET programme = :prog, year_of_study = :year, bio = :bio, resume_path = :resume WHERE user_id = :uid"
            )->execute([
                ':prog' => trim($_POST['programme'] ?? ''),
                ':year' => (int) ($_POST['year_of_study'] ?? 0),
                ':bio' => trim($_POST['bio'] ?? ''),
                ':resume' => $resumePath,
                ':uid' => $me['user_id'],
            ]);
        } elseif ($role === 'faculty') {
            $pdo->prepare("UPDATE faculty_profiles SET department = :dept, title = :title WHERE user_id = :uid")
                ->execute([':dept' => trim($_POST['department'] ?? ''), ':title' => trim($_POST['title'] ?? ''), ':uid' => $me['user_id']]);
        } elseif ($role === 'recruiter') {
            $pdo->prepare("UPDATE recruiter_profiles SET company_name = :company, company_website = :site, industry = :industry WHERE user_id = :uid")
                ->execute([
                    ':company' => trim($_POST['company_name'] ?? ''),
                    ':site' => trim($_POST['company_website'] ?? ''),
                    ':industry' => trim($_POST['industry'] ?? ''),
                    ':uid' => $me['user_id'],
                ]);
        } elseif ($role === 'staff') {
            $pdo->prepare("UPDATE staff_profiles SET office = :office, position_title = :title WHERE user_id = :uid")
                ->execute([':office' => trim($_POST['office'] ?? ''), ':title' => trim($_POST['position_title'] ?? ''), ':uid' => $me['user_id']]);
        }

        header('Location: profile.php?id=' . $me['user_id']);
        exit;
    }
}
?>

<div class="card">
    <h3><?= icon('edit') ?> Edit Profile</h3>
    <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <label>Profile Photo</label>
        <?php if ($me['avatar_path']): ?>
            <img src="<?= htmlspecialchars($me['avatar_path']) ?>" alt="" class="profile-avatar" style="width:64px;height:64px;margin-bottom:8px;">
        <?php endif; ?>
        <input type="file" name="avatar" accept="image/png,image/jpeg,image/gif,image/webp">
        <p class="form-hint">JPG, PNG, GIF or WebP, up to 2MB.</p>

        <label>Full Name</label>
        <input type="text" name="full_name" value="<?= htmlspecialchars($me['full_name']) ?>" required>

        <?php if ($role === 'student'): ?>
            <label>Programme</label>
            <input type="text" name="programme" value="<?= htmlspecialchars($roleDetails['programme'] ?? '') ?>">
            <label>Year of Study</label>
            <input type="number" name="year_of_study" min="1" max="6" value="<?= htmlspecialchars((string) ($roleDetails['year_of_study'] ?? '')) ?>">
            <label>Short Bio</label>
            <textarea name="bio" rows="4"><?= htmlspecialchars($roleDetails['bio'] ?? '') ?></textarea>
            <label>Resume</label>
            <?php if (!empty($roleDetails['resume_path'])): ?>
                <p class="form-hint"><a href="<?= htmlspecialchars($roleDetails['resume_path']) ?>" target="_blank">Current resume</a> — upload a new file to replace it.</p>
            <?php endif; ?>
            <input type="file" name="resume" accept=".pdf,.doc,.docx">
            <p class="form-hint">PDF or Word document, up to 5MB.</p>
        <?php elseif ($role === 'faculty'): ?>
            <label>Department</label>
            <input type="text" name="department" value="<?= htmlspecialchars($roleDetails['department'] ?? '') ?>">
            <label>Title</label>
            <input type="text" name="title" value="<?= htmlspecialchars($roleDetails['title'] ?? '') ?>">
        <?php elseif ($role === 'recruiter'): ?>
            <label>Company Name</label>
            <input type="text" name="company_name" value="<?= htmlspecialchars($roleDetails['company_name'] ?? '') ?>">
            <label>Company Website</label>
            <input type="url" name="company_website" value="<?= htmlspecialchars($roleDetails['company_website'] ?? '') ?>">
            <label>Industry</label>
            <input type="text" name="industry" value="<?= htmlspecialchars($roleDetails['industry'] ?? '') ?>">
        <?php elseif ($role === 'staff'): ?>
            <label>Office</label>
            <input type="text" name="office" value="<?= htmlspecialchars($roleDetails['office'] ?? '') ?>">
            <label>Position Title</label>
            <input type="text" name="position_title" value="<?= htmlspecialchars($roleDetails['position_title'] ?? '') ?>">
        <?php endif; ?>

        <button type="submit">Save Changes</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
