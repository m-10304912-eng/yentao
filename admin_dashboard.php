<?php
session_start();
require_once 'db_connect.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch Statistics
$total_students = $pdo->query("SELECT COUNT(*) FROM PENGGUNA WHERE role = 'murid'")->fetchColumn();
$total_votes = $pdo->query("SELECT COUNT(*) FROM UNDIAN")->fetchColumn();
$total_positions = $pdo->query("SELECT COUNT(*) FROM JAWATAN")->fetchColumn();
$total_candidates = $pdo->query("SELECT COUNT(*) FROM CALON")->fetchColumn();

// Calculate voter turnout
$voters_count = $pdo->query("SELECT COUNT(DISTINCT no_murid) FROM UNDIAN")->fetchColumn();
$voter_turnout = $total_students > 0 ? round(($voters_count / $total_students) * 100, 1) : 0;

// Fetch candidate statistics with votes
$sql_candidates = "SELECT 
    c.id_calon,
    p.nama_murid,
    j.nama_jawatan,
    COUNT(u.id_undi) as jumlah_undi
FROM CALON c
JOIN PENGGUNA p ON c.no_murid = p.no_murid
JOIN JAWATAN j ON c.id_jawatan = j.id_jawatan
LEFT JOIN UNDIAN u ON c.id_calon = u.id_calon
GROUP BY c.id_calon, p.nama_murid, j.nama_jawatan
ORDER BY j.nama_jawatan, jumlah_undi DESC";
$candidates = $pdo->query($sql_candidates)->fetchAll();

// Fetch vote distribution by position
$sql_position_votes = "SELECT 
    j.nama_jawatan,
    COUNT(u.id_undi) as jumlah_undi
FROM JAWATAN j
LEFT JOIN CALON c ON j.id_jawatan = c.id_jawatan
LEFT JOIN UNDIAN u ON c.id_calon = u.id_calon
GROUP BY j.id_jawatan, j.nama_jawatan
ORDER BY j.nama_jawatan";
$position_votes = $pdo->query($sql_position_votes)->fetchAll();

// Prepare data for charts
$candidate_names = [];
$candidate_votes = [];
$candidate_colors = [];
$position_names = [];
$position_vote_counts = [];

foreach ($candidates as $candidate) {
    $candidate_names[] = $candidate['nama_murid'] . ' (' . $candidate['nama_jawatan'] . ')';
    $candidate_votes[] = $candidate['jumlah_undi'];
    $candidate_colors[] = 'rgba(13, 185, 242, 0.8)'; // Neon blue
}

foreach ($position_votes as $position) {
    $position_names[] = $position['nama_jawatan'];
    $position_vote_counts[] = $position['jumlah_undi'];
}
?>
<!DOCTYPE html>
<html class="dark" lang="ms">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Papan Pemuka Taktik | Jawatankuasa Pustakawan</title>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#0db9f2",
                    "accent": "#ffb400",
                    "background-light": "#f5f8f8",
                    "background-dark": "#05080a",
                },
                fontFamily: {
                    "display": ["Space Grotesk", "sans-serif"]
                },
                borderRadius: {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
            },
        },
    }
</script>
<style>
    .grid-bg {
        background-image: linear-gradient(to right, rgba(13, 185, 242, 0.05) 1px, transparent 1px),
                          linear-gradient(to bottom, rgba(13, 185, 242, 0.05) 1px, transparent 1px);
        background-size: 40px 40px;
    }
    .hex-shape {
        clip-path: polygon(25% 0%, 75% 0%, 100% 50%, 75% 100%, 25% 100%, 0% 50%);
    }
    .glow-cyan {
        box-shadow: 0 0 15px rgba(13, 185, 242, 0.3);
    }
    .glow-amber {
        box-shadow: 0 0 15px rgba(255, 180, 0, 0.3);
    }
    .scanline {
        width: 100%;
        height: 100px;
        z-index: 10;
        background: linear-gradient(0deg, rgba(13, 185, 242, 0) 0%, rgba(13, 185, 242, 0.05) 50%, rgba(13, 185, 242, 0) 100%);
        position: absolute;
        pointer-events: none;
        animation: scan 4s linear infinite;
    }
    @keyframes scan {
        0% { top: -100px; }
        100% { top: 100%; }
    }
</style>
</head>
<body class="bg-background-dark font-display text-white overflow-hidden select-none">
<!-- Tactical HUD Background Layers -->
<div class="fixed inset-0 grid-bg"></div>
<div class="fixed inset-0 bg-radial-gradient from-transparent via-background-dark/80 to-background-dark pointer-events-none"></div>
<div class="scanline top-0"></div>

