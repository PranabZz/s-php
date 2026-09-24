@layout('main', ['title' => 'Welcome to Sphp!'])

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

<body class="bg-white dark:bg-black text-black dark:text-white min-h-screen flex flex-col justify-between items-center transition-colors duration-150 font-sans selection:bg-blue-600 selection:text-white">

    <!-- Top Navigation / Theme Toggle -->
    <nav class="w-full max-w-5xl px-6 py-6 flex justify-between items-center">
        <div class="flex items-center space-x-2">
            <img src="<?= asset('img/logo.png') ?>" class="w-7 h-7 object-contain" alt="Sphp Logo">
            <span class="font-bold text-sm tracking-tight">Sphp</span>
        </div>

        <div class="flex items-center space-x-4">
            <a href="https://docs-delta-amber.vercel.app/" target="_blank" class="text-sm text-neutral-500 hover:text-black dark:hover:text-white transition-colors">
                Docs
            </a>
            <a href="https://github.com/pranabZz/S-PHP" target="_blank" class="text-sm text-neutral-500 hover:text-black dark:hover:text-white transition-colors">
                GitHub
            </a>
            <a href="/login" class="text-sm text-neutral-500 hover:text-black dark:hover:text-white transition-colors">
                Sign In
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
    </nav>

    <!-- Main Content Area (Classic Next.js Style) -->
    <main class="flex flex-col items-center justify-center flex-1 px-6 text-center max-w-4xl w-full py-12">
        
        <!-- Big Iconic Title -->
        <h1 class="text-5xl sm:text-7xl font-bold tracking-tight mb-6">
            Welcome to <a href="https://github.com/pranabZz/S-PHP" target="_blank" class="text-blue-600 hover:underline">Sphp!</a>
        </h1>

        <!-- Get Started Prompt -->
        <p class="text-lg sm:text-xl text-neutral-600 dark:text-neutral-400 mb-12">
            Get started by editing <code class="font-mono text-sm sm:text-base font-semibold bg-neutral-100 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 px-2 py-1 rounded text-black dark:text-white">app/views/welcome.php</code>
        </p>

        <!-- 4 Grid Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full max-w-3xl text-left">
            
            <!-- Card 1: Documentation -->
            <a href="https://docs-delta-amber.vercel.app/" target="_blank"
                class="group p-6 rounded-xl border border-neutral-200 dark:border-neutral-800 hover:border-blue-600 dark:hover:border-blue-600 transition-colors bg-white dark:bg-black">
                <h2 class="text-xl font-bold mb-2 group-hover:text-blue-600 transition-colors flex items-center justify-between">
                    <span>Documentation</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Find in-depth information about Sphp features, architecture, and API routing.
                </p>
            </a>

            <!-- Card 2: CLI -->
            <div class="group p-6 rounded-xl border border-neutral-200 dark:border-neutral-800 hover:border-blue-600 dark:hover:border-blue-600 transition-colors bg-white dark:bg-black cursor-pointer" onclick="copyCli()">
                <h2 class="text-xl font-bold mb-2 group-hover:text-blue-600 transition-colors flex items-center justify-between">
                    <span id="cli-title">Artisan CLI</span>
                    <span id="cli-arrow" class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Run <code class="font-mono text-xs bg-neutral-100 dark:bg-neutral-900 px-1 py-0.5 rounded text-neutral-800 dark:text-neutral-200">php do up</code> to start your server or <code class="font-mono text-xs bg-neutral-100 dark:bg-neutral-900 px-1 py-0.5 rounded text-neutral-800 dark:text-neutral-200">php do migrate</code> for migrations.
                </p>
            </div>

            <!-- Card 3: Multi-Database -->
            <div class="group p-6 rounded-xl border border-neutral-200 dark:border-neutral-800 hover:border-blue-600 dark:hover:border-blue-600 transition-colors bg-white dark:bg-black">
                <h2 class="text-xl font-bold mb-2 group-hover:text-blue-600 transition-colors flex items-center justify-between">
                    <span>Multi-Database</span>
                    <span class="text-xs font-mono px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-900">
                        <?= strtoupper(env('DB_CONNECTION', 'sqlite')) ?>
                    </span>
                </h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Zero-config SQLite ready out of the box, with seamless MySQL and PostgreSQL switching.
                </p>
            </div>

            <!-- Card 4: GitHub -->
            <a href="https://github.com/pranabZz/S-PHP" target="_blank"
                class="group p-6 rounded-xl border border-neutral-200 dark:border-neutral-800 hover:border-blue-600 dark:hover:border-blue-600 transition-colors bg-white dark:bg-black">
                <h2 class="text-xl font-bold mb-2 group-hover:text-blue-600 transition-colors flex items-center justify-between">
                    <span>GitHub</span>
                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                </h2>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Explore the source code, open discussions, and contribute to the Sphp community.
                </p>
            </a>

        </div>

    </main>

    <!-- Footer: Powered by Vertexnp -->
    <footer class="w-full h-24 border-t border-neutral-200 dark:border-neutral-800 flex justify-center items-center text-sm">
        <a href="https://vertexnp.com" target="_blank" rel="noopener noreferrer"
            class="flex items-center gap-1.5 text-neutral-500 hover:text-black dark:hover:text-white transition-colors">
            <span>Powered by</span>
            <span class="font-bold tracking-tight text-black dark:text-white">vertexnp</span>
        </a>
    </footer>

    <!-- Scripts -->
    <script>
        // Theme toggle
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

        // Click to copy CLI command helper
        function copyCli() {
            navigator.clipboard.writeText('php do up').then(() => {
                const title = document.getElementById('cli-title');
                const arrow = document.getElementById('cli-arrow');
                const original = title.textContent;
                title.textContent = 'Copied "php do up"!';
                title.classList.add('text-blue-600');
                setTimeout(() => {
                    title.textContent = original;
                    title.classList.remove('text-blue-600');
                }, 1500);
            });
        }
    </script>
</body>
</html>