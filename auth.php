<?php
require_once __DIR__ . '/includes/config.php';

// Only accept POST + JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Method not allowed', [], 405);
}

$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);
$action = sanitise($body['action'] ?? '');

// ---------------------------------------------------------
// LOGIN
// ---------------------------------------------------------
if ($action === 'login') {
    $username = sanitise($body['username'] ?? '');
    $password = $body['password'] ?? '';
    $mfa_code = sanitise($body['mfa_code'] ?? '');
    $csrf     = $body['csrf_token'] ?? '';

    // CSRF check
    if (!verify_csrf_token($csrf)) {
        json_response(false, 'Invalid request token.', [], 403);
    }

    // Validate fields
    if (empty($username) || empty($password)) {
        json_response(false, 'Username and password are required.', [], 422);
    }

    // Rate limit: 5 attempts per 5 minutes per IP
    $ip_key = 'login_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    if (!check_rate_limit($ip_key, 5, 300)) {
        json_response(false, 'Too many login attempts. Please wait 5 minutes.', [], 429);
    }

    // Call .NET API
    $payload = json_encode([
        'username' => $username,
        'password' => $password,
        'mfaCode'  => $mfa_code ?: null,
    ]);

    $ch = curl_init(API_BASE_URL . '/auth/login');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'X-Forwarded-For: ' . ($_SERVER['REMOTE_ADDR'] ?? ''),
            'User-Agent: DigitalHub-Portal/1.0',
        ],
        // In production: CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err  = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        error_log("[DigitalHub] Login API error: {$curl_err}");
        json_response(false, 'Authentication service unavailable. Please try again.', [], 503);
    }

    $api_data = json_decode($response, true);

    if ($http_code === 200 && !empty($api_data['data']['accessToken'])) {
        $user = $api_data['data']['user'];

        // MFA required — return challenge
        if (!empty($api_data['data']['mfaRequired'])) {
            json_response(true, 'MFA required', [
                'mfa_required'  => true,
                'mfa_challenge' => $api_data['data']['mfaChallenge'] ?? null,
            ]);
        }

        // Regenerate session to prevent fixation
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id'               => $user['id'],
            'biller_ext_id'    => $user['billerExtId'],
            'username'         => $user['username'],
            'email'            => $user['email'],
            'full_name'        => $user['fullName'],
            'portal_role'      => $user['portalRole'],
            'access_level'     => $user['accessLevel'],
            'environment'      => $user['environmentAccess'],
            'force_pwd_reset'  => $user['forcePwdReset'] ?? false,
            'mfa_enabled'      => $user['mfaEnabled'] ?? false,
        ];
        $_SESSION['access_token']  = $api_data['data']['accessToken'];
        $_SESSION['refresh_token'] = $api_data['data']['refreshToken'];
        $_SESSION['token_expiry']  = $api_data['data']['expiresAt'];
        $_SESSION['login_ip']      = $_SERVER['REMOTE_ADDR'] ?? '';
        $_SESSION['login_at']      = time();

        // Reset rate limit on success
        unset($_SESSION["rl_{$ip_key}"]);

        $redirect = ($user['forcePwdReset'] ?? false) ? '/change-password.php' : '/dashboard.php';

        json_response(true, 'Login successful.', [
            'redirect'        => $redirect,
            'force_pwd_reset' => $user['forcePwdReset'] ?? false,
            'user' => [
                'full_name'   => $user['fullName'],
                'portal_role' => $user['portalRole'],
            ],
        ]);
    }

    // API returned an error
    $msg = $api_data['message'] ?? 'Invalid credentials.';
    $err_code = match($http_code) {
        401, 403 => 401,
        422      => 422,
        429      => 429,
        423      => 423,
        default  => 401,
    };

    json_response(false, $msg, [], $err_code);
}

// ---------------------------------------------------------
// LOGOUT
// ---------------------------------------------------------
if ($action === 'logout') {
    $csrf = $body['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf)) {
        json_response(false, 'Invalid request token.', [], 403);
    }

    $refresh_token = $_SESSION['refresh_token'] ?? null;
    $access_token  = $_SESSION['access_token'] ?? null;

    // Call API logout if we have tokens
    if ($refresh_token && $access_token) {
        $ch = curl_init(API_BASE_URL . '/auth/logout');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'refreshToken'        => $refresh_token,
                'logoutAllSessions'   => (bool)($body['logout_all'] ?? false),
            ]),
            CURLOPT_TIMEOUT   => 5,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $access_token,
            ],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }

    // Destroy session completely
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p['path'], $p['domain'], $p['secure'], $p['httponly']
        );
    }
    session_destroy();

    json_response(true, 'Logged out successfully.', ['redirect' => '/login.php']);
}

// ---------------------------------------------------------
// REFRESH CSRF (for SPA-style forms)
// ---------------------------------------------------------
if ($action === 'csrf') {
    // Rotate token
    unset($_SESSION['csrf_token']);
    json_response(true, 'Token refreshed', ['csrf_token' => generate_csrf_token()]);
}

json_response(false, 'Unknown action.', [], 400);
