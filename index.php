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

        if (isMember()) {
            include 'member/dashboard.php';
        } else {
            include 'operations/dashboard.php';
        }
        break;

    case 'members':
        include 'operations/members.php';
        break;

    case 'payments':
        include 'operations/payments.php';
        break;

    case 'plans':
        if (!isStaff()) {
            header('Location: /dashboard');
            exit;
        }
        include 'operations/plans.php';
        break;

    case 'checkin':
        include 'operations/checkin.php';
        break;

    case 'logout':
        include 'auth/logout.php';
        break;

    case 'staff':
        include 'admin/staff.php';
        break;

    case 'reports':
        if (!isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
        include 'admin/reports.php';
        break;

    case 'classes':
        include 'operations/classes.php';
        break;

    default:
        include '404.php';
        break;
}