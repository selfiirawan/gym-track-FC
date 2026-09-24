<?php

$path = trim($_SERVER['REQUEST_URI'], '/');

$path = parse_url($path, PHP_URL_PATH);

switch ($path) {
    case '':
        include 'auth/login.php';
        break;

    case 'login':
        include 'auth/login.php';
        break;

    case 'register':
        include 'auth/register.php';
        break;

    case 'dashboard':
        include 'operations/dashboard.php';
        break;

    default:
        include 'auth/login.php';
        break;
}