<?php
// session_start();
require_once __DIR__ . '/../includes/auth.php';
session_destroy();

header('Location: /login');
exit;