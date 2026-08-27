<?php
session_start();

// Check if the admin is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin.php");
    exit;
}

// The login form now lives as a popup modal on home.php - this script is
// just the POST handler behind it, so there is nothing to render here.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: home.php");
    exit;
}

// Hardcoded admin credentials
$adminEmail = "admin@gmail.com";
$adminPassword = "12345678";

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === $adminEmail && $password === $adminPassword) {
    $_SESSION['admin_logged_in'] = true;
    header("Location: admin.php");
    exit;
}

header("Location: home.php?login_error=1");
exit;
