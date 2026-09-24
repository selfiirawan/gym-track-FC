<?php

session_start();
require_once __DIR__ . '/../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // check if email already registered
    $check = $db->prepare("SELECT * FROM users WHERE email = ?");
    $check->execute([$email]);

    if ($check->fetch()) {
        $error = 'The email is already registered';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // insert new member to db
        $statement = $db->prepare("INSERT INTO users (name, email, password_hash) VALUES (?,?,?)");
        $statement->execute([$name,$email,$hashedPassword]);

        $success = 'Welcome to the club. Log in to start your journey';
        
        header('Location: login.php');
        exit;
    }
}

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
            <p>New Member Registration & Portal Onboarding</p>

            <h1>Create Your Account</h1>
            <p>Join GymZ and start your journey.</p>

            <!-- the signup form -->
            <form method="POST" action="/register.php">
                 <!-- name -->
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control" name="name" placeholder="Enter your name" required>
                </div>

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

                <!-- login button -->
                <button type="submit" class="btn btn-primary">Create Account</button>
                <p>or</p>
                <button type="submit" class="btn btn-primary">Sign Up with Google</button>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input">
                    <label class="form-check-label">I agree to the <a href="/">Terms of Service</a> and <a href="/">Privacy Policy</a></label>
                </div>

                <p>Already have an account? <a href="/register.php">Log in here</a></p>
            </form>
        </div>

        <div class="poster">
            <img src="../assets/img/poster.png" alt="gym model poster">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>