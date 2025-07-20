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
     * Verwendet Umgebungsvariablen für sichere Konfiguration mit verbessertem Fallback
     *
     * @return PDO Datenbankverbindung
     * @throws RuntimeException bei Verbindungsfehlern
     */
    public static function getConnection(): PDO {
        if (!self::$connection) {
            try {
                // Verbesserte Konfiguration mit detaillierteren Fallback-Werten
                $host = $_ENV['DB_HOST'] ?? 'localhost';
                $dbname = $_ENV['DB_NAME'] ?? 'brokeandhungry';
                $user = $_ENV['DB_USER'] ?? 'root';
                $pass = $_ENV['DB_PASS'] ?? '';
                $port = $_ENV['DB_PORT'] ?? '3306';

                // DSN mit Port-Unterstützung
                $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

                self::$connection = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false, // Echte Prepared Statements verwenden
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'",
                    PDO::ATTR_TIMEOUT => 30 // Verbindungs-Timeout
                ]);

                // Verbindung testen
                self::$connection->query('SELECT 1');

            } catch (PDOException $e) {
                $errorDetails = [
                    'host' => $host,
                    'port' => $port,
                    'database' => $dbname,
                    'user' => $user,
                    'error_code' => $e->getCode(),
                    'error_message' => $e->getMessage()
                ];

                error_log("MySQL-Verbindungsfehler: " . json_encode($errorDetails));

                // Benutzerfreundliche Fehlermeldung basierend auf Fehlertyp
                if (strpos($e->getMessage(), 'Access denied') !== false) {
                    throw new RuntimeException("MySQL-Zugriff verweigert. Bitte prüfen Sie Benutzername und Passwort in den Umgebungsvariablen.");
                } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
                    throw new RuntimeException("MySQL-Datenbank '$dbname' existiert nicht. Bitte erstellen Sie die Datenbank zuerst.");
                } elseif (strpos($e->getMessage(), "Can't connect") !== false) {
                    throw new RuntimeException("MySQL-Server nicht erreichbar unter $host:$port. Bitte prüfen Sie die Serververbindung.");
                } else {
                    throw new RuntimeException("MySQL-Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
                }
            }
        }

        return self::$connection;
    }
}