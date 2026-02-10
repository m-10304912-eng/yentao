?>
<?php
session_start();
require_once 'db_connect.php';

// Auth
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Global Stats
$totalVotes = $pdo->query("SELECT COUNT(*) FROM UNDIAN")->fetchColumn();
$positions = $pdo->query("SELECT * FROM JAWATAN ORDER BY id_jawatan")->fetchAll();

// Dashboard Stats (copied from index.php)
$totalMurid = $pdo->query("SELECT COUNT(*) FROM PENGGUNA WHERE role='murid'")->fetchColumn();
$totalJawatan = $pdo->query("SELECT COUNT(*) FROM JAWATAN")->fetchColumn();
$rankings = $pdo->query("SELECT p.nama_murid, j.nama_jawatan, COUNT(u.id_undi) as jumlah_undi 
          FROM CALON c 
          JOIN PENGGUNA p ON c.no_murid = p.no_murid
          JOIN JAWATAN j ON c.id_jawatan = j.id_jawatan 
          LEFT JOIN UNDIAN u ON c.id_calon = u.id_calon 
          GROUP BY c.id_calon 
          ORDER BY j.id_jawatan, jumlah_undi DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="utf-8"/>
    <title>Keputusan & Papan Pemuka - Sistem Pengundian Jawatankuasa Lembaga Pustakawan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet"/>
    <style>body { font-family: 'Public Sans', sans-serif; background-color: #0a0e17; }</style>
</head>
<body class="bg-[#0a0e17] text-white">

<?php include 'navbar.php'; ?>

<main class="max-w-5xl mx-auto p-6">
    <header class="flex flex-col md:flex-row md:justify-between md:items-end mb-10 border-b border-white/10 pb-6">
        <div>
            <h1 class="text-3xl font-bold text-[#d4af37]">Keputusan & Papan Pemuka</h1>
            <span class="text-xs font-bold text-slate-500 uppercase">Sistem Pengundian Jawatankuasa Lembaga Pustakawan</span>
        </div>
        <div class="text-right mt-4 md:mt-0">
            <span class="text-xs font-bold text-slate-500 uppercase">Jumlah Undian</span>
            <p class="text-4xl font-bold"><?= $totalVotes ?></p>
        </div>
    </header>

    <!-- Dashboard Stats -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-[#101622] border border-slate-800 p-6 rounded-xl">
            <div class="text-xs text-slate-400 mb-1">Jumlah Murid</div>
            <div class="text-3xl font-bold text-white"><?= $totalMurid ?></div>
        </div>
        <div class="bg-[#101622] border border-slate-800 p-6 rounded-xl">
            <div class="text-xs text-slate-400 mb-1">Jumlah Jawatan</div>
            <div class="text-3xl font-bold text-white"><?= $totalJawatan ?></div>
        </div>
        <div class="bg-[#101622] border border-slate-800 p-6 rounded-xl">
            <div class="text-xs text-slate-400 mb-1">Jumlah Undian</div>
            <div class="text-3xl font-bold text-white"><?= $totalVotes ?></div>
        </div>
    </section>

    <!-- Keputusan Mengikut Jawatan -->
    <div class="grid gap-8">
        <?php foreach($positions as $pos): 
            $posID = $pos['id_jawatan'];
            
            // Query Results: JOIN CALON -> PENGGUNA
            $sql = "SELECT c.*, p.nama_murid, COUNT(u.id_undi) as total_undi 
                    FROM CALON c 
                    JOIN PENGGUNA p ON c.no_murid = p.no_murid 
                    LEFT JOIN UNDIAN u ON c.id_calon = u.id_calon 
                    WHERE c.id_jawatan = ? 
                    GROUP BY c.id_calon 
                    ORDER BY total_undi DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$posID]);
            $results = $stmt->fetchAll();

            $totalPosVotes = array_sum(array_column($results, 'total_undi'));
        ?>
        <div class="bg-[#101622] p-6 rounded-xl border border-slate-800">
            <h2 class="text-xl font-bold mb-4 border-l-4 border-[#d4af37] pl-3">
                <?= htmlspecialchars($pos['nama_jawatan']) ?>
            </h2>

            <div class="space-y-4">
                <?php foreach($results as $idx => $r): 
                    $percent = ($totalPosVotes > 0) ? round(($r['total_undi'] / $totalPosVotes * 100), 1) : 0;
                    $isWinner = ($idx === 0 && $r['total_undi'] > 0);
                ?>
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="<?= $isWinner ? 'text-yellow-400 font-bold' : 'text-slate-300' ?>">
                            <?= $idx + 1 ?>. <?= htmlspecialchars($r['nama_murid']) ?>
                            <?php if($isWinner) echo "👑"; ?>
                        </span>
                        <span class="font-mono text-slate-400"><?= $r['total_undi'] ?> undi (<?= $percent ?>%)</span>
                    </div>
                    <div class="h-2 w-full bg-slate-800 rounded-full overflow-hidden">
                        <div class="h-full <?= $isWinner ? 'bg-yellow-500' : 'bg-blue-600' ?>" style="width: <?= $percent ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if(empty($results)) echo "<p class='text-slate-500 italic'>Tiada calon.</p>"; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>
</body>
</html>
