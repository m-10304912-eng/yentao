<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo "Sila log masuk.";
    exit;
}

echo "<h1 class='text-2xl font-bold text-[#d4af37] mb-4'>Ujian Sambungan Pengguna</h1>";
echo "<span class='text-xs font-bold text-slate-500 uppercase mb-4 block'>Sistem Pengundian Jawatankuasa Lembaga Pustakawan</span>";
echo "Berjaya sambung sebagai: " . htmlspecialchars($_SESSION['nama_murid']) . " (" . htmlspecialchars($_SESSION['user_id']) . ")<br>";
?>
