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

                <!-- info to edit -->
                <div class="parents">
                    <div class="profile-form">
                        <form action="/profiles" method="POST">
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
                                    <input type="email" id="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" placeholder="member@gmail.com">
                                </div>
                            </div>

                            <!-- button -->
                            <button type="submit" class="btn btn-outline-dark">Save changes</button>
                        </form>
                    </div>

                    <!-- account details -->
                    <div class="details">
                        <p>Account details</p>
                        
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
                            <p><?= htmlspecialchars($staffProfile['status'] ?? 'Available') ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>