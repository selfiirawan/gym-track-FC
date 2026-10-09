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
    // active or expired
    $isActive = strtotime($me['expiry_date']) >= strtotime('today');

    // count monthly checkin
    $checkin = $db->prepare("
        SELECT COUNT(*) FROM checkins 
        WHERE member_id = ? 
        AND MONTH(checkin_time) = MONTH(CURDATE())
        AND YEAR(checkin_time) = YEAR(CURDATE())
    ");
    $checkin->execute([$me['member_id']]);
    $monthlyCheckin = $checkin->fetchColumn();

    // expiring soon
    $expiringSoon = $me['days_left'] >= 1 && $me['days_left'] <= 7;

    // expired
    $expired = $me['days_left'] <= 0;

    // upcoming class
    $ucStmt = $db->prepare("
        SELECT  c.class_name, c.instructor, c.schedule_time
        FROM class_bookings cb JOIN classes c
        ON cb.class_id = c.class_id
        WHERE cb.member_id = ?
        AND cb.status = 'booked' 
        AND c.schedule_time >= NOW()
        ORDER BY c.schedule_time ASC
    ");
    $ucStmt->execute([$me['member_id']]);
    $upcomingClass = $ucStmt->fetchAll(PDO::FETCH_ASSOC);

    // recent checkins
    $recentStmt = $db->prepare("SELECT checkin_time FROM checkins WHERE member_id = ?");
    $recentStmt->execute([$me['member_id']]);
    $recentCheckin = $recentStmt->fetchAll(PDO::FETCH_ASSOC);
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
    <link rel="stylesheet" href="../assets/css/m-dashboard.css">
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
                            <h3>Hello, <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></h3>
                            <p class="mt-1">Here's your membership overview</p>
                        </div>
                    </div>

                    <!-- expiring soon -->
                    <?php if ($expiringSoon): ?>
                        <p class="alert alert-warning mx-2 my-4">Your membership expires in <?= $expiringSoon ?> day<?= ($me['days_left'] == 1) ? '' : 's' ?>. Please contact us or walk-in to renew</p>
                    <?php elseif ($expired): ?>
                        <p class="alert alert-danger">Your membership has expired. Please contact us or walk-in to renew</p>
                    <?php endif; ?>

                    <!-- stat cards -->
                    <div class="stat-cards my-5 px-2">
                        <div class="cards plan">
                            <div class="d-flex plan justify-content-between align-items-center">
                                <p class="stat-label plan text-secondary">Current plan</p>
                                <p class="badge rounded-pill badge-<?= $isActive ? 'active' : 'expired' ?>">
                                    <?= $isActive ? 'Active' : 'Expired' ?>
                                </p>
                            </div>
                            <p class="stat-value"><?= htmlspecialchars($me['plan_name']) ?></p>
                        </div>

                        <div class="cards expired">
                            <p class="stat-label text-secondary">Expires on</p>
                            <p class="stat-value"><?= htmlspecialchars(date('d-m-Y', strtotime($me['expiry_date']))) ?></p>
                        </div>

                        <div class="cards days-left">
                            <p class="stat-label text-secondary">Days left</p>
                            <p class="stat-value"><?= htmlspecialchars($me['days_left']) ?></p>
                        </div>

                        <div class="cards checkin-card">
                            <p class="stat-label text-secondary">Check-ins this month</p>
                            <p class="stat-value"><?= $monthlyCheckin ?></p>
                        </div>
                    </div>

                    <!-- classes and checkins -->
                    <div class="row mx-2">
                        <!-- classes -->
                        <div class="col class py-3 px-4">
                            <p class="m-0 fw-bold fs-4">Upcoming classes</p>

                            <?php if (empty($upcomingClass)): ?>
                                <p class="text-center text-secondary py-3">No upcoming class yet</p>
                            <?php else: ?>
                                <?php foreach ($upcomingClass as $class): ?>
                                    <div class="d-flex justify-content-between mt-2 name-date py-2">
                                        <p class="m-0 class-name"><?= htmlspecialchars($class['class_name']) ?></p>
                                        <p class="m-0 text-secondary class-time"><?= htmlspecialchars(date('d/m/Y (D), g:i A', strtotime($class['schedule_time']))) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- checkins -->
                        <div class="col checkin py-3 px-4">
                            <p class="m-0 fw-bold fs-4">Recent check-ins</p>

                            <?php if (empty($recentCheckin)): ?>
                                <p class="text-center text-secondary py-3">No check-in recorded yet</p>
                            <?php else: ?>
                                <?php foreach ($recentCheckin as $checkin): ?>
                                    <div class="d-flex justify-content-between date-time mt-2 py-2">
                                        <p class="m-0"><?= htmlspecialchars(date('d-m-Y', strtotime($checkin['checkin_time']))) ?></p>
                                        <p class="m-0 text-secondary"><?= htmlspecialchars(date('g:i A', strtotime($checkin['checkin_time']))) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
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