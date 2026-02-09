<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "sistem_undian_pustakawan";

// 1. Connection for MySQLi (used by Login, Register, Index, Vote, Results)
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Sambungan MySQLi Gagal: " . mysqli_connect_error());
}
$conn->set_charset("utf8mb4");

// 2. Connection for PDO (used by admin_manage.php)
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Sambungan PDO Gagal: " . $e->getMessage());
}
?>
