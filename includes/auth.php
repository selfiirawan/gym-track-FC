<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function preventCaching() {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

// user role = 'admin';
function isAdmin() {
    if (isset($_SESSION['user'])) {
        if ($_SESSION['user']['role'] === 'admin') {
            return true;
        }
    }
    return false;
}

// user role = 'staff' & 'admin'
function isStaff() {
    if (isset($_SESSION['user'])) {
        if ($_SESSION['user']['role'] === 'staff' ||
        $_SESSION['user']['role'] === 'admin') {
            return true;
        }
    }
    return false;
}

// user role = 'member'
function isMember() {
    if (isset($_SESSION['user'])) {
        if ($_SESSION['user']['role'] === 'member'){
            return true;
        }
    }
    return false;
}

// guest
function isGuest() {
    return ! isset($_SESSION['user']);
}

// get logged in member db 
function getCurrentMember($db) {
    if (!isset($_SESSION['user']['id'])) {
        return null;
    }

    $stmt = $db->prepare("
        SELECT m.*, p.plan_name, GREATEST(DATEDIFF(expiry_date, CURDATE()), 0) AS days_left 
        FROM members m
        JOIN membership_plans p ON m.plan_id = p.plan_id
        WHERE m.user_id = ?
    ");
    $stmt->execute([$_SESSION['user']['id']]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}