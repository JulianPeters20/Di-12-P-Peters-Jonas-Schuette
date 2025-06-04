<?php
// Dateipfad: data/pdo-mysql.php

class Database {
    private static ?PDO $connection = null;

    public static function getConnection(): PDO {
        if (!self::$connection) {
            $host = 'localhost';
            $dbname = 'brokeandhungry';
            $user = 'dein_user';
            $pass = 'dein_passwort';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        }

        return self::$connection;
    }
}