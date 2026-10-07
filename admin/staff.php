<?php 

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

if (!isAdmin()) {
    header('Location: /dashboard');
    exit;
}

preventCaching();

$searchStaff = $_GET['s'] ?? '';
$searchResult = [];

if ($searchStaff !== '') {
    $stmt = $db->prepare("
        SELECT u.user_id, u.name, u.email, u.role, sp.contact, sp.job_role, sp.status, sp.leave_start, sp.leave_end
        FROM users u
        JOIN staff_profiles sp
        ON u.user_id = sp.user_id
        WHERE u.name LIKE ?
    ");
    $stmt->execute(['%' . $searchStaff . '%']);
    $searchResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// fetch staff 
if ($searchStaff !== '') {
    $staffs = $searchResult;
} else {
    $staffs = $db->query("
        SELECT u.user_id, u.name, u.email, u.role, sp.contact, sp.job_role, sp.status, sp.leave_start, sp.leave_end
        FROM users u 
        JOIN staff_profiles sp ON u.user_id = sp.user_id
    ")->fetchAll(PDO::FETCH_ASSOC);
}

// to add new staff form
$showAddForm = isset($_GET['action']) && $_GET['action'] === 'new';
$error = '';

// edit staff
$editId = $_GET['edit'] ?? null;
$editStaff = null;

if ($editId) {
    $editStmt = $db->prepare("
        SELECT u.user_id, u.name, u.email, sp.contact, sp.job_role, sp.status, sp.leave_start, sp.leave_end
        FROM users u JOIN staff_profiles sp ON u.user_id = sp.user_id
        WHERE u.user_id = ?
    ");
    $editStmt->execute([$editId]);
    $editStaff = $editStmt->fetch(PDO::FETCH_ASSOC);
}

$showForm = $showAddForm || $editStaff;

// edit, add, delete submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // remove staff
    if (isset($_POST['delete_id'])) {
        $userId = $_POST['delete_id'];

        // 1. delete from staff profile
        $deleteStaff = $db->prepare("DELETE FROM staff_profiles WHERE user_id = ?");
        $deleteStaff->execute([$userId]);

        // 2. delete from users 
        $deleteUser = $db->prepare("DELETE FROM users WHERE user_id = ?");
        $deleteUser->execute([$userId]);

        header('Location: /staff');
        exit;
    }

    // add staff
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $role = $_POST['job_role'];
    $systemRole = $_POST['system_role'];
    $status = $_POST['status'];
        
    if (isset($_POST['user_id'])) {

        // edit
        $userId = $_POST['user_id'];

        // leave
        $leaveStart = null;
        $leaveEnd = null;

        if ($status === 'on_leave') {
            $leaveStart = $_POST['leave_start'] ?? null;
            $leaveEnd = $_POST['leave_end'] ?? null;
        }

        try {

            $db->beginTransaction();

            // update users table
            $updateUsers = $db->prepare("UPDATE users SET name=?, email=?, role=? WHERE user_id = ?");
            $updateUsers->execute([$name, $email, $systemRole, $userId]);

            // update staff profiles table
            $updateStaff = $db->prepare("UPDATE staff_profiles SET contact=?, job_role=?, status=?, leave_start=?, leave_end=? WHERE user_id=?");
            $updateStaff->execute([$contact, $role, $status, $leaveStart, $leaveEnd, $userId]);

            $db->commit();

            header('Location: /staff');
            exit;

        } catch (Exception $e) {

            $db->rollBack();
            $error = 'Failed to update staff.';

        }
    } else {
        // add new staff
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // leave
        $leaveStart = null;
        $leaveEnd = null;

        if ($status === 'on_leave') {
            $leaveStart = $_POST['leave_start'] ?? null;
            $leaveEnd = $_POST['leave_end'] ?? null;
        }

        // check if email already registered
        $check = $db->prepare("SELECT * FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->fetch()) {
            $error = 'The email is already registered';
        } else if ($password !== $confirmPassword) {
            $error = 'Password does not match!';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // insert to users table
            $insertUser = $db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)");
            $insertUser->execute([$name, $email, $hashedPassword, $systemRole]);

            // newly created user's ID
            $userId = $db->lastInsertId();
            
            // insert to staff profiles table
            $insertStaff = $db->prepare("INSERT INTO staff_profiles (user_id, contact, job_role, status, leave_start, leave_end) VALUES (?, ?, ?, ?, ?, ?)");
            $insertStaff->execute([$userId, $contact, $role, $status, $leaveStart, $leaveEnd]);

            header('Location: /staff');
            exit;
        }
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
    <link rel="stylesheet" href="../assets/css/staff.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
</head>
<body>
    <div class="d-flex min-vh-100">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <div class="main-content flex-grow-1">
            <!-- navbar -->
            <?php include __DIR__ . '/../includes/navbar.php' ?>

            <!-- content -->
            <div class="content px-4">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                    <div class="greeting">
                        <h3>Staff</h3>
                        <p>Manage all gym staff</p>
                    </div>

                    <div class="add-btn align-content-center">
                        <a href="/staff?action=new" class="p-2 px-3">
                            <i class="bi bi-plus"></i> Add Staff
                        </a>
                    </div>
                </div>

                <!-- add staff form -->
                <?php if ($showForm): ?>
                    <div class="staff-form m-3 mt-4 ">
                        <p class="fw-bold fs-4"><?= $editStaff ? 'Edit' : 'Add New' ?> Staff</p>

                        <?php if ($error): ?>
                            <p class="alert alert-danger"><?= htmlspecialchars($error) ?></p>
                        <?php endif; ?>

                        <form action="/staff?action=new" method="POST" class="row g-3" style="max-width: 1000px;">

                            <!-- if edit staff -->
                            <?php if ($editStaff): ?>
                                <input type="hidden" name="user_id" value="<?= $editStaff['user_id'] ?>">
                            <?php endif; ?>
                            
                            <!-- name -->
                            <div class="col-md-12">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($editStaff['name'] ?? '') ?>" required>
                            </div>

                            <!-- email -->
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($editStaff['email'] ?? '') ?>" required>
                            </div>

                            <!-- contact -->
                            <div class="col-md-6">
                                <label class="form-label">Contact No.</label>
                                <input type="text" class="form-control" name="contact" value="<?= htmlspecialchars($editStaff['contact'] ?? '') ?>" required>
                            </div>

                            <!-- job role -->
                            <div class="col-md-4">
                                <label class="form-label">Job Role</label>
                                
                                <select name="job_role" class="form-select" required>
                                    <option value="Trainer">Trainer</option>
                                    <option value="Sales">Sales</option>
                                    <option value="Marketing">Marketing</option>
                                    <option value="Front Desk">Front Desk</option>
                                </select>
                            </div>

                            <!-- system role -->
                            <div class="col-md-4">
                                <label class="form-label">System Role</label>
                                
                                <select name="system_role" class="form-select" required>
                                    <option value="admin">Admin</option>
                                    <option value="staff" selected>Staff</option>
                                </select>
                            </div>

                            <!-- status -->
                            <div class="col-md-4">
                                <label class="form-label">Status</label>

                                <select id="status" name="status" class="form-select">
                                    <option value="available" <?= ($editStaff['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="on_leave" <?= ($editStaff['status'] ?? '') === 'on_leave' ? 'selected' : '' ?>>On Leave</option>
                                </select>
                            </div>

                            <!-- leave start and leave end  -->
                            <div id="leaveDates" class="row g-3" style="display: none;">

                                <!-- leave start -->
                                <div class="col-md-6">
                                    <label class="form-label">Leave Start</label>
                                    <input type="date" class="form-control" name="leave_start" value="<?= htmlspecialchars($editStaff['leave_start'] ?? '') ?>">
                                </div>

                                <!-- leave end -->
                                <div class="col-md-6">
                                    <label class="form-label">Leave End</label>
                                    <input type="date" class="form-control" name="leave_end" value="<?= htmlspecialchars($editStaff['leave_end'] ?? '') ?>">
                                </div>
                            </div>
                            
                            <?php if (!$editStaff): ?>
                                <!-- create password -->
                                <div class="col-md-6">
                                    <label class="form-label">Create Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>

                                <!-- confirm password -->
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                            <?php endif; ?>

                            <!-- button -->
                            <div class="d-flex mt-4">
                                <button type="submit" class="btn btn-dark me-3"><?= $editStaff ? 'Save Changes' : 'Add Staff' ?></button>
                                <a href="/staff" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- staff cards -->
                <div class="parents mx-2 my-5">
                    <?php if (empty($staffs)): ?>
                        <p>No staffs yet</p>
                    <?php else: ?>
                        <?php foreach ($staffs as $staff): ?>
                            <div class="staff-card py-3 px-4">
                                <div class="d-flex justify-content-between header mb-2">
                                    <div class="d-flex gap-2 align-items-center">
                                        <!-- avatar -->
                                        <div class="avatar d-flex justify-content-center align-items-center me-1 m-0">
                                            <?= htmlspecialchars(strtoupper(substr($staff['name'], 0, 1))) ?>
                                        </div>

                                        <!-- name and role -->
                                        <div>
                                            <p class="fw-bold m-0 name"><?= htmlspecialchars($staff['name']) ?></p>
                                            <p class="m-0 roles text-secondary m-0"><?= htmlspecialchars(ucfirst($staff['role'])) ?> - <?= htmlspecialchars($staff['job_role']) ?></p>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <!-- status -->
                                        <p class="m-0 p-3 badge rounded-pill <?= $staff['status'] ?>"><?= htmlspecialchars(($staff['status'] === 'on_leave' ) ? 'On Leave' : 'Available') ?></p>

                                        <!-- leave dates -->
                                        <?php if ($staff['status'] === 'on_leave'): ?>
                                            <p class="m-0 text-secondary p-2 leave-date"><?= htmlspecialchars(date('d/m/Y', strtotime($staff['leave_start']))) ?> - <?= htmlspecialchars(date('d/m/Y', strtotime($staff['leave_end']))) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- contact -->
                                <p class="contact m-0 ms-2 my-1 mt-3">Contact:</p>
                                <div class="contact-info d-flex justify-content-around p-2 py-2 rounded-4">
                                    <p class="m-0 email"><?= htmlspecialchars($staff['email']) ?></p>
                                    <p class="m-0 number"><?= htmlspecialchars($staff['contact']) ?></p>
                                </div>

                                <!-- edit delete btn -->
                                <div class="mt-3 d-flex align-items-center theBtn justify-content-end">
                                    <a href="/staff?edit=<?= $staff['user_id'] ?>" class="editBtn fw-bold">
                                        <i class="bi bi-pencil-square text-light me-2"></i>
                                    </a>

                                    <form action="/staff" method="POST" class="d-inline" onsubmit="return confirm('Remove this staff?');">
                                        <input type="hidden" name="delete_id" value="<?= $staff['user_id'] ?>">
                                        <button type="submit" class="btn-icon btn text-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        const status = document.getElementById('status');
        const leaveDates = document.getElementById('leaveDates');

        status.addEventListener('change', function () {
            leaveDates.style.display = this.value === 'on_leave' ? 'flex' : 'none';
        });

        if (status.value === 'on_leave') {
            leaveDates.style.display = 'flex';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>