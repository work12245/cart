<?php

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- DATABASE CONFIGURATION ---
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'quickkart_db';

// --- Establish DB Connection ---
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- SITE CONFIGURATION ---
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/QuickKart_v.2/'); // Adjust folder name if needed
define('CURRENCY_SYMBOL', '₹');

// --- Helper Functions ---
function redirect($url) {
    echo "<script>window.location.href='$url'</script>";
    exit();
}

function check_login() {
    if (!isset($_SESSION['user_id'])) {
        redirect(SITE_URL . 'login.php');
    }
}

function check_admin_login() {
    if (!isset($_SESSION['admin_id'])) {
        redirect(SITE_URL . 'admin/login.php');
    }
}
?>