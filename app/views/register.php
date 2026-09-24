@layout('main', ['title' => 'Sign Up - Sphp'])

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

    <!-- Top Navigation -->
    <nav class="w-full max-w-5xl px-6 py-6 flex justify-between items-center">
        <a href="/" class="flex items-center space-x-2 text-neutral-600 dark:text-neutral-400 hover:text-black dark:hover:text-white transition-colors">
            <img src="<?= function_exists('asset') ? asset('img/logo.png') : '/public/img/logo.png' ?>" class="w-7 h-7 object-contain" alt="Sphp Logo">
            <span class="font-bold text-sm tracking-tight text-black dark:text-white">Sphp</span>
        </a>

        <div class="flex items-center space-x-4">
            <a href="/" class="text-sm text-neutral-500 hover:text-black dark:hover:text-white transition-colors">
                Home
            </a>
            <a href="https://docs-delta-amber.vercel.app/" target="_blank" class="text-sm text-neutral-500 hover:text-black dark:hover:text-white transition-colors">
                Docs
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

    <!-- Main Content Area -->
    <main class="flex flex-col items-center justify-center flex-1 px-4 w-full max-w-md py-8">
        
        <div class="w-full bg-white dark:bg-black border border-neutral-200 dark:border-neutral-800 rounded-2xl p-8 shadow-sm">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold tracking-tight mb-2 text-black dark:text-white">
                    Create an account
                </h1>
                <p class="text-sm text-neutral-600 dark:text-neutral-400">
                    Get started with your free Sphp account.
                </p>
            </div>

            <!-- Session Message -->
            <?php if (!empty($_SESSION['message'])): 
                $flash = $_SESSION['message'];
                $type = 'info';
                $messages = [];

                if (is_array($flash)) {
                    if (isset($flash['error'])) {
                        $type = 'error';
                        $messages = is_array($flash['error']) ? $flash['error'] : [$flash['error']];
                    } elseif (isset($flash['success'])) {
                        $type = 'success';
                        $messages = is_array($flash['success']) ? $flash['success'] : [$flash['success']];
                    } else {
                        $messages = $flash;
                    }
                } else {
                    $messages = [(string) $flash];
                }
            ?>
                <?php if ($type === 'error'): ?>
                    <div class="mb-6 p-3.5 text-sm rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/40 text-red-800 dark:text-red-300 flex items-start space-x-2">
                        <svg class="w-5 h-5 flex-shrink-0 text-red-600 dark:text-red-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="space-y-1">
                            <?php foreach ($messages as $msg): ?>
                                <p><?= htmlspecialchars($msg) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php elseif ($type === 'success'): ?>
                    <div class="mb-6 p-3.5 text-sm rounded-lg border border-green-200 dark:border-green-900/50 bg-green-50 dark:bg-green-950/40 text-green-800 dark:text-green-300 flex items-start space-x-2">
                        <svg class="w-5 h-5 flex-shrink-0 text-green-600 dark:text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div class="space-y-1">
                            <?php foreach ($messages as $msg): ?>
                                <p><?= htmlspecialchars($msg) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="mb-6 p-3.5 text-sm rounded-lg border border-blue-200 dark:border-blue-900/50 bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 flex items-start space-x-2">
                        <svg class="w-5 h-5 flex-shrink-0 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="space-y-1">
                            <?php foreach ($messages as $msg): ?>
                                <p><?= htmlspecialchars($msg) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <!-- Form -->
            <form action="/register" method="POST" class="space-y-4">
                
                <!-- Full Name -->
                <div>
                    <label for="name" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Full name
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        placeholder="John Doe"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 text-black dark:text-white placeholder-neutral-400 focus:outline-none focus:border-blue-600 dark:focus:border-blue-600 focus:bg-white dark:focus:bg-black transition-colors"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Email address
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        placeholder="you@example.com"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 text-black dark:text-white placeholder-neutral-400 focus:outline-none focus:border-blue-600 dark:focus:border-blue-600 focus:bg-white dark:focus:bg-black transition-colors"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        Password
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 text-black dark:text-white placeholder-neutral-400 focus:outline-none focus:border-blue-600 dark:focus:border-blue-600 focus:bg-white dark:focus:bg-black transition-colors"
                    >
                </div>

                <!-- Terms -->
                <div class="flex items-center space-x-2 pt-1">
                    <input
                        type="checkbox"
                        id="terms"
                        name="terms"
                        required
                        class="rounded border-neutral-300 dark:border-neutral-700 text-blue-600 focus:ring-0 focus:ring-offset-0 bg-neutral-50 dark:bg-neutral-900"
                    >
                    <label for="terms" class="text-xs text-neutral-600 dark:text-neutral-400 cursor-pointer">
                        I agree to the <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">Terms of Service</a> and <a href="#" class="text-blue-600 dark:text-blue-400 hover:underline">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full mt-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white font-medium text-sm rounded-lg transition-colors flex justify-center items-center cursor-pointer shadow-sm"
                >
                    Create Account
                </button>
            </form>

            <!-- Bottom Link -->
            <p class="mt-6 text-center text-xs text-neutral-500 dark:text-neutral-400">
                Already have an account?
                <a href="/login" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">
                    Sign in
                </a>
            </p>

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
