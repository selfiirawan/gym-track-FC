<?php
    $roleLabel = [
        'admin' => 'Admin',
        'staff' => 'Staff',
        'member' => 'Member'
    ];

    $role = $roleLabel[$_SESSION['user']['role']] ?? 'Guest';
?>

<div class="sidebar p-3 d-flex flex-column position-sticky top-0 left-0 bottom-0">
    <div class="role d-flex mb-2">
        <img src="../assets/img/logo.png" alt="GT logo" width="50px">
        <p class="fw-bold h-100 align-content-center px-1">GymZ <?= htmlspecialchars($role) ?></p>
    </div><hr class="m-0">

    <ul class="px-3 h-100">
        <!-- staff & admin -->
        <?php if (isStaff()): ?>
            <li>
                <a href="/dashboard">
                    <i class="bi bi-columns-gap"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="/members">
                    <i class="bi bi-people"></i>
                    <span>Members</span>
                </a>
            </li>

            <li>
                <a href="/checkin">
                    <i class="bi bi-clipboard2-check"></i>
                    <span>Check-In</span>
                </a>
            </li>

            <li>
                <a href="/payments">
                    <i class="bi bi-cash-stack"></i>
                    <span>Payment</span>
                </a>
            </li>

            <li>
                <a href="/classes">
                    <i class="bi bi-calendar3"></i>
                    <span>Class Timetable</span>
                </a>
            </li>
        <?php endif; ?>
        
        <!-- member only -->
        <?php if (isMember()): ?>
            <li>
                <a href="/dashboard">
                    <i class="bi bi-columns-gap"></i>
                    <span>My Dashboard</span>
                </a>
            </li>

            <li>
                <a href="/classes">
                    <i class="bi bi-calendar"></i>
                    <span>Class Timetable</span>
                </a>
            </li>

            <li>
                <a href="/member/history">
                    <i class="bi bi-clock-history"></i>
                    <span>My History</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- visible in admin only -->
        <?php if (isAdmin()): ?>
            <li>
                <a href="/plans">
                    <i class="bi bi-credit-card"></i>
                    <span>Plans</span>
                </a>
            </li>

            <li>
                <a href="/admin/classes">
                    <i class="bi bi-calendar-plus"></i>
                    <span>Classes</span>
                </a>
            </li>

            <li>
                <a href="/admin/staff">
                    <i class="bi bi-person-badge"></i>
                    <span>Staff</span>
                </a>
            </li>
            
            <li>
                <a href="/admin/reports">
                    <i class="bi bi-bar-chart-line"></i>
                    <span>Reports</span>
                </a>
            </li>
        <?php endif; ?>

        <!-- <li>
            <a href="#">
                <i class="bi bi-gear"></i>
                <span>Settings</span>
            </a>
        </li> -->
    </ul>
    
    <hr class="mt-auto">
    
    <a href="/logout" class="logout p-2 px-4">
        <i class="bi bi-box-arrow-right"></i>
        <span>Log Out</span>
    </a>
</div>