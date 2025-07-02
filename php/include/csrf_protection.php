<?php
// php/include/csrf_protection.php

/**
 * CSRF-Schutz für Formulare
 */

/**
 * Generiert ein CSRF-Token und speichert es in der Session
 */
function generateCSRFToken(): string {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validiert ein CSRF-Token
 */
function validateCSRFToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Gibt ein verstecktes Input-Feld mit CSRF-Token zurück
 */
function getCSRFTokenField(): string {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Prüft CSRF-Token bei POST-Requests
 */
function checkCSRFToken(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!validateCSRFToken($token)) {
            http_response_code(403);
            die('CSRF-Token ungültig. Bitte versuche es erneut.');
        }
    }
}

/**
 * Regeneriert CSRF-Token (z.B. nach Login)
 */
function regenerateCSRFToken(): void {
    unset($_SESSION['csrf_token']);
    generateCSRFToken();
}