<!-- Main Container -->
<div class="relative z-20 flex flex-col h-screen w-full p-4 overflow-hidden">
    <!-- Top Tactical Nav Bar -->
    <header class="flex items-center justify-between border-b border-primary/20 bg-background-dark/40 backdrop-blur-md px-8 py-2 mb-4 rounded-lg">
        <div class="flex items-center gap-6">
            <div class="flex items-center gap-3">
                <div class="p-1 border border-primary/50 rounded flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-xl">auto_stories</span>
                </div>
                <div>
                    <h1 class="text-xs uppercase tracking-widest text-primary font-bold">Papan Pemuka</h1>
                    <p class="text-[10px] text-primary/60 font-mono">SISTEM UNDIAN PUSTAKAWAN v2.0</p>
                </div>
            </div>
            <div class="h-8 w-px bg-primary/20"></div>
            <nav class="flex items-center gap-6">
                <a class="text-[11px] uppercase tracking-wider text-primary hover:text-white transition-colors" href="admin_dashboard.php">Papan Pemuka</a>
                <a class="text-[11px] uppercase tracking-wider text-primary/40 hover:text-primary transition-colors" href="admin_manage.php">Pengurusan Data</a>
            </nav>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <p class="text-[10px] text-accent font-mono uppercase">Selamat Datang, Pentadbir</p>
                <p class="text-xs text-white/80 font-mono"><?= date('H:i:s') ?> MYT</p>
            </div>
            <a href="login.php" class="size-10 rounded-full border border-primary/40 bg-primary/10 overflow-hidden flex items-center justify-center hover:border-primary transition-colors">
                <span class="material-symbols-outlined text-primary">logout</span>
            </a>
        </div>
    </header>

    <!-- Main HUD Body -->
    <main class="flex-1 flex items-center justify-center relative overflow-auto">
        <div class="w-full max-w-7xl mx-auto px-4">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Students -->
                <div class="bg-background-dark/60 border border-primary/20 rounded-lg backdrop-blur-md p-6 hover:border-primary/40 transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="material-symbols-outlined text-primary text-3xl">group</span>
                        <span class="text-[10px] text-primary/60 uppercase tracking-widest font-bold">Terdaftar</span>
                    </div>
                    <p class="text-xs text-primary/70 uppercase tracking-widest mb-1">Jumlah Murid</p>
                    <h3 class="text-4xl font-black text-white"><?= $total_students ?></h3>
                </div>

                <!-- Total Votes -->
                <div class="bg-background-dark/60 border border-accent/20 rounded-lg backdrop-blur-md p-6 hover:border-accent/40 transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="material-symbols-outlined text-accent text-3xl">how_to_vote</span>
                        <span class="text-[10px] text-accent/60 uppercase tracking-widest font-bold">Dikumpul</span>
                    </div>
                    <p class="text-xs text-accent/70 uppercase tracking-widest mb-1">Undian Diterima</p>
                    <h3 class="text-4xl font-black text-white"><?= $total_votes ?></h3>
                </div>

                <!-- Active Positions -->
                <div class="bg-background-dark/60 border border-primary/20 rounded-lg backdrop-blur-md p-6 hover:border-primary/40 transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="material-symbols-outlined text-primary text-3xl">badge</span>
                        <span class="text-[10px] text-primary/60 uppercase tracking-widest font-bold">Aktif</span>
                    </div>
                    <p class="text-xs text-primary/70 uppercase tracking-widest mb-1">Jawatan Aktif</p>
                    <h3 class="text-4xl font-black text-white"><?= $total_positions ?></h3>
                </div>

                <!-- Voter Turnout -->
                <div class="bg-background-dark/60 border border-accent/20 rounded-lg backdrop-blur-md p-6 hover:border-accent/40 transition-all">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="material-symbols-outlined text-accent text-3xl">how_to_reg</span>
                        <span class="text-[10px] text-accent/60 uppercase tracking-widest font-bold">Penyertaan</span>
                    </div>
                    <p class="text-xs text-accent/70 uppercase tracking-widest mb-1">Kadar Keluar Mengundi</p>
                    <h3 class="text-4xl font-black text-white"><?= $voter_turnout ?>%</h3>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Vote Distribution by Position -->
                <div class="bg-background-dark/60 border border-primary/20 rounded-lg backdrop-blur-md p-6">
                    <h3 class="text-xs uppercase tracking-widest text-primary font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined">pie_chart</span>
                        Taburan Undian Mengikut Jawatan
                    </h3>
                    <div class="relative h-64">
                        <canvas id="positionChart"></canvas>
                    </div>
                </div>

                <!-- Candidate Vote Statistics -->
                <div class="bg-background-dark/60 border border-accent/20 rounded-lg backdrop-blur-md p-6">
                    <h3 class="text-xs uppercase tracking-widest text-accent font-bold mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined">bar_chart</span>
                        Statistik Undian Calon
                    </h3>
                    <div class="relative h-64">
                        <canvas id="candidateChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Candidate Statistics Table -->
            <div class="bg-background-dark/60 border border-primary/20 rounded-lg backdrop-blur-md p-6">
                <h3 class="text-xs uppercase tracking-widest text-primary font-bold mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined">leaderboard</span>
                    Statistik Calon
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-primary/20">
                            <tr>
                                <th class="pb-3 text-primary/70 font-bold uppercase tracking-wider text-xs">Nama Calon</th>
                                <th class="pb-3 text-primary/70 font-bold uppercase tracking-wider text-xs">Jawatan</th>
                                <th class="pb-3 text-primary/70 font-bold uppercase tracking-wider text-xs text-right">Undian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-primary/10">
                            <?php if (count($candidates) > 0): ?>
                                <?php foreach ($candidates as $candidate): ?>
                                <tr class="hover:bg-primary/5 transition-colors">
                                    <td class="py-3 text-white"><?= htmlspecialchars($candidate['nama_murid']) ?></td>
                                    <td class="py-3 text-accent"><?= htmlspecialchars($candidate['nama_jawatan']) ?></td>
                                    <td class="py-3 text-white text-right font-bold"><?= $candidate['jumlah_undi'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-primary/40 text-sm">Tiada data undian.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Background Decoration -->
<div class="fixed inset-0 pointer-events-none border-[20px] border-background-dark shadow-[inset_0_0_100px_rgba(0,0,0,1)] z-50"></div>

<script>
// Chart.js Configuration
Chart.defaults.color = '#9ca3af';
Chart.defaults.borderColor = 'rgba(13, 185, 242, 0.1)';

// Position Vote Distribution Chart (Doughnut)
const positionCtx = document.getElementById('positionChart').getContext('2d');
const positionChart = new Chart(positionCtx, {
    type: 'doughnut',
    data: {
        labels: <?= json_encode($position_names) ?>,
        datasets: [{
            data: <?= json_encode($position_vote_counts) ?>,
            backgroundColor: [
                'rgba(13, 185, 242, 0.8)',
                'rgba(255, 180, 0, 0.8)',
                'rgba(188, 19, 254, 0.8)',
                'rgba(0, 243, 255, 0.8)',
                'rgba(255, 204, 51, 0.8)',
            ],
            borderColor: [
                'rgba(13, 185, 242, 1)',
                'rgba(255, 180, 0, 1)',
                'rgba(188, 19, 254, 1)',
                'rgba(0, 243, 255, 1)',
                'rgba(255, 204, 51, 1)',
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    color: '#e5e7eb',
                    padding: 15,
                    font: {
                        family: 'Space Grotesk',
                        size: 11
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(5, 8, 10, 0.9)',
                titleColor: '#0db9f2',
                bodyColor: '#e5e7eb',
                borderColor: '#0db9f2',
                borderWidth: 1,
                padding: 12,
                displayColors: true,
                callbacks: {
                    label: function(context) {
                        return context.label + ': ' + context.parsed + ' undian';
                    }
                }
            }
        }
    }
});

// Candidate Vote Statistics Chart (Bar)
const candidateCtx = document.getElementById('candidateChart').getContext('2d');
const candidateChart = new Chart(candidateCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($candidate_names) ?>,
        datasets: [{
            label: 'Jumlah Undian',
            data: <?= json_encode($candidate_votes) ?>,
            backgroundColor: 'rgba(13, 185, 242, 0.6)',
            borderColor: 'rgba(13, 185, 242, 1)',
            borderWidth: 2,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    color: '#9ca3af',
                    font: {
                        family: 'Space Grotesk'
                    }
                },
                grid: {
                    color: 'rgba(13, 185, 242, 0.1)'
                }
            },
            x: {
                ticks: {
                    color: '#9ca3af',
                    font: {
                        family: 'Space Grotesk',
                        size: 10
                    },
                    maxRotation: 45,
                    minRotation: 45
                },
                grid: {
                    display: false
                }
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(5, 8, 10, 0.9)',
                titleColor: '#0db9f2',
                bodyColor: '#e5e7eb',
                borderColor: '#0db9f2',
                borderWidth: 1,
                padding: 12,
                callbacks: {
                    label: function(context) {
                        return 'Undian: ' + context.parsed.y;
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>
