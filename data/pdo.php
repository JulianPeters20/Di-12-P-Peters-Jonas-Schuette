<?php
class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            try {
                $dsn = 'sqlite:' . __DIR__ . '/brokeandhungry';
                self::$pdo = new PDO($dsn);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

                // SQLite-spezifische Sicherheitseinstellungen
                self::$pdo->exec('PRAGMA foreign_keys = ON');
                self::$pdo->exec('PRAGMA journal_mode = WAL');

            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                die("Datenbankverbindung fehlgeschlagen.");
            }
        }
        return self::$pdo;
    }
}
