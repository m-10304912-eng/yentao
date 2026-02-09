<?php
session_start();
require_once 'db_connect.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Stats Queries (MySQLi)
// 1. Total Murid
$res = $conn->query("SELECT COUNT(*) as total FROM PENGGUNA WHERE role='murid'");
$totalMurid = $res->fetch_assoc()['total'];

// 2. Total Undian
// Note: Using 'id_undi' as per new schema
$res = $conn->query("SELECT COUNT(*) as total FROM UNDIAN");
$totalUndi = $res->fetch_assoc()['total'];

// 3. Active Jawatan
$res = $conn->query("SELECT COUNT(*) as total FROM JAWATAN");
$totalJawatan = $res->fetch_assoc()['total'];

// Fetch Live Ranking Data
// Updated: JOIN PENGGUNA to get name, use id_undi for count
$query = "SELECT p.nama_murid, j.nama_jawatan, COUNT(u.id_undi) as jumlah_undi 
          FROM CALON c 
          JOIN PENGGUNA p ON c.no_murid = p.no_murid
          JOIN JAWATAN j ON c.id_jawatan = j.id_jawatan 
          LEFT JOIN UNDIAN u ON c.id_calon = u.id_calon 
          GROUP BY c.id_calon 
          ORDER BY j.id_jawatan, jumlah_undi DESC";

$rankings = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Papan Pemuka Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" rel="stylesheet" />
    <style>body { font-family: 'Public Sans', sans-serif; background-color: #0a0e17; color: white; }</style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="max-w-6xl mx-auto px-6 py-10">
        <header class="mb-10">
            <h1 class="text-3xl font-bold text-white">Papan Pemuka</h1>
            <p class="text-slate-400">Selamat datang, <?php echo htmlspecialchars($_SESSION['nama_murid']); ?></p>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="bg-[#101622] border border-slate-800 p-6 rounded-xl">
                <div class="flex items-center gap-4 mb-2">
                    <span class="p-3 bg-blue-500/10 rounded-lg text-blue-400 material-symbols-outlined">group</span>
                    <h3 class="text-slate-400 text-sm font-bold uppercase">Jumlah Murid</h3>
                </div>
                <p class="text-3xl font-bold ml-1"><?php echo $totalMurid; ?></p>
            </div>

            <div class="bg-[#101622] border border-slate-800 p-6 rounded-xl">
                <div class="flex items-center gap-4 mb-2">
                    <span class="p-3 bg-yellow-500/10 rounded-lg text-yellow-400 material-symbols-outlined">how_to_vote</span>
                    <h3 class="text-slate-400 text-sm font-bold uppercase">Undian Diterima</h3>
                </div>
                <p class="text-3xl font-bold ml-1"><?php echo $totalUndi; ?></p>
            </div>

            <div class="bg-[#101622] border border-slate-800 p-6 rounded-xl">
                <div class="flex items-center gap-4 mb-2">
                    <span class="p-3 bg-green-500/10 rounded-lg text-green-400 material-symbols-outlined">badge</span>
                    <h3 class="text-slate-400 text-sm font-bold uppercase">Jawatan Aktif</h3>
                </div>
                <p class="text-3xl font-bold ml-1"><?php echo $totalJawatan; ?></p>
            </div>
        </div>

        <!-- Live Table -->
        <div class="bg-[#101622] border border-slate-800 rounded-xl overflow-hidden">
            <div class="p-6 border-b border-slate-800">
                <h2 class="text-xl font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#d4af37]">leaderboard</span>
                    Statistik Calon
                </h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-[#0a0e17] text-slate-400 text-xs uppercase font-bold">
                        <tr>
                            <th class="px-6 py-4">Nama Calon</th>
                            <th class="px-6 py-4">Jawatan</th>
                            <th class="px-6 py-4 text-center">Undian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        <?php while($row = $rankings->fetch_assoc()): ?>
                        <tr class="hover:bg-slate-800/30">
                            <!-- Displaying P.NAMA_MURID now -->
                            <td class="px-6 py-4 font-medium text-white"><?php echo htmlspecialchars($row['nama_murid']); ?></td>
                            <td class="px-6 py-4 text-slate-300">
                                <span class="bg-blue-900/30 text-blue-300 px-2 py-1 rounded text-xs border border-blue-500/20">
                                    <?php echo htmlspecialchars($row['nama_jawatan']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-[#d4af37]"><?php echo $row['jumlah_undi']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if($rankings->num_rows == 0) echo "<tr><td colspan='3' class='p-6 text-center text-slate-500'>Tiada data undian.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>
