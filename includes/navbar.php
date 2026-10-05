<?php
$currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$searchAction = 'members';
$searchParam = 'q';
$searchPlaceholder = 'Search...';

if ($currentPath === 'checkin') {
    $searchAction = 'checkin';
    $searchParam = 'log';
    $searchPlaceholder = 'Search check-in log...';
}
?>

<div class="content-navbar d-flex justify-content-between align-items-center pt-2">
    <!-- search bar -->
    <form action="/<?= htmlspecialchars($searchAction) ?>" method="GET" class="d-flex align-items-center gap-2 h-100 ms-3 w-25 p-3 pb-0 search-form">
        <i class="bi bi-search m-0 p-0"></i>
        <input type="search" name="<?= $searchParam ?>" class="form-control text-dark m-0 p-1 ps-3 bg-transparent" placeholder="<?= $searchPlaceholder ?>">
    </form>

    <div class="d-flex align-items-center right-nav">
        <!-- <a href="#" class="">
            <i class="bi bi-bell me-2 p-0"></i>
            === add circle if there's notif ===
            === future plan ===
        </a> -->

        <!-- dropdown here -->
        <div class=" p-0 pb-0 d-flex dropdown">
            <!-- user image -->
            <button class="btn dropdown-toggle d-flex align-items-center mx-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">

                <!-- profile pic -->
                <div class="avatar-initials me-3 rounded-circle d-flex justify-content-center align-items-center fw-bold text-white">
                    <?= htmlspecialchars(strtoupper(substr($_SESSION['user']['name'], 0, 1))) ?>
                </div>

                <!-- user's name and role -->
                <div class="user-role d-flex flex-column text-start text-dark pe-2">
                    <p class="fw-bold"><?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?></p>
                    <p class="text-secondary text-capitalize navbar-role"><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Guest') ?></p>
                </div>
            </button>
            
            <ul class="dropdown-menu">
                <li><a href="#" class="dropdown-item">Profile</a></li>
                <li><a href="/logout" class="dropdown-item">Log Out</a></li>
            </ul>
        </div>
    </div>
</div>