<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

$me = getCurrentMember($db);

if ($me) {

    // fetch checkins
    $checkStmt = $db->prepare("SELECT checkin_time FROM checkins WHERE member_id = ?");
    $checkStmt->execute([$me['member_id']]);
    $checkinHistory = $checkStmt->fetchAll(PDO::FETCH_ASSOC);

    // fetch payments
    $payStmt = $db->prepare("SELECT * FROM payments WHERE member_id = ?");
    $payStmt->execute([$me['member_id']]);
    $paymentHistory = $payStmt->fetchAll(PDO::FETCH_ASSOC);

    // fetch classes
    $stmt = $db->prepare("
        SELECT c.class_name, c.instructor, c.schedule_time, cb.status
        FROM classes c
        JOIN class_bookings cb
        ON cb.class_id = c.class_id
        WHERE member_id = ?
        ORDER BY schedule_time DESC
    ");
    $stmt->execute([$me['member_id']]);
    $classHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../assets/css/m-history.css">
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
                            <h3>My History</h3>
                            <p class="mt-1">Your check-ins, payments and class bookings</p>
                        </div>
                    </div>

                    <!-- button tabs -->
                    <div class="history-tabs mx-2 mb-5 mt-4">
                        <button class="btn btn-outline-light tab-btn active me-2" onclick="showHistory('checkin', this)">
                            Check-ins
                        </button>

                        <button class="btn btn-outline-light tab-btn me-2" onclick="showHistory('payment', this)">
                            Payments
                        </button>
                        
                        <button class="btn btn-outline-light tab-btn" onclick="showHistory('class', this)">
                            Classes
                        </button>
                    </div>

                    <!-- === TABLES === -->
                    <!-- check ins -->
                    <div id="checkin" class="history-content">
                        <table class="table-content w-100">
                            <thead>
                                <tr class="">
                                    <th>Date</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($checkinHistory)): ?>
                                    <tr>
                                        <td colspan="2" class="text-center py-4">No check-in recorded yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($checkinHistory as $checkin): ?>
                                        <tr>
                                            <!-- date -->
                                            <td class="date"><?= htmlspecialchars(date('d-m-Y', strtotime($checkin['checkin_time']))) ?></td>

                                            <!-- time -->
                                            <td class="text-secondary"><?= htmlspecialchars(date('g:i A', strtotime($checkin['checkin_time']))) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- payments -->
                    <div id="payment" class="history-content" hidden>
                        <table class="table-content w-100">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>New Expiry</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paymentHistory)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">No payment recorded yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($paymentHistory as $payment): ?>
                                        <tr>
                                            <!-- date -->
                                            <td class="text-secondary"><?= htmlspecialchars(date('d-m-Y', strtotime($payment['payment_date']))) ?></td>

                                            <!-- amount -->
                                            <td><?= htmlspecialchars($payment['amount']) ?></td>

                                            <!-- method -->
                                            <td class="text-secondary"><?= htmlspecialchars($payment['method']) ?></td>

                                            <!-- status -->
                                            <td>
                                                <span class="badge rounded-pill badge-<?= $payment['status'] ?>">
                                                    <?= htmlspecialchars(ucfirst($payment['status'])) ?>
                                                </span>
                                            </td>

                                            <!-- new expiry -->
                                            <td><?= htmlspecialchars($payment['new_expiry_date']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- classes -->
                    <div id="class" class="history-content" hidden>
                        <table class="table-content w-100">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Instructor</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($classHistory)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-secondary py-4">No record of booked / cancelled class yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($classHistory as $class): ?>
                                        <tr>
                                            <!-- class name -->
                                            <td><?= htmlspecialchars($class['class_name']) ?></td>

                                            <!-- instructor -->
                                            <td><?= htmlspecialchars($class['instructor']) ?></td>

                                            <!-- date -->
                                            <td><?= htmlspecialchars(date('d-m-Y', strtotime($class['schedule_time']))) ?></td>

                                            <!-- time -->
                                            <td><?= htmlspecialchars(date('g:i A', strtotime($class['schedule_time']))) ?></td>

                                            <!-- status -->
                                            <td>
                                                <span class="badge rounded-pill badge-<?= $class['status'] ?>">
                                                    <?= htmlspecialchars(ucfirst($class['status'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                <?php else: ?>
                    <p class="alert alert-secondary my-5">No membership found. Please contact us or walk-in to register</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script>
        function showHistory(section, clickedBtn) {
            // 1.1 hide all three
            document.querySelectorAll('.history-content').forEach(function(content) {
                content.hidden = true;
            });

            // 1.2 show selected 
            document.getElementById(section).hidden = false;

            // 2.1 remove active style from all 
            document.querySelectorAll('.tab-btn').forEach(function(tab) {
                tab.classList.remove('active');
            })

            // 2.2 add active style to selected tab 
            clickedBtn.classList.add('active');
        }
    </script>
</body>
</html>