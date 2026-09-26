<?php

require_once __DIR__ . '/includes/auth.php';

$path = trim($_SERVER['REQUEST_URI'], '/');

$path = parse_url($path, PHP_URL_PATH);

switch ($path) {
    case '':
    case 'login':
        include 'auth/login.php';
        break;

    case 'register':
        include 'auth/register.php';
        break;

    case 'dashboard':
        if (isGuest()) {
            header('Location: /login');
            exit;
        }
        include 'operations/dashboard.php';
        break;

    case 'logout':
        include 'auth/logout.php';
        break;

    default:
        include 'auth/login.php';
        break;
}