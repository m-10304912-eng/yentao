<?php
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $no_murid = trim($_POST['no_murid']);
    $nama_murid = trim($_POST['nama_murid']);
    $kelas_murid = trim($_POST['kelas_murid']);
    $password = $_POST['password'];

    // 1. Validation: School ID (Letter + 4 Digits)
    if (!preg_match('/^[A-Za-z]\d{4}$/', $no_murid)) {
        $error = "Format No. Murid tidak sah. Mesti bermula dengan huruf dan 4 nombor (Cth: D6557).";
    }
    // 2. Validation: Password (Uppercase required)
    elseif (!preg_match('/[A-Z]/', $password)) {
        $error = "Kata laluan mesti mengandungi sekurang-kurangnya satu huruf besar.";
    }
    // 3. Validation: Empty fields
    elseif (empty($nama_murid) || empty($kelas_murid)) {
        $error = "Sila isi semua maklumat.";
    }
    else {
        // Check duplicate
        $stmt = $conn->prepare("SELECT no_murid FROM PENGGUNA WHERE no_murid = ?");
        $stmt->bind_param("s", $no_murid);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = "No. Murid ini sudah wujud.";
        } else {
            // INSERT
            $role = 'murid';
            $stmt = $conn->prepare("INSERT INTO PENGGUNA (no_murid, nama_murid, kelas_murid, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $no_murid, $nama_murid, $kelas_murid, $password, $role);
            
            if ($stmt->execute()) {
                $success = "Pendaftaran Berjaya! Anda kini boleh log masuk.";
            } else {
                $error = "Ralat Pangkalan Data: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html class="dark" lang="ms">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Daftar - Pusat Sumber Siber</title>
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
            Protokol Pendaftaran v2.0
        </div>
    </header>

    <main class="flex flex-1 items-center justify-center p-4">
        <div class="glass-card w-full max-w-[500px] rounded-2xl p-10 relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-[2px] bg-gradient-to-r from-transparent via-cyber-blue to-transparent"></div>
            
            <div class="flex flex-col gap-3 mb-8 text-center">
                <h1 class="text-3xl font-black leading-tight tracking-widest text-white uppercase">Daftar Masuk</h1>
                <p class="text-cyber-blue/60 text-xs font-mono uppercase tracking-widest">Wujudkan Identiti Digital Anda</p>
            </div>

            <form class="flex flex-col gap-6" method="POST">
                
                <!-- School ID -->
                <div class="flex flex-col gap-2">
                    <label class="text-cyber-blue text-[10px] font-bold uppercase tracking-[0.3em] ml-1">No. Murid (Cth: D6557)</label>
                    <div class="flex w-full items-stretch rounded-lg group">
                        <div class="text-cyber-blue flex border border-r-0 border-cyber-blue/30 bg-cyber-blue/5 items-center justify-center pl-4 rounded-l-lg transition-all group-focus-within:border-cyber-blue">
                            <span class="material-symbols-outlined neon-glow-text">badge</span>
                        </div>
                        <input name="no_murid" class="form-input flex w-full flex-1 border border-cyber-blue/30 bg-cyber-blue/5 h-12 text-white placeholder:text-gray-600 p-4 focus:outline-0 focus:ring-0 focus:border-cyber-blue rounded-r-lg border-l-0 text-sm transition-all" placeholder="Abjad + 4 Nombor" type="text" required />
                    </div>
                </div>

                <!-- Full Name -->
                <div class="flex flex-col gap-2">
                    <label class="text-cyber-blue text-[10px] font-bold uppercase tracking-[0.3em] ml-1">Nama Penuh</label>
                    <div class="flex w-full items-stretch rounded-lg group">
                        <div class="text-cyber-blue flex border border-r-0 border-cyber-blue/30 bg-cyber-blue/5 items-center justify-center pl-4 rounded-l-lg transition-all group-focus-within:border-cyber-blue">
                            <span class="material-symbols-outlined neon-glow-text">person</span>
                        </div>
                        <input name="nama_murid" class="form-input flex w-full flex-1 border border-cyber-blue/30 bg-cyber-blue/5 h-12 text-white placeholder:text-gray-600 p-4 focus:outline-0 focus:ring-0 focus:border-cyber-blue rounded-r-lg border-l-0 text-sm transition-all" placeholder="Nama Anda" type="text" required />
                    </div>
                </div>

                <!-- Class -->
                <div class="flex flex-col gap-2">
                    <label class="text-cyber-blue text-[10px] font-bold uppercase tracking-[0.3em] ml-1">Kelas</label>
                    <div class="flex w-full items-stretch rounded-lg group">
                        <div class="text-cyber-blue flex border border-r-0 border-cyber-blue/30 bg-cyber-blue/5 items-center justify-center pl-4 rounded-l-lg transition-all group-focus-within:border-cyber-blue">
                            <span class="material-symbols-outlined neon-glow-text">class</span>
                        </div>
                        <input name="kelas_murid" class="form-input flex w-full flex-1 border border-cyber-blue/30 bg-cyber-blue/5 h-12 text-white placeholder:text-gray-600 p-4 focus:outline-0 focus:ring-0 focus:border-cyber-blue rounded-r-lg border-l-0 text-sm transition-all" placeholder="Nama Kelas" type="text" required />
                    </div>
                </div>

                <!-- Password -->
                <div class="flex flex-col gap-2">
                    <label class="text-cyber-blue text-[10px] font-bold uppercase tracking-[0.3em] ml-1">Kata Laluan (Wajib Huruf Besar)</label>
                    <div class="flex w-full items-stretch rounded-lg group">
                        <div class="text-cyber-blue flex border border-r-0 border-cyber-blue/30 bg-cyber-blue/5 items-center justify-center pl-4 rounded-l-lg transition-all group-focus-within:border-cyber-blue">
                            <span class="material-symbols-outlined neon-glow-text">lock</span>
                        </div>
                        <input name="password" class="form-input flex w-full flex-1 border border-cyber-blue/30 bg-cyber-blue/5 h-12 text-white placeholder:text-gray-600 p-4 focus:outline-0 focus:ring-0 focus:border-cyber-blue rounded-r-lg border-l-0 text-sm transition-all" placeholder="••••••••" type="password" required />
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="neon-pulse w-full flex cursor-pointer items-center justify-center rounded-lg h-14 px-6 bg-cyber-gold text-background-dark text-sm font-black leading-normal tracking-[0.25em] uppercase transition-all duration-300 hover:brightness-110 active:scale-95">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-lg">fact_check</span>
                            Daftar Sekarang
                        </span>
                    </button>
                    <div class="mt-4 text-center">
                        <a href="login.php" class="text-xs text-cyber-blue hover:text-white transition-colors tracking-widest uppercase">Sudah ada akaun? Log Masuk</a>
                    </div>
                </div>
            </form>
            <div class="absolute bottom-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-cyber-gold/50 to-transparent"></div>
        </div>
    </main>
    <footer class="p-8 text-center text-[10px] text-cyber-blue/30 font-mono uppercase tracking-[0.4em]">
        © 2024 PUSTAKA_DIGITAL // MODUL_PENDAFTARAN
    </footer>
</div>

<script>
    <?php if ($error): ?>
    Swal.fire({
        icon: 'error',
        title: 'Ralat Pendaftaran',
        text: '<?= addslashes($error) ?>',
        background: '#121212',
        color: '#ffffff',
        confirmButtonColor: '#ffcc00'
    });
    <?php endif; ?>

    <?php if ($success): ?>
    Swal.fire({
        icon: 'success',
        title: 'Berjaya!',
        text: '<?= addslashes($success) ?>',
        background: '#121212',
        color: '#ffffff',
        confirmButtonColor: '#00f2ff',
        timer: 2000,
        showConfirmButton: false
    }).then(() => {
        window.location.href = 'login.php';
    });
    <?php endif; ?>
</script>
</body>
</html>
