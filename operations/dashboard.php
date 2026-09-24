<?php

require_once __DIR__ . '/../includes/auth.php';

if (!isset($_SESSION['user'])) {
    header('Location: /../auth/login.php');
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
    <h1>Hello, Admin</h1>
</body>
</html>