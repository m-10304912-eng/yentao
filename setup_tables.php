<?php
require_once 'db_connect.php';

echo "<h1>Permulaan Setup Pangkalan Data...</h1>";

try {
    // 1. JAWATAN (Positions)
    $sql1 = "CREATE TABLE IF NOT EXISTS JAWATAN (
        `id_jawatan` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `nama_jawatan` varchar(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql1);
    echo "✅ Jadual 'JAWATAN' diperiksa/dicipta.<br>";

    // 2. CALON (Candidates) - Foreign Key to JAWATAN
    $sql2 = "CREATE TABLE IF NOT EXISTS CALON (
        `id_calon` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `nama_calon` varchar(100) NOT NULL,
        `emel_calon` varchar(100) NOT NULL,
        `id_jawatan` int(11) NOT NULL,
        `gambar_url` varchar(255) DEFAULT '',
        FOREIGN KEY (`id_jawatan`) REFERENCES `JAWATAN`(`id_jawatan`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql2);
    echo "✅ Jadual 'CALON' diperiksa/dicipta.<br>";

    // 3. PENGGUNA (Voters/Admins)
    $sql3 = "CREATE TABLE IF NOT EXISTS PENGGUNA (
        `no_murid` varchar(20) NOT NULL PRIMARY KEY,
        `nama_murid` varchar(100) NOT NULL,
        `kelas_murid` varchar(50) NOT NULL,
        `password` varchar(255) NOT NULL,
        `role` enum('admin','murid') NOT NULL DEFAULT 'murid'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql3);
    echo "✅ Jadual 'PENGGUNA' diperiksa/dicipta.<br>";

    // 4. UNDIAN (Votes)
    $sql4 = "CREATE TABLE IF NOT EXISTS UNDIAN (
        `id_undian` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        `no_murid` varchar(20) NOT NULL,
        `id_calon` int(11) NOT NULL,
        `id_jawatan` int(11) NOT NULL,
        `tarikh_undi` timestamp NOT NULL DEFAULT current_timestamp(),
        UNIQUE KEY `had_undi` (`no_murid`, `id_jawatan`),
        FOREIGN KEY (`no_murid`) REFERENCES `PENGGUNA`(`no_murid`) ON DELETE CASCADE,
        FOREIGN KEY (`id_calon`) REFERENCES `CALON`(`id_calon`) ON DELETE CASCADE,
        FOREIGN KEY (`id_jawatan`) REFERENCES `JAWATAN`(`id_jawatan`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $pdo->exec($sql4);
    echo "✅ Jadual 'UNDIAN' diperiksa/dicipta.<br>";

    // 5. SEED DATA (Admin & Positions)
    // Check if Admin exists
    $stmt = $pdo->prepare("SELECT * FROM PENGGUNA WHERE no_murid = 'admin'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        // Insert Admin (Plain Text '12345')
        $pdo->exec("INSERT INTO PENGGUNA (no_murid, nama_murid, kelas_murid, password, role) VALUES ('admin', 'System Admin', 'Staff', '12345', 'admin')");
        echo "🔹 Akaun Admin (password: 12345) dicipta.<br>";
    }

    // Check if Positions exist
    $stmt = $pdo->query("SELECT count(*) FROM JAWATAN");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO JAWATAN (nama_jawatan) VALUES ('Pengerusi'), ('Naib Pengerusi'), ('Setiausaha'), ('Bendahari')");
        echo "🔹 Jawatan lalai ditambah.<br>";
    }

    echo "<hr><h3>🎉 Selesai! Pangkalan data telah dibaiki.</h3>";
    echo "<h1 class='text-2xl font-bold text-[#d4af37] mb-4'>Persediaan Jadual Pangkalan Data</h1>";
    echo "<span class='text-xs font-bold text-slate-500 uppercase mb-4 block'>Sistem Pengundian Jawatankuasa Lembaga Pustakawan</span>";
    echo "<a href='admin_manage.php' class='text-neon-blue underline'>&rarr; Kembali ke Urusan Pentadbir</a>";

} catch (PDOException $e) {
    echo "<h1>❌ Ralat Kritikal</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
