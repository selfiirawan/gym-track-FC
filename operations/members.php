<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

// for add new member form
$plans = $db->query("SELECT * FROM membership_plans")->fetchAll(PDO::FETCH_ASSOC);
$showAddForm = isset($_GET['action']) && $_GET['action'] === 'new';

$error = '';

// edit member
$editId = $_GET['edit'] ?? null;
$editMember = null;

if ($editId) {
    $editStmt = $db->prepare("SELECT * FROM members WHERE member_id = ?");
    $editStmt->execute([$editId]);
    $editMember = $editStmt->fetch(PDO::FETCH_ASSOC);
}

$showForm = $showAddForm || $editMember;

// add and delete submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // delete member
    if (isset($_POST['delete_id'])) {
        $delete = $db->prepare("DELETE FROM members WHERE member_id = ?");
        $delete->execute([$_POST['delete_id']]);
        header('Location: /members');
        exit;
    }

    // add new member
    $planId = $_POST['plan_id'];
    $joinedDate = $_POST['joined_date'];

    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $registerBy = $_SESSION['user']['id'];

    $planStmt = $db->prepare("SELECT duration_days FROM membership_plans WHERE plan_id = ?");
    $planStmt->execute([$planId]);
    $duration = $planStmt->fetchColumn();

    $expiryDate = date('Y-m-d', strtotime($joinedDate . "+$duration days"));

    if (isset($_POST['member_id'])) {
        // update 
        $update = $db->prepare("UPDATE members SET name=?, contact=?, email=?, join_date=?, plan_id=?, expiry_date=? WHERE member_id=?");
        $update->execute([$name, $contact, $email, $joinedDate, $planId, $expiryDate, $_POST['member_id']]);
    } else {
        try {
            $insert = $db->prepare("INSERT INTO members (name, contact, email, join_date, plan_id, expiry_date, registered_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insert->execute([$name, $contact, $email, $joinedDate, $planId, $expiryDate, $registerBy]);
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'A member with this email already exists.';
            } else {
                throw $e;
            }
        }
    }

    if ($error == '') {
        header('Location: /members');
        exit;
    }
}

// search, filter and fetch members
$search = $_GET['q'] ?? '';
$filter = $_GET['filter'] ?? 'all';

$sql = "SELECT m.*, p.plan_name FROM members m JOIN membership_plans p ON m.plan_id = p.plan_id WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND m.name LIKE ?";
    $params[] = '%' . $search . '%';
}

if ($filter === 'active') {
    $sql .= " AND m.expiry_date >= CURDATE()";
} elseif ($filter === 'expired') {
    $sql .= " AND m.expiry_date < CURDATE()";
}

