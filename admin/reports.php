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

// revenue by months
$monthlyRevenue = $db->query("
    SELECT DATE_FORMAT(p.payment_date, '%b %Y') AS month, SUM(p.amount) AS total_revenue
    FROM payments p
    WHERE p.status = 'paid'
    AND YEAR(p.payment_date) = YEAR(CURDATE())
    GROUP BY DATE_FORMAT(p.payment_date, '%b %Y')
    ORDER BY min(p.payment_date)
")->fetchAll(PDO::FETCH_ASSOC);

// check ins by months
$monthlyCheckin = $db->query("
    SELECT DATE_FORMAT(c.checkin_time, '%b %Y') AS month, COUNT(c.checkin_id) as total_checkin
    FROM checkins c
    WHERE YEAR(c.checkin_time) = YEAR(CURDATE())
    group by DATE_FORMAT(c.checkin_time, '%b %Y')
    ORDER BY min(c.checkin_time)
")->fetchAll(PDO::FETCH_ASSOC);

// active members by plan
$activePlans = $db->query("
    SELECT p.plan_name, COUNT(m.member_id) AS total_subs
    FROM membership_plans p
    JOIN members m
    WHERE p.plan_id = m.plan_id
    AND m.expiry_date >= CURDATE()
    GROUP BY p.plan_name, m.plan_id
    ORDER BY total_subs DESC
")->fetchAll(PDO::FETCH_ASSOC);

// most booked class
$topClass = $db->query("
    SELECT c.class_name, COUNT(cb.booking_id) AS total_booked
    FROM classes c 
    JOIN class_bookings cb
    WHERE c.class_id = cb.class_id
    AND cb.status = 'booked'
    GROUP BY c.class_name, c.class_id
    ORDER BY total_booked DESC
    LIMIT 3
")->fetchAll(PDO::FETCH_ASSOC);

// max revenue
$maxRevenue = $monthlyRevenue ? max(array_column($monthlyRevenue, 'total_revenue')) : 0;

// max checkin count
$maxCheckin = $monthlyCheckin ? max(array_column($monthlyCheckin, 'total_checkin')) : 0;

// max subs
$maxSubs = $activePlans ? max(array_column($activePlans, 'total_subs')) : 0;

// max booked
$maxBooked = $topClass ? max(array_column($topClass, 'total_booked')) : 0;

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
                <div class="big-reports my-5 mx-2">

                    <!-- months revenue -->
                    <div class="big-cards p-4">
                        <p class="title">Revenues by Month</p>
                        
                        <?php foreach ($monthlyRevenue as $revenue): 
                            $fillPercent = $maxRevenue > 0 ? round($revenue['total_revenue'] / $maxRevenue * 100) : 0;    
                        ?>
                            <div class="d-flex justify-content-between mb-2 mt-4">
                                <p class=""><?= htmlspecialchars($revenue['month']) ?></p>
                                <p class="total">RM <?= htmlspecialchars(number_format($revenue['total_revenue'], 0)) ?></p>
                            </div>

                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar card-bg" style="width: <?= $fillPercent ?>%;"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- monthly checkins -->
                    <div class="big-cards p-4">
                        <p class="title">Check-ins by Month</p>

                        <?php foreach ($monthlyCheckin as $check): 
                            $fillPercent = $maxCheckin > 0 ? round($check['total_checkin'] / $maxCheckin * 100) : 0;
                        ?>
                            <div class="d-flex justify-content-between mb-2 mt-4">
                                <p><?= htmlspecialchars($check['month']) ?></p>
                                <p class="total"><?= htmlspecialchars($check['total_checkin']) ?></p>
                            </div>

                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar card-bg" style="width: <?= $fillPercent ?>%;"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="big-cards p-4">
                        <p class="title">Active Memberships by Plans</p>

                        <?php foreach ($activePlans as $plan): 
                            $fillPercent = $maxSubs > 0 ? round($plan['total_subs'] / $maxSubs * 100) : 0;    
                        ?>
                            <div class="d-flex justify-content-between mb-2 mt-4">
                                <p><?= htmlspecialchars($plan['plan_name']) ?></p>
                                <p class="total"><?= htmlspecialchars($plan['total_subs']) ?></p>
                            </div>

                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar card-bg" style="width: <?= $fillPercent ?>%;"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="big-cards p-4">
                        <p class="title">Most Booked Class</p>

                        <?php foreach ($topClass as $class): 
                            $fillPercent = $maxBooked > 0 ? round($class['total_booked'] / $maxBooked * 100) : 0;    
                        ?>
                            <div class="d-flex justify-content-between mb-2 mt-4">
                                <p><?= htmlspecialchars($class['class_name']) ?></p>
                                <p class="total"><?= htmlspecialchars($class['total_booked']) ?></p>
                            </div>

                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar card-bg" style="width: <?= $fillPercent ?>%;"></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>