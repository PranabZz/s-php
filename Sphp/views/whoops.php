<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(get_class($e)) ?>: <?= htmlspecialchars($e->getMessage()) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #0d0d0f;
            --bg-card: #141417;
            --bg-card-subtle: #1c1c21;
            --border-color: #27272a;
            --border-subtle: #1f1f23;
            --text-title: #ffffff;
            --text-body: #e4e4e7;
            --text-muted: #a1a1aa;
            --text-dim: #71717a;
            --brand-red: #e11d48;
            --brand-red-dark: #be123c;
            --line-highlight: #881337;
            --font-sans: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            --font-mono: 'Poppins', sans-serif;
            --font-code: 'JetBrains Mono', Consolas, Menlo, Monaco, monospace;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: var(--font-sans);
        }

        .code-viewer, .code-viewer * {
            font-family: var(--font-code) !important;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-body);
            font-family: var(--font-sans);
            font-size: 13.5px;
            line-height: 1.5;
            min-height: 100vh;
            padding-bottom: 80px;
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
            padding: 44px 24px 0;
        }

        /* Header section */
        .error-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 24px;
        }

        .error-main {
            flex: 1;
        }

        .error-type {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-title);
            line-height: 1.2;
            margin-bottom: 8px;
        }

        .error-message {
            font-size: 16px;
            color: var(--text-muted);
            line-height: 1.5;
            margin-bottom: 18px;
            word-break: break-word;
        }

        .badges-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .pill {
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .pill-dark {
            background: #1c1c21;
            color: #d4d4d8;
            border: 1px solid var(--border-color);
        }

        .pill-red {
            background: var(--brand-red);
            color: #ffffff;
        }

        .btn-copy-md {
            background: #18181b;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            font-family: var(--font-sans);
            font-size: 12px;
            font-weight: 600;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .btn-copy-md:hover {
            color: #fff;
            border-color: #3f3f46;
            background: #27272a;
        }

        /* Suggestion card (Minimal, clean, like Laravel) */
        .suggestion-box {
            background: #141417;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 14px 18px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .suggestion-badge {
            background: #27272a;
            color: #f4f4f5;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
        }
        .suggestion-text {
            color: #e4e4e7;
            font-size: 13px;
            word-break: break-word;
        }

        /* Request URL Bar */
        .request-bar {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 32px;
        }

        .request-left {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .method-badge {
            background: var(--brand-red);
            color: #ffffff;
            font-family: var(--font-mono);
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        .request-url {
            font-family: var(--font-mono);
            font-size: 13px;
            color: #e4e4e7;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .icon-btn {
            background: none;
            border: none;
            color: var(--text-dim);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.15s;
        }
        .icon-btn:hover {
            color: #ffffff;
        }
        .icon-btn svg {
            width: 14px;
            height: 14px;
            fill: currentColor;
        }

        /* Section Headings */
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #f4f4f5;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .trace-icon {
            color: #10b981;
            font-size: 11px;
        }

        /* Overview Dotted Table */
        .overview-section {
            margin-bottom: 32px;
        }

        .overview-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .overview-item {
            display: flex;
            align-items: center;
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .overview-label {
            flex-shrink: 0;
        }

        .dotted-line {
            flex: 1;
            border-bottom: 1px dotted #3f3f46;
            margin: 0 14px;
            height: 1px;
        }

        .overview-value {
            flex-shrink: 0;
            color: #e4e4e7;
            text-transform: none;
        }

        .val-badge-red {
            background: var(--brand-red);
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .val-badge-dark {
            background: #27272a;
            color: #f4f4f5;
            font-weight: 700;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
        }

        /* Exception Trace Cards */
        .trace-section {
            margin-bottom: 40px;
        }

        .trace-box {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 12px;
        }

        .trace-box-header {
            padding: 12px 18px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: var(--font-mono);
            font-size: 12px;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
            transition: background 0.15s;
        }
        .trace-box-header:hover {
            background: #18181c;
        }

        .trace-file-left {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #e4e4e7;
        }
        .trace-dot {
            width: 6px;
            height: 6px;
            background: var(--text-dim);
            border-radius: 50%;
        }

        .trace-line-right {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-muted);
        }
        .code-toggle-icon {
            color: var(--text-dim);
            font-size: 13px;
        }

        /* Code Viewer */
        .code-viewer {
            background: #09090b;
            overflow-x: auto;
            font-family: var(--font-mono);
            font-size: 13px;
            line-height: 1.75;
            padding: 10px 0;
        }

        .code-row {
            display: flex;
            width: 100%;
        }

        .code-row.target {
            background: var(--line-highlight);
        }

        .code-num {
            width: 50px;
            min-width: 50px;
            text-align: right;
            padding-right: 18px;
            color: #52525b;
            user-select: none;
        }

        .code-row.target .code-num {
            color: #fda4af;
            font-weight: 700;
        }

        .code-text {
            flex: 1;
            white-space: pre;
            padding-right: 24px;
            color: #e4e4e7;
        }

        /* Syntax Highlighter Tokens */
        .tok-kw { color: #c084fc; font-weight: 600; }
        .tok-var { color: #f4f4f5; }
        .tok-str { color: #6ee7b7; }
        .tok-com { color: #71717a; font-style: italic; }
        .tok-num { color: #fb923c; }
        .tok-name { color: #2dd4bf; }

        /* Secondary Frames Accordion */
        .stack-list {
            display: flex;
            flex-direction: column;
            gap: 4px;
            margin-top: 14px;
        }

        .stack-row {
            background: #141417;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            overflow: hidden;
            transition: all 0.15s;
        }

        .stack-row-header {
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-family: var(--font-mono);
            font-size: 12px;
            cursor: pointer;
        }
        .stack-row-header:hover {
            background: #18181c;
        }

        .stack-row-path {
            color: #a1a1aa;
        }
        .stack-row-call {
            color: #e4e4e7;
            font-weight: 500;
        }

        .stack-row-code {
            display: none;
            border-top: 1px solid var(--border-color);
        }
        .stack-row.open .stack-row-code {
            display: block;
        }
        .stack-row.open .stack-row-path {
            color: #ffffff;
        }

        /* Toast feedback */
        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            background: #18181b;
            border: 1px solid var(--border-color);
            color: #ffffff;
            font-weight: 600;
            font-size: 12px;
            padding: 8px 16px;
            border-radius: 20px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 10000;
        }
        .toast.show {
            opacity: 1;
        }
    </style>
</head>
<body>

    <div class="container">

        <!-- Top Header -->
        <header class="error-header">
            <div class="error-main">
                <h1 class="error-type"><?= htmlspecialchars(basename(str_replace('\\', '/', get_class($e)))) ?></h1>
                <p class="error-message"><?= htmlspecialchars($e->getMessage()) ?></p>
                <div class="badges-row">
                    <span class="pill pill-dark">SPHP 1.0.0</span>
                    <span class="pill pill-dark">PHP <?= htmlspecialchars(PHP_VERSION) ?></span>
                    <span class="pill pill-red">▲ UNHANDLED</span>
                    <span class="pill pill-red">CODE <?= htmlspecialchars((string) ($e->getCode() ?: 0)) ?></span>
                </div>
            </div>
            <button class="btn-copy-md" onclick="copyMarkdown()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
                <span>Copy as Markdown</span>
            </button>
        </header>

        <!-- Clean Solution Suggestion (if available) -->
        <?php if (!empty($suggestion)): ?>
        <section class="suggestion-box">
            <span class="suggestion-badge">Suggestion</span>
            <span class="suggestion-text"><?= htmlspecialchars($suggestion['action']) ?></span>
        </section>
        <?php endif; ?>

        <!-- Request URL Bar -->
        <div class="request-bar">
            <div class="request-left">
                <span class="method-badge"><?= htmlspecialchars($request['method']) ?></span>
                <span class="request-url"><?= htmlspecialchars($request['url']) ?></span>
            </div>
            <button class="icon-btn" onclick="copyText(<?= json_encode($request['url']) ?>)" title="Copy URL">
                <svg viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
            </button>
        </div>

        <!-- Overview Section with Dotted Leaders -->
        <section class="overview-section">
            <h2 class="section-title">Overview</h2>
            <div class="overview-list">
                <div class="overview-item">
                    <span class="overview-label">DATE</span>
                    <span class="dotted-line"></span>
                    <span class="overview-value"><?= gmdate('Y/m/d H:i:s') ?> UTC</span>
                </div>
                <div class="overview-item">
                    <span class="overview-label">STATUS CODE</span>
                    <span class="dotted-line"></span>
                    <span class="overview-value">
                        <span class="val-badge-red">▲ <?= htmlspecialchars((string) $statusCode) ?></span>
                    </span>
                </div>
                <div class="overview-item">
                    <span class="overview-label">METHOD</span>
                    <span class="dotted-line"></span>
                    <span class="overview-value">
                        <span class="val-badge-dark"><?= htmlspecialchars($request['method']) ?></span>
                    </span>
                </div>
            </div>
        </section>

        <!-- Exception Trace Section -->
        <section class="trace-section">
            <h2 class="section-title">
                <span class="trace-icon">▲</span>
                <span>Exception trace</span>
            </h2>

            <!-- Primary Origin Trace Card -->
            <?php if (!empty($frames[0])): $f0 = $frames[0]; ?>
            <div class="trace-box">
                <div class="trace-box-header">
                    <div class="trace-file-left">
                        <span class="trace-dot"></span>
                        <span><?= htmlspecialchars($f0['formatted_path'] ?? $f0['file']) ?></span>
                    </div>
                    <div class="trace-line-right">
                        <span><?= htmlspecialchars($f0['formatted_path'] ?? $f0['file']) ?>:<?= htmlspecialchars((string) $f0['line']) ?></span>
                        <span class="code-toggle-icon">[ ]</span>
                    </div>
                </div>
                <?php if (!empty($f0['snippet']['lines'])): ?>
                <div class="code-viewer">
                    <?php foreach ($f0['snippet']['lines'] as $line): ?>
                    <div class="code-row <?= $line['is_target'] ? 'target' : '' ?>">
                        <div class="code-num"><?= $line['num'] ?></div>
                        <div class="code-text"><?= $line['html'] ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <!-- Additional Call Stack Frames (expandable) -->
            <?php if (count($frames) > 1): ?>
            <div class="stack-list">
                <?php for ($i = 1; $i < count($frames); $i++): $frame = $frames[$i]; ?>
                <div class="stack-row" id="stack-row-<?= $i ?>">
                    <div class="stack-row-header" onclick="toggleStackRow(<?= $i ?>)">
                        <span class="stack-row-path">
                            • <?= htmlspecialchars($frame['formatted_path'] ?? $frame['file']) ?><?= $frame['line'] ? ':' . $frame['line'] : '' ?>
                        </span>
                        <span class="stack-row-call">
                            <?= htmlspecialchars($frame['call'] ?: '[top]') ?>
                        </span>
                    </div>
                    <?php if (!empty($frame['snippet']['lines'])): ?>
                    <div class="stack-row-code">
                        <div class="code-viewer">
                            <?php foreach ($frame['snippet']['lines'] as $line): ?>
                            <div class="code-row <?= $line['is_target'] ? 'target' : '' ?>">
                                <div class="code-num"><?= $line['num'] ?></div>
                                <div class="code-text"><?= $line['html'] ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
            <?php endif; ?>

        </section>

    </div>

    <div class="toast" id="toast">Copied to clipboard!</div>

    <script>
        function toggleStackRow(idx) {
            const el = document.getElementById('stack-row-' + idx);
            if (el) {
                el.classList.toggle('open');
            }
        }

        function showToast(msg) {
            const t = document.getElementById('toast');
            t.textContent = msg || 'Copied to clipboard!';
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 2000);
        }

        function copyText(str) {
            navigator.clipboard.writeText(str).then(() => showToast('Copied URL!'));
        }

        function copyMarkdown() {
            const errClass = <?= json_encode(get_class($e)) ?>;
            const errMsg = <?= json_encode($e->getMessage()) ?>;
            const errFile = <?= json_encode($e->getFile() . ':' . $e->getLine()) ?>;
            const trace = <?= json_encode($e->getTraceAsString()) ?>;

            const md = `### ${errClass}\n**Message:** ${errMsg}\n**File:** ${errFile}\n\n\`\`\`\n${trace}\n\`\`\``;
            navigator.clipboard.writeText(md).then(() => showToast('Copied as Markdown!'));
        }
    </script>
</body>
</html>
