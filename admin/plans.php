<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

preventCaching();

$caption = 'Manage pricing and features';

if ($_SESSION['user']['role'] === 'staff') {
    $caption = 'GymZ Fitness available memberships plan';
}

// fetch membership plans
$plans = $db->query("SELECT * FROM membership_plans")->fetchAll(PDO::FETCH_ASSOC);

// when create new plan is clicked
$showAddForm = isset($_GET['action']) && $_GET['action'] === 'new';

// add and delete plan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // delete plan
    if (isset($_POST['delete_id'])) {
        $delete = $db->prepare("DELETE FROM membership_plans WHERE plan_id = ?");
        $delete->execute([$_POST['delete_id']]);
        header('Location: /plans');
        exit;
    }

    // create new plan
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $features = $_POST['features'];

    $stmt = $db->prepare("INSERT INTO membership_plans (plan_name, price, duration_days, features) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $price, $duration, $features]);

    header('Location: /plans');
    exit;
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymZ Fitness</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/plans.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
</head>
<body>
    <div class="d-flex min-vh-100">
        <!-- SIDEBAR -->
        <?php include __DIR__ . '/../includes/sidebar.php' ?>

        <!-- MAIN CONTENT -->
        <div class="main-content flex-grow-1">
            <!-- NAVBAR -->
            <?php include __DIR__ . '/../includes/navbar.php' ?>

            <!-- content -->
            <div class="content px-4">
                <!-- header -->
                <div class="header d-flex justify-content-between p-3 pb-0 my-3">
                    <div class="greeting">
                        <h3>Membership Plans</h3>
                        <p><?= htmlspecialchars($caption) ?></p>
                    </div>

                    <div class="add-btn align-content-center" <?= ($_SESSION['user']['role'] === 'staff') ? 'hidden' : '' ?>>
                        <a href="/plans?action=new" class="p-2 px-3">
                            <i class="bi bi-plus"></i> Create New Plan
                        </a>
                    </div>
                </div>

                <!-- create new plan -->
                <?php if ($showAddForm): ?>
                    <div class="create-new-form">
                        <form action="/plans" method="POST">
                            <!-- name -->
                            <div class="row mb-3">
                                <label for="name" class="col-md-2 col-form-label">Plan's Name</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>

                            <!-- price -->
                            <div class="row mb-3">
                                <label for="price" class="col-md-2 col-form-label">Price (RM)</label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control" id="price" name="price" required>
                                </div>
                            </div>

                            <!-- duration (days) -->
                            <div class="row mb-3">
                                <label for="duration" class="col-md-2 col-form-label">Duration (days)</label>
                                <div class="col-md-3">
                                    <input type="number" class="form-control id="duration" name="duration" min="0" max="365" required">
                                </div>
                            </div>

                            <!-- features -->
                            <div class="row mb-3">
                                <label for="features" class="col-md-2 col-form-label">Features</label>
                                <div class="col-md-5">
                                    <textarea name="features" id="features" class="form-control" rows="3" placeholder="Separate with comma ( , )"></textarea>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-outline-dark mt-3">Create</button>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- membership plans card -->
                <div class="parents mx-3">
                    <?php foreach ($plans as $plan): 
                        // separate features list
                        $featuresList = explode(',', $plan['features']);
                    ?>
                        <div class="plan-card p-4">
                            <!-- plan name -->
                            <p><?= htmlspecialchars($plan['plan_name']) ?></p>

                            <!-- price -->
                            <p>RM <?= htmlspecialchars($plan['price']) ?> </p>

                            <!-- duration -->
                            <p><?= htmlspecialchars($plan['duration_days']) ?> days</p>

                            <!-- benefits -->
                            <ul>
                                <?php foreach ($featuresList as $feature): ?>
                                    <li><?= htmlspecialchars(trim($feature)) ?></li>
                                <?php endforeach; ?>
                            </ul>

                            <!-- edit and delete button -->
                            <div>
                                <a href="/plans?edit=<?= $plan['plan_id'] ?>" class="editBtn">
                                    <i class="bi bi-pencil text-secondary"></i>
                                    Edit
                                </a>

                                <form action="/plans" method="POST" class="d-inline" onsubmit="return confirm('Delete this plan?');">
                                    <input type="hidden" name="delete_id" value="<?= $plan['plan_id'] ?>">
                                    <button type="submit" class="btn-icon btn text-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>