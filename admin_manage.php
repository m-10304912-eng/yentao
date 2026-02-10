<?php
session_start();
require_once 'db_connect.php';

// 1. Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$message = '';
$error = '';

/**
 * Helper to parse CSV securely
 */
function parseValues($line) {
    return str_getcsv($line);
}

// 2. Handle POST Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // --- ADD POSITION ---
        if (isset($_POST['add_position'])) {
            $stmt = $pdo->prepare("INSERT INTO JAWATAN (nama_jawatan) VALUES (?)");
            $stmt->execute([trim($_POST['nama_jawatan'])]);
            $message = "Jawatan ditambah.";
        }

        // --- DELETE POSITION ---
        if (isset($_POST['delete_position'])) {
            $stmt = $pdo->prepare("DELETE FROM JAWATAN WHERE id_jawatan = ?");
            $stmt->execute([$_POST['id_jawatan']]);
            $message = "Jawatan dipadam.";
        }

        // --- ADD CANDIDATE ---
        if (isset($_POST['add_candidate'])) {
            $check = $pdo->prepare("SELECT no_murid FROM PENGGUNA WHERE no_murid = ?");
            $check->execute([$_POST['no_murid']]);
            
            if ($check->rowCount() > 0) {
                $stmt = $pdo->prepare("INSERT INTO CALON (no_murid, id_jawatan, manifesto, gambar) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['no_murid'], 
                    $_POST['id_jawatan'], 
                    $_POST['manifesto'], 
                    $_POST['gambar']
                ]);
                $message = "Calon berjaya didaftarkan.";
            } else {
                $error = "No. Murid tidak ditemui. Sila daftar murid dahulu.";
            }
        }

        // --- DELETE CANDIDATE ---
        if (isset($_POST['delete_candidate'])) {
            $stmt = $pdo->prepare("DELETE FROM CALON WHERE id_calon = ?");
            $stmt->execute([$_POST['id_calon']]);
            $message = "Calon dipadam.";
        }

        // --- BULK UPLOAD PAPAN KENYATAAN ---
        if (isset($_POST['bulk_upload_confirm']) && isset($_SESSION['csv_data'])) {
            $csv_data = $_SESSION['csv_data'];
            $count = 0;
            $stmt = $pdo->prepare("INSERT INTO papan_kenyataan (title, content, date, author) VALUES (?, ?, ?, ?)");
            
            foreach ($csv_data as $row) {
                // Ensure row has 4 columns (Title, Content, Date, Author)
                if (count($row) >= 4) {
                    $stmt->execute([$row[0], $row[1], $row[2], $row[3]]);
                    $count++;
                }
            }
            unset($_SESSION['csv_data']); // Clear session
            $message = "$count rekod berjaya dimuat naik ke Papan Kenyataan.";
        }
        
    } catch (PDOException $e) {
        $error = "Ralat: " . $e->getMessage();
    }
}

// --- HANDLE CSV FILE PREVIEW ---
$preview_data = [];
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
    $file = $_FILES['csv_file']['tmp_name'];
    $h = fopen($file, "r");
    
    // Header check
    $headers = fgetcsv($h, 1000, ",");
    $valid_headers = ['Title', 'Content', 'Date', 'Author'];
    
    if ($headers === $valid_headers) {
        while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {
            $preview_data[] = $data;
        }
        $_SESSION['csv_data'] = $preview_data; // Store for confirmation
    } else {
        $error = "Format CSV Salah. Header mesti: Title, Content, Date, Author";
    }
    fclose($h);
}

// 3. Fetch Data
$sql_calon = "SELECT c.*, p.nama_murid, p.kelas_murid, j.nama_jawatan 
              FROM CALON c 
              JOIN PENGGUNA p ON c.no_murid = p.no_murid 
              JOIN JAWATAN j ON c.id_jawatan = j.id_jawatan 
              ORDER BY j.id_jawatan, p.nama_murid";
