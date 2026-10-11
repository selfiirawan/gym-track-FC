<?php 

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

$userId = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];
$error = '';

// fetch member amd staff
$me = null;
$staffProfile = null;

if ($role === 'member') {
    $me = getCurrentMember($db);
} else {
    $stmt = $db->prepare("SELECT contact, job_role, status FROM staff_profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $staffProfile = $stmt->fetch(PDO::FETCH_ASSOC);
}

$contact = $me['contact'] ?? $staffProfile['contact'] ?? '';

// fetch admin and email of user
$adStmt = $db->prepare("SELECT name, email, role FROM users WHERE user_id = ?");
$adStmt->execute([$userId]);
$user = $adStmt->fetch(PDO::FETCH_ASSOC);

// submit updated info
if (isset($_POST['save_profile'])) {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];

    try {
        $db->beginTransaction();

        // update user = admin
        $updatedAdmin = $db->prepare("UPDATE users SET name=?, email=? WHERE user_id = ?");
        $updatedAdmin->execute([$name, $email, $userId]);

        if ($me) {
            // update user = member 
            $updatedMember = $db->prepare("UPDATE members SET name=?, email=?, contact=? WHERE user_id = ?");
            $updatedMember->execute([$name, $email, $contact, $userId]);
        } else if ($staffProfile) {
            // update user = staff
            $updatedStaff = $db->prepare("UPDATE staff_profiles SET contact=? WHERE user_id = ?");
            $updatedStaff->execute([$contact, $userId]);
        }

        $db->commit();

        $_SESSION['user']['name'] = $name;
        $_SESSION['user']['email'] = $email;

        header('Location: /profile?saved=1');
        exit;

    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }

        if ($e->getcode() == 23000) {
            $error = 'This email is already in use';
        } else {
            throw $e;
        }
    }
}

// change password
if (isset($_POST['change_pass'])) {
    $pass = $db->prepare("SELECT password_hash FROM users WHERE user_id = ?");
    $pass->execute([$userId]);
    $hassPw = $pass->fetchColumn();

    $newPass = $_POST['new_pass'];
    $confirmPass = $_POST['confirm_pass'];

    if (!password_verify($_POST['curr_pass'], $hassPw)) {
        $error = 'Current password is incorrect!';
    } else if ($newPass !== $confirmPass) {
        $error = 'New passwords do not match!';
    } else {
        $hashNew = password_hash($newPass, PASSWORD_DEFAULT);

        $confirmedNew = $db->prepare("UPDATE users SET password_hash=? WHERE user_id = ?");
        $confirmedNew->execute([$hashNew, $userId]);

        header('Location: /profile?saved=1');
        exit;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymZ Fitness</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <link rel="stylesheet" href="/assets/css/navbar.css">
</head>
<body>

    <div class="d-flex min-vh-100">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <div class="main-content flex-grow-1">
            <!-- navbar -->
            <?php include __DIR__ . '/includes/navbar.php' ?>

            <!-- content -->
            <div class="content px-4">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                    <div class="greeting">
                        <h3>Edit Profile</h3>
                        <p>Update your details</p>
                    </div>
                </div>

                <?php if (isset($_GET['saved'])): ?>
                    <p class="alert alert-success mx-3">Profile updated!</p>
                <?php endif; ?>

                <!-- info to edit and acc details -->
                <div class="parents mx-3 my-4">
                    <div class="profile-form">
                        <p class="fw-bold fs-4">Personal Information</p>

                        <form action="/profile" method="POST">
                            <input type="hidden" name="save_profile" value="1">

                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>

                            <!-- contact and email -->
                            <div class="mb-3 row ">
                                <div class="col">
                                    <label class="form-label" for="contact">Contact No.</label>
                                    <input type="text" id="contact" class="form-control" name="contact" value="<?= htmlspecialchars($contact) ?>" placeholder="0123456789" required>
                                </div>
                                <div class="col">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" id="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="member@gmail.com" required>
                                </div>
                            </div>

                            <!-- button -->
                            <button type="submit" class="btn btn-outline-light mt-4">Save changes</button>
                        </form>
                    </div>

                    <!-- account details -->
                    <div class="details">
                        <p class="fw-bold fs-4">Account details</p>
                        
                        <!-- role -->
                        <div class="d-flex justify-content-between">
                            <p class="text-secondary">Role</p>
                            <p><?= htmlspecialchars(ucfirst($user['role'])) ?></p>
                        </div>

                        <!-- job role -->
                        <div class="d-flex justify-content-between">
                            <p class="text-secondary">Job role</p>
                            <p><?= htmlspecialchars($staffProfile['job_role'] ?? '' ) ?></p>
                        </div>

                        <!-- status -->
                        <div class="d-flex justify-content-between">
                            <p class="text-secondary">Status</p>
                            <p><?= htmlspecialchars(ucfirst($staffProfile['status']) ?? 'Available') ?></p>
                        </div>
                    </div>
                </div>

                <!-- change pass -->
                <div class="password mx-3 my-4">
                    <p class="fw-bold fs-4">Change Password</p>

                    <form action="/profile" method="POST">
                        <input type="hidden" name="change_pass" value="1">

                        <?php if ($error): ?>
                            <p class="alert alert-danger"><?= htmlspecialchars($error) ?></p>
                        <?php endif; ?>

                        <!-- current pass -->
                        <div class="mb-4">
                            <input type="password" name="curr_pass" class="form-control" placeholder="Current password" required>
                        </div>

                        <!-- new pass -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <input type="password" name="new_pass" class="form-control" placeholder="New password" required>
                            </div>

                            <div class="col-6">
                                <input type="password" name="confirm_pass" class="form-control" placeholder="Confirm new password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-outline-light mt-4">Update password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>