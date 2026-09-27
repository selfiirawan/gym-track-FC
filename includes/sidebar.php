<?php
    $roleLabel = [
        'admin' => 'Admin',
        'staff' => 'Staff',
        'member' => 'Member'
    ];

    $role = $roleLabel[$_SESSION['user']['role']] ?? 'Guest';
?>

<div class="sidebar d-flex flex-column">
    <p>GymZ <?= htmlspecialchars($role) ?></p>

    <!-- staff & admin -->
    <?php if (isStaff()): ?>
        <a href="/dashboard">Dashboard</a>
        <a href="/members">Members</a>
        <a href="/checkin">Check-In</a>
        <a href="/payments">Payment</a>
        <a href="/classes">Class Timetable</a>
    <?php endif; ?>
    
    <!-- member only -->
    <?php if (isMember()): ?>
        <a href="/dashboard">My Dashboard</a>
        <a href="/classes">Class Timetable</a>
        <a href="/member/history">My History</a>
    <?php endif; ?>

    <?php if (isAdmin()): ?>
        <a href="/admin/plans">Plans</a>
        <a href="/admin/classes">Classes</a>
        <a href="/admin/staff">Staff</a>
        <a href="/admin/reports">Reports</a>
    <?php endif; ?>
</div>