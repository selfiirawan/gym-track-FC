<?php

session_start();
require_once __DIR__ . '/../config/database.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GymTrack</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

</head>
<body>
    <div class="app-container m-0 p-0 d-flex ">
        <div class="main-content w-50 h-100 p-5">
            <h3>GymTrack</h3>
            <p>Member & Club Management Portal</p>

            <h1>Welcome Back</h1>
            <p>Log in to continue your journey.</p>

            <!-- the login form -->
            <form method="POST" action="/login.php">
                <!-- email -->
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" placeholder="john.doe@gmail.com" required>
                </div>

                <!-- password -->
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" placeholder="●●●●●●●" required>
                </div>

                <!-- remember me and forget password -->
                <div class="d-flex justify-content-between">
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input">
                        <label class="form-check-label">Remember me</label>
                    </div>

                    <a href="/">Forgot password?</a>
                </div>

                <!-- login button -->
                <button type="submit" class="btn btn-primary">Log In</button>
                <p>or</p>
                <button type="submit" class="btn btn-primary">Log In with Google</button>

                <p>Don't have an account? <a href="/register.php">Sign up here</a></p>
                <p>GymTrack v1.0. Secure club access for GymZ Fitness.</p>
            </form>
        </div>

        <div class="poster">
            <img src="../assets/img/poster.png" alt="gym model poster">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>