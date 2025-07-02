<?php
// Dateipfad: data/pdo-mysql.php

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (!self::$connection) {
            // Sichere Konfiguration über Umgebungsvariablen
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $dbname = $_ENV['DB_NAME'] ?? 'brokeandhungry';
            $user = $_ENV['DB_USER'] ?? 'dein_user';
            $pass = $_ENV['DB_PASS'] ?? 'dein_passwort';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, // Echte Prepared Statements
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'"
            ]);
        }

        return self::$connection;
    }
}