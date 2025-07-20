<?php
/**
 * SQLite-Datenbankverbindung für Broke & Hungry
 * Singleton-Pattern für einmalige Verbindung mit korrekter .sqlite-Erweiterung
 */
class Database {
    private static ?PDO $pdo = null;

    /**
     * Stellt SQLite-Datenbankverbindung bereit
     * Konfiguriert Sicherheitseinstellungen und Foreign Key Constraints
     *
     * @return PDO Datenbankverbindung
     * @throws RuntimeException bei Verbindungsfehlern
     */
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            try {

                $dsn = 'sqlite:' . __DIR__ . '/brokeandhungry.sqlite';
                self::$pdo = new PDO($dsn);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                // SQLite-spezifische Sicherheitseinstellungen
                self::$pdo->exec('PRAGMA foreign_keys = ON');  // Foreign Key Constraints aktivieren
                self::$pdo->exec('PRAGMA journal_mode = WAL');  // Write-Ahead Logging für bessere Performance

            } catch (PDOException $e) {
                error_log("SQLite-Datenbankverbindungsfehler: " . $e->getMessage());
                throw new RuntimeException("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
