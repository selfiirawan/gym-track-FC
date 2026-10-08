<?php 

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

// search member
$search = $_GET['q'] ?? '';
$manageId = $_GET['manage'] ?? null;
$results = [];

if ($search != '') {
    $stmt = $db->prepare("SELECT * FROM members WHERE name LIKE ?");
    $stmt->execute(['%' . $search . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$errorDup = '';

if (isset($_GET['error']) && $_GET['error'] === 'duplicate') {
    $errorDup = 'This member is already booked for this class';
}

if (isset($_POST['member_id']) && isset($_POST['class_id'])) {

    // check duplicates
    $check = $db->prepare("SELECT booking_id FROM class_bookings WHERE member_id = ? AND class_id = ? AND status = 'booked'");
    $check->execute([$_POST['member_id'], $_POST['class_id']]);

    if ($check->fetch()) {
        // has duplicate
        header('Location: /classes?manage=' . $_POST['class_id'] . '&error=duplicate');
        exit;
    } else {
        // no duplicate - add to booked
        $insert = $db->prepare("INSERT INTO class_bookings (member_id, class_id, status) VALUES (?, ?, 'booked')");
        $insert->execute([$_POST['member_id'], $_POST['class_id']]);
        header('Location: /classes?manage=' . $_POST['class_id']);
        exit;
    }
}

// remove booking 
if (isset($_POST['cancel_id'])) {
    $cancel = $db->prepare("UPDATE class_bookings SET status = 'cancelled' WHERE booking_id = ?");
    $cancel->execute([$_POST['cancel_id']]);
    header('Location: /classes?manage=' . $_POST['class_id']);
    exit;
}

// fetch classes and total booked for each class 
$classes = $db->query("
    SELECT c.*, 
    (SELECT COUNT(*) FROM class_bookings cb WHERE c.class_id = cb.class_id AND cb.status = 'booked')
    AS slots_filled FROM classes c
    ORDER BY c.schedule_time ASC
")->fetchAll(PDO::FETCH_ASSOC);

// when create btn in clicked
$showAddForm = isset($_GET['action']) && $_GET['action'] === 'new';

// edit class btn
$editId = $_GET['edit'] ?? null;
$editClass = null;

if ($editId) {
    $editStmt = $db->prepare("SELECT * FROM classes WHERE class_id = ?");
    $editStmt->execute([$editId]);
    $editClass = $editStmt->fetch(PDO::FETCH_ASSOC);
}

$showForm = $showAddForm || $editClass;
$error = '';

// add, edit, delete class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_name'])) {

    // delete class
    if (isset($_POST['delete_id'])) {
        $delete = $db->prepare("DELETE FROM classes WHERE class_id = ?");
        $delete->execute([$_POST['delete_id']]);
        
        header('Location: /classes');
        exit;
    }

    // create new class
    $className = $_POST['class_name'];
    $instructor = $_POST['instructor'];
    $scheduleTime = $_POST['schedule_time'];
    $capacity = $_POST['capacity'];

    try {
        if (isset($_POST['class_id'])) {
            // edit
            $update = $db->prepare("UPDATE classes SET class_name=?, instructor=?, schedule_time=?, capacity=? WHERE class_id=?");
            $update->execute([$className, $instructor, $scheduleTime, $capacity, $_POST['class_id']]);
        } else {
            // add
            $stmt = $db->prepare("INSERT INTO classes (class_name, instructor, schedule_time, capacity) VALUES (?, ?, ?, ?)");
            $stmt->execute([$className, $instructor, $scheduleTime, $capacity]);
        }

        header('Location: /classes');
        exit;

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = 'A class with this name already exists.';
        } else {
            throw $e;
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
    <link rel="stylesheet" href="../assets/css/class.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
</head>
<body>
    <div class="d-flex min-vh-100">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../includes/sidebar.php' ?>

        <!-- MAIN CONTENT -->
        <div class="main-content flex-grow-1">
            <!-- NAVBAR -->
            <?php include __DIR__ . '/../includes/navbar.php' ?>

            <div class="content px-4">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                    <div class="greeting">
                        <h3>Classes</h3>
                        <p>Manage fitness classes</p>
                    </div>

                    <?php if ($_SESSION['user']['role'] !== 'staff'): ?>
                    <div class="add-btn align-content-center">
                        <a href="/classes?action=new" class="p-2 px-3">
                            <i class="bi bi-plus"></i> Create Class
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- edit or create new class -->
                <?php if ($showForm): ?>
                    <div class="class-form">
                        <p class="fw-bold fs-4"><?= $editClass ? 'Edit' : 'Create New' ?> Class</p>

                        <?php if ($error): ?>
                            <p class="alert alert-danger"><?= htmlspecialchars($error) ?></p>
                        <?php endif; ?>

                        <form action="/classes?action=new" method="POST" class="row g-3">
                            <?php if ($editClass): ?>
                                <input type="hidden" name="class_id" value="<?= $editClass['class_id'] ?>">
                            <?php endif; ?>

                            <!-- class name -->
                            <div class="col-md-6">
                                <label class="form-label">Class Name</label>
                                <input type="text" class="form-control" name="class_name" value="<?= htmlspecialchars($editClass['class_name'] ?? '') ?>" required>
                            </div>

                            <!-- instructor -->
                            <div class="col-md-6">
                                <label class="form-label">Instructor</label>
                                <input type="text" class="form-control" name="instructor" value="<?= htmlspecialchars($editClass['instructor'] ?? '') ?>" required>
                            </div>

                            <!-- schedule -->
                            <div class="col-md-6">
                                <label class="form-label">Schedule</label>
                                <input type="datetime-local" class="form-control" name="schedule_time" value="<?= htmlspecialchars($editClass['schedule_time'] ?? '') ?>" required>
                            </div>

                            <!-- capacity -->
                            <div class="col-md-6">
                                <label class="form-label">Capacity</label>
                                <input type="number" min="1" class="form-control" name="capacity" value="<?= htmlspecialchars($editClass['capacity'] ?? '') ?>" required>
                            </div>

                            <!-- buttons -->
                            <div class="d-flex mt-3">
                                <button type="submit" class="btn btn-dark me-3"><?= $editClass ? 'Save Changes' : 'Create Class' ?></button>
                                <a href="/classes" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- class card -->
                <div class="parents mx-3 my-5">
                    <?php if (empty($classes)): ?>
                        <p class="text-secondary">No classes yet</p>
                    <?php else: ?>
                        <!-- CARD -->
                        <?php foreach ($classes as $class): 
                            // slot left
                            $slotsLeft = $class['capacity'] - $class['slots_filled'];
                            $fillPercent = $class['capacity'] > 0 ? round(($class['slots_filled'] / $class['capacity']) * 100) : 0;

                            $barColor = '';
                            if ($fillPercent >= 90) {
                                $barColor = 'bg-danger';
                            } else if ($barColor >= 70) {
                                $barColor = 'bg-warning';
                            } else {
                                $barColor = 'bg-success';
                            }

                            $isFull = $slotsLeft <= 0;
                        ?>
                            <div class="class-card p-4">
                                <!-- class name and status badge -->
                                <div class="d-flex justify-content-between name-badge">
                                    <p class="fw-bold fs-3 m-0"><?= htmlspecialchars($class['class_name']) ?></p>
                                    <span class="p-2 px-3 badge rounded-pill align-content-center <?= $isFull ? 'badge-full' : 'badge-open' ?>">
                                        <?= $isFull ? 'Full' : 'Open' ?>
                                    </span>
                                </div>

                                <!-- instructor -->
                                <p class="instructor m-0"><?= htmlspecialchars($class['instructor']) ?></p>

                                <!-- day date and time -->
                                <p class="m-0 mt-2 date-n-time text-secondary">
                                    <i class="bi bi-clock me-1"></i>
                                    <?= htmlspecialchars(date('d/m/Y (D), g:i A', strtotime($class['schedule_time']))) ?>
                                </p>

                                <!-- capacity and slots left -->
                                <div class="d-flex justify-content-between mt-3 mb-1 cap-slot">
                                    <p class="m-0 text-secondary">Capacity: <?= htmlspecialchars($class['slots_filled']) ?>/<?= htmlspecialchars($class['capacity']) ?></p>
                                    <p class="small m-0 <?= $isFull ? 'text-danger' : 'text-secondary' ?>"><?= max($slotsLeft, 0) ?> slots left</p>
                                </div>

                                <!-- progress bar -->
                                <div class="progress mb-3" style="height: 5px;">
                                    <div class="progress-bar <?= $barColor ?>" style="width: <?= $fillPercent ?>%;"></div>
                                </div>

                                <!-- buttons -->
                                <div class="mt-auto d-flex align-items-center gap-2">
                                    <!-- manage button -->
                                    <button type="button" class="btn btn-dark flex-grow-1 me-2" data-bs-toggle="modal" data-bs-target="#manageModal<?= $class['class_id'] ?>">Manage</button>

                                    <!-- edit and delete btn. only admin -->
                                    <?php if (isAdmin()): ?>
                                        <a href="/classes?edit=<?= $class['class_id'] ?>" class="fw-bold editBtn">
                                            <i class="bi bi-pencil-square text-light"></i>
                                        </a>

                                        <form action="/classes" method="POST" class="d-inline" onsubmit="return confirm('Remove this class?');">
                                            <input type="hidden" name="delete_id" value="<?= $class['class_id'] ?>">
                                            <button type="submit" class="btn-icon btn text-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- MODAL STARTS HERE -->
                        <?php foreach ($classes as $class): 
                            // slots left
                            $slotsLeft = $class['capacity'] - $class['slots_filled']
                        ?>
                            <!-- manage form -->
                            <div class="modal fade" id="manageModal<?= $class['class_id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <!-- header -->
                                        <div class="modal-header">
                                            <p class="m-0 fw-bold fs-4"><?= htmlspecialchars($class['class_name']) ?></p>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <p class="m-0 text-secondary text-end capacity"><?= htmlspecialchars(max($slotsLeft, 0)) ?>/<?= htmlspecialchars($class['capacity']) ?> slots left</p>
                                            <!-- search form -->
                                            <p class="m-0 ps-1 text-secondary">Book a member to this class</p>
                                            <form action="/classes" method="GET" class="mb-3 gap-2 d-flex">
                                                <input type="hidden" name="manage" value="<?= $class['class_id'] ?>">
                                                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search member by name...">
                                                <button type="submit" class="btn btn-dark">Search</button>
                                            </form>

                                            <!-- duplicate -->
                                            <?php if ($errorDup !== ''): ?>
                                                <div class="alert alert-danger"><?= $errorDup ?></div>
                                            <?php endif; ?>

                                            <!-- result of search -->
                                            <?php if ($search !== ''): ?>
                                                <?php if (empty($results)): ?>
                                                    <p class="text-secondary">Member not found</p>
                                                <?php else: ?>
                                                    <?php foreach ($results as $member): ?>
                                                        <div class="search-result d-flex justify-content-between align-items-center p-2 px-3 mb-2">
                                                            <p class="m-0">
                                                                <?= htmlspecialchars($member['name']) ?> - 
                                                                <span class="text-secondary contact"><?= htmlspecialchars($member['contact']) ?>, <?= htmlspecialchars($member['email'] ?? 'no email') ?></span>
                                                            </p>

                                                            <!-- book button -->
                                                            <form action="/classes" method="POST">
                                                                <input type="hidden" name="member_id" value="<?= $member['member_id'] ?>">
                                                                <input type="hidden" name="class_id" value="<?= $class['class_id'] ?>">
                                                                <button type="submit" class="btn btn-outline-dark p-1 px-2 btn-sm">Book</button>
                                                            </form>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                            <!-- booked member -->
                                            <?php 
                                            $bookedStmt = $db->prepare("
                                                SELECT cb.booking_id, m.name, m.contact, m.email
                                                FROM class_bookings cb
                                                JOIN members m ON m.member_id = cb.member_id
                                                WHERE cb.class_id = ? AND cb.status = 'booked' 
                                            ");
                                            $bookedStmt->execute([$class['class_id']]);
                                            $bookedMembers = $bookedStmt->fetchAll(PDO::FETCH_ASSOC);
                                            ?>

                                            <p class="m-0 mt-3 mb-2 fw-bold text-secondary ps-1">Booked members</p>
                                            <?php if (empty($bookedMembers)): ?>
                                                <p class="ms-1 text-secondary small">No one booked yet</p>
                                            <?php else: ?>
                                                <?php foreach ($bookedMembers as $booking): ?>
                                                    <div class="d-flex justify-content-between align-items-center p-2 px-3 booked mb-2">
                                                        <p class="m-0 ">
                                                            <?= htmlspecialchars($booking['name']) ?> |  
                                                            <span class="text-secondary contact"><?= htmlspecialchars($booking['contact']) ?>, <?= htmlspecialchars($booking['email'] ?? 'no email') ?></span>
                                                        </p>

                                                        <!-- remove button -->
                                                        <form action="/classes" method="POST">
                                                            <input type="hidden" name="cancel_id" value="<?= $booking['booking_id'] ?>">
                                                            <input type="hidden" name="class_id" value="<?= $class['class_id'] ?>">
                                                            <button type="submit" class="btn btn-outline-danger btn-sm">Remove</button>
                                                        </form>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <?php if ($manageId): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('manageModal<?= $manageId ?>');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        </script>
    <?php endif; ?>
</body>
</html>