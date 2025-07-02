<?php
// php/include/form_utils.php

/**
 * Validiert die ID aus einem GET/POST-Parameter.
 * Gibt int oder null zurück.
 */
function validateId($idRaw): ?int {
    return (isset($idRaw) && ctype_digit((string)$idRaw)) ? (int)$idRaw : null;
}

/**
 * Validiert eine E-Mail-Adresse.
 * Gibt gültige E-Mail oder null zurück.
 */
function validateEmail($emailRaw): ?string {
    return (isset($emailRaw) && filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) ? $emailRaw : null;
}

/**
 * Gibt true zurück, wenn eingeloggter Nutzer Admin ist.
 */
function istAdmin(): bool {
    return isset($_SESSION['istAdmin']) && $_SESSION['istAdmin'] === true;
}

/**
 * Prüft, ob ein Wert als "nicht leer" gilt.
 */
function is_not_empty($value): bool {
    return isset($value) && trim($value) !== '';
}

/**
 * Filtert ein String-Feld sicher.
 */
function sanitize_text(string $value): string {
    return htmlspecialchars(trim($value));
}

/**
 * Validiert ein ganzzahliges Array (z.B. IDs aus Checkboxen).
 */
function sanitize_int_array(array $werte): array {
    return array_filter(array_map('intval', $werte));
}

/**
 * Validiert eine E-Mail-Adresse.
 * Gibt die E-Mail zurück, wenn sie gültig ist, sonst einen leeren String.
 */
function sanitize_email(string $email): string {
    $email = trim($email);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
}

/**
 * Prüft Datei-Uploads auf Bildformat mit erweiterten Sicherheitsprüfungen.
 */
function validate_and_store_image(array $file, string $uploadDir = 'images/'): ?string {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $maxFileSize = 5 * 1024 * 1024; // 5MB
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    // Grundlegende Validierung
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) return null;

    // Dateigröße prüfen
    if ($file['size'] > $maxFileSize) return null;

    // MIME-Type prüfen
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $allowedTypes, true)) return null;

    // Dateiendung prüfen
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) return null;

    // Bildvalidierung mit getimagesize
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) return null;

    // Sichere Dateinamen generieren
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($file['name']));
    $name = substr($name, 0, 100); // Länge begrenzen
    $zielPfad = $uploadDir . uniqid('img_') . '_' . $name;

    // Upload-Verzeichnis sicherstellen
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    return move_uploaded_file($file['tmp_name'], $zielPfad) ? $zielPfad : null;
}

/**
 * Baut ein Zutaten-Array aus 3 gleichlangen Arrays auf.
 */
function build_zutaten_array(array $namen, array $mengen, array $einheiten): array {
    $zutaten = [];

    foreach ($namen as $i => $name) {
        $z = trim($name ?? '');
        $m = trim($mengen[$i] ?? '');
        $e = trim($einheiten[$i] ?? '');

        if ($z !== '' && $m !== '' && $e !== '') {
            $zutaten[] = [
                'zutat' => sanitize_text($z),
                'menge' => sanitize_text($m),
                'einheit' => sanitize_text($e)
            ];
        }
    }

    return $zutaten;
}

/**
 * Speichert eine Flash-Nachricht in der Session.
 * Wird verwendet, um Nachrichten zwischen Requests anzuzeigen.
 */
function flash(string $type, string $message): void {
    $_SESSION["flash"] = [
        "type" => $type,  // z.B. success, error, warning
        "message" => $message
    ];
}