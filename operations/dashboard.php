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
            <div class="content-navbar d-flex justify-content-between align-items-center">
                <form class="d-flex h-100">
                    <i class="bi bi-search m-0 p-0"></i>
                    <input type="search" class="form-control m-0 p-0" placeholder="Search...">
                </form>

                <div class="m-0 p-0 d-flex align-items-center">
                    <a href="#" class="m-0 p-0">
                        <i class="bi bi-bell m-0 p-0"></i>
                        <!-- add circle if there's notif -->
                    </a>

                    <!-- dropdown here -->
                    <div class="m-0 p-0 d-flex">
                        <!-- user image -->
                        <button class="btn dropdown-toggle d-flex align-items-center m-0 p-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle m-0 p-0"></i> <!-- temporary -->

                            <div class="user-role">
                                <p><?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></p>
                                <p><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Guest') ?></p>
                            </div>
                        </button>
                        
                        <ul class="dropdown-menu">
                            <li><a href="#" class="dropdown-item">Profile</a></li>
                            <li><a href="/logout" class="dropdown-item">Log Out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- main content -->
            <h1>Hello, <?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></h1>
            <h3>Welcome to your first dashboard</h3>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>

