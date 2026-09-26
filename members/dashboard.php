<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (isGuest()) {
    header('Location: /login');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymTrack by GymZ</title>
</head>
<body>
    <div>
        <?php include __DIR__ . '/../includes/sidebar.php'; ?>

        <div>
            <h1>Hello, Member</h1>
            <h3>Welcome to your first dashboard</h3>
        </div>
    </div>

   

    <a href="/login">Log Out</a>
</body>
</html>