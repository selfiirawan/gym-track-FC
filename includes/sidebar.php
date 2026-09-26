<div>
    <p>GymTrack</p>

    <?php if (isStaff()): ?>
        <a href="/dashboard">Dashboard</a>
        <a href="/members">Members</a>
        <a href="/checkin">Check-In</a>
        <a href="/payments">Payment</a>
        <a href="/classes">Class Timetable</a>
    <?php endif; ?>

    <?php if (isMember()): ?>
        <a href="/dashboard">My Dashboard</a>
        <a href="/classes">Class Timetable</a>
        <a href="/member/history">My History</a>
    <?php endif; ?>
</div>