<?php
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['no_murid']);
    $password = $_POST['password'];

    // Validation: ID format (Optional check here, strict on register)
    // Validation: Uppercase password check (Optional here, strict on register)

    $stmt = $conn->prepare("SELECT no_murid, nama_murid, password, role FROM PENGGUNA WHERE no_murid = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['no_murid'];
            $_SESSION['nama_murid'] = $user['nama_murid'];
            $_SESSION['role'] = $user['role'];
            $success = "Log masuk berjaya!";
            // Redirect handled by JS after popup
        } else {
            $error = "Kata laluan salah.";
        }
    } else {
        $error = "Pengguna tidak ditemui.";
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="ms">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Log Masuk - Pusat Sumber Siber</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;900&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "cyber-blue": "#00f2ff",
                        "cyber-gold": "#ffcc00",
                        "background-dark": "#05070a",
                        "charcoal": "#121212",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                },
            },
        }
    </script>
    <style type="text/tailwindcss">
        @keyframes gold-pulse {
            0% { box-shadow: 0 0 0 0 rgba(255, 204, 0, 0.4); }
            70% { box-shadow: 0 0 0 15px rgba(255, 204, 0, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 204, 0, 0); }
        }
        .neon-pulse { animation: gold-pulse 2s infinite; }
        .glass-card {
            background: rgba(18, 18, 18, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 242, 255, 0.3);
            box-shadow: 0 0 20px rgba(0, 242, 255, 0.1);
        }
        .library-overlay {
            background: linear-gradient(rgba(5, 7, 10, 0.9), rgba(5, 7, 10, 0.95)), url('https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
        }
        .neon-glow-text { text-shadow: 0 0 10px rgba(0, 242, 255, 0.8); }
        .neon-glow-gold { text-shadow: 0 0 10px rgba(255, 204, 0, 0.8); }
    </style>
</head>
<body class="bg-background-dark font-display text-white overflow-x-hidden">
<div class="relative flex min-h-screen w-full flex-col library-overlay">
    <header class="flex items-center justify-between px-8 py-6">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 border-2 border-cyber-gold rounded-lg flex items-center justify-center shadow-[0_0_15px_rgba(255,204,0,0.3)]">
                <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght@400;FILL@0..1&display=swap" rel="stylesheet" />
                <span class="material-symbols-outlined text-neon-blue text-2xl font-bold">menu_book</span>
            </div>
            <span class="text-2xl font-black tracking-tighter text-white uppercase italic">Siber<span class="text-cyber-blue neon-glow-text">Pustaka</span></span>
        </div>
        <div class="text-xs text-cyber-blue/60 font-mono tracking-widest uppercase bg-cyber-blue/5 px-3 py-1 border border-cyber-blue/20 rounded">
            Protokol v4.0.1 // Nod_LCS
        </div>
    </header>

    <main class="flex flex-1 items-center justify-center p-4">
        <div class="glass-card w-full max-w-[460px] rounded-2xl p-10 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-cyber-blue to-transparent"></div>
            
            <div class="flex flex-col gap-3 mb-10 text-center">
                <h1 class="text-3xl font-black leading-tight tracking-widest text-white uppercase">Pengesahan</h1>
                <p class="text-cyber-blue/60 text-xs font-mono uppercase tracking-widest">Akses Jawatankuasa Pustakawan</p>
            </div>

            <form class="flex flex-col gap-8" method="POST">
                <div class="flex flex-col gap-3">
                    <label class="text-cyber-blue text-[10px] font-bold uppercase tracking-[0.3em] ml-1">ID Pengguna</label>
                    <div class="flex w-full items-stretch rounded-lg group">
                        <div class="text-cyber-blue flex border border-r-0 border-cyber-blue/30 bg-cyber-blue/5 items-center justify-center pl-4 rounded-l-lg transition-all group-focus-within:border-cyber-blue group-focus-within:shadow-[0_0_10px_rgba(0,242,255,0.2)]">
                            <span class="material-symbols-outlined neon-glow-text">account_circle</span>
                        </div>
                        <input name="no_murid" class="form-input flex w-full min-w-0 flex-1 border border-cyber-blue/30 bg-cyber-blue/5 h-14 text-white placeholder:text-gray-600 p-4 focus:outline-0 focus:ring-0 focus:border-cyber-blue rounded-r-lg border-l-0 text-base font-normal transition-all" placeholder="Masukkan ID (Cth: D6557)" type="text" required />
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="flex justify-between items-center ml-1">
                        <label class="text-cyber-blue text-[10px] font-bold uppercase tracking-[0.3em]">Kunci Neural</label>
                        <a class="text-[10px] text-cyber-gold/80 hover:text-cyber-gold uppercase tracking-tighter transition-colors" href="#">Lupa Kunci?</a>
                    </div>
                    <div class="flex w-full items-stretch rounded-lg group">
                        <div class="text-cyber-blue flex border border-r-0 border-cyber-blue/30 bg-cyber-blue/5 items-center justify-center pl-4 rounded-l-lg transition-all group-focus-within:border-cyber-blue group-focus-within:shadow-[0_0_10px_rgba(0,242,255,0.2)]">
                            <span class="material-symbols-outlined neon-glow-text">lock</span>
                        </div>
                        <input name="password" class="form-input flex w-full min-w-0 flex-1 border border-cyber-blue/30 bg-cyber-blue/5 h-14 text-white placeholder:text-gray-600 p-4 focus:outline-0 focus:ring-0 focus:border-cyber-blue rounded-r-lg border-l-0 text-base font-normal transition-all" placeholder="••••••••" type="password" required />
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="neon-pulse w-full flex cursor-pointer items-center justify-center rounded-lg h-16 px-6 bg-cyber-gold text-background-dark text-sm font-black leading-normal tracking-[0.25em] uppercase transition-all duration-300 hover:brightness-110 active:scale-95">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-xl">fingerprint</span>
                            Mulakan Urutan
                        </span>
                    </button>
                    <div class="mt-4 text-center">
                        <a href="register.php" class="text-xs text-cyber-blue hover:text-white transition-colors tracking-widest uppercase">Daftar Akaun Baru</a>
                    </div>
                </div>
            </form>

            <div class="mt-10 pt-8 border-t border-cyber-blue/10 flex flex-col items-center gap-4">
                <p class="text-[10px] text-cyber-blue/40 flex items-center gap-2 font-mono">
                    <span class="material-symbols-outlined text-xs">verified</span>
                    TAHAP_ENKRIPSI: MAX_BIT
                </p>
            </div>
            <div class="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-cyber-gold/50 to-transparent"></div>
        </div>
    </main>
    <footer class="p-8 text-center text-[10px] text-cyber-blue/30 font-mono uppercase tracking-[0.4em]">
        © 2024 KORPORAT_PUSTAKAWAN_SIBER // SISTEM_DATA_TERKAWAL
    </footer>
</div>

<script>
    <?php if ($error): ?>
    Swal.fire({
        icon: 'error',
        title: 'Akses Ditolak',
        text: '<?= addslashes($error) ?>',
        background: '#121212',
        color: '#ffffff',
        confirmButtonColor: '#ffcc00'
    });
    <?php endif; ?>

    <?php if ($success): ?>
    Swal.fire({
        icon: 'success',
        title: 'Akses Dibenarkan',
        text: '<?= addslashes($success) ?>',
        background: '#121212',
        color: '#ffffff',
        confirmButtonColor: '#00f2ff',
        timer: 1500,
        showConfirmButton: false
    }).then(() => {
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        window.location.href = 'index.php'; // Or admin_manage.php
        <?php else: ?>
        window.location.href = 'vote.php';
        <?php endif; ?>
    });
    <?php endif; ?>
</script>
</body>
</html>
