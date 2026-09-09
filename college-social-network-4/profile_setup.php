<?php
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['profile_setup_user_id'])) {
    header('Location: register.php');
    exit;
}

$userId = (int) $_SESSION['profile_setup_user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id");
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: register.php');
    exit;
}

$role = $user['role'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        if ($role === 'student') {
            $stmt = $pdo->prepare(
                "INSERT INTO student_profiles (user_id, student_id_no, programme, year_of_study, bio)
                 VALUES (:uid, :sid, :prog, :year, :bio)"
            );
            $stmt->execute([
                ':uid' => $userId,
                ':sid' => trim($_POST['student_id_no'] ?? ''),
                ':prog' => trim($_POST['programme'] ?? ''),
                ':year' => (int) ($_POST['year_of_study'] ?? 0),
                ':bio' => trim($_POST['bio'] ?? ''),
            ]);
        } elseif ($role === 'faculty') {
            $stmt = $pdo->prepare(
                "INSERT INTO faculty_profiles (user_id, staff_id_no, department, title)
                 VALUES (:uid, :sid, :dept, :title)"
            );
            $stmt->execute([
                ':uid' => $userId,
                ':sid' => trim($_POST['staff_id_no'] ?? ''),
                ':dept' => trim($_POST['department'] ?? ''),
                ':title' => trim($_POST['title'] ?? ''),
            ]);
        } elseif ($role === 'recruiter') {
            $stmt = $pdo->prepare(
                "INSERT INTO recruiter_profiles (user_id, company_name, company_website, industry)
                 VALUES (:uid, :company, :site, :industry)"
            );
            $stmt->execute([
                ':uid' => $userId,
                ':company' => trim($_POST['company_name'] ?? ''),
                ':site' => trim($_POST['company_website'] ?? ''),
                ':industry' => trim($_POST['industry'] ?? ''),
            ]);
        } elseif ($role === 'staff') {
            $stmt = $pdo->prepare(
                "INSERT INTO staff_profiles (user_id, office, position_title)
                 VALUES (:uid, :office, :title)"
            );
            $stmt->execute([
                ':uid' => $userId,
                ':office' => trim($_POST['office'] ?? ''),
                ':title' => trim($_POST['position_title'] ?? ''),
            ]);
        }

        $pdo->prepare("UPDATE users SET profile_completed = TRUE WHERE user_id = :uid")
            ->execute([':uid' => $userId]);

        $pdo->commit();

        unset($_SESSION['profile_setup_user_id']);
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = $role;
        header('Location: dashboard.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Could not save your profile. Please check your entries and try again.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Complete Your Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-box">
        <h2>Complete Your Profile</h2>
        <p>Almost done — this information appears on your profile once approved.</p>
        <?php if ($error): ?><p class="error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST">
            <?php if ($role === 'student'): ?>
                <label>Student ID Number</label>
                <input type="text" name="student_id_no" required>
                <label>Programme</label>
                <input type="text" name="programme" placeholder="e.g. Bachelor of Commerce - Information Systems" required>
                <label>Year of Study</label>
                <input type="number" name="year_of_study" min="1" max="6" required>
                <label>Short Bio</label>
                <textarea name="bio" rows="4"></textarea>

            <?php elseif ($role === 'faculty'): ?>
                <label>Staff ID Number</label>
                <input type="text" name="staff_id_no" required>
                <label>Department</label>
                <input type="text" name="department" required>
                <label>Title</label>
                <input type="text" name="title" placeholder="e.g. Senior Lecturer">

            <?php elseif ($role === 'recruiter'): ?>
                <label>Company Name</label>
                <input type="text" name="company_name" required>
                <label>Company Website</label>
                <input type="url" name="company_website">
                <label>Industry</label>
                <input type="text" name="industry">

            <?php elseif ($role === 'staff'): ?>
                <label>Office</label>
                <input type="text" name="office" placeholder="e.g. Career & Placement Office" required>
                <label>Position Title</label>
                <input type="text" name="position_title" required>
            <?php endif; ?>

            <button type="submit">Save Profile & Submit for Approval</button>
        </form>
    </div>
</body>
</html>
