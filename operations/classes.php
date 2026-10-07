<?php 

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

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
                        <?php foreach ($classes as $class): 
                            // slot left
                            $slotsLeft = $class['capacity'] - $class['slots_filled'];
                            $fillPercent = $class['capacity'] > 0 ? round(($class['slots_filled'] / $class['capacity']) * 100) : 0;

                            $barColor = 'bg-success';
                            if ($fillPercent >= 90) {
                                $barColor = 'bg-danger';
                            } else if ($barColor >= 70) {
                                $barColor = 'bg-warning';
                            }

                            $isFull = $slotsLeft <= 0;
                        ?>
                            <div class="class-card p-4">
                                <!-- class name and status badge -->
                                <div>
                                    <p><?= htmlspecialchars($class['class_name']) ?></p>
                                    <span class="badge rounded-pill <?= $isFull ? 'badge-full' : 'badge-open' ?>">
                                        <?= $isFull ? 'Full' : 'Open' ?>
                                    </span>
                                </div>

                                <!-- instructor -->
                                <p><?= htmlspecialchars($class['instructor']) ?></p>

                                <!-- day date and time -->
                                <p>
                                    <i class="bi bi-clock"></i>
                                    <?= htmlspecialchars(date('d/m/Y (D), g:i A', strtotime($class['schedule_time']))) ?>
                                </p>

                                <!-- capacity and slots left -->
                                <div class="d-flex justify-content-between mb-1">
                                    <p>Capacity: <?= htmlspecialchars($class['slots_filled']) ?>/<?= htmlspecialchars($class['capacity']) ?></p>
                                    <p class="small <?= $isFull ? 'text-danger' : 'text-secondary' ?>"><?= max($slotsLeft, 0) ?> slots left</p>
                                </div>

                                <!-- progress bar -->
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar <?= $barColor ?>" style="width: <?= $fillPercent ?>%;"></div>
                                </div>

                                <!-- buttons -->
                                <div class="mt-auto d-flex align-items-center gap-2">
                                    <!-- manage button -->
                                    <button type="button" class="btn btn-dark flex-grow-1" data-bs-toggle="modal" data-bs-target="#manageModal<?= $class['class_id'] ?>">Manage</button>

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
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>