$candidates = $pdo->query($sql_calon)->fetchAll();
$positions = $pdo->query("SELECT * FROM JAWATAN ORDER BY id_jawatan")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ms" class="dark">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Papan Pemuka Neon | Jawatankuasa Pustakawan</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&amp;family=JetBrains+Mono:wght@400;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "neon-blue": "#00f3ff",
                        "neon-gold": "#ffcc33",
                        "neon-purple": "#bc13fe",
                        "background-night": "#05070a",
                        "card-night": "#0d1117",
                        "border-night": "#1f2937"
                    },
                    fontFamily: {
                        "sans": ["Space Grotesk", "sans-serif"],
                        "mono": ["JetBrains Mono", "monospace"]
                    }
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .neon-shadow-blue { box-shadow: 0 0 10px rgba(0, 243, 255, 0.3), inset 0 0 5px rgba(0, 243, 255, 0.2); }
            .neon-shadow-gold { box-shadow: 0 0 10px rgba(255, 204, 51, 0.3), inset 0 0 5px rgba(255, 204, 51, 0.2); }
            .neon-border-blue { border: 1px solid rgba(0, 243, 255, 0.5); }
            .neon-border-gold { border: 1px solid rgba(255, 204, 51, 0.5); }
            .glow-text-blue { text-shadow: 0 0 8px rgba(0, 243, 255, 0.6); }
            .glow-text-gold { text-shadow: 0 0 8px rgba(255, 204, 51, 0.6); }
        }
        body { background-color: #05070a; color: #e5e7eb; font-family: 'Space Grotesk', sans-serif; }
    </style>
</head>
<body class="min-h-screen">
<div class="flex h-screen overflow-hidden">
    
    <!-- Sidebar -->
    <aside class="w-64 flex-shrink-0 bg-card-night border-r border-white/5 flex flex-col justify-between p-4">
        <div class="flex flex-col gap-10">
            <div class="flex items-center gap-3 px-2">
                <div class="relative">
                    <span class="material-symbols-outlined text-neon-blue text-3xl glow-text-blue">auto_stories</span>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-white text-lg font-bold tracking-tighter leading-none">NEON<span class="text-neon-gold">PUSTAKA</span></h1>
                    <p class="text-gray-500 text-[10px] font-mono tracking-widest mt-1 uppercase">Teras Pustakawan v2.0</p>
                </div>
            </div>
            <nav class="flex flex-col gap-2">
                <a href="admin_dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-md text-gray-500 hover:text-neon-blue hover:bg-white/5 transition-all cursor-pointer group">
                    <span class="material-symbols-outlined">analytics</span>
                    <p class="text-sm font-medium tracking-wide uppercase">Papan Pemuka</p>
                </a>
                <div class="flex items-center gap-3 px-4 py-3 rounded-md bg-white/5 border-l-2 border-neon-blue cursor-pointer group">
                    <span class="material-symbols-outlined text-neon-blue">dashboard</span>
                    <p class="text-sm font-bold tracking-wide uppercase text-neon-blue">Pengurusan Data</p>
                </div>
            </nav>
        </div>
        <div class="flex flex-col gap-2 border-t border-white/5 pt-4">
            <a href="login.php" class="flex items-center gap-3 px-4 py-3 rounded-md text-gray-500 hover:text-red-400 transition-all cursor-pointer">
                <span class="material-symbols-outlined">logout</span>
                <p class="text-sm font-medium tracking-wide uppercase">Tamat Sesi</p>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto bg-background-night p-8">
        <div class="max-w-6xl mx-auto space-y-12">
            
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-end md:justify-between border-b border-white/10 pb-6 mb-8">
                <div>
                    <h2 class="text-[#d4af37] text-3xl font-bold tracking-tighter uppercase">Urusan Pentadbir</h2>
                    <span class="text-xs font-bold text-slate-500 uppercase">Sistem Pengundian Jawatankuasa Lembaga Pustakawan</span>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="results.php" class="px-4 py-2 border border-neon-blue text-neon-blue text-xs font-bold uppercase tracking-widest rounded hover:bg-neon-blue hover:text-black transition-all">Laman Pengguna</a>
                </div>
                    <div class="flex items-center gap-2 mt-2 font-mono text-xs text-neon-blue/70">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neon-blue opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-neon-blue"></span>
                        </span>
                        <span>SISTEM AKTIF // SESI: <?= date('Y') ?></span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-card-night p-6 neon-border-blue neon-shadow-blue relative overflow-hidden">
                    <div class="flex justify-between items-start mb-6">
                        <span class="material-symbols-outlined text-3xl text-neon-blue">group</span>
                        <span class="font-mono text-[10px] text-neon-blue border border-neon-blue/30 px-2 py-0.5">TERDAFTAR</span>
                    </div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Jumlah Calon</p>
                    <h3 class="text-white text-4xl font-black glow-text-blue"><?= count($candidates) ?></h3>
                </div>
                <div class="bg-card-night p-6 neon-border-gold neon-shadow-gold relative overflow-hidden">
                    <div class="flex justify-between items-start mb-6">
                        <span class="material-symbols-outlined text-3xl text-neon-gold">military_tech</span>
                        <span class="font-mono text-[10px] text-neon-gold border border-neon-gold/30 px-2 py-0.5">AKTIF</span>
                    </div>
                    <p class="text-gray-400 text-xs font-bold uppercase tracking-widest mb-1">Jawatan Dipertandingkan</p>
                    <h3 class="text-white text-4xl font-black glow-text-gold"><?= count($positions) ?></h3>
                </div>
            </div>

            <!-- Bulk Upload Section -->
            <div class="bg-card-night border border-white/5 p-8 rounded-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-neon-purple/5 -mr-16 -mt-16 rounded-full blur-3xl"></div>
                <h3 class="text-white font-bold uppercase tracking-widest text-lg mb-6 flex items-center gap-2">
                    <span class="material-symbols-outlined text-neon-purple">upload_file</span> Muat Naik Papan Kenyataan (Bulk)
                </h3>
                
                <form method="POST" enctype="multipart/form-data" class="flex items-end gap-4">
                    <div class="flex-1">
                        <label class="block text-gray-500 text-xs font-mono uppercase mb-2">Pilih Fail CSV (.csv)</label>
                        <input type="file" name="csv_file" accept=".csv" required class="block w-full text-sm text-gray-400 font-mono file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-neon-purple/20 file:text-neon-purple hover:file:bg-neon-purple/30"/>
                        <p class="text-[10px] text-gray-600 mt-1">Header wajib: Title, Content, Date, Author</p>
                    </div>
                    <button class="px-6 py-2 bg-neon-purple/20 border border-neon-purple text-neon-purple text-xs font-black uppercase tracking-widest hover:bg-neon-purple hover:text-black transition-all">
                        PREVIEW DATA
                    </button>
                </form>

                <?php if (!empty($preview_data) || isset($_SESSION['csv_data'])): ?>
                    <?php $data_to_show = $preview_data ?: $_SESSION['csv_data']; ?>
                    <div class="mt-8">
                        <h4 class="text-neon-purple text-xs font-bold uppercase mb-4">Pratonton Data</h4>
                        <div class="overflow-x-auto border border-white/10 rounded">
                            <table class="w-full text-left text-xs font-mono text-gray-400">
                                <thead class="bg-white/5 text-neon-purple">
                                    <tr>
                                        <th class="p-3">Title</th>
                                        <th class="p-3">Content</th>
                                        <th class="p-3">Date</th>
                                        <th class="p-3">Author</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    <?php foreach ($data_to_show as $row): ?>
                                    <tr>
                                        <td class="p-3"><?= htmlspecialchars($row[0] ?? '') ?></td>
                                        <td class="p-3"><?= htmlspecialchars($row[1] ?? '') ?></td>
                                        <td class="p-3"><?= htmlspecialchars($row[2] ?? '') ?></td>
                                        <td class="p-3"><?= htmlspecialchars($row[3] ?? '') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="bulk_upload_confirm" value="1">
                            <button class="w-full py-3 bg-neon-purple text-black font-black uppercase tracking-widest hover:brightness-110">
                                SAHKAN & IMPORT
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Forms Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Add Candidate -->
                <div class="bg-card-night border border-white/5 p-6 rounded-xl">
                    <h3 class="text-neon-blue font-bold uppercase tracking-widest text-xs mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined">person_add</span> Tambah Calon
                    </h3>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="add_candidate" value="1">
                        <input name="no_murid" type="text" placeholder="No. Murid (Cth: D6557)" required class="w-full bg-background-night border border-white/10 text-white text-xs p-3 focus:border-neon-blue focus:ring-0 placeholder-gray-600 rounded">
                        <select name="id_jawatan" required class="w-full bg-background-night border border-white/10 text-white text-xs p-3 focus:border-neon-blue focus:ring-0 rounded">
                            <option value="">Pilih Jawatan...</option>
                            <?php foreach($positions as $pos): ?>
                                <option value="<?= $pos['id_jawatan'] ?>"><?= htmlspecialchars($pos['nama_jawatan']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <textarea name="manifesto" placeholder="Manifesto Calon..." class="w-full bg-background-night border border-white/10 text-white text-xs p-3 focus:border-neon-blue focus:ring-0 placeholder-gray-600 rounded h-20"></textarea>
                        <input name="gambar" type="text" placeholder="URL Gambar (Pilihan)" class="w-full bg-background-night border border-white/10 text-white text-xs p-3 focus:border-neon-blue focus:ring-0 placeholder-gray-600 rounded">
                        <button class="w-full py-3 bg-neon-blue/10 border border-neon-blue text-neon-blue font-bold uppercase text-xs tracking-widest hover:bg-neon-blue hover:text-black transition-all">Simpan Calon</button>
                    </form>
                </div>

                <!-- Candidates Table -->
                <div class="lg:col-span-2 bg-card-night border border-white/5 p-6 rounded-xl overflow-hidden">
                    <h3 class="text-white font-bold uppercase tracking-widest text-xs mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-neon-gold">list_alt</span> Senarai Calon
                    </h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-400">
                            <thead class="bg-white/5 text-neon-gold uppercase font-mono tracking-wider">
                                <tr>
                                    <th class="p-3">Nama</th>
                                    <th class="p-3">Jawatan</th>
                                    <th class="p-3 text-right">Tindakan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/5">
                                <?php foreach($candidates as $c): ?>
                                <tr class="group hover:bg-white/5 transition-colors">
                                    <td class="p-3">
                                        <div class="text-white font-bold"><?= htmlspecialchars($c['nama_murid']) ?></div>
                                        <div class="font-mono text-[10px] text-gray-600"><?= htmlspecialchars($c['no_murid']) ?></div>
                                    </td>
                                    <td class="p-3 text-neon-gold"><?= htmlspecialchars($c['nama_jawatan']) ?></td>
                                    <td class="p-3 text-right">
                                        <form method="POST" onsubmit="return confirm('Padam calon ini?')">
                                            <input type="hidden" name="delete_candidate" value="1">
                                            <input type="hidden" name="id_calon" value="<?= $c['id_calon'] ?>">
                                            <button class="text-red-500 hover:text-red-400 opacity-60 group-hover:opacity-100"><span class="material-symbols-outlined text-lg">delete</span></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<script>
    <?php if ($error): ?>
    Swal.fire({
        icon: 'error',
        title: 'Ralat',
        text: '<?= addslashes($error) ?>',
        background: '#121212',
        color: '#ffffff',
        confirmButtonColor: '#ffcc00'
    });
    <?php endif; ?>

    <?php if ($message): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berjaya',
        text: '<?= addslashes($message) ?>',
        background: '#121212',
        color: '#ffffff',
        confirmButtonColor: '#00f3ff'
    });
    <?php endif; ?>
</script>
</body>
</html>
