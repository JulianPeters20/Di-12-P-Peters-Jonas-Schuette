<?php
// Dateipfad: data/pdo-mysql.php

/**
 * MySQL-Datenbankverbindung für Broke & Hungry
 * Singleton-Pattern mit verbesserter Fehlerbehandlung und Konfiguration
 */
class Database {
    private static ?PDO $connection = null;

    /**
     * Stellt MySQL-Datenbankverbindung bereit
     * Verwendet Umgebungsvariablen für sichere Konfiguration
     *
     * @return PDO Datenbankverbindung
     * @throws RuntimeException bei Verbindungsfehlern
     */
    public static function getConnection(): PDO {
        if (!self::$connection) {
            try {

                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $dbname = $_ENV['DB_NAME'] ?? 'brokeandhungry';
                $user = $_ENV['DB_USER'] ?? 'root';
                $pass = $_ENV['DB_PASS'] ?? '';

                $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

                self::$connection = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false, // Echte Prepared Statements verwenden
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'"
                ]);
            } catch (PDOException $e) {
                error_log("MySQL-Verbindungsfehler: " . $e->getMessage());
                throw new RuntimeException("MySQL-Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}