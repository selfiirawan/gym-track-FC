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
    $expiringSoon = $me['days_left'] >= 0 && $me['days_left'] <= 7;
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
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                    <div class="greeting">
                        <h3>Hello, <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></h3>
                        <p>Here's your membership overview</p>
                    </div>
                </div>

                <!-- expiring soon -->
                <?php if ($expiringSoon): ?>
                    <p class="alert alert-secondary mx-2 my-4">Your membership expires in <?= $expiringSoon ?> days. Please contact us or walk-in to renew.</p>
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
                <div class="row gap-3 mx-2">
                    <!-- classes -->
                    <div class="col class">
                        <p class="m-0">Upcoming classes</p>
                    </div>

                    <!-- checkins -->
                    <div class="col checkin">
                        <p class="m-0">Recent check-ins</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>