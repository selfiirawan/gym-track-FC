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