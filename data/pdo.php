<?php
class Database {
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            try {
                $dsn = 'sqlite:' . __DIR__ . '/brokeandhungry';
                self::$pdo = new PDO($dsn);
                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Verbindungsfehler: " . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
