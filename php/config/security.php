<?php
// php/config/security.php

/**
 * Zentrale Sicherheitskonfiguration
 */

// Session-Sicherheit
function configureSecureSession(): void {
    // Session-Cookie-Einstellungen
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '1'); // Nur über HTTPS
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    
    // Session-Regeneration
    ini_set('session.gc_maxlifetime', 3600); // 1 Stunde
    ini_set('session.gc_probability', 1);
    ini_set('session.gc_divisor', 100);
}

// Sichere HTTP-Header
function setSecurityHeaders(): void {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy
    $csp = "default-src 'self'; " .
           "script-src 'self' 'unsafe-inline'; " .
           "style-src 'self' 'unsafe-inline'; " .
           "img-src 'self' data:; " .
           "font-src 'self'; " .
           "connect-src 'self'; " .
           "frame-ancestors 'none';";
    
    header("Content-Security-Policy: $csp");
}

// Input-Sanitization
function sanitizeInput(string $input, string $type = 'text'): string {
    switch ($type) {
        case 'email':
            return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var(trim($input), FILTER_SANITIZE_URL);
        case 'int':
            return (string)filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return (string)filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        default:
            return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}

// Passwort-Validierung
function validatePassword(string $password): array {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = 'Passwort muss mindestens 8 Zeichen lang sein.';
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Passwort muss mindestens einen Großbuchstaben enthalten.';
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Passwort muss mindestens einen Kleinbuchstaben enthalten.';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Passwort muss mindestens eine Zahl enthalten.';
    }
    
    return $errors;
}

// IP-Adresse sicher ermitteln
function getClientIP(): string {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = $_SERVER[$key];
            // Bei mehreren IPs die erste nehmen
            if (strpos($ip, ',') !== false) {
                $ip = trim(explode(',', $ip)[0]);
            }
            
            // IP validieren
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

// Sichere Umleitung
function safeRedirect(string $url): void {
    // Nur interne URLs erlauben
    $allowedHosts = [$_SERVER['HTTP_HOST']];
    $parsedUrl = parse_url($url);
    
    if (isset($parsedUrl['host']) && !in_array($parsedUrl['host'], $allowedHosts)) {
        $url = '/'; // Fallback zur Startseite
    }
    
    header("Location: $url");
    exit;
}

// Logging für Sicherheitsereignisse
function logSecurityEvent(string $event, array $data = []): void {
    $logData = [
        'timestamp' => date('Y-m-d H:i:s'),
        'event' => $event,
        'ip' => getClientIP(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'session_id' => session_id(),
        'user_id' => $_SESSION['nutzerId'] ?? null,
        'data' => $data
    ];
    
    $logFile = __DIR__ . '/../../logs/security.log';
    $logDir = dirname($logFile);
    
    if (!is_dir($logDir)) {
        mkdir($logDir, 0750, true);
    }
    
    file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
}
