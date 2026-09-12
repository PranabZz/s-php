<?php

namespace Sphp\Core;

class ErrorHandler
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        if (
            session_status() === PHP_SESSION_NONE &&
            php_sapi_name() !== "cli"
        ) {
            @session_start();
        }

        // Start output buffering so we can clear half-rendered content on error
        if (ob_get_level() === 0) {
            ob_start();
        }

        error_reporting(E_ALL);
        ini_set("display_errors", "0");

        set_error_handler([self::class, "handleError"]);
        set_exception_handler([self::class, "handleException"]);
        register_shutdown_function([self::class, "handleShutdown"]);
    }

    public static function handleError(
        int $severity,
        string $message,
        string $file,
        int $line,
    ): bool {
        // Don't handle errors suppressed with @
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if (
            $error &&
            in_array($error["type"], [
                E_ERROR,
                E_CORE_ERROR,
                E_COMPILE_ERROR,
                E_PARSE,
            ])
        ) {
            $exception = new \ErrorException(
                $error["message"],
                0,
                $error["type"],
                $error["file"],
                $error["line"],
            );
            self::handleException($exception);
        }
    }

    private static bool $isHandling = false;

    public static function handleException(\Throwable $e): void
    {
        if (self::$isHandling) {
            echo "<!DOCTYPE html><html><body><h1>Emergency Error</h1><pre>" . htmlspecialchars((string) $e) . "</pre></body></html>";
            exit(1);
        }
        self::$isHandling = true;

        error_log(
            sprintf(
                "[%s] %s in %s on line %d\nStack trace:\n%s",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
                $e->getTraceAsString(),
            ),
        );

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $statusCode = 500;
        if ($e->getCode() >= 400 && $e->getCode() < 600) {
            $statusCode = (int) $e->getCode();
        }

        if (!headers_sent()) {
            http_response_code($statusCode);
        }

        if (php_sapi_name() === "cli") {
            self::renderCli($e);
            exit(1);
        }

        if (self::isJsonRequest()) {
            self::renderJson($e, $statusCode);
            exit(1);
        }

        if (!self::isDebug()) {
            self::renderProduction($e, $statusCode);
            exit(1);
        }

        self::renderWhoops($e, $statusCode);
        exit(1);
    }

    public static function isDebug(): bool
    {
        if (function_exists("env")) {
            $debug = env("APP_DEBUG");
            if ($debug !== null && $debug !== "") {
                return filter_var($debug, FILTER_VALIDATE_BOOLEAN);
            }
            $appEnv = env("APP_ENV");
            if ($appEnv === "production") {
                return false;
            }
        }
        return true;
    }

    public static function isJsonRequest(): bool
    {
        $uri = $_SERVER["REQUEST_URI"] ?? "";
        $path = parse_url($uri, PHP_URL_PATH) ?? "";
        if (str_starts_with($path, "/api")) {
            return true;
        }

        $accept = $_SERVER["HTTP_ACCEPT"] ?? "";
        if (str_contains(strtolower($accept), "application/json")) {
            return true;
        }

        $requestedWith = $_SERVER["HTTP_X_REQUESTED_WITH"] ?? "";
        return strtolower($requestedWith) === "xmlhttprequest";
    }

    public static function getSolutionSuggestion(\Throwable $e): ?array
    {
        $msg = $e->getMessage();

        if (
            str_contains(
                $msg,
                "php_network_getaddresses: getaddrinfo for mysql failed",
            ) ||
            str_contains($msg, "Unknown MySQL server host")
        ) {
            return [
                "title" => "Database Hostname Resolution Failed",
                "description" =>
                    'The hostname "mysql" could not be resolved. This occurs when running the app on your local machine outside of Docker network.',
                "action" =>
                    "Open your .env file and change `DB_HOST = mysql` to `DB_HOST = 127.0.0.1` or `localhost` (or start your Docker containers with `docker compose up -d`).",
            ];
        }

        if (str_contains($msg, "Access denied for user")) {
            return [
                "title" => "Database Authentication Failed",
                "description" =>
                    "MySQL rejected the username or password provided in your database configuration.",
                "action" =>
                    "Check DB_USERNAME and DB_PASSWORD in your .env file and verify they match your database setup.",
            ];
        }

        if (str_contains($msg, "Connection refused")) {
            return [
                "title" => "Database Connection Refused",
                "description" =>
                    "Could not establish connection to the database server on the specified port.",
                "action" =>
                    "Ensure MySQL service is running and listening on the port configured in .env (DB_PORT).",
            ];
        }

        if (str_contains($msg, "Unknown database")) {
            return [
                "title" => "Database Not Found",
                "description" =>
                    "The specified database does not exist on your MySQL server.",
                "action" =>
                    "Create the database or run migrations using: php do migrate",
            ];
        }

        if (
            str_contains($msg, "Failed opening required") ||
            str_contains($msg, "No such file or directory")
        ) {
            return [
                "title" => "Missing File or Autoload Issue",
                "description" => "PHP could not find a required file.",
                "action" =>
                    "Check that dependencies are installed (`composer install`) and relative file paths use `__DIR__`.",
            ];
        }

        return null;
    }

    public static function getFrames(\Throwable $e): array
    {
        $frames = [];
        $baseDir = realpath(__DIR__ . "/../../") ?: "";
        $trace = $e->getTrace();

        $originCall = get_class($e) . ' thrown';
        $skipFirstTrace = false;

        if (!empty($trace)) {
            $firstTraceFile = $trace[0]['file'] ?? '';
            $firstTraceLine = $trace[0]['line'] ?? 0;
            if ($firstTraceFile === $e->getFile() && $firstTraceLine === $e->getLine()) {
                $cls = $trace[0]['class'] ?? '';
                $typ = $trace[0]['type'] ?? '';
                $fn = $trace[0]['function'] ?? '';
                $originCall = $cls ? ($cls . $typ . $fn . '()') : ($fn ? ($fn . '()') : $originCall);
                $skipFirstTrace = true;
            }
        }

        // Frame #0 is the origin where the exception was thrown
        $frames[] = [
            "index" => 0,
            "file" => $e->getFile(),
            "formatted_path" => self::formatDisplayPath($e->getFile()),
            "relative_file" => self::getRelativePath($e->getFile(), $baseDir),
            "line" => $e->getLine(),
            "class" => "",
            "function" => get_class($e),
            "call" => $originCall,
            "is_app" => self::isAppPath($e->getFile(), $baseDir),
            "snippet" => self::highlightFileSnippet(
                $e->getFile(),
                $e->getLine(),
            ),
        ];

        $traceItems = $skipFirstTrace ? array_slice($trace, 1) : $trace;
        foreach ($traceItems as $i => $frame) {
            $file = $frame["file"] ?? "[internal function]";
            $line = $frame["line"] ?? 0;
            $class = $frame["class"] ?? "";
            $type = $frame["type"] ?? "";
            $fn = $frame["function"] ?? "";
            $call = $class
                ? $class . $type . $fn . "()"
                : ($fn
                    ? $fn . "()"
                    : "");

            $isInternal = $file === "[internal function]";
            $frames[] = [
                "index" => count($frames),
                "file" => $file,
                "formatted_path" => !$isInternal
                    ? self::formatDisplayPath($file)
                    : "[internal]",
                "relative_file" => !$isInternal
                    ? self::getRelativePath($file, $baseDir)
                    : "[internal]",
                "line" => $line,
                "class" => $class,
                "function" => $fn,
                "call" => $call,
                "is_app" => !$isInternal && self::isAppPath($file, $baseDir),
                "snippet" => !$isInternal
                    ? self::highlightFileSnippet($file, $line)
                    : null,
            ];
        }

        return $frames;
    }

    public static function formatDisplayPath(string $file): string
    {
        $home = getenv('HOME') ?: '/home/' . (getenv('USER') ?: 'pranab');
        if ($home && str_starts_with($file, $home)) {
            return '~' . substr($file, strlen($home));
        }
        return $file;
    }

    public static function getRelativePath(
        string $file,
        string $baseDir,
    ): string {
        if ($baseDir && str_starts_with($file, $baseDir)) {
            return ltrim(substr($file, strlen($baseDir)), "/\\");
        }
        return $file;
    }

    public static function isAppPath(string $file, string $baseDir): bool
    {
        if (
            str_contains($file, "/vendor/") ||
            str_contains($file, '\\vendor\\')
        ) {
            return false;
        }
        return true;
    }

    public static function highlightFileSnippet(
        string $file,
        int $targetLine,
        int $radius = 8,
    ): ?array {
        if (!file_exists($file) || !is_readable($file) || is_dir($file)) {
            return null;
        }

        $code = file_get_contents($file);
        if ($code === false) {
            return null;
        }

        $tokens = token_get_all($code);
        $html = "";

        foreach ($tokens as $token) {
            if (is_array($token)) {
                [$id, $text] = $token;
                $class = match ($id) {
                    T_COMMENT, T_DOC_COMMENT => "tok-com",
                    T_CONSTANT_ENCAPSED_STRING,
                    T_ENCAPSED_AND_WHITESPACE
                        => "tok-str",
                    T_VARIABLE => "tok-var",
                    T_FUNCTION,
                    T_CLASS,
                    T_INTERFACE,
                    T_TRAIT,
                    T_EXTENDS,
                    T_IMPLEMENTS,
                    T_NAMESPACE,
                    T_USE,
                    T_PUBLIC,
                    T_PROTECTED,
                    T_PRIVATE,
                    T_STATIC,
                    T_FINAL,
                    T_ABSTRACT,
                    T_NEW,
                    T_RETURN,
                    T_IF,
                    T_ELSE,
                    T_ELSEIF,
                    T_WHILE,
                    T_FOR,
                    T_FOREACH,
                    T_AS,
                    T_TRY,
                    T_CATCH,
                    T_FINALLY,
                    T_THROW,
                    T_MATCH,
                    T_SWITCH,
                    T_CASE,
                    T_DEFAULT,
                    T_BREAK,
                    T_CONTINUE
                        => "tok-kw",
                    T_LNUMBER, T_DNUMBER => "tok-num",
                    T_STRING => "tok-name",
                    default => "",
                };

                if ($class !== "") {
                    if (str_contains($text, "\n")) {
                        $parts = explode("\n", $text);
                        $wrapped = array_map(function ($part) use ($class) {
                            return '<span class="' .
                                $class .
                                '">' .
                                htmlspecialchars($part, ENT_QUOTES, "UTF-8") .
                                "</span>";
                        }, $parts);
                        $html .= implode("\n", $wrapped);
                    } else {
                        $html .=
                            '<span class="' .
                            $class .
                            '">' .
                            htmlspecialchars($text, ENT_QUOTES, "UTF-8") .
                            "</span>";
                    }
                } else {
                    $html .= htmlspecialchars($text, ENT_QUOTES, "UTF-8");
                }
            } else {
                $html .= htmlspecialchars($token, ENT_QUOTES, "UTF-8");
            }
        }

        $lines = explode("\n", $html);
        $total = count($lines);
        $start = max(0, $targetLine - $radius - 1);
        $end = min($total - 1, $targetLine + $radius - 1);

        $lineItems = [];
        for ($i = $start; $i <= $end; $i++) {
            $num = $i + 1;
            $lineItems[] = [
                "num" => $num,
                "is_target" => $num === $targetLine,
                "html" => $lines[$i],
            ];
        }

        return [
            "file" => $file,
            "target_line" => $targetLine,
            "start_line" => $start + 1,
            "end_line" => $end + 1,
            "lines" => $lineItems,
        ];
    }

    public static function maskSensitive(array $data): array
    {
        $sensitiveKeys = [
            "password",
            "pwd",
            "secret",
            "token",
            "key",
            "auth",
            "api_key",
            "private_key",
            "hash",
            "db_password",
            "root_password",
            "app_key",
        ];
        $result = [];
        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);
            $isSensitive = false;
            foreach ($sensitiveKeys as $pattern) {
                if (str_contains($lowerKey, $pattern)) {
                    $isSensitive = true;
                    break;
                }
            }
            if ($isSensitive) {
                $result[$key] = "••••••••";
            } elseif (is_array($value)) {
                $result[$key] = self::maskSensitive($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function renderJson(\Throwable $e, int $statusCode): void
    {
        header("Content-Type: application/json; charset=utf-8");
        $debug = self::isDebug();

        $response = [
            "error" => true,
            "message" => $e->getMessage(),
            "code" => $statusCode,
        ];

        if ($debug) {
            $response["exception"] = get_class($e);
            $response["file"] = $e->getFile();
            $response["line"] = $e->getLine();
            $response["suggestion"] = self::getSolutionSuggestion($e);
            $response["trace"] = array_map(function ($frame) {
                return [
                    "file" => $frame["file"] ?? "[internal]",
                    "line" => $frame["line"] ?? 0,
                    "call" =>
                        ($frame["class"] ?? "") .
                        ($frame["type"] ?? "") .
                        ($frame["function"] ?? "") .
                        "()",
                ];
            }, $e->getTrace());
        }

        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public static function renderCli(\Throwable $e): void
    {
        $redBg = "\033[41;97;1m";
        $reset = "\033[0m";
        $bold = "\033[1m";
        $dim = "\033[2m";
        $yellow = "\033[33;1m";
        $cyan = "\033[36m";

        echo "\n";
        echo " {$redBg}  " . get_class($e) . "  {$reset}\n";
        echo " {$bold}" . $e->getMessage() . "{$reset}\n\n";
        echo " {$dim}at{$reset} {$cyan}" .
            $e->getFile() .
            "{$reset}:{$yellow}" .
            $e->getLine() .
            "{$reset}\n\n";

        $suggestion = self::getSolutionSuggestion($e);
        if ($suggestion) {
            echo " {$yellow}💡 " . $suggestion["title"] . "{$reset}\n";
            echo "    " . $suggestion["description"] . "\n";
            echo "    {$bold}Fix:{$reset} " . $suggestion["action"] . "\n\n";
        }

        echo " {$bold}Stack trace:{$reset}\n";
        foreach ($e->getTrace() as $i => $frame) {
            $f = $frame["file"] ?? "[internal function]";
            $l = $frame["line"] ?? "";
            $call =
                ($frame["class"] ?? "") .
                ($frame["type"] ?? "") .
                ($frame["function"] ?? "") .
                "()";
            echo sprintf(
                "  %s#%-2d%s %s%s%s%s\n      %s\n",
                $dim,
                $i,
                $reset,
                $cyan,
                $f,
                $l ? ":" . $l : "",
                $reset,
                $bold . $call . $reset,
            );
        }
        echo "\n";
    }

    public static function renderProduction(
        \Throwable $e,
        int $statusCode,
    ): void {
        $viewFile = __DIR__ . "/../error.html";
        if (file_exists($viewFile)) {
            $content = file_get_contents($viewFile);
            $msg =
                "An error occurred while processing your request. Please try again later.";
            $content = str_replace(
                "params.get('error') || \"Unknown error occurred.\"",
                json_encode($msg),
                $content,
            );
            echo $content;
            return;
        }

        echo "<!DOCTYPE html><html><head><title>500 Server Error</title><style>body{font-family:sans-serif;text-align:center;padding:100px;background:#f8f9fa;color:#333}h1{font-size:48px;margin-bottom:10px}p{font-size:18px;color:#666}</style></head><body><h1>500</h1><p>Server Error. Please try again later.</p></body></html>";
    }

    public static function renderWhoops(\Throwable $e, int $statusCode): void
    {
        $frames = self::getFrames($e);
        $suggestion = self::getSolutionSuggestion($e);
        $headers = function_exists("getallheaders") ? getallheaders() : [];
        $request = [
            "method" => $_SERVER["REQUEST_METHOD"] ?? "GET",
            "url" =>
                (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] === "on"
                    ? "https"
                    : "http") .
                "://" .
                ($_SERVER["HTTP_HOST"] ?? "localhost") .
                ($_SERVER["REQUEST_URI"] ?? "/"),
            "ip" => $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1",
            "user_agent" => $_SERVER["HTTP_USER_AGENT"] ?? "Unknown",
            "referrer" => $_SERVER["HTTP_REFERER"] ?? "None",
        ];
        $queryParams = self::maskSensitive($_GET ?? []);
        $postParams = self::maskSensitive($_POST ?? []);
        $sessionData = self::maskSensitive($_SESSION ?? []);
        $serverEnv = self::maskSensitive($_SERVER ?? []);

        $viewPath = __DIR__ . "/../views/whoops.php";
        if (file_exists($viewPath)) {
            try {
                require $viewPath;
                return;
            } catch (\Throwable $renderError) {
                error_log("ErrorHandler view render failed: " . $renderError->getMessage());
            }
        }

        // Fallback inline rendering if view missing or error occurs
        self::renderProduction($e, $statusCode);
    }
}
