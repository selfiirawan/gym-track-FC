<?php

// session_start();
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

$error = '';
$greeting = 'Log in to continue your journey.';

if (isset($_GET['registered'])) {
    $greeting = 'Welcome to the club. Log in to start your journey';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // find user through email
    $statement = $db->prepare("SELECT * FROM users WHERE email = ?");
    $statement->execute([$email]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    // verify password
    if ($user && password_verify($password, $user['password_hash'])) {
        // storing in user sessions
        $_SESSION['authenticated'] = true;
        $_SESSION['user'] = [
            'id' => $user['user_id'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        header('Location: /dashboard');
        exit;
    } else {
        $error = 'Invalid email or password!';
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
        <div class="main-content w-50 h-100 p-4">
            <div class="">
                <h3 class="fw-bold fs-4 mb-0">GymTrack</h3>
                <p class="text-secondary caption">Member & Club Management Portal</p>
            </div>

            <div class="content px-5 pt-4 m-5 <?= $error ? 'mt-3' : '' ?>">
                <h1 class="fw-bold mb-0">Welcome Back</h1>
                <p class="text-secondary p-0"><?= htmlspecialchars($greeting) ?></p>

                <!-- if email/pass didn't match -->
                <?php if ($error): ?>
                    <p class="alert alert-dark"><?= htmlspecialchars($error) ?></p>
                <?php endif; ?>

                <!-- the login form -->
                <form method="POST" action="/login">
                    <!-- email -->
                    <div class="mb-3 mt-4">
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
                            <input type="checkbox" class="form-check-input checkbox">
                            <label class="form-check-label">Remember me</label>
                        </div>

                        <a href="/" class="text-secondary">Forgot password?</a>
                    </div>

                    <!-- login button -->
                    <button type="submit" class="btn bg-white w-100 fw-bold mt-4 login-btn">Log In</button>
                    
                    <div class="divider m-2 d-flex align-items-center">
                        <span class="line"></span>
                        <span class="mx-2 text-secondary">or</span>
                        <span class="line"></span>
                    </div>

                    <button type="submit" class="btn bg-transparent w-100 text-white border-white fw-bold">
                        <img src="./assets/img/google.png" alt="google" width="16px" class="me-2">Log In with Google
                    </button>

                    <p class="text-secondary m-0 mt-5 caption">Don't have an account? <a href="/register" class="text-light">Sign up here</a></p>
                    <p class="text-secondary caption m-0 mb-3">GymTrack v1.0. Secure club access for GymZ Fitness.</p>
                </form>
            </div>
        </div>

        <div class="poster">
            <img src="../assets/img/poster3.png" alt="gym model poster">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>