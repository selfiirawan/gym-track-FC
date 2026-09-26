<?php

// session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$error = '';

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
        $statement = $db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,'member')");
        $statement->execute([$name,$email,$hashedPassword]);
        
        header('Location: /login?registered=1');
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="app-container m-0 p-0 d-flex">
        <div class="main-content w-50 h-100 p-4">
            <div>
                <h3 class="fw-bold fs-4 mb-0">GymTrack</h3>
                <p class="text-secondary caption">New Member Registration & Portal Onboarding</p>
            </div>

            <div class="content px-5 pt-4 m-0 mt-5 <?= $error ? 'mt-md-3' : '' ?> m-md-5 mt-md-4">
                <h1 class="fw-bold mb-0">Create Your Account</h1>
                <p class="text-secondary p-0">Join GymZ and start your journey.</p>

                <?php if ($error) { ?>
                    <p class="alert alert-dark"><?= htmlspecialchars($error) ?></p>
                <?php } ?>

                <!-- the signup form -->
                <form method="POST" action="/register">
                    <!-- name -->
                    <div class="mb-3 mt-4">
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
                    <button type="submit" class="btn bg-white w-100 fw-bold mt-4 login-btn">Create Account</button>
                    
                    <!-- divider  -->
                    <div class="divider m-2 d-flex align-items-center">
                        <span class="line"></span>
                        <span class="mx-2 text-secondary">or</span>
                        <span class="line"></span>
                    </div>

                    <!-- sign up with google -->
                    <button type="submit" class="btn bg-transparent text-white border-white fw-bold w-100">
                        <img src="./assets/img/google.png" alt="google" width="16px" class="me-2">Sign Up with Google
                    </button>

                    <div class="form-check mt-3 caption">
                        <input type="checkbox" class="form-check-input checkbox">
                        <label class="form-check-label text-secondary">I agree to the <a href="/" class="text-light">Terms of Service</a> and <a href="/" class="text-light">Privacy Policy</a></label>
                    </div>
                </form>

                <p class="caption text-secondary mt-3">Already have an account? <a href="/login" class="text-white">Log in here</a></p>
            </div>
        </div>

        <div class="poster">
            <img src="../assets/img/poster3.png" alt="gym model poster">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>