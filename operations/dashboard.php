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

// recent members
$recentMembers = $db->query("
    SELECT m.member_id, m.name, m.join_date, p.plan_name, m.expiry_date 
    FROM members m
    JOIN membership_plans p ON m.plan_id = p.plan_id
    ORDER BY m.join_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// recent payments
$recentPayments = $db->query("
    SELECT p.amount, p.payment_date, m.name, m.contact
    FROM payments p
    JOIN members m ON p.member_id = m.member_id
    ORDER BY p.payment_date DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// expiring soon
$expiringSoon = $db->query("
    SELECT name, contact, expiry_date, DATEDIFF(expiry_date, CURDATE()) AS days_left
    FROM members 
    WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ORDER BY expiry_date ASC
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
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
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
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
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
                        <p class="stat-label text-secondary">Total Members</p>
                        <p class="stat-value"><?= htmlspecialchars($totalMembers) ?></p>
                    </div>

                    <div class="cards active-member">
                        <i class="bi bi-person-check"></i>
                        <p class="stat-label text-secondary">Active Memberships</p>
                        <p class="stat-value"><?= htmlspecialchars($activeMembers) ?></p>
                    </div>

                    <div class="cards revenue">
                        <i class="bi bi-currency-dollar"></i>
                        <p class="stat-label text-secondary">Monthly Revenue</p>
                        <p class="stat-value">RM <?= htmlspecialchars(number_format($monthlyRevenue, 2)) ?></p>
                    </div>

                    <div class="cards daily-checkin">
                        <i class="bi bi-calendar2-check"></i>
                        <p class="stat-label text-secondary">Today's Check-ins</p>
                        <p class="stat-value"><?= htmlspecialchars($checkIns) ?></p>
                    </div>
                </div>

                <!-- recent members -->
                <div class="recent-members mx-2 my-5">
                    <div class="table-title d-flex justify-content-between p-3">
                        <p class="fw-bold fs-3 align-content-center">Recent Members</p>
                        <a href="/members?action=new" class="add-member align-content-center p-2 px-3">
                            <i class="bi bi-plus"></i> Add Member
                        </a>
                    </div>

                    <table class="recent-members-table w-100">
                        <thead class="text-dark">
                            <tr>
                                <th>Member</th>
                                <th>Plan</th>
                                <th>Joined</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentMembers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No members yet</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentMembers as $member):
                                    $isActive = strtotime($member['expiry_date']) >= strtotime('today');
                                ?>
                                    <tr>
                                        <td class="fw-semibold"><?= htmlspecialchars($member['name']) ?></td>
                                        <td class="text-secondary plan"><?= htmlspecialchars($member['plan_name']) ?></td>
                                        <td class="text-secondary join"><?= htmlspecialchars(date('d-m-Y', strtotime($member['join_date']))) ?></td>
                                        <td>
                                            <span class="badge rounded-pill <?= $isActive ? 'badge-active' : 'badge-expired' ?>">
                                                <?= $isActive ? 'Active' : 'Expired' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- recent payments and expiring soon -->
                <div class="d-flex gap-4 mx-2 my-5 payment-and-expiring">
                    <!-- recent payments -->
                    <div class="recent-payments px-4 py-3" style="flex: 2;">
                        <div class="title d-flex justify-content-between mb-4">
                            <p class="fw-bold fs-5 align-content-center m-0">Recent Payments</p>
                            <a href="/payments" class="view-all align-content-center m-0 p-2 px-3">View all</a>
                        </div>

                        <div class="payment-list">
                            <?php if (empty($recentPayments)): ?>
                                <p class="text-center text-secondary py-3">No payment yet</p>
                            <?php else: ?>
                                <?php foreach ($recentPayments as $payment): ?>
                                    <div class="payment-row d-flex justify-content-between align-items-center py-3">
                                        <div>
                                            <p class=""><?= htmlspecialchars($payment['name']) ?></p>
                                            <p class="text-secondary"><?= htmlspecialchars(date('d-m-Y', strtotime($payment['payment_date']))) ?></p>
                                        </div>

                                        <div class="text-end">
                                            <p class="fw-bold">RM <?= htmlspecialchars($payment['amount']) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- expiring soon -->
                    <div class="expiring-soon border border-danger px-3" style="flex: 1;">
                        <div class="title p-3 text-danger">
                            <p class="fw-bold fs-5 align-content-center m-0">Expiring Soon</p>
                            <p class="days">(7 Days)</p>
                        </div>
                        
                        <div>
                            <?php if (empty($expiringSoon)): ?>
                                <p class="text-center text-secondary py-4">No memberships expiring soon</p>
                            <?php else: ?>
                                <?php foreach ($expiringSoon as $member): ?>
                                    <div class="expiring-row d-flex justify-content-between align-items-center px-3">
                                        <div>
                                            <p class="mb-0"><?= htmlspecialchars($member['name']) ?></p>
                                            <p class="text-secondary"><?= htmlspecialchars($member['contact']) ?></p>
                                        </div>

                                        <p class="text-danger mb-0 fw-bold"><?= $member['days_left'] ?> day<?= $member['days_left'] == 1 ? '' : 's' ?></p>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>

