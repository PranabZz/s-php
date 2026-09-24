@layout('main', ['title' => 'Dashboard - Sphp'])

<?php
$userName = $user['name'] ?? $_SESSION['user']['name'] ?? $_SESSION['name'] ?? 'User';
$userEmail = $user['email'] ?? $_SESSION['user']['email'] ?? $_SESSION['email'] ?? '';
$initial = strtoupper(substr($userName, 0, 1));
?>

<script>
    if (typeof tailwind !== 'undefined') {
        tailwind.config = {
            darkMode: 'class',
        };
    }
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
</script>

<body class="bg-neutral-50 dark:bg-black text-black dark:text-white min-h-screen flex flex-col font-sans selection:bg-blue-600 selection:text-white">

    <!-- Top Navbar -->
    <header class="w-full h-16 border-b border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 px-6 flex justify-between items-center z-10 sticky top-0">
        <!-- Navbar Brand / Name -->
        <div class="flex items-center space-x-3">
            <a href="/" class="flex items-center space-x-2 text-neutral-600 dark:text-neutral-400 hover:text-black dark:hover:text-white transition-colors">
                <img src="<?= function_exists('asset') ? asset('img/logo.png') : '/public/img/logo.png' ?>" class="w-7 h-7 object-contain" alt="Sphp Logo">
                <span class="font-bold text-sm tracking-tight text-black dark:text-white">Sphp</span>
            </a>
            <span class="text-neutral-300 dark:text-neutral-700">/</span>
            <span class="text-sm font-medium text-neutral-600 dark:text-neutral-300">Dashboard</span>
        </div>

        <!-- Right Side: Docs link & Theme Toggle -->
        <div class="flex items-center space-x-3">
            <a href="https://docs-delta-amber.vercel.app/" target="_blank" class="text-xs text-neutral-500 hover:text-black dark:hover:text-white transition-colors">
                Docs
            </a>

            <!-- Theme Toggle Button -->
            <button id="theme-toggle" type="button" aria-label="Toggle theme"
                class="p-2 rounded-md border border-neutral-200 dark:border-neutral-800 hover:border-neutral-400 dark:hover:border-neutral-600 text-neutral-600 dark:text-neutral-400 transition-colors">
                <!-- Sun Icon -->
                <svg id="theme-icon-sun" class="w-4 h-4 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <!-- Moon Icon -->
                <svg id="theme-icon-moon" class="w-4 h-4 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Main Container: Sidebar + Content -->
    <div class="flex flex-1 min-h-[calc(100vh-4rem)]">
        
        <!-- Sidebar -->
        <aside class="w-64 border-r border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-950 flex flex-col justify-between p-4">
            
            <!-- Navigation Links -->
            <nav class="space-y-1">
                <a href="/dashboard" class="flex items-center space-x-3 px-3 py-2 text-sm font-medium rounded-lg bg-neutral-100 dark:bg-neutral-900 text-black dark:text-white transition-colors">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
            </nav>

            <!-- Bottom: User Name & Logout Button -->
            <div class="border-t border-neutral-200 dark:border-neutral-800 pt-4 space-y-2">
                
                <!-- User Info -->
                <div class="flex items-center space-x-3 px-2 py-1">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                        <?= $initial ?>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-black dark:text-white truncate">
                            <?= htmlspecialchars($userName) ?>
                        </p>
                        <?php if (!empty($userEmail)): ?>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">
                                <?= htmlspecialchars($userEmail) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Logout Button -->
                <form action="/logout" method="POST">
                    <button type="submit" class="w-full flex items-center space-x-2.5 px-3 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span>Log out</span>
                    </button>
                </form>

            </div>

        </aside>

        <!-- Main Content View -->
        <main class="flex-1 p-8">
            <div class="max-w-4xl mx-auto space-y-6">
                
                <div class="flex justify-between items-center pb-6 border-b border-neutral-200 dark:border-neutral-800">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-black dark:text-white">
                            Dashboard
                        </h1>
                        <p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
                            Welcome back, <?= htmlspecialchars($userName) ?>!
                        </p>
                    </div>
                </div>

                <!-- Content Card -->
                <div class="bg-white dark:bg-neutral-950 border border-neutral-200 dark:border-neutral-800 rounded-2xl p-8 shadow-sm">
                    <h2 class="text-base font-semibold text-black dark:text-white mb-2">
                        Overview
                    </h2>
                    <p class="text-sm text-neutral-600 dark:text-neutral-400">
                        You have successfully authenticated into Sphp. You can start building your dashboard features here.
                    </p>
                </div>

            </div>
        </main>

    </div>

    <!-- Theme Toggle Script -->
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        themeToggleBtn.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>
</html>
