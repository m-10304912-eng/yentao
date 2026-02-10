<?php
session_start();
require_once 'db_connect.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$no_murid_curr = $_SESSION['user_id'];
$message = '';

// Handle Vote
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_vote'])) {
    $id_calon = $_POST['id_calon'];
    // We need id_jawatan to prevent double voting. It is passed via hidden field.
    $id_jawatan = $_POST['id_jawatan'];

    // Check if double vote
    // Note: User's UNDIAN table has (no_murid, id_calon). 
    // To check per position, we must query if user voted for ANY candidate sharing the same position.
    // Querying UNDIAN -> JOIN CALON to check id_jawatan
    $check = $pdo->prepare("SELECT u.id_undi FROM UNDIAN u 
                            JOIN CALON c ON u.id_calon = c.id_calon 
                            WHERE u.no_murid = ? AND c.id_jawatan = ?");
    $check->execute([$no_murid_curr, $id_jawatan]);

    if ($check->rowCount() > 0) {
        $message = "<div class='bg-red-900/50 text-red-200 p-4 rounded mb-6'>Anda sudah mengundi untuk jawatan ini.</div>";
    } else {
        // Casting Vote
        $stmt = $pdo->prepare("INSERT INTO UNDIAN (no_murid, id_calon) VALUES (?, ?)");
        if ($stmt->execute([$no_murid_curr, $id_calon])) {
            $message = "<div class='bg-green-900/50 text-green-200 p-4 rounded mb-6'>Undian berjaya!</div>";
        }
    }
}

// Fetch Positions
$positions = $pdo->query("SELECT * FROM JAWATAN ORDER BY id_jawatan")->fetchAll();

?>
<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="utf-8"/>
    <title>Undi - Sistem Pustakawan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet"/>
    <style>body { font-family: 'Public Sans', sans-serif; background-color: #0a0e17; }</style>
</head>
<body class="bg-[#0a0e17] text-white">


<?php include 'navbar.php'; ?>

<main class="max-w-5xl mx-auto p-6">
    <header class="mb-10 border-b border-white/10 pb-6">
        <h1 class="text-3xl font-bold text-[#d4af37]">Undian Jawatankuasa</h1>
        <span class="text-xs font-bold text-slate-500 uppercase">Sistem Pengundian Jawatankuasa Lembaga Pustakawan</span>
    </header>
</main>

<main class="max-w-7xl mx-auto p-6">
    <h1 class="text-3xl font-bold mb-2 text-[#d4af37]">Portal Undian</h1>
    <p class="text-slate-400 mb-8">Sila pilih satu calon bagi setiap jawatan.</p>

    <?= $message ?>

    <?php foreach($positions as $pos): 
        // Check Status
        $posID = $pos['id_jawatan'];
        $checkVote = $pdo->prepare("SELECT u.id_undi FROM UNDIAN u 
                                    JOIN CALON c ON u.id_calon = c.id_calon 
                                    WHERE u.no_murid = ? AND c.id_jawatan = ?");
        $checkVote->execute([$no_murid_curr, $posID]);
        $hasVoted = ($checkVote->rowCount() > 0);

        // Fetch Candidates for this Position (JOIN PENGGUNA)
        $c_stm = $pdo->prepare("SELECT c.*, p.nama_murid, p.kelas_murid 
                                FROM CALON c 
                                JOIN PENGGUNA p ON c.no_murid = p.no_murid 
                                WHERE c.id_jawatan = ?");
        $c_stm->execute([$posID]);
        $candidates = $c_stm->fetchAll();
    ?>
    
    <div class="mb-12">
        <div class="flex items-center gap-4 mb-6 border-b border-white/10 pb-2">
            <h2 class="text-2xl font-bold"><?= htmlspecialchars($pos['nama_jawatan']) ?></h2>
            <?php if($hasVoted): ?>
                <span class="bg-green-600 px-2 py-0.5 rounded text-sm font-bold">SOKONGAN DIBERI</span>
            <?php else: ?>
                <span class="bg-blue-600 px-2 py-0.5 rounded text-sm font-bold animate-pulse">SILA UNDI</span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($candidates as $c): ?>
            <div class="bg-[#101622] border border-slate-800 rounded-xl overflow-hidden hover:border-blue-500 transition-colors group">
                <!-- Image -->
                <?php if($c['gambar']): ?>
                    <div class="h-48 bg-cover bg-center" style="background-image: url('<?= htmlspecialchars($c['gambar']) ?>')"></div>
                <?php else: ?>
                    <div class="h-48 bg-slate-800 flex items-center justify-center text-slate-600 text-4xl font-bold">?</div>
                <?php endif; ?>
                
                <div class="p-5">
                    <h3 class="font-bold text-lg mb-1 truncate"><?= htmlspecialchars($c['nama_murid']) ?></h3>
                    <p class="text-slate-400 text-sm mb-2"><?= htmlspecialchars($c['kelas_murid']) ?></p>
                    <p class="text-slate-500 text-xs italic mb-4">"<?= htmlspecialchars($c['manifesto']) ?>"</p>

                    <?php if(!$hasVoted): ?>
                        <form method="POST">
                            <input type="hidden" name="id_calon" value="<?= $c['id_calon'] ?>">
                            <input type="hidden" name="id_jawatan" value="<?= $posID ?>">
                            <button name="submit_vote" class="w-full bg-[#d4af37] text-black font-bold py-2 rounded hover:bg-yellow-600 transition" onclick="return confirm('Sahkan undian?')">
                                Undi Calon Ini
                            </button>
                        </form>
                    <?php else: ?>
                        <button disabled class="w-full bg-slate-800 text-slate-500 font-bold py-2 rounded cursor-not-allowed">
                            Anda Telah Mengundi
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if(empty($candidates)) echo "<p class='text-slate-500'>Tiada calon.</p>"; ?>
        </div>
    </div>
    <?php endforeach; ?>

</main>
</body>
</html>
