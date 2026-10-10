<?php 

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

// revenue
$sql = "SELECT SUM(amount) FROM payments WHERE status = 'paid' AND YEAR(payment_date) = YEAR(CURDATE()) ";

// this month
$curMonthRevenue = $db->query($sql . "AND MONTH(payment_date) = MONTH(CURDATE())")->fetchColumn();

// this year 
$yearlyRevenue = $db->query($sql)->fetchColumn();

// active members
$activeMembers = $db->query("SELECT COUNT(*) FROM members WHERE expiry_date >= CURDATE()")->fetchColumn();

// expired members
$expiredMembers = $db->query("SELECT COUNT(*) FROM members WHERE expiry_date < CURDATE()")->fetchColumn();

// revenue by month
$monthlyRevenue = $db->query("
    SELECT DATE_FORMAT(p.payment_date, '%b %Y') AS month, SUM(p.amount) AS total_revenue
    FROM payments p
    WHERE p.status = 'paid'
    AND YEAR(p.payment_date) = YEAR(CURDATE())
    GROUP BY DATE_FORMAT(p.payment_date, '%b %Y')
    ORDER BY min(p.payment_date)
")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymZ Fitness</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/reports.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
</head>
<body>
    <div class="d-flex min-vh-100">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <div class="main-content flex-grow-1">
            <!-- NAVBAR -->
            <?php include __DIR__ . '/../includes/navbar.php' ?>

             <!-- content -->
            <div class="content px-4">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                    <div class="greeting">
                        <h3>Reports</h3>
                        <p>Revenue, membership and attendance overview</p>
                    </div>
                </div>

                <!-- stat cards -->
                <div class="stat-cards my-5 px-2">
                    <div class="cards">
                        <i class="bi bi-cash-stack"></i>
                        <p class="stat-label text-secondary">Revenue this Month</p>
                        <p class="stat-value">RM <?= htmlspecialchars(number_format($curMonthRevenue, 2)) ?></p>
                    </div>

                    <div class="cards">
                        <i class="bi bi-bank"></i>
                        <p class="stat-label text-secondary">Total Revenue</p>
                        <p class="stat-value">RM <?= htmlspecialchars(number_format($yearlyRevenue, 2)) ?></p>
                    </div>

                    <div class="cards">
                        <i class="bi bi-cash-stack"></i>
                        <p class="stat-label text-secondary">Active Members</p>
                        <p class="stat-value"><?= htmlspecialchars($activeMembers) ?></p>
                    </div>

                    <div class="cards">
                        <i class="bi bi-cash-stack"></i>
                        <p class="stat-label text-secondary">Expired Members</p>
                        <p class="stat-value"><?= htmlspecialchars($expiredMembers) ?></p>
                    </div>
                </div>

                <!-- big report cards -->
                <div class="big-reports my-5 px-2">

                    <!-- months revenue -->
                    <div class="big-cards">
                        <p>Revenues by Month</p>
                        
                        <?php foreach ($monthlyRevenue as $revenue): ?>
                            <div class="d-flex justify-content-between">
                                <p><?= htmlspecialchars($revenue['month']) ?></p>
                                <p><?= htmlspecialchars($revenue['total_revenue']) ?></p>
                            </div>

                            <div class="progress mb-3" style="height: 5px;">
                                <div class="progress-bar"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- monthly checkins -->
                    <div class="big-cards">
                        <p>Check-ins by Month</p>
                    </div>

                    <div class="big-cards">
                        <p>Active Memberships by Plans</p>
                    </div>

                    <div class="big-cards">
                        <p>Most Booked Class</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>