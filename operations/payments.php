<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

// show search form
$showForm = isset($_GET['action']) && $_GET['action'] === 'new';

$memberSearch = $_GET['member_q'] ?? '';
$memberResult = [];

if ($memberSearch !== '') {
    $stmt = $db->prepare("SELECT member_id, name, contact, email FROM members WHERE name LIKE ?");
    $stmt->execute(['%' . $memberSearch . '%']);
    $memberResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// payment form
$selectedMemberId = $_GET['member_id'] ?? null;
$selectedMember = null;

if ($selectedMemberId) {
    $selStmt = $db->prepare("SELECT * FROM members WHERE member_id = ?");
    $selStmt->execute([$selectedMemberId]);
    $selectedMember = $selStmt->fetch(PDO::FETCH_ASSOC);
}


// revenue this month
$monthlyRevenue = $db->query("
    SELECT SUM(amount) FROM payments
    WHERE status = 'paid'
    AND MONTH(payment_date) = MONTH(CURDATE())
    AND YEAR(payment_date) = YEAR(CURDATE())
")->fetchColumn();
$monthlyRevenue = $monthlyRevenue ?? 0;

// succssful payment
$successful = $db->query("
    SELECT COUNT(*) FROM payments
    WHERE status = 'paid'
    AND MONTH(payment_date) = MONTH(CURDATE())
    AND YEAR(payment_date) = YEAR(CURDATE())
")->fetchColumn();

// failed payment
$failed = $db->query("
    SELECT COUNT(*) FROM payments
    WHERE status = 'failed'
    AND MONTH(payment_date) = MONTH(CURDATE())
    AND YEAR(payment_date) = YEAR(CURDATE())
")->fetchColumn();

// payment history 
$paymentHistory = $db->query("
    SELECT p.amount, p.payment_date, p.status, p.method, m.name AS member_name, pl.plan_name
    FROM payments p
    JOIN members m ON p.member_id = m.member_id
    LEFT JOIN membership_plans pl ON m.plan_id = pl.plan_id
    ORDER BY p.payment_date DESC
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
    <link rel="stylesheet" href="../assets/css/payments.css">
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
                        <h3>Payments</h3>
                        <p>Payment history and revenue</p>
                    </div>

                    <div class="add-btn align-content-center">
                        <a href="/payments?action=new" class="p-2 px-3">
                            <i class="bi bi-plus"></i> Recent Payment
                        </a>
                    </div>
                </div>

                <!-- stat cards -->
                <div class="stat-cards my-5 px-2">
                    <div class="cards revenue">
                        <i class="bi bi-currency-dollar"></i>
                        <p class="stat-label text-secondary">Revenue This Month</p>
                        <p class="stat-value">RM <?= htmlspecialchars(number_format($monthlyRevenue, 2)) ?></p>
                    </div>

                    <div class="cards successful-payment">
                        <i class="bi bi-check2-all"></i>
                        <p class="stat-label text-secondary">Successful Payments</p>
                        <p class="stat-value"><?= htmlspecialchars($successful) ?></p>
                    </div>

                    <div class="cards failed-payment">
                        <i class="bi bi-x-lg"></i>
                        <p class="stat-label text-secondary">Failed Payments</p>
                        <p class="stat-value"><?= htmlspecialchars($failed) ?></p>
                    </div>
                </div>

                <!-- search box -->
                <?php if ($showForm && !$selectedMember): ?>
                    <div class="payment-form mx-3 p-3">
                        <p class="fw-bold">Find member to record a payment</p>
                        <form action="/payments" method="GET" class="mb-3 gap-2 d-flex">
                            <input type="hidden" name="action" value="new">
                            <input type="text" class="form-control" name="member_q" value="<?= htmlspecialchars($memberSearch) ?>" placeholder="Search member by name...">
                            <button type="submit" class="btn btn-outline-dark">Search</button>
                        </form>

                        <!-- after search -->
                        <?php foreach ($memberResult as $member): ?>
                            <div class="d-flex justify-content-between p-2">
                                <span><?= htmlspecialchars($member['name']) ?> - <?= htmlspecialchars($member['contact']) ?></span>
                                <a href="/payments?action=new&member_id=<?= $member['member_id'] ?>" class="btn btn-sm btn-outline-dark">Select</a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- payment form -->
                <?php if ($selectedMember): ?>
                    <div class="payment-form mx-3 p-3">
                        <p class="fw-bold">Record payment for <?= htmlspecialchars($selectedMember['name']) ?></p>

                        <form action="/payments" method="POST" class="row g-3">
                            <input type="hidden" name="member_id" value="<?= $selectedMember['member_id'] ?>">

                            <div class="col-md-4">
                                <label class="form-label">Amount (RM)</label>
                                <input type="number" class="form-control" name="amount" class="form-control" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Method</label>
                                <select name="method" class="form-select">
                                    <option selected>- Select -</option>
                                    <option value="Cash">Cash</option>
                                    <option value="Card">Card</option>
                                    <option value="Online Banking">Online Banking</option>
                                    <option value="E-Wallet">E-Wallet</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="paid">Paid</option>
                                    <option value="pending">Pending</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>

                            <div class="d-flex">
                                <button type="submit" class="btn btn-outline-dark">Record Payment</button>
                                <a href="/payments" class="btn btn-outline-dark">Cancel</a>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- payment history  -->
                <div class="my-5 payment-history">
                    <p>Payment History</p>

                    <!-- table -->
                    <div class="table-wrapper">
                        <table class="table-conten w-100">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($paymentHistory)): ?>
                                    <tr><td colspan="6" class="text-center text-secondary py-4">No payments yet</td></tr>
                                <?php else: ?>
                                    <?php foreach ($paymentHistory as $payment): ?>
                                        <tr>
                                            <!-- name -->
                                            <td class="fw-semibold">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar d-flex justify-content-center align-items-center me-1">
                                                        <?= htmlspecialchars(strtoupper(substr($payment['member_name'], 0, 1))) ?>
                                                    </div>
                                                    <span><?= htmlspecialchars($payment['member_name']) ?></span>
                                                </div>
                                            </td>

                                            <!-- plan -->
                                            <td class="text-secondary"><?= htmlspecialchars($payment['plan_name'] ?? '-') ?></td>

                                            <!-- amount -->
                                            <td>RM <?= htmlspecialchars(number_format($payment['amount'], 0)) ?></td>

                                            <!-- payment method -->
                                            <td class="text-secondary"><?= htmlspecialchars($payment['method'] ?? '-') ?></td>

                                            <!-- payment date -->
                                            <td class="text-secondary"><?= htmlspecialchars(date('d-m-Y', strtotime($payment['payment_date']))) ?></td>

                                            <!-- payment status -->
                                            <td>
                                                <span class="badge rounded-pill badge-<?= $payment['status'] ?>">
                                                    <?= htmlspecialchars(ucfirst($payment['status'])) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>