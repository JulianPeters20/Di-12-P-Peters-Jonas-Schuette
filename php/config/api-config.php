<?php
/**
 * API-Konfiguration für externe Dienste
 * 
 * WICHTIG: Diese Datei sollte nicht in die Versionskontrolle!
 * Erstelle eine .env-Datei oder setze Umgebungsvariablen für Produktionsumgebung.
 */

// Spoonacular API-Konfiguration
define('SPOONACULAR_API_KEY', $_ENV['SPOONACULAR_API_KEY'] ?? 'ff9872f585b64b92aed93d68038751b4');

// API-Rate-Limits (Anfragen pro Tag)
define('SPOONACULAR_DAILY_LIMIT', 150); // Free Plan: 150 requests/day

// Cache-Einstellungen
define('API_CACHE_DURATION', 86400); // 24 Stunden

/**
 * Holt den Spoonacular API-Key
 * 
 * @return string|null API-Key oder null wenn nicht konfiguriert
 */
function getSpoonacularApiKey(): ?string {
    $key = SPOONACULAR_API_KEY;

    if (empty($key) || $key === 'HIER_DEINEN_API_KEY_EINFUEGEN') {
        return null;
    }

    return $key;
}

/**
 * Prüft ob die API-Konfiguration vollständig ist
 * 
 * @return bool True wenn API konfiguriert ist
 */
function isApiConfigured(): bool {
    return getSpoonacularApiKey() !== null;
}
