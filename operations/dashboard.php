<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymTrack by GymZ</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="d-flex min-vh-100">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <!-- MAIN CONTENT -->
        <div class="main-content flex-grow-1">
            <!-- navbar -->
            <?php include __DIR__ . '/../includes/navbar.php' ?>

            <!-- main content -->
            <div class="content">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 px-4 pb-0">
                    <div class="greeting">
                        <h3>Hello, <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></h3>
                        <p>Here's what's happening today</p>
                    </div>

                    <div class="quick-btn align-content-end">
                        <a href="/members" class="me-3">
                            <i class="bi bi-person-add me-1"></i>
                            <span>Register Member</span>
                        </a>

                        <a href="/checkin" class="me-3">
                            <i class="bi bi-clipboard2-check me-1"></i>
                            <span>Log Check-in</span>
                        </a>

                        <a href="/payments">
                            <i class="bi bi-cash-stack me-1"></i>
                            <span>Record Payment</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>

