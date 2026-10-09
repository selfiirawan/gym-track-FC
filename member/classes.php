<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

$me = getCurrentMember($db);
$error = '';
$isActive = $me && strtotime($me['expiry_date']) >= strtotime('today');

// cancel and book class
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $me) {
    $classId = $_POST['class_id'];

    // cancel booking
    if (isset($_POST['cancel'])) {
        $cancel = $db->prepare("UPDATE class_bookings SET status = 'cancelled' WHERE member_id = ? AND class_id = ? AND status = 'booked'");
        $cancel->execute([$me['member_id'], $classId]);
        
        header('Location: /classes');
        exit;
    }

    // book class
    if (isset($_POST['book'])) {
        $check = $db->prepare("
            SELECT c.capacity, c.schedule_time,
            (SELECT COUNT(*) FROM class_bookings cb WHERE cb.class_id = c.class_id AND status = 'booked') AS filled,
            (SELECT COUNT(*) FROM class_bookings cb WHERE cb.class_id = c.class_id AND cb.member_id = ? AND status = 'booked') AS already
            FROM classes c 
            WHERE c.class_id = ?
        ");
        $check->execute([$me['member_id'], $classId]);
        $c = $check->fetch(PDO::FETCH_ASSOC);

        if (!$c || strtotime($c['schedule_time']) < time()) {
            $error = 'This class is no longer available';
        } else {
            $insert = $db->prepare("INSERT INTO class_bookings (member_id, class_id, status) VALUES (?, ?, 'booked')");
            $insert->execute([$me['member_id'], $classId]);

            header('Location: /classes');
            exit;
        }
    }
}

// fetch classes 
$classes = [];

if ($me) {
    $stmt = $db->prepare("
        SELECT c.*, 
        (SELECT COUNT(*) FROM class_bookings cb WHERE cb.class_id = c.class_id AND cb.status = 'booked') AS slots_filled,
        (SELECT COUNT(*) FROM class_bookings cb WHERE cb.class_id = c.class_id AND cb.member_id = ? AND cb.status = 'booked') AS is_booked
        FROM classes c
        WHERE c.schedule_time >= NOW()
        ORDER BY c.schedule_time ASC
    ");
    $stmt->execute([$me['member_id']]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymZ Fitness</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Lora:ital,wght@0,400..700;1,400..700&family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/m-classes.css">
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
                <?php if ($me): ?>
                    <!-- header -->
                    <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                        <div class="greeting">
                            <h3>Class Timetable</h3>
                            <p class="mt-1">Book your classes</p>
                        </div>
                    </div>

                    <?php if (!$isActive): ?>
                        <p class="alert alert-danger mx-3">Your membership has expired. Please renew to book a class</p>
                    <?php endif; ?>

                    <!-- class cards -->
                    <div class="parents my-5 mx-3">
                        <?php if (empty($classes)): ?>
                            <p class="alert alert-secondary">No class available yet. Stay tune</p>
                        <?php else: ?>
                            <?php foreach ($classes as $class): 
                                // slots left
                                $slotsLeft = $class['capacity'] - $class['slots_filled'];
                                $fillPercent = $class['capacity'] > 0 ? round(($class['slots_filled'] / $class['capacity']) * 100) : 0;

                                // progress bar color
                                $barColor = 'bg-success';
                                if ($fillPercent >= 90) {
                                    $barColor = 'bg-danger';
                                } else if ($fillPercent >= 70) {
                                    $barColor = 'bg-warning';
                                }

                                // full slots 
                                $isFull = $slotsLeft <= 0;
                            ?>
                                <div class="class-card p-4 mb-2">
                                    <!-- class name and status badge -->
                                    <div class="d-flex justify-content-between name-badge">
                                        <p class="fw-bold fs-4 m-0"><?= htmlspecialchars($class['class_name']) ?></p>
                                        <span class="p-2 px-3 badge rounded-pill align-content-center <?= $isFull ? 'badge-full' : 'badge-open' ?>">
                                            <?= $isFull ? 'Full' : 'Open' ?>
                                        </span>
                                    </div>

                                    <!-- instructor -->
                                    <p class="instructor m-0"><?= htmlspecialchars($class['instructor']) ?></p>

                                    <!-- day date time -->
                                    <p class="m-0 mt-2 date-n-time text-secondary">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= htmlspecialchars(date('d/m/Y (D), g:i A', strtotime($class['schedule_time']))) ?>
                                    </p>

                                    <!-- capacity and slots left  -->
                                    <div class="d-flex justify-content-between mt-3 mb-1 cap-slot">
                                        <p class="m-0">Capacity: <?= htmlspecialchars($class['slots_filled']) ?>/<?= htmlspecialchars($class['capacity']) ?></p>
                                        <p class="small m-0 <?= $isFull ? 'text-danger' : 'text-secondary' ?>"><?= max($slotsLeft, 0) ?> slots left</p>
                                    </div>

                                    <!-- progress bar -->
                                    <div class="progress mb-3 bg-secondary" style="height: 5px;">
                                        <div class="progress-bar <?= $barColor ?>" style="width: <?= $fillPercent ?>%;"></div>
                                    </div>

                                    <!-- button -->
                                    <?php if ($class['is_booked']): ?>
                                        <form action="/classes" method="POST">
                                            <input type="hidden" name="class_id" value="<?= $class['class_id'] ?>">
                                            <button type="submit" name="cancel" class="btn btn-outline-danger w-100">Cancel booking</button>
                                        </form>
                                    <?php elseif ($isFull): ?>
                                        <button class="btn btn-secondary w-100" disabled>Class full</button>
                                    <?php else: ?>
                                        <form action="/classes" method="POST">
                                            <input type="hidden" name="class_id" value="<?= $class['class_id'] ?>">
                                            <button type="submit" name="book" class="btn btn-outline-success w-100 <?= !$isActive ? 'disabled' : '' ?>">Book class</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="alert alert-secondary my-5">No membership found. Please contact us or walk-in to register</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>