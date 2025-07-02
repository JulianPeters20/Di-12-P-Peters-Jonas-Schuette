<?php
// php/include/rate_limiting.php

/**
 * Einfaches Rate Limiting für Login-Versuche
 */

/**
 * Prüft und protokolliert Login-Versuche
 */
function checkLoginAttempts(string $email): bool {
    $maxAttempts = 5;
    $timeWindow = 900; // 15 Minuten
    
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    $now = time();
    $attempts = $_SESSION['login_attempts'];
    
    // Alte Versuche entfernen
    $attempts = array_filter($attempts, function($attempt) use ($now, $timeWindow) {
        return ($now - $attempt['time']) < $timeWindow;
    });
    
    // Versuche für diese E-Mail zählen
    $emailAttempts = array_filter($attempts, function($attempt) use ($email) {
        return $attempt['email'] === $email;
    });
    
    $_SESSION['login_attempts'] = $attempts;
    
    return count($emailAttempts) < $maxAttempts;
}

/**
 * Registriert einen fehlgeschlagenen Login-Versuch
 */
function recordFailedLogin(string $email): void {
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = [];
    }
    
    $_SESSION['login_attempts'][] = [
        'email' => $email,
        'time' => time(),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
}

/**
 * Löscht Login-Versuche nach erfolgreichem Login
 */
function clearLoginAttempts(string $email): void {
    if (!isset($_SESSION['login_attempts'])) {
        return;
    }
    
    $_SESSION['login_attempts'] = array_filter($_SESSION['login_attempts'], function($attempt) use ($email) {
        return $attempt['email'] !== $email;
    });
}

/**
 * Gibt die verbleibende Sperrzeit zurück
 */
function getRemainingLockTime(string $email): int {
    $timeWindow = 900; // 15 Minuten
    
    if (!isset($_SESSION['login_attempts'])) {
        return 0;
    }
    
    $attempts = $_SESSION['login_attempts'];
    $emailAttempts = array_filter($attempts, function($attempt) use ($email) {
        return $attempt['email'] === $email;
    });
    
    if (count($emailAttempts) < 5) {
        return 0;
    }
    
    $lastAttempt = max(array_column($emailAttempts, 'time'));
    $remaining = $timeWindow - (time() - $lastAttempt);
    
    return max(0, $remaining);
}
