<?php
    $roleLabel = [
        'admin' => 'Admin',
        'staff' => 'Staff',
        'member' => 'Member'
    ];

    $role = $roleLabel[$_SESSION['user']['role']] ?? 'Guest';
?>

<div class="sidebar d-flex flex-column">
    <div class="role">
        <p>GymZ <?= htmlspecialchars($role) ?></p>
    </div>

    <ul>
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
                <a href="/admin/plans">
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

        <li>
            
        </li>
    </ul>

    <a href="/logout" class="logout">
        <i class="bi bi-box-arrow-right"></i>
        <span>Log Out</span>
    </a>
</div>