$sql .= " ORDER BY join_date DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymZ Fitness</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/members.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
</head>
<body>
    <div class="d-flex min-vh-100">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- MAIN -->
        <div class="main-content flex-grow-1">
            <!-- navbar -->
            <?php include __DIR__ . '/../includes/navbar.php'; ?>

            <!-- content -->
            <div class="content px-4">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                    <div class="greeting">
                        <h3>Members</h3>
                        <p>Manage all gym members</p>
                    </div>

                    <div class="add-btn align-content-center">
                        <a href="/members?action=new" class="p-2 px-3">
                            <i class="bi bi-plus"></i> Add Member
                        </a>
                    </div>
                </div>

                <!-- duplicate error -->
                <?php if ($error): ?>
                    <div class="mx-3">
                        <p class="alert alert-danger" class=""><?= htmlspecialchars($error) ?></p>
                    </div>
                <?php endif; ?>

                <!-- add member form -->
                <?php if ($showForm): ?>
                    <div class="add-member-form my-3 p-3 pb-0">
                        <form action="/members" method="POST" class="px-5 py-4">

                            <?php if ($editMember): ?>
                                <input type="hidden" name="member_id" value="<?= $editMember['member_id'] ?>">
                            <?php endif; ?>

                            <div class="mb-3 ">
                                <label class="form-label" for="new-name">Name</label>
                                <input type="text" id="new-name" class="form-control" name="name" value="<?= htmlspecialchars($editMember['name'] ?? '') ?>" placeholder="New member's name" required>
                            </div>

                            <div class="mb-3 row ">
                                <div class="col">
                                    <label class="form-label" for="contact">Contact No.</label>
                                    <input type="text" id="contact" class="form-control" name="contact" value="<?= htmlspecialchars($editMember['contact'] ?? '') ?>" placeholder="0123456789" required>
                                </div>
                                <div class="col">
                                    <label class="form-label" for="email">Email</label>
                                    <input type="email" id="email" class="form-control" name="email" value="<?= htmlspecialchars($editMember['email'] ?? '') ?>" placeholder="member@gmail.com">
                                </div>
                            </div>

                            <div class="mb-3 row ">
                                <div class="col">
                                    <label class="form-label" for="joined-date">Joined Date</label>
                                    <input type="date" name="joined_date" value="<?= htmlspecialchars($editMember['join_date'] ?? '') ?>" id="joined-date" class="form-control" required>
                                </div>

                                <div class="col">
                                    <label for="plan" class="form-label">Membership Plan</label>

                                    <select name="plan_id" id="plan" class="form-select" required>
                                        <?php foreach ($plans as $plan): ?>
                                            <option value="<?= $plan['plan_id'] ?>" <?= ($editMember['plan_id'] ?? null) == $plan['plan_id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($plan['plan_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-outline-light mt-3"><?= $editMember ? 'Update' : 'Register' ?> Member</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- filter -->
                <div class="filter-btn p-3 pb-0 mb-4">
                    <a href="/members?filter=all&q=<?= urlencode($search) ?>">All</a>
                    <a href="/members?filter=active&q=<?= urlencode($search) ?>">Active</a>
                    <a href="/members?filter=expired&q=<?= urlencode($search) ?>">Expired</a>
                </div>

                <!-- members table -->
                <div class="member-table mx-3 my-5 px-3">
                    <table class="table-content w-100 ">
                        <thead>
                            <th>Member</th>
                            <th>Contact</th>
                            <th>Plan</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <?php if (empty($members)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No members yet</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($members as $member): 
                                    $isActive = strtotime($member['expiry_date']) >= strtotime('today');
                                ?>
                                    <tr>
                                        <!-- name -->
                                        <td class="fw-semibold">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar d-flex justify-content-center align-items-center me-1">
                                                    <?= htmlspecialchars(strtoupper(substr($member['name'], 0, 1))) ?>
                                                </div>
                                                <span><?= htmlspecialchars($member['name']) ?></span>
                                            </div>
                                        </td>

                                        <!-- contact -->
                                        <td class="contact">
                                            <div><?= htmlspecialchars($member['email'] ?? '-') ?></div>
                                            <div class="text-secondary"><?= htmlspecialchars($member['contact']) ?></div>
                                        </td>

                                        <!-- plan -->
                                        <td class="plan"><?= htmlspecialchars($member['plan_name']) ?></td>

                                        <!-- joined -->
                                        <td class="joined"><?= htmlspecialchars(date('d-m-Y', strtotime($member['join_date']))) ?></td>

                                        <!-- status -->
                                        <td>
                                            <span class="badge rounded-pill <?= $isActive ? 'badge-active' : 'badge-expired' ?>">
                                                <?= $isActive ? 'Active' : 'Expired' ?>
                                            </span>
                                        </td>

                                        <!-- action -->
                                        <td>
                                            <a href="/members?action=new&edit=<?= $member['member_id'] ?>" class="editBtn"><i class="bi bi-pencil text-secondary"></i></a>

                                            <form action="/members" method="POST" class="d-inline" onsubmit="return confirm('Delete this member?');">
                                                <input type="hidden" name="delete_id" value="<?= $member['member_id'] ?>">
                                                <button type="submit" class="btn-icon btn text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>