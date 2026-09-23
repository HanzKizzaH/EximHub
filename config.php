<?php
// Digital Hub — Configuration
define('APP_NAME', 'Digital Hub');
define('APP_VERSION', '1.0');
define('API_BASE_URL', 'https://api.digitalhub.local/v1');

// Session config — hardened
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);
ini_set('session.gc_maxlifetime', 3600);

session_name('DH_SESSION');
session_start();

// CSRF token generator
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting (in-memory via session — use Redis in production)
function check_rate_limit(string $key, int $max, int $window): bool {
    $now = time();
    $attempts = $_SESSION["rl_{$key}"] ?? [];
    // Remove old attempts outside window
    $attempts = array_filter($attempts, fn($t) => ($now - $t) < $window);
    if (count($attempts) >= $max) return false;
    $attempts[] = $now;
    $_SESSION["rl_{$key}"] = array_values($attempts);
    return true;
}

// JSON response helper
function json_response(bool $success, string $message, array $data = [], int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data, 'timestamp' => date('c')]);
    exit;
}

// Sanitise input
function sanitise(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Redirect helper
function redirect(string $url): void {
    header("Location: {$url}");
    exit;
}

// Auth check
function require_auth(): void {
    if (empty($_SESSION['user'])) {
        redirect('/login.php');
    }
}
