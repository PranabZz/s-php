<?php



if (!isset($_SESSION) && php_sapi_name() !== 'cli') {
    session_start();
}

require_once __DIR__ . '/Core/ErrorHandler.php';
\Sphp\Core\ErrorHandler::register();


function asset($path)
{
    return '/public/' . ltrim($path, '/');
}


function sanitizeHtml($input)
{
    if (is_array($input)) {
        return array_map('sanitizeHtml', $input);
    }

    // Safely cast to string to avoid null warnings
    $input = (string) $input;

    $input = html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $allowedTags = '<p><br><b><strong><i><em><ul><ol><li><a><img><blockquote><span><div><h1><h2><h3><h4><h5><h6>';

    // Remove script and style tags
    $input = preg_replace('#<(script|style)\b[^>]*>.*?</\1>#is', '', $input);

    // Strip tags except allowed ones
    $clean = strip_tags($input, $allowedTags);

    // Remove event handlers and inline styles
    $clean = preg_replace('/(<[^>]+)(\s*on\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+))/i', '$1', $clean);
    $clean = preg_replace('/(<[^>]+)(\s*style\s*=\s*(".*?"|\'.*?\'|[^\s>]+))/i', '$1', $clean);

    // Clean href/src attributes
    $clean = preg_replace_callback('/(<[^>]+?\s(?:href|src)\s*=\s*)(["\']?)(.*?)(\2)/i', function ($matches) {
        $attrStart = $matches[1];
        $quote = $matches[2];
        $url = trim($matches[3]);

        if (stripos($attrStart, 'src=') !== false && preg_match('/^data:image\/(png|jpeg|jpg|gif|webp);base64,[a-z0-9\/+=]+$/i', $url)) {
            return $matches[0];
        }

        if (preg_match('/^(javascript:|data:)/i', $url)) {
            return $attrStart . $quote . '#' . $quote;
        }

        return $matches[0];
    }, $clean);

    return trim($clean);
}



function csrf()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token']) . '">';
}

function validateCsrfToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url, $message = "")
{
    if (!empty($message)) {
        $_SESSION['message'] = $message;
    }
    header("Location: " . $url);
}


function loadEnv($filePath)
{
    static $loaded = [];
    if (isset($loaded[$filePath])) {
        return true;
    }

    if (!file_exists($filePath)) {
        $fallback = dirname($filePath) . '/.env.example';
        if (file_exists($fallback)) {
            $filePath = $fallback;
        } else {
            return false;
        }
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return false;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        // Strip surrounding quotes
        if (strlen($value) >= 2) {
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
        }

        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
        putenv("$key=$value");
    }

    $loaded[$filePath] = true;
    return true;
}

loadEnv(__DIR__ . '/../.env');

if (!function_exists('env')) {
    function env($key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || ($value === '' && $default !== null)) {
            return $default;
        }

        if (is_string($value)) {
            switch (strtolower($value)) {
                case 'true':
                case '(true)':
                    return true;
                case 'false':
                case '(false)':
                    return false;
                case 'empty':
                case '(empty)':
                    return '';
                case 'null':
                case '(null)':
                    return null;
            }
        }

        return $value;
    }
}

if (!function_exists('get_env')) {
    function get_env($key, $default = null)
    {
        return env($key, $default);
    }
}

if (!function_exists('app')) {
    function app(?string $abstract = null, array $parameters = []): mixed
    {
        if ($abstract === null) {
            return \Sphp\Core\App::getInstance();
        }

        return \Sphp\Core\App::getInstance()->make($abstract, $parameters);
    }
}


function dd($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
    die();
}