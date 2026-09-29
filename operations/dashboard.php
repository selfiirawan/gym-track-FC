<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

// total members
$totalMembers = $db->query("SELECT COUNT(*) FROM members")->fetchColumn();

// active member
$activeMembers = $db->query("SELECT COUNT(*) FROM members WHERE expiry_date >= CURDATE()")->fetchColumn();

// monthly revenue
$monthlyRevenue = $db->query("
    SELECT SUM(amount) FROM payments
    WHERE MONTH(payment_date) = MONTH(CURDATE())
    AND YEAR(payment_date) = YEAR(CURDATE())
")->fetchColumn();

$monthlyRevenue = $monthlyRevenue ?? 0;

// today's check-ins
$checkIns = $db->query("SELECT COUNT(*) FROM checkins WHERE DATE(checkin_time) = CURDATE()")->fetchColumn();

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
            <!-- NAVBAR -->
            <?php include __DIR__ . '/../includes/navbar.php' ?>

            <!-- main content -->
            <div class="content px-4">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 mb-3">
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

                <!-- stat cards -->
                <div class="stat-cards my-5 px-2">
                    <div class="cards total-member">
                        <i class="bi bi-people"></i>
                        <p class="stat-label">Total Members</p>
                        <p class="stat-value"><?= htmlspecialchars($totalMembers) ?></p>
                    </div>

                    <div class="cards active-member">
                        <i class="bi bi-person-check"></i>
                        <p class="stat-label">Active Memberships</p>
                        <p class="stat-value"><?= htmlspecialchars($activeMembers) ?></p>
                    </div>

                    <div class="cards revenue">
                        <i class="bi bi-currency-dollar"></i>
                        <p class="stat-label">Monthly Revenue</p>
                        <p class="stat-value"><?= htmlspecialchars(number_format($monthlyRevenue, 2)) ?></p>
                    </div>

                    <div class="cards daily-checkin">
                        <i class="bi bi-calendar2-check"></i>
                        <p class="stat-label">Today's Check-ins</p>
                        <p class="stat-value"><?= htmlspecialchars($checkIns) ?></p>
                    </div>
                </div>

                <!-- recent members -->
                <div class="recent-members mx-2">
                    <div class="table-title d-flex justify-content-between p-3">
                        <p class="fw-bold fs-5 align-content-center">Recent Members</p>
                        <a href="/members?action=new" class="align-content-center p-2 px-3">
                            <i class="bi bi-plus"></i> Add Member
                        </a>
                    </div>

                    <table class="table-dark table">
                        <thead>
                            <tr>
                            <th scope="col">#</th>
                            <th scope="col">First</th>
                            <th scope="col">Last</th>
                            <th scope="col">Handle</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <th scope="row">1</th>
                            <td>Mark</td>
                            <td>Otto</td>
                            <td>@mdo</td>
                            </tr>
                            <tr>
                            <th scope="row">2</th>
                            <td>Jacob</td>
                            <td>Thornton</td>
                            <td>@fat</td>
                            </tr>
                            <tr>
                            <th scope="row">3</th>
                            <td>John</td>
                            <td>Doe</td>
                            <td>@social</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>

