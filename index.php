<?php
session_start();
require_once 'db_connect.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}


// Redirect admin to user view (results page) by default
header("Location: results.php");
exit;
