<nav class="border-b border-white/10 bg-background-night/80 backdrop-blur-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 border border-neon-blue/50 rounded flex items-center justify-center shadow-[0_0_10px_rgba(0,243,255,0.2)]">
                <span class="material-symbols-outlined text-neon-blue text-xl">menu_book</span>
            </div>
            <a href="index.php" class="text-xl font-black tracking-tighter text-white uppercase italic hover:text-neon-blue transition-colors">
                Siber<span class="text-neon-blue">Pustaka</span>
            </a>
        </div>

        <!-- Links (Center) -->
        <div class="hidden md:flex gap-8">
            <a href="vote.php" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-white transition-colors">Undi</a>
            <a href="results.php" class="text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-white transition-colors">Keputusan</a>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_manage.php" class="text-xs font-bold uppercase tracking-widest text-neon-gold hover:text-white transition-colors">Admin</a>
            <?php endif; ?>
        </div>

        <!-- User Profile (Right) -->
        <div class="flex items-center gap-4">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="text-right hidden sm:block">
                    <p class="text-white text-xs font-bold uppercase tracking-wide"><?= htmlspecialchars($_SESSION['nama_murid']) ?></p>
                    <p class="text-[10px] text-gray-500 font-mono tracking-widest"><?= htmlspecialchars($_SESSION['user_id']) ?></p>
                </div>
                <a href="login.php" class="flex items-center justify-center w-8 h-8 rounded bg-white/5 hover:bg-red-900/50 text-gray-400 hover:text-red-400 transition-colors border border-white/10">
                    <span class="material-symbols-outlined text-sm">logout</span>
                </a>
            <?php else: ?>
                <a href="login.php" class="px-4 py-2 border border-neon-blue text-neon-blue text-[10px] font-black uppercase tracking-widest hover:bg-neon-blue hover:text-black transition-all">
                    Log Masuk
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
