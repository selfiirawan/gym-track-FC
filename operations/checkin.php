<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

$currDate = date('l, F j, Y');

// search for member
$search = $_GET['q'] ?? '';
$results = [];

if ($search !== '') {
    $stmt = $db->prepare("SELECT member_id, name, contact, email FROM members WHERE name LIKE ?");
    $stmt->execute(['%' . $search . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// check in submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['member_id'])) {
    $insert = $db->prepare("INSERT INTO checkins (member_id, checkin_time) VALUES (?, NOW())");
    $insert->execute([$_POST['member_id']]);
    header('Location: /checkin');
    exit;
}

// count today's check in
$todayCheckIn = $db->query("SELECT COUNT(*) FROM checkins WHERE DATE(checkin_time) = CURDATE()")->fetchColumn();

// check in log
// $checkinLog = $db->query("
//     SELECT c.checkin_time, m.name FROM checkins c
//     JOIN members m ON c.member_id = m.member_id
//     ORDER BY c.checkin_time DESC
// ")->fetchAll(PDO::FETCH_ASSOC);

$logSearch = $_GET['log'] ?? '';

$logSql = "SELECT c.checkin_time, m.name FROM checkins c JOIN members m ON c.member_id = m.member_id WHERE 1=1";
$logParams = [];

if ($logSearch !== '') {
    $logSql .= " AND m.name LIKE ?";
    $logParams[] = '%' . $logSearch . '%';
}

$logSql .= " ORDER BY c.checkin_time DESC";

$logStmt = $db->prepare($logSql);
$logStmt->execute($logParams);
$checkinLog = $logStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymZ Fitness</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/checkin.css">
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

            <!-- content -->
            <div class="content px-4">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                    <div class="greeting">
                        <h3>Member's Check-In</h3>
                        <p>Log member attendance</p>
                        <p class="text-secondary date mt-2"><?= $currDate ?></p>
                    </div>
                </div>

                <!-- checkin stat -->
                <div class="stat-cards my-5 px-2">
                    <div class="cards total-checkin">
                        <i class="bi bi-calendar2-check"></i>
                        <p class="stat-label text-secondary">Today's Check-Ins</p>
                        <p class="stat-value"><?= htmlspecialchars($todayCheckIn) ?></p>
                    </div>
                </div>

                <!-- search form -->
                <div class="search-form2 mx-2 my-5 p-4">
                    <p class="fw-bold fs-5">Find member to check in</p>
                    <form action="/checkin" method="GET" class="mb-3 gap-2 d-flex">
                        <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($search) ?>" placeholder="Search member by name...">
                        <button type="submit" class="btn btn-outline-light">Search</button>
                    </form>

                    <!-- result of search -->
                    <?php if ($search !== ''): ?>
                        <?php if (empty($results)): ?>
                            <p class="text-secondary">No members found</p>
                        <?php else: ?>
                            <?php foreach ($results as $member): ?>
                                <div class="search-result d-flex justify-content-between align-items-center p-2">
                                    <p class="m-0 my-2"><?= htmlspecialchars($member['name']) ?> - <span class="text-secondary"><?= htmlspecialchars($member['contact']) ?>, <?= htmlspecialchars($member['email'] ?? 'no email') ?></span></p>

                                    <form action="/checkin" method="POST">
                                        <input type="hidden" name="member_id" value="<?= $member['member_id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-light">Check In</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <!-- check in log table -->
                <div class="my-4 checkin-log mx-2 my-5">
                    <p class="title">Check-in Log</p>

                    <table class="w-100 table-content">
                        <thead>
                            <th>Member</th>
                            <th>Date</th>
                            <th>Time</th>
                        </thead>

                        <tbody>
                            <?php if (empty($checkinLog)): ?>
                                <tr><td colspan="3" class="text-center text-secondary py-4">No check-ins yet</td></tr>
                            <?php else: ?>
                                <?php foreach ($checkinLog as $log): ?>
                                    <tr>
                                        <!-- name -->
                                        <td class="fw-semibold">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar d-flex justify-content-center align-items-center me-1">
                                                    <?= htmlspecialchars(strtoupper(substr($log['name'], 0, 1))) ?>
                                                </div>
                                                <span><?= htmlspecialchars($log['name']) ?></span>
                                            </div>
                                        </td>

                                        <!-- date and time -->
                                        <td><?= htmlspecialchars(date('d-m-Y', strtotime($log['checkin_time']))) ?></td>
                                        <td><?= htmlspecialchars(date('g:i A', strtotime($log['checkin_time']))) